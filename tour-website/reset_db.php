<?php
$pdo = new PDO('mysql:host=5.2.85.141;dbname=ahmetkes_agency', 'ahmetkes_sezer', 'Szr4569*-');
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Tüm tabloları sil
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `$table`");
    echo "Silindi: $table\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "\nTüm tablolar silindi. Şimdi migrate.php çalıştırın.\n";
