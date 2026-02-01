<?php
/**
 * Media API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            // Dosya bilgilerini al
            $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
            $stmt->execute([$id]);
            $media = $stmt->fetch();
            
            if (!$media) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            // Fiziksel dosyayı sil
            $filePath = UPLOADS_PATH . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // DB kaydını sil
            $stmt = $db->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(true, 'Dosya başarıyla silindi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'update_info':
        $id = (int)($_POST['id'] ?? 0);
        $altText = $_POST['alt_text'] ?? '';
        $title = $_POST['title'] ?? '';
        
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $stmt = $db->prepare("UPDATE media SET alt_text = ?, title = ? WHERE id = ?");
            $stmt->execute([$altText, $title, $id]);
            
            jsonResponse(true, 'Dosya bilgileri güncellendi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
