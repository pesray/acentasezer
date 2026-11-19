<?php
/**
 * Inter Transfers API - Ara Transferler (Otelden Otele) AJAX CRUD işlemleri
 */

function saveInterPassengers($db, $interTransferId, $bookingNumber, $adultNames, $childNames) {
    $db->prepare("DELETE FROM booking_passengers WHERE inter_transfer_id = ?")->execute([$interTransferId]);
    $ins = $db->prepare("INSERT INTO booking_passengers (booking_id, inter_transfer_id, booking_number, passenger_type, full_name, sort_order) VALUES (NULL, ?, ?, ?, ?, ?)");
    $i = 0;
    if (is_array($adultNames)) {
        foreach ($adultNames as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $ins->execute([$interTransferId, $bookingNumber, 'adult', $name, $i++]);
        }
    }
    $i = 0;
    if (is_array($childNames)) {
        foreach ($childNames as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $ins->execute([$interTransferId, $bookingNumber, 'child', $name, $i++]);
        }
    }
}

switch ($action) {

    // === Ara Transfer Oluştur ===
    case 'create':
        try {
            $cName  = trim($_POST['customer_name'] ?? '');
            $cEmail = trim($_POST['customer_email'] ?? '');
            $cPhone = trim($_POST['customer_phone'] ?? '') ?: null;
            $customerId = (int)($_POST['customer_id'] ?? 0);

            if ($cName === '') {
                jsonResponse(false, 'Müşteri adı zorunludur.');
            }

            if (!$customerId) {
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
                    $stmtInsertCust = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                    $stmtInsertCust->execute([$cName, $cEmail, $cPhone]);
                    $customerId = (int)$db->lastInsertId();
                }
            }

            // Alınış ve Varış otelleri
            $pickupHotelId     = (int)($_POST['pickup_hotel_id'] ?? 0) ?: null;
            $pickupHotelName   = trim($_POST['pickup_hotel_name'] ?? '') ?: null;
            $destHotelId       = (int)($_POST['destination_hotel_id'] ?? 0) ?: null;
            $destHotelName     = trim($_POST['destination_hotel_name'] ?? '') ?: null;

            // Kod üretme: ARA-YYYYMMDD-XXXX
            $bookingNum = 'ARA-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("
                INSERT INTO inter_hotel_transfers (
                    booking_number, customer_id, customer_name, customer_email, customer_phone,
                    destination_id, vehicle_id,
                    pickup_hotel_id, pickup_hotel_name, pickup_date, pickup_time,
                    destination_hotel_id, destination_hotel_name,
                    adults, children, child_seat, booster,
                    total_price, currency,
                    notes, admin_notes, booking_status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, NOW()
                )
            ");
            $stmt->execute([
                $bookingNum, $customerId ?: null, $cName, $cEmail, $cPhone,
                ((int)($_POST['destination_id'] ?? 0)) ?: null,
                ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                $pickupHotelId, $pickupHotelName,
                ($_POST['pickup_date'] ?? '') ?: null,
                ($_POST['pickup_time'] ?? '') ?: null,
                $destHotelId, $destHotelName,
                (int)($_POST['adults'] ?? 1),
                (int)($_POST['children'] ?? 0),
                (int)($_POST['child_seat'] ?? 0),
                max(0, (int)($_POST['booster'] ?? 0)),
                (float)($_POST['total_price'] ?? 0),
                trim($_POST['currency'] ?? 'EUR'),
                trim($_POST['notes'] ?? '') ?: null,
                trim($_POST['admin_notes'] ?? '') ?: null,
                $_POST['booking_status'] ?? 'confirmed'
            ]);

            $interId = (int)$db->lastInsertId();

            // Yolcuları kaydet
            $adultNames = $_POST['passenger_adult_name'] ?? [];
            $childNames = $_POST['passenger_child_name'] ?? [];
            saveInterPassengers($db, $interId, $bookingNum, $adultNames, $childNames);

            jsonResponse(true, 'Ara transfer başarıyla eklendi.', ['id' => $interId, 'booking_number' => $bookingNum]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Ara Transfer Güncelle ===
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $cName  = trim($_POST['customer_name'] ?? '');
            $cEmail = trim($_POST['customer_email'] ?? '');
            $cPhone = trim($_POST['customer_phone'] ?? '') ?: null;
            $customerId = (int)($_POST['customer_id'] ?? 0);

            if (!$customerId && $cName !== '') {
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
                    $stmtInsertCust = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                    $stmtInsertCust->execute([$cName, $cEmail, $cPhone]);
                    $customerId = (int)$db->lastInsertId();
                }
            }

            $pickupHotelId   = (int)($_POST['pickup_hotel_id'] ?? 0) ?: null;
            $pickupHotelName = trim($_POST['pickup_hotel_name'] ?? '') ?: null;
            $destHotelId     = (int)($_POST['destination_hotel_id'] ?? 0) ?: null;
            $destHotelName   = trim($_POST['destination_hotel_name'] ?? '') ?: null;

            $stmt = $db->prepare("
                UPDATE inter_hotel_transfers SET
                    customer_id = ?, customer_name = ?, customer_email = ?, customer_phone = ?,
                    destination_id = ?, vehicle_id = ?,
                    pickup_hotel_id = ?, pickup_hotel_name = ?, pickup_date = ?, pickup_time = ?,
                    destination_hotel_id = ?, destination_hotel_name = ?,
                    adults = ?, children = ?, child_seat = ?, booster = ?,
                    total_price = ?, currency = ?,
                    notes = ?, admin_notes = ?,
                    booking_status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $customerId ?: null, $cName, $cEmail, $cPhone,
                ((int)($_POST['destination_id'] ?? 0)) ?: null,
                ((int)($_POST['vehicle_id'] ?? 0)) ?: null,
                $pickupHotelId, $pickupHotelName,
                ($_POST['pickup_date'] ?? '') ?: null,
                ($_POST['pickup_time'] ?? '') ?: null,
                $destHotelId, $destHotelName,
                (int)($_POST['adults'] ?? 1),
                (int)($_POST['children'] ?? 0),
                (int)($_POST['child_seat'] ?? 0),
                max(0, (int)($_POST['booster'] ?? 0)),
                (float)($_POST['total_price'] ?? 0),
                trim($_POST['currency'] ?? 'EUR'),
                trim($_POST['notes'] ?? '') ?: null,
                trim($_POST['admin_notes'] ?? '') ?: null,
                $_POST['booking_status'] ?? 'pending',
                $id
            ]);

            // Yolcuları güncelle
            $numStmt = $db->prepare("SELECT booking_number FROM inter_hotel_transfers WHERE id = ?");
            $numStmt->execute([$id]);
            $bookingNum = $numStmt->fetchColumn() ?: '';
            $adultNames = $_POST['passenger_adult_name'] ?? [];
            $childNames = $_POST['passenger_child_name'] ?? [];
            saveInterPassengers($db, $id, $bookingNum, $adultNames, $childNames);

            jsonResponse(true, 'Ara transfer güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Sil (Soft Delete) ===
    case 'delete':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) $ids = [$id];
        }
        if (empty($ids)) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE inter_hotel_transfers SET is_deleted = 1 WHERE id = ?");
            $deletedCount = 0;
            foreach ($ids as $id) {
                $stmt->execute([(int)$id]);
                $deletedCount += $stmt->rowCount();
            }
            $db->commit();
            jsonResponse(true, 'Ara transfer(ler) silindi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;

    // === Geri Yükle ===
    case 'restore':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) $ids = [$id];
        }
        if (empty($ids)) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE inter_hotel_transfers SET is_deleted = 0 WHERE id = ?");
            $restoredCount = 0;
            foreach ($ids as $id) {
                $stmt->execute([(int)$id]);
                $restoredCount += $stmt->rowCount();
            }
            $db->commit();
            jsonResponse(true, 'Ara transfer(ler) geri yüklendi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Geri yükleme hatası: ' . $e->getMessage());
        }
        break;

    // === Yolcu İsimlerini Getir ===
    case 'get_passengers':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("SELECT * FROM booking_passengers WHERE inter_transfer_id = ? ORDER BY sort_order, id");
            $stmt->execute([$id]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(true, 'Yolcu listesi', ['passengers' => $passengers]);
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
                FROM inter_hotel_transfers b
                LEFT JOIN outsource_partners op ON (
                    (b.outsource_partner_id IS NOT NULL AND op.id = b.outsource_partner_id)
                    OR
                    (b.outsource_partner_id IS NULL AND op.name = b.outsource_name AND op.is_active = 1)
                )
                LEFT JOIN booking_drivers bd ON b.id = bd.inter_transfer_id
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
                'outsource_currency'    => $row['outsource_currency'] ?: ($row['currency'] ?: 'EUR'),
                'outsource_pickup_time' => $row['outsource_pickup_time'],
                'driver_id'             => $row['driver_id'] ? (int)$row['driver_id'] : null,
                'driver_name'           => $row['driver_name'] ? trim($row['driver_name'] . ' ' . ($row['driver_surname'] ?? '')) : null
            ]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === İş Durumunu Güncelle ===
    case 'update_ops':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $isCompleted  = isset($_POST['is_completed']) ? (int)$_POST['is_completed'] : null;
            $isOutsourced = isset($_POST['is_outsourced']) ? (int)$_POST['is_outsourced'] : null;

            $fields = [];
            $params = [];

            if ($isCompleted !== null) {
                $fields[] = "is_completed = ?";
                $params[] = $isCompleted;
            }
            if ($isOutsourced !== null) {
                $fields[] = "is_outsourced = ?";
                $params[] = $isOutsourced;
            }

            if (!empty($fields)) {
                $params[] = $id;
                $sql = "UPDATE inter_hotel_transfers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $db->prepare($sql)->execute($params);
            }

            jsonResponse(true, 'İş durumu güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    // === Şöför Ata ===
    case 'assign_driver':
        $id       = (int)($_POST['id'] ?? 0);
        $driverId = (int)($_POST['driver_id'] ?? 0);

        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM booking_drivers WHERE inter_transfer_id = ?")->execute([$id]);

            $driverName = null;
            if ($driverId > 0) {
                $db->prepare("INSERT INTO booking_drivers (booking_id, inter_transfer_id, driver_id) VALUES (NULL, ?, ?)")->execute([$id, $driverId]);
                $sD = $db->prepare("SELECT CONCAT(name,' ',surname) FROM drivers WHERE id = ?");
                $sD->execute([$driverId]);
                $driverName = trim($sD->fetchColumn() ?: '');
            }

            $db->commit();
            jsonResponse(true, 'Şöför atandı.', ['driver_id' => $driverId, 'driver_name' => $driverName]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Şöför atama hatası: ' . $e->getMessage());
        }
        break;

    // === Hızlı Durum Güncelle ===
    case 'quick_status':
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!$id || !in_array($status, $validStatuses)) {
            jsonResponse(false, 'Geçersiz parametreler');
        }

        try {
            $db->prepare("UPDATE inter_hotel_transfers SET booking_status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $id]);
            jsonResponse(true, 'Durum güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz işlem: ' . $action);
}
