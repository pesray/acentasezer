<?php
/**
 * Bookings API - AJAX işlemleri
 */

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, 'Kayıt bulunamadı');
            }
            
            jsonResponse(true, 'Rezervasyon başarıyla silindi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Silme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'update_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!$id || !in_array($status, $validStatuses)) {
            jsonResponse(false, 'Geçersiz parametreler');
        }
        
        try {
            $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $statusTexts = [
                'pending' => 'beklemede',
                'confirmed' => 'onaylandı',
                'cancelled' => 'iptal edildi',
                'completed' => 'tamamlandı'
            ];
            
            jsonResponse(true, "Rezervasyon durumu: " . $statusTexts[$status]);
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    case 'mark_read':
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            jsonResponse(false, 'Geçersiz ID');
        }
        
        try {
            $stmt = $db->prepare("UPDATE bookings SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(true, 'Okundu olarak işaretlendi');
            
        } catch (Exception $e) {
            jsonResponse(false, 'Güncelleme hatası: ' . $e->getMessage());
        }
        break;
        
    default:
        jsonResponse(false, 'Geçersiz action: ' . $action);
}
