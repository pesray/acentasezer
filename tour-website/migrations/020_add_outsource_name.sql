-- Migration: 020_add_outsource_name
-- Dışarıya verilen firma/kişi adı

ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS outsource_name VARCHAR(255) NULL DEFAULT NULL AFTER outsource_price;
