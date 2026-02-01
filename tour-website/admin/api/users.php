<?php
/**
 * Users API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        // Kendini silmeye çalışıyorsa engelle
        if ($id == ($_SESSION['admin_id'] ?? 0)) {
            jsonResponse(false, 'Kendinizi silemezsiniz');
        }
        
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            jsonResponse(true, 'Kullanıcı başarıyla silindi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_status':
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        
        // Kendini pasif yapmaya çalışıyorsa engelle
        if ($id == ($_SESSION['admin_id'] ?? 0) && !$active) {
            jsonResponse(false, 'Kendinizi pasif yapamazsınız');
        }
        
        try {
            $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$active, $id]);
            
            $text = $active ? 'aktif edildi' : 'pasif edildi';
            jsonResponse(true, "Kullanıcı $text");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
