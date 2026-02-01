<?php
/**
 * Languages API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            // Varsayılan dil silinemez
            $stmt = $db->prepare("SELECT is_default FROM languages WHERE id = ?");
            $stmt->execute([$id]);
            $lang = $stmt->fetch();
            
            if ($lang && $lang['is_default']) {
                jsonResponse(false, 'Varsayılan dil silinemez');
            }
            
            $stmt = $db->prepare("DELETE FROM languages WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            jsonResponse(true, 'Dil başarıyla silindi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_status':
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        
        try {
            $stmt = $db->prepare("UPDATE languages SET is_active = ? WHERE id = ?");
            $stmt->execute([$active, $id]);
            
            $text = $active ? 'aktif edildi' : 'pasif edildi';
            jsonResponse(true, "Dil $text");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
