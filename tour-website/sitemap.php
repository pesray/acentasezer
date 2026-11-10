<?php
/**
 * Dinamik XML site haritası — tüm aktif diller, hreflang + x-default, lastmod
 */

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$db = getDB();
$langCodes = array_column(getActiveLanguages(), 'code');
if (!$langCodes) $langCodes = ['tr'];
$defLang = sl_default_lang_code();
$xLang = in_array('en', $langCodes, true) ? 'en' : $defLang;

function smX($s) {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * $urls: [lang => url]  (yalnızca var olan dil sürümleri)
 */
function smNode($urls, $xDefault, $priority, $freq, $lastmod = null) {
    foreach ($urls as $lang => $loc) {
        echo "  <url>\n    <loc>" . smX($loc) . "</loc>\n";
        if ($lastmod) echo "    <lastmod>" . smX(date('Y-m-d', strtotime($lastmod))) . "</lastmod>\n";
        foreach ($urls as $alt => $href) {
            echo '    <xhtml:link rel="alternate" hreflang="' . smX($alt) . '" href="' . smX($href) . "\" />\n";
        }
        if ($xDefault) echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . smX($xDefault) . "\" />\n";
        echo "    <changefreq>" . smX($freq) . "</changefreq>\n    <priority>" . smX($priority) . "</priority>\n  </url>\n";
    }
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

try {
    // 1) Anasayfa
    $u = [];
    foreach ($langCodes as $l) $u[$l] = langUrl('', $l);
    smNode($u, SITE_URL . '/', '1.0', 'daily', date('Y-m-d'));

    // 2) Liste sayfaları
    $hasTours = (int)$db->query("SELECT COUNT(*) FROM tours WHERE status = 'published'")->fetchColumn() > 0;
    $hasPosts = (int)$db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn() > 0;
    $lists = ['destinations' => ['0.9', 'daily'], 'faq' => ['0.6', 'monthly'], 'contact' => ['0.5', 'yearly']];
    if ($hasPosts) $lists['blog'] = ['0.6', 'weekly'];
    if ($hasTours) $lists['tours'] = ['0.6', 'weekly'];
    foreach ($lists as $key => $cfg) {
        $u = [];
        foreach ($langCodes as $l) $u[$l] = langUrl(getPageSlugByKey($key, $l), $l);
        smNode($u, $u[$xLang] ?? reset($u), $cfg[0], $cfg[1]);
    }

    // 3) Transfer (bölge) sayfaları — havalimanı kaydı hariç
    $dests = $db->query("SELECT id, title, slug, updated_at FROM destinations WHERE status = 'published' ORDER BY sort_order, id")->fetchAll();
    $dt = [];
    foreach ($db->query("SELECT destination_id, language_code, slug FROM destination_translations")->fetchAll() as $r) {
        if (trim((string)$r['slug']) !== '') $dt[(int)$r['destination_id']][$r['language_code']] = $r['slug'];
    }
    foreach ($dests as $d) {
        if (sl_is_airport_title($d['title'] . ' ' . $d['slug'])) continue;
        $u = [];
        foreach ($langCodes as $l) {
            $u[$l] = langUrl(getPageSlugByKey('destinations', $l) . '/' . ($dt[(int)$d['id']][$l] ?? $d['slug']), $l);
        }
        smNode($u, $u[$xLang] ?? reset($u), '0.9', 'weekly', $d['updated_at']);
    }

    // 4) Turlar
    if ($hasTours) {
        $tt = [];
        foreach ($db->query("SELECT tour_id, language_code, slug FROM tour_translations")->fetchAll() as $r) {
            if (trim((string)$r['slug']) !== '') $tt[(int)$r['tour_id']][$r['language_code']] = $r['slug'];
        }
        foreach ($db->query("SELECT id, slug, updated_at FROM tours WHERE status = 'published'")->fetchAll() as $t) {
            $u = [];
            foreach ($langCodes as $l) $u[$l] = langUrl(getPageSlugByKey('tours', $l) . '/' . ($tt[(int)$t['id']][$l] ?? $t['slug']), $l);
            smNode($u, $u[$xLang] ?? reset($u), '0.6', 'monthly', $t['updated_at']);
        }
    }

    // 5) Blog yazıları — yalnızca çevirisi olan diller (+ varsayılan dil)
    if ($hasPosts) {
        $bt = [];
        foreach ($db->query("SELECT post_id, language_code, slug, title FROM blog_post_translations")->fetchAll() as $r) {
            if (trim((string)$r['title']) !== '') $bt[(int)$r['post_id']][$r['language_code']] = trim((string)$r['slug']);
        }
        foreach ($db->query("SELECT id, slug, updated_at FROM blog_posts WHERE status = 'published'")->fetchAll() as $p) {
            $u = [];
            foreach ($langCodes as $l) {
                if (!isset($bt[(int)$p['id']][$l]) && $l !== $defLang) continue;
                $s = ($bt[(int)$p['id']][$l] ?? '') !== '' ? $bt[(int)$p['id']][$l] : $p['slug'];
                $u[$l] = langUrl(getPageSlugByKey('blog', $l) . '/' . $s, $l);
            }
            if ($u) smNode($u, $u[$xLang] ?? reset($u), '0.6', 'monthly', $p['updated_at']);
        }
    }

    // 6) Genel sayfalar (KVKK, iptal koşulları vb.) — çevirisi olan diller (+ varsayılan dil)
    $pt = [];
    foreach ($db->query("SELECT page_id, language_code, slug, title FROM page_translations")->fetchAll() as $r) {
        if (trim((string)$r['slug']) !== '' && trim((string)$r['title']) !== '') $pt[(int)$r['page_id']][$r['language_code']] = $r['slug'];
    }
    foreach ($db->query("SELECT id, slug, updated_at FROM pages WHERE status = 'published' AND is_homepage = 0")->fetchAll() as $p) {
        $u = [];
        foreach ($langCodes as $l) {
            if (!isset($pt[(int)$p['id']][$l]) && $l !== $defLang) continue;
            $u[$l] = langUrl($pt[(int)$p['id']][$l] ?? $p['slug'], $l);
        }
        if ($u) smNode($u, $u[$xLang] ?? reset($u), '0.3', 'yearly', $p['updated_at']);
    }
} catch (Exception $e) {
    echo "  <!-- sitemap error -->\n";
}

echo "</urlset>\n";
