<?php
/**
 * Anasayfa İçerik Yönetimi — Çoklu Dil Destekli
 * Tüm section'ları tek sayfada her dil için yönet
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Anasayfa
$homepage = $db->query("SELECT * FROM pages WHERE is_homepage = 1 LIMIT 1")->fetch();

// Aktif diller (dinamik, sıralı)
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, sort_order")->fetchAll();
$defaultLang = 'tr';
foreach ($languages as $l) { if ($l['is_default']) { $defaultLang = $l['code']; break; } }

// POST işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $homepage) {
    // CSRF kontrolü
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        setFlashMessage('error', 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.');
        header('Location: ' . ADMIN_URL . '/homepage.php');
        exit;
    }

    try {
        $db->beginTransaction();

        // SEO — page_translations'a her dil için ayrı kayıt
        $seoStmt = $db->prepare("
            INSERT INTO page_translations
                (page_id, language_code, title, slug, meta_title, meta_description, meta_keywords)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                slug = VALUES(slug),
                meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description),
                meta_keywords = VALUES(meta_keywords)
        ");
        if (isset($_POST['seo']) && is_array($_POST['seo'])) {
            foreach ($_POST['seo'] as $langCode => $seo) {
                $title    = trim($seo['meta_title'] ?? '');
                $slugRaw  = trim($seo['slug'] ?? '');
                $slug     = $slugRaw !== '' ? $slugRaw : ($langCode === $defaultLang ? '/' : 'home-' . $langCode);
                $seoStmt->execute([
                    $homepage['id'],
                    $langCode,
                    $title !== '' ? $title : ($homepage['title'] ?: 'Anasayfa'),
                    $slug,
                    $title ?: null,
                    trim($seo['meta_description'] ?? '') ?: null,
                    trim($seo['meta_keywords'] ?? '') ?: null,
                ]);
            }
        }

        // Section'lar
        if (isset($_POST['sections']) && is_array($_POST['sections'])) {
            // Base section update — SADECE dil-bağımsız alanlar.
            // title/subtitle/content/settings ASLA buradan yazılmaz (veri kaybı önleme).
            // Her dil için içerik section_translations'da saklanır; frontend COALESCE ile fallback yapar.
            $secBaseStmt = $db->prepare("
                UPDATE sections SET
                    is_active = ?,
                    background_image = ?,
                    background_video = ?
                WHERE id = ?
            ");

            // Translation upsert (her dil için ayrı kayıt)
            $secTransStmt = $db->prepare("
                INSERT INTO section_translations
                    (section_id, language_code, title, subtitle, content, settings)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    subtitle = VALUES(subtitle),
                    content = VALUES(content),
                    settings = VALUES(settings)
            ");

            foreach ($_POST['sections'] as $sectionId => $sec) {
                $sectionId = (int)$sectionId;
                $isActive  = !empty($sec['is_active']) ? 1 : 0;
                $bgImage   = trim($sec['background_image'] ?? '');
                $bgVideo   = trim($sec['background_video'] ?? '');

                // Base güncelle (sadece dil-bağımsız alanlar)
                $secBaseStmt->execute([
                    $isActive, $bgImage, $bgVideo,
                    $sectionId
                ]);

                // Her dil için translation kaydet
                if (!empty($sec['translations']) && is_array($sec['translations'])) {
                    foreach ($sec['translations'] as $langCode => $tr) {
                        $title    = trim($tr['title']    ?? '');
                        $subtitle = trim($tr['subtitle'] ?? '');
                        $content  = trim($tr['content']  ?? '');
                        $settings = $tr['settings'] ?? [];
                        $settingsJson = json_encode($settings, JSON_UNESCAPED_UNICODE);

                        $secTransStmt->execute([
                            $sectionId, $langCode,
                            $title !== '' ? $title : null,
                            $subtitle !== '' ? $subtitle : null,
                            $content !== '' ? $content : null,
                            $settingsJson
                        ]);
                    }
                }
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

// Header
$pageTitle = 'Anasayfa İçerik Yönetimi';
require_once __DIR__ . '/includes/header.php';

if (!$homepage) {
    echo '<div class="alert alert-danger">Anasayfa bulunamadı! `pages` tablosunda <code>is_homepage = 1</code> olan kayıt yok.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Mevcut SEO çevirileri (her dil için)
$seoData = [];
$seoStmt = $db->prepare("SELECT language_code, title, slug, meta_title, meta_description, meta_keywords FROM page_translations WHERE page_id = ?");
$seoStmt->execute([$homepage['id']]);
foreach ($seoStmt->fetchAll() as $row) {
    $seoData[$row['language_code']] = $row;
}

// Section'lar
$sectionsStmt = $db->prepare("SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order");
$sectionsStmt->execute([$homepage['id']]);
$sections = $sectionsStmt->fetchAll();

// Section çevirileri
$sectionTrans = [];
if ($sections) {
    $sectionIds = array_map(function($s) { return (int)$s['id']; }, $sections);
    $placeholders = implode(',', array_fill(0, count($sectionIds), '?'));
    $stTransStmt = $db->prepare("SELECT * FROM section_translations WHERE section_id IN ($placeholders)");
    $stTransStmt->execute($sectionIds);
    foreach ($stTransStmt->fetchAll() as $row) {
        $sectionTrans[$row['section_id']][$row['language_code']] = $row;
    }
}

// Section meta (tasarım için)
$sectionMeta = [
    'hero'                  => ['icon' => 'bi-film',          'label' => 'Hero / Slider Bölümü',     'color' => '#6366f1'],
    'why_us'                => ['icon' => 'bi-patch-check',   'label' => 'Neden Biz Bölümü',         'color' => '#10b981'],
    'featured_destinations' => ['icon' => 'bi-geo-alt',       'label' => 'Öne Çıkan Destinasyonlar', 'color' => '#f59e0b'],
    'featured_tours'        => ['icon' => 'bi-compass',       'label' => 'Öne Çıkan Turlar',         'color' => '#3b82f6'],
    'testimonials'          => ['icon' => 'bi-chat-quote',    'label' => 'Müşteri Yorumları',        'color' => '#8b5cf6'],
    'cta'                   => ['icon' => 'bi-megaphone',     'label' => 'Call to Action',           'color' => '#ef4444'],
];

/**
 * Section için ilgili dilde değer döner (translations'tan, yoksa base, yoksa default)
 */
function getSectionValue($sectionTrans, $section, $sectionId, $langCode, $field, $default = '') {
    if (isset($sectionTrans[$sectionId][$langCode][$field]) && $sectionTrans[$sectionId][$langCode][$field] !== null) {
        return $sectionTrans[$sectionId][$langCode][$field];
    }
    return $section[$field] ?? $default;
}

function getSectionSetting($sectionTrans, $section, $sectionId, $langCode, $key, $default = '') {
    // Dile özgü settings
    if (isset($sectionTrans[$sectionId][$langCode]['settings'])) {
        $s = json_decode($sectionTrans[$sectionId][$langCode]['settings'] ?? '{}', true) ?: [];
        if (array_key_exists($key, $s)) return $s[$key];
    }
    // Base settings
    $base = json_decode($section['settings'] ?? '{}', true) ?: [];
    return $base[$key] ?? $default;
}
?>

<style>
.lang-tabs-bar {
    display: flex;
    gap: 4px;
    background: var(--hover-bg);
    padding: 4px;
    border-radius: 10px;
    flex-wrap: wrap;
}
.lang-tab-btn {
    background: transparent;
    border: 0;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: .85rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all .18s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.lang-tab-btn:hover { color: var(--text-base); background: rgba(0,0,0,.04); }
.lang-tab-btn.active { background: var(--sidebar-bg); color: var(--primary); box-shadow: var(--shadow-sm); }
[data-bs-theme="dark"] .lang-tab-btn:hover { background: rgba(255,255,255,.05); }

.section-card {
    border: 1px solid var(--sidebar-border);
    border-radius: 12px;
    margin-bottom: 18px;
    overflow: hidden;
    background: var(--sidebar-bg);
    transition: box-shadow .25s;
}
.section-card:hover { box-shadow: var(--shadow-md); }
.section-header {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background: var(--hover-bg);
    border-bottom: 1px solid var(--sidebar-border);
}
.section-header .section-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 17px;
}
.section-header .section-title { font-weight: 600; font-size: 15px; color: var(--text-base); }
.section-body { padding: 20px; display: none; }
.section-card.expanded .section-body { display: block; }
.section-card.expanded .section-header { border-bottom: 1px solid var(--sidebar-border); }
.chevron-icon { transition: transform .25s; color: var(--text-soft); }
.section-card.expanded .chevron-icon { transform: rotate(180deg); }

.toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s;
    border-radius: 24px;
}
.toggle-slider:before {
    position: absolute; content: "";
    height: 18px; width: 18px;
    left: 3px; bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}
input:checked + .toggle-slider { background-color: #10b981; }
input:checked + .toggle-slider:before { transform: translateX(20px); }
.toggle-label { font-size: 12px; color: var(--text-muted); min-width: 50px; }

.form-label-sm { font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .3px; }

.lang-pane { display: none; }
.lang-pane.active { display: block; animation: fadeIn .2s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px);} to { opacity: 1; transform: none; }}

.seo-card {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 22px;
    color: white;
}
.seo-card .form-label-sm { color: rgba(255,255,255,.85); }
.seo-card .form-control { background: rgba(255,255,255,.95); border: none; color: #111827; }
[data-bs-theme="dark"] .seo-card .form-control { background: rgba(255,255,255,.95); color: #111827; }
.seo-card .char-count { color: rgba(255,255,255,.7); font-size: 11px; }

.lang-flag-emoji { font-size: 1rem; line-height: 1; }
.section-summary { color: var(--text-muted); font-size: .82rem; margin-top: 2px; }
</style>

<form method="post" id="homepageForm">
    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-house-door text-white" style="font-size:24px;"></i>
            </div>
            <div>
                <h1 class="h4 mb-0 fw-bold">Anasayfa İçerik Yönetimi</h1>
                <small class="text-muted">Tüm bölümleri her dil için ayrı ayrı yönetin</small>
            </div>
        </div>

        <!-- Dil Tab Bar -->
        <div class="lang-tabs-bar" id="langTabsBar">
            <?php foreach ($languages as $i => $lang): ?>
            <button type="button" class="lang-tab-btn <?= $i === 0 ? 'active' : '' ?>" data-lang="<?= e($lang['code']) ?>">
                <span class="lang-flag-emoji"><?= e($lang['flag']) ?></span>
                <?= e($lang['native_name']) ?>
                <?php if ($lang['is_default']): ?>
                <span class="badge bg-primary ms-1" style="font-size:.6rem;">Varsayılan</span>
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-2"></i>Tümünü Kaydet
        </button>
    </div>

    <!-- SEO Ayarları -->
    <div class="seo-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-search" style="font-size:20px;"></i>
            <h5 class="mb-0 fw-bold">SEO Ayarları</h5>
            <span class="ms-auto small opacity-75">Her dil için ayrı SEO bilgisi</span>
        </div>

        <?php foreach ($languages as $i => $lang):
            $code = $lang['code'];
            $s    = $seoData[$code] ?? [];
        ?>
        <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($code) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-sm">URL Slug <span class="opacity-75">(<?= $code === $defaultLang ? 'Varsayılan dil için "/" kullan' : 'Örn: home-' . $code ?>)</span></label>
                    <input type="text" name="seo[<?= e($code) ?>][slug]" class="form-control"
                           value="<?= e($s['slug'] ?? ($code === $defaultLang ? '/' : 'home-' . $code)) ?>"
                           placeholder="<?= $code === $defaultLang ? '/' : 'home-' . $code ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-sm">Meta Başlık (60 karakter ideal)</label>
                    <input type="text" name="seo[<?= e($code) ?>][meta_title]" class="form-control char-counter"
                           data-max="60" value="<?= e($s['meta_title'] ?? '') ?>" maxlength="80">
                    <div class="char-count mt-1" data-for-input>0/60 karakter</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label-sm">Meta Açıklama (160 karakter ideal)</label>
                    <textarea name="seo[<?= e($code) ?>][meta_description]" class="form-control char-counter"
                              data-max="160" rows="2" maxlength="200"><?= e($s['meta_description'] ?? '') ?></textarea>
                    <div class="char-count mt-1" data-for-input>0/160 karakter</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label-sm">Anahtar Kelimeler (virgülle)</label>
                    <input type="text" name="seo[<?= e($code) ?>][meta_keywords]" class="form-control"
                           value="<?= e($s['meta_keywords'] ?? '') ?>"
                           placeholder="tur, transfer, antalya">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Section'lar -->
    <?php foreach ($sections as $section):
        $sectionId = (int)$section['id'];
        $key       = $section['section_key'];
        $meta      = $sectionMeta[$key] ?? ['icon' => 'bi-grid', 'label' => $section['title'], 'color' => '#6b7280'];
        $baseSettings = json_decode($section['settings'] ?? '{}', true) ?: [];
    ?>
    <div class="section-card <?= $section['is_active'] ? '' : 'opacity-75' ?>" data-section-id="<?= $sectionId ?>">
        <div class="section-header" onclick="toggleSection(this)">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon" style="background:<?= e($meta['color']) ?>;">
                    <i class="bi <?= e($meta['icon']) ?>"></i>
                </div>
                <div>
                    <div class="section-title"><?= e($meta['label']) ?></div>
                    <div class="section-summary"><?= e($section['section_key']) ?></div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="toggle-switch mb-0" onclick="event.stopPropagation();">
                    <input type="checkbox" name="sections[<?= $sectionId ?>][is_active]" value="1" <?= $section['is_active'] ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label"><?= $section['is_active'] ? 'Görünür' : 'Gizli' ?></span>
                <i class="bi bi-chevron-down chevron-icon"></i>
            </div>
        </div>

        <div class="section-body">
            <!-- Dil-bağımsız alanlar (image, video) -->
            <?php if (in_array($key, ['hero','why_us','cta'])): ?>
            <div class="row g-3 mb-3 pb-3" style="border-bottom:1px dashed var(--sidebar-border);">
                <div class="col-12">
                    <small class="text-muted fw-semibold"><i class="bi bi-image me-1"></i> MEDYALAR (Tüm diller için ortak)</small>
                </div>
                <?php if ($key === 'hero'): ?>
                <div class="col-md-6">
                    <label class="form-label-sm">Arka Plan Video</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $sectionId ?>][background_video]" id="bgvid_<?= $sectionId ?>" class="form-control" value="<?= e($section['background_video']) ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('bgvid_<?= $sectionId ?>', 'video')">
                            <i class="bi bi-folder2-open"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label-sm">Arka Plan / Ana Görsel</label>
                    <div class="input-group">
                        <input type="text" name="sections[<?= $sectionId ?>][background_image]" id="bgimg_<?= $sectionId ?>" class="form-control" value="<?= e($section['background_image']) ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('bgimg_<?= $sectionId ?>', 'image')">
                            <i class="bi bi-folder2-open"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="sections[<?= $sectionId ?>][background_image]" value="<?= e($section['background_image']) ?>">
            <input type="hidden" name="sections[<?= $sectionId ?>][background_video]" value="<?= e($section['background_video']) ?>">
            <?php endif; ?>

            <!-- Dil-bağımlı alanlar -->
            <?php foreach ($languages as $i => $lang):
                $code = $lang['code'];
                $title    = getSectionValue($sectionTrans, $section, $sectionId, $code, 'title', $section['title'] ?? '');
                $subtitle = getSectionValue($sectionTrans, $section, $sectionId, $code, 'subtitle', $section['subtitle'] ?? '');
                $content  = getSectionValue($sectionTrans, $section, $sectionId, $code, 'content', $section['content'] ?? '');
            ?>
            <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($code) ?>">
                <?php switch ($key):
                    case 'hero': ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-sm">Ana Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm">Alt Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 1 Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button1_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button1_text', 'Start Exploring')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 1 Linki</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button1_url]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button1_url', '#')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 2 Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button2_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button2_text', 'Browse Tours')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 2 Linki</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button2_url]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button2_url', '#')) ?>">
                    </div>

                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input type="checkbox" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][show_booking_form]" class="form-check-input" id="show_form_<?= $sectionId ?>_<?= e($code) ?>" value="1"
                                <?= getSectionSetting($sectionTrans, $section, $sectionId, $code, 'show_booking_form', 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_form_<?= $sectionId ?>_<?= e($code) ?>">Rezervasyon Formunu Göster</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info mb-0 mt-2 small"><i class="bi bi-info-circle me-1"></i> Form etiketleri (label/placeholder) ileride <code>translations</code> tablosundan otomatik çekilecek (UI çevirileri). Şuan inline ayarlanabilir:</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Form Başlığı</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][form_title]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'form_title', 'Plan Your Adventure')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Buton Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][form_button_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'form_button_text', 'Find Your Perfect Trip')) ?>">
                    </div>
                </div>

                <?php break; case 'why_us': ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-sm">Ana Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Deneyim Rozeti Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][experience_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'experience_text', 'Years of Excellence')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label-sm">Açıklama</label>
                        <textarea name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][content]" class="form-control" rows="3"><?= e($content) ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Deneyim Rozeti (sayı)</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][experience_badge]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'experience_badge', '15+')) ?>">
                    </div>

                    <div class="col-12 mt-2">
                        <small class="text-muted fw-semibold">İSTATİSTİKLER (3 adet)</small>
                    </div>
                    <?php
                    $stats = getSectionSetting($sectionTrans, $section, $sectionId, $code, 'stats', [
                        ['number' => 1200, 'label' => 'Happy Travelers'],
                        ['number' => 85,   'label' => 'Countries Covered'],
                        ['number' => 15,   'label' => 'Years Experience']
                    ]);
                    for ($i2 = 0; $i2 < 3; $i2++):
                        $st = $stats[$i2] ?? ['number' => '', 'label' => ''];
                    ?>
                    <div class="col-md-2">
                        <input type="number" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][stats][<?= $i2 ?>][number]" class="form-control" placeholder="Sayı"
                               value="<?= e($st['number'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][stats][<?= $i2 ?>][label]" class="form-control" placeholder="Etiket"
                               value="<?= e($st['label'] ?? '') ?>">
                    </div>
                    <?php endfor; ?>
                </div>

                <?php break; case 'featured_destinations': ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-sm">Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Alt Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Gösterilecek Sayı</label>
                        <input type="number" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][limit]" class="form-control" min="1" max="12"
                               value="<?= (int)getSectionSetting($sectionTrans, $section, $sectionId, $code, 'limit', 4) ?>">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][show_featured_only]" class="form-check-input" id="dest_feat_<?= $sectionId ?>_<?= e($code) ?>" value="1"
                                <?= getSectionSetting($sectionTrans, $section, $sectionId, $code, 'show_featured_only', 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="dest_feat_<?= $sectionId ?>_<?= e($code) ?>">Sadece Öne Çıkanlar</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Destinasyonlar <a href="<?= ADMIN_URL ?>/destinations.php" class="alert-link">Destinasyon Yönetimi</a>'nden çekilir. Her destinasyonun çevirisi orada düzenlenir.
                        </div>
                    </div>
                </div>

                <?php break; case 'featured_tours': ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-sm">Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Alt Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Gösterilecek Sayı</label>
                        <input type="number" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][limit]" class="form-control" min="1" max="12"
                               value="<?= (int)getSectionSetting($sectionTrans, $section, $sectionId, $code, 'limit', 6) ?>">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][show_featured_only]" class="form-check-input" id="tour_feat_<?= $sectionId ?>_<?= e($code) ?>" value="1"
                                <?= getSectionSetting($sectionTrans, $section, $sectionId, $code, 'show_featured_only', 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tour_feat_<?= $sectionId ?>_<?= e($code) ?>">Sadece Öne Çıkanlar</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][show_view_all]" class="form-check-input" id="tour_va_<?= $sectionId ?>_<?= e($code) ?>" value="1"
                                <?= getSectionSetting($sectionTrans, $section, $sectionId, $code, 'show_view_all', 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tour_va_<?= $sectionId ?>_<?= e($code) ?>">Tümünü Gör Butonu</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Tümünü Gör Linki</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][view_all_url]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'view_all_url', '/turlar')) ?>">
                    </div>
                </div>

                <?php break; case 'testimonials': ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-sm">Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Alt Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Gösterilecek Sayı</label>
                        <input type="number" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][limit]" class="form-control" min="1" max="20"
                               value="<?= (int)getSectionSetting($sectionTrans, $section, $sectionId, $code, 'limit', 5) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Otomatik Geçiş (ms)</label>
                        <input type="number" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][autoplay_delay]" class="form-control" step="500"
                               value="<?= (int)getSectionSetting($sectionTrans, $section, $sectionId, $code, 'autoplay_delay', 5000) ?>">
                    </div>
                </div>

                <?php break; case 'cta': ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-sm">Ana Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm">Rozet Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label-sm">Açıklama</label>
                        <textarea name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][content]" class="form-control" rows="2"><?= e(strip_tags($content)) ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 1 Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button1_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button1_text', 'Explore Now')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 1 Linki</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button1_url]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button1_url', '/destinasyonlar')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 2 Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button2_text]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button2_text', 'View Deals')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-sm">Buton 2 Linki</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][button2_url]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'button2_url', '/turlar')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm">İletişim Metni</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][contact_label]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'contact_label', 'Need help choosing?')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm">Telefon</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][settings][phone]" class="form-control"
                               value="<?= e(getSectionSetting($sectionTrans, $section, $sectionId, $code, 'phone', '')) ?>">
                    </div>
                </div>

                <?php break; default: ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-sm">Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][title]" class="form-control" value="<?= e($title) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm">Alt Başlık</label>
                        <input type="text" name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][subtitle]" class="form-control" value="<?= e($subtitle) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label-sm">İçerik</label>
                        <textarea name="sections[<?= $sectionId ?>][translations][<?= e($code) ?>][content]" class="form-control" rows="4"><?= e($content) ?></textarea>
                    </div>
                </div>
                <?php endswitch; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg me-2"></i>Tüm Değişiklikleri Kaydet
        </button>
    </div>
</form>

<script>
// Section accordion
function toggleSection(header) {
    header.closest('.section-card').classList.toggle('expanded');
}
// İlk section açık
document.querySelector('.section-card')?.classList.add('expanded');

// Toggle switch label güncelleme
document.querySelectorAll('.toggle-switch input').forEach(function(input) {
    input.addEventListener('change', function() {
        var label = this.closest('.section-header').querySelector('.toggle-label');
        if (label) label.textContent = this.checked ? 'Görünür' : 'Gizli';
        this.closest('.section-card').classList.toggle('opacity-75', !this.checked);
    });
});

// Dil tab switch
document.querySelectorAll('.lang-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var lang = this.dataset.lang;
        // Tüm tab butonları
        document.querySelectorAll('.lang-tab-btn').forEach(function(b) {
            b.classList.toggle('active', b.dataset.lang === lang);
        });
        // Tüm pane'ler (SEO + section'lar)
        document.querySelectorAll('.lang-pane').forEach(function(p) {
            p.classList.toggle('active', p.dataset.langPane === lang);
        });
    });
});

// Karakter sayacı
function updateCharCount(input) {
    var max = parseInt(input.dataset.max) || 100;
    var len = input.value.length;
    var counter = input.parentElement.querySelector('[data-for-input]');
    if (counter) {
        counter.textContent = len + '/' + max + ' karakter';
        counter.style.color = len > max ? '#fca5a5' : '';
    }
}
document.querySelectorAll('.char-counter').forEach(function(input) {
    updateCharCount(input);
    input.addEventListener('input', function() { updateCharCount(this); });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
