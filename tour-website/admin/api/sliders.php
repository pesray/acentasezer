<?php
/**
 * Sliders API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("DELETE FROM slider_translations WHERE slider_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM sliders WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            $db->commit();
            jsonResponse(true, 'Slider başarıyla silindi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_status':
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        
        try {
            $stmt = $db->prepare("UPDATE sliders SET is_active = ? WHERE id = ?");
            $stmt->execute([$active, $id]);
            
            $text = $active ? 'aktif edildi' : 'pasif edildi';
            jsonResponse(true, "Slider $text");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'update_order':
        $items = $_POST['items'] ?? [];
        
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE sliders SET sort_order = ? WHERE id = ?");
            
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
