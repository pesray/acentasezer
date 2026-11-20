-- =====================================================================
-- 033 — Şoföre verilen işlerde "Tahsilat var / yok" (Sezer, 2026-11-20)
-- Şoför seçilirken (İş yapıldı → "İşi Yapan Şoförü Seç" ve İş Durumu → "Şoför Ata")
-- tahsilat seçimi kaydedilir:
--   bookings.tahsilat              (Arrival / Depar)
--   inter_hotel_transfers.tahsilat (Ara Transfer)
-- Değerler: NULL = seçilmemiş / şoför yok, 1 = tahsilat var, 0 = tahsilat yok.
-- Mevcut kayıtlar NULL kalır (hiçbir veri değişmez). Sitedeki rezervasyon sihirbazı DEĞİŞMEZ.
-- Tekrar çalıştırılabilir (sütun varsa hiçbir şey yapmaz). MySQL 8 + MariaDB 10.4 uyumlu.
--
-- SIRA ÖNEMLİ: Önce bu SQL, SONRA kod deploy. (Yeni admin kodu bu sütunu okur/yazar;
-- sütun yokken deploy edilirse İş Durumu penceresi ve ara transfer listesi hata verir.)
-- Bu SQL eski kodu bozmaz: yeni sütun boş (NULL) eklenir, eski kod onu hiç kullanmaz.
--
-- Geri alma (gerekirse, önce kodu eski haline döndür):
--   ALTER TABLE `bookings` DROP COLUMN `tahsilat`;
--   ALTER TABLE `inter_hotel_transfers` DROP COLUMN `tahsilat`;
-- =====================================================================

SET @db := DATABASE();

SET @s := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'tahsilat') = 0,
    'ALTER TABLE `bookings` ADD COLUMN `tahsilat` TINYINT(1) NULL DEFAULT NULL AFTER `is_completed`',
    'SELECT ''bookings.tahsilat zaten var'' AS bilgi'
);
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'inter_hotel_transfers' AND COLUMN_NAME = 'tahsilat') = 0,
    'ALTER TABLE `inter_hotel_transfers` ADD COLUMN `tahsilat` TINYINT(1) NULL DEFAULT NULL AFTER `is_completed`',
    'SELECT ''inter_hotel_transfers.tahsilat zaten var'' AS bilgi'
);
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
