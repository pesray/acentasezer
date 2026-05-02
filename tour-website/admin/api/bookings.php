<?php
/**
 * Bookings API - AJAX CRUD işlemleri
 */

// Yolcuları kaydet (insert) — booking_id ve booking_number gerekli
function savePassengers($db, $bookingId, $bookingNumber, $adultNames, $childNames) {
    $db->prepare("DELETE FROM booking_passengers WHERE booking_id = ?")->execute([$bookingId]);
    $ins = $db->prepare("INSERT INTO booking_passengers (booking_id, booking_number, passenger_type, full_name, sort_order) VALUES (?, ?, ?, ?, ?)");
    $i = 0;
    foreach ($adultNames as $name) {
        $name = trim($name);
        if ($name === '') continue;
        $ins->execute([$bookingId, $bookingNumber, 'adult', $name, $i++]);
    }
    $i = 0;
    foreach ($childNames as $name) {
        $name = trim($name);
        if ($name === '') continue;
        $ins->execute([$bookingId, $bookingNumber, 'child', $name, $i++]);
    }
}

switch ($action) {

    // === Rezervasyon Oluştur (Manuel) ===
    case 'create':
        try {
            $destinationId = (int)($_POST['destination_id'] ?? 0);
            $vehicleId     = (int)($_POST['vehicle_id'] ?? 0);
            $hasOutbound   = !empty($_POST['has_outbound']);
            $hasReturn     = !empty($_POST['has_return']);
            // En az biri olmak zorunda; hiçbiri yoksa varsayılan geliş
            if (!$hasOutbound && !$hasReturn) $hasOutbound = true;

            $insertBooking = $db->prepare("
                INSERT INTO bookings (
                    booking_number, booking_type, booking_status, booking_direction,
                    destination_id, vehicle_id,
                    customer_name, customer_email, customer_phone,
                    flight_date, flight_time, flight_number, hotel_address, pickup_time,
                    adults, children, child_seat,
                    total_price, currency,
                    notes, admin_notes, created_at
                ) VALUES (
                    ?, 'transfer', 'confirmed', ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, NOW()
                )
            ");

            $commonFields = [
                $destinationId ?: null, $vehicleId ?: null,
                trim($_POST['customer_name'] ?? ''),
                trim($_POST['customer_email'] ?? ''),
                trim($_POST['customer_phone'] ?? ''),
            ];
            $passengerFields = [
                (int)($_POST['adults'] ?? 1),
                (int)($_POST['children'] ?? 0),
                (int)($_POST['child_seat'] ?? 0),
            ];
            $noteFields = [
                trim($_POST['notes'] ?? '') ?: null,
                trim($_POST['admin_notes'] ?? '') ?: null,
            ];

            $adultNames = $_POST['passenger_adult_name'] ?? [];
            $childNames = $_POST['passenger_child_name'] ?? [];
            $messages   = [];

            // Geliş kaydı (isteğe bağlı)
            if ($hasOutbound) {
                $numOutbound = 'TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $insertBooking->execute(array_merge(
                    [$numOutbound, 'outbound'],
                    $commonFields,
                    [
                        ($_POST['flight_date'] ?? '') ?: null,
                        ($_POST['flight_time'] ?? '') ?: null,
                        trim($_POST['flight_number'] ?? '') ?: null,
                        trim($_POST['hotel_address'] ?? '') ?: null,
                        null, // pickup_time geliş için yok
                    ],
                    $passengerFields,
                    [(float)($_POST['total_price'] ?? 0), trim($_POST['currency'] ?? 'TRY')],
                    $noteFields
                ));
                $outboundId = (int)$db->lastInsertId();
                savePassengers($db, $outboundId, $numOutbound, $adultNames, $childNames);
                $messages[] = 'Geliş: #' . $numOutbound;
            }

            // Dönüş kaydı (isteğe bağlı)
            if ($hasReturn) {
                $numReturn = 'TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $insertBooking->execute(array_merge(
                    [$numReturn, 'return'],
                    $commonFields,
                    [
                        ($_POST['return_flight_date'] ?? '') ?: null,
                        ($_POST['return_flight_time'] ?? '') ?: null,
                        trim($_POST['return_flight_number'] ?? '') ?: null,
                        trim($_POST['return_hotel_address'] ?? '') ?: null,
                        ($_POST['return_pickup_time'] ?? '') ?: null,
                    ],
                    $passengerFields,
                    [(float)($_POST['return_total_price'] ?? 0), trim($_POST['return_currency'] ?? $_POST['currency'] ?? 'TRY')],
                    $noteFields
                ));
                $returnId = (int)$db->lastInsertId();
                savePassengers($db, $returnId, $numReturn, $adultNames, $childNames);
                $messages[] = 'Dönüş: #' . $numReturn;
            }

            jsonResponse(true, 'Rezervasyon oluşturuldu — ' . implode(' | ', $messages));
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Rezervasyon Güncelle ===
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $stmt = $db->prepare("
                UPDATE bookings SET
                    destination_id = ?, vehicle_id = ?,
                    customer_name = ?, customer_email = ?, customer_phone = ?,
                    pickup_location = ?, pickup_date = ?, pickup_time = ?, return_time = ?,
                    flight_date = ?, flight_time = ?, flight_number = ?, hotel_address = ?,
                    adults = ?, children = ?, child_seat = ?,
                    total_price = ?, currency = ?,
                    notes = ?, admin_notes = ?,
                    booking_status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                ((int)($_POST['destination_id'] ?? 0)) ?: null,
                ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                trim($_POST['customer_name'] ?? ''),
                trim($_POST['customer_email'] ?? ''),
                trim($_POST['customer_phone'] ?? ''),
                trim($_POST['pickup_location'] ?? '') ?: null,
                ($_POST['pickup_date'] ?? '') ?: null,
                ($_POST['pickup_time'] ?? '') ?: null,
                ($_POST['return_time'] ?? '') ?: null,
                ($_POST['flight_date'] ?? '') ?: null,
                ($_POST['flight_time'] ?? '') ?: null,
                trim($_POST['flight_number'] ?? '') ?: null,
                trim($_POST['hotel_address'] ?? '') ?: null,
                (int)($_POST['adults'] ?? 1),
                (int)($_POST['children'] ?? 0),
                (int)($_POST['child_seat'] ?? 0),
                (float)($_POST['total_price'] ?? 0),
                trim($_POST['currency'] ?? 'TRY'),
                trim($_POST['notes'] ?? '') ?: null,
                trim($_POST['admin_notes'] ?? '') ?: null,
                $_POST['booking_status'] ?? 'pending',
                $id
            ]);
            // Yolcuları güncelle
            $numStmt = $db->prepare("SELECT booking_number FROM bookings WHERE id = ?");
            $numStmt->execute([$id]);
            $bookingNum = $numStmt->fetchColumn() ?: '';
            $adultNames = $_POST['passenger_adult_name'] ?? [];
            $childNames = $_POST['passenger_child_name'] ?? [];
            savePassengers($db, $id, $bookingNum, $adultNames, $childNames);

            jsonResponse(true, 'Rezervasyon güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Rezervasyon Sil ===
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }

            jsonResponse(true, 'Rezervasyon silindi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;

    // === Hızlı Durum Güncelle ===
    case 'quick_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'confirmed', 'cancelled'];
        if (!$id || !in_array($status, $validStatuses)) {
            jsonResponse(false, 'Geçersiz parametreler');
        }

        try {
            $db->prepare("UPDATE bookings SET booking_status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $id]);
            $statusTexts = ['pending' => 'Onay Bekliyor', 'confirmed' => 'Onaylandı', 'cancelled' => 'İptal Edildi'];
            jsonResponse(true, 'Durum güncellendi: ' . $statusTexts[$status]);
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;

    // === Okundu İşaretle ===
    case 'mark_read':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->prepare("UPDATE bookings SET is_read = 1 WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Okundu olarak işaretlendi');
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;

    // === Yolcuları Getir ===
    case 'get_passengers':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("SELECT passenger_type, full_name, sort_order FROM booking_passengers WHERE booking_id = ? ORDER BY passenger_type DESC, sort_order ASC");
            $stmt->execute([$id]);
            jsonResponse(true, 'OK', ['passengers' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Operasyonel Alan Güncelle ===
    case 'update_ops':
        $id    = (int)($_POST['id']    ?? 0);
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        $allowed = ['is_completed', 'is_outsourced', 'outsource_price', 'outsource_currency', 'outsource_name', 'outsource_partner_id'];
        if (!$id || !in_array($field, $allowed)) jsonResponse(false, 'Geçersiz parametre');

        try {
            if ($field === 'outsource_partner_id') {
                $val = ($value === '' || $value === '0') ? null : (int)$value;
            } elseif (in_array($field, ['outsource_price', 'outsource_name', 'outsource_currency'])) {
                $val = $value === '' ? null : ($field === 'outsource_price' ? (float)$value : $value);
            } else {
                $val = (int)(bool)$value;
            }
            $db->prepare("UPDATE bookings SET $field = ?, updated_at = NOW() WHERE id = ?")->execute([$val, $id]);
            jsonResponse(true, 'Güncellendi');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Dışarıya Verme Fiyat/Birim Güncelle ===
    case 'update_outsource_pricing':
        $id       = (int)($_POST['id'] ?? 0);
        $price    = ($_POST['outsource_price'] ?? '') !== '' ? (float)$_POST['outsource_price'] : null;
        $currency = trim($_POST['outsource_currency'] ?? '') ?: null;
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("UPDATE bookings SET outsource_price = ?, outsource_currency = ?, updated_at = NOW() WHERE id = ?")
               ->execute([$price, $currency, $id]);
            jsonResponse(true, 'Kaydedildi');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Dışarıya Verme Temizle ===
    case 'clear_outsource':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("
                UPDATE bookings
                SET is_outsourced = 0,
                    outsource_name = NULL,
                    outsource_partner_id = NULL,
                    outsource_price = NULL,
                    outsource_currency = NULL,
                    outsource_pickup_time = NULL,
                    updated_at = NOW()
                WHERE id = ?
            ")->execute([$id]);
            jsonResponse(true, 'Temizlendi');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Dışarıya Verme Detaylarını Kaydet ===
    case 'save_outsource':
        $id         = (int)($_POST['id'] ?? 0);
        $name       = trim($_POST['outsource_name'] ?? '');
        $partnerId  = ($_POST['outsource_partner_id'] ?? '') !== '' ? (int)$_POST['outsource_partner_id'] : null;
        $price      = ($_POST['outsource_price'] ?? '') !== '' ? (float)$_POST['outsource_price'] : null;
        $currency   = trim($_POST['outsource_currency'] ?? '') ?: null;
        $pickupTime = ($_POST['outsource_pickup_time'] ?? '') ?: null;
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("
                UPDATE bookings
                SET is_outsourced = 1,
                    outsource_partner_id = ?,
                    outsource_name = ?,
                    outsource_price = ?,
                    outsource_currency = COALESCE(?, outsource_currency, currency),
                    outsource_pickup_time = COALESCE(?, outsource_pickup_time),
                    updated_at = NOW()
                WHERE id = ?
            ")->execute([$partnerId, $name ?: null, $price, $currency, $pickupTime, $id]);
            jsonResponse(true, 'Kaydedildi.', ['outsource_name' => $name, 'outsource_price' => $price, 'outsource_currency' => $currency]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === İş Durumunu Getir ===
    case 'get_ops':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("
                SELECT b.is_completed, b.is_outsourced, b.outsource_name, b.outsource_price,
                       b.outsource_currency, b.currency, b.outsource_pickup_time,
                       op.phone AS outsource_phone,
                       d.id AS driver_id, d.name AS driver_name, d.surname AS driver_surname
                FROM bookings b
                LEFT JOIN outsource_partners op ON (
                    (b.outsource_partner_id IS NOT NULL AND op.id = b.outsource_partner_id)
                    OR
                    (b.outsource_partner_id IS NULL AND op.name = b.outsource_name AND op.is_active = 1)
                )
                LEFT JOIN booking_drivers bd ON b.id = bd.booking_id
                LEFT JOIN drivers d ON bd.driver_id = d.id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) jsonResponse(false, 'Bulunamadı');
            jsonResponse(true, 'OK', [
                'id'                    => (int)$id,
                'is_completed'          => (int)$row['is_completed'],
                'is_outsourced'         => (int)$row['is_outsourced'],
                'outsource_name'        => $row['outsource_name'],
                'outsource_phone'       => $row['outsource_phone'],
                'outsource_price'       => $row['outsource_price'],
                'outsource_currency'    => $row['outsource_currency'] ?: ($row['currency'] ?: 'TRY'),
                'outsource_pickup_time' => $row['outsource_pickup_time'],
                'driver_id'             => (int)($row['driver_id'] ?? 0),
                'driver_name'           => $row['driver_name'] ?? '',
                'driver_surname'        => $row['driver_surname'] ?? '',
            ]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'list_drivers':
        try {
            $stmt = $db->prepare("
                SELECT d.id, d.name, d.surname, d.phone, d.plate, v.brand, v.model
                FROM drivers d
                LEFT JOIN vehicles v ON d.vehicle_id = v.id
                ORDER BY d.name, d.surname
            ");
            $stmt->execute();
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(true, 'OK', ['drivers' => $drivers]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'set_driver':
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $driverId = (int)($_POST['driver_id'] ?? 0);
        if (!$bookingId) jsonResponse(false, 'Geçersiz Booking ID');
        try {
            $db->beginTransaction();
            // Mevcut şöförü sil
            $db->prepare("DELETE FROM booking_drivers WHERE booking_id = ?")->execute([$bookingId]);
            // Yeni şöförü ekle (boş değilse)
            if ($driverId > 0) {
                $stmt = $db->prepare("INSERT INTO booking_drivers (booking_id, driver_id) VALUES (?, ?)");
                $stmt->execute([$bookingId, $driverId]);
            }
            $db->commit();
            jsonResponse(true, 'Şöför kaydedildi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
