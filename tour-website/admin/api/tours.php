<?php
/**
 * Tours API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $db->beginTransaction();
            
            // Önce ilişkili kayıtları sil
            $stmt = $db->prepare("DELETE FROM tour_translations WHERE tour_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM tour_vehicles WHERE tour_id = ?");
            $stmt->execute([$id]);
            
            // Ana kaydı sil
            $stmt = $db->prepare("DELETE FROM tours WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            $db->commit();
            jsonResponse(true, 'Tur başarıyla silindi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        
        if (!$id || !in_array($status, ['published', 'draft'])) {
            jsonResponse(false, 'Geçersiz parametreler');
        }
        
        try {
            $stmt = $db->prepare("UPDATE tours SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $statusText = $status === 'published' ? 'yayınlandı' : 'taslağa alındı';
            jsonResponse(true, "Tur $statusText");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_featured':
        $id = (int)($_POST['id'] ?? 0);
        $featured = (int)($_POST['featured'] ?? 0);
        
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $stmt = $db->prepare("UPDATE tours SET is_featured = ? WHERE id = ?");
            $stmt->execute([$featured, $id]);
            
            $text = $featured ? 'öne çıkarıldı' : 'öne çıkarmadan kaldırıldı';
            jsonResponse(true, "Tur $text");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'update_order':
        $items = $_POST['items'] ?? [];
        
        if (empty($items)) {
            jsonResponse(false, 'Sıralama verisi bulunamadı');
        }
        
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE tours SET sort_order = ? WHERE id = ?");
            
            foreach ($items as $order => $id) {
                $stmt->execute([$order, (int)$id]);
            }
            
            $db->commit();
            jsonResponse(true, 'Sıralama güncellendi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Sıralama hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
