<?php
/**
 * İç sayfa başlığı (temadaki hero'nun küçük hali)
 * Değişkenler: $h1, $sub (ops.), $crumbs = [[etiket, url], ...] (sonuncusu mevcut sayfa), $img (ops.), $eyebrow (ops.), $size ('xs' | 'sm')
 */
$heroCfg = sl_section('hero');
$bgImg = !empty($img) ? $img : (trim((string)($heroCfg['image'] ?? '')) !== '' ? getMediaUrl($heroCfg['image']) : sl_default_base()['hero']['image']);
$crumbs = isset($crumbs) && is_array($crumbs) ? $crumbs : [];
?>
<header class="hero hero-<?= e($size ?? 'xs') ?>">
  <div class="hero-bg" style="background-image:url('<?= e($bgImg) ?>')"></div>
  <div class="container hero-in">
    <ol class="bc">
      <li><a href="<?= e(langUrl('')) ?>"><?= sl_e('home') ?></a></li>
      <?php foreach ($crumbs as $i => $c): ?>
        <?php if ($i === count($crumbs) - 1): ?>
        <li aria-current="page"><?= e($c[0]) ?></li>
        <?php else: ?>
        <li><a href="<?= e($c[1]) ?>"><?= e($c[0]) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
    <?php if (!empty($eyebrow)): ?><div class="eyebrow"><?= e($eyebrow) ?></div><?php endif; ?>
    <h1><?= e($h1 ?? '') ?></h1>
    <?php if (!empty($sub)): ?><p><?= e($sub) ?></p><?php endif; ?>
  </div>
</header>
