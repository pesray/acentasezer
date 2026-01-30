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

// Her destinasyon için minimum fiyatı ve para birimini al
$destinationPrices = [];
foreach ($destinations as $dest) {
    $priceStmt = $db->prepare("
        SELECT MIN(price) as min_price, currency 
        FROM destination_vehicles 
        WHERE destination_id = ? AND language_code = ? AND price > 0
        GROUP BY currency
        ORDER BY min_price ASC
        LIMIT 1
    ");
    $priceStmt->execute([$dest['id'], $lang]);
    $priceData = $priceStmt->fetch();
    if ($priceData) {
        $destinationPrices[$dest['id']] = $priceData;
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
                <li><a href="<?= SITE_URL ?>"><?= __('menu_home', 'header') ?></a></li>
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

<style>
/* Transfer kartları için özel stiller - Kare kartlar */
#travel-destinations .isotope-container .destination-tile {
    display: block !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image {
    position: relative !important;
    overflow: hidden !important;
    border-radius: 12px !important;
    width: 100% !important;
    padding-bottom: 100% !important;
    height: 0 !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image img {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.3s ease !important;
}

#travel-destinations .isotope-container .destination-tile:hover .tile-image img {
    transform: scale(1.05) !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: linear-gradient(to bottom, transparent 0%, transparent 0%,
        rgba(0, 0, 0, 0.4) 0%,
        rgba(0, 0, 0, 0.85) 65%) !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: flex-end !important;
    padding: 15px !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-tag {
    position: absolute !important;
    top: 12px !important;
    right: 12px !important;
    bottom: auto !important;
    left: auto !important;
    background: #28a745 !important;
    color: white !important;
    padding: 5px 12px !important;
    border-radius: 15px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info {
    color: white !important;
    margin-top: auto !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info h4 {
    color: white !important;
    font-size: 25px !important;
    font-weight: 700 !important;
    margin-bottom: 6px !important;
    line-height: 1.2 !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info p {
    display: none !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .tours-available {
    font-size: 13px !important;
    opacity: 0.9 !important;
    color: white !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .tours-available i {
    margin-right: 4px !important;
}

#travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .starting-price {
    background: #28a745 !important;
    color: white !important;
    padding: 8px 18px !important;
    border-radius: 20px !important;
    font-size: 20px !important;
    font-weight: 600 !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* XL (1200px+) için 6 kart */
@media (min-width: 1200px) {
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content {
        padding: 12px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info h4 {
        font-size: 25px !important;
        margin-bottom: 5px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 5px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .tours-available {
        font-size: 13px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .starting-price {
        font-size: 20px !important;
        padding: 8px 18px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-tag {
        font-size: 10px !important;
        padding: 4px 10px !important;
        top: 10px !important;
        right: 10px !important;
    }
}

/* Responsive - LG (992px - 1199px) için 4 kart */
@media (min-width: 992px) and (max-width: 1199.98px) {
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info h4 {
        font-size: 25px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .tours-available {
        font-size: 13px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .starting-price {
        font-size: 20px !important;
        padding: 8px 18px !important;
    }
}

/* Responsive - Mobil (768px altı) için 2 kart */
@media (max-width: 767.98px) {
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content {
        padding: 12px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info h4 {
        font-size: 25px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .tours-available {
        font-size: 13px !important;
    }
    #travel-destinations .isotope-container .destination-tile .tile-image .overlay-content .destination-info .destination-stats .starting-price {
        padding: 8px 18px !important;
        font-size: 20px !important;
    }
}
</style>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
