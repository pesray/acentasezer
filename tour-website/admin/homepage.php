<?php
/**
 * Anasayfa İçerik Yönetimi
 * Tüm section'ları tek sayfada yönet
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Anasayfa ID'sini al
$homepage = $db->query("SELECT * FROM pages WHERE is_homepage = 1 LIMIT 1")->fetch();

// POST işlemi - header'dan önce yapılmalı
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $homepage) {
    try {
        $db->beginTransaction();
        
        // Sayfa SEO bilgilerini güncelle
        $stmt = $db->prepare("UPDATE pages SET meta_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?");
        $stmt->execute([
            $_POST['meta_title'] ?? '',
            $_POST['meta_description'] ?? '',
            $_POST['meta_keywords'] ?? '',
            $homepage['id']
        ]);
        
        // Section'ları güncelle
        if (isset($_POST['sections'])) {
            foreach ($_POST['sections'] as $sectionId => $data) {
                $isActive = isset($data['is_active']) ? 1 : 0;
                $settings = json_encode($data['settings'] ?? [], JSON_UNESCAPED_UNICODE);
                
                $stmt = $db->prepare("
                    UPDATE sections SET 
                        title = ?, subtitle = ?, content = ?, 
                        settings = ?, background_image = ?, background_video = ?,
                        is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['title'] ?? '',
                    $data['subtitle'] ?? '',
                    $data['content'] ?? '',
                    $settings,
                    $data['background_image'] ?? '',
                    $data['background_video'] ?? '',
                    $isActive,
                    $sectionId
                ]);
            }
        }
        
        $db->commit();
        setFlashMessage('success', 'Anasayfa içeriği başarıyla güncellendi.');
        header('Location: ' . ADMIN_URL . '/homepage.php');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
}

// Header'ı şimdi include et
$pageTitle = 'Anasayfa İçerik Yönetimi';
require_once __DIR__ . '/includes/header.php';

if (!$homepage) {
    echo '<div class="alert alert-danger">Anasayfa bulunamadı!</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = 'tr';

// Section'ları al
$sections = $db->prepare("SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order");
$sections->execute([$homepage['id']]);
$sections = $sections->fetchAll();

// Section başlıkları ve ikonları
$sectionMeta = [
    'hero' => ['icon' => 'bi-film', 'label' => 'Hero / Slider Bölümü', 'color' => '#6366f1'],
    'why_us' => ['icon' => 'bi-patch-check', 'label' => 'Neden Biz Bölümü', 'color' => '#10b981'],
    'featured_destinations' => ['icon' => 'bi-geo-alt', 'label' => 'Öne Çıkan Destinasyonlar', 'color' => '#f59e0b'],
    'featured_tours' => ['icon' => 'bi-compass', 'label' => 'Öne Çıkan Turlar', 'color' => '#3b82f6'],
    'testimonials' => ['icon' => 'bi-chat-quote', 'label' => 'Müşteri Yorumları', 'color' => '#8b5cf6'],
    'cta' => ['icon' => 'bi-megaphone', 'label' => 'Call to Action', 'color' => '#ef4444']
];
?>

<style>
.section-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
}
.section-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.section-header {
    background: #f8fafc;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    border-bottom: 1px solid #e5e7eb;
}
.section-header:hover {
    background: #f1f5f9;
}
.section-header .section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    margin-right: 12px;
}
.section-header .section-title {
    font-weight: 600;
    font-size: 16px;
    color: #1e293b;
    flex: 1;
}
.section-header .section-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-body {
    padding: 24px;
    background: white;
    display: none;
}
.section-body.show {
    display: block;
}
.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: .3s;
    border-radius: 26px;
}
.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}
input:checked + .toggle-slider {
    background-color: #10b981;
}
input:checked + .toggle-slider:before {
    transform: translateX(24px);
}
.toggle-label {
    font-size: 13px;
    color: #64748b;
}
.form-label-sm {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    margin-bottom: 6px;
}
.section-card.collapsed .section-body {
    display: none;
}
.section-card.expanded .section-body {
    display: block;
}
.chevron-icon {
    transition: transform 0.3s;
    color: #94a3b8;
}
.section-card.expanded .chevron-icon {
    transform: rotate(180deg);
}
.seo-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    color: white;
}
.seo-card .form-label {
    color: rgba(255,255,255,0.9);
    font-weight: 500;
}
.seo-card .form-control {
    background: rgba(255,255,255,0.95);
    border: none;
}
.seo-card .char-count {
    color: rgba(255,255,255,0.7);
    font-size: 12px;
}
.btn-save-all {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    padding: 12px 32px;
    font-weight: 600;
    border-radius: 8px;
}
.btn-save-all:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
</style>

<form method="post" id="homepageForm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-house-door text-white" style="font-size: 24px;"></i>
            </div>
            <div>
                <h1 class="h4 mb-0 fw-bold">Anasayfa İçerik Yönetimi</h1>
                <small class="text-muted">Tüm bölümleri tek sayfadan yönetin</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select" style="width: 140px;">
                <option value="tr">🇹🇷 Türkçe</option>
                <option value="en">🇬🇧 English</option>
            </select>
            <button type="submit" class="btn btn-primary btn-save-all">
                <i class="bi bi-check-lg me-2"></i>Tümünü Kaydet
            </button>
        </div>
    </div>

    <!-- SEO Ayarları -->
    <div class="seo-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-search" style="font-size: 20px;"></i>
            <h5 class="mb-0 fw-bold">SEO Ayarları</h5>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Meta Başlık (SEO)</label>
                <input type="text" name="meta_title" class="form-control" value="<?= e($homepage['meta_title'] ?? $homepage['title']) ?>" maxlength="60">
                <div class="char-count mt-1"><?= strlen($homepage['meta_title'] ?? '') ?>/60 karakter</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Meta Açıklama</label>
                <input type="text" name="meta_description" class="form-control" value="<?= e($homepage['meta_description'] ?? '') ?>" maxlength="160">
                <div class="char-count mt-1"><?= strlen($homepage['meta_description'] ?? '') ?>/160 karakter</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Anahtar Kelimeler</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?= e($homepage['meta_keywords'] ?? '') ?>" placeholder="Anahtar, Kelimeler, Virgülle, Ayrılmış">
            </div>
        </div>
    </div>

    <!-- Section'lar -->
    <?php foreach ($sections as $section): 
        $meta = $sectionMeta[$section['section_key']] ?? ['icon' => 'bi-grid', 'label' => $section['title'], 'color' => '#6b7280'];
        $settings = json_decode($section['settings'] ?? '{}', true) ?: [];
    ?>
    <div class="section-card <?= $section['is_active'] ? '' : 'opacity-75' ?>" data-section-id="<?= $section['id'] ?>">
        <div class="section-header" onclick="toggleSection(this)">
            <div class="d-flex align-items-center">
                <div class="section-icon" style="background: <?= $meta['color'] ?>;">
                    <i class="bi <?= $meta['icon'] ?>"></i>
                </div>
                <span class="section-title"><?= e($meta['label']) ?></span>
            </div>
            <div class="section-toggle d-flex align-items-center gap-3">
                <label class="toggle-switch mb-0" onclick="event.stopPropagation();">
                    <input type="checkbox" name="sections[<?= $section['id'] ?>][is_active]" value="1" <?= $section['is_active'] ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label"><?= $section['is_active'] ? 'Görünür' : 'Gizli' ?></span>
                <i class="bi bi-chevron-down chevron-icon"></i>
            </div>
        </div>
        <div class="section-body">
            <?php 
            // Section tipine göre özel form alanları
            switch ($section['section_key']):
                case 'hero':
            ?>
            <!-- Hero Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Ana Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Alt Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][subtitle]" class="form-control" value="<?= e($section['subtitle']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Arka Plan Video</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $section['id'] ?>][background_video]" id="hero_video_<?= $section['id'] ?>" class="form-control" value="<?= e($section['background_video']) ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('hero_video_<?= $section['id'] ?>', 'video')">
                            <i class="bi bi-folder2-open"></i> Seç
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Arka Plan Görsel</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $section['id'] ?>][background_image]" id="hero_image_<?= $section['id'] ?>" class="form-control" value="<?= e($section['background_image']) ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('hero_image_<?= $section['id'] ?>', 'image')">
                            <i class="bi bi-folder2-open"></i> Seç
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 1 Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button1_text]" class="form-control" value="<?= e($settings['button1_text'] ?? 'Start Exploring') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 1 Linki</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button1_url]" class="form-control" value="<?= e($settings['button1_url'] ?? '#') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 2 Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button2_text]" class="form-control" value="<?= e($settings['button2_text'] ?? 'Browse Tours') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 2 Linki</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button2_url]" class="form-control" value="<?= e($settings['button2_url'] ?? '#') ?>">
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="sections[<?= $section['id'] ?>][settings][show_booking_form]" class="form-check-input" id="show_booking_form" value="1" <?= ($settings['show_booking_form'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_booking_form">Rezervasyon Formunu Göster</label>
                    </div>
                </div>
                
                <!-- Rezervasyon Formu Alanları -->
                <div class="col-12">
                    <hr class="my-3">
                    <label class="form-label-sm d-block mb-2 fw-bold"><i class="bi bi-card-text me-1"></i> Rezervasyon Formu Metinleri</label>
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Form Başlığı</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][form_title]" class="form-control" value="<?= e($settings['form_title'] ?? 'Plan Your Adventure') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Destinasyon Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_destination]" class="form-control" value="<?= e($settings['label_destination'] ?? 'Destination') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Destinasyon Placeholder</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][placeholder_destination]" class="form-control" value="<?= e($settings['placeholder_destination'] ?? 'Choose your destination') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Gidiş Tarihi Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_departure]" class="form-control" value="<?= e($settings['label_departure'] ?? 'Departure Date') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Dönüş Tarihi Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_return]" class="form-control" value="<?= e($settings['label_return'] ?? 'Return Date') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Yetişkin Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_adults]" class="form-control" value="<?= e($settings['label_adults'] ?? 'Adults') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Çocuk Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_children]" class="form-control" value="<?= e($settings['label_children'] ?? 'Children') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Tur Tipi Label</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][label_tour_type]" class="form-control" value="<?= e($settings['label_tour_type'] ?? 'Tour Type') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Tur Tipi Placeholder</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][placeholder_tour_type]" class="form-control" value="<?= e($settings['placeholder_tour_type'] ?? 'Select tour type') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Form Buton Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][form_button_text]" class="form-control" value="<?= e($settings['form_button_text'] ?? 'Find Your Perfect Trip') ?>">
                </div>
            </div>
            <?php break; case 'why_us': ?>
            <!-- Why Us Section Ayarları -->
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-sm">Ana Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-sm">Açıklama</label>
                    <textarea name="sections[<?= $section['id'] ?>][content]" class="form-control" rows="3"><?= e($section['content']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Ana Görsel</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $section['id'] ?>][settings][image]" id="whyus_image_<?= $section['id'] ?>" class="form-control" value="<?= e($settings['image'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('whyus_image_<?= $section['id'] ?>', 'image')">
                            <i class="bi bi-folder2-open"></i> Seç
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Deneyim Rozeti</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][experience_badge]" class="form-control" value="<?= e($settings['experience_badge'] ?? '15+') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Rozet Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][experience_text]" class="form-control" value="<?= e($settings['experience_text'] ?? 'Years of Excellence') ?>">
                </div>
                <div class="col-12">
                    <hr class="my-3">
                    <label class="form-label-sm d-block mb-2">İstatistikler</label>
                    <div class="row g-2">
                        <?php 
                        $stats = $settings['stats'] ?? [
                            ['number' => 1200, 'label' => 'Happy Travelers'],
                            ['number' => 85, 'label' => 'Countries Covered'],
                            ['number' => 15, 'label' => 'Years Experience']
                        ];
                        for ($i = 0; $i < 3; $i++): 
                        ?>
                        <div class="col-md-2">
                            <input type="number" name="sections[<?= $section['id'] ?>][settings][stats][<?= $i ?>][number]" class="form-control" placeholder="Sayı" value="<?= (int)($stats[$i]['number'] ?? 0) ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="sections[<?= $section['id'] ?>][settings][stats][<?= $i ?>][label]" class="form-control" placeholder="Etiket" value="<?= e($stats[$i]['label'] ?? '') ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php break; case 'featured_destinations': ?>
            <!-- Destinations Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Gösterilecek Sayı</label>
                    <input type="number" name="sections[<?= $section['id'] ?>][settings][limit]" class="form-control" value="<?= (int)($settings['limit'] ?? 4) ?>" min="1" max="12">
                    <small class="text-muted">Veritabanından kaç destinasyon gösterileceğini belirler</small>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="sections[<?= $section['id'] ?>][settings][show_featured_only]" class="form-check-input" id="dest_featured" value="1" <?= ($settings['show_featured_only'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="dest_featured">Sadece Öne Çıkanlar</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Destinasyonlar, <a href="<?= ADMIN_URL ?>/destinations.php" class="alert-link">Destinasyon Yönetimi</a> sayfasından otomatik olarak çekilir.
                    </div>
                </div>
            </div>
            <?php break; case 'featured_tours': ?>
            <!-- Tours Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Gösterilecek Sayı</label>
                    <input type="number" name="sections[<?= $section['id'] ?>][settings][limit]" class="form-control" value="<?= (int)($settings['limit'] ?? 6) ?>" min="1" max="12">
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="sections[<?= $section['id'] ?>][settings][show_featured_only]" class="form-check-input" id="tour_featured" value="1" <?= ($settings['show_featured_only'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tour_featured">Sadece Öne Çıkanlar</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="sections[<?= $section['id'] ?>][settings][show_view_all]" class="form-check-input" id="tour_viewall" value="1" <?= ($settings['show_view_all'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tour_viewall">Tümünü Gör Butonu Göster</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Tümünü Gör Linki</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][view_all_url]" class="form-control" value="<?= e($settings['view_all_url'] ?? '/turlar') ?>">
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Turlar, <a href="<?= ADMIN_URL ?>/tours.php" class="alert-link">Tur Yönetimi</a> sayfasından otomatik olarak çekilir.
                    </div>
                </div>
            </div>
            <?php break; case 'testimonials': ?>
            <!-- Testimonials Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Gösterilecek Sayı</label>
                    <input type="number" name="sections[<?= $section['id'] ?>][settings][limit]" class="form-control" value="<?= (int)($settings['limit'] ?? 5) ?>" min="1" max="20">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Otomatik Geçiş (ms)</label>
                    <input type="number" name="sections[<?= $section['id'] ?>][settings][autoplay_delay]" class="form-control" value="<?= (int)($settings['autoplay_delay'] ?? 5000) ?>" step="500">
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Yorumlar, <a href="<?= ADMIN_URL ?>/testimonials.php" class="alert-link">Yorum Yönetimi</a> sayfasından otomatik olarak çekilir.
                    </div>
                </div>
            </div>
            <?php break; case 'cta': ?>
            <!-- CTA Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Ana Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Rozet Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][subtitle]" class="form-control" value="<?= e($section['subtitle']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-sm">Açıklama</label>
                    <textarea name="sections[<?= $section['id'] ?>][content]" class="form-control" rows="2"><?= e(strip_tags($section['content'])) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Ana Görsel</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $section['id'] ?>][settings][image]" id="cta_image_<?= $section['id'] ?>" class="form-control" value="<?= e($settings['image'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('cta_image_<?= $section['id'] ?>', 'image')">
                            <i class="bi bi-folder2-open"></i> Seç
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Telefon</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][phone]" class="form-control" value="<?= e($settings['phone'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 1 Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button1_text]" class="form-control" value="<?= e($settings['button1_text'] ?? 'Explore Now') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 1 Linki</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button1_url]" class="form-control" value="<?= e($settings['button1_url'] ?? '/destinasyonlar') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 2 Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button2_text]" class="form-control" value="<?= e($settings['button2_text'] ?? 'View Deals') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Buton 2 Linki</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][button2_url]" class="form-control" value="<?= e($settings['button2_url'] ?? '/turlar') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">İletişim Metni</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][settings][contact_label]" class="form-control" value="<?= e($settings['contact_label'] ?? 'Need help choosing?') ?>">
                </div>
            </div>
            <?php break; case 'default': ?>
            <!-- Genel Section Ayarları -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][title]" class="form-control" value="<?= e($section['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Alt Başlık</label>
                    <input type="text" name="sections[<?= $section['id'] ?>][subtitle]" class="form-control" value="<?= e($section['subtitle']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-sm">İçerik</label>
                    <textarea name="sections[<?= $section['id'] ?>][content]" class="form-control" rows="4"><?= e($section['content']) ?></textarea>
                </div>
            </div>
            <?php endswitch; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary btn-save-all btn-lg">
            <i class="bi bi-check-lg me-2"></i>Tümünü Kaydet
        </button>
    </div>
</form>

<script>
function toggleSection(header) {
    const card = header.closest('.section-card');
    card.classList.toggle('expanded');
    card.classList.toggle('collapsed');
}

// Toggle switch değişikliğinde label güncelle
document.querySelectorAll('.toggle-switch input').forEach(input => {
    input.addEventListener('change', function() {
        const label = this.closest('.section-toggle').querySelector('.toggle-label');
        label.textContent = this.checked ? 'Görünür' : 'Gizli';
        this.closest('.section-card').classList.toggle('opacity-75', !this.checked);
    });
});

// İlk section'ı aç
document.querySelector('.section-card')?.classList.add('expanded');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
