<?php
/**
 * Raporlar — ortak parçalar (reports.php + reports_drivers.php)
 * Filtreler, veri sorgusu (yalnız okuma), biçimlendirme, sayfa üstü, stil ve JS.
 *
 * Kurallar (Sezer, 2026-11-21):
 *  - TAHMİNİ CİRO = sisteme girilen işlerin bedeli (total_price), her iş kendi para biriminde (potansiyel kazanç;
 *    iptal edilen ve silinenler hariç; onay bekleyen site rezervasyonları dahil).
 *  - Durumlar: Dışarıya verildi = is_outsourced 1 (aynı iş "iş yapıldı" da işaretli olabilir, BİR KEZ sayılır)
 *              Kendimiz yaptık  = is_completed 1 ve dışarıya verilmemiş · Bekleyen = ikisi de değil
 *  - NET CİRO = kendimiz yaptığımız işlerin bedelinin tamamı + dışarıya verilen işlerde PASS.
 *  - PARTNER HAKEDİŞİ = işi dışarıya verdiğimiz tutar (İş Durumu'ndaki "Verilen Tutar", outsource_price).
 *  - PASS = iş bedeli − partner hakedişi (bize kalan). Örnek: 40 € iş, dışarıya 30 € → hakediş 30 €, pass 10 €.
 *    Verilen Tutar girilmemişse düşülecek tutar yok (pass = iş bedeli) ve "Kontrol edilmesi gerekenler"de listelenir.
 *  - Hakediş farklı para biriminde girildiyse (ör. € iş, ₺ hakediş) iş bedeli GÜNCEL kurla hakediş para birimine
 *    çevrilir: 40 € × 55 = 2.200 ₺ − 1.500 ₺ = 700 ₺ → pass ve net ciro ₺'ye 700 ₺ eklenir.
 *    Kur: TCMB günlük kurları (döviz alış), rp_kurlar(). Kur alınamazsa o işlerin pass'ı hesaplanmaz, sayfada uyarı çıkar.
 *  - Para birimleri birbirine eklenmez (kur yalnız yukarıdaki pass hesabında kullanılır).
 *  - Tarih = transferin tarihi (Arrival/Depar: uçuş tarihi, Ara transfer: alış tarihi; boşsa kayıt tarihi).
 *  - Gidiş-dönüş rezervasyon 2 kayıttır (her yön kendi tutarıyla), rezervasyon ekranındaki gibi.
 *  - Şoför: booking_drivers tablosundaki atama (bir işe bir şoför).
 */

if (!defined('RAPOR_SAYFASI')) { // yalnız rapor sayfalarından (girişli + admin) çağrılır
    http_response_code(403);
    exit;
}

/* ------------------------------------------------------------------ */
/*  Filtreler                                                          */
/* ------------------------------------------------------------------ */
$presets = [
    'bu_ay'     => 'Bu ay',
    'gecen_ay'  => 'Geçen ay',
    'son_3_ay'  => 'Son 3 ay',
    'bu_yil'    => 'Bu yıl',
    'gecen_yil' => 'Geçen yıl',
    'tumu'      => 'Tüm zamanlar',
    'ozel'      => 'Özel aralık',
];
$turler = [
    'tumu'    => 'Tüm transferler',
    'arrival' => 'Arrival',
    'depar'   => 'Depar',
    'ara'     => 'Ara Transfer',
];

$donem = isset($_GET['donem']) && is_string($_GET['donem']) && isset($presets[$_GET['donem']]) ? $_GET['donem'] : 'bu_ay';
$tur   = isset($_GET['tur']) && is_string($_GET['tur']) && isset($turler[$_GET['tur']]) ? $_GET['tur'] : 'tumu';

function rp_tarih_gecerli($s) {
    if (!is_string($s) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) return false;
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
}
/** gg.aa.yyyy (Türkiye) yazılmışsa yyyy-aa-gg'ye çevirir; diğerlerini olduğu gibi bırakır */
function rp_tarih_normal($s) {
    if (is_string($s) && preg_match('/^\s*(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})\s*$/', $s, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }
    return is_string($s) ? trim($s) : '';
}

$bugun = new DateTime('today');
$from = null;
$to = null;
switch ($donem) {
    case 'gecen_ay':
        $from = (new DateTime('first day of last month'))->format('Y-m-d');
        $to   = (new DateTime('last day of last month'))->format('Y-m-d');
        break;
    case 'son_3_ay':
        $from = (new DateTime('first day of -2 month'))->format('Y-m-d');
        $to   = (new DateTime('last day of this month'))->format('Y-m-d');
        break;
    case 'bu_yil':
        $from = $bugun->format('Y') . '-01-01';
        $to   = $bugun->format('Y') . '-12-31';
        break;
    case 'gecen_yil':
        $y = (int)$bugun->format('Y') - 1;
        $from = $y . '-01-01';
        $to   = $y . '-12-31';
        break;
    case 'tumu':
        break;
    case 'ozel':
        $f = rp_tarih_normal($_GET['bas'] ?? '');
        $t = rp_tarih_normal($_GET['bit'] ?? '');
        if (rp_tarih_gecerli($f) && rp_tarih_gecerli($t)) {
            $from = $f;
            $to = $t;
            if ($from > $to) { $tmp = $from; $from = $to; $to = $tmp; }
            break;
        }
        $donem = 'bu_ay'; // geçersiz tarih → bu ay
        // no break
    default: // bu_ay
        $from = (new DateTime('first day of this month'))->format('Y-m-d');
        $to   = (new DateTime('last day of this month'))->format('Y-m-d');
}

/* ------------------------------------------------------------------ */
/*  Veri (yalnız okuma)                                                */
/* ------------------------------------------------------------------ */
function rp_satirlar($db, $from, $to, $tur) {
    $rows = [];
    $soforSec = "sd.driver_id AS sofor_id,
                 NULLIF(TRIM(CONCAT(COALESCE(dr.name, ''), ' ', COALESCE(dr.surname, ''))), '') AS sofor,
                 dr.plate AS plaka";

    if ($tur !== 'ara') {
        $sql = "
            SELECT b.booking_number, b.booking_direction AS yon, b.booking_status, b.customer_name,
                   COALESCE(b.flight_date, DATE(b.created_at)) AS tarih,
                   b.total_price, b.currency, b.is_completed, b.is_outsourced,
                   b.outsource_price, b.outsource_currency,
                   COALESCE(NULLIF(TRIM(p.name), ''), NULLIF(TRIM(b.outsource_name), '')) AS partner,
                   d.title AS guzergah,
                   TRIM(CONCAT(COALESCE(v.brand, ''), ' ', COALESCE(v.model, ''))) AS arac,
                   $soforSec
            FROM bookings b
            LEFT JOIN outsource_partners p ON p.id = b.outsource_partner_id
            LEFT JOIN destinations d ON d.id = b.destination_id
            LEFT JOIN vehicles v ON v.id = b.vehicle_id
            LEFT JOIN (SELECT booking_id, MIN(driver_id) AS driver_id FROM booking_drivers
                       WHERE booking_id IS NOT NULL GROUP BY booking_id) sd ON sd.booking_id = b.id
            LEFT JOIN drivers dr ON dr.id = sd.driver_id
            WHERE b.is_deleted = 0 AND b.booking_type = 'transfer'";
        $params = [];
        if ($tur === 'arrival') $sql .= " AND b.booking_direction = 'outbound'";
        if ($tur === 'depar')   $sql .= " AND b.booking_direction = 'return'";
        if ($from !== null) {
            $sql .= " AND COALESCE(b.flight_date, DATE(b.created_at)) BETWEEN ? AND ?";
            $params[] = $from;
            $params[] = $to;
        }
        $st = $db->prepare($sql);
        $st->execute($params);
        foreach ($st->fetchAll() as $r) {
            $r['tip'] = $r['yon'] === 'return' ? 'depar' : 'arrival';
            $rows[] = $r;
        }
    }

    if ($tur === 'tumu' || $tur === 'ara') {
        try {
            $sql = "
                SELECT i.booking_number, 'inter' AS yon, i.booking_status, i.customer_name,
                       COALESCE(i.pickup_date, DATE(i.created_at)) AS tarih,
                       i.total_price, i.currency, i.is_completed, i.is_outsourced,
                       i.outsource_price, i.outsource_currency,
                       COALESCE(NULLIF(TRIM(p.name), ''), NULLIF(TRIM(i.outsource_name), '')) AS partner,
                       CONCAT_WS(' → ', NULLIF(i.pickup_hotel_name, ''), NULLIF(i.destination_hotel_name, '')) AS guzergah,
                       TRIM(CONCAT(COALESCE(v.brand, ''), ' ', COALESCE(v.model, ''))) AS arac,
                       $soforSec
                FROM inter_hotel_transfers i
                LEFT JOIN outsource_partners p ON p.id = i.outsource_partner_id
                LEFT JOIN vehicles v ON v.id = i.vehicle_id
                LEFT JOIN (SELECT inter_transfer_id, MIN(driver_id) AS driver_id FROM booking_drivers
                           WHERE inter_transfer_id IS NOT NULL GROUP BY inter_transfer_id) sd ON sd.inter_transfer_id = i.id
                LEFT JOIN drivers dr ON dr.id = sd.driver_id
                WHERE i.is_deleted = 0";
            $params = [];
            if ($from !== null) {
                $sql .= " AND COALESCE(i.pickup_date, DATE(i.created_at)) BETWEEN ? AND ?";
                $params[] = $from;
                $params[] = $to;
            }
            $st = $db->prepare($sql);
            $st->execute($params);
            foreach ($st->fetchAll() as $r) {
                $r['tip'] = 'ara';
                $rows[] = $r;
            }
        } catch (Exception $e) {
            // ara transfer tablosu yoksa rapor yine çalışır
        }
    }

    usort($rows, function ($a, $b) {
        return strcmp((string)$a['tarih'], (string)$b['tarih']);
    });
    return $rows;
}

function rp_soforler($db) {
    try {
        return $db->query("SELECT id, TRIM(CONCAT(COALESCE(name, ''), ' ', COALESCE(surname, ''))) AS ad, plate
                           FROM drivers ORDER BY name, surname")->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/* ------------------------------------------------------------------ */
/*  Sınıflandırma + biçim                                              */
/* ------------------------------------------------------------------ */
function rp_kategori($r) {
    if (($r['booking_status'] ?? '') === 'cancelled') return 'iptal';
    if ((int)$r['is_outsourced'] === 1) return 'disari';
    if ((int)$r['is_completed'] === 1 || ($r['booking_status'] ?? '') === 'completed') return 'yapildi';
    return 'bekleyen';
}
function rp_pb($c) {
    $c = strtoupper(trim((string)$c));
    return $c !== '' ? $c : '—';
}
/**
 * İşin tutarları:
 *  bedel / pb = işin bedeli (total_price, currency) → tahmini ciro
 *  hk / hpb   = dışarıya verildiyse partner hakedişi (outsource_price, outsource_currency; boşsa işin para birimi)
 */
function rp_is($r, $k) {
    $o = ['bedel' => max(0, (float)$r['total_price']), 'pb' => rp_pb($r['currency']), 'hk' => 0.0, 'hpb' => ''];
    if ($k === 'disari') {
        $o['hk'] = max(0, (float)$r['outsource_price']);
        $o['hpb'] = rp_pb(trim((string)($r['outsource_currency'] ?? '')) !== '' ? $r['outsource_currency'] : $r['currency']);
    }
    return $o;
}

/* ------------------------------------------------------------------ */
/*  Döviz kuru — yalnız hakedişi farklı para biriminde girilen işlerin  */
/*  pass hesabı için. Kaynak: TCMB günlük kurlar (döviz alış).          */
/*  Sezer ileride sabit kur verirse rp_kurlar() başında kullanılır.     */
/* ------------------------------------------------------------------ */
define('RP_KUR_URL', 'https://www.tcmb.gov.tr/kurlar/today.xml');

/** TCMB today.xml → ['kur' => ['TRY' => 1, 'EUR' => .., 'USD' => .., 'GBP' => ..] (1 birim = x ₺), 'tarih' => 'gg.aa.yyyy', 'kaynak' => ..] ya da null */
function rp_kur_ayristir($xml) {
    if (!is_string($xml) || $xml === '' || !function_exists('simplexml_load_string')) return null;
    $eski = libxml_use_internal_errors(true);
    $x = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($eski);
    if ($x === false || !isset($x->Currency)) return null;
    $kur = ['TRY' => 1.0];
    foreach ($x->Currency as $c) {
        $kod = strtoupper((string)$c['Kod']);
        if (!in_array($kod, ['EUR', 'USD', 'GBP'], true)) continue;
        $birim = max(1, (int)$c->Unit);
        $deger = (float)str_replace(',', '.', (string)$c->ForexBuying);
        if ($deger > 0) $kur[$kod] = $deger / $birim;
    }
    if (count($kur) < 4) return null; // EUR, USD, GBP üçü de gelmeli
    $tarih = (string)$x['Tarih'];
    return ['kur' => $kur, 'tarih' => preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $tarih) ? $tarih : '', 'kaynak' => 'TCMB döviz alış'];
}
function rp_kur_cek($url) {
    $govde = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 12, // TCMB bazen 9-10 sn'de cevap veriyor
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $govde = curl_exec($ch);
        if ((int)curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) $govde = false;
        curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        $govde = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 12]]));
    }
    return $govde === false ? null : rp_kur_ayristir($govde);
}
/** Son alınan kurun saklandığı geçici dosya (sunucunun geçici klasörü; veritabanına yazılmaz, deploy'dan etkilenmez) */
function rp_kur_dosya() {
    return rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sunline_rapor_kur_' . substr(md5(__DIR__), 0, 8) . '.json';
}
function rp_kur_gecerli($k) {
    if (!is_array($k) || !isset($k['kur']) || !is_array($k['kur'])) return false;
    foreach (['EUR', 'USD', 'GBP'] as $pb) {
        if (!isset($k['kur'][$pb]) || !is_numeric($k['kur'][$pb]) || $k['kur'][$pb] <= 0) return false;
    }
    return isset($k['tarih'], $k['kaynak']) && is_string($k['tarih']) && is_string($k['kaynak']);
}
/**
 * Güncel kurlar. TCMB en fazla saatte bir sorulur; son alınan kur geçici dosyada saklanır (tüm oturumlar ortak).
 * TCMB cevap vermezse son alınan kur kullanılır ('eski' => true; tarihi sayfada yazar), hiç alınmamışsa null.
 * Başarısız denemeden sonra o oturumda 5 dk yeniden denenmez (her sayfa beklemesin).
 */
function rp_kurlar() {
    static $kur = false;
    if ($kur !== false) return $kur;
    $dosya = rp_kur_dosya();
    $sakli = null;
    if (is_file($dosya)) {
        $j = json_decode((string)@file_get_contents($dosya), true);
        if (is_array($j) && isset($j['zaman']) && rp_kur_gecerli($j['veri'] ?? null)) $sakli = $j;
    }
    $simdi = time();
    if ($sakli && $simdi - (int)$sakli['zaman'] < 3600) return $kur = $sakli['veri'];
    if ($simdi - (int)($_SESSION['rp_kur_deneme'] ?? 0) >= 300) {
        $yeni = rp_kur_cek(RP_KUR_URL);
        if ($yeni) {
            @file_put_contents($dosya, json_encode(['zaman' => $simdi, 'veri' => $yeni]), LOCK_EX);
            unset($_SESSION['rp_kur_deneme']);
            return $kur = $yeni;
        }
        if (session_status() === PHP_SESSION_ACTIVE) $_SESSION['rp_kur_deneme'] = $simdi;
    }
    return $kur = $sakli ? $sakli['veri'] + ['eski' => true] : null;
}
/** Kur gerekiyor mu: hakedişi işin para biriminden farklı girilmiş dışarı işi var mı */
function rp_kur_gerekli($rows) {
    foreach ($rows as $r) {
        $k = rp_kategori($r);
        if ($k !== 'disari') continue;
        $is = rp_is($r, $k);
        if ($is['hk'] > 0 && $is['hpb'] !== $is['pb']) return true;
    }
    return false;
}
/** Tutarı bir para biriminden diğerine çevirir (₺ üzerinden); kur yoksa null */
function rp_cevir($tutar, $kaynak, $hedef, $kur) {
    if ($kaynak === $hedef) return $tutar;
    if (!$kur || empty($kur['kur'][$kaynak]) || empty($kur['kur'][$hedef])) return null;
    return $tutar * $kur['kur'][$kaynak] / $kur['kur'][$hedef];
}
function rp_kur_yazi($kur, $pb) { // "1 € = 49,1234 ₺"
    global $pbSem;
    return '1 ' . ($pbSem[$pb] ?? $pb) . ' = ' . number_format($kur['kur'][$pb], 4, ',', '.') . ' ₺';
}

/**
 * Bir işin rapora girişi (Sezer, 2026-11-21):
 *  tahmini = [pb, bedel] (iptal hariç hepsi)
 *  net     = [pb, tutar] | null  — kendimiz yaptık: bedel; dışarıya verildi: pass; bekleyen: null
 *  hakedis = [hpb, hk]   | null  — dışarıya verildi ve Verilen Tutar girilmişse
 *  pass    = [pb, tutar] | null  — dışarıya verildi: bedel − hakediş (hakediş para biriminde; gerekirse kurla)
 *  cevrim  = [bedel, pb, çevrilmiş bedel, hpb] | null — kurla çevrildiyse
 *  kurYok  = true → hakediş farklı para biriminde ama kur yok; pass hesaplanmadı
 */
function rp_is_hesap($r, $k, $kur) {
    $is = rp_is($r, $k);
    $o = ['k' => $k, 'bedel' => $is['bedel'], 'pb' => $is['pb'], 'hk' => $is['hk'], 'hpb' => $is['hpb'],
          'tahmini' => [$is['pb'], $is['bedel']], 'net' => null, 'hakedis' => null, 'pass' => null, 'cevrim' => null, 'kurYok' => false];
    if ($k === 'yapildi') {
        $o['net'] = [$is['pb'], $is['bedel']];
    } elseif ($k === 'disari') {
        if ($is['hk'] <= 0) {
            $o['pass'] = [$is['pb'], $is['bedel']]; // Verilen Tutar girilmemiş: düşülecek tutar yok
        } else {
            $o['hakedis'] = [$is['hpb'], $is['hk']];
            $bedelH = rp_cevir($is['bedel'], $is['pb'], $is['hpb'], $kur);
            if ($bedelH !== null) $bedelH = round($bedelH, 2); // kuruşa yuvarla: özet, liste ve Excel toplamları birebir tutsun
            if ($bedelH === null) {
                $o['kurYok'] = true;
            } else {
                if ($is['hpb'] !== $is['pb']) $o['cevrim'] = [$is['bedel'], $is['pb'], $bedelH, $is['hpb']];
                $o['pass'] = [$is['hpb'], $bedelH - $is['hk']];
            }
        }
        $o['net'] = $o['pass'];
    }
    return $o;
}

function rp_bos_alan() {
    $b = ['n' => 0, 't' => 0.0];
    // netCevrim: net cironun, başka para birimiyle satılıp bu para biriminde hakediş verilen (kurla çevrilen) işlerden gelen kısmı
    return ['tahmini' => $b, 'net' => $b, 'hakedis' => $b, 'pass' => $b, 'netCevrim' => $b];
}
function rp_alan_ekle(&$set, $alan, $t) {
    if (!is_array($set)) $set = rp_bos_alan();
    $set[$alan]['n']++;
    $set[$alan]['t'] += $t;
}

/**
 * Genel rapor hesabı (yalnız okuma). Döner: say, ozet[pb], aylar[pb][ym], tipler[pb][tip], partner[pb][ad],
 * isler[pb][] (işlem listesi; ek = satır başka para biriminin dökümünden geliyor), kontrol sayıları.
 */
function rp_genel_hesapla($rows, $kur) {
    $h = ['say' => ['yapildi' => 0, 'disari' => 0, 'bekleyen' => 0, 'iptal' => 0],
          'ozet' => [], 'aylar' => [], 'tipler' => [], 'partner' => [], 'isler' => [], 'cevrimKaynak' => [],
          'fiyatsiz' => 0, 'verilenEksik' => 0, 'passEksi' => 0, 'partnerYok' => 0, 'cevrilen' => 0, 'kurYok' => 0];
    foreach ($rows as $r) {
        $k = rp_kategori($r);
        $h['say'][$k]++;
        if ($k === 'iptal') continue;

        $x = rp_is_hesap($r, $k, $kur);
        if ($x['bedel'] <= 0) $h['fiyatsiz']++;
        $ym = $r['tarih'] ? substr($r['tarih'], 0, 7) : '0000-00';
        $tip = $r['tip'];

        $girisler = ['tahmini' => $x['tahmini'], 'net' => $x['net'], 'hakedis' => $x['hakedis'], 'pass' => $x['pass']];
        foreach ($girisler as $alan => $g) {
            if (!$g) continue;
            rp_alan_ekle($h['ozet'][$g[0]], $alan, $g[1]);
            rp_alan_ekle($h['aylar'][$g[0]][$ym], $alan, $g[1]);
            rp_alan_ekle($h['tipler'][$g[0]][$tip], $alan, $g[1]);
        }
        if ($x['cevrim'] && $x['net']) { // özet kırılımı: kendi işleri + kurla çevrilen işlerin pass'ı
            rp_alan_ekle($h['ozet'][$x['net'][0]], 'netCevrim', $x['net'][1]);
            $h['cevrimKaynak'][$x['net'][0]][$x['pb']] = true;
        }

        $pad = '';
        if ($k === 'disari') {
            if ($x['hk'] <= 0) $h['verilenEksik']++;
            if ($x['kurYok']) $h['kurYok']++;
            if ($x['cevrim']) $h['cevrilen']++;
            if ($x['pass'] && $x['pass'][1] <= -0.005) $h['passEksi']++;
            $pad = trim((string)$r['partner']);
            if ($pad === '') { $h['partnerYok']++; $pad = '(Partner girilmemiş)'; }
            $dokundu = [];
            foreach (['hakedis', 'pass'] as $alan) {
                if (!$x[$alan]) continue;
                $ppb = $x[$alan][0];
                if (!isset($h['partner'][$ppb][$pad])) $h['partner'][$ppb][$pad] = ['n' => 0, 'hakedis' => 0.0, 'pass' => 0.0, 'passN' => 0];
                $h['partner'][$ppb][$pad][$alan] += $x[$alan][1];
                if ($alan === 'pass') $h['partner'][$ppb][$pad]['passN']++;
                if (!isset($dokundu[$ppb])) { $dokundu[$ppb] = 1; $h['partner'][$ppb][$pad]['n']++; }
            }
        }

        $satir = $x + ['tarih' => $r['tarih'], 'no' => $r['booking_number'], 'tip' => $tip,
                       'musteri' => $r['customer_name'], 'guzergah' => $r['guzergah'], 'partner' => $pad];
        // İşlem listesi: işin kendi para biriminde; hakediş / pass başka para birimindeyse o dökümde de (ek satır)
        $dokum = [$x['pb'] => false];
        foreach (['hakedis', 'net'] as $alan) {
            if ($x[$alan] && !isset($dokum[$x[$alan][0]])) $dokum[$x[$alan][0]] = true;
        }
        foreach ($dokum as $dpb => $ek) $h['isler'][$dpb][] = ['ek' => $ek] + $satir;
    }
    return $h;
}
function rp_sirala_anahtar($keys) {
    $sira = ['EUR' => 1, 'TRY' => 2, 'USD' => 3, 'GBP' => 4];
    usort($keys, function ($a, $b) use ($sira) {
        return (($sira[$a] ?? 9) <=> ($sira[$b] ?? 9)) ?: strcmp($a, $b);
    });
    return $keys;
}
function rp_para($tutar, $pb) {
    $sem = ['EUR' => '€', 'TRY' => '₺', 'USD' => '$', 'GBP' => '£'];
    if (abs($tutar) < 0.005) $tutar = 0.0; // "-0" yazmasın
    $ond = abs($tutar - round($tutar)) < 0.005 ? 0 : 2;
    return number_format($tutar, $ond, ',', '.') . ' ' . ($sem[$pb] ?? $pb);
}
function rp_birlesik(...$bloklar) {
    $o = ['n' => 0, 't' => 0.0];
    foreach ($bloklar as $b) { $o['n'] += $b['n']; $o['t'] += $b['t']; }
    return $o;
}
/** Tek para birimli hücre: tutar (iş yoksa —; eksi tutar kırmızı) */
function rp_tutar($blok, $pb) {
    if ($blok['n'] === 0) return '<span class="text-body-secondary">—</span>';
    $yazi = e(rp_para($blok['t'], $pb));
    return $blok['t'] <= -0.005 ? '<span class="text-danger">' . $yazi . '</span>' : $yazi;
}
function rp_ay_ad($ym) {
    if ($ym === '0000-00') return 'Tarihsiz';
    $ad = ['01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan', '05' => 'Mayıs', '06' => 'Haziran',
           '07' => 'Temmuz', '08' => 'Ağustos', '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'];
    return ($ad[substr($ym, 5, 2)] ?? '') . ' ' . substr($ym, 0, 4);
}

$pbAd = ['EUR' => 'Euro', 'TRY' => 'Türk Lirası', 'USD' => 'Dolar', 'GBP' => 'Sterlin'];
$pbSem = ['EUR' => '€', 'TRY' => '₺', 'USD' => '$', 'GBP' => '£'];
$tipAd = ['arrival' => 'Arrival', 'depar' => 'Depar', 'ara' => 'Ara Transfer'];
$katAd = ['yapildi' => 'Kendimiz yaptık', 'disari' => 'Dışarıya verildi', 'bekleyen' => 'Bekleyen', 'iptal' => 'İptal'];
$katRenk = ['yapildi' => 'success', 'disari' => 'info', 'bekleyen' => 'warning', 'iptal' => 'secondary'];

/** Filtre adres parçası (sekme ve CSV bağlantıları için) */
function rp_filtre_qs($ek = []) {
    global $donem, $from, $to, $tur;
    return http_build_query(array_filter(array_merge([
        'donem' => $donem,
        'bas'   => $donem === 'ozel' ? $from : null,
        'bit'   => $donem === 'ozel' ? $to : null,
        'tur'   => $tur !== 'tumu' ? $tur : null,
    ], $ek)));
}
function rp_aralik_yazi() {
    global $from, $to;
    return $from === null ? 'Tüm zamanlar' : date('d.m.Y', strtotime($from)) . ' – ' . date('d.m.Y', strtotime($to));
}

/* ------------------------------------------------------------------ */
/*  Excel (CSV) — header.php'den ÖNCE çağrılır                          */
/* ------------------------------------------------------------------ */
function rp_csv_gonder($rows, $dosyaOnEk) {
    global $from, $to, $tipAd, $katAd;
    $guvenli = function ($v) { // Excel formül enjeksiyonuna karşı (=, +, -, @ ile başlayan metin)
        $v = (string)$v;
        return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    };
    $sayi = function ($v) { return $v === null || $v === '' ? '' : number_format((float)$v, 2, ',', ''); };
    $ad = $dosyaOnEk . '_' . ($from ?? 'tumu') . '_' . ($to ?? 'tumu') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $ad . '"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // Excel UTF-8 tanısın
    $kur = rp_kur_gerekli($rows) ? rp_kurlar() : null;
    fputcsv($out, ['Tarih', 'Rezervasyon No', 'Tür', 'Durum', 'Müşteri', 'Güzergah', 'Araç', 'Şoför', 'İşin bedeli (tahmini ciro)', 'Para Birimi', 'Partner', 'Partner hakedişi', 'Hakediş PB', 'Pass', 'Pass PB', 'Net ciro', 'Net PB', 'Not'], ';');
    foreach ($rows as $r) {
        $k = rp_kategori($r);
        $x = rp_is_hesap($r, $k, $kur);
        $not = '';
        if ($k === 'iptal') {
            $not = 'İptal — hesaba katılmadı';
        } elseif ($k === 'bekleyen') {
            $not = 'Bekliyor — net ciroya girmedi';
        } elseif ($k === 'disari' && $x['hk'] <= 0) {
            $not = 'Verilen Tutar girilmemiş — hakediş düşülmedi';
        } elseif ($x['kurYok']) {
            $not = 'Kur alınamadı — pass hesaplanmadı';
        } elseif ($x['cevrim']) {
            $not = 'Pass = ' . $sayi($x['cevrim'][0]) . ' ' . $x['cevrim'][1] . ' × ' . number_format($kur['kur'][$x['cevrim'][1]] / $kur['kur'][$x['cevrim'][3]], 4, ',', '')
                 . ' = ' . $sayi($x['cevrim'][2]) . ' ' . $x['cevrim'][3] . ' − ' . $sayi($x['hk']) . ' ' . $x['hpb']
                 . ' (' . $kur['kaynak'] . ($kur['tarih'] !== '' ? ' ' . $kur['tarih'] : '') . ')';
        }
        fputcsv($out, [
            $r['tarih'] ? date('d.m.Y', strtotime($r['tarih'])) : '',
            $guvenli($r['booking_number']),
            $tipAd[$r['tip']],
            $katAd[$k],
            $guvenli($r['customer_name']),
            $guvenli($r['guzergah']),
            $guvenli($r['arac']),
            $guvenli($r['sofor'] ?? ''),
            $sayi($r['total_price']),
            rp_pb($r['currency']),
            $k === 'disari' ? $guvenli($r['partner']) : '',
            $x['hakedis'] ? $sayi($x['hakedis'][1]) : '',
            $x['hakedis'] ? $x['hakedis'][0] : '',
            $x['pass'] ? $sayi($x['pass'][1]) : '',
            $x['pass'] ? $x['pass'][0] : '',
            $x['net'] ? $sayi($x['net'][1]) : '',
            $x['net'] ? $x['net'][0] : '',
            $not,
        ], ';');
    }
    fclose($out);
    exit;
}

/* ------------------------------------------------------------------ */
/*  Sayfa üstü: başlık + alt sekmeler + filtre                          */
/* ------------------------------------------------------------------ */
function rp_ust($aktif, $soforler = null, $soforSecili = '') {
    global $presets, $turler, $donem, $tur, $from, $to;
    $sekmeler = [
        'genel' => ['reports.php', 'bi-bar-chart-line', 'Genel rapor'],
        'sofor' => ['reports_drivers.php', 'bi-person-badge', 'Şoförler'],
    ];
    $csvQs = rp_filtre_qs($soforSecili !== '' ? ['sofor' => $soforSecili] : []);
    ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Raporlar</h1>
        <div class="rp-note mt-1"><?= e(rp_aralik_yazi()) ?> · <?= e($turler[$tur]) ?> · tarih = transfer tarihi</div>
    </div>
    <a class="btn btn-outline-success" href="?<?= e($csvQs) ?>&amp;export=csv">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel'e aktar (CSV)
    </a>
</div>

<ul class="nav nav-tabs rp-tabs mb-3">
    <?php foreach ($sekmeler as $k => $s): ?>
    <li class="nav-item">
        <a class="nav-link <?= $aktif === $k ? 'active' : '' ?>" href="<?= e($s[0]) ?>?<?= e(rp_filtre_qs()) ?>"<?= $aktif === $k ? ' aria-current="page"' : '' ?>>
            <i class="bi <?= $s[1] ?> me-1"></i><?= e($s[2]) ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-3 col-xl-2">
                <label class="form-label small fw-semibold mb-1">Dönem</label>
                <select name="donem" class="form-select" id="rpDonem">
                    <?php foreach ($presets as $k => $ad): ?>
                    <option value="<?= e($k) ?>" <?= $donem === $k ? 'selected' : '' ?>><?= e($ad) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 rp-ozel" <?= $donem === 'ozel' ? '' : 'hidden' ?>>
                <label class="form-label small fw-semibold mb-1">Başlangıç</label>
                <input type="date" name="bas" class="form-control rp-tarih" max="9999-12-31" placeholder="gg.aa.yyyy" value="<?= e($donem === 'ozel' ? $from : date('Y-m-01')) ?>">
            </div>
            <div class="col-6 col-md-2 rp-ozel" <?= $donem === 'ozel' ? '' : 'hidden' ?>>
                <label class="form-label small fw-semibold mb-1">Bitiş</label>
                <input type="date" name="bit" class="form-control rp-tarih" max="9999-12-31" placeholder="gg.aa.yyyy" value="<?= e($donem === 'ozel' ? $to : date('Y-m-t')) ?>">
            </div>
            <div class="col-12 col-md-3 col-xl-2">
                <label class="form-label small fw-semibold mb-1">Transfer türü</label>
                <select name="tur" class="form-select">
                    <?php foreach ($turler as $k => $ad): ?>
                    <option value="<?= e($k) ?>" <?= $tur === $k ? 'selected' : '' ?>><?= e($ad) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($soforler !== null): ?>
            <div class="col-12 col-md-3 col-xl-2">
                <label class="form-label small fw-semibold mb-1">Şoför</label>
                <select name="sofor" class="form-select">
                    <option value="">Tüm şoförler</option>
                    <?php foreach ($soforler as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (string)$soforSecili === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['ad']) ?><?= $s['plate'] ? ' · ' . e($s['plate']) : '' ?></option>
                    <?php endforeach; ?>
                    <option value="yok" <?= $soforSecili === 'yok' ? 'selected' : '' ?>>(Şoför atanmamış işler)</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Göster</button>
            </div>
        </form>
    </div>
</div>
    <?php
}

function rp_stil() {
    ?>
<link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
<style>
    /* Özel aralık takvimi (flatpickr) — panel renkleri, açık/koyu tema */
    .flatpickr-calendar { font-family: inherit; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.selected:focus { background: var(--primary); border-color: var(--primary); color: #fff; }
    .flatpickr-day.today { border-color: var(--primary); }
    .flatpickr-input[readonly], .flatpickr-input.form-control { background-color: var(--bs-body-bg); }
    [data-bs-theme="dark"] .flatpickr-calendar { background: var(--sidebar-bg); box-shadow: 0 6px 24px rgba(0,0,0,.5); border: 1px solid var(--sidebar-border); }
    [data-bs-theme="dark"] .flatpickr-calendar.arrowTop:before, [data-bs-theme="dark"] .flatpickr-calendar.arrowTop:after { border-bottom-color: var(--sidebar-bg); }
    [data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:before, [data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:after { border-top-color: var(--sidebar-bg); }
    [data-bs-theme="dark"] .flatpickr-months .flatpickr-month, [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month, [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month { color: var(--text-base); fill: var(--text-base); }
    [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month svg, [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month svg { fill: var(--text-base); }
    [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months, [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months option, [data-bs-theme="dark"] .flatpickr-current-month input.cur-year { background: var(--sidebar-bg); color: var(--text-base); }
    [data-bs-theme="dark"] .numInputWrapper span.arrowUp:after { border-bottom-color: var(--text-base); }
    [data-bs-theme="dark"] .numInputWrapper span.arrowDown:after { border-top-color: var(--text-base); }
    [data-bs-theme="dark"] span.flatpickr-weekday { color: var(--text-muted); }
    [data-bs-theme="dark"] .flatpickr-day { color: var(--text-base); }
    [data-bs-theme="dark"] .flatpickr-day:hover, [data-bs-theme="dark"] .flatpickr-day:focus { background: var(--hover-bg); border-color: var(--hover-bg); }
    [data-bs-theme="dark"] .flatpickr-day.prevMonthDay, [data-bs-theme="dark"] .flatpickr-day.nextMonthDay, [data-bs-theme="dark"] .flatpickr-day.flatpickr-disabled { color: var(--text-muted); opacity: .6; }
    [data-bs-theme="dark"] .flatpickr-day.selected, [data-bs-theme="dark"] .flatpickr-day.selected:hover { background: var(--primary); border-color: var(--primary); color: #0b1220; }
    .rp-note { font-size: .8rem; color: var(--text-muted); }
    .rp-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }
    .rp-big { font-size: 1.75rem; font-weight: 700; line-height: 1.15; font-variant-numeric: tabular-nums; }
    .rp-table td, .rp-table th { white-space: nowrap; font-variant-numeric: tabular-nums; vertical-align: middle; }
    .rp-table thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
    .rp-table tfoot td, .rp-table tfoot th { font-weight: 700; border-top: 2px solid var(--bs-border-color); background: var(--hover-bg); }
    .rp-table .rp-col-gercek { font-weight: 700; color: var(--bs-success-text-emphasis); }
    .rp-ozet td { font-size: 1.05rem; padding-top: .8rem; padding-bottom: .8rem; }
    .rp-ozet .rp-n { display: block; font-size: .75rem; font-weight: 500; color: var(--text-muted); }
    .rp-kirilim { display: block; margin-top: .35rem; padding-top: .3rem; border-top: 1px dashed var(--bs-border-color); font-size: .72rem; font-weight: 500; color: var(--text-muted); line-height: 1.35; }
    .rp-kirilim > span { display: block; }
    .rp-pb { display: inline-flex; align-items: center; gap: .5rem; font-weight: 700; }
    .rp-pb-sem { width: 2rem; height: 2rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
                 background: var(--primary-light); color: var(--primary); font-size: 1rem; }
    .rp-pills .nav-link { border: 1px solid var(--bs-border-color); margin: 0 .5rem .5rem 0; font-weight: 600; color: var(--text-base); }
    .rp-pills .nav-link.active { background: var(--primary); border-color: var(--primary); color: #fff; }
    .rp-pills .nav-link small { font-weight: 500; opacity: .8; }
    .rp-tabs .nav-link { font-weight: 600; color: var(--text-muted); }
    .rp-tabs .nav-link.active { color: var(--primary); }
    .rp-progress { height: .5rem; border-radius: 999px; }
    .dataTables_wrapper .dataTables_paginate .pagination { flex-wrap: wrap; justify-content: flex-end; } /* telefonda sayfa düğmeleri taşmasın */
    .stat-card .stat-icon { flex-shrink: 0; }
</style>
    <?php
}

/** Para birimi seçici düğmeler */
function rp_pb_secici($pbler, $aktifPb, $sayilar) {
    global $pbSem;
    ?>
<div class="d-flex flex-wrap align-items-center mb-2">
    <span class="fw-semibold me-3 mb-2"><i class="bi bi-currency-exchange me-1"></i>Döküm para birimi:</span>
    <div class="nav nav-pills rp-pills" role="tablist">
        <?php foreach ($pbler as $pb): ?>
        <button type="button" class="nav-link <?= $pb === $aktifPb ? 'active' : '' ?>" data-rp-pb="<?= e($pb) ?>" role="tab" aria-selected="<?= $pb === $aktifPb ? 'true' : 'false' ?>">
            <?= e(($pbSem[$pb] ?? '') . ' ' . $pb) ?> <small>· <?= (int)($sayilar[$pb] ?? 0) ?> iş</small>
        </button>
        <?php endforeach; ?>
    </div>
</div>
    <?php
}

/** Ortak JS: dönem kutuları, DataTables (partner + işlem listesi alt toplamları), para birimi sekmeleri */
function rp_js() {
    return <<<'JS'
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/tr.js"></script>
<script>
(function () {
    // Özel aralık: Türkçe takvim, ekranda gg.aa.yyyy; sunucuya yyyy-aa-gg gider.
    // Takvim kütüphanesi yüklenemezse tarayıcının kendi tarih kutusu çalışmaya devam eder.
    if (window.flatpickr) {
        document.querySelectorAll('input.rp-tarih').forEach(function (el) {
            window.flatpickr(el, {
                locale: (window.flatpickr.l10ns && window.flatpickr.l10ns.tr) ? 'tr' : 'default',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd.m.Y',
                allowInput: true,
                disableMobile: true
            });
        });
    }

    var donem = document.getElementById('rpDonem');
    function ozelKutular() {
        var ozel = donem.value === 'ozel';
        document.querySelectorAll('.rp-ozel').forEach(function (el) {
            el.hidden = !ozel;
            el.querySelectorAll('input').forEach(function (i) { i.required = ozel; });
        });
    }
    if (donem) { donem.addEventListener('change', ozelKutular); ozelKutular(); }

    var SEM = { EUR: '€', TRY: '₺', USD: '$', GBP: '£' };
    function para(v, pb) {
        if (Math.abs(v) < 0.005) v = 0; // "-0" yazmasın
        var d = Math.abs(v - Math.round(v)) < 0.005 ? 0 : 2;
        return v.toLocaleString('tr-TR', { minimumFractionDigits: d, maximumFractionDigits: d }) + ' ' + (SEM[pb] || pb);
    }

    $(function () {
        if (!$.fn.DataTable) return;
        $('table.rp-dt').each(function () {
            $(this).DataTable({ order: [[1, 'desc']], pageLength: 25 });
        });
        $('table.rp-isler').each(function () {
            var pb = $(this).data('pb');
            $(this).DataTable({
                order: [[0, 'asc']],
                pageLength: 25,
                footerCallback: function () {
                    // ek satır = bedeli başka para biriminde olan işin, bu para biriminde girilen hakedişi
                    // data-tutar / .rp-f-tutar: Şoförler sayfası; data-bedel/hk/net: Genel rapor
                    var api = this.api(), n = 0, ek = 0, tutar = 0, bedel = 0, hk = 0, net = 0, kat = {}, sira = [];
                    api.rows({ search: 'applied' }).every(function () {
                        var tr = this.node();
                        tutar += parseFloat(tr.getAttribute('data-tutar')) || 0;
                        bedel += parseFloat(tr.getAttribute('data-bedel')) || 0;
                        hk += parseFloat(tr.getAttribute('data-hk')) || 0;
                        net += parseFloat(tr.getAttribute('data-net')) || 0;
                        if (tr.getAttribute('data-ek') === '1') { ek++; return; }
                        n++;
                        var k = tr.getAttribute('data-kad');
                        if (!(k in kat)) { kat[k] = 0; sira.push(k); }
                        kat[k]++;
                    });
                    var f = $(api.table().footer());
                    f.find('.rp-f-n').text('TOPLAM · ' + n + ' işlem' + (ek ? ' + ' + ek + ' hakediş' : ''));
                    f.find('.rp-f-kat').text(sira.sort().map(function (k) { return k + ': ' + kat[k]; }).join(' · '));
                    f.find('.rp-f-tutar').text(para(tutar, pb));
                    f.find('.rp-f-bedel').text(para(bedel, pb));
                    f.find('.rp-f-hk').text(para(hk, pb));
                    f.find('.rp-f-net').text(para(net, pb)).toggleClass('text-danger', net <= -0.005);
                }
            });
        });

        document.querySelectorAll('[data-rp-pb]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pb = btn.getAttribute('data-rp-pb');
                document.querySelectorAll('[data-rp-pb]').forEach(function (b) {
                    var on = b === btn;
                    b.classList.toggle('active', on);
                    b.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                document.querySelectorAll('[data-rp-pane]').forEach(function (p) {
                    p.hidden = p.getAttribute('data-rp-pane') !== pb;
                });
                var tablolar = $.fn.dataTable.tables({ visible: true, api: true });
                tablolar.columns.adjust();
                if (tablolar.responsive) tablolar.responsive.recalc();
            });
        });
    });
})();
</script>
JS;
}
