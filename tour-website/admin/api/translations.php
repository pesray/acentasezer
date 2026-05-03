<?php
/**
 * Translations API - DeepL çeviri ve CRUD işlemleri
 */

// DeepL bazı diller için alt-kod istiyor (EN -> EN-GB, PT -> PT-PT)
function deeplLangCode($code) {
    $map = [
        'EN' => 'EN-GB',
        'PT' => 'PT-PT',
    ];
    $code = strtoupper(trim($code));
    return isset($map[$code]) ? $map[$code] : $code;
}

switch ($action) {
    case 'deepl_translate':
        $text = trim($_POST['text'] ?? '');
        $sourceLang = strtoupper(trim($_POST['source_lang'] ?? 'TR'));
        $targetLang = strtoupper(trim($_POST['target_lang'] ?? ''));

        if ($text === '' || $targetLang === '') {
            jsonResponse(false, 'Metin ve hedef dil zorunludur.');
        }

        $apiKey = env('DEEPL_API_KEY', '');
        if ($apiKey === '') {
            jsonResponse(false, 'DeepL API anahtarı tanımlı değil. .env dosyasına DEEPL_API_KEY ekleyin.');
        }

        // DeepL Free vs Pro endpoint
        $apiUrl = env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate');

        $postData = http_build_query([
            'text'        => $text,
            'source_lang' => strtoupper($sourceLang),
            'target_lang' => deeplLangCode($targetLang),
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: DeepL-Auth-Key ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            jsonResponse(false, 'DeepL bağlantı hatası: ' . $curlErr);
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200 || !isset($result['translations'][0]['text'])) {
            $errMsg = isset($result['message']) ? $result['message'] : 'Bilinmeyen hata (HTTP ' . $httpCode . ')';
            jsonResponse(false, 'DeepL hatası: ' . $errMsg);
        }

        jsonResponse(true, '', [
            'translated_text' => $result['translations'][0]['text'],
            'detected_source' => $result['translations'][0]['detected_source_language'] ?? $sourceLang,
        ]);
        break;

    case 'deepl_translate_batch':
        $text = trim($_POST['text'] ?? '');
        $sourceLang = strtoupper(trim($_POST['source_lang'] ?? 'TR'));
        $targetLangsRaw = $_POST['target_langs'] ?? '';

        if (is_string($targetLangsRaw)) {
            $targetLangs = array_filter(array_map('trim', explode(',', $targetLangsRaw)));
        } else {
            $targetLangs = (array)$targetLangsRaw;
        }

        if ($text === '' || empty($targetLangs)) {
            jsonResponse(false, 'Metin ve hedef diller zorunludur.');
        }

        $apiKey = env('DEEPL_API_KEY', '');
        if ($apiKey === '') {
            jsonResponse(false, 'DeepL API anahtarı tanımlı değil. .env dosyasına DEEPL_API_KEY ekleyin.');
        }

        $apiUrl = env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate');
        $results = [];

        $sourceLangUpper = strtoupper($sourceLang);
        foreach ($targetLangs as $tl) {
            $tlUpper = strtoupper($tl);
            if ($tlUpper === $sourceLangUpper) continue;

            $postData = http_build_query([
                'text'        => $text,
                'source_lang' => $sourceLangUpper,
                'target_lang' => deeplLangCode($tlUpper),
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $apiUrl,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: DeepL-Auth-Key ' . $apiKey,
                ],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $r = json_decode($response, true);
            if ($httpCode === 200 && isset($r['translations'][0]['text'])) {
                $results[strtolower($tl)] = $r['translations'][0]['text'];
            } else {
                $results[strtolower($tl)] = null;
            }
        }

        jsonResponse(true, '', ['translations' => $results]);
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
