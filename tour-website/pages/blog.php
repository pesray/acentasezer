<?php
/**
 * Blog listesi
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$posts = sl_blog_posts(60);
$blogSec = sl_section('blog');
$title = trim((string)($blogSec['title'] ?? '')) !== '' ? $blogSec['title'] : sl_t('blog_t');
$url = sl_page_url('blog');

$seoTitle = $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = sl_t('seo_home_d');
$canonicalUrl = $url;
$bodyClass = 'sl-blog';
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'crumbs' => [[$title, $url]], 'eyebrow' => 'Blog']);
?>
<section class="sec" id="blog">
  <div class="container">
    <?php if (!$posts): ?>
    <p class="sec-s"><?= sl_e('blog_empty') ?></p>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($posts as $p): ?>
      <div class="col-md-6 col-lg-4"><article class="blog-c">
        <?php if ($p['img'] !== ''): ?><a class="bimg" href="<?= e($p['url']) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e(sl_card_img($p['img'])) ?>" alt="" loading="lazy" decoding="async" width="640" height="360"></a><?php else: ?><div class="ph"><?= e($title) ?></div><?php endif; ?>
        <?php if (!empty($p['date'])): ?><time class="bdate" datetime="<?= e(date('Y-m-d', strtotime($p['date']))) ?>"><?= e(date('d.m.Y', strtotime($p['date']))) ?></time><?php endif; ?>
        <h2 class="h5" style="font-size:1.02rem"><a href="<?= e($p['url']) ?>"><?= e($p['title']) ?></a></h2>
        <?php if (trim($p['excerpt']) !== ''): ?><p style="padding:8px 18px 0;margin:0;color:var(--cmuted);font-size:.9rem;line-height:1.6"><?= e(mb_strimwidth(strip_tags($p['excerpt']), 0, 150, '…')) ?></p><?php endif; ?>
        <a href="<?= e($p['url']) ?>"><?= sl_e('read') ?></a>
      </article></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
