<?php
/**
 * Bookings API - AJAX CRUD işlemleri
 */

switch ($action) {

    // === Rezervasyon Oluştur (Manuel) ===
    case 'create':
        try {
            $destinationId = (int)($_POST['destination_id'] ?? 0);
            $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
            $bookingDirection = ($_POST['booking_direction'] ?? 'outbound') === 'return' ? 'return' : 'outbound';

            $prefix = 'TRF';
            $bookingNumber = $prefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("
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
            $stmt->execute([
                $bookingNumber, $bookingDirection,
                $destinationId ?: null, $vehicleId ?: null,
                trim($_POST['customer_name'] ?? ''),
                trim($_POST['customer_email'] ?? ''),
                trim($_POST['customer_phone'] ?? ''),
                ($_POST['flight_date'] ?? '') ?: null,
                ($_POST['flight_time'] ?? '') ?: null,
                trim($_POST['flight_number'] ?? '') ?: null,
                trim($_POST['hotel_address'] ?? '') ?: null,
                ($_POST['pickup_time'] ?? '') ?: null,
                (int)($_POST['adults'] ?? 1),
                (int)($_POST['children'] ?? 0),
                (int)($_POST['child_seat'] ?? 0),
                (float)($_POST['total_price'] ?? 0),
                trim($_POST['currency'] ?? 'TRY'),
                trim($_POST['notes'] ?? '') ?: null,
                trim($_POST['admin_notes'] ?? '') ?: null,
            ]);
            jsonResponse(true, 'Rezervasyon oluşturuldu: #' . $bookingNumber, ['booking_number' => $bookingNumber]);
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

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
