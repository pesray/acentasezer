<?php
/**
 * Destinasyonlar Listesi
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$lang = getCurrentLang();
$db = getDB();

// Sayfa ayarlarını çek
$pageSettings = null;
$pageSettingTrans = null;
try {
    $stmt = $db->prepare("
        SELECT ps.*, pst.title as page_title, pst.subtitle as page_subtitle
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations'
    ");
    $stmt->execute([$lang]);
    $pageSettings = $stmt->fetch();
} catch (Exception $e) {}

// Sayfa başlığı (önce ayarlardan, yoksa çeviriden)
$pageTitle = !empty($pageSettings['page_title']) ? $pageSettings['page_title'] : __('menu_destinations', 'header');
$pageSubtitle = !empty($pageSettings['page_subtitle']) ? $pageSettings['page_subtitle'] : __('explore_destinations', 'general');
$pageBgImage = !empty($pageSettings['background_image']) ? getMediaUrl($pageSettings['background_image']) : ASSETS_URL . 'img/page-title-bg.webp';

// Transfer detay sayfası için dil bazlı prefix al
$transferDetailPrefix = 'transfers'; // varsayılan
try {
    $prefixStmt = $db->prepare("
        SELECT pst.slug 
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations'
    ");
    $prefixStmt->execute([$lang]);
    $prefixRow = $prefixStmt->fetch();
    if (!empty($prefixRow['slug'])) {
        $transferDetailPrefix = $prefixRow['slug'];
    }
} catch (Exception $e) {}

$bodyClass = 'destinations-page';

// Destinasyonları çek
$stmt = $db->prepare("
    SELECT d.*, COALESCE(dt.title, d.title) as title, COALESCE(dt.slug, d.slug) as slug,
           COALESCE(dt.description, d.description) as description
    FROM destinations d
    LEFT JOIN destination_translations dt ON d.id = dt.destination_id AND dt.language_code = ?
    WHERE d.status = 'published'
    ORDER BY d.is_featured DESC, d.sort_order
");
$stmt->execute([$lang]);
$destinations = $stmt->fetchAll();

// Tüm destinasyonlar için minimum fiyatları tek sorguda al (N+1 query problemi çözümü)
$destinationPrices = [];
if (!empty($destinations)) {
    $destIds = array_column($destinations, 'id');
    $placeholders = implode(',', array_fill(0, count($destIds), '?'));
    $priceStmt = $db->prepare("
        SELECT destination_id, MIN(price) as min_price, currency 
        FROM destination_vehicles 
        WHERE destination_id IN ($placeholders) AND language_code = ? AND price > 0
        GROUP BY destination_id, currency
    ");
    $params = array_merge($destIds, [$lang]);
    $priceStmt->execute($params);
    $priceResults = $priceStmt->fetchAll();
    foreach ($priceResults as $row) {
        // Her destinasyon için en düşük fiyatı al
        if (!isset($destinationPrices[$row['destination_id']]) || 
            $row['min_price'] < $destinationPrices[$row['destination_id']]['min_price']) {
            $destinationPrices[$row['destination_id']] = $row;
        }
    }
}

// Para birimi sembolleri
$currencySymbols = [
    'EUR' => '€',
    'USD' => '$',
    'TRY' => '₺',
    'GBP' => '£'
];

require_once INCLUDES_PATH . 'header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade" style="background-image: url(<?= e($pageBgImage) ?>);">
    <div class="container position-relative">
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageSubtitle) ?></p>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="<?= langUrl('') ?>"><?= __('menu_home', 'header') ?></a></li>
                <li class="current"><?= e($pageTitle) ?></li>
            </ol>
        </nav>
    </div>
</div><!-- End Page Title -->

<!-- Travel Destinations Section -->
<section id="travel-destinations" class="travel-destinations section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">
            <?php foreach ($destinations as $dest): 
                $imageUrl = !empty($dest['image']) ? getMediaUrl($dest['image']) : ASSETS_URL . 'img/travel/destination-3.webp';
                $priceInfo = $destinationPrices[$dest['id']] ?? null;
                $currencySymbol = $priceInfo ? ($currencySymbols[$priceInfo['currency']] ?? $priceInfo['currency']) : '';
            ?>
            <div class="col-xl-2 col-lg-3 col-6 destination-item isotope-item">
                <a href="<?= langUrl($transferDetailPrefix . '/' . $dest['slug']) ?>" class="destination-tile">
                    <div class="tile-image">
                        <img src="<?= e($imageUrl) ?>" alt="<?= e($dest['title']) ?>" class="img-fluid" loading="lazy">
                        <div class="overlay-content">
                            <?php if (!empty($dest['badge'])): ?>
                            <span class="destination-tag luxury"><?= e($dest['badge']) ?></span>
                            <?php endif; ?>
                            <div class="destination-info">
                                <h4><?= e($dest['title']) ?></h4>
                                <?php if (!empty($dest['description'])): ?>
                                <p><?= e(mb_substr($dest['description'], 0, 100)) ?><?= mb_strlen($dest['description']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                <div class="destination-stats">
                                    <?php if (!empty($dest['location'])): ?>
                                    <span class="tours-available"><i class="bi bi-geo-alt"></i> <?= e($dest['location']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($priceInfo): ?>
                                    <span class="starting-price"><?= __('', 'general') ?> <?= $currencySymbol ?><?= number_format($priceInfo['min_price'], 0) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div><!-- End Destination Item -->
            <?php endforeach; ?>
        </div><!-- End Destinations Container -->

        <?php if (empty($destinations)): ?>
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="bi bi-geo-alt display-1 text-muted"></i>
                <h4 class="mt-3"><?= __('no_destinations', 'general') ?></h4>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section><!-- /Travel Destinations Section -->

<link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/destinations.css">

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
