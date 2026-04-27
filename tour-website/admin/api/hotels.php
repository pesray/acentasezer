<?php
/**
 * Hotels API - AJAX CRUD işlemleri
 */

switch ($action) {

    case 'create':
        try {
            $stmt = $db->prepare("
                INSERT INTO hotels (name, address, phone, distance_km, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['address'] ?? '') ?: null,
                trim($_POST['phone'] ?? '') ?: null,
                ($_POST['distance_km'] ?? '') !== '' ? (float)$_POST['distance_km'] : null,
                isset($_POST['is_active']) ? 1 : 0,
            ]);
            jsonResponse(true, 'Otel eklendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("
                UPDATE hotels SET
                    name = ?, address = ?, phone = ?, distance_km = ?,
                    is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['address'] ?? '') ?: null,
                trim($_POST['phone'] ?? '') ?: null,
                ($_POST['distance_km'] ?? '') !== '' ? (float)$_POST['distance_km'] : null,
                isset($_POST['is_active']) ? 1 : 0,
                $id,
            ]);
            jsonResponse(true, 'Otel güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("DELETE FROM hotels WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) jsonResponse(false, 'Kayıt bulunamadı');
            jsonResponse(true, 'Otel silindi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;

    case 'toggle_active':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("UPDATE hotels SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Durum güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
