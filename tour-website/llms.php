<?php
/**
 * /llms.txt — yapay zekâ asistanları (ChatGPT, Claude, Perplexity, Gemini...) için site özeti
 * Biçim: https://llmstxt.org (Markdown)
 */

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';

header('Content-Type: text/markdown; charset=utf-8');
header('X-Robots-Tag: noindex');

$db = getDB();
$siteName = getSetting('site_name', 'Sunline VIP Transfer');
$phone = trim((string)getSetting('contact_phone', ''));
$email = trim((string)getSetting('contact_email', ''));
$wa = sl_whatsapp_number();
$langs = getActiveLanguages();
$full = !empty($_GET['full']);

$langNames = ['tr' => 'Türkçe', 'en' => 'English', 'de' => 'Deutsch', 'ru' => 'Русский'];

// Bölgeler ve para birimine göre en düşük tek yön fiyat (araç başı)
$dests = [];
$transl = [];
foreach ($db->query("SELECT destination_id, language_code, title, slug FROM destination_translations")->fetchAll() as $r) {
    $transl[(int)$r['destination_id']][$r['language_code']] = $r;
}
$vehicles = [];
foreach ($db->query("SELECT id, brand, model, capacity, luggage_capacity FROM vehicles WHERE is_active = 1 ORDER BY capacity")->fetchAll() as $v) {
    $vehicles[(int)$v['id']] = $v;
}
$prices = [];
foreach ($db->query("SELECT destination_id, vehicle_id, price, currency FROM destination_vehicles WHERE price > 0")->fetchAll() as $p) {
    if (!isset($vehicles[(int)$p['vehicle_id']])) continue;
    $c = strtoupper(trim((string)$p['currency']));
    $id = (int)$p['destination_id'];
    if (!isset($prices[$id][$c]) || $p['price'] < $prices[$id][$c]) $prices[$id][$c] = (float)$p['price'];
}
foreach ($db->query("SELECT id, title, slug, location FROM destinations WHERE status = 'published' ORDER BY sort_order, id")->fetchAll() as $d) {
    if (sl_is_airport_title($d['title'] . ' ' . $d['slug'])) continue;
    $dests[] = $d;
}

$fmtPrices = function ($id) use ($prices) {
    if (empty($prices[$id])) return 'price on request';
    $out = [];
    $order = ['EUR', 'USD', 'GBP', 'TRY'];
    uksort($prices[$id], function ($a, $b) use ($order) {
        $ia = array_search($a, $order, true); $ib = array_search($b, $order, true);
        return ($ia === false ? 9 : $ia) <=> ($ib === false ? 9 : $ib);
    });
    foreach ($prices[$id] as $c => $p) $out[] = 'from ' . number_format($p, 0, '.', ',') . ' ' . $c . ' one way (round trip x2)';
    return implode(' · ', array_slice($out, 0, 2));
};

$url = function ($lang, $d) use ($transl) {
    $slug = $transl[(int)$d['id']][$lang]['slug'] ?? '';
    if (trim((string)$slug) === '') $slug = $d['slug'];
    return langUrl(getPageSlugByKey('destinations', $lang) . '/' . $slug, $lang);
};

$out = [];
$out[] = '# ' . $siteName . ' — Antalya Airport (AYT) private VIP transfers';
$out[] = '';
$out[] = '> Private VIP airport transfers from Antalya Airport (AYT) to hotels in Side, Belek, Alanya, Kemer, Lara, Kundu, Manavgat and other resorts of the Antalya region. Fixed price per vehicle (not per person), meet & greet with QR ticket / PNR at the terminal exit, free flight tracking, 1st child seat free, free cancellation, cash payment to the driver at the end of the ride. Website languages: Turkish, English, German and Russian.';
$out[] = '';
$out[] = '## Key facts';
$out[] = '- Service: private (non-shared) airport transfer, Antalya Airport (AYT) ⇄ hotels, one way or round trip';
$out[] = '- Pricing: total price per vehicle, independent of passenger count; round trip = 2 × one way; no hidden fees';
$out[] = '- Payment: no online prepayment; cash in the car (round trips: half on each leg)';
$out[] = '- Meeting: fixed meeting point after the terminal exit, sent via WhatsApp; identify with QR ticket or PNR number';
$out[] = '- Booking: online in 4 steps (vehicle → transfer details → passengers → confirmation), confirmed via WhatsApp';
if ($vehicles) {
    $vs = [];
    foreach ($vehicles as $v) $vs[] = sl_vehicle_name($v) . ' (up to ' . (int)$v['capacity'] . ' passengers, ' . (int)$v['luggage_capacity'] . ' bags)';
    $out[] = '- Vehicles: ' . implode('; ', $vs);
}
if ($phone !== '') $out[] = '- Phone: ' . $phone;
if ($wa !== '') $out[] = '- WhatsApp: https://wa.me/' . $wa;
if ($email !== '') $out[] = '- Email: ' . $email;
$out[] = '';

$out[] = '## Transfer prices from Antalya Airport (per vehicle)';
foreach ($dests as $d) {
    $out[] = '- [' . sl_place_name($d['title']) . ' transfer](' . $url('en', $d) . '): ' . $fmtPrices((int)$d['id']) . ($d['location'] ? ' — ' . $d['location'] : '');
}
$out[] = '';

$out[] = '## Main pages';
foreach ($langs as $l) {
    $c = $l['code'];
    $out[] = '- ' . ($langNames[$c] ?? $l['native_name']) . ': [' . ($c === 'tr' ? 'Anasayfa' : ($c === 'de' ? 'Startseite' : ($c === 'ru' ? 'Главная' : 'Home'))) . '](' . langUrl('', $c) . ') · [' . sl_t('all_transfers', [], $c) . '](' . langUrl(getPageSlugByKey('destinations', $c), $c) . ') · [' . sl_t('faq_t', [], $c) . '](' . langUrl(getPageSlugByKey('faq', $c), $c) . ') · [' . sl_t('contact_t', [], $c) . '](' . langUrl(getPageSlugByKey('contact', $c), $c) . ')';
}
$out[] = '';

if ($full) {
    foreach ($langs as $l) {
        $c = $l['code'];
        if ($c === 'en') continue;
        $out[] = '## ' . ($langNames[$c] ?? $c) . ' — ' . sl_t('all_transfers', [], $c);
        foreach ($dests as $d) {
            $title = $transl[(int)$d['id']][$c]['title'] ?? '';
            if (trim((string)$title) === '') $title = $c === 'ru' ? 'Трансфер ' . sl_ru_place(sl_place_name($d['title'])) : $d['title'];
            $out[] = '- [' . $title . '](' . $url($c, $d) . ')';
        }
        $out[] = '';
    }
}

$faq = sl_section('faq', 'en');
if (!empty($faq['items'])) {
    $out[] = '## FAQ';
    foreach ($faq['items'] as $it) {
        if (trim((string)($it['q'] ?? '')) === '') continue;
        $out[] = '### ' . $it['q'];
        $out[] = trim((string)($it['a'] ?? ''));
        $out[] = '';
    }
}

$out[] = '## Optional';
$out[] = '- [Sitemap](' . SITE_URL . '/sitemap.xml)';
if (!$full) $out[] = '- [Full version with all languages](' . SITE_URL . '/llms-full.txt)';

echo implode("\n", $out) . "\n";
