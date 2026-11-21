<?php
/**
 * Sunline teması — çekirdek yardımcılar
 *
 * Veri kaynakları:
 *  - Bölgeler/fiyatlar: destinations + destination_translations + destination_vehicles (Tur Yönetimi > Transferler)
 *  - Araçlar: vehicles + vehicle_services (Tur Yönetimi > Araçlar)
 *  - Tema içeriği: sections (section_type = sun_*) + section_translations (Admin > Anasayfa & Tema)
 *  - Arayüz metinleri: translations (grup "sunline") → yoksa defaults.php
 *
 * PHP 7.4 uyumlu.
 */

// Not: Bu dosya her zaman require_once ile yüklenmelidir.
if (!defined('SL_ASSET_VER')) define('SL_ASSET_VER', '1.2.6');

require_once __DIR__ . '/defaults.php';

/* ------------------------------------------------------------------ */
/*  Dil & metin                                                        */
/* ------------------------------------------------------------------ */

function sl_lang() {
    return getCurrentLang();
}

function sl_default_lang_code() {
    static $code = null;
    if ($code === null) {
        $def = getDefaultLanguage();
        $code = !empty($def['code']) ? $def['code'] : 'tr';
    }
    return $code;
}

function sl_db_strings($lang) {
    static $cache = [];
    if (isset($cache[$lang])) return $cache[$lang];
    $out = [];
    try {
        $st = getDB()->prepare("SELECT trans_key, trans_value FROM translations WHERE language_code = ? AND trans_group = 'sunline'");
        $st->execute([$lang]);
        foreach ($st->fetchAll() as $r) {
            if ($r['trans_value'] !== null && trim($r['trans_value']) !== '') {
                $out[$r['trans_key']] = $r['trans_value'];
            }
        }
    } catch (Exception $e) {}
    $cache[$lang] = $out;
    return $out;
}

/** Arayüz metni ({degisken} yer tutucuları desteklenir) */
function sl_t($key, $vars = [], $lang = null) {
    $lang = $lang ?: sl_lang();
    $db = sl_db_strings($lang);
    if (isset($db[$key])) {
        $v = $db[$key];
    } else {
        $d = sl_default_strings();
        if (isset($d[$lang][$key])) $v = $d[$lang][$key];
        elseif (isset($d['en'][$key])) $v = $d['en'][$key];
        else $v = $key;
    }
    if (!empty($vars)) {
        foreach ($vars as $k => $val) {
            $v = str_replace('{' . $k . '}', (string)$val, $v);
        }
    }
    return $v;
}

/** HTML'e güvenli basılmış metin */
function sl_e($key, $vars = []) {
    return e(sl_t($key, $vars));
}

/* ------------------------------------------------------------------ */
/*  Tema bölümleri (sections.section_type = sun_*)                     */
/* ------------------------------------------------------------------ */

function sl_section_types() {
    return ['global', 'hero', 'regions', 'info', 'guests', 'blog', 'faq'];
}

function sl_home_page_id() {
    static $id = null;
    if ($id !== null) return $id;
    $id = 1;
    try {
        $row = getDB()->query("SELECT id FROM pages WHERE is_homepage = 1 LIMIT 1")->fetch();
        if ($row) $id = (int)$row['id'];
    } catch (Exception $e) {}
    return $id;
}

function sl_section_rows() {
    static $rows = null;
    if ($rows !== null) return $rows;
    $rows = [];
    try {
        $db = getDB();
        $types = array_map(function ($t) { return 'sun_' . $t; }, sl_section_types());
        $ph = implode(',', array_fill(0, count($types), '?'));
        $st = $db->prepare("SELECT * FROM sections WHERE page_id = ? AND section_type IN ($ph) ORDER BY id");
        $st->execute(array_merge([sl_home_page_id()], $types));
        $ids = [];
        foreach ($st->fetchAll() as $r) {
            if (isset($rows[$r['section_type']])) continue; // aynı tipten ilk kayıt geçerli
            $r['trans'] = [];
            $rows[$r['section_type']] = $r;
            $ids[(int)$r['id']] = $r['section_type'];
        }
        if ($ids) {
            $ph2 = implode(',', array_fill(0, count($ids), '?'));
            $st2 = $db->prepare("SELECT * FROM section_translations WHERE section_id IN ($ph2)");
            $st2->execute(array_keys($ids));
            foreach ($st2->fetchAll() as $t) {
                $type = $ids[(int)$t['section_id']] ?? null;
                if ($type) $rows[$type]['trans'][$t['language_code']] = $t;
            }
        }
    } catch (Exception $e) {}
    return $rows;
}

/**
 * Bölüm verisi: varsayılanlar + ortak ayarlar + aktif dilin ayarları
 */
function sl_section($key, $lang = null) {
    static $cache = [];
    $lang = $lang ?: sl_lang();
    $ck = $key . '|' . $lang;
    if (isset($cache[$ck])) return $cache[$ck];

    $rows = sl_section_rows();
    $row = $rows['sun_' . $key] ?? null;
    $defBase = sl_default_base();
    $defBase = $defBase[$key] ?? [];
    $defLang = sl_default_content($lang);
    $defLang = $defLang[$key] ?? [];

    $base = [];
    $trans = [];
    if ($row) {
        $base = json_decode((string)($row['settings'] ?? ''), true);
        if (!is_array($base)) $base = [];
        if (isset($row['trans'][$lang]['settings'])) {
            $trans = json_decode((string)$row['trans'][$lang]['settings'], true);
            if (!is_array($trans)) $trans = [];
        }
    }
    $data = array_merge($defBase, $base, $defLang, $trans);
    $data['_active'] = $row ? ((int)$row['is_active'] === 1) : true;
    $data['_id'] = $row ? (int)$row['id'] : 0;
    $cache[$ck] = $data;
    return $data;
}

function sl_is_home() {
    return !empty($GLOBALS['sl_is_home']);
}

/* ------------------------------------------------------------------ */
/*  Tema renkleri (temadaki Tweaks panelinin PHP karşılığı)            */
/* ------------------------------------------------------------------ */

/** Claude Design "Tweaks" panelindeki hazır temalar (+ indirilen varsayılan: Krem Siyah) */
function sl_theme_presets() {
    return [
        'gece'       => ['n' => 'Gece Altın', 'bg' => '#0b0b0c', 'card' => '#1a1a1a', 'accent' => '#FFD700'],
        'lacivert'   => ['n' => 'Lacivert',   'bg' => '#0a1224', 'card' => '#131e38', 'accent' => '#f2c94c'],
        'bordo'      => ['n' => 'Bordo',      'bg' => '#14080c', 'card' => '#251219', 'accent' => '#e9c27a'],
        'turkuaz'    => ['n' => 'Akdeniz',    'bg' => '#08161a', 'card' => '#11252b', 'accent' => '#3fd6c6'],
        'zumrut'     => ['n' => 'Zümrüt',     'bg' => '#07130e', 'card' => '#11231a', 'accent' => '#d8b45a'],
        'krem'       => ['n' => 'Krem',       'bg' => '#f4efe6', 'card' => '#ffffff', 'accent' => '#b8860b'],
        'krem_siyah' => ['n' => 'Krem Siyah', 'bg' => '#f4efe6', 'card' => '#1a1a1a', 'accent' => '#FFD700'],
    ];
}

/** Tweaks panelindeki renk örnekleri (birebir) */
function sl_theme_swatches() {
    return [
        'bg' => ['#0b0b0c', '#111827', '#0a1224', '#14080c', '#08161a', '#07130e', '#1c1917', '#f4efe6', '#f7f7f5', '#eef3f6'],
        'card' => ['#1a1a1a', '#222226', '#131e38', '#251219', '#11252b', '#11231a', '#ffffff', '#faf6ee'],
        'accent' => ['#FFD700', '#e8c547', '#f2c94c', '#e9c27a', '#d8b45a', '#3fd6c6', '#ff8a3d', '#b8860b'],
    ];
}

function sl_hex($h, $fallback) {
    $h = trim((string)$h);
    if (preg_match('/^#?([0-9a-f]{6})$/i', $h, $m)) return '#' . strtolower($m[1]);
    if (preg_match('/^#?([0-9a-f])([0-9a-f])([0-9a-f])$/i', $h, $m)) return '#' . strtolower($m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3]);
    return $fallback;
}

function sl_rgb($h) {
    $h = ltrim($h, '#');
    return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
}

function sl_lum($h) {
    list($r, $g, $b) = sl_rgb($h);
    return .2126 * $r / 255 + .7152 * $g / 255 + .0722 * $b / 255;
}

function sl_mix($h, $amt) {
    $t = $amt > 0 ? 255 : 0;
    $a = abs($amt);
    $out = '#';
    foreach (sl_rgb($h) as $v) {
        $out .= str_pad(dechex((int)round($v + ($t - $v) * $a)), 2, '0', STR_PAD_LEFT);
    }
    return $out;
}

/** WCAG göreli parlaklık */
function sl_rel_lum($h) {
    $out = [];
    foreach (sl_rgb($h) as $v) {
        $c = $v / 255;
        $out[] = $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    }
    return 0.2126 * $out[0] + 0.7152 * $out[1] + 0.0722 * $out[2];
}

function sl_contrast($a, $b) {
    $l1 = sl_rel_lum($a);
    $l2 = sl_rel_lum($b);
    return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
}

/** Vurgu rengini zemine göre okunur hale getirir (gerekirse koyulaştırır/açar) */
function sl_readable($fg, $bg, $min) {
    if (sl_contrast($fg, $bg) >= $min) return $fg;
    $darken = sl_rel_lum($bg) > 0.35;
    for ($s = 0.05; $s <= 0.9; $s += 0.05) {
        $c = sl_mix($fg, $darken ? -$s : $s);
        if (sl_contrast($c, $bg) >= $min) return $c;
    }
    return $darken ? '#141414' : '#ffffff';
}

/** Açık / koyu palet ayarları (Admin > Anasayfa & Tema > Tema Renkleri) */
function sl_theme_config() {
    $g = sl_section('global');
    $p = sl_theme_presets();
    $light = is_array($g['light'] ?? null) ? $g['light'] : [];
    $dark = is_array($g['dark'] ?? null) ? $g['dark'] : [];
    $mode = in_array(($g['mode'] ?? ''), ['auto', 'light', 'dark'], true) ? $g['mode'] : 'auto';
    return [
        'mode' => $mode,
        'toggle' => !isset($g['toggle']) || (int)$g['toggle'] === 1,
        'light' => [
            'bg' => sl_hex($light['bg'] ?? '', $p['krem_siyah']['bg']),
            'card' => sl_hex($light['card'] ?? '', $p['krem_siyah']['card']),
            'accent' => sl_hex($light['accent'] ?? '', '#ffd700'),
        ],
        'dark' => [
            'bg' => sl_hex($dark['bg'] ?? '', $p['gece']['bg']),
            'card' => sl_hex($dark['card'] ?? '', $p['gece']['card']),
            'accent' => sl_hex($dark['accent'] ?? '', '#ffd700'),
        ],
    ];
}

/** Bir paletten CSS değişkenleri (temadaki applyTheme() fonksiyonunun birebir karşılığı + okunabilirlik) */
function sl_palette_vars($pal) {
    $bg = $pal['bg'];
    $card = $pal['card'];
    $acc = $pal['accent'];
    $lb = sl_lum($bg) > .5;
    $lc = sl_lum($card) > .5;
    $bg2 = sl_mix($bg, $lb ? -.04 : .04);
    $text = $lb ? '#141414' : '#ffffff';
    $arrow = rawurlencode($text);
    return [
        '--bg' => $bg,
        '--bg2' => $bg2,
        '--card' => $card,
        '--card2' => sl_mix($card, $lc ? -.05 : .07),
        '--accent' => $acc,
        '--cta1' => sl_mix($acc, -.2),
        '--cta2' => sl_mix($acc, .12),
        '--text' => $text,
        '--muted' => $lb ? '#5a5550' : '#b9b9b9',
        '--line' => $lb ? 'rgba(0,0,0,.1)' : 'rgba(255,255,255,.09)',
        '--red' => $lc ? '#d11a1a' : '#ff4d4d',
        '--ctext' => $lc ? '#141414' : '#ffffff',
        '--cmuted' => $lc ? '#6b6560' : '#b9b9b9',
        '--cline' => $lc ? 'rgba(0,0,0,.12)' : 'rgba(255,255,255,.12)',
        // eklemeler
        '--acc-t' => sl_readable($acc, $bg, 4.5),
        '--acc-h' => $lb ? sl_mix(sl_readable($acc, $bg, 4.5), -.15) : sl_mix($acc, .12),
        '--acc-f' => sl_readable($acc, $bg2, 4.5),
        '--acc-c' => sl_readable($acc, $card, 3),
        '--cs' => $lb ? 'light' : 'dark',
        '--ccs' => $lc ? 'light' : 'dark',
        '--lg-l' => $lb ? 'block' : 'none',
        '--lg-d' => $lb ? 'none' : 'block',
        '--ico-moon' => $lb ? 'inline-block' : 'none',
        '--ico-sun' => $lb ? 'none' : 'inline-block',
        '--close-f' => $lc ? 'none' : 'invert(1) grayscale(100%) brightness(200%)',
        '--bs-form-select-bg-img' => "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='" . $arrow . "' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e\")",
    ];
}

function sl_vars_css($vars) {
    $css = '';
    foreach ($vars as $k => $v) $css .= $k . ':' . $v . ';';
    return $css;
}

/** <head> içine basılan tema değişkenleri — açık/koyu, cihaza göre otomatik */
function sl_theme_style_tag() {
    $cfg = sl_theme_config();
    $L = sl_vars_css(sl_palette_vars($cfg['light']));
    $D = sl_vars_css(sl_palette_vars($cfg['dark']));
    if ($cfg['mode'] === 'light') {
        $css = ':root{' . $L . 'color-scheme:light}';
    } elseif ($cfg['mode'] === 'dark') {
        $css = ':root{' . $D . 'color-scheme:dark}';
    } else {
        $css = ':root{' . $L . 'color-scheme:light}'
             . '@media (prefers-color-scheme: dark){:root:not([data-theme="light"]){' . $D . 'color-scheme:dark}}'
             . ':root[data-theme="dark"]{' . $D . 'color-scheme:dark}';
    }
    return '<style id="sl-theme">' . $css . '</style>';
}

/** Tarayıcı çubuğu rengi ve renk şeması meta etiketleri */
function sl_theme_meta_tags() {
    $cfg = sl_theme_config();
    if ($cfg['mode'] === 'light') {
        return '<meta name="color-scheme" content="light"><meta name="theme-color" content="' . $cfg['light']['bg'] . '">';
    }
    if ($cfg['mode'] === 'dark') {
        return '<meta name="color-scheme" content="dark"><meta name="theme-color" content="' . $cfg['dark']['bg'] . '">';
    }
    return '<meta name="color-scheme" content="light dark">'
        . '<meta name="theme-color" media="(prefers-color-scheme: light)" content="' . $cfg['light']['bg'] . '">'
        . '<meta name="theme-color" media="(prefers-color-scheme: dark)" content="' . $cfg['dark']['bg'] . '">';
}

/** Ziyaretçinin seçimini (açık/koyu) boyamadan önce uygular — yanıp sönmeyi önler */
function sl_theme_boot_script() {
    $cfg = sl_theme_config();
    if ($cfg['mode'] !== 'auto') return '';
    return "<script>(function(){try{var t=localStorage.getItem('sl-theme');if(t==='light'||t==='dark'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();</script>";
}

/** Logo adresleri (açık zemin / koyu zemin) */
function sl_logo_urls() {
    $g = sl_section('global');
    $l = trim((string)($g['logo_light'] ?? ''));
    $d = trim((string)($g['logo_dark'] ?? ''));
    return [
        'light' => $l !== '' ? getMediaUrl($l) : ASSETS_URL . 'img/sunline-logo.webp?v=' . SL_ASSET_VER,
        'dark' => $d !== '' ? getMediaUrl($d) : ASSETS_URL . 'img/sunline-logo-dark.webp?v=' . SL_ASSET_VER,
        'png' => SITE_URL . '/assets/img/sunline-logo-512.png',
    ];
}

/* ------------------------------------------------------------------ */
/*  Metin yardımcıları                                                 */
/* ------------------------------------------------------------------ */

/** Türkçe karakterleri sadeleştirip küçük harfe çevirir (eşleştirme için) */
function sl_norm($s) {
    $s = str_replace(['İ', 'I', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['i', 'i', 's', 'g', 'u', 'o', 'c'], (string)$s);
    $s = mb_strtolower($s, 'UTF-8');
    $s = strtr($s, ['ı' => 'i', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c', 'â' => 'a', 'î' => 'i', 'û' => 'u', 'é' => 'e']);
    return $s;
}

/** "Side Transfer" → "Side", "Трансфер Сиде" → "Сиде" */
function sl_place_name($title) {
    $t = trim((string)$title);
    $p = preg_replace('/\s+(vip\s+)?transfer(i|leri)?\s*$/iu', '', $t);
    $p = preg_replace('/^(vip\s+)?(трансфер|transfer)\s+(в\s+|nach\s+|to\s+)?/iu', '', $p);
    $p = trim($p);
    return $p !== '' ? $p : $t;
}

function sl_translit_ru($s) {
    $s = str_replace(['İ', 'I'], ['i', 'ı'], (string)$s);
    $s = mb_strtolower($s, 'UTF-8');
    $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
    $map = ['a' => 'а', 'b' => 'б', 'c' => 'дж', 'ç' => 'ч', 'd' => 'д', 'e' => 'е', 'f' => 'ф', 'g' => 'г', 'ğ' => '',
        'h' => 'х', 'ı' => 'ы', 'i' => 'и', 'j' => 'ж', 'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о',
        'ö' => 'ё', 'p' => 'п', 'q' => 'к', 'r' => 'р', 's' => 'с', 'ş' => 'ш', 't' => 'т', 'u' => 'у', 'ü' => 'ю',
        'v' => 'в', 'w' => 'в', 'x' => 'кс', 'z' => 'з'];
    $yv = ['a' => 'я', 'u' => 'ю', 'e' => 'е', 'o' => 'ё'];
    $out = '';
    $n = count($chars);
    for ($i = 0; $i < $n; $i++) {
        $c = $chars[$i];
        if ($c === 'y') {
            $next = $chars[$i + 1] ?? '';
            if (isset($yv[$next])) { $out .= $yv[$next]; $i++; continue; }
            $out .= 'й';
            continue;
        }
        $out .= $map[$c] ?? $c;
    }
    return mb_strtoupper(mb_substr($out, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($out, 1, null, 'UTF-8');
}

/** Türkçe yer adının Rusça karşılığı */
function sl_ru_place($place) {
    $map = sl_ru_place_map();
    $words = preg_split('/\s+/u', trim((string)$place));
    $out = [];
    foreach ($words as $w) {
        if ($w === '') continue;
        $k = preg_replace('/[^a-z0-9]/', '', sl_norm($w));
        if (preg_match('/^\(?ayt\)?$/i', $w)) { $out[] = '(AYT)'; continue; }
        $out[] = isset($map[$k]) ? $map[$k] : (preg_match('/[a-z]/i', $k) ? sl_translit_ru($w) : $w);
    }
    return implode(' ', $out);
}

/** Basit metin → güvenli HTML (*vurgu* desteği) */
function sl_rich($text) {
    $h = e((string)$text);
    return preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $h);
}

/** Paragraflara böl (boş satırlar paragraf ayırır) */
function sl_paragraphs($text) {
    $text = trim((string)$text);
    if ($text === '') return '';
    $parts = preg_split('/\n\s*\n/u', $text);
    $html = '';
    foreach ($parts as $p) $html .= '<p>' . nl2br(e(trim($p))) . '</p>';
    return $html;
}

function sl_json($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function sl_abs_url($url) {
    if ($url === '' || $url === null) return '';
    if (preg_match('#^https?://#i', $url)) return $url;
    if (strpos($url, '//') === 0) return 'https:' . $url;
    return SITE_URL . '/' . ltrim($url, '/');
}

/* ------------------------------------------------------------------ */
/*  URL'ler                                                            */
/* ------------------------------------------------------------------ */

function sl_page_url($key, $lang = null) {
    $lang = $lang ?: sl_lang();
    return langUrl(getPageSlugByKey($key, $lang), $lang);
}

/** Temel slug'ı verilen sayfanın (pages tablosu) aktif dildeki adresi; yoksa '' */
function sl_page_url_by_base($baseSlug, $lang = null) {
    static $cache = [];
    $lang = $lang ?: sl_lang();
    $ck = $baseSlug . '|' . $lang;
    if (array_key_exists($ck, $cache)) return $cache[$ck];
    $url = '';
    try {
        $st = getDB()->prepare("
            SELECT p.slug, pt.slug AS tslug FROM pages p
            LEFT JOIN page_translations pt ON pt.page_id = p.id AND pt.language_code = ?
            WHERE p.slug = ? AND p.status = 'published' LIMIT 1
        ");
        $st->execute([$lang, $baseSlug]);
        $row = $st->fetch();
        if ($row) {
            $slug = !empty($row['tslug']) ? $row['tslug'] : $row['slug'];
            $url = langUrl($slug, $lang);
        }
    } catch (Exception $e) {}
    $cache[$ck] = $url;
    return $url;
}

/**
 * Admin'de girilen bağlantıyı çözer.
 * {home} {transfers} {blog} {contact} {faq} {tours} {gallery} {page:slug}, #capa, /yol, https://...
 */
function sl_link($u) {
    $u = trim((string)$u);
    if ($u === '' || $u === '{home}' || $u === '/') return langUrl('');
    if (preg_match('#^(https?:|mailto:|tel:|//|whatsapp:)#i', $u)) return $u;
    if ($u[0] === '#') return (sl_is_home() ? '' : langUrl('')) . $u;
    if (preg_match('/^\{(transfers|destinations|blog|contact|faq|tours|gallery)\}(#[\w-]+)?$/', $u, $m)) {
        $key = $m[1] === 'transfers' ? 'destinations' : $m[1];
        return sl_page_url($key) . ($m[2] ?? '');
    }
    if (preg_match('/^\{page:([a-z0-9\-]+)\}$/i', $u, $m)) {
        return sl_page_url_by_base($m[1]);
    }
    if (preg_match('/^\{home\}(#[\w-]+)$/', $u, $m)) {
        return (sl_is_home() ? '' : langUrl('')) . $m[1];
    }
    return langUrl(ltrim($u, '/'));
}

function sl_whatsapp_number() {
    $g = sl_section('global');
    $n = preg_replace('/\D+/', '', (string)($g['whatsapp'] ?? ''));
    if ($n === '') $n = preg_replace('/\D+/', '', (string)getSetting('contact_whatsapp', ''));
    if ($n === '') $n = preg_replace('/\D+/', '', (string)getSetting('contact_phone', ''));
    if (strpos($n, '00') === 0) $n = substr($n, 2);
    if (strlen($n) === 11 && $n[0] === '0') $n = '90' . substr($n, 1);
    return $n;
}

function sl_wa_link($text = '') {
    $n = sl_whatsapp_number();
    if ($n === '') return '';
    return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/* ------------------------------------------------------------------ */
/*  Ölçüm (Site Ayarları > SEO)                                        */
/* ------------------------------------------------------------------ */

/**
 * Ölçüm kimlikleri. Ayara tam kod yapıştırılsa bile kimlik ayıklanır; geçersizse ''.
 * Hepsi boşsa sitede ölçüm kodu ve çerez onayı kutusu çıkmaz.
 * Çerez onayı: header.php (Consent Mode v2 varsayılanı) + footer.php (kutu) + assets/js/sl-olcum.js
 */
function sl_olcum() {
    static $o = null;
    if ($o !== null) return $o;
    $pick = function ($key, $rx) {
        return preg_match($rx, (string)getSetting($key, ''), $m) ? strtoupper($m[1]) : '';
    };
    $o = [
        'gtm' => $pick('google_tag_manager', '/\b(GTM-[A-Z0-9]{4,12})\b/i'),
        'ga'  => $pick('google_analytics', '/\b(G-[A-Z0-9]{4,15})\b/i'),
        'aw'  => $pick('google_ads_id', '/\b(AW-\d{5,15})\b/i'),
        'ym'  => '',
    ];
    $ym = trim((string)getSetting('yandex_metrika', ''));
    if (preg_match('/^(\d{5,12})$/', $ym, $m) || preg_match('/\bym\(\s*(\d{5,12})/', $ym, $m) || preg_match('#metrika/watch/(\d{5,12})#', $ym, $m)) {
        $o['ym'] = $m[1];
    }
    $o['any'] = ($o['gtm'] . $o['ga'] . $o['aw'] . $o['ym']) !== '';
    return $o;
}

/* ------------------------------------------------------------------ */
/*  Fiyat yardımcıları                                                 */
/* ------------------------------------------------------------------ */

function sl_currency_symbol($c) {
    $map = ['EUR' => '€', 'USD' => '$', 'GBP' => '£', 'TRY' => '₺', 'TL' => '₺', 'RUB' => '₽'];
    $c = strtoupper(trim((string)$c));
    return $map[$c] ?? $c;
}

function sl_money($amount, $currency, $space = false) {
    $lang = sl_lang();
    $amount = (float)$amount;
    $dec = (abs($amount - round($amount)) < 0.001) ? 0 : 2;
    $th = $lang === 'en' ? ',' : ($lang === 'ru' ? ' ' : '.');
    $dp = $lang === 'en' ? '.' : ',';
    $num = number_format($amount, $dec, $dp, $th);
    $cur = strtoupper(trim((string)$currency));
    $sym = sl_currency_symbol($cur);
    if ($cur === 'USD' || $cur === 'GBP') return $sym . $num;
    if (mb_strlen($sym) > 1) return $num . ' ' . $sym;
    return $num . ($space ? ' ' : '') . $sym;
}

function sl_discount_pct() {
    $g = sl_section('global');
    $p = (float)str_replace(',', '.', (string)($g['discount'] ?? 0));
    return max(0, min(90, $p));
}

/** Üstü çizili "eski fiyat" (Admin > Anasayfa & Tema > Kampanya oranı). 0 → gösterilmez */
function sl_old_price($price) {
    $pct = sl_discount_pct();
    $price = (float)$price;
    if ($pct <= 0 || $price <= 0) return 0;
    $raw = round($price * (1 + $pct / 100), 6);
    $step = $price < 100 ? 5 : ($price < 1000 ? 10 : ($price < 10000 ? 100 : 500));
    $old = ceil($raw / $step) * $step;
    if ($old <= $price) $old = $price + $step;
    return $old;
}

/* ------------------------------------------------------------------ */
/*  Para birimi & ödeme (Admin > Anasayfa & Tema > Fiyat & Ödeme)      */
/*  Sitede tek para birimi listelenir; diğerleri ödeme adımında seçilir */
/* ------------------------------------------------------------------ */

/** Panelde fiyat girilebilen para birimleri (Transferler ekranıyla aynı) */
function sl_known_currencies() {
    return ['EUR', 'USD', 'GBP', 'TRY'];
}

/** Sitede listelenen varsayılan para birimi */
function sl_currency() {
    $g = sl_section('global');
    $c = strtoupper(trim((string)($g['currency'] ?? 'EUR')));
    return preg_match('/^[A-Z]{3}$/', $c) ? $c : 'EUR';
}

/** Ödeme adımında sunulan para birimleri (varsayılan her zaman dahil ve ilk sırada) */
function sl_pay_currencies() {
    $g = sl_section('global');
    $list = $g['pay_currencies'] ?? null;
    if (!is_array($list) || !$list) $list = sl_known_currencies();
    $def = sl_currency();
    $out = [$def];
    foreach ($list as $c) {
        $c = strtoupper(trim((string)$c));
        if (preg_match('/^[A-Z]{3}$/', $c) && $c !== $def) $out[] = $c;
    }
    return array_values(array_unique($out));
}

/** Çocuk koltuğu / yükseltici ücret ayarları */
function sl_fees() {
    $g = sl_section('global');
    $norm = function ($m) {
        $o = [];
        if (is_array($m)) {
            foreach ($m as $c => $v) {
                $v = str_replace(',', '.', trim((string)$v));
                if ($v !== '' && is_numeric($v) && (float)$v >= 0) $o[strtoupper((string)$c)] = (float)$v;
            }
        }
        return $o;
    };
    return [
        'free' => max(0, (int)($g['free_seats'] ?? 1)),
        'perLeg' => !isset($g['fee_per_leg']) || (int)$g['fee_per_leg'] === 1,
        'seat' => $norm($g['seat_fee'] ?? []),
        'booster' => $norm($g['booster_fee'] ?? []),
    ];
}

/**
 * Bir bölgenin fiyat satırlarından para birimine göre fiyat tablosu.
 * $rows: [['lang' => 'de', 'vid' => 3, 'p' => 50.0, 'c' => 'EUR'], ...]  (destination_vehicles)
 * Aynı para birimi birden çok dil sekmesinde girildiyse en çok aracı fiyatlı olan sekme,
 * eşitlikte DE, EN, TR, RU sırası kullanılır.  Dönüş: ['EUR' => [vid => fiyat], 'TRY' => [...]]
 */
function sl_currency_matrix($rows) {
    $byCur = [];
    foreach ($rows as $r) {
        $byCur[$r['c']][$r['lang']][(int)$r['vid']] = (float)$r['p'];
    }
    $order = ['de' => 1, 'en' => 2, 'tr' => 3, 'ru' => 4];
    $out = [];
    foreach ($byCur as $cur => $langs) {
        $best = null;
        foreach ($langs as $lc => $list) {
            if ($best === null) { $best = $lc; continue; }
            $a = count($list);
            $b = count($langs[$best]);
            if ($a > $b || ($a === $b && ($order[$lc] ?? 9) < ($order[$best] ?? 9))) $best = $lc;
        }
        $out[$cur] = $langs[$best];
    }
    return $out;
}

/** Tek bölge için para birimi fiyat tablosu (rezervasyon API'si; sl_destinations ile aynı kural) */
function sl_dest_price_matrix($destId) {
    $vehicles = sl_vehicles();
    $rows = [];
    $st = getDB()->prepare("SELECT vehicle_id, language_code, price, currency FROM destination_vehicles WHERE destination_id = ? AND price > 0");
    $st->execute([(int)$destId]);
    foreach ($st->fetchAll() as $p) {
        $vid = (int)$p['vehicle_id'];
        if (!isset($vehicles[$vid])) continue;
        $rows[] = [
            'lang' => $p['language_code'],
            'vid' => $vid,
            'p' => (float)$p['price'],
            'c' => strtoupper(trim((string)$p['currency'])) ?: 'EUR',
        ];
    }
    return $rows ? sl_currency_matrix($rows) : [];
}

/** Bölgede gösterilecek para birimi: varsayılan varsa o, yoksa EUR / USD / GBP / TRY sırası */
function sl_pick_currency($matrix) {
    if (!$matrix) return '';
    $def = sl_currency();
    if (!empty($matrix[$def])) return $def;
    foreach (sl_known_currencies() as $c) {
        if (!empty($matrix[$c])) return $c;
    }
    return (string)sl_first_key($matrix);
}

function sl_first_key($arr) {
    foreach ($arr as $k => $v) return $k;
    return null;
}

/** 100 altı tam sayı, üstü 10'a yuvarlanır (JS ile aynı) */
function sl_round_nice($x) {
    return $x < 100 ? round($x) : round($x / 10) * 10;
}

/**
 * Koltuk ücreti ($kind = seat | booster) verilen para biriminde.
 * O para birimi için ücret girilmemişse, ücret girilmiş bir para biriminden aracın fiyat oranıyla çevrilir.
 */
function sl_fee_in($kind, $cur, $matrix, $vid, $fees = null) {
    $fees = $fees ?: sl_fees();
    $map = $fees[$kind] ?? [];
    if (isset($map[$cur])) return (float)$map[$cur];
    if (!$map) return 0.0;
    $from = isset($map[sl_currency()]) ? sl_currency() : sl_first_key($map);
    $pTo = $matrix[$cur][$vid] ?? 0;
    $pFrom = $matrix[$from][$vid] ?? 0;
    if ($pTo <= 0 || $pFrom <= 0) return 0.0;
    return (float)sl_round_nice($map[$from] * $pTo / $pFrom);
}

/**
 * Bir transfer (yön) için çocuk koltuğu + yükseltici ek ücreti.
 * Toplam "free" adet ücretsiz; ücretsiz hak önce pahalı olana uygulanır.
 */
function sl_extras_amount($seats, $boosters, $feeSeat, $feeBooster, $free) {
    $seats = max(0, (int)$seats);
    $boosters = max(0, (int)$boosters);
    $free = max(0, (int)$free);
    if ($feeSeat >= $feeBooster) {
        $freeS = min($seats, $free);
        $freeB = min($boosters, $free - $freeS);
    } else {
        $freeB = min($boosters, $free);
        $freeS = min($seats, $free - $freeB);
    }
    $paidS = $seats - $freeS;
    $paidB = $boosters - $freeB;
    return ['amt' => $paidS * $feeSeat + $paidB * $feeBooster, 'paidS' => $paidS, 'paidB' => $paidB];
}

/* ------------------------------------------------------------------ */
/*  Araçlar                                                            */
/* ------------------------------------------------------------------ */

function sl_vehicle_name($v) {
    $brand = trim((string)($v['brand'] ?? ''));
    $model = trim((string)($v['model'] ?? ''));
    if ($model === '' || mb_stripos($brand, $model) !== false) return $brand;
    if ($brand === '') return $model;
    return $brand . ' ' . $model;
}

function sl_vehicles() {
    static $cache = [];
    $lang = sl_lang();
    if (isset($cache[$lang])) return $cache[$lang];
    $out = [];
    try {
        $db = getDB();
        $rows = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY sort_order, capacity, id")->fetchAll();
        $services = [];
        try {
            $st = $db->prepare("
                SELECT vs.id, vs.icon, vst.language_code, vst.name
                FROM vehicle_services vs
                LEFT JOIN vehicle_service_translations vst ON vst.service_id = vs.id
                WHERE vs.is_active = 1
                ORDER BY vs.sort_order
            ");
            $st->execute();
            $defLang = sl_default_lang_code();
            foreach ($st->fetchAll() as $r) {
                $id = (int)$r['id'];
                if (!isset($services[$id])) $services[$id] = ['icon' => $r['icon'], 'names' => []];
                if ($r['language_code']) $services[$id]['names'][$r['language_code']] = $r['name'];
            }
            foreach ($services as $id => $s) {
                $n = $s['names'][$lang] ?? ($s['names']['en'] ?? ($s['names'][$defLang] ?? ''));
                $services[$id]['name'] = trim((string)$n);
            }
        } catch (Exception $e) {}

        foreach ($rows as $v) {
            $sv = [];
            $ids = json_decode((string)($v['services'] ?? ''), true);
            if (is_array($ids)) {
                foreach ($ids as $sid) {
                    $sid = (int)$sid;
                    if (!empty($services[$sid]['name'])) {
                        $sv[] = ['i' => $services[$sid]['icon'], 'n' => $services[$sid]['name']];
                    }
                }
            }
            $out[(int)$v['id']] = [
                'id' => (int)$v['id'],
                'name' => sl_vehicle_name($v),
                'cap' => max(1, (int)$v['capacity']),
                'lug' => (int)$v['luggage_capacity'],
                'cs' => (int)$v['child_seat_capacity'],
                'img' => !empty($v['image']) ? getMediaUrl($v['image']) : '',
                'sv' => $sv,
            ];
        }
    } catch (Exception $e) {}
    $cache[$lang] = $out;
    return $out;
}

/* ------------------------------------------------------------------ */
/*  Bölgeler (destinations)                                            */
/* ------------------------------------------------------------------ */

function sl_is_airport_title($s) {
    return (bool)preg_match('/havaliman|airport|flughafen|аэропорт|\(ayt\)/iu', (string)$s);
}

function sl_region_filters() {
    $r = sl_section('regions');
    $f = $r['filters'] ?? [];
    return is_array($f) ? array_values($f) : [];
}

function sl_region_key($haystack) {
    $hay = sl_norm($haystack);
    foreach (sl_region_filters() as $f) {
        $key = (string)($f['k'] ?? '');
        if ($key === '') continue;
        foreach (explode(',', (string)($f['m'] ?? '')) as $kw) {
            $kw = trim(sl_norm($kw));
            if ($kw === '') continue;
            if (preg_match('/(?<![a-z0-9])' . preg_quote($kw, '/') . '(?![a-z0-9])/u', $hay)) return $key;
        }
    }
    return '';
}

/** Tüm yayındaki bölgeler (aktif dil için), sort_order sırasıyla */
function sl_destinations() {
    static $cache = [];
    $lang = sl_lang();
    if (isset($cache[$lang])) return $cache[$lang];

    $out = [];
    try {
        $db = getDB();
        $st = $db->prepare("
            SELECT d.*,
                   dt.title AS t_title, dt.slug AS t_slug, dt.description AS t_desc, dt.content AS t_content,
                   dt.meta_title AS t_mt, dt.meta_description AS t_md,
                   dt.from_location AS t_from, dt.to_location AS t_to,
                   dt.id AS t_id
            FROM destinations d
            LEFT JOIN destination_translations dt ON dt.destination_id = d.id AND dt.language_code = ?
            WHERE d.status = 'published'
            ORDER BY d.sort_order, d.id
        ");
        $st->execute([$lang]);
        $rows = $st->fetchAll();

        // Fiyatlar: dile göre değil para birimine göre (Transferler ekranındaki dil sekmelerinde seçilen para birimi)
        $vehicles = sl_vehicles();
        $priceRows = [];
        $pst = $db->query("SELECT destination_id, vehicle_id, language_code, price, currency FROM destination_vehicles WHERE price > 0");
        foreach ($pst->fetchAll() as $p) {
            $vid = (int)$p['vehicle_id'];
            if (!isset($vehicles[$vid])) continue;
            $priceRows[(int)$p['destination_id']][] = [
                'lang' => $p['language_code'],
                'vid' => $vid,
                'p' => (float)$p['price'],
                'c' => strtoupper(trim((string)$p['currency'])) ?: 'EUR',
            ];
        }
        $defLang = sl_default_lang_code();
        $listSlug = getPageSlugByKey('destinations', $lang);
        $fallbackImg = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop';

        foreach ($rows as $r) {
            $id = (int)$r['id'];
            $hasT = !empty($r['t_id']);
            $isDefault = ($lang === $defLang);

            $title = trim((string)($r['t_title'] ?? ''));
            if ($title === '') {
                if ($lang === 'ru' && !$isDefault) {
                    $title = 'Трансфер ' . sl_ru_place(sl_place_name($r['title']));
                } else {
                    $title = $r['title'];
                }
            }
            $place = sl_place_name($title);
            $slug = trim((string)($r['t_slug'] ?? '')) ?: $r['slug'];

            // Dile özel metinler: çeviri yoksa sadece varsayılan dilde temel tabloyu kullan
            $desc = $hasT ? (string)$r['t_desc'] : ($isDefault ? (string)$r['description'] : '');
            $content = $hasT ? (string)$r['t_content'] : ($isDefault ? (string)$r['content'] : '');
            $mt = $hasT ? (string)$r['t_mt'] : ($isDefault ? (string)$r['meta_title'] : '');
            $md = $hasT ? (string)$r['t_md'] : ($isDefault ? (string)$r['meta_description'] : '');

            // Fiyatlar: sitede tek para birimi (varsayılan), diğerleri ödeme için matriste
            $matrix = !empty($priceRows[$id]) ? sl_currency_matrix($priceRows[$id]) : [];
            $cur = sl_pick_currency($matrix);
            $pp = [];
            if ($cur !== '') {
                foreach ($matrix[$cur] as $vid => $pv) $pp[$vid] = ['p' => $pv, 'c' => $cur];
            }
            $min = null;
            foreach ($pp as $vid => $pr) {
                if ($min === null || $pr['p'] < $min['p']) $min = ['p' => $pr['p'], 'c' => $pr['c'], 'v' => $vid];
            }

            $img = !empty($r['image']) ? getMediaUrl($r['image']) : (!empty($r['featured_image']) ? getMediaUrl($r['featured_image']) : '');
            if ($img === '' || strpos($img, 'assets/img/travel/') !== false) $img = $fallbackImg;

            $hay = $r['title'] . ' ' . $r['location'] . ' ' . $r['slug'];
            $out[$id] = [
                'id' => $id,
                'base_title' => $r['title'],
                'title' => $title,
                'place' => $place,
                'slug' => $slug,
                'base_slug' => $r['slug'],
                'url' => langUrl($listSlug . '/' . $slug, $lang),
                'img' => $img,
                'location' => (string)$r['location'],
                'from' => trim((string)($r['t_from'] ?? '')),
                'to' => trim((string)($r['t_to'] ?? '')),
                'badge' => trim((string)$r['badge']),
                'featured' => (int)$r['is_featured'] === 1,
                'airport' => sl_is_airport_title($r['title'] . ' ' . $r['slug']),
                'region' => sl_region_key($hay),
                'desc' => trim($desc),
                'content' => trim($content),
                'meta_title' => trim($mt),
                'meta_desc' => trim($md),
                'prices' => $pp,
                'min' => $min,
                'cur' => $cur,
                'pc' => $matrix,
                'updated' => $r['updated_at'] ?? null,
            ];
        }
    } catch (Exception $e) {}

    $cache[$lang] = $out;
    return $out;
}

/** Kartlarda/listede gösterilecek bölgeler (havalimanı kaydı hariç) */
function sl_bookable_destinations($onlyFeatured = false) {
    $out = [];
    foreach (sl_destinations() as $d) {
        if ($d['airport']) continue;
        if ($onlyFeatured && !$d['featured']) continue;
        $out[] = $d;
    }
    return $out;
}

function sl_destination_by_slug($slug) {
    $slug = trim((string)$slug);
    foreach (sl_destinations() as $d) {
        if ($d['slug'] === $slug) return $d;
    }
    foreach (sl_destinations() as $d) {
        if ($d['base_slug'] === $slug) return $d;
    }
    return null;
}

/** Bölge için fiyat özeti: [ow, rt, ow_old, rt_old, currency] */
function sl_price_pair($d) {
    if (empty($d['min'])) return null;
    $ow = (float)$d['min']['p'];
    $rt = $ow * 2;
    return [
        'ow' => $ow,
        'rt' => $rt,
        'ow_old' => sl_old_price($ow),
        'rt_old' => sl_old_price($rt),
        'c' => $d['min']['c'],
    ];
}

/* ------------------------------------------------------------------ */
/*  SEO yardımcıları                                                   */
/* ------------------------------------------------------------------ */

function sl_dest_seo($d) {
    $pp = sl_price_pair($d);
    $vars = [
        'title' => $d['title'],
        'place' => $d['place'],
        'ow' => $pp ? sl_money($pp['ow'], $pp['c']) : '',
        'rt' => $pp ? sl_money($pp['rt'], $pp['c']) : '',
        'airport' => sl_t('airport'),
    ];
    $title = $d['meta_title'];
    if ($title === '' || mb_strtolower($title) === mb_strtolower($d['title'])) {
        $title = sl_t('seo_dest_t', $vars);
    }
    $desc = $d['meta_desc'];
    if ($desc === '') {
        $desc = $pp ? sl_t('seo_dest_d', $vars) : sl_t('seo_dest_d_np', $vars);
    }
    return ['title' => $title, 'desc' => $desc, 'vars' => $vars];
}

/** Anahtar kelime şeridi: admin'de girildiyse onu, yoksa bölgelerden otomatik üretir */
function sl_keywords() {
    $g = sl_section('global');
    $raw = trim((string)($g['keywords'] ?? ''));
    $out = [];
    if ($raw !== '') {
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode('|', $line, 2));
            $out[] = ['t' => $parts[0], 'u' => isset($parts[1]) && $parts[1] !== '' ? sl_link($parts[1]) : ''];
        }
        return $out;
    }
    $lang = sl_lang();
    $generic = [
        'tr' => ['antalya transfer', 'antalya vip transfer', 'antalya havalimanı transfer'],
        'en' => ['antalya airport transfer', 'antalya vip transfer', 'antalya private transfer'],
        'de' => ['transfer antalya', 'flughafentransfer antalya', 'privattransfer antalya'],
        'ru' => ['трансфер анталия', 'трансфер из аэропорта анталии', 'vip трансфер анталия'],
    ];
    foreach ($generic[$lang] ?? $generic['en'] as $k) {
        $out[] = ['t' => $k, 'u' => sl_page_url('destinations')];
    }
    foreach (sl_bookable_destinations(true) as $d) {
        $out[] = ['t' => mb_strtolower(sl_t('kw_dest', ['place' => $d['place']]), 'UTF-8'), 'u' => $d['url']];
    }
    return $out;
}

/** Rezervasyon sihirbazı için sayfaya gömülen veri */
function sl_booking_payload() {
    $lang = sl_lang();
    $dests = [];
    foreach (sl_bookable_destinations(false) as $d) {
        $p = [];
        foreach ($d['prices'] as $vid => $pr) $p[$vid] = [$pr['p'], $pr['c']];
        // pc: para birimine göre tüm fiyatlar (ödeme adımındaki para birimi seçimi için)
        $pc = [];
        foreach ($d['pc'] as $c => $list) {
            foreach ($list as $vid => $pv) $pc[$c][(string)$vid] = $pv;
        }
        $dests[$d['id']] = ['n' => $d['place'], 't' => $d['title'], 'u' => $d['url'], 'p' => $p, 'cur' => $d['cur'], 'pc' => (object)$pc];
    }
    $veh = [];
    foreach (sl_vehicles() as $v) {
        $veh[$v['id']] = ['n' => $v['name'], 'cap' => $v['cap'], 'lug' => $v['lug'], 'cs' => $v['cs'], 'img' => $v['img'],
            'sv' => array_map(function ($s) { return $s['n']; }, array_slice($v['sv'], 0, 6))];
    }

    // Sözleşme ve uyarı (Tur Yönetimi > Transferler ekranından yönetilir)
    $terms = ['title' => sl_t('terms_title'), 'check' => sl_t('terms'), 'text' => sl_t('terms_default')];
    $alert = '';
    try {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM terms_translations WHERE language_code = ? LIMIT 1");
        $st->execute([$lang]);
        if ($r = $st->fetch()) {
            if (trim((string)$r['title']) !== '') $terms['title'] = $r['title'];
            if (trim((string)$r['checkbox_text']) !== '') $terms['check'] = $r['checkbox_text'];
            if (trim((string)$r['content']) !== '') $terms['text'] = $r['content'];
        }
        $st = $db->prepare("SELECT message, is_active FROM booking_alert_translations WHERE language_code = ? LIMIT 1");
        $st->execute([$lang]);
        if (($r = $st->fetch()) && (int)$r['is_active'] === 1) $alert = trim((string)$r['message']);
    } catch (Exception $e) {}

    $keys = ['airport', 'airport_short', 'rt', 'ow', 'step1', 'step2', 'step3', 'step4', 'trip', 'direction', 'dir_arr', 'dir_dep',
        'choose_vehicle', 'cap_pax', 'cap_lug', 'cap_seat', 'too_small', 'too_few_seats', 'vh_for', 'select', 'selected', 'next', 'back',
        'arrival_title', 'departure_title', 'flight_date', 'arr_time', 'dep_time', 'flight', 'flight_ph', 'hotel', 'hotel_ph',
        'pickup_time', 'pickup_hint', 'adults', 'children', 'child_seat', 'child_seat_note', 'contact_title', 'name', 'phone',
        'phone_ph', 'email', 'optional', 'pax_title', 'pax_hint', 'adult_n', 'child_n', 'notes', 'notes_ph', 'summary', 'route',
        'vehicle', 'passengers', 'total', 'cash', 'send', 'sending', 'ok', 'ok2', 'booking_no', 'wa_write', 'wa_msg',
        'err_required', 'err_vehicle', 'err_terms', 'err_generic', 'err_date', 'err_order', 'err_phone', 'err_email',
        'err_capacity', 'err_names', 'pick', 'pick_airport', 'close', 'no_price', 'person',
        'booster', 'seat_free_note', 'seat_fee_note', 'per_leg', 'per_booking', 'extras', 'extras_incl', 'free', 'pay_cur', 'pay_cur_note',
        'info', 'flight_info', 'flight_info_dep', 'err_flight'];
    $i18n = [];
    foreach ($keys as $k) $i18n[$k] = sl_t($k);
    $fees = sl_fees();

    $cfg = sl_theme_config();
    return [
        'lang' => $lang,
        'themeBg' => ['light' => $cfg['light']['bg'], 'dark' => $cfg['dark']['bg']],
        'api' => SITE_URL . '/api/booking',
        'wa' => sl_whatsapp_number(),
        'disc' => sl_discount_pct(),
        'today' => date('Y-m-d'),
        'cur' => sl_currency(),
        'payCurs' => sl_pay_currencies(),
        'fees' => ['free' => $fees['free'], 'perLeg' => $fees['perLeg'], 'seat' => (object)$fees['seat'], 'booster' => (object)$fees['booster']],
        'dests' => $dests,
        'veh' => $veh,
        'terms' => $terms,
        'alert' => $alert,
        't' => $i18n,
        'num' => ['th' => $lang === 'en' ? ',' : ($lang === 'ru' ? ' ' : '.'), 'dp' => $lang === 'en' ? '.' : ','],
    ];
}

/* ------------------------------------------------------------------ */
/*  Blog                                                               */
/* ------------------------------------------------------------------ */

function sl_blog_posts($limit = 6) {
    $lang = sl_lang();
    $defLang = sl_default_lang_code();
    $out = [];
    try {
        $st = getDB()->prepare("
            SELECT bp.id, bp.title, bp.slug, bp.excerpt, bp.featured_image, bp.published_at, bp.created_at, bp.updated_at,
                   bpt.title AS t_title, bpt.slug AS t_slug, bpt.excerpt AS t_excerpt, bpt.id AS t_id
            FROM blog_posts bp
            LEFT JOIN blog_post_translations bpt ON bpt.post_id = bp.id AND bpt.language_code = ?
            WHERE bp.status = 'published'
            ORDER BY COALESCE(bp.published_at, bp.created_at) DESC
            LIMIT " . max(1, (int)$limit) . "
        ");
        $st->execute([$lang]);
        $blogSlug = getPageSlugByKey('blog', $lang);
        foreach ($st->fetchAll() as $r) {
            $hasT = !empty($r['t_id']) && trim((string)$r['t_title']) !== '';
            if (!$hasT && $lang !== $defLang) continue; // çevirisi olmayan yazıyı başka dilde gösterme
            $slug = $hasT && trim((string)$r['t_slug']) !== '' ? $r['t_slug'] : $r['slug'];
            $out[] = [
                'id' => (int)$r['id'],
                'title' => $hasT ? $r['t_title'] : $r['title'],
                'excerpt' => $hasT ? (string)$r['t_excerpt'] : (string)$r['excerpt'],
                'img' => !empty($r['featured_image']) ? getMediaUrl($r['featured_image']) : '',
                'url' => langUrl($blogSlug . '/' . $slug, $lang),
                'date' => $r['published_at'] ?: $r['created_at'],
            ];
        }
    } catch (Exception $e) {}
    return $out;
}

/* ------------------------------------------------------------------ */
/*  Şablon yardımcıları                                                */
/* ------------------------------------------------------------------ */

function sl_partial($name, $vars = []) {
    extract($vars, EXTR_SKIP);
    include __DIR__ . '/partials/' . $name . '.php';
}

/** Tema stilinde rozetli bölge kartı */
function sl_card($d) {
    sl_partial('card', ['d' => $d]);
}
