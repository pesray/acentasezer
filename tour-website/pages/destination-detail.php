<?php
/**
 * Transfer (bölge) detay sayfası — "xxx transfer" aramaları için SEO açılış sayfası
 * Veri: Tur Yönetimi > Transferler (başlık, adres, açıklama, içerik, meta, araç fiyatları)
 * Boş bırakılan SEO alanları dile göre otomatik üretilir (Admin > Çeviriler > sunline grubu).
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$slug = $_GET['slug'] ?? '';
$d = sl_destination_by_slug($slug);

if (!$d) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Eski/temel adresle gelindiyse dile ait kanonik adrese kalıcı yönlendir
if ($d['slug'] !== $slug) {
    header('Location: ' . $d['url'], true, 301);
    exit;
}

$lang = sl_lang();
$seo = sl_dest_seo($d);
$pp = sl_price_pair($d);
$vehicles = sl_vehicles();
$g = sl_section('global');
$info = sl_section('info');

// Fiyatı olan araçlar (ucuzdan pahalıya)
$rows = [];
foreach ($d['prices'] as $vid => $pr) {
    if (!empty($vehicles[$vid])) $rows[] = ['v' => $vehicles[$vid], 'p' => $pr['p'], 'c' => $pr['c']];
}
usort($rows, function ($a, $b) { return $a['p'] <=> $b['p']; });

$vars = $seo['vars'];
$vars['vehicles'] = implode(', ', array_map(function ($r) { return $r['v']['name']; }, $rows));

// SSS (şablonlar Admin > Çeviriler'den düzenlenebilir)
$faqs = [];
for ($i = 1; $i <= 5; $i++) {
    if ($i === 1 && !$pp) continue;
    if ($i === 2 && !$rows) continue;
    $faqs[] = ['q' => sl_t('dfaq_q' . $i, $vars), 'a' => sl_t('dfaq_a' . $i, $vars)];
}

// Diğer bölgeler: önce aynı bölge grubu, sonra öne çıkanlar
$others = [];
foreach (sl_bookable_destinations(false) as $o) {
    if ($o['id'] === $d['id']) continue;
    if ($d['region'] !== '' && $o['region'] === $d['region']) $others[] = $o;
}
foreach (sl_bookable_destinations(true) as $o) {
    if (count($others) >= 6) break;
    if ($o['id'] === $d['id'] || in_array($o, $others, true)) continue;
    $others[] = $o;
}
$others = array_slice($others, 0, 6);

$listTitle = sl_t('all_transfers');
try {
    $st = getDB()->prepare("
        SELECT pst.title FROM page_settings ps
        JOIN page_setting_translations pst ON pst.page_setting_id = ps.id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations' LIMIT 1
    ");
    $st->execute([$lang]);
    $t = $st->fetchColumn();
    if ($t) $listTitle = $t;
} catch (Exception $e) {}
$listUrl = sl_page_url('destinations');

// <head>
$seoTitle = $seo['title'];
$metaDescription = $seo['desc'];
$canonicalUrl = $d['url'];
$ogImage = $d['img'];
$preloadImage = $d['img'];
$bodyClass = 'sl-dest has-mbar';
if ($d['airport']) $metaRobots = 'noindex, follow';

$pageSchema = [
    getBreadcrumbSchema([
        ['name' => $listTitle, 'url' => $listUrl],
        ['name' => $d['title'], 'url' => $d['url']],
    ]),
    getTransferServiceSchema($d, $d['url']),
    getFaqSchema($faqs),
];

$intro = $d['desc'] !== '' ? $d['desc'] : sl_t($pp ? 'dest_intro' : 'dest_intro_np', $vars);
if ($d['desc'] !== '') {
    $heroText = mb_strlen($d['desc']) > 200 ? preg_replace('/\s+\S*$/u', '', mb_substr($d['desc'], 0, 200)) . '…' : $d['desc'];
} else {
    $heroText = sl_t('dest_hero', $vars);
}
// Bilgi tablosu (SEO + yapay zekâ cevapları için net bilgiler)
$facts = [[sl_t('route'), sl_t('airport') . ' ⇄ ' . $d['place']]];
if ($d['location'] !== '') $facts[] = [sl_t('loc'), $d['location']];
if ($rows) $facts[] = [sl_t('vehicles_l'), implode(', ', array_map(function ($r) { return $r['v']['name'] . ' (' . sl_t('cap_pax', ['n' => $r['v']['cap']]) . ')'; }, $rows))];
if ($pp) $facts[] = [sl_t('price_l'), sl_t('ow') . ' ' . sl_money($pp['ow'], $pp['c']) . ' · ' . sl_t('rt') . ' ' . sl_money($pp['rt'], $pp['c'])];
$facts[] = [sl_t('meet_l'), sl_t('meet_v')];
$facts[] = [sl_t('payment_l'), sl_t('pay_v')];
$facts[] = [sl_t('cancel_l'), sl_t('cancel_v')];

require_once INCLUDES_PATH . 'header.php';
?>

<header class="hero hero-sm">
  <div class="hero-bg" style="background-image:url('<?= e($d['img']) ?>')"></div>
  <div class="container hero-in">
    <ol class="bc">
      <li><a href="<?= e(langUrl('')) ?>"><?= sl_e('home') ?></a></li>
      <li><a href="<?= e($listUrl) ?>"><?= e($listTitle) ?></a></li>
      <li aria-current="page"><?= e($d['place']) ?></li>
    </ol>
    <div class="eyebrow"><?= e(sl_t('dest_eyebrow', ['airport' => sl_t('airport_short'), 'place' => $d['place']])) ?></div>
    <h1><?= e($d['title']) ?></h1>
    <p><?= e($heroText) ?></p>
  </div>
</header>

<div class="container">
  <div class="qf dbox">
    <div class="row g-4 align-items-center">
      <div class="col-lg-7">
        <?php if ($pp): ?>
        <div class="rc-ozel"><?= sl_e('ozel') ?> · <?= sl_e('per_vehicle') ?></div>
        <div class="rc-row">
          <div class="rc-box rc-rt">
            <div class="rc-rt-h"><?= sl_e('rt') ?></div>
            <div class="rc-rt-p"><?php if ($pp['rt_old']): ?><del><?= e(sl_money($pp['rt_old'], $pp['c'])) ?></del><?php endif; ?><span class="rc-now"><?= e(sl_money($pp['rt'], $pp['c'])) ?></span></div>
          </div>
          <div class="rc-box rc-ow">
            <div class="rc-rt-h"><?= sl_e('ow') ?></div>
            <div class="rc-rt-p"><?php if ($pp['ow_old']): ?><del><?= e(sl_money($pp['ow_old'], $pp['c'])) ?></del><?php endif; ?><span class="rc-now"><?= e(sl_money($pp['ow'], $pp['c'])) ?></span></div>
          </div>
        </div>
        <?php else: ?>
        <div class="rc-np"><?= sl_e('no_price') ?></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-5">
        <div class="dbox-act">
          <a class="btn btn-cta" href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>"><i class="bi bi-calendar2-check me-2"></i><?= sl_e('book') ?></a>
          <?php if ($wa = sl_wa_link($d['title'])): ?>
          <a class="btn btn-ghost" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i><?= sl_e('wa_write') ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php if (!empty($g['trust']) && is_array($g['trust'])): ?>
    <div class="trust">
      <?php foreach ($g['trust'] as $tr): if (trim((string)($tr['t'] ?? '')) === '') continue; ?>
      <span><i class="bi <?= e($tr['i'] ?? 'bi-check2-circle') ?>"></i><?= e($tr['t']) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($rows): ?>
<section class="sec" id="fiyatlar">
  <div class="container">
    <div class="eyebrow"><?= sl_e('vehicle_col') ?></div>
    <h2 class="sec-t"><?= e(sl_t('dest_prices_t', $vars)) ?></h2>
    <p class="sec-s"><?= sl_e('dest_prices_s') ?></p>
    <div class="vlist">
      <?php foreach ($rows as $r): $v = $r['v']; $ow = $r['p']; $rt = $ow * 2; $owO = sl_old_price($ow); $rtO = sl_old_price($rt); ?>
      <div class="vrow">
        <div class="vrow-img"><i class="bi bi-car-front" aria-hidden="true"></i><?php if ($v['img'] !== ''): ?><img src="<?= e($v['img']) ?>" alt="<?= e($v['name']) ?> — <?= e($d['title']) ?>" loading="lazy" decoding="async" width="400" height="250" onerror="this.remove()"><?php endif; ?></div>
        <div>
          <h3><?= e($v['name']) ?></h3>
          <div class="vspec">
            <span><i class="bi bi-people-fill"></i><?= e(sl_t('cap_pax', ['n' => $v['cap']])) ?></span>
            <span><i class="bi bi-briefcase-fill"></i><?= e(sl_t('cap_lug', ['n' => $v['lug']])) ?></span>
            <?php if ($v['cs'] > 0): ?><span><i class="bi bi-person-arms-up"></i><?= e(sl_t('cap_seat', ['n' => $v['cs']])) ?></span><?php endif; ?>
          </div>
          <?php if ($v['sv']): ?>
          <div class="vsv"><?php foreach (array_slice($v['sv'], 0, 8) as $s): ?><span><i class="bi <?= e($s['i']) ?> me-1"></i><?= e($s['n']) ?></span><?php endforeach; ?></div>
          <?php endif; ?>
        </div>
        <div class="vprice">
          <div class="pp"><?= sl_e('ow') ?><?php if ($owO): ?> <del><?= e(sl_money($owO, $r['c'])) ?></del><?php endif; ?> <b><?= e(sl_money($ow, $r['c'])) ?></b></div>
          <div class="pp"><?= sl_e('rt') ?><?php if ($rtO): ?> <del><?= e(sl_money($rtO, $r['c'])) ?></del><?php endif; ?> <b><?= e(sl_money($rt, $r['c'])) ?></b></div>
          <a class="btn btn-cta" href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>" data-vehicle="<?= (int)$v['id'] ?>"><?= sl_e('choose_book') ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sec<?= $rows ? ' pt-0' : '' ?>" id="hakkinda">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-8">
        <div class="eyebrow"><?= e($d['place']) ?></div>
        <h2 class="sec-t"><?= e(sl_t('dest_about', $vars)) ?></h2>
        <p class="lead-in"><?= e($intro) ?></p>
        <?php if ($d['content'] !== ''): ?>
        <div class="prose mt-4"><?= $d['content'] ?></div>
        <?php elseif ($d['desc'] !== ''): ?>
        <div class="prose mt-3"><p><?= e(sl_t($pp ? 'dest_intro' : 'dest_intro_np', $vars)) ?></p></div>
        <?php endif; ?>
        <h3 class="sub-t"><?= sl_e('facts_t') ?></h3>
        <dl class="facts">
          <?php foreach ($facts as $fa): ?><div><dt><?= e($fa[0]) ?></dt><dd><?= e($fa[1]) ?></dd></div><?php endforeach; ?>
        </dl>
        <h3 class="sub-t"><?= sl_e('how_t') ?></h3>
        <ol class="howto">
          <?php for ($hi = 1; $hi <= 4; $hi++): ?><li><?= sl_e('how' . $hi) ?></li><?php endfor; ?>
        </ol>
        <?php if ($d['from'] !== '' && $d['to'] !== ''): ?>
        <div class="chip-links"><a href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>"><i class="bi bi-geo-alt me-1"></i><?= e($d['from'] . ' → ' . $d['to']) ?></a></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-4">
        <aside class="side-card">
          <h3><?= sl_e('dest_why') ?></h3>
          <ul>
            <?php foreach ((array)($g['trust'] ?? []) as $tr): if (trim((string)($tr['t'] ?? '')) === '') continue; ?>
            <li><i class="bi bi-check-circle-fill"></i><span><?= e($tr['t']) ?></span></li>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-cta w-100 mt-4" href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>"><?= sl_e('book') ?></a>
        </aside>
      </div>
    </div>
  </div>
</section>

<?php if ($info['_active'] && !empty($info['items'])): ?>
<section class="sec info" id="bilgi">
  <div class="container"><div class="row g-4 g-lg-5">
    <div class="col-lg-4">
      <div class="eyebrow"><?= e($info['eyebrow'] ?? '') ?></div>
      <h2 class="sec-t"><?= e($info['title'] ?? '') ?></h2>
      <?php if (trim((string)($info['subtitle'] ?? '')) !== ''): ?><p class="sec-s"><?= e($info['subtitle']) ?></p><?php endif; ?>
    </div>
    <div class="col-lg-8"><div class="info-list">
      <?php foreach ((array)$info['items'] as $it): if (trim((string)($it['t'] ?? '')) === '') continue; ?>
      <div class="info-it"><i class="bi <?= e($it['i'] ?? 'bi-check2-circle') ?>" aria-hidden="true"></i><div><h3><?= e($it['t']) ?></h3><p><?= e($it['p'] ?? '') ?></p></div></div>
      <?php endforeach; ?>
    </div></div>
  </div></div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="sec" id="sss">
  <div class="container" style="max-width:920px">
    <div class="eyebrow"><?= sl_e('faq_e') ?></div>
    <h2 class="sec-t"><?= e($d['title']) ?> — <?= sl_e('faq_t') ?></h2>
    <?php sl_partial('faq', ['items' => $faqs]); ?>
  </div>
</section>
<?php endif; ?>

<?php if ($others): ?>
<section class="sec pt-0" id="bolgeler">
  <div class="container">
    <div class="eyebrow"><?= sl_e('all_transfers') ?></div>
    <h2 class="sec-t"><?= sl_e('dest_other') ?></h2>
    <div class="rc-grid">
      <?php foreach ($others as $o) sl_card($o); ?>
    </div>
    <div class="rc-more"><a class="btn btn-ghost" href="<?= e($listUrl) ?>"><?= sl_e('all_transfers_link') ?></a></div>
  </div>
</section>
<?php endif; ?>

<section class="sec pt-0">
  <div class="container">
    <div class="cta-band">
      <div>
        <h2><?= e(sl_t('dest_cta_t', $vars)) ?></h2>
        <p><?= sl_e('dest_cta_s') ?></p>
      </div>
      <div class="acts">
        <a class="btn btn-cta" href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>"><?= sl_e('book') ?></a>
        <?php if ($wa = sl_wa_link($d['title'])): ?><a class="btn btn-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a><?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="mbar">
  <div>
    <small><?= $pp ? sl_e('ow') : e($d['place']) ?></small>
    <b><?= $pp ? e(sl_money($pp['ow'], $pp['c'])) : '' ?></b>
  </div>
  <a class="btn btn-cta" href="<?= e($d['url']) ?>#fiyatlar" data-book="<?= (int)$d['id'] ?>"><?= sl_e('book') ?></a>
</div>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
