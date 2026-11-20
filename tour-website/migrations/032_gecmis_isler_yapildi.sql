-- =====================================================================
-- 032 — Geçmiş işleri "İş yapıldı" yap (Sezer, 2026-11-20)
-- Tarihi 20.11.2026'dan ÖNCE olan, panelde "Onaylandı" durumunda ve "İş yapıldı"
-- işaretlenmemiş işler → is_completed = 1:
--   bookings              (transfer: Arrival / Depar; tarih = flight_date)
--   inter_hotel_transfers (ara transfer; tarih = pickup_date)
-- DOKUNULMAYANLAR: "Onay Bekliyor" (siteden gelip onaylanmamış), İptal, silinmiş
-- kayıtlar, 20.11.2026 ve sonrası. Yalnız is_completed + updated_at değişir;
-- şoför ataması yapılmaz (panelde elle işaretlerken açılan şoför seçimi burada yok).
-- Tarih sabit (20.11.2026): dosya başka gün çalıştırılsa da kapsam değişmez.
-- Tekrar çalıştırılabilir: ikinci kez çalışınca değişecek kayıt kalmaz.
-- Değiştirilen kayıtların listesi `yedek_032_yapildi` tablosunda tutulur (geri alma için).
-- MySQL 8 + MariaDB 10.4 uyumlu.
--
-- Geri alma (yalnız bu dosyanın "yapıldı" yaptıklarını eski haline döndürür):
--   UPDATE bookings b JOIN yedek_032_yapildi y ON y.tablo = 'bookings' AND y.kayit_id = b.id SET b.is_completed = 0;
--   UPDATE inter_hotel_transfers i JOIN yedek_032_yapildi y ON y.tablo = 'inter_hotel_transfers' AND y.kayit_id = i.id SET i.is_completed = 0;
-- =====================================================================

CREATE TABLE IF NOT EXISTS `yedek_032_yapildi` (
    `tablo`    VARCHAR(30) NOT NULL,
    `kayit_id` INT NOT NULL,
    `eklendi`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`tablo`, `kayit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `yedek_032_yapildi` (`tablo`, `kayit_id`)
SELECT 'bookings', `id` FROM `bookings`
WHERE `is_completed` = 0
  AND `is_deleted` = 0
  AND `booking_type` = 'transfer'
  AND `booking_status` = 'confirmed'
  AND `flight_date` >= '2000-01-01'
  AND `flight_date` < '2026-11-20';

INSERT IGNORE INTO `yedek_032_yapildi` (`tablo`, `kayit_id`)
SELECT 'inter_hotel_transfers', `id` FROM `inter_hotel_transfers`
WHERE `is_completed` = 0
  AND `is_deleted` = 0
  AND `booking_status` = 'confirmed'
  AND `pickup_date` >= '2000-01-01'
  AND `pickup_date` < '2026-11-20';

UPDATE `bookings` b
JOIN `yedek_032_yapildi` y ON y.`tablo` = 'bookings' AND y.`kayit_id` = b.`id`
SET b.`is_completed` = 1, b.`updated_at` = NOW()
WHERE b.`is_completed` = 0
  AND b.`is_deleted` = 0
  AND b.`booking_type` = 'transfer'
  AND b.`booking_status` = 'confirmed'
  AND b.`flight_date` >= '2000-01-01'
  AND b.`flight_date` < '2026-11-20';

UPDATE `inter_hotel_transfers` i
JOIN `yedek_032_yapildi` y ON y.`tablo` = 'inter_hotel_transfers' AND y.`kayit_id` = i.`id`
SET i.`is_completed` = 1, i.`updated_at` = NOW()
WHERE i.`is_completed` = 0
  AND i.`is_deleted` = 0
  AND i.`booking_status` = 'confirmed'
  AND i.`pickup_date` >= '2000-01-01'
  AND i.`pickup_date` < '2026-11-20';

-- Sonuç: kaç iş "yapıldı" yapıldı
SELECT `tablo`, COUNT(*) AS `yapildi_yapilan_is` FROM `yedek_032_yapildi` GROUP BY `tablo`;
