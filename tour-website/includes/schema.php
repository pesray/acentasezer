<?php
/**
 * Schema.org JSON-LD Structured Data Helpers
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

/**
 * Render JSON-LD script block
 */
function renderSchema($schema) {
    if (empty($schema)) return '';
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

/**
 * Organization (TravelAgency) Schema
 */
function getOrganizationSchema() {
    $siteName = getSetting('site_name', 'Tour');
    $siteLogo = getSetting('site_logo', '');
    $logoUrl = $siteLogo ? UPLOADS_URL . $siteLogo : ASSETS_URL . 'img/logo.png';
    if (strpos($logoUrl, 'http') !== 0) {
        $logoUrl = SITE_URL . '/' . ltrim($logoUrl, '/');
    }

    $phone = getSetting('contact_phone', '');
    $email = getSetting('contact_email', '');
    $address = getSetting('contact_address', '');

    $sameAs = [];
    foreach (['facebook', 'twitter', 'instagram', 'youtube'] as $social) {
        $val = getSetting('social_' . $social, '');
        if (!empty($val)) {
            $sameAs[] = $val;
        }
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        '@id' => SITE_URL . '/#organization',
        'name' => $siteName,
        'url' => SITE_URL,
        'logo' => $logoUrl,
        'image' => $logoUrl,
        'description' => getSetting('site_description', ''),
    ];

    if (!empty($phone) || !empty($email)) {
        $schema['contactPoint'] = [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'telephone' => $phone,
            'email' => $email,
            'areaServed' => 'Worldwide',
            'availableLanguage' => ['Turkish', 'English', 'German', 'Russian']
        ];
    }

    if (!empty($address)) {
        $schema['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressCountry' => 'TR'
        ];
    }

    if (!empty($sameAs)) {
        $schema['sameAs'] = $sameAs;
    }

    return $schema;
}

/**
 * BreadcrumbList Schema
 */
function getBreadcrumbSchema($crumbs) {
    if (empty($crumbs)) return null;

    $items = [];
    $position = 1;

    // Home is always first
    $items[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => __('menu_home', 'header'),
        'item' => langUrl('')
    ];

    foreach ($crumbs as $crumb) {
        $url = $crumb['url'];
        if (strpos($url, 'http') !== 0) {
            $url = SITE_URL . '/' . ltrim($url, '/');
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $crumb['name'],
            'item' => $url
        ];
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items
    ];
}

/**
 * Tour (TourPackage) Schema
 */
function getTourSchema($tour, $tourUrl, $vehicles = []) {
    if (empty($tour)) return null;

    $imageUrl = !empty($tour['image']) ? getMediaUrl($tour['image']) : ASSETS_URL . 'img/travel/tour-1.webp';
    if (strpos($imageUrl, 'http') !== 0) {
        $imageUrl = SITE_URL . '/' . ltrim($imageUrl, '/');
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristTrip',
        'name' => $tour['title'],
        'description' => strip_tags($tour['description'] ?? ''),
        'image' => $imageUrl,
        'url' => $tourUrl,
        'provider' => [
            '@type' => 'TravelAgency',
            'name' => getSetting('site_name', 'Tour'),
            'url' => SITE_URL
        ]
    ];

    // If there is pricing info, we can add it as Offers
    if (!empty($vehicles)) {
        $offers = [];
        foreach ($vehicles as $v) {
            if ($v['price'] > 0) {
                $offers[] = [
                    '@type' => 'Offer',
                    'price' => $v['price'],
                    'priceCurrency' => $v['currency'] ?? 'EUR',
                    'name' => $v['name'] ?? '',
                    'url' => $tourUrl,
                    'availability' => 'https://schema.org/InStock',
                    'seller' => [
                        '@type' => 'TravelAgency',
                        'name' => getSetting('site_name', 'Tour')
                    ]
                ];
            }
        }
        if (!empty($offers)) {
            $schema['offers'] = count($offers) === 1 ? $offers[0] : $offers;
        }
    }

    return $schema;
}

/**
 * TouristDestination Schema
 */
function getDestinationSchema($dest, $destUrl) {
    if (empty($dest)) return null;

    $imageUrl = !empty($dest['image']) ? getMediaUrl($dest['image']) : ASSETS_URL . 'img/travel/destination-1.webp';
    if (strpos($imageUrl, 'http') !== 0) {
        $imageUrl = SITE_URL . '/' . ltrim($imageUrl, '/');
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'TouristDestination',
        'name' => $dest['title'],
        'description' => strip_tags($dest['description'] ?? ''),
        'image' => $imageUrl,
        'url' => $destUrl
    ];
}

/**
 * FAQPage Schema
 */
function getFaqSchema($faqs) {
    if (empty($faqs)) return null;

    $mainEntity = [];
    foreach ($faqs as $faq) {
        $mainEntity[] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => strip_tags($faq['answer'])
            ]
        ];
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $mainEntity
    ];
}
