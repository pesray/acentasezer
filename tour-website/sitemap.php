<?php
/**
 * Dynamic XML Sitemap Generator
 */

// XML header
header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/config/config.php';

$db = getDB();

// Get active languages
$languages = getActiveLanguages();
$langCodes = array_column($languages, 'code');

// Helper to output url XML node
function writeUrlNode($loc, $alternates, $priority = '0.5', $changefreq = 'weekly') {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    foreach ($alternates as $lang => $href) {
        echo "    <xhtml:link rel=\"alternate\" hreflang=\"" . htmlspecialchars($lang, ENT_XML1) . "\" href=\"" . htmlspecialchars($href, ENT_XML1) . "\" />\n";
    }
    echo "    <changefreq>" . htmlspecialchars($changefreq, ENT_XML1) . "</changefreq>\n";
    echo "    <priority>" . htmlspecialchars($priority, ENT_XML1) . "</priority>\n";
    echo "  </url>\n";
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
echo "        xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

try {
    // ----------------------------------------------------
    // 1. Homepage URL Mapping
    // ----------------------------------------------------
    foreach ($langCodes as $lang) {
        $loc = langUrl('', $lang);
        $alternates = [];
        foreach ($langCodes as $altLang) {
            $alternates[$altLang] = langUrl('', $altLang);
        }
        writeUrlNode($loc, $alternates, '1.0', 'daily');
    }

    // ----------------------------------------------------
    // 2. Listing Pages URL Mapping (tours, destinations, blog, etc.)
    // ----------------------------------------------------
    $corePages = ['tours', 'destinations', 'blog', 'gallery', 'faq', 'contact', 'booking'];
    
    // Cache all slugs across all languages
    $slugMap = [];
    foreach ($corePages as $pageKey) {
        foreach ($langCodes as $lang) {
            $slugMap[$pageKey][$lang] = getPageSlugByKey($pageKey, $lang);
        }
    }

    foreach ($corePages as $pageKey) {
        foreach ($langCodes as $lang) {
            $currentSlug = $slugMap[$pageKey][$lang];
            $loc = langUrl($currentSlug, $lang);
            
            $alternates = [];
            foreach ($langCodes as $altLang) {
                $alternates[$altLang] = langUrl($slugMap[$pageKey][$altLang], $altLang);
            }
            writeUrlNode($loc, $alternates, '0.8', 'daily');
        }
    }

    // ----------------------------------------------------
    // 3. Destinations Detail Pages URL Mapping
    // ----------------------------------------------------
    $stmt = $db->query("SELECT id, slug FROM destinations WHERE status = 'published'");
    $destinations = $stmt->fetchAll();
    
    // Pre-fetch all translations
    $destTrans = [];
    if (!empty($destinations)) {
        $transStmt = $db->query("SELECT destination_id, language_code, slug FROM destination_translations");
        while ($row = $transStmt->fetch()) {
            $destTrans[$row['destination_id']][$row['language_code']] = $row['slug'];
        }
    }

    foreach ($destinations as $d) {
        $dId = $d['id'];
        
        // Build slug maps for each lang
        $dSlugs = [];
        foreach ($langCodes as $lang) {
            $dSlugs[$lang] = $destTrans[$dId][$lang] ?? $d['slug'];
        }

        foreach ($langCodes as $lang) {
            $prefix = $slugMap['destinations'][$lang];
            $loc = langUrl($prefix . '/' . $dSlugs[$lang], $lang);

            $alternates = [];
            foreach ($langCodes as $altLang) {
                $altPrefix = $slugMap['destinations'][$altLang];
                $alternates[$altLang] = langUrl($altPrefix . '/' . $dSlugs[$altLang], $altLang);
            }
            writeUrlNode($loc, $alternates, '0.7', 'weekly');
        }
    }

    // ----------------------------------------------------
    // 4. Tours Detail Pages URL Mapping
    // ----------------------------------------------------
    $stmt = $db->query("SELECT id, slug FROM tours WHERE status = 'published'");
    $tours = $stmt->fetchAll();

    // Pre-fetch all translations
    $tourTrans = [];
    if (!empty($tours)) {
        $transStmt = $db->query("SELECT tour_id, language_code, slug FROM tour_translations");
        while ($row = $transStmt->fetch()) {
            $tourTrans[$row['tour_id']][$row['language_code']] = $row['slug'];
        }
    }

    foreach ($tours as $t) {
        $tId = $t['id'];

        $tSlugs = [];
        foreach ($langCodes as $lang) {
            $tSlugs[$lang] = $tourTrans[$tId][$lang] ?? $t['slug'];
        }

        foreach ($langCodes as $lang) {
            $prefix = $slugMap['tours'][$lang];
            $loc = langUrl($prefix . '/' . $tSlugs[$lang], $lang);

            $alternates = [];
            foreach ($langCodes as $altLang) {
                $altPrefix = $slugMap['tours'][$altLang];
                $alternates[$altLang] = langUrl($altPrefix . '/' . $tSlugs[$altLang], $altLang);
            }
            writeUrlNode($loc, $alternates, '0.7', 'weekly');
        }
    }

    // ----------------------------------------------------
    // 5. Blog Posts Detail Pages URL Mapping
    // ----------------------------------------------------
    $stmt = $db->query("SELECT id, slug FROM blog_posts WHERE status = 'published'");
    $blogPosts = $stmt->fetchAll();

    // Pre-fetch all translations
    $blogTrans = [];
    if (!empty($blogPosts)) {
        $transStmt = $db->query("SELECT post_id, language_code, slug FROM blog_post_translations");
        while ($row = $transStmt->fetch()) {
            $blogTrans[$row['post_id']][$row['language_code']] = $row['slug'];
        }
    }

    foreach ($blogPosts as $bp) {
        $bpId = $bp['id'];

        $bpSlugs = [];
        foreach ($langCodes as $lang) {
            $bpSlugs[$lang] = $blogTrans[$bpId][$lang] ?? $bp['slug'];
        }

        foreach ($langCodes as $lang) {
            $prefix = $slugMap['blog'][$lang];
            $loc = langUrl($prefix . '/' . $bpSlugs[$lang], $lang);

            $alternates = [];
            foreach ($langCodes as $altLang) {
                $altPrefix = $slugMap['blog'][$altLang];
                $alternates[$altLang] = langUrl($altPrefix . '/' . $bpSlugs[$altLang], $altLang);
            }
            writeUrlNode($loc, $alternates, '0.6', 'weekly');
        }
    }

    // ----------------------------------------------------
    // 6. General Pages URL Mapping (excluding homepage)
    // ----------------------------------------------------
    $stmt = $db->query("SELECT id, slug FROM pages WHERE status = 'published' AND is_homepage = 0");
    $pages = $stmt->fetchAll();

    // Pre-fetch all translations
    $pageTrans = [];
    if (!empty($pages)) {
        $transStmt = $db->query("SELECT page_id, language_code, slug FROM page_translations");
        while ($row = $transStmt->fetch()) {
            $pageTrans[$row['page_id']][$row['language_code']] = $row['slug'];
        }
    }

    foreach ($pages as $p) {
        $pId = $p['id'];

        $pSlugs = [];
        foreach ($langCodes as $lang) {
            $pSlugs[$lang] = $pageTrans[$pId][$lang] ?? $p['slug'];
        }

        foreach ($langCodes as $lang) {
            $loc = langUrl($pSlugs[$lang], $lang);

            $alternates = [];
            foreach ($langCodes as $altLang) {
                $alternates[$altLang] = langUrl($pSlugs[$altLang], $altLang);
            }
            writeUrlNode($loc, $alternates, '0.6', 'monthly');
        }
    }

} catch (Exception $e) {
    // Handle error gracefully inside XML comment if needed
    echo "  <!-- Error generating full sitemap: " . htmlspecialchars($e->getMessage(), ENT_XML1) . " -->\n";
}

echo "</urlset>\n";
