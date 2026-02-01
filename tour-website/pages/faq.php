<?php
/**
 * SSS Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('faq', 'general');
$bodyClass = 'faq-page';

$lang = getCurrentLang();
$db = getDB();

// Kategorileri ve FAQ'ları tek sorguda al (optimize edildi)
$stmt = $db->prepare("
    SELECT f.*, 
           COALESCE(ft.question, f.question) as question, 
           COALESCE(ft.answer, f.answer) as answer,
           fc.id as cat_id, fc.name as cat_name, fc.sort_order as cat_sort
    FROM faqs f
    LEFT JOIN faq_translations ft ON f.id = ft.faq_id AND ft.language_code = ?
    LEFT JOIN faq_categories fc ON f.category_id = fc.id
    WHERE f.is_active = 1
    ORDER BY fc.sort_order, f.sort_order
");
$stmt->execute([$lang]);
$faqs = $stmt->fetchAll();

// Kategorilere göre grupla ve kategori bilgilerini çıkar
$faqsByCategory = [];
$categories = [];
foreach ($faqs as $faq) {
    $catId = $faq['category_id'] ?: 0;
    $faqsByCategory[$catId][] = $faq;
    
    // Kategori bilgisini kaydet (ilk karşılaşmada)
    if ($catId > 0 && !isset($categories[$catId])) {
        $categories[$catId] = [
            'id' => $faq['cat_id'],
            'name' => $faq['cat_name']
        ];
    }
}

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('faq', 'general') ?></h1>
        <p><?= __('faq_subtitle', 'general') ?></p>
    </div>
</div>

<section class="faq section">
    <div class="container">
        <?php foreach ($categories as $cat): ?>
        <?php if (!isset($faqsByCategory[$cat['id']])) continue; ?>
        <div class="faq-category mb-5">
            <h3 class="mb-4"><?= e($cat['name']) ?></h3>
            <div class="accordion" id="faqAccordion<?= $cat['id'] ?>">
                <?php foreach ($faqsByCategory[$cat['id']] as $i => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                            <?= e($faq['question']) ?>
                        </button>
                    </h2>
                    <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion<?= $cat['id'] ?>">
                        <div class="accordion-body"><?= nl2br(e($faq['answer'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (isset($faqsByCategory[0])): ?>
        <div class="faq-category">
            <h3 class="mb-4"><?= __('general_questions', 'general') ?></h3>
            <div class="accordion" id="faqAccordionGeneral">
                <?php foreach ($faqsByCategory[0] as $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                            <?= e($faq['question']) ?>
                        </button>
                    </h2>
                    <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordionGeneral">
                        <div class="accordion-body"><?= nl2br(e($faq['answer'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
