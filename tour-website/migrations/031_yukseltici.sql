-- =====================================================================
-- 031 — Admin rezervasyonlarında "Yükseltici" sayısı (2026-11-20)
-- Çocuk koltuğuyla (child_seat) aynı mantık: ayrı sayı alanı, varsayılan 0, fiyatı etkilemez.
--   bookings.booster              (Yeni Rezervasyon / Düzenle formları)
--   inter_hotel_transfers.booster (Ara Transfer formu)
-- Sitedeki rezervasyon sihirbazı DEĞİŞMEZ: yükselticiyi notlara "YÜKSELTİCİ: N" yazmaya devam eder.
-- Tekrar çalıştırılabilir (sütun varsa hiçbir şey yapmaz). MySQL 8 + MariaDB 10.4 uyumlu.
--
-- SIRA ÖNEMLİ: Önce bu SQL, SONRA kod deploy. (Yeni admin kodu bu sütuna yazar;
-- sütun yokken deploy edilirse admin'den rezervasyon kaydı hata verir.)
-- Bu SQL eski kodu bozmaz: tüm INSERT'ler sütun adlarını tek tek yazıyor, yeni sütun 0 alır.
--
-- Geri alma (gerekirse, önce kodu eski haline döndür):
--   ALTER TABLE `bookings` DROP COLUMN `booster`;
--   ALTER TABLE `inter_hotel_transfers` DROP COLUMN `booster`;
-- =====================================================================

SET @db := DATABASE();

SET @s := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'booster') = 0,
    'ALTER TABLE `bookings` ADD COLUMN `booster` INT(11) NULL DEFAULT 0 AFTER `child_seat`',
    'SELECT ''bookings.booster zaten var'' AS bilgi'
);
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'inter_hotel_transfers' AND COLUMN_NAME = 'booster') = 0,
    'ALTER TABLE `inter_hotel_transfers` ADD COLUMN `booster` INT(11) NULL DEFAULT 0 AFTER `child_seat`',
    'SELECT ''inter_hotel_transfers.booster zaten var'' AS bilgi'
);
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
