# Sunline Teması — Site & Anasayfa Yönetimi

Kaynak tasarım: `yeni-tema/index-sunline.html` (Claude Design). Ön yüzün tamamı bu temaya geçirildi (2026-11-10).

## Nerede ne yönetilir?

| Ne | Admin ekranı |
|---|---|
| Renkler (açık + koyu tema, Tweaks paneli), logo, kampanya %'si, WhatsApp | İçerik › **Anasayfa & Tema** › Tema Renkleri / Genel |
| Duyuru şeridi, üst menü, footer, anahtar kelime şeridi | Anasayfa & Tema › Genel (dil sekmesine göre) |
| Hero, hızlı form notu, güven rozetleri | Anasayfa & Tema › Hero & Hızlı Fiyat Formu |
| Bölge kartları başlıkları + filtre düğmeleri | Anasayfa & Tema › Bölgeler & Fiyatlar |
| Bilgilendirme (6 madde), Misafir fotoğrafları, Blog, SSS | Anasayfa & Tema › ilgili kart |
| Anasayfa Google başlığı/açıklaması | Anasayfa & Tema › Anasayfa SEO |
| **Bölge kartları, fiyatlar, bölge sayfası metinleri ve SEO** | Tur Yönetimi › **Transferler** (Öne Çıkan = anasayfa kartı) |
| Araçlar (rezervasyon sihirbazı 1. adım) | Tur Yönetimi › Araçlar |
| Rezervasyon koşulları metni + uyarı mesajı (sihirbaz 4. adım) | Tur Yönetimi › Transferler › Sözleşme / Uyarı |
| Arayüz metinleri (butonlar, sihirbaz, SEO şablonları) | Sistem › Çeviriler › grup **sunline** |
| Diller (TR / EN / DE / RU) | Sistem › Diller |
| Google / Yandex / Bing doğrulama kodları | Sistem › Site Ayarları › SEO |

## Teknik yapı

- Çekirdek: `includes/sunline/core.php` (veri, renk, fiyat, URL, SEO yardımcıları), `includes/sunline/defaults.php` (4 dil varsayılan metin + içerik).
- Anasayfa: `includes/sunline/home.php`; parçalar `includes/sunline/partials/` (kart, hızlı form, SSS, rezervasyon modalı, sayfa başlığı).
- Stil / betik: `assets/css/sunline.css`, `assets/js/sunline.js` (sürüm: `SL_ASSET_VER`).
- İçerik kaydı: `sections` tablosunda `section_type` = `sun_global`, `sun_hero`, `sun_regions`, `sun_info`, `sun_guests`, `sun_blog`, `sun_faq`.
  Dilden bağımsız ayarlar `sections.settings`, dile bağlı içerik `section_translations.settings` (JSON). Kayıtlar admin'de ilk "Kaydet"te oluşur;
  yoksa kod içindeki varsayılanlar gösterilir.
- Renkler: açık ve koyu palet; "Otomatik" modda ziyaretçinin cihazına göre (`prefers-color-scheme`), ziyaretçi üst menüdeki düğmeyle değiştirebilir.
  Kart içindeki yazı/vurgu renkleri okunabilirlik için otomatik ayarlanır (`--acc-t`, `--acc-c`).
- Üstü çizili fiyat: Anasayfa & Tema › Genel › Kampanya %'si (0 = kapalı). Eski fiyat = fiyat × (1 + %) yuvarlanır.
- Fiyat dili: bölge fiyatı aktif dilde yoksa EN, sonra varsayılan dil fiyatı kullanılır (RU için ayrıca fiyat girilmesi gerekmez).

## Rezervasyon sihirbazı (modal)

1. **Araç**: transfer tipi (gidiş-dönüş / tek yön + yön), araç seçimi (kapasite kontrolü)
2. **Transfer**: geliş (uçuş tarihi, iniş saati, uçuş no, otel) / dönüş (uçuş tarihi, kalkış saati, uçuş no, alınış saati, otel)
3. **Yolcular**: yetişkin / çocuk / çocuk koltuğu + iletişim; yetişkin sayısı arttıkça isim alanları açılır (1. yetişkin = iletişim adı)
4. **Onay**: özet, uyarı, not, koşullar → `api/booking.php`

API: fiyatı sunucuda hesaplar, müşteriyi (ad + telefon) bulur/oluşturur, geliş/dönüş kayıtlarını `related_booking_id` ile bağlar,
yolcu isimlerini `booking_passengers`'a yazar, durum `pending`, `admin_notes` = "Web sitesi rezervasyonu (DİL)".

## SEO

- Her bölge sayfası: dile özel başlık/açıklama (boşsa şablon: `seo_dest_t`, `seo_dest_d`), H1, fiyat tablosu, bilgi tablosu,
  "Nasıl rezervasyon yapılır", SSS (FAQPage), diğer bölgeler; JSON-LD: TravelAgency, BreadcrumbList, TaxiService + Offer, FAQPage.
- hreflang (tr/en/de/ru + x-default), kanonik adres, `sitemap.xml` (dil eşleşmeli), `robots.txt` (yapay zekâ botları açık), `llms.txt` + `llms-full.txt`.
- Rusça başlık girilmemiş bölgeler otomatik "Трансфер Сиде" gibi Rusça adla gösterilir (`sl_ru_place_map`).

## Veritabanı

`migrations/026_sunline_tema.sql` — RU dili, SEO ayar alanları, `sunline` çeviri metinleri, KVKK ve Ücretsiz İptal sayfaları.
Geri uyumlu; önce kod yayınlanmalı, sonra SQL çalıştırılmalı.
