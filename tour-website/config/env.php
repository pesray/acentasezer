<?php
/**
 * .env Dosya Yükleyici
 * Harici bağımlılık olmadan .env dosyasını parse eder
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Yorum satırlarını atla
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // KEY=VALUE formatını parse et
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Tırnak işaretlerini temizle
        if (preg_match('/^"(.*)"$/', $value, $m)) {
            $value = $m[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
            $value = $m[1];
        }

        // Sadece tanımlı değilse ayarla
        if (!isset($_ENV[$key]) && getenv($key) === false) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Ortam değişkenini al
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    // Boolean dönüşümleri
    $lower = strtolower($value);
    if ($lower === 'true') return true;
    if ($lower === 'false') return false;
    if ($lower === 'null') return null;

    return $value;
}

// .env dosyasını yükle
loadEnv(dirname(__DIR__) . '/.env');
