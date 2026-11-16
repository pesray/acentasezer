<?php
/**
 * IndexNow — admin'de içerik kaydedilince Bing, Yandex vb. arama motorlarına
 * "bu sayfalar değişti, yeniden tara" bildirimi (ChatGPT araması Bing dizinini kullanır).
 *
 * - Anahtar dosyası site kökünde: /<INDEXNOW_KEY>.txt (içinde yalnız anahtar)
 * - Adresler sitemap.php ile aynı kurallarla üretilir (yalnız yayındaki içerik, havalimanı kaydı hariç)
 * - Yalnız canlı alan adında gönderir; test/yerel ortamda gönderilecek veriyi error_log'a yazar
 * - Kaydı asla bozmaz: her hata yakalanır, en fazla ~6 sn bekler
 */

if (!defined('INDEXNOW_KEY')) define('INDEXNOW_KEY', '16c2f348b75c36330328a00258c2de6b');
if (!defined('INDEXNOW_HOSTS')) define('INDEXNOW_HOSTS', 'sunlineviptransfer.com,www.sunlineviptransfer.com');

require_once INCLUDES_PATH . 'sunline/core.php';

/**
 * Kaydedilen içeriğin tüm dil adresleri.
 * $type: home | destination | tour | blog | page
 */
function indexnow_urls($type, $id = 0)
{
    $db = getDB();
    $id = (int)$id;
    $langs = array_column(getActiveLanguages(), 'code');
    if (!$langs) $langs = ['tr'];
    $def = sl_default_lang_code();
    $urls = [];

    if ($type === 'home') {
        foreach ($langs as $l) $urls[] = langUrl('', $l);

    } elseif ($type === 'destination') {
        $st = $db->prepare("SELECT id, title, slug, status FROM destinations WHERE id = ?");
        $st->execute([$id]);
        $d = $st->fetch();
        if (!$d || $d['status'] !== 'published' || sl_is_airport_title($d['title'] . ' ' . $d['slug'])) return [];
        $st = $db->prepare("SELECT language_code, slug FROM destination_translations WHERE destination_id = ?");
        $st->execute([$id]);
        $tr = [];
        foreach ($st->fetchAll() as $r) {
            if (trim((string)$r['slug']) !== '') $tr[$r['language_code']] = $r['slug'];
        }
        foreach ($langs as $l) {
            $urls[] = langUrl(getPageSlugByKey('destinations', $l) . '/' . ($tr[$l] ?? $d['slug']), $l);
        }

    } elseif ($type === 'tour') {
        $st = $db->prepare("SELECT id, slug, status FROM tours WHERE id = ?");
        $st->execute([$id]);
        $t = $st->fetch();
        if (!$t || $t['status'] !== 'published') return [];
        $st = $db->prepare("SELECT language_code, slug FROM tour_translations WHERE tour_id = ?");
        $st->execute([$id]);
        $tr = [];
        foreach ($st->fetchAll() as $r) {
            if (trim((string)$r['slug']) !== '') $tr[$r['language_code']] = $r['slug'];
        }
        foreach ($langs as $l) {
            $urls[] = langUrl(getPageSlugByKey('tours', $l) . '/' . ($tr[$l] ?? $t['slug']), $l);
        }

    } elseif ($type === 'blog') {
        $st = $db->prepare("SELECT id, slug, status FROM blog_posts WHERE id = ?");
        $st->execute([$id]);
        $p = $st->fetch();
        if (!$p || $p['status'] !== 'published') return [];
        $st = $db->prepare("SELECT language_code, slug, title FROM blog_post_translations WHERE post_id = ?");
        $st->execute([$id]);
        $tr = [];
        foreach ($st->fetchAll() as $r) {
            if (trim((string)$r['title']) !== '') $tr[$r['language_code']] = trim((string)$r['slug']);
        }
        foreach ($langs as $l) {
            if (!isset($tr[$l]) && $l !== $def) continue;
            $s = ($tr[$l] ?? '') !== '' ? $tr[$l] : $p['slug'];
            $urls[] = langUrl(getPageSlugByKey('blog', $l) . '/' . $s, $l);
        }

    } elseif ($type === 'page') {
        $st = $db->prepare("SELECT id, slug, status, is_homepage FROM pages WHERE id = ?");
        $st->execute([$id]);
        $p = $st->fetch();
        if (!$p || $p['status'] !== 'published') return [];
        if ((int)$p['is_homepage'] === 1) return indexnow_urls('home');
        $st = $db->prepare("SELECT language_code, slug, title FROM page_translations WHERE page_id = ?");
        $st->execute([$id]);
        $tr = [];
        foreach ($st->fetchAll() as $r) {
            if (trim((string)$r['slug']) !== '' && trim((string)$r['title']) !== '') $tr[$r['language_code']] = $r['slug'];
        }
        foreach ($langs as $l) {
            if (!isset($tr[$l]) && $l !== $def) continue;
            $urls[] = langUrl($tr[$l] ?? $p['slug'], $l);
        }
    }

    return array_values(array_unique($urls));
}

/** JSON'u IndexNow uç noktasına POST eder, HTTP kodunu döner (0 = bağlantı yok). */
function indexnow_post($endpoint, $body)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json; charset=utf-8\r\n",
        'content' => $body,
        'timeout' => 6,
        'ignore_errors' => true,
    ]]);
    @file_get_contents($endpoint, false, $ctx);
    if (!empty($http_response_header[0]) && preg_match('#\s(\d{3})\b#', $http_response_header[0], $m)) {
        return (int)$m[1];
    }
    return 0;
}

/**
 * İçerik kaydından sonra çağrılır. Admin'deki başarı mesajına eklenecek kısa notu döner ('' = not yok).
 */
function indexnow_ping($type, $id = 0)
{
    try {
        $urls = indexnow_urls($type, $id);
        if (!$urls) return '';

        $host = strtolower((string)parse_url(SITE_URL, PHP_URL_HOST));
        $live = in_array($host, explode(',', INDEXNOW_HOSTS), true);
        $payload = json_encode([
            'host' => $host,
            'key' => INDEXNOW_KEY,
            'keyLocation' => rtrim(SITE_URL, '/') . '/' . INDEXNOW_KEY . '.txt',
            'urlList' => $urls,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (!$live && getenv('INDEXNOW_TEST_SEND') !== '1') {
            error_log('IndexNow (test ortamı, gönderilmedi): ' . $payload);
            return ' (IndexNow: test ortamı, gönderilmedi)';
        }

        $endpoint = getenv('INDEXNOW_ENDPOINT') ?: 'https://api.indexnow.org/indexnow';
        $code = indexnow_post($endpoint, $payload);
        error_log('IndexNow HTTP ' . $code . ': ' . count($urls) . ' adres (' . $type . ' #' . (int)$id . ')');
        if ($code === 200 || $code === 202) return ' Arama motorlarına bildirildi.';
        return ' (Arama motoru bildirimi gönderilemedi: ' . ($code ?: 'bağlantı yok') . ')';
    } catch (Throwable $e) {
        error_log('IndexNow hata: ' . $e->getMessage());
        return '';
    }
}
