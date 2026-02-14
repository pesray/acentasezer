<?php
/**
 * Rezervasyon API Endpoint
 * POST: Yeni rezervasyon oluştur
 */

require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Sadece POST kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = getDB();
    
    // Form verilerini al
    $bookingType = trim($_POST['booking_type'] ?? 'tour');
    if (!in_array($bookingType, ['tour', 'transfer'])) {
        $bookingType = 'tour';
    }
    
    $customerName = trim($_POST['full_name'] ?? '');
    $customerEmail = trim($_POST['email'] ?? '');
    $customerPhone = trim($_POST['phone'] ?? '');
    
    // Validasyon
    if (empty($customerName) || empty($customerEmail)) {
        echo json_encode(['success' => false, 'message' => 'Ad Soyad ve E-posta zorunludur.']);
        exit;
    }
    
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir e-posta adresi girin.']);
        exit;
    }
    
    // Benzersiz rezervasyon numarası
    $prefix = $bookingType === 'tour' ? 'TUR' : 'TRF';
    $bookingNumber = $prefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Referanslar
    $tourId = !empty($_POST['tour_id']) ? (int)$_POST['tour_id'] : null;
    $destinationId = !empty($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : null;
    $vehicleId = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
    
    // Tur alanları
    $pickupLocation = trim($_POST['pickup_location'] ?? '') ?: null;
    $pickupDate = !empty($_POST['pickup_date']) ? $_POST['pickup_date'] : null;
    $pickupTime = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : null;
    $returnTime = !empty($_POST['return_time']) ? $_POST['return_time'] : null;
    
    // Transfer alanları
    $flightDate = !empty($_POST['flight_date']) ? $_POST['flight_date'] : null;
    $flightTime = !empty($_POST['flight_time']) ? $_POST['flight_time'] : null;
    $flightNumber = trim($_POST['flight_number'] ?? '') ?: null;
    $hotelAddress = trim($_POST['hotel_address'] ?? '') ?: null;
    
    // Dönüş transferi
    $returnTransfer = !empty($_POST['return_transfer']) ? 1 : 0;
    $returnFlightDate = !empty($_POST['return_flight_date']) ? $_POST['return_flight_date'] : null;
    $returnFlightTime = !empty($_POST['return_flight_time']) ? $_POST['return_flight_time'] : null;
    $returnFlightNumber = trim($_POST['return_flight_number'] ?? '') ?: null;
    $returnPickupTime = !empty($_POST['return_pickup_time']) ? $_POST['return_pickup_time'] : null;
    $returnHotelAddress = trim($_POST['return_hotel_address'] ?? '') ?: null;
    
    // Yolcu bilgileri
    $adults = max(1, (int)($_POST['adults'] ?? $_POST['passengers'] ?? 1));
    $children = max(0, (int)($_POST['children'] ?? 0));
    $childSeat = max(0, (int)($_POST['child_seat'] ?? 0));
    
    // Fiyat - dönüş transferi varsa toplam fiyatı 2'ye böl (her rezervasyona yarısı)
    $totalPrice = (float)($_POST['total_price'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'TRY');
    if ($returnTransfer && $totalPrice > 0) {
        $totalPrice = $totalPrice / 2;
    }
    
    // Notlar
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
    
    // Dönüş transferi seçildiyse 2. rezervasyon oluştur
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
