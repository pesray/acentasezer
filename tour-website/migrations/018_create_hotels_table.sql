-- Migration 018: Oteller tablosu
CREATE TABLE IF NOT EXISTS hotels (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255)   NOT NULL,
    address      TEXT           NULL,
    phone        VARCHAR(50)    NULL,
    distance_km  DECIMAL(8,2)   NULL,
    is_active    TINYINT(1)     NOT NULL DEFAULT 1,
    sort_order   INT            NOT NULL DEFAULT 0,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP      NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
