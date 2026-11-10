<?php
/**
 * Bölge fiyat kartı (temadaki .rc kartının birebir karşılığı)
 * Değişken: $d (sl_destinations() elemanı)
 */
$pp = sl_price_pair($d);
$badge = $d['badge'] !== '' ? $d['badge'] : (sl_discount_pct() > 0 ? sl_t('badge') : '');
$fb = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop';
?>
<article class="rc" data-r="<?= e($d['region'] !== '' ? $d['region'] : 'x') ?>">
  <a class="rc-media" href="<?= e($d['url']) ?>" data-book="<?= (int)$d['id'] ?>" title="<?= e($d['title']) ?>">
    <img src="<?= e($d['img']) ?>" alt="<?= e($d['place']) ?> VIP Transfer" width="640" height="420" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= e($fb) ?>'">
    <?php if ($badge !== '' && $pp): ?><span class="rc-badge"><?= e($badge) ?></span><?php endif; ?>
    <div class="rc-ov"><h3><?= e($d['place']) ?></h3></div>
  </a>
  <div class="rc-body">
    <?php if ($pp): ?>
    <div class="rc-ozel"><?= sl_e('ozel') ?></div>
    <div class="rc-row">
      <div class="rc-box rc-rt">
        <div class="rc-rt-h"><?= sl_e('rt') ?></div>
        <div class="rc-rt-p"><?php if ($pp['rt_old']): ?><del><?= e(sl_money($pp['rt_old'], $pp['c'])) ?></del><?php endif; ?><span class="rc-now"><?= e(sl_money($pp['rt'], $pp['c'])) ?></span></div>
        <?php if ($pp['rt_old']): ?><span class="rc-disc"><i class="bi bi-tag-fill"></i>−<?= e(sl_money($pp['rt_old'] - $pp['rt'], $pp['c'], true)) ?></span><?php endif; ?>
      </div>
      <div class="rc-box rc-ow">
        <div class="rc-rt-h"><?= sl_e('ow') ?></div>
        <div class="rc-rt-p"><?php if ($pp['ow_old']): ?><del><?= e(sl_money($pp['ow_old'], $pp['c'])) ?></del><?php endif; ?><span class="rc-now"><?= e(sl_money($pp['ow'], $pp['c'])) ?></span></div>
        <?php if ($pp['ow_old']): ?><span class="rc-disc"><i class="bi bi-tag-fill"></i>−<?= e(sl_money($pp['ow_old'] - $pp['ow'], $pp['c'], true)) ?></span><?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="rc-np"><?= sl_e('no_price') ?></div>
    <?php endif; ?>
    <a class="btn btn-cta rc-book" href="<?= e($d['url']) ?>" data-book="<?= (int)$d['id'] ?>"><?= sl_e('book') ?></a>
  </div>
</article>
