# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

VIP transfer hizmet platformu: **sunlineviptransfer.com**

İki ana bileşen:
- `tour-template/` — BootstrapMade kaynak statik HTML şablonu (referans, doğrudan kullanılmıyor)
- `tour-website/` — Aktif prodüksiyon PHP uygulaması (admin panel + çok dilli frontend)

## Local Development

```bash
# XAMPP sembolik link oluşturma (ilk kurulumda bir kez):
htdocsxamppicin.bat
# Sonrasında: http://localhost/transfer

# Admin panel:
# http://localhost/transfer/admin
# Default: admin / admin123
```

## Deployment

```bash
# cPanel'e otomatik deploy (.cpanel.yml ile):
git push origin main
# → /home/ahmetkes/sunlineviptransfer.com/app klasörüne kopyalanır
# → .env dosyası korunur (üzerine yazılmaz)
```

## Architecture

### URL Routing
`tour-website/index.php` → `router.php` zinciri tüm istekleri yönetir.

URL kalıbı: `/[lang]/[page-slug]`  
Özel bypass: `/api/*` (dil prefix'siz), `/admin` (dil bağımsız)  
Dil algılama sırası: URL prefix → Cookie → `Accept-Language` header → default lang

### Multi-Language System
Tüm dil fonksiyonları `config/config.php` içinde:
- `__($key, $group)` — UI metni çevirisi (`translations` tablosundan, cache'li)
- `getCurrentLang()` — Mevcut URL'den dil kodu
- `langUrl($path, $lang)` — Dil prefix'li URL üretimi
- `getAlternateLanguageUrl($targetLang)` — Aynı sayfanın başka dildeki URL'i

İçerik tabloları için `*_translations` pattern: her entity'nin (destinations, tours, pages, vb.) kendi çeviri tablosu var. Ana tabloda `id` + dil bağımsız alanlar, çeviri tablosunda `(entity_id, language_code)` + dile özgü alanlar.

### Booking System
İki tip rezervasyon: `tour` (TUR-YYYYMMDD-XXXX) ve `transfer` (TRF-YYYYMMDD-XXXX).  
Transfer rezervasyonunda dönüş seçilirse sistem otomatik 2 kayıt oluşturur (`booking_direction`: outbound/return), toplam fiyat ikiye bölünür.

Frontend endpoint: `api/booking.php`  
Admin yönetimi: `admin/bookings.php` + `admin/api/bookings.php`

### Admin API Pattern
`admin/api/handler.php` → `$_POST['entity']` değerine göre ilgili API dosyasına yönlendirir.  
Her CRUD işlemi `$_POST['action']` ile belirlenir: `create`, `update`, `delete`, `quick_status`.

### Database
PDO singleton: `getDB()` fonksiyonu (`config/config.php`)  
Tüm sorgular prepared statement kullanır.  
Output: `e($string)` fonksiyonu (`htmlspecialchars()` wrapper) — tüm kullanıcı verilerinde zorunlu.

### Configuration
`.env` → `config/env.php` → `config/config.php` zinciri.  
`getSetting($key, $default)` — `settings` tablosundan site ayarlarını çeker.

## Key Files

| Dosya | Amaç |
|-------|------|
| `tour-website/index.php` | Ana router (177 satır) |
| `tour-website/router.php` | Route eşleştirme mantığı |
| `tour-website/config/config.php` | Tüm helper fonksiyonlar + DB bağlantısı |
| `tour-website/admin/includes/auth.php` | Admin session yönetimi |
| `tour-website/admin/api/handler.php` | Admin AJAX API router |
| `tour-website/api/booking.php` | Frontend rezervasyon endpoint |
| `tour-website/includes/header.php` | Dil switcher dahil frontend header |
| `tour-website/migrations/` | SQL şema dosyaları |

## Security Notes

- `.env` dosyası git'e commit edilmemeli — `.gitignore`'a eklendiğinden emin ol
- CSRF token: `<meta name="csrf-token">` içinde (session'dan), AJAX isteklerinde gönderilmeli
- Admin sayfaları `require_once '../includes/auth.php'; requireLogin();` ile başlamalı
