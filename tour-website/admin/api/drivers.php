<?php
/**
 * Drivers API - AJAX CRUD işlemleri
 */

switch ($action) {

    case 'create':
        try {
            $stmt = $db->prepare("
                INSERT INTO drivers (name, surname, phone, vehicle_id, plate)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['surname'] ?? ''),
                trim($_POST['phone'] ?? '') ?: null,
                (int)($_POST['vehicle_id'] ?? 0) ?: null,
                trim($_POST['plate'] ?? '') ?: null,
            ]);
            jsonResponse(true, 'Şöför eklendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("
                UPDATE drivers SET
                    name = ?, surname = ?, phone = ?, vehicle_id = ?, plate = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['surname'] ?? ''),
                trim($_POST['phone'] ?? '') ?: null,
                (int)($_POST['vehicle_id'] ?? 0) ?: null,
                trim($_POST['plate'] ?? '') ?: null,
                $id,
            ]);
            jsonResponse(true, 'Şöför güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("DELETE FROM drivers WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) jsonResponse(false, 'Kayıt bulunamadı');
            jsonResponse(true, 'Şöför silindi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
