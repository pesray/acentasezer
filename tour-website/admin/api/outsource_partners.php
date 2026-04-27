<?php
/**
 * Outsource Partners API - Dışarıya verilen kişi/firma listesi
 */

switch ($action) {

    case 'list':
        try {
            $stmt = $db->query("SELECT id, name, phone FROM outsource_partners WHERE is_active = 1 ORDER BY name ASC");
            jsonResponse(true, 'OK', ['partners' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'create':
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '') ?: null;
        $notes = trim($_POST['notes'] ?? '') ?: null;
        if (!$name) jsonResponse(false, 'Ad alanı zorunludur.');
        try {
            $db->prepare("INSERT INTO outsource_partners (name, phone, notes, is_active) VALUES (?, ?, ?, 1)")
               ->execute([$name, $phone, $notes]);
            $newId = (int)$db->lastInsertId();
            jsonResponse(true, 'Partner eklendi.', ['id' => $newId, 'name' => $name, 'phone' => $phone]);
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'update':
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '') ?: null;
        $notes = trim($_POST['notes'] ?? '') ?: null;
        if (!$id)   jsonResponse(false, 'Geçersiz ID');
        if (!$name) jsonResponse(false, 'Ad alanı zorunludur.');
        try {
            $db->prepare("UPDATE outsource_partners SET name = ?, phone = ?, notes = ?, updated_at = NOW() WHERE id = ?")
               ->execute([$name, $phone, $notes, $id]);
            jsonResponse(true, 'Partner güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("DELETE FROM outsource_partners WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Partner silindi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'toggle_active':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $db->prepare("UPDATE outsource_partners SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?")
               ->execute([$id]);
            jsonResponse(true, 'Durum güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
