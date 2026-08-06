<?php
/**
 * Admin API - Havalimanı İşlemleri
 */

if (!defined('ADMIN_URL')) {
    require_once __DIR__ . '/../includes/auth.php';
    requireLogin();
}

$db = getDB();
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
    case 'quick_add':
        $name     = trim($_POST['name'] ?? '');
        $city     = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $isDefault = isset($_POST['is_default']) ? (int)$_POST['is_default'] : 0;

        if (empty($name)) {
            jsonResponse(false, 'Havalimanı adı zorunludur.');
        }

        try {
            $db->beginTransaction();

            if ($isDefault === 1) {
                $db->query("UPDATE airports SET is_default = 0");
            }

            $stmt = $db->prepare("
                INSERT INTO airports (name, city, district, code, is_default, is_deleted)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $city, $district, $code, $isDefault]);
            $newId = $db->lastInsertId();

            $db->commit();

            jsonResponse(true, 'Havalimanı başarıyla eklendi.', [
                'id' => (int)$newId,
                'name' => $name,
                'city' => $city,
                'district' => $district,
                'code' => $code,
                'is_default' => $isDefault
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'update':
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $city     = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $isDefault = isset($_POST['is_default']) ? (int)$_POST['is_default'] : 0;

        if (!$id || empty($name)) {
            jsonResponse(false, 'Geçersiz parametre veya boş ad.');
        }

        try {
            $db->beginTransaction();

            if ($isDefault === 1) {
                $db->query("UPDATE airports SET is_default = 0 WHERE id != " . (int)$id);
            }

            $stmt = $db->prepare("
                UPDATE airports 
                SET name = ?, city = ?, district = ?, code = ?, is_default = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $city, $district, $code, $isDefault, $id]);

            $db->commit();

            jsonResponse(true, 'Havalimanı güncellendi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'set_default':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->beginTransaction();
            $db->query("UPDATE airports SET is_default = 0");
            $db->prepare("UPDATE airports SET is_default = 1 WHERE id = ?")->execute([$id]);
            $db->commit();

            jsonResponse(true, 'Varsayılan havalimanı güncellendi.');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Geçersiz ID');

        try {
            $db->prepare("UPDATE airports SET is_deleted = 1 WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Havalimanı silindi.');
        } catch (Exception $e) {
            jsonResponse(false, 'Hata: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Geçersiz işlem: ' . $action);
}
