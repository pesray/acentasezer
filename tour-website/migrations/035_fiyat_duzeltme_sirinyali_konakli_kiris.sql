-- =====================================================================
-- 035 — Bölge fiyatı düzeltmeleri (Sezer, 2026-11-22)
--
--   1) Şirinyalı (destination 27) TR sekmesi: para birimi € seçiliydi ama rakamlar TL
--      (1.700 / 2.000 / 2.300 / 2.500 / 2.800 — Lara ve Kundu TL fiyatlarıyla aynı).
--      → para birimi TRY; rakamlar aynı kalır. (5 satır)
--   2) Konaklı (destination 38) TR sekmesi Large Vip 1-13 (vehicle 3): 4.800 ₺ → 4.500 ₺
--      (Konaklı €/£/$ fiyatları Türkler'le birebir aynı; Türkler TL Large 1-13 = 4.500 ₺) (1 satır)
--   3) Kiriş (destination 23) RU sekmesi Vip Minivan 5-6 (vehicle 4): 70 $ → 65 $
--      (Kiriş €/£/₺ fiyatları Çamyuva ve Tekirova ile aynı; ikisinde de 65 $) (1 satır)
--
-- Her UPDATE eski değeri de kontrol eder: değer beklenenden farklıysa (ör. panelden
-- değiştirilmişse) o satıra dokunmaz. Tekrar çalıştırılabilir. Toplam 7 satır değişir.
--
-- Geri alma:
--   UPDATE destination_vehicles SET currency = 'EUR' WHERE destination_id = 27 AND language_code = 'tr';
--   UPDATE destination_vehicles SET price = 4800 WHERE destination_id = 38 AND language_code = 'tr' AND vehicle_id = 3;
--   UPDATE destination_vehicles SET price = 70   WHERE destination_id = 23 AND language_code = 'ru' AND vehicle_id = 4;
-- =====================================================================

UPDATE `destination_vehicles`
SET `currency` = 'TRY'
WHERE `destination_id` = 27 AND `language_code` = 'tr' AND `currency` = 'EUR'
  AND `price` IN (1700, 2000, 2300, 2500, 2800);

UPDATE `destination_vehicles`
SET `price` = 4500
WHERE `destination_id` = 38 AND `language_code` = 'tr' AND `vehicle_id` = 3
  AND `price` = 4800 AND `currency` = 'TRY';

UPDATE `destination_vehicles`
SET `price` = 65
WHERE `destination_id` = 23 AND `language_code` = 'ru' AND `vehicle_id` = 4
  AND `price` = 70 AND `currency` = 'USD';

-- Sonuç (bilgi)
SELECT `destination_id`, `language_code`, `vehicle_id`, `price`, `currency`
FROM `destination_vehicles`
WHERE (`destination_id` = 27 AND `language_code` = 'tr')
   OR (`destination_id` = 38 AND `language_code` = 'tr' AND `vehicle_id` = 3)
   OR (`destination_id` = 23 AND `language_code` = 'ru' AND `vehicle_id` = 4)
ORDER BY `destination_id`, `vehicle_id`;
