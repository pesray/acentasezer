<?php
/**
 * WhatsApp Templates API - AJAX CRUD işlemleri
 */

switch ($action) {

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');
        try {
            $stmt = $db->prepare("
                UPDATE whatsapp_templates SET
                    content = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                trim($_POST['content'] ?? ''),
                $id,
            ]);
            jsonResponse(true, 'Şablon güncellendi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
