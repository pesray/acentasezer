<?php
/**
 * Dynamic robots.txt Generator
 */

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/config.php';

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "\n";
echo "Sitemap: " . SITE_URL . "/sitemap.xml\n";
