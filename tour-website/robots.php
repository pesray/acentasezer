<?php
/**
 * Dinamik robots.txt — arama motorları ve yapay zekâ arama botları için açık
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$rules = "Allow: /\nDisallow: /admin/\nDisallow: /api/\n";

echo "User-agent: *\n" . $rules . "\n";

// Yapay zekâ arama/asistan botları (ChatGPT, Claude, Perplexity, Gemini, Apple, Bing, Yandex)
$aiBots = ['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-User', 'Claude-SearchBot', 'PerplexityBot', 'Perplexity-User',
    'Google-Extended', 'Applebot-Extended', 'Applebot', 'Bingbot', 'YandexBot', 'DuckAssistBot', 'MistralAI-User', 'CCBot'];
foreach ($aiBots as $bot) {
    echo "User-agent: " . $bot . "\n" . $rules . "\n";
}

echo "Sitemap: " . SITE_URL . "/sitemap.xml\n";
