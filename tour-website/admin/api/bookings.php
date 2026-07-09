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

            $cName = trim($_POST['customer_name'] ?? '');
            $cEmail = trim($_POST['customer_email'] ?? '');
            $cPhone = trim($_POST['customer_phone'] ?? '') ?: null;
            $customerId = (int)($_POST['customer_id'] ?? 0);

            if (!$customerId && $cName !== '') {
                // Müşteri ara (boş/null telefonları doğru eşleştirmek için)
                if ($cPhone === null || $cPhone === '') {
                    $stmtCheck = $db->prepare("SELECT id FROM customers WHERE name = ? AND (phone IS NULL OR phone = '')");
                    $stmtCheck->execute([$cName]);
                } else {
                    $stmtCheck = $db->prepare("SELECT id FROM customers WHERE name = ? AND phone = ?");
                    $stmtCheck->execute([$cName, $cPhone]);
                }
                $existingCust = $stmtCheck->fetch();
                if ($existingCust) {
                    $customerId = (int)$existingCust['id'];
                } else {
                    // Müşteri oluştur
                    $stmtInsertCust = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                    $stmtInsertCust->execute([$cName, $cEmail, $cPhone]);
                    $customerId = (int)$db->lastInsertId();
                }
            }

            $insertBooking = $db->prepare("
                INSERT INTO bookings (
                    booking_number, booking_type, booking_status, booking_direction,
                    destination_id, vehicle_id,
                    customer_name, customer_email, customer_phone,
                    flight_date, flight_time, flight_number, hotel_address, pickup_time,
                    adults, children, child_seat,
                    total_price, currency,
                    notes, admin_notes, created_at, customer_id
                ) VALUES (
                    ?, 'transfer', 'confirmed', ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, NOW(), ?
                )
            ");

            $commonFields = [
                $destinationId ?: null, $vehicleId ?: null,
                $cName,
                $cEmail,
                $cPhone,
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

            $outboundId = null;
            $returnId   = null;

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
                    $noteFields,
                    [$customerId ?: null]
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
                    $noteFields,
                    [$customerId ?: null]
                ));
                $returnId = (int)$db->lastInsertId();
                savePassengers($db, $returnId, $numReturn, $adultNames, $childNames);
                $messages[] = 'Dönüş: #' . $numReturn;
            }

            // Eğer hem geliş hem dönüş varsa, birbirlerine bağla
            if ($outboundId && $returnId) {
                $stmtLink = $db->prepare("UPDATE bookings SET related_booking_id = ? WHERE id = ?");
                $stmtLink->execute([$returnId, $outboundId]);
                $stmtLink->execute([$outboundId, $returnId]);
            }

            jsonResponse(true, 'Rezervasyon oluşturuldu — ' . implode(' | ', $messages));
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Rezervasyon Güncelle ===
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        $outboundId = (int)($_POST['outbound_id'] ?? 0) ?: $id;
        $returnId   = (int)($_POST['return_id'] ?? 0);
        $hasOutbound = !empty($_POST['has_outbound']);
        $hasReturn   = !empty($_POST['has_return']);

        if (!$outboundId && !$returnId) jsonResponse(false, 'Geçersiz ID');

        try {
            $cName = trim($_POST['customer_name'] ?? '');
            $cEmail = trim($_POST['customer_email'] ?? '');
            $cPhone = trim($_POST['customer_phone'] ?? '') ?: null;
            $customerId = (int)($_POST['customer_id'] ?? 0);

            if (!$customerId && $cName !== '') {
                // Müşteri ara (boş/null telefonları doğru eşleştirmek için)
                if ($cPhone === null || $cPhone === '') {
                    $stmtCheck = $db->prepare("SELECT id FROM customers WHERE name = ? AND (phone IS NULL OR phone = '')");
                    $stmtCheck->execute([$cName]);
                } else {
                    $stmtCheck = $db->prepare("SELECT id FROM customers WHERE name = ? AND phone = ?");
                    $stmtCheck->execute([$cName, $cPhone]);
                }
                $existingCust = $stmtCheck->fetch();
                if ($existingCust) {
                    $customerId = (int)$existingCust['id'];
                } else {
                    // Müşteri oluştur
                    $stmtInsertCust = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                    $stmtInsertCust->execute([$cName, $cEmail, $cPhone]);
                    $customerId = (int)$db->lastInsertId();
                }
            }

            // Check if it's a tour booking
            $bookingType = 'transfer';
            $checkId = $outboundId ?: $returnId;
            if ($checkId) {
                $sT = $db->prepare("SELECT booking_type FROM bookings WHERE id = ?");
                $sT->execute([$checkId]);
                $bookingType = $sT->fetchColumn() ?: 'transfer';
            }

            $adultNames = $_POST['passenger_adult_name'] ?? [];
            $childNames = $_POST['passenger_child_name'] ?? [];

            if ($bookingType === 'tour') {
                // Tour update logic
                $stmt = $db->prepare("
                    UPDATE bookings SET
                        destination_id = ?, vehicle_id = ?,
                        customer_name = ?, customer_email = ?, customer_phone = ?,
                        pickup_location = ?, pickup_date = ?, pickup_time = ?, return_time = ?,
                        adults = ?, children = ?, child_seat = ?,
                        total_price = ?, currency = ?,
                        notes = ?, admin_notes = ?,
                        booking_status = ?,
                        customer_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    ((int)($_POST['destination_id'] ?? 0)) ?: null,
                    ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                    $cName, $cEmail, $cPhone,
                    trim($_POST['pickup_location'] ?? '') ?: null,
                    ($_POST['pickup_date'] ?? '') ?: null,
                    ($_POST['pickup_time'] ?? '') ?: null,
                    ($_POST['return_time'] ?? '') ?: null,
                    (int)($_POST['adults'] ?? 1),
                    (int)($_POST['children'] ?? 0),
                    (int)($_POST['child_seat'] ?? 0),
                    (float)($_POST['total_price'] ?? 0),
                    trim($_POST['currency'] ?? 'TRY'),
                    trim($_POST['notes'] ?? '') ?: null,
                    trim($_POST['admin_notes'] ?? '') ?: null,
                    $_POST['booking_status'] ?? 'pending',
                    $customerId ?: null,
                    $checkId
                ]);

                // Update passengers
                $numStmt = $db->prepare("SELECT booking_number FROM bookings WHERE id = ?");
                $numStmt->execute([$checkId]);
                $bookingNum = $numStmt->fetchColumn() ?: '';
                savePassengers($db, $checkId, $bookingNum, $adultNames, $childNames);
            } else {
                // Transfer update/create logic
                $newOutboundCreated = false;
                $newReturnCreated = false;

                // 1) Outbound (Geliş)
                if ($hasOutbound) {
                    if ($outboundId > 0) {
                        // Update existing
                        $stmtOut = $db->prepare("
                            UPDATE bookings SET
                                destination_id = ?, vehicle_id = ?,
                                customer_name = ?, customer_email = ?, customer_phone = ?,
                                flight_date = ?, flight_time = ?, flight_number = ?, hotel_address = ?,
                                pickup_time = NULL,
                                adults = ?, children = ?, child_seat = ?,
                                total_price = ?, currency = ?,
                                notes = ?, admin_notes = ?,
                                booking_status = ?,
                                customer_id = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmtOut->execute([
                            ((int)($_POST['destination_id'] ?? 0)) ?: null,
                            ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                            $cName, $cEmail, $cPhone,
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
                            $customerId ?: null,
                            $outboundId
                        ]);
                    } else {
                        // Insert new outbound
                        $numOutbound = 'TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $db->prepare("
                            INSERT INTO bookings (
                                booking_number, booking_type, booking_status, booking_direction,
                                destination_id, vehicle_id,
                                customer_name, customer_email, customer_phone,
                                flight_date, flight_time, flight_number, hotel_address,
                                adults, children, child_seat,
                                total_price, currency,
                                notes, admin_notes, created_at, customer_id
                            ) VALUES (
                                ?, 'transfer', ?, 'outbound',
                                ?, ?,
                                ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?,
                                ?, ?,
                                ?, ?, NOW(), ?
                            )
                        ")->execute([
                            $numOutbound, $_POST['booking_status'] ?? 'confirmed',
                            ((int)($_POST['destination_id'] ?? 0)) ?: null,
                            ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                            $cName, $cEmail, $cPhone,
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
                            $customerId ?: null
                        ]);
                        $outboundId = (int)$db->lastInsertId();
                        $newOutboundCreated = true;
                        savePassengers($db, $outboundId, $numOutbound, $adultNames, $childNames);
                    }
                }

                // 2) Return (Dönüş)
                if ($hasReturn) {
                    if ($returnId > 0) {
                        // Update existing
                        $stmtRet = $db->prepare("
                            UPDATE bookings SET
                                destination_id = ?, vehicle_id = ?,
                                customer_name = ?, customer_email = ?, customer_phone = ?,
                                flight_date = ?, flight_time = ?, flight_number = ?, hotel_address = ?,
                                pickup_time = ?,
                                adults = ?, children = ?, child_seat = ?,
                                total_price = ?, currency = ?,
                                notes = ?, admin_notes = ?,
                                booking_status = ?,
                                customer_id = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmtRet->execute([
                            ((int)($_POST['destination_id'] ?? 0)) ?: null,
                            ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                            $cName, $cEmail, $cPhone,
                            ($_POST['return_flight_date'] ?? '') ?: null,
                            ($_POST['return_flight_time'] ?? '') ?: null,
                            trim($_POST['return_flight_number'] ?? '') ?: null,
                            trim($_POST['return_hotel_address'] ?? '') ?: null,
                            ($_POST['return_pickup_time'] ?? '') ?: null,
                            (int)($_POST['adults'] ?? 1),
                            (int)($_POST['children'] ?? 0),
                            (int)($_POST['child_seat'] ?? 0),
                            (float)($_POST['return_total_price'] ?? 0),
                            trim($_POST['return_currency'] ?? $_POST['currency'] ?? 'TRY'),
                            trim($_POST['notes'] ?? '') ?: null,
                            trim($_POST['admin_notes'] ?? '') ?: null,
                            $_POST['booking_status'] ?? 'pending',
                            $customerId ?: null,
                            $returnId
                        ]);
                    } else {
                        // Insert new return
                        $numReturn = 'TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $db->prepare("
                            INSERT INTO bookings (
                                booking_number, booking_type, booking_status, booking_direction,
                                destination_id, vehicle_id,
                                customer_name, customer_email, customer_phone,
                                flight_date, flight_time, flight_number, hotel_address, pickup_time,
                                adults, children, child_seat,
                                total_price, currency,
                                notes, admin_notes, created_at, customer_id
                            ) VALUES (
                                ?, 'transfer', ?, 'return',
                                ?, ?,
                                ?, ?, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?,
                                ?, ?,
                                ?, ?, NOW(), ?
                            )
                        ")->execute([
                            $numReturn, $_POST['booking_status'] ?? 'confirmed',
                            ((int)($_POST['destination_id'] ?? 0)) ?: null,
                            ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                            $cName, $cEmail, $cPhone,
                            ($_POST['return_flight_date'] ?? '') ?: null,
                            ($_POST['return_flight_time'] ?? '') ?: null,
                            trim($_POST['return_flight_number'] ?? '') ?: null,
                            trim($_POST['return_hotel_address'] ?? '') ?: null,
                            ($_POST['return_pickup_time'] ?? '') ?: null,
                            (int)($_POST['adults'] ?? 1),
                            (int)($_POST['children'] ?? 0),
                            (int)($_POST['child_seat'] ?? 0),
                            (float)($_POST['return_total_price'] ?? 0),
                            trim($_POST['return_currency'] ?? $_POST['currency'] ?? 'TRY'),
                            trim($_POST['notes'] ?? '') ?: null,
                            trim($_POST['admin_notes'] ?? '') ?: null,
                            $customerId ?: null
                        ]);
                        $returnId = (int)$db->lastInsertId();
                        $newReturnCreated = true;
                        savePassengers($db, $returnId, $numReturn, $adultNames, $childNames);
                    }
                }

                // 3) Link them if both are active, otherwise clear link
                if ($hasOutbound && $hasReturn && $outboundId && $returnId && $outboundId !== $returnId) {
                    $stmtLink = $db->prepare("UPDATE bookings SET related_booking_id = ? WHERE id = ?");
                    $stmtLink->execute([$returnId, $outboundId]);
                    $stmtLink->execute([$outboundId, $returnId]);
                } else {
                    $stmtUnlink = $db->prepare("UPDATE bookings SET related_booking_id = NULL WHERE id = ?");
                    if ($outboundId) $stmtUnlink->execute([$outboundId]);
                    if ($returnId && $returnId !== $outboundId) $stmtUnlink->execute([$returnId]);
                }

                // 4) Update passenger lists for existing bookings
                if ($outboundId && $hasOutbound && !$newOutboundCreated) {
                    $numStmt = $db->prepare("SELECT booking_number FROM bookings WHERE id = ?");
                    $numStmt->execute([$outboundId]);
                    $bookingNum = $numStmt->fetchColumn() ?: '';
                    savePassengers($db, $outboundId, $bookingNum, $adultNames, $childNames);
                }
                if ($returnId && $hasReturn && !$newReturnCreated) {
                    $numStmt = $db->prepare("SELECT booking_number FROM bookings WHERE id = ?");
                    $numStmt->execute([$returnId]);
                    $bookingNum = $numStmt->fetchColumn() ?: '';
                    savePassengers($db, $returnId, $bookingNum, $adultNames, $childNames);
                }
            }

            jsonResponse(true, 'Rezervasyon güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Rezervasyon Sil ===
    case 'delete':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) $ids = [$id];
        }
        if (empty($ids)) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE bookings SET is_deleted = 1 WHERE id = ?");
            $deletedCount = 0;
            foreach ($ids as $id) {
                $stmt->execute([(int)$id]);
                $deletedCount += $stmt->rowCount();
            }
            $db->commit();

            if ($deletedCount === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }

            jsonResponse(true, 'Rezervasyon(lar) silindi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;

    // === Rezervasyon Geri Yükle ===
    case 'restore':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) $ids = [$id];
        }
        if (empty($ids)) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE bookings SET is_deleted = 0 WHERE id = ?");
            $restoredCount = 0;
            foreach ($ids as $id) {
                $stmt->execute([(int)$id]);
                $restoredCount += $stmt->rowCount();
            }
            $db->commit();

            if ($restoredCount === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }

            jsonResponse(true, 'Rezervasyon(lar) geri yüklendi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Geri yükleme hatası: ' . $e->getMessage());
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
                    is_completed = 0,
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
                    is_completed = 1,
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

    case 'search_customers':
        $q = trim($_POST['q'] ?? $_GET['q'] ?? '');
        if (strlen($q) < 2) {
            jsonResponse(true, 'OK', ['customers' => []]);
        }
        try {
            $stmt = $db->prepare("
                SELECT id, name, email, phone 
                FROM customers 
                WHERE name LIKE ? OR phone LIKE ? 
                ORDER BY name ASC 
                LIMIT 20
            ");
            $stmt->execute(['%' . $q . '%', '%' . $q . '%']);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(true, 'OK', ['customers' => $customers]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
