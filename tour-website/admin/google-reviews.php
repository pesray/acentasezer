<?php
/**
 * Google Yorumları — anasayfadaki "Müşteri Yorumları" alanının yorum listesi
 *
 * - Yorumlar Google'dan elle eklenir (ad, yıldız, metin, yazıldığı dil, yaklaşık tarih)
 * - Her yorum tek tıkla Gizle / Göster; ↑ ↓ ile sıralanır; sitede yalnız görünür + metni olanlar çıkar
 * - Alanın açılıp kapanması, yeri, puan / yorum sayısı ve başlıklar: Anasayfa & Tema › Google Yorumları
 * Tablo: google_reviews (migrations/036_google_yorumlari.sql)
 * PHP 7.4 uyumlu. Kayıt işlemleri sayfa çıktısından ÖNCE yapılır (yönlendirme her sunucuda çalışsın).
 */

require_once __DIR__ . '/includes/auth.php';
csrfProtect(); // POST + silme bağlantısı güvenlik anahtarı ister
require_once INCLUDES_PATH . 'sunline/core.php';

$db = getDB();
$self = ADMIN_URL . '/google-reviews.php';

$tableOk = true;
try {
    $db->query("SELECT 1 FROM google_reviews LIMIT 1");
} catch (Exception $e) {
    $tableOk = false;
}

/** Yorumun yazıldığı dil seçenekleri (site dilleri + sık gelenler) */
function gr_lang_options() {
    return [
        'tr' => 'Türkçe', 'en' => 'İngilizce', 'de' => 'Almanca', 'ru' => 'Rusça',
        'nl' => 'Felemenkçe', 'fr' => 'Fransızca', 'it' => 'İtalyanca', 'es' => 'İspanyolca',
        'pl' => 'Lehçe', 'uk' => 'Ukraynaca', 'ar' => 'Arapça', 'fa' => 'Farsça',
    ];
}

/** Sitedeki sırayla (sort_order, yeni tarih, id) bütün yorumların id listesi */
function gr_ordered_ids($db) {
    return array_map('intval', $db->query("SELECT id FROM google_reviews ORDER BY sort_order ASC, review_date DESC, id DESC")->fetchAll(PDO::FETCH_COLUMN));
}

function gr_back($self, $q = '') {
    header('Location: ' . $self . ($q !== '' ? '?' . $q : ''));
    exit;
}

$filter = in_array($_GET['f'] ?? '', ['vis', 'hid'], true) ? $_GET['f'] : 'all';
$keepQ = $filter !== 'all' ? 'f=' . $filter : '';

/* ======================================================================
 * KAYIT İŞLEMLERİ (çıktıdan önce)
 * ====================================================================== */
if ($tableOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($act === 'save') {
            $name = mb_substr(trim((string)($_POST['author_name'] ?? '')), 0, 120);
            $content = mb_substr(trim(str_replace("\r\n", "\n", (string)($_POST['content'] ?? ''))), 0, 5000);
            $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $lang = strtolower(trim((string)($_POST['language_code'] ?? '')));
            if (!preg_match('/^[a-z]{2,3}$/', $lang)) $lang = null;
            $date = trim((string)($_POST['review_date'] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4))) $date = null;
            if ($date !== null && $date > date('Y-m-d')) $date = date('Y-m-d');
            $photo = mb_substr(trim((string)($_POST['author_photo'] ?? '')), 0, 500);
            $visible = !empty($_POST['is_visible']) ? 1 : 0;

            if ($name === '' || $content === '') {
                setFlashMessage('error', 'Yazar adı ve yorum metni gereklidir.');
                gr_back($self, $id ? 'action=edit&id=' . $id : 'action=add');
            }
            if ($id) {
                $st = $db->prepare("UPDATE google_reviews SET author_name = ?, author_photo = ?, rating = ?, content = ?, language_code = ?, review_date = ?, is_visible = ? WHERE id = ?");
                $st->execute([$name, $photo !== '' ? $photo : null, $rating, $content, $lang, $date, $visible, $id]);
                setFlashMessage('success', 'Yorum güncellendi.');
            } else {
                // Yeni yorum listenin en üstüne eklenir
                $min = $db->query("SELECT MIN(sort_order) FROM google_reviews")->fetchColumn();
                $sort = $min === null ? 10 : (int)$min - 10;
                $st = $db->prepare("INSERT INTO google_reviews (author_name, author_photo, rating, content, language_code, review_date, is_visible, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $st->execute([$name, $photo !== '' ? $photo : null, $rating, $content, $lang, $date, $visible, $sort]);
                setFlashMessage('success', 'Yorum eklendi (listenin en üstüne).');
            }
            gr_back($self, $keepQ);
        }

        if ($act === 'toggle' && $id) {
            $db->prepare("UPDATE google_reviews SET is_visible = 1 - is_visible WHERE id = ?")->execute([$id]);
            $stv = $db->prepare("SELECT is_visible FROM google_reviews WHERE id = ?");
            $stv->execute([$id]);
            $v = (int)$stv->fetchColumn();
            setFlashMessage('success', $v ? 'Yorum sitede gösteriliyor.' : 'Yorum gizlendi (sitede görünmez).');
            gr_back($self, $keepQ);
        }

        if (($act === 'up' || $act === 'down') && $id) {
            $ids = gr_ordered_ids($db);
            $i = array_search($id, $ids, true);
            if ($i !== false) {
                $j = $act === 'up' ? $i - 1 : $i + 1;
                if ($j >= 0 && $j < count($ids)) {
                    $tmp = $ids[$i]; $ids[$i] = $ids[$j]; $ids[$j] = $tmp;
                    $db->beginTransaction();
                    $st = $db->prepare("UPDATE google_reviews SET sort_order = ? WHERE id = ?");
                    foreach ($ids as $k => $rid) $st->execute([($k + 1) * 10, $rid]);
                    $db->commit();
                }
            }
            gr_back($self, $keepQ);
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        setFlashMessage('error', 'Kaydedilemedi: ' . $e->getMessage());
        gr_back($self, $keepQ);
    }
}

if ($tableOk && ($_GET['action'] ?? '') === 'delete' && !empty($_GET['id'])) {
    $db->prepare("DELETE FROM google_reviews WHERE id = ?")->execute([(int)$_GET['id']]);
    setFlashMessage('success', 'Yorum silindi.');
    gr_back($self, $keepQ);
}

/* ======================================================================
 * GÖRÜNÜM
 * ====================================================================== */
$action = $_GET['action'] ?? 'list';
$editData = null;
if ($tableOk && $action === 'edit' && !empty($_GET['id'])) {
    $st = $db->prepare("SELECT * FROM google_reviews WHERE id = ?");
    $st->execute([(int)$_GET['id']]);
    $editData = $st->fetch() ?: null;
    if (!$editData) $action = 'list';
}

$rvSec = sl_section('reviews', 'tr');
$pageTitle = 'Google Yorumları';
require_once __DIR__ . '/includes/header.php';
?>
<style>
.gr-av{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex:none;object-fit:cover}
.gr-st{color:#fbbc04;letter-spacing:1px;white-space:nowrap}.gr-st .off{color:#cbd5e1}
.gr-txt{max-width:460px;font-size:.88rem;color:var(--text-muted);white-space:normal}
.gr-row.hid td{opacity:.55}.gr-row.hid td.gr-keep{opacity:1}
.gr-badge{font-size:.72rem;font-weight:700;padding:3px 8px;border-radius:999px}
.gr-mini{display:inline}
.gr-tools{white-space:nowrap}
</style>

<?php if (!$tableOk): ?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>Yorum tablosu veritabanında yok. Önce <code>migrations/036_google_yorumlari.sql</code> çalıştırılmalı (phpMyAdmin › İçe aktar).</div>
<?php require_once __DIR__ . '/includes/footer.php'; exit; endif; ?>

<?php if ($action === 'add' || $action === 'edit'):
    $d = $editData ?: ['author_name' => '', 'author_photo' => '', 'rating' => 5, 'content' => '', 'language_code' => 'tr', 'review_date' => date('Y-m-d'), 'is_visible' => 1];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= $editData ? 'Yorumu Düzenle' : 'Yeni Google Yorumu' ?></h5>
  <a href="<?= e($self) ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Listeye dön</a>
</div>
<form method="post" action="<?= e($self) ?>" class="card"><div class="card-body">
  <?= csrfField() ?>
  <input type="hidden" name="act" value="save">
  <input type="hidden" name="id" value="<?= (int)($editData['id'] ?? 0) ?>">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Yazar adı (Google'daki gibi) *</label>
      <input class="form-control" name="author_name" maxlength="120" required value="<?= e($d['author_name']) ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label">Yıldız</label>
      <select class="form-select" name="rating">
        <?php for ($s = 5; $s >= 1; $s--): ?><option value="<?= $s ?>" <?= (int)$d['rating'] === $s ? 'selected' : '' ?>><?= str_repeat('★', $s) . str_repeat('☆', 5 - $s) ?> (<?= $s ?>)</option><?php endfor; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label">Yorumun dili</label>
      <select class="form-select" name="language_code">
        <?php $known = gr_lang_options(); $cur = (string)$d['language_code']; if ($cur !== '' && !isset($known[$cur])) $known[$cur] = strtoupper($cur); ?>
        <?php foreach ($known as $code => $label): ?><option value="<?= e($code) ?>" <?= $cur === $code ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Yorum metni (Google'daki orijinal haliyle) *</label>
      <textarea class="form-control" name="content" rows="5" maxlength="5000" required><?= e($d['content']) ?></textarea>
      <div class="form-text">Google yorumu çevrilmiş gösteriyorsa "Orijinal içeriği göster"e basıp orijinalini yapıştırın. Sitede, sayfanın dilinde yazılmış yorumlar öne alınır.</div>
    </div>
    <div class="col-md-4">
      <label class="form-label">Google'da ne yazıyor?</label>
      <select class="form-select" id="grAgo">
        <option value="">— seçin, tarih otomatik dolsun —</option>
        <option value="d:0">bugün</option>
        <?php for ($k = 1; $k <= 6; $k++): ?><option value="d:<?= $k ?>"><?= $k ?> gün önce</option><?php endfor; ?>
        <?php for ($k = 1; $k <= 3; $k++): ?><option value="w:<?= $k ?>"><?= $k ?> hafta önce</option><?php endfor; ?>
        <?php for ($k = 1; $k <= 11; $k++): ?><option value="m:<?= $k ?>"><?= $k === 1 ? 'bir' : $k ?> ay önce</option><?php endfor; ?>
        <?php for ($k = 1; $k <= 6; $k++): ?><option value="y:<?= $k ?>"><?= $k === 1 ? 'bir' : $k ?> yıl önce</option><?php endfor; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Yaklaşık tarih</label>
      <input class="form-control" type="date" name="review_date" id="grDate" max="<?= date('Y-m-d') ?>" value="<?= e((string)$d['review_date']) ?>">
      <div class="form-text">Sitede "3 ay önce" gibi gösterilir ve zamanla kendiliğinden ilerler.</div>
    </div>
    <div class="col-md-4">
      <label class="form-label">Profil fotoğrafı (isteğe bağlı)</label>
      <div class="input-group"><input class="form-control" id="grPhoto" name="author_photo" value="<?= e((string)$d['author_photo']) ?>" placeholder="boş = baş harf"><button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('grPhoto','image')" title="Medyadan seç"><i class="bi bi-folder2-open"></i></button></div>
    </div>
    <div class="col-12">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="grVis" name="is_visible" value="1" <?= (int)$d['is_visible'] === 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="grVis">Sitede göster</label>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-end gap-2 mt-3">
    <a href="<?= e($self) ?>" class="btn btn-light">Vazgeç</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Kaydet</button>
  </div>
</div></form>
<script>
document.getElementById('grAgo').addEventListener('change', function () {
  var v = this.value; if (!v) return;
  var p = v.split(':'), n = parseInt(p[1], 10), d = new Date();
  if (p[0] === 'd') d.setDate(d.getDate() - n);
  if (p[0] === 'w') d.setDate(d.getDate() - n * 7);
  if (p[0] === 'm') d.setMonth(d.getMonth() - n);
  if (p[0] === 'y') d.setFullYear(d.getFullYear() - n);
  var z = function (x) { return (x < 10 ? '0' : '') + x; };
  document.getElementById('grDate').value = d.getFullYear() + '-' + z(d.getMonth() + 1) + '-' + z(d.getDate());
});
</script>

<?php else:
    $all = $db->query("SELECT * FROM google_reviews ORDER BY sort_order ASC, review_date DESC, id DESC")->fetchAll();
    $nAll = count($all);
    $nVis = 0;
    foreach ($all as $r) if ((int)$r['is_visible'] === 1 && trim((string)$r['content']) !== '') $nVis++;
    $rows = array_values(array_filter($all, function ($r) use ($filter) {
        if ($filter === 'vis') return (int)$r['is_visible'] === 1;
        if ($filter === 'hid') return (int)$r['is_visible'] !== 1;
        return true;
    }));
    $pos = array_flip(array_map(function ($r) { return (int)$r['id']; }, $all));
    $langs = gr_lang_options();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <span class="me-2">Sitede görünen: <b><?= $nVis ?></b> / toplam <?= $nAll ?></span>
    <?php if (!empty($rvSec['_active'])): ?>
    <span class="gr-badge bg-success-subtle text-success">Anasayfa alanı AÇIK</span>
    <?php else: ?>
    <span class="gr-badge bg-secondary-subtle text-secondary">Anasayfa alanı KAPALI</span>
    <?php endif; ?>
    <a class="small ms-1" href="<?= ADMIN_URL ?>/homepage.php#gyorum">aç/kapat · yer · puan →</a>
  </div>
  <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Yorum Ekle</a>
</div>

<ul class="nav nav-pills mb-3 small">
  <li class="nav-item"><a class="nav-link py-1 <?= $filter === 'all' ? 'active' : '' ?>" href="<?= e($self) ?>">Tümü (<?= $nAll ?>)</a></li>
  <li class="nav-item"><a class="nav-link py-1 <?= $filter === 'vis' ? 'active' : '' ?>" href="?f=vis">Görünür</a></li>
  <li class="nav-item"><a class="nav-link py-1 <?= $filter === 'hid' ? 'active' : '' ?>" href="?f=hid">Gizli</a></li>
</ul>

<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr><th style="width:64px">Sıra</th><th>Yazar</th><th>Yorum</th><th>Dil</th><th>Tarih</th><th class="text-center">Sitede</th><th class="text-end">İşlem</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?>
    <tr><td colspan="7" class="text-center text-muted py-4">Yorum yok.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $r):
      $rid = (int)$r['id'];
      $vis = (int)$r['is_visible'] === 1;
      $empty = trim((string)$r['content']) === '';
      $i = $pos[$rid];
      $lc = (string)$r['language_code'];
      $photo = trim((string)$r['author_photo']);
  ?>
    <tr class="gr-row <?= $vis ? '' : 'hid' ?>">
      <td class="gr-tools">
        <form method="post" class="gr-mini"><?= csrfField() ?><input type="hidden" name="act" value="up"><input type="hidden" name="id" value="<?= $rid ?>"><button class="btn btn-sm btn-light" title="Yukarı" <?= $i === 0 ? 'disabled' : '' ?>><i class="bi bi-arrow-up"></i></button></form>
        <form method="post" class="gr-mini"><?= csrfField() ?><input type="hidden" name="act" value="down"><input type="hidden" name="id" value="<?= $rid ?>"><button class="btn btn-sm btn-light" title="Aşağı" <?= $i === $nAll - 1 ? 'disabled' : '' ?>><i class="bi bi-arrow-down"></i></button></form>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <?php if ($photo !== ''): ?><img class="gr-av" src="<?= e(getMediaUrl($photo)) ?>" alt=""><?php else: ?><span class="gr-av" style="background:<?= e(sl_review_color($r['author_name'])) ?>"><?= e(sl_review_initial($r['author_name'])) ?></span><?php endif; ?>
          <div><div class="fw-semibold"><?= e($r['author_name']) ?></div><div class="gr-st"><?= str_repeat('★', (int)$r['rating']) ?><span class="off"><?= str_repeat('★', 5 - (int)$r['rating']) ?></span></div></div>
        </div>
      </td>
      <td><div class="gr-txt"><?= $empty ? '<i>(metin yok — sitede gösterilmez)</i>' : e(mb_strimwidth((string)$r['content'], 0, 160, '…', 'UTF-8')) ?></div></td>
      <td><?= e($langs[$lc] ?? strtoupper($lc)) ?></td>
      <td class="small text-nowrap"><?= e(sl_review_ago($r['review_date'], 'tr')) ?><?php if (!empty($r['review_date'])): ?><br><span class="text-muted"><?= e(date('d.m.Y', strtotime($r['review_date']))) ?></span><?php endif; ?></td>
      <td class="text-center gr-keep">
        <form method="post" class="gr-mini"><?= csrfField() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= $rid ?>">
          <?php if ($vis): ?>
          <button class="btn btn-sm btn-success" title="Gizlemek için tıklayın"><i class="bi bi-eye me-1"></i>Görünür</button>
          <?php else: ?>
          <button class="btn btn-sm btn-outline-secondary" title="Göstermek için tıklayın"><i class="bi bi-eye-slash me-1"></i>Gizli</button>
          <?php endif; ?>
        </form>
      </td>
      <td class="text-end text-nowrap gr-keep">
        <a href="?action=edit&id=<?= $rid ?>" class="btn btn-sm btn-outline-primary" title="Düzenle"><i class="bi bi-pencil"></i></a>
        <a href="?action=delete&id=<?= $rid ?>&<?= csrfQuery() ?><?= $filter !== 'all' ? '&f=' . e($filter) : '' ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu yorum kalıcı olarak silinsin mi? (Gizlemek için Görünür/Gizli düğmesini kullanabilirsiniz.)')"><i class="bi bi-trash"></i></a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<div class="small text-muted mt-2">Sitede sıra: bu listedeki sıra; ziyaretçinin dilinde yazılmış yorumlar öne alınır. Gizli yorumlar sitede hiç görünmez. Yeni yorum listenin en üstüne eklenir.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
