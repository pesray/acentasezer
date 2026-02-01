<?php
/**
 * Menus API - AJAX işlemleri
 */

switch ($action) {
    case 'delete_item':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $db->beginTransaction();
            
            // Alt menü öğelerini de sil
            $stmt = $db->prepare("DELETE FROM menu_item_translations WHERE menu_item_id IN (SELECT id FROM menu_items WHERE parent_id = ?)");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM menu_items WHERE parent_id = ?");
            $stmt->execute([$id]);
            
            // Çevirileri sil
            $stmt = $db->prepare("DELETE FROM menu_item_translations WHERE menu_item_id = ?");
            $stmt->execute([$id]);
            
            // Ana kaydı sil
            $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            $db->commit();
            jsonResponse(true, 'Menü öğesi başarıyla silindi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'delete_menu':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $db->beginTransaction();
            
            // Menü öğelerinin çevirilerini sil
            $stmt = $db->prepare("DELETE FROM menu_item_translations WHERE menu_item_id IN (SELECT id FROM menu_items WHERE menu_id = ?)");
            $stmt->execute([$id]);
            
            // Menü öğelerini sil
            $stmt = $db->prepare("DELETE FROM menu_items WHERE menu_id = ?");
            $stmt->execute([$id]);
            
            // Menüyü sil
            $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            jsonResponse(true, 'Menü başarıyla silindi');
            
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'toggle_item_status':
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        
        try {
            $stmt = $db->prepare("UPDATE menu_items SET is_active = ? WHERE id = ?");
            $stmt->execute([$active, $id]);
            
            $text = $active ? 'aktif edildi' : 'pasif edildi';
            jsonResponse(true, "Menü öğesi $text");
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
