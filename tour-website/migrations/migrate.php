<?php
/**
 * Migration Runner
 * Tüm migration dosyalarını sırayla çalıştırır
 * 
 * Kullanım: php migrate.php
 */

// Veritabanı ayarları
$host = '5.2.85.141';
$dbname = 'ahmetkes_agency';
$username = 'ahmetkes_sezer';
$password = 'Szr4569*-';

// Foreign key kontrolünü devre dışı bırak
$disableFKCheck = "SET FOREIGN_KEY_CHECKS = 0";

echo "=== Tour CMS Migration Tool ===\n\n";

try {
    // Önce veritabanı olmadan bağlan
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Veritabanını oluştur
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[OK] Veritabanı oluşturuldu veya zaten mevcut: $dbname\n";
    
    // Veritabanına bağlan
    $pdo->exec("USE `$dbname`");
    
    // Foreign key kontrolünü devre dışı bırak
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Migration dosyalarını al
    $migrationDir = __DIR__;
    $files = glob($migrationDir . '/*.sql');
    sort($files);
    
    if (empty($files)) {
        echo "[!] Migration dosyası bulunamadı.\n";
        exit(1);
    }
    
    echo "\n" . count($files) . " migration dosyası bulundu.\n\n";
    
    // Her migration dosyasını çalıştır
    foreach ($files as $file) {
        $filename = basename($file);
        echo "Çalıştırılıyor: $filename ... ";
        
        $sql = file_get_contents($file);
        
        // Birden fazla SQL ifadesini ayır ve çalıştır
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) { return !empty($s) && strpos($s, '--') !== 0; }
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Duplicate key hatalarını yoksay (zaten var olan kayıtlar)
                    if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                        strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        echo "[OK]\n";
    }
    
    // Foreign key kontrolünü tekrar aç
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n=== Migration tamamlandı! ===\n";
    echo "\nVarsayılan admin bilgileri:\n";
    echo "  Kullanıcı: admin\n";
    echo "  E-posta: admin@example.com\n";
    echo "  Şifre: admin123\n";
    
} catch (PDOException $e) {
    echo "[HATA] " . $e->getMessage() . "\n";
    exit(1);
}
