<?php
/**
 * Sunline anasayfa — temadaki bölümlerin birebir PHP karşılığı
 * Sıra: Hero (tek görsel ya da misafir fotoğraf mozaiği) → Hızlı form → Bölgeler & Fiyatlar → Bilgilendirme → Misafirlerimiz → Blog → SSS
 *       + Google Yorumları (panelden seçilen yerde: Bilgilendirme sonrası / SSS öncesi (varsayılan) / en alt)
 * İçerik: Admin > Anasayfa & Tema
 */

require_once __DIR__ . '/core.php';

/**
 * Giriş mozaiği açık mı? Açıksa (Hero aktif + "Misafir fotoğraf mozaiği" seçili + en az 1 fotoğraf)
 * fotoğraf yollarını, değilse [] döndürür. index.php de bunu kullanır (mozaikte büyük görsel ön yüklenmez).
 */
function sl_hero_mosaic_photos($hero) {
    if (empty($hero['_active']) || (string)($hero['style'] ?? 'image') !== 'mosaic') return [];
    $out = [];
    foreach ((array)($hero['mosaic'] ?? []) as $p) {
        $p = trim((string)$p);
        if ($p !== '') $out[] = $p;
    }
    return $out;
}

/**
 * Mozaik kare sırası: $tiles kareye $n fotoğraf dağıtılır.
 * - Her fotoğraf eşit sayıda kullanılır (önce hiç kullanılmamışlar)
 * - Son 13 karede kullanılan foto tekrar seçilmez → 4/6/10/12 sütunda yan yana ya da alt alta aynı foto gelmez
 * - Sonuç her istekte aynıdır (rastgele değil)
 */
function sl_mosaic_order($n, $tiles) {
    $n = (int)$n;
    if ($n < 1) return [];
    $seq = [];
    $used = array_fill(0, $n, 0);
    for ($k = 0; $k < $tiles; $k++) {
        $ban = [];
        for ($d = 1; $d <= 13 && $d <= $k; $d++) $ban[$seq[$k - $d]] = true;
        $best = -1;
        $bestKey = 0;
        for ($pass = 0; $pass < 2 && $best < 0; $pass++) {
            for ($i = 0; $i < $n; $i++) {
                if ($pass === 0 && isset($ban[$i])) continue;
                $key = $used[$i] * 1000 + ((($i - $k * 7) % $n) + $n) % $n;
                if ($best < 0 || $key < $bestKey) { $best = $i; $bestKey = $key; }
            }
        }
        $seq[] = $best;
        $used[$best]++;
    }
    return $seq;
}

function sl_render_home() {
    $lang = sl_lang();
    $hero = sl_section('hero');
    $reg = sl_section('regions');
    $info = sl_section('info');
    $guests = sl_section('guests');
    $rv = sl_section('reviews');
    $rvOn = !empty($rv['_active']);
    $rvPos = in_array($rv['position'] ?? '', ['after_info', 'after_blog', 'after_faq'], true) ? $rv['position'] : 'after_blog';
    $blog = sl_section('blog');
    $faq = sl_section('faq');

    $heroImg = trim((string)($hero['image'] ?? '')) !== '' ? getMediaUrl($hero['image']) : sl_default_base()['hero']['image'];

    // Bölge kartları (Tur Yönetimi > Transferler > "Öne Çıkan")
    $onlyFeatured = !isset($reg['only_featured']) || (int)$reg['only_featured'] === 1;
    $cards = sl_bookable_destinations($onlyFeatured);
    if (!$cards) $cards = sl_bookable_destinations(false);

    // Kartlarda geçen bölgelere göre filtre düğmeleri
    $used = [];
    foreach ($cards as $c) if ($c['region'] !== '') $used[$c['region']] = true;
    $filters = [];
    foreach (sl_region_filters() as $f) {
        $k = (string)($f['k'] ?? '');
        if ($k === '' || empty($used[$k])) continue;
        $label = $f['l'][$lang] ?? ($f['l']['en'] ?? $k);
        $filters[] = ['k' => $k, 'l' => $label];
    }
    $allLabel = $reg['all_label'][$lang] ?? ($reg['all_label']['en'] ?? 'All');

    $photos = array_values(array_filter((array)($guests['photos'] ?? []), function ($p) { return trim((string)$p) !== ''; }));
    $showGuests = $guests['_active'] && $photos;
    $posts = $blog['_active'] ? sl_blog_posts((int)($blog['limit'] ?? 6)) : [];
    $faqItems = $faq['_active'] ? array_values(array_filter((array)($faq['items'] ?? []), function ($i) { return trim((string)($i['q'] ?? '')) !== '' && trim((string)($i['a'] ?? '')) !== ''; })) : [];
    $mz = sl_hero_mosaic_photos($hero);
    ?>
<?php if ($mz):
    // Mozaik: büyük marka yazısı + altında H1 başlığı (yıldızsız) tek satır; alt başlık gösterilmez
    $mzBrand = trim((string)($hero['brand'] ?? '')) !== '' ? (string)$hero['brand'] : 'SUNLINE *VIP*';
    $mzKw = trim(str_replace('*', '', (string)($hero['title'] ?? '')));
    $mzN = count($mz);
?>
<header class="hero hero-mz">
  <div class="mz-grid" aria-hidden="true"><?php foreach (sl_mosaic_order($mzN, 72) as $k => $i): ?><i><img src="<?= e(getMediaUrl($mz[$i])) ?>" alt="" width="360" height="360" decoding="async" fetchpriority="low"<?= $k < $mzN ? '' : ' loading="lazy"' ?>></i><?php endforeach; ?></div>
  <div class="container hero-in">
    <?php if (trim((string)($hero['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($hero['eyebrow']) ?></div><?php endif; ?>
    <h1 class="mz-h1"><span class="mz-brand"><?= sl_rich($mzBrand) ?></span><?php if ($mzKw !== ''): ?><span class="mz-kw"><?= e($mzKw) ?></span><?php endif; ?></h1>
  </div>
</header>
<?php else: ?>
<header class="hero"<?= $hero['_active'] ? '' : ' style="min-height:420px"' ?>>
  <div class="hero-bg" style="background-image:url('<?= e($heroImg) ?>')"></div>
  <div class="container hero-in">
    <?php if (trim((string)($hero['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($hero['eyebrow']) ?></div><?php endif; ?>
    <h1><?= sl_rich($hero['title'] ?? 'Antalya VIP Transfer') ?></h1>
    <?php if (trim((string)($hero['subtitle'] ?? '')) !== ''): ?><p><?= e($hero['subtitle']) ?></p><?php endif; ?>
  </div>
</header>
<?php endif; ?>
<div class="container">
  <?php sl_partial('quickform'); ?>
</div>

<?php if ($reg['_active']): ?>
<section class="sec" id="bolgeler">
  <div class="container">
    <?php if (trim((string)($reg['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($reg['eyebrow']) ?></div><?php endif; ?>
    <h2 class="sec-t"><?= e($reg['title'] ?? '') ?></h2>
    <?php if (trim((string)($reg['subtitle'] ?? '')) !== ''): ?><p class="sec-s"><?= e($reg['subtitle']) ?></p><?php endif; ?>
    <?php if (count($filters) > 1): ?>
    <div class="flt" role="toolbar" aria-label="<?= e($reg['title'] ?? '') ?>">
      <button type="button" class="on" data-f="all" aria-pressed="true"><?= e($allLabel) ?></button>
      <?php foreach ($filters as $f): ?><button type="button" data-f="<?= e($f['k']) ?>" aria-pressed="false"><?= e($f['l']) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="rc-grid">
      <?php foreach ($cards as $d) sl_card($d); ?>
    </div>
    <div class="rc-more"><a class="btn btn-ghost" href="<?= e(sl_page_url('destinations')) ?>"><?= sl_e('all_transfers_link') ?></a></div>
  </div>
</section>
<?php endif; ?>

<?php if ($info['_active']): ?>
<section class="sec info" id="bilgi">
  <div class="container"><div class="row g-4 g-lg-5">
    <div class="col-lg-4">
      <?php if (trim((string)($info['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($info['eyebrow']) ?></div><?php endif; ?>
      <h2 class="sec-t"><?= e($info['title'] ?? '') ?></h2>
      <?php if (trim((string)($info['subtitle'] ?? '')) !== ''): ?><p class="sec-s"><?= e($info['subtitle']) ?></p><?php endif; ?>
      <?php if (trim((string)($info['sign'] ?? '')) !== ''): ?><p class="mt-4" style="color:var(--muted);font-size:.9rem"><?= e($info['sign']) ?></p><?php endif; ?>
    </div>
    <div class="col-lg-8"><div class="info-list">
      <?php foreach ((array)($info['items'] ?? []) as $it): if (trim((string)($it['t'] ?? '')) === '') continue; ?>
      <div class="info-it"><i class="bi <?= e($it['i'] ?? 'bi-check2-circle') ?>" aria-hidden="true"></i><div><h3><?= e($it['t']) ?></h3><p><?= e($it['p'] ?? '') ?></p></div></div>
      <?php endforeach; ?>
    </div></div>
  </div></div>
</section>
<?php endif; ?>

<?php
    // Önceki bölüm düz (bantsız) ise sonrakinin üst boşluğu kaldırılır (pt-0).
    // Yorum alanı kapalıyken çıktı eskisiyle birebir aynıdır.
    $plain = false;
    if ($rvOn && $rvPos === 'after_info' && sl_render_reviews($rv, $plain)) $plain = true;
?>
<?php if ($showGuests): ?>
<section class="sec<?= $plain ? ' pt-0' : '' ?>" id="misafir">
  <div class="container">
    <?php if (trim((string)($guests['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($guests['eyebrow']) ?></div><?php endif; ?>
    <h2 class="sec-t"><?= e($guests['title'] ?? '') ?></h2>
    <div class="guests">
      <?php foreach ($photos as $i => $p): ?>
      <figure class="gst"><img src="<?= e(getMediaUrl($p)) ?>" alt="<?= e(($guests['eyebrow'] ?? '') . ' ' . ($i + 1)) ?>" loading="lazy" decoding="async" width="300" height="400"></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($showGuests) $plain = true; ?>
<?php if ($posts): ?>
<section class="sec<?= $plain ? ' pt-0' : '' ?>" id="blog">
  <div class="container">
    <?php if (trim((string)($blog['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($blog['eyebrow']) ?></div><?php endif; ?>
    <h2 class="sec-t"><?= e($blog['title'] ?? '') ?></h2>
    <div class="row g-4 mt-2">
      <?php foreach ($posts as $p): ?>
      <div class="col-md-6 col-lg-4"><article class="blog-c">
        <?php if ($p['img'] !== ''): ?><a class="bimg" href="<?= e($p['url']) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e(sl_card_img($p['img'])) ?>" alt="" loading="lazy" decoding="async" width="640" height="360"></a><?php else: ?><div class="ph"><?= e($blog['title'] ?? '') ?></div><?php endif; ?>
        <h3><a href="<?= e($p['url']) ?>"><?= e($p['title']) ?></a></h3>
        <a href="<?= e($p['url']) ?>"><?= sl_e('read') ?></a>
      </article></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
    if ($posts) $plain = true;
    if ($rvOn && $rvPos === 'after_blog' && sl_render_reviews($rv, $plain)) $plain = true;
?>
<?php if ($faqItems): ?>
<section class="sec<?= $plain ? ' pt-0' : '' ?>" id="sss">
  <div class="container" style="max-width:920px">
    <?php if (trim((string)($faq['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($faq['eyebrow']) ?></div><?php endif; ?>
    <h2 class="sec-t"><?= e($faq['title'] ?? '') ?></h2>
    <?php sl_partial('faq', ['items' => $faqItems]); ?>
  </div>
</section>
<?php endif; ?>
<?php
    if ($faqItems) $plain = true;
    if ($rvOn && $rvPos === 'after_faq') sl_render_reviews($rv, $plain);
    return ['faq' => $faqItems, 'cards' => $cards, 'hero_img' => $heroImg];
}
