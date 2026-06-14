<?php
/**
 * Customers API - Müşteri listesi ve yönetimi
 */

switch ($action) {

    case 'create':
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '') ?: null;
        $phone = trim($_POST['phone'] ?? '') ?: null;
        if (!$name) jsonResponse(false, 'Ad Soyad alanı zorunludur.');
        try {
            $db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)")
               ->execute([$name, $email, $phone]);
            $newId = (int)$db->lastInsertId();
            jsonResponse(true, 'Müşteri eklendi.', ['id' => $newId, 'name' => $name, 'email' => $email, 'phone' => $phone]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'update':
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '') ?: null;
        $phone = trim($_POST['phone'] ?? '') ?: null;
        if (!$id)   jsonResponse(false, 'Geçersiz ID');
        if (!$name) jsonResponse(false, 'Ad Soyad alanı zorunludur.');
        try {
            $db->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?")
               ->execute([$name, $email, $phone, $id]);
            jsonResponse(true, 'Müşteri güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            // Rezervasyonlarda bu müşteri kullanılıyorsa ne yapacağız?
            // bookings tablosunda customer_id null'a çekilebilir veya ilişki korunabilir.
            // İlişki kopmaması için, bookings tablosundaki bu customer_id'leri null yapalım.
            $db->beginTransaction();
            $db->prepare("UPDATE bookings SET customer_id = NULL WHERE customer_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
            $db->commit();
            jsonResponse(true, 'Müşteri silindi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
