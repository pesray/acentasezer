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
| **Sitede listelenen para birimi**, araçta nakit ödeme seçenekleri, çocuk koltuğu / yükseltici ücreti | Anasayfa & Tema › **Para Birimi & Ekstralar** |
| **Bölge kartları, fiyatlar, bölge sayfası metinleri ve SEO** | Tur Yönetimi › **Transferler** (Öne Çıkan = anasayfa kartı) |
| Araçlar (rezervasyon sihirbazı 3. adım) | Tur Yönetimi › Araçlar |
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
## Para birimi (2026-11-12'den itibaren)

- Fiyat **dile göre değil para birimine göre** seçilir. Sitede (tüm dillerde) tek para birimi listelenir:
  Anasayfa & Tema › Para Birimi & Ekstralar › "Sitede listelenen para birimi" (varsayılan **EUR**).
- Fiyatlar yine Tur Yönetimi › Transferler ekranındaki dil sekmelerinde, para birimi seçilerek girilir (ekran değişmedi).
  Aynı para birimi birden fazla sekmede girildiyse en çok aracı fiyatlı sekme, eşitlikte DE → EN → TR → RU sırası kullanılır.
- Bir transferde varsayılan para biriminde fiyat yoksa o transfer sırasıyla EUR / USD / GBP / TRY fiyatıyla gösterilir.
- Onay adımında "**araçta nakit**" ödeme para birimi seçilir: varsayılan seçili gelir, işaretli diğer para birimleri
  (o araç için fiyatı girilmiş olanlar) seçenek olarak çıkar. Rezervasyon seçilen para birimi + o para birimindeki fiyatla kaydedilir.

## Çocuk koltuğu & yükseltici ücreti

- Sihirbazda çocuk koltuğu (araçtaki koltuk sayısı ve çocuk sayısıyla sınırlı) ve **yükseltici** (sınırsız; teknik üst sınır 20) seçilir.
- Toplamda **1 adet** çocuk koltuğu **veya** yükseltici ücretsiz (açılıp kapatılabilir); ücretsiz hak önce pahalı olana uygulanır.
- Ücretler para birimi başına girilir; boş bırakılan para biriminde ücret, girilen ücretten aracın fiyat oranıyla çevrilir
  (100 altı tam sayıya, üstü 10'a yuvarlanır). 0 = ücretsiz. Hiç ücret girilmezse tüm koltuklar ücretsizdir.
- Uygulama: **transfer başına** (gidiş-dönüşte her kayda) veya **rezervasyon başına** (yalnız ilk kayda). Ücret `total_price`'a eklenir.
- Yükseltici için ayrı sütun yok: müşteri notunun başına `YÜKSELTİCİ: N` yazılır (WhatsApp şablonlarındaki `{NOTES}` ile sürücüye gider);
  `admin_notes`'a ödeme para birimi ve ekstra ücret dökümü yazılır.

## Rezervasyon sihirbazı (modal)

Sıra (2026-11-12'den itibaren): **Transfer → Yolcular → Araç → Onay**

1. **Transfer**: en üstte transfer tipi (gidiş-dönüş / tek yön) ve tek yönde yön (Havalimanı → Otel / Otel → Havalimanı);
   altında geliş (uçuş tarihi, iniş saati, uçuş no, otel) / dönüş (uçuş tarihi, kalkış saati, uçuş no, alınış saati, otel).
   **Uçuş no zorunlu**, yazarken büyük harfe çevrilir (ör. `TK 2412`); yanında kart formlarındaki CVV bilgisi gibi **(i)** bilgi balonu var
2. **Yolcular**: yetişkin / çocuk / çocuk koltuğu / yükseltici + iletişim (e-posta isteğe bağlı); yetişkin sayısı arttıkça isim alanları açılır
   (1. yetişkin = iletişim adı). **Yolcu isimleri yazarken büyük harfe çevrilir** (Türkçede i → İ). Sayı sınırı: o bölgede fiyatı olan en büyük aracın kapasitesi
3. **Araç**: girilen yolcu ve çocuk koltuğu sayısına uymayan araçlar pasif ve uyarılı ("kapasite yetersiz" / "en fazla N çocuk koltuğu");
   yolcu sayısı sonradan artarsa uymayan seçili araç otomatik kaldırılır
4. **Onay**: özet (araç fiyatı, ekstralar, toplam), **ödeme para birimi seçimi**, uyarı, not, koşullar → `api/booking.php`

Adım başlıkları çeviri anahtarlarından gelir (anlamına göre): `step2` Transfer, `step3` Yolcular, `step1` Araç, `step4` Onay.

API: fiyatı ve ek koltuk ücretini sunucuda (seçilen para biriminde) hesaplar, uçuş numarasını doğrular, müşteriyi (ad + telefon) bulur/oluşturur,
geliş/dönüş kayıtlarını `related_booking_id` ile bağlar, yolcu isimlerini büyük harfle `booking_passengers`'a yazar, durum `pending`,
`admin_notes` = "Web sitesi rezervasyonu (DİL) · Ödeme: araçta nakit PARA · Ekstra: …".

## SEO

- Her bölge sayfası: dile özel başlık/açıklama (boşsa şablon: `seo_dest_t`, `seo_dest_d`), H1, fiyat tablosu, bilgi tablosu,
  "Nasıl rezervasyon yapılır", SSS (FAQPage), diğer bölgeler; JSON-LD: TravelAgency, BreadcrumbList, TaxiService + Offer, FAQPage.
- hreflang (tr/en/de/ru + x-default), kanonik adres, `sitemap.xml` (dil eşleşmeli), `robots.txt` (yapay zekâ botları açık), `llms.txt` + `llms-full.txt`.
- Rusça başlık girilmemiş bölgeler otomatik "Трансфер Сиде" gibi Rusça adla gösterilir (`sl_ru_place_map`).

## Veritabanı

`migrations/026_sunline_tema.sql` — RU dili, SEO ayar alanları, `sunline` çeviri metinleri, KVKK ve Ücretsiz İptal sayfaları.
Geri uyumlu; önce kod yayınlanmalı, sonra SQL çalıştırılmalı.

`migrations/027_sunline_sihirbaz_sirasi.sql` — isteğe bağlı; sihirbazın yeni metinlerini (adım sırası, yükseltici, ödeme para birimi,
uçuş no bilgisi) Çeviriler › sunline grubuna ekler. Şema değişikliği yok; para birimi/ücret ayarları `sun_global` bölümünde saklanır.
