-- =====================================================================
-- 034 — Ara transfer id'leri rezervasyon id'lerinden ayrılır (Sezer, 2026-11-22)
--
-- SORUN: bookings ve inter_hotel_transfers ayrı tablolar ama id'leri aynı aralıkta.
-- Aynı numaralı bir rezervasyon ve bir ara transfer olunca (ör. 37 ve 38):
--   * Rezervasyonda "İş yapıldı" / şoför ata → şoför ve iş durumu ARA TRANSFERE yazılıyor
--     (admin/api/bookings.php set_driver + update_ops, id ara transferlerde varsa oraya yazıyor)
--   * Ara transferin voucher'ı aynı numaralı rezervasyonun voucher'ını açıyor (admin/voucher.php)
--   * "Tümü / Günlük" listesinde ikisi yan yana gelince pencereler yanlış kaydı bulabiliyor
-- Ara transfer sayacı 48'deydi; 48+ rezervasyonların çoğu var → her yeni ara transfer çakışacaktı.
--
-- ÇÖZÜM (kod değişmez):
--   1) Bir rezervasyon id'siyle çakışan ara transferler 1.000.000+ numaraya taşınır;
--      bağlı satırlar (booking_drivers.inter_transfer_id, booking_passengers.inter_transfer_id)
--      aynı numaraya güncellenir. Rezervasyonlara (bookings) DOKUNULMAZ.
--   2) Yeni ara transferler 1.000.000'dan başlar (AUTO_INCREMENT) → rezervasyonlarla (şu an ~1.570)
--      bir daha çakışmaz.
-- Ara transferin booking_number'ı (ARA-...) DEĞİŞMEZ; ekranda görünen numara aynı kalır.
--
-- Tekrar çalıştırılabilir: çakışma yoksa hiçbir satır değişmez. Yarıda kalırsa yeniden
-- çalıştırmak aynı numaraları verir ve işi tamamlar. MySQL 8 + MariaDB 10.4 uyumlu.
-- Kod deploy'u gerekmez; SQL tek başına yeter.
--
-- Geri alma (gerekirse): taşınan her ara transfer için (ör. 1000000 → 37)
--   UPDATE booking_drivers    SET inter_transfer_id = 37 WHERE inter_transfer_id = 1000000;
--   UPDATE booking_passengers SET inter_transfer_id = 37 WHERE inter_transfer_id = 1000000;
--   UPDATE inter_hotel_transfers SET id = 37 WHERE id = 1000000;
-- =====================================================================

SET @taban := GREATEST(1000000, (SELECT IFNULL(MAX(`id`), 0) + 1 FROM `inter_hotel_transfers`));

DROP TEMPORARY TABLE IF EXISTS `_ara_id_esle`;
CREATE TEMPORARY TABLE `_ara_id_esle` (
    `eski` INT NOT NULL PRIMARY KEY,
    `yeni` INT NOT NULL UNIQUE
);

INSERT INTO `_ara_id_esle` (`eski`, `yeni`)
SELECT i.`id`, @taban + ROW_NUMBER() OVER (ORDER BY i.`id`) - 1
FROM `inter_hotel_transfers` i
JOIN `bookings` b ON b.`id` = i.`id`;

-- Önce bağlı satırlar, sonra ara transferin kendisi
UPDATE `booking_drivers` bd
JOIN `_ara_id_esle` m ON bd.`inter_transfer_id` = m.`eski`
SET bd.`inter_transfer_id` = m.`yeni`;

UPDATE `booking_passengers` bp
JOIN `_ara_id_esle` m ON bp.`inter_transfer_id` = m.`eski`
SET bp.`inter_transfer_id` = m.`yeni`;

-- updated_at kendiliğinden "şimdi" olmasın (ON UPDATE CURRENT_TIMESTAMP) → mevcut değerinde kalır
UPDATE `inter_hotel_transfers` i
JOIN `_ara_id_esle` m ON i.`id` = m.`eski`
SET i.`id` = m.`yeni`, i.`updated_at` = i.`updated_at`;

-- Yeni ara transferler 1.000.000'dan (ya da en büyük id + 1'den) başlasın
SET @sonraki := GREATEST(1000000, (SELECT IFNULL(MAX(`id`), 0) + 1 FROM `inter_hotel_transfers`));
SET @s := CONCAT('ALTER TABLE `inter_hotel_transfers` AUTO_INCREMENT = ', @sonraki);
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Sonuç (bilgi): taşınan kayıtlar ve kalan çakışma sayısı (0 olmalı)
SELECT m.`eski` AS eski_id, m.`yeni` AS yeni_id, i.`booking_number`
FROM `_ara_id_esle` m JOIN `inter_hotel_transfers` i ON i.`id` = m.`yeni`
ORDER BY m.`eski`;
SELECT COUNT(*) AS kalan_cakisma FROM `inter_hotel_transfers` i JOIN `bookings` b ON b.`id` = i.`id`;

DROP TEMPORARY TABLE IF EXISTS `_ara_id_esle`;
