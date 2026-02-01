<?php
/**
 * Section Helper Functions
 * Dinamik section yönetimi için yardımcı fonksiyonlar
 */

/**
 * Sayfa section'larını getir
 */
function getPageSections($pageId) {
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $pageId . '_' . $lang;
    
    // Cache'den döndür
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    try {
        $db = getDB();
        // Tek sorguda section ve çevirileri al (N+1 query problemi çözümü)
        $stmt = $db->prepare("
            SELECT s.*, 
                   COALESCE(st.title, s.title) as title,
                   COALESCE(st.subtitle, s.subtitle) as subtitle,
                   COALESCE(st.content, s.content) as content
            FROM sections s
            LEFT JOIN section_translations st ON s.id = st.section_id AND st.language_code = ?
            WHERE s.page_id = ? AND s.is_active = 1
            ORDER BY s.sort_order
        ");
        $stmt->execute([$lang, $pageId]);
        $sections = $stmt->fetchAll();
        
        $cache[$cacheKey] = $sections;
        return $sections;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Section'ı render et
 */
function renderSection($section) {
    $type = $section['section_type'];
    $templateFile = INCLUDES_PATH . 'sections/' . $type . '.php';
    
    // Section verilerini global yap
    $GLOBALS['current_section'] = $section;
    $settings = json_decode($section['settings'] ?? '{}', true) ?: [];
    $GLOBALS['section_settings'] = $settings;
    
    if (file_exists($templateFile)) {
        include $templateFile;
    }
}

/**
 * Tüm features'ları getir (section filtresi olmadan)
 */
function getAllFeatures() {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT f.*, 
                   COALESCE(ft.title, f.title) as title,
                   COALESCE(ft.description, f.description) as description
            FROM features f
            LEFT JOIN feature_translations ft ON f.id = ft.feature_id AND ft.language_code = ?
            WHERE f.is_active = 1
            ORDER BY f.sort_order
        ");
        $stmt->execute([$lang]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Hero section verilerini getir
 */
function getHeroData() {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT sl.*, 
                   COALESCE(slt.title, sl.title) as title,
                   COALESCE(slt.subtitle, sl.subtitle) as subtitle,
                   COALESCE(slt.button_text, sl.button_text) as button_text,
                   COALESCE(slt.button_url, sl.button_url) as button_url,
                   COALESCE(slt.button2_text, sl.button2_text) as button2_text,
                   COALESCE(slt.button2_url, sl.button2_url) as button2_url
            FROM sliders sl
            LEFT JOIN slider_translations slt ON sl.id = slt.slider_id AND slt.language_code = ?
            WHERE sl.location = 'home' AND sl.is_active = 1
            AND (sl.start_date IS NULL OR sl.start_date <= CURDATE())
            AND (sl.end_date IS NULL OR sl.end_date >= CURDATE())
            ORDER BY sl.sort_order
            LIMIT 1
        ");
        $stmt->execute([$lang]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Öne çıkan destinasyonları getir
 */
function getFeaturedDestinations($limit = 4) {
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $lang . '_' . $limit;
    
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT d.*, 
                   COALESCE(dt.title, d.title) as title,
                   COALESCE(dt.slug, d.slug) as slug,
                   COALESCE(dt.description, d.description) as description
            FROM destinations d
            LEFT JOIN destination_translations dt ON d.id = dt.destination_id AND dt.language_code = ?
            WHERE d.status = 'published' AND d.is_featured = 1
            ORDER BY d.sort_order
            LIMIT ?
        ");
        $stmt->execute([$lang, $limit]);
        $cache[$cacheKey] = $stmt->fetchAll();
        return $cache[$cacheKey];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Öne çıkan turları getir
 */
function getFeaturedTours($limit = 6) {
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $lang . '_' . $limit;
    
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT t.*, 
                   COALESCE(tt.title, t.title) as title,
                   COALESCE(tt.slug, t.slug) as slug,
                   COALESCE(tt.description, t.description) as description,
                   COALESCE(tt.highlights, t.highlights) as highlights
            FROM tours t
            LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
            WHERE t.status = 'published' AND t.is_featured = 1
            ORDER BY t.sort_order
            LIMIT ?
        ");
        $stmt->execute([$lang, $limit]);
        $cache[$cacheKey] = $stmt->fetchAll();
        return $cache[$cacheKey];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Testimonial'ları getir
 */
function getTestimonials($limit = 6) {
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $lang . '_' . $limit;
    
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT t.*, 
                   COALESCE(tt.content, t.content) as content,
                   COALESCE(tt.customer_title, t.customer_title) as customer_title
            FROM testimonials t
            LEFT JOIN testimonial_translations tt ON t.id = tt.testimonial_id AND tt.language_code = ?
            WHERE t.is_approved = 1 AND t.is_featured = 1
            ORDER BY t.sort_order
            LIMIT ?
        ");
        $stmt->execute([$lang, $limit]);
        $cache[$cacheKey] = $stmt->fetchAll();
        return $cache[$cacheKey];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Features (Neden Biz) getir
 */
function getFeatures($section = 'why_us') {
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $lang . '_' . $section;
    
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT f.*, 
                   COALESCE(ft.title, f.title) as title,
                   COALESCE(ft.description, f.description) as description
            FROM features f
            LEFT JOIN feature_translations ft ON f.id = ft.feature_id AND ft.language_code = ?
            WHERE f.section = ? AND f.is_active = 1
            ORDER BY f.sort_order
        ");
        $stmt->execute([$lang, $section]);
        $cache[$cacheKey] = $stmt->fetchAll();
        return $cache[$cacheKey];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Blog yazılarını getir
 */
function getBlogPosts($limit = 3, $categoryId = null) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        $sql = "
            SELECT bp.*, 
                   COALESCE(bpt.title, bp.title) as title,
                   COALESCE(bpt.slug, bp.slug) as slug,
                   COALESCE(bpt.excerpt, bp.excerpt) as excerpt,
                   u.full_name as author_name
            FROM blog_posts bp
            LEFT JOIN blog_post_translations bpt ON bp.id = bpt.post_id AND bpt.language_code = ?
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published'
        ";
        $params = [$lang];
        
        if ($categoryId) {
            $sql .= " AND bp.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY bp.published_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * FAQ'ları getir
 */
function getFaqs($categoryId = null) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        $sql = "
            SELECT f.*, 
                   COALESCE(ft.question, f.question) as question,
                   COALESCE(ft.answer, f.answer) as answer
            FROM faqs f
            LEFT JOIN faq_translations ft ON f.id = ft.faq_id AND ft.language_code = ?
            WHERE f.is_active = 1
        ";
        $params = [$lang];
        
        if ($categoryId) {
            $sql .= " AND f.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY f.sort_order";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Galeri öğelerini getir
 */
function getGalleryItems($limit = 12, $categoryId = null) {
    try {
        $db = getDB();
        $sql = "SELECT * FROM gallery WHERE 1=1";
        $params = [];
        
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY sort_order LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Sayfa bilgisini slug'a göre getir
 */
function getPageBySlug($slug) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        
        // Önce çevirilerde ara
        $stmt = $db->prepare("
            SELECT p.*, pt.title, pt.slug, pt.content, pt.excerpt,
                   pt.meta_title, pt.meta_description, pt.meta_keywords
            FROM pages p
            JOIN page_translations pt ON p.id = pt.page_id
            WHERE pt.slug = ? AND pt.language_code = ? AND p.status = 'published'
        ");
        $stmt->execute([$slug, $lang]);
        $page = $stmt->fetch();
        
        if (!$page) {
            // Ana tabloda ara
            $stmt = $db->prepare("
                SELECT * FROM pages WHERE slug = ? AND status = 'published'
            ");
            $stmt->execute([$slug]);
            $page = $stmt->fetch();
        }
        
        return $page;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Tur bilgisini slug'a göre getir
 */
function getTourBySlug($slug) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT t.*, 
                   COALESCE(tt.title, t.title) as title,
                   COALESCE(tt.slug, t.slug) as slug,
                   COALESCE(tt.description, t.description) as description,
                   COALESCE(tt.content, t.content) as content,
                   COALESCE(tt.highlights, t.highlights) as highlights,
                   COALESCE(tt.included, t.included) as included,
                   COALESCE(tt.excluded, t.excluded) as excluded,
                   COALESCE(tt.itinerary, t.itinerary) as itinerary,
                   COALESCE(tt.meta_title, t.meta_title) as meta_title,
                   COALESCE(tt.meta_description, t.meta_description) as meta_description
            FROM tours t
            LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
            WHERE (tt.slug = ? OR t.slug = ?) AND t.status = 'published'
        ");
        $stmt->execute([$lang, $slug, $slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Destinasyon bilgisini slug'a göre getir
 */
function getDestinationBySlug($slug) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT d.*, 
                   COALESCE(dt.title, d.title) as title,
                   COALESCE(dt.slug, d.slug) as slug,
                   COALESCE(dt.from_location, '') as from_location,
                   COALESCE(dt.to_location, '') as to_location,
                   COALESCE(dt.description, d.description) as description,
                   COALESCE(dt.content, d.content) as content,
                   COALESCE(dt.meta_title, d.meta_title) as meta_title,
                   COALESCE(dt.meta_description, d.meta_description) as meta_description
            FROM destinations d
            LEFT JOIN destination_translations dt ON d.id = dt.destination_id AND dt.language_code = ?
            WHERE (dt.slug = ? OR d.slug = ?) AND d.status = 'published'
        ");
        $stmt->execute([$lang, $slug, $slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}
