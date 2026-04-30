# Proje Kuralları & Teknik Kısıtlar

> Bu dosya proje boyunca uyulacak kuralların özet referansıdır. Yapılacak her geliştirmede önce buraya bakılır.

---

## Teknoloji Stack

| Katman | Teknoloji | Sürüm |
|--------|-----------|-------|
| Backend | PHP | **7.4** (production cPanel) |
| Veritabanı | MySQL | utf8mb4_unicode_ci |
| Frontend | Bootstrap | 5.3.x |
| JS Kütüphaneleri | jQuery 3.7, Select2 4.1, DataTables 1.13, Summernote 0.8 |
| İkonlar | Bootstrap Icons 1.11 |
| Local Dev | XAMPP (PHP 8.2 olabilir, ama kod 7.4 uyumlu yazılır) |
| Deployment | cPanel auto-deploy via `.cpanel.yml` |

---

## PHP 7.4 Uyumluluğu — YASAKLAR

PHP 8+ syntax'ı **KESİNLİKLE KULLANILMAZ**:

- ❌ `match()` expression → `switch` veya `if/else` kullan
- ❌ Named arguments → positional arguments kullan
- ❌ Nullsafe operator (`?->`) → `isset()` veya null coalescing
- ❌ Constructor property promotion → klasik constructor
- ❌ `enum` → const veya class
- ❌ `readonly` properties
- ❌ Mixed type, never type, intersection types
- ❌ First-class callable syntax (`func(...)`)
- ❌ `str_contains`, `str_starts_with`, `str_ends_with` → `strpos !== false` kullan
- ❌ Array unpacking with string keys → manual `array_merge`

**Kullanılabilir PHP 7.4 özellikleri:**
- ✅ Arrow functions (`fn() =>`)
- ✅ Null coalescing assignment (`??=`)
- ✅ Typed properties
- ✅ Spread operator (only numeric keys)

---

## Çoklu Dil Sistemi

- Diller `languages` tablosundan dinamik yönetilir (admin panelden eklenip çıkarılabilir)
- Aktif diller `getActiveLanguages()` ile alınır (cache'li)
- Tüm UI metinleri `__($key, $group)` ile çevrilir (`translations` tablosundan)
- İçerik tabloları için `*_translations` pattern (her entity'nin kendi çeviri tablosu)
- Dil algılama sırası: URL prefix → Cookie → `Accept-Language` → default
- Mevcut diller: tr (default), en, de (pasif), ru (pasif), ar (pasif)
- **Yeni özellik geliştirirken:** çeviri tablosu varsa kullan, yoksa multi-lang gerektirenler için tablo öner

---

## Mimari Standartlar

### Database
- Tüm sorgular **prepared statements** (PDO)
- Tek connection: `getDB()` singleton
- Migration dosyaları `migrations/NNN_aciklama.sql` formatında (sıralı numaralı)

### Output
- Tüm kullanıcı verisi `e($string)` ile escape edilir (`htmlspecialchars` wrapper)
- JSON response'lar: `jsonResponse($success, $message, $data)` helper'ı

### Admin API
- Endpoint: `admin/api/handler.php?entity=X` → `entity` ile dosya routing
- Action: `$_POST['action']` ile alt işlem (create, update, delete, vb.)
- CSRF token zorunlu (POST'larda)
- Auth: session bazlı, `requireLogin()` her admin sayfasında

### Frontend (Site)
- Router: `index.php` → `router.php` → `pages/X.php`
- URL pattern: `/[lang]/[page-slug]`
- API endpoints (`/api/*`) dil prefix'inden bypass edilir

---

## Tasarım & UX Kuralları

### Genel
- **Responsive ön planda** — mobile, tablet, desktop hepsinde sorunsuz
- **Dinamik state'ler localStorage'da kalıcı** (sidebar collapse, tema vb.)
- **Site ayarlarından** dinamik veri (logo, site adı, vb.) — hiçbir şey hardcoded değil
- **Bootstrap 5 utility class'ları** öncelikli, custom CSS minimum
- **Tutarlı görsel dil:** primary mavi `#4e73df`, başarı yeşil `#1cc88a`, uyarı sarı `#f6c23e`, tehlike kırmızı `#e74a3b`, info açık mavi

### Admin Panel UI (v2 — 2026-04-28 redesign)
- **Font:** Inter (Google Fonts)
- **Sidebar:** beyaz arka plan, sol kenara hafif border, modern minimal görünüm (Vercel/Linear/Notion ilhamı)
  - 264px açık / 76px daralmış
  - Logo + site adı + alt yazı (Yönetim Paneli) — site_name + site_logo settings'den dinamik
  - Active link: açık mor arka plan (`#eef2ff`) + sol kenarda 3px mor barr
  - Section başlıkları: küçük, gri, uppercase
  - Alt kısımda kullanıcı kartı (avatar + ad + rol)
- **Renk paleti (yeni):**
  - Primary: `#4f46e5` (indigo-600)
  - Hover: `#f3f4f6`
  - Active bg: `#eef2ff`
  - Text: `#1f2937` (slate-800)
  - Muted: `#6b7280`, `#9ca3af`
  - Border: `#e9ecef`
- **Top navbar:**
  - Toggle butonu solda (38x38 outline)
  - Page title + tarih (md+ ekranlarda)
  - Sağda: "Siteyi Görüntüle" outline butonu, kullanıcı dropdown (avatar + isim)
- **Card-based tasarım:** `box-shadow: 0 1px 2px rgba(0,0,0,.04)` (daha hafif)
- **Border radius:** 10px (cards, buttons, modals)
- **Mobile (≤991px):**
  - Sidebar gizli, hamburger ile açılır
  - Sağdan koyu overlay arka plan (tıklayınca kapanır)
  - ESC tuşu ile kapatma
  - Sidebar içindeki link tıklaması otomatik kapatır
- **Responsive davranış:** desktop collapse state localStorage'da, mobile toggle ayrı
- **Flyout submenu:** sidebar daraltıldığında hover ile yan tarafta beyaz floating menu (eski gradient değil)

### Frontend Site
- Tema kaynağı: `tour-template/` (BootstrapMade)
- Renkler: accent `#008cad` (teal), heading `#1c4b56`
- Fontlar: Roboto (body), Raleway (heading), Poppins (nav)

---

## Çalışma Yaklaşımı

- **UI tasarımı kararlarını ben veriyorum** — kullanıcı onay vermek dışında detayda boğulmuyor
- **Adım adım ilerle**, küçük commit'ler önerilebilir
- **Test edilmeden production'a gönderilmez** — local'de doğrulanır
- **Geriye uyumluluk** önemli ama eski kayıtlar test ortamında ise feda edilebilir
- **PHP 7.4 syntax** her zaman test öncesi kontrol edilir

---

## Mevcut Durum (2026-04-28)

- ✅ Rezervasyon modülü tamamlandı (detaylar `rezervasyon_yapilanlar.md`)
- ✅ **Admin panel sidebar redesign** — beyaz minimal, Inter font, dinamik logo/site adı, modern flyout, gelişmiş mobile davranış
- ✅ **Dark mode** eklendi — top navbar'da ay/güneş toggle, localStorage kalıcı, flash'sız yükleme, Bootstrap 5.3 `data-bs-theme` ile entegre
- ✅ **Mobile responsive iyileştirmeleri** (header.php global CSS) — tablo yatay scroll, modal full-screen <576px, page header stack, button compact, DataTables filter stack, touch-friendly tap targets, nav-tabs scroll
- ✅ **Bookings tablosu mobil-spesifik:** view-X wrapper'ları + nth-child gizleme stratejisi
- ✅ **Zebra stripe tablo satırları** — tüm `.table-hover` tablolarda otomatik (light + dark uyumlu)
- 🔜 Sırada: **Site yönetimi (frontend yönetimi)** — Anasayfa içerik yönetiminden başla, çoklu dil sistemi düzeltmeleri, SEO odaklı tüm sayfaların admin'den yönetilebilirliği
- Sonrasında: Diğer admin sayfaları polish, raporlama, dashboard widget'ları

## SEO & Çoklu Dil Kuralları (Frontend Yönetimi için)

- **Her sayfa SEO odaklı:** title, meta_description, meta_keywords, og_image, canonical URL admin'den düzenlenebilmeli
- **Tüm içerik tabloları çoklu dil destekli:** `*_translations` pattern — her aktif dil için kayıt
- **Yeni dil eklendiğinde** dinamik olarak çeviri inputları görünmeli — hardcoded `tr/en/de` yok
- **Schema.org structured data** uygun yerlerde (TourPackage, Hotel, Organization, BreadcrumbList)
- **Sitemap.xml** otomatik üretilmeli (tüm dillerin URL'leri ile)
- **hreflang** tag'leri her sayfada otomatik (mevcut `getAlternateLanguageUrl()` fonksiyonu kullan)
- **Image alt** ve **title** attributeleri tüm medyalar için zorunlu

## URL Slug Sistemi (SEO odaklı)

- **Her içerik tipinde slug zorunlu:** pages, tours, destinations, blog_posts, vb.
- **Slug her dil için ayrı:** `page_translations.slug`, `tour_translations.slug` — aynı içerik farklı dillerde farklı slug'lara sahip olabilir
- **Otomatik slug üretimi:** title girilince otomatik slug öner (`title` blur event), kullanıcı isterse manuel düzenler
- **Türkçe karakter normalizasyonu:** ı→i, ğ→g, ü→u, ş→s, ö→o, ç→c, boşluk→-, lowercase
- **URL pattern:** `/[lang]/[content-type]/[slug]` — Örn: `/tr/turlar/kappadokya-balon-turu`, `/en/tours/cappadocia-balloon-tour`
- **Unique constraint:** her dil içinde benzersiz olmalı (`UNIQUE KEY (slug, language_code)`)
- **301 redirect:** slug değiştirilirse eski URL → yeni URL yönlendirme (gelecekte eklenecek)
- **Reserved slug'lar:** `admin`, `api`, `assets`, `uploads`, `tr`, `en`, `de`, `ru`, `ar` (dil kodları) — kontrol et, kullandırma
- **Slug input'u:** her zaman görünür, manuel düzenlenebilir, otomatik üretim sadece boşken çalışır

## Admin'den Yönetilebilen Tüm Site Yapısı

Frontend'in her parçası admin panelden yönetilebilmeli:

| Alan | Admin Sayfası | Tablo |
|------|--------------|-------|
| Anasayfa section'lar | homepage.php | sections + section_translations |
| Statik sayfalar (Hakkımızda, KVKK, vb.) | pages.php | pages + page_translations |
| Menüler (header/footer) | menus.php | menus + menu_items + menu_item_translations |
| Slider'lar | sliders.php | sliders + slider_translations |
| Tur paketleri | tours.php | tours + tour_translations + tour_categories |
| Destinasyonlar/Transferler | destinations.php | destinations + destination_translations + destination_vehicles |
| Araçlar | vehicles.php | vehicles + vehicle_services |
| Oteller | hotels.php | hotels |
| Müşteri yorumları | testimonials.php | testimonials + testimonial_translations |
| Blog yazıları | blog.php | blog_posts + blog_post_translations + blog_categories |
| Galeri | gallery.php | gallery + gallery_categories |
| SSS | faq.php | faqs + faq_translations + faq_categories |
| Features (özellikler) | features.php | features + feature_translations |
| İletişim formları | contacts.php | contacts |
| UI metinleri (header/footer/buttons) | translations.php | translations |
| Site genel ayarları | settings.php | settings |
| Diller | languages.php | languages |

## Mobile Responsive Pattern (Tüm admin sayfaları)

`includes/header.php` içinde global CSS var. Ek bir şey gerekmiyorsa sayfa otomatik responsive. Sayfa-spesifik gereksinimler için pattern:

- **Tablolar:** `<div class="table-responsive">` ile sar, sonra `.view-X` wrapper içinde `nth-child` ile mobile'da gizleme stratejisi uygula
- **Stat cards:** col-xl-3 col-md-6 (otomatik mobile'da 1 sütun)
- **Page header:** `<div class="d-flex justify-content-between align-items-center mb-4">` → mobile'da otomatik stack
- **Modal:** Default boyutlarda mobil için otomatik full-screen davranış var (<576px)
- **Form satırları:** `<div class="row">` + `col-md-X` kullan — mobile'da otomatik stack
- **Mobile-only gizle:** `.d-mobile-hide` utility class kullan (≤575px)
