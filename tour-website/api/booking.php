<?php
/**
 * Rezervasyon API
 *
 * 1) Yeni transfer sihirbazı (booking_type=transfer + trip): fiyat sunucuda hesaplanır,
 *    müşteri kaydı bulunur/oluşturulur, geliş/dönüş ayrı kayıt olarak açılıp birbirine bağlanır,
 *    yolcu isimleri booking_passengers tablosuna yazılır (yönetim panelindeki mantıkla aynı).
 * 2) Eski formlar (tur rezervasyonu vb.): önceki davranış aynen korunur.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

function sl_api_out($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['success' => (bool)$ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function sl_api_date($s) {
    $s = trim((string)$s);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) return null;
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]) ? $s : null;
}

function sl_api_time($s) {
    $s = trim((string)$s);
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $s)) return null;
    return substr($s, 0, 5);
}

function sl_api_str($s, $max) {
    $s = strip_tags((string)$s);
    if (!mb_check_encoding($s, 'UTF-8')) $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8'); // bozuk baytları temizle
    $r = preg_replace('/\s+/u', ' ', $s);
    return mb_substr(trim($r === null ? $s : $r), 0, $max);
}

function sl_api_booking_number($db) {
    for ($i = 0; $i < 25; $i++) {
        $n = 'TRF-' . date('Ymd') . '-' . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $st = $db->prepare("SELECT 1 FROM bookings WHERE booking_number = ? LIMIT 1");
        $st->execute([$n]);
        if (!$st->fetchColumn()) return $n;
    }
    return 'TRF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
}

/** Basit kötüye kullanım koruması: oturum + IP başına sınır */
function sl_api_rate_ok() {
    $now = time();
    $sess = isset($_SESSION['sl_bk']) && is_array($_SESSION['sl_bk']) ? $_SESSION['sl_bk'] : [];
    $sess = array_values(array_filter($sess, function ($t) use ($now) { return $t > $now - 600; }));
    if (count($sess) >= 5) return false;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0';
    $file = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sl_bk_' . md5($ip) . '.json';
    $hits = [];
    if (is_file($file)) {
        $hits = json_decode((string)@file_get_contents($file), true);
        if (!is_array($hits)) $hits = [];
    }
    $hits = array_values(array_filter($hits, function ($t) use ($now) { return $t > $now - 3600; }));
    if (count($hits) >= 12) return false;

    $sess[] = $now;
    $_SESSION['sl_bk'] = $sess;
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits));
    return true;
}

$bookingType = trim($_POST['booking_type'] ?? 'tour');

/* ======================================================================
 * 1) Yeni transfer sihirbazı
 * ====================================================================== */
if ($bookingType === 'transfer' && isset($_POST['trip'])) {
    $activeLangs = array_column(getActiveLanguages(), 'code');
    $lang = in_array($_POST['lang'] ?? '', $activeLangs, true) ? $_POST['lang'] : sl_default_lang_code();
    $t = function ($k, $v = []) use ($lang) { return sl_t($k, $v, $lang); };

    // Bot koruması
    if (trim((string)($_POST['website'] ?? '')) !== '' || (int)($_POST['elapsed'] ?? 0) < 4) {
        sl_api_out(false, $t('err_spam'));
    }
    if (!sl_api_rate_ok()) {
        sl_api_out(false, $t('err_rate'));
    }

    try {
        $db = getDB();

        $destId = (int)($_POST['destination_id'] ?? 0);
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        $trip = $_POST['trip'] ?? '';
        if (!in_array($trip, ['roundtrip', 'arrival', 'departure'], true)) sl_api_out(false, $t('err_required'));

        $st = $db->prepare("SELECT id, title FROM destinations WHERE id = ? AND status = 'published' LIMIT 1");
        $st->execute([$destId]);
        $dest = $st->fetch();
        $st = $db->prepare("SELECT * FROM vehicles WHERE id = ? AND is_active = 1 LIMIT 1");
        $st->execute([$vehicleId]);
        $veh = $st->fetch();
        if (!$dest || !$veh) sl_api_out(false, $t('err_vehicle'));

        // Fiyat: dile göre değil para birimine göre. Sitede tek (varsayılan) para birimi listelenir;
        // müşteri onay adımında "araçta nakit" ödeme para birimini seçer (Transferler ekranında girilen fiyatlardan).
        $matrix = sl_dest_price_matrix($destId);
        $dispCur = sl_pick_currency($matrix);
        $allowed = sl_pay_currencies();
        if ($dispCur !== '' && !in_array($dispCur, $allowed, true)) $allowed[] = $dispCur;
        $currency = strtoupper(trim((string)($_POST['pay_currency'] ?? '')));
        if (!in_array($currency, $allowed, true) || empty($matrix[$currency][$vehicleId])) $currency = '';
        if ($currency === '') {
            foreach ([sl_currency(), $dispCur] as $c) {
                if ($c !== '' && !empty($matrix[$c][$vehicleId])) { $currency = $c; break; }
            }
        }
        if ($currency === '') {
            foreach ($matrix as $c => $list) {
                if (!empty($list[$vehicleId])) { $currency = $c; break; }
            }
        }
        if ($currency === '') sl_api_out(false, $t('err_vehicle'));
        $legPrice = (float)$matrix[$currency][$vehicleId];

        // Uçuş numarası: zorunlu, büyük harf (ör. TK 2412)
        $flightNorm = function ($s) {
            $s = preg_replace('/[^A-Z0-9 \-]/', '', strtoupper(trim((string)$s)));
            return trim(preg_replace('/\s+/', ' ', $s));
        };
        $flightOk = function ($s) {
            return (bool)preg_match('/^(?=.*\d)[A-Z0-9]{3,8}$/', preg_replace('/[\s\-]+/', '', $s));
        };
        // Yolcu isimleri büyük harf (Türkçede i → İ)
        $upper = function ($s) use ($lang) {
            if ($lang === 'tr') $s = str_replace(['i', 'ı'], ['İ', 'I'], $s);
            return mb_strtoupper($s, 'UTF-8');
        };

        // Transfer bilgileri
        $needA = $trip !== 'departure';
        $needD = $trip !== 'arrival';
        $today = date('Y-m-d');

        $aDate = $aTime = $aFlight = $aHotel = null;
        if ($needA) {
            $aDate = sl_api_date($_POST['arr_date'] ?? '');
            $aTime = sl_api_time($_POST['arr_time'] ?? '');
            $aFlight = $flightNorm(sl_api_str($_POST['arr_flight'] ?? '', 20));
            $aHotel = sl_api_str($_POST['arr_hotel'] ?? '', 250);
            if (!$aDate || !$aTime || $aHotel === '' || $aFlight === '') sl_api_out(false, $t('err_required'));
            if (!$flightOk($aFlight)) sl_api_out(false, $t('err_flight'));
            if ($aDate < $today) sl_api_out(false, $t('err_date'));
        }
        $dDate = $dTime = $dFlight = $dHotel = $dPick = null;
        if ($needD) {
            $dDate = sl_api_date($_POST['dep_date'] ?? '');
            $dTime = sl_api_time($_POST['dep_time'] ?? '');
            $dFlight = $flightNorm(sl_api_str($_POST['dep_flight'] ?? '', 20));
            $dHotel = sl_api_str($_POST['dep_hotel'] ?? '', 250);
            $dPick = sl_api_time($_POST['dep_pickup'] ?? '');
            if (!$dDate || !$dTime || $dHotel === '' || $dFlight === '') sl_api_out(false, $t('err_required'));
            if (!$flightOk($dFlight)) sl_api_out(false, $t('err_flight'));
            if ($dDate < $today) sl_api_out(false, $t('err_date'));
            if ($needA && $dDate < $aDate) sl_api_out(false, $t('err_order'));
        }

        // Yolcular
        $cap = max(1, (int)$veh['capacity']);
        $adults = (int)($_POST['adults'] ?? 1);
        $children = max(0, (int)($_POST['children'] ?? 0));
        $childSeat = max(0, (int)($_POST['child_seat'] ?? 0));
        if ($adults < 1 || $adults + $children > $cap) sl_api_out(false, $t('err_capacity'));
        $childSeat = min($childSeat, $children, max(0, (int)$veh['child_seat_capacity']));
        $booster = min(20, max(0, (int)($_POST['booster'] ?? 0))); // yükseltici: araç kapasitesiyle sınırlı değil

        // Çocuk koltuğu / yükseltici ek ücreti (toplam 1 adedi ücretsiz; Anasayfa & Tema > Genel > Fiyat & Ödeme)
        $fees = sl_fees();
        $feeSeat = sl_fee_in('seat', $currency, $matrix, $vehicleId, $fees);
        $feeBooster = sl_fee_in('booster', $currency, $matrix, $vehicleId, $fees);
        $ex = sl_extras_amount($childSeat, $booster, $feeSeat, $feeBooster, $fees['free']);
        $extraAmt = (float)$ex['amt'];

        // İletişim
        $name = sl_api_str($_POST['full_name'] ?? '', 120);
        $phone = sl_api_str($_POST['phone'] ?? '', 40);
        $email = sl_api_str($_POST['email'] ?? '', 150);
        if (mb_strlen($name) < 2) sl_api_out(false, $t('err_required'));
        if (strlen(preg_replace('/\D+/', '', $phone)) < 7) sl_api_out(false, $t('err_phone'));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) sl_api_out(false, $t('err_email'));

        $adultNames = [];
        $postedAdults = isset($_POST['pax_adult']) && is_array($_POST['pax_adult']) ? array_values($_POST['pax_adult']) : [];
        for ($i = 0; $i < $adults; $i++) {
            $n = sl_api_str($postedAdults[$i] ?? '', 120);
            if ($i === 0 && $n === '') $n = $name;
            if (mb_strlen($n) < 2) sl_api_out(false, $t('err_names'));
            $adultNames[] = $upper($n);
        }
        $childNames = [];
        $postedChildren = isset($_POST['pax_child']) && is_array($_POST['pax_child']) ? array_values($_POST['pax_child']) : [];
        for ($i = 0; $i < $children; $i++) {
            $n = sl_api_str($postedChildren[$i] ?? '', 120);
            if ($n !== '') $childNames[] = $upper($n);
        }

        // Müşteri notu; yükseltici bilgisi başa eklenir (sürücü / WhatsApp şablonlarındaki {NOTES} ile görünür)
        $notes = trim(mb_substr(strip_tags((string)($_POST['notes'] ?? '')), 0, 1000));
        if ($booster > 0) $notes = 'YÜKSELTİCİ: ' . $booster . ($notes !== '' ? "\n" . $notes : '');
        $notes = $notes !== '' ? $notes : null;

        $money = function ($v) { return rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.'); };
        $adminNote = 'Web sitesi rezervasyonu (' . strtoupper($lang) . ') · Ödeme: araçta nakit ' . $currency;
        if ($childSeat + $booster > 0) {
            $parts = [];
            if ($childSeat) $parts[] = $childSeat . ' çocuk koltuğu';
            if ($booster) $parts[] = $booster . ' yükseltici';
            $adminNote .= ' · Ekstra: ' . implode(' + ', $parts) . ' = '
                . ($extraAmt > 0
                    ? $money($extraAmt) . ' ' . $currency . ($fees['perLeg'] ? ' (transfer başına)' : ' (rezervasyon başına)')
                    : 'ücretsiz');
        }

        // Varsayılan havalimanı (Tur Yönetimi > Havalimanları)
        $airportId = null;
        try {
            $airportId = $db->query("SELECT id FROM airports WHERE is_default = 1 AND is_deleted = 0 ORDER BY id LIMIT 1")->fetchColumn() ?: null;
        } catch (Exception $e) {
            $airportId = null;
        }

        $db->beginTransaction();

        // Müşteri kaydı (yönetim paneliyle aynı eşleştirme: ad + telefon)
        $customerId = null;
        try {
            $st = $db->prepare("SELECT id FROM customers WHERE name = ? AND phone = ? LIMIT 1");
            $st->execute([$name, $phone]);
            $customerId = $st->fetchColumn();
            if (!$customerId) {
                $st = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                $st->execute([$name, $email !== '' ? $email : null, $phone]);
                $customerId = (int)$db->lastInsertId();
            } elseif ($email !== '') {
                $db->prepare("UPDATE customers SET email = ? WHERE id = ? AND (email IS NULL OR email = '')")->execute([$email, $customerId]);
            }
        } catch (Exception $e) {
            $customerId = null;
        }

        $ins = $db->prepare("
            INSERT INTO bookings (
                booking_number, booking_type, booking_status, booking_direction,
                airport_id, destination_id, vehicle_id,
                customer_name, customer_email, customer_phone,
                flight_date, flight_time, flight_number, hotel_address, pickup_time,
                adults, children, child_seat,
                total_price, currency,
                notes, admin_notes, created_at, customer_id
            ) VALUES (
                ?, 'transfer', 'pending', ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, ?, NOW(), ?
            )
        ");
        $paxIns = $db->prepare("INSERT INTO booking_passengers (booking_id, booking_number, passenger_type, full_name, sort_order) VALUES (?, ?, ?, ?, ?)");

        $created = [];
        $legs = [];
        if ($needA) $legs[] = ['outbound', $aDate, $aTime, $aFlight, $aHotel, null];
        if ($needD) $legs[] = ['return', $dDate, $dTime, $dFlight, $dHotel, $dPick];

        $grandTotal = 0.0;
        foreach ($legs as $li => $leg) {
            $num = sl_api_booking_number($db);
            // Ek koltuk ücreti: transfer başına her kayda, rezervasyon başına yalnız ilk kayda eklenir
            $rowTotal = $legPrice + ($fees['perLeg'] || $li === 0 ? $extraAmt : 0);
            $grandTotal += $rowTotal;
            $ins->execute([
                $num, $leg[0],
                $airportId, $destId, $vehicleId,
                $name, $email, $phone,
                $leg[1], $leg[2], $leg[3], $leg[4], $leg[5],
                $adults, $children, $childSeat,
                $rowTotal, $currency,
                $notes, $adminNote, $customerId ?: null,
            ]);
            $bid = (int)$db->lastInsertId();
            foreach ($adultNames as $i => $n) $paxIns->execute([$bid, $num, 'adult', $n, $i]);
            foreach ($childNames as $i => $n) $paxIns->execute([$bid, $num, 'child', $n, $i]);
            $created[] = ['id' => $bid, 'no' => $num];
        }

        // Geliş + dönüş kayıtlarını birbirine bağla (yönetim panelindeki gibi)
        if (count($created) === 2) {
            try {
                $link = $db->prepare("UPDATE bookings SET related_booking_id = ? WHERE id = ?");
                $link->execute([$created[1]['id'], $created[0]['id']]);
                $link->execute([$created[0]['id'], $created[1]['id']]);
            } catch (Exception $e) {}
        }

        $db->commit();

        $numbers = array_column($created, 'no');
        $wa = sl_wa_link($t('wa_msg', ['no' => implode(', ', $numbers)]));
        sl_api_out(true, $t('ok2'), [
            'booking_numbers' => $numbers,
            'total' => round($grandTotal, 2),
            'currency' => $currency,
            'wa_url' => $wa,
        ]);
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        error_log('[booking] ' . $e->getMessage());
        http_response_code(500);
        sl_api_out(false, $t('err_generic'));
    }
}

/* ======================================================================
 * 2) Eski formlar (tur rezervasyonu vb.) — önceki davranış
 * ====================================================================== */
try {
    $db = getDB();

    if (!in_array($bookingType, ['tour', 'transfer'])) {
        $bookingType = 'tour';
    }

    $customerName = trim($_POST['full_name'] ?? '');
    $customerEmail = trim($_POST['email'] ?? '');
    $customerPhone = trim($_POST['phone'] ?? '');

    if (empty($customerName) || empty($customerEmail)) {
        echo json_encode(['success' => false, 'message' => 'Ad Soyad ve E-posta zorunludur.']);
        exit;
    }
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir e-posta adresi girin.']);
        exit;
    }

    $prefix = $bookingType === 'tour' ? 'TUR' : 'TRF';
    $bookingNumber = $prefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $tourId = !empty($_POST['tour_id']) ? (int)$_POST['tour_id'] : null;
    $destinationId = !empty($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : null;
    $vehicleId = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;

    $pickupLocation = trim($_POST['pickup_location'] ?? '') ?: null;
    $pickupDate = !empty($_POST['pickup_date']) ? $_POST['pickup_date'] : null;
    $pickupTime = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : null;
    $returnTime = !empty($_POST['return_time']) ? $_POST['return_time'] : null;

    $flightDate = !empty($_POST['flight_date']) ? $_POST['flight_date'] : null;
    $flightTime = !empty($_POST['flight_time']) ? $_POST['flight_time'] : null;
    $flightNumber = trim($_POST['flight_number'] ?? '') ?: null;
    $hotelAddress = trim($_POST['hotel_address'] ?? '') ?: null;

    $returnTransfer = !empty($_POST['return_transfer']) ? 1 : 0;
    $returnFlightDate = !empty($_POST['return_flight_date']) ? $_POST['return_flight_date'] : null;
    $returnFlightTime = !empty($_POST['return_flight_time']) ? $_POST['return_flight_time'] : null;
    $returnFlightNumber = trim($_POST['return_flight_number'] ?? '') ?: null;
    $returnPickupTime = !empty($_POST['return_pickup_time']) ? $_POST['return_pickup_time'] : null;
    $returnHotelAddress = trim($_POST['return_hotel_address'] ?? '') ?: null;

    $adults = max(1, (int)($_POST['adults'] ?? $_POST['passengers'] ?? 1));
    $children = max(0, (int)($_POST['children'] ?? 0));
    $childSeat = max(0, (int)($_POST['child_seat'] ?? 0));

    $totalPrice = (float)($_POST['total_price'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'TRY');
    if ($returnTransfer && $totalPrice > 0) {
        $totalPrice = $totalPrice / 2;
    }

    $notes = trim($_POST['notes'] ?? '') ?: null;

    $sql = "
        INSERT INTO bookings (
            booking_number, booking_type, booking_status, booking_direction,
            tour_id, destination_id, vehicle_id,
            customer_name, customer_email, customer_phone,
            pickup_location, pickup_date, pickup_time, return_time,
            flight_date, flight_time, flight_number, hotel_address,
            return_transfer, return_flight_date, return_flight_time, return_flight_number, return_pickup_time, return_hotel_address,
            adults, children, child_seat,
            total_price, currency,
            notes, created_at
        ) VALUES (
            ?, ?, 'pending', ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, NOW()
        )
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        $bookingNumber, $bookingType, 'outbound',
        $tourId, $destinationId, $vehicleId,
        $customerName, $customerEmail, $customerPhone,
        $pickupLocation, $pickupDate, $pickupTime, $returnTime,
        $flightDate, $flightTime, $flightNumber, $hotelAddress,
        $returnTransfer, $returnFlightDate, $returnFlightTime, $returnFlightNumber, $returnPickupTime, $returnHotelAddress,
        $adults, $children, $childSeat,
        $totalPrice, $currency,
        $notes
    ]);

    $bookingId = $db->lastInsertId();
    $returnBookingNumber = null;

    if ($returnTransfer && $returnFlightDate) {
        $returnBookingNumber = $prefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmtReturn = $db->prepare($sql);
        $stmtReturn->execute([
            $returnBookingNumber, $bookingType, 'return',
            $tourId, $destinationId, $vehicleId,
            $customerName, $customerEmail, $customerPhone,
            $returnHotelAddress, $returnFlightDate, $returnPickupTime, null,
            $returnFlightDate, $returnFlightTime, $returnFlightNumber, $hotelAddress,
            0, null, null, null, null, null,
            $adults, $children, $childSeat,
            $totalPrice, $currency,
            $notes
        ]);
    }

    $response = [
        'success' => true,
        'booking_id' => $bookingId,
        'booking_number' => $bookingNumber,
        'message' => 'Rezervasyonunuz başarıyla alındı!'
    ];
    if ($returnBookingNumber) {
        $response['return_booking_number'] = $returnBookingNumber;
        $response['message'] = 'Gidiş ve dönüş rezervasyonlarınız başarıyla alındı!';
    }
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}
