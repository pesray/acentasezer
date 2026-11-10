<?php
/**
 * Schema.org JSON-LD yardımcıları (Google + yapay zekâ arama motorları için yapısal veri)
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}
require_once INCLUDES_PATH . 'sunline/core.php';

/**
 * JSON-LD script bloğu
 */
function renderSchema($schema) {
    if (empty($schema)) return '';
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>' . "\n";
}

function schemaOrgId() {
    return SITE_URL . '/#organization';
}

/**
 * Firma (TravelAgency + yerel işletme bilgileri)
 */
function getOrganizationSchema() {
    $siteName = getSetting('site_name', 'Sunline VIP Transfer');
    $logo = sl_logo_urls();
    $phone = trim((string)getSetting('contact_phone', ''));
    $email = trim((string)getSetting('contact_email', ''));
    $address = trim((string)getSetting('contact_address', ''));

    $sameAs = [];
    foreach (['facebook', 'twitter', 'instagram', 'youtube', 'linkedin'] as $social) {
        $val = trim((string)getSetting('social_' . $social, ''));
        if ($val !== '') $sameAs[] = $val;
    }

    $areas = [['@type' => 'City', 'name' => 'Antalya']];
    foreach (array_slice(sl_bookable_destinations(false), 0, 40) as $d) {
        $areas[] = ['@type' => 'Place', 'name' => sl_place_name($d['base_title'])];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        '@id' => schemaOrgId(),
        'name' => $siteName,
        'alternateName' => ['Sunline VIP Transfer Antalya', 'Sunline VIP'],
        'url' => SITE_URL . '/',
        'logo' => $logo['png'],
        'image' => $logo['png'],
        'description' => sl_t('seo_home_d'),
        'priceRange' => '€€',
        'currenciesAccepted' => 'EUR, USD, TRY, GBP',
        'paymentAccepted' => 'Cash',
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '00:00',
            'closes' => '23:59',
        ],
        'areaServed' => $areas,
        'knowsLanguage' => ['tr', 'en', 'de', 'ru'],
    ];
    if ($phone !== '') $schema['telephone'] = $phone;
    if ($email !== '') $schema['email'] = $email;
    if ($phone !== '' || $email !== '') {
        $cp = [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'areaServed' => 'TR',
            'availableLanguage' => ['Turkish', 'English', 'German', 'Russian'],
        ];
        if ($phone !== '') $cp['telephone'] = $phone;
        if ($email !== '') $cp['email'] = $email;
        $schema['contactPoint'] = $cp;
    }
    $schema['address'] = [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Antalya',
        'addressRegion' => 'Antalya',
        'addressCountry' => 'TR',
    ];
    if ($address !== '') $schema['address']['streetAddress'] = $address;
    if ($sameAs) $schema['sameAs'] = $sameAs;
    return $schema;
}

/**
 * WebSite şeması (anasayfa)
 */
function getWebsiteSchema() {
    $langs = array_column(getActiveLanguages(), 'code');
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => SITE_URL . '/#website',
        'url' => SITE_URL . '/',
        'name' => getSetting('site_name', 'Sunline VIP Transfer'),
        'inLanguage' => $langs ?: ['tr'],
        'publisher' => ['@id' => schemaOrgId()],
    ];
}

/**
 * BreadcrumbList — $crumbs: [['name' => ..., 'url' => tam veya göreli adres], ...]
 */
function getBreadcrumbSchema($crumbs) {
    if (empty($crumbs)) return null;
    $items = [];
    $position = 1;
    $items[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => sl_t('home'),
        'item' => langUrl(''),
    ];
    foreach ($crumbs as $crumb) {
        $url = $crumb['url'];
        if (strpos($url, 'http') !== 0) {
            $url = langUrl(ltrim($url, '/'));
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $crumb['name'],
            'item' => $url,
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

/**
 * Bölge transfer hizmeti (TaxiService + araç bazlı fiyat teklifleri)
 */
function getTransferServiceSchema($d, $url) {
    if (empty($d)) return null;
    $vehicles = sl_vehicles();
    $offers = [];
    $low = null;
    $high = null;
    $cur = 'EUR';
    foreach ($d['prices'] as $vid => $pr) {
        if (empty($vehicles[$vid])) continue;
        $cur = $pr['c'];
        $low = $low === null ? $pr['p'] : min($low, $pr['p']);
        $high = $high === null ? $pr['p'] : max($high, $pr['p']);
        $offers[] = [
            '@type' => 'Offer',
            'name' => sl_t('ow') . ' · ' . $vehicles[$vid]['name'],
            'price' => number_format($pr['p'], 2, '.', ''),
            'priceCurrency' => $pr['c'],
            'availability' => 'https://schema.org/InStock',
            'url' => $url,
            'eligibleQuantity' => ['@type' => 'QuantitativeValue', 'maxValue' => $vehicles[$vid]['cap'], 'unitText' => 'passengers'],
            'seller' => ['@id' => schemaOrgId()],
        ];
    }
    $img = sl_abs_url($d['img']);
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TaxiService',
        '@id' => $url . '#service',
        'name' => $d['title'],
        'serviceType' => 'Airport transfer',
        'description' => sl_dest_seo($d)['desc'],
        'url' => $url,
        'image' => $img,
        'provider' => ['@id' => schemaOrgId()],
        'areaServed' => [
            ['@type' => 'Place', 'name' => $d['place']],
            ['@type' => 'Airport', 'name' => 'Antalya Airport', 'iataCode' => 'AYT'],
        ],
        'availableLanguage' => ['Turkish', 'English', 'German', 'Russian'],
    ];
    if ($offers) {
        $schema['offers'] = [
            '@type' => 'AggregateOffer',
            'lowPrice' => number_format($low, 2, '.', ''),
            'highPrice' => number_format($high, 2, '.', ''),
            'priceCurrency' => $cur,
            'offerCount' => count($offers),
            'offers' => $offers,
        ];
    }
    return $schema;
}

/**
 * Bölge listesi (ItemList)
 */
function getDestinationListSchema($dests) {
    if (empty($dests)) return null;
    $items = [];
    $i = 1;
    foreach ($dests as $d) {
        $items[] = ['@type' => 'ListItem', 'position' => $i++, 'url' => $d['url'], 'name' => $d['title']];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'itemListElement' => $items,
    ];
}

/**
 * Tur şeması (Turlar sayfaları için korunuyor)
 */
function getTourSchema($tour, $tourUrl, $vehicles = []) {
    if (empty($tour)) return null;
    $imageUrl = !empty($tour['image']) ? getMediaUrl($tour['image']) : '';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristTrip',
        'name' => $tour['title'],
        'description' => strip_tags($tour['description'] ?? ''),
        'url' => $tourUrl,
        'provider' => ['@id' => schemaOrgId()],
    ];
    if ($imageUrl !== '') $schema['image'] = sl_abs_url($imageUrl);
    if (!empty($vehicles)) {
        $offers = [];
        foreach ($vehicles as $v) {
            if (($v['price'] ?? 0) > 0) {
                $offers[] = [
                    '@type' => 'Offer',
                    'price' => $v['price'],
                    'priceCurrency' => $v['currency'] ?? 'EUR',
                    'name' => $v['name'] ?? '',
                    'url' => $tourUrl,
                    'availability' => 'https://schema.org/InStock',
                ];
            }
        }
        if ($offers) $schema['offers'] = count($offers) === 1 ? $offers[0] : $offers;
    }
    return $schema;
}

/**
 * Eski uyumluluk: TouristDestination
 */
function getDestinationSchema($dest, $destUrl) {
    if (empty($dest)) return null;
    return [
        '@context' => 'https://schema.org',
        '@type' => 'TouristDestination',
        'name' => $dest['title'],
        'description' => strip_tags($dest['description'] ?? ''),
        'url' => $destUrl,
    ];
}

/**
 * FAQPage — $faqs: [['question'|'q' => ..., 'answer'|'a' => ...], ...]
 */
function getFaqSchema($faqs) {
    if (empty($faqs)) return null;
    $mainEntity = [];
    foreach ($faqs as $faq) {
        $q = $faq['question'] ?? ($faq['q'] ?? '');
        $a = $faq['answer'] ?? ($faq['a'] ?? '');
        if (trim((string)$q) === '' || trim((string)$a) === '') continue;
        $mainEntity[] = [
            '@type' => 'Question',
            'name' => strip_tags($q),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($a))],
        ];
    }
    if (!$mainEntity) return null;
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $mainEntity,
    ];
}
