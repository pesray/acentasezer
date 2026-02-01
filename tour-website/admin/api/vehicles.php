<?php
/**
 * Vehicles API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $db->beginTransaction();
            
            // İlişkili fiyatları sil
            $stmt = $db->prepare("DELETE FROM destination_vehicles WHERE vehicle_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM tour_vehicles WHERE vehicle_id = ?");
            $stmt->execute([$id]);
            
            // Ana kaydı sil
            $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            $db->commit();
            jsonResponse(true, 'Araç başarıyla silindi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_active':
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $stmt = $db->prepare("UPDATE vehicles SET is_active = ? WHERE id = ?");
            $stmt->execute([$active, $id]);
            
            $text = $active ? 'aktif edildi' : 'pasif edildi';
            jsonResponse(true, "Araç $text");
            
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
            $stmt = $db->prepare("UPDATE vehicles SET sort_order = ? WHERE id = ?");
            
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
