# Rezervasyon Modülü — Yapılanlar Günlüğü

> Bu belge admin panelindeki rezervasyon yönetiminde adım adım yapılan tüm geliştirmeleri kayıt altına alır. Her başlık ayrı bir özelliği veya iyileştirmeyi temsil eder.

---

## 1. Voucher Sistemi

### 1.1 Voucher Butonları (Tüm Listelerde)
- **Tüm rezervasyonlar tablosunda** her satıra TR/EN/DE/RU dropdown'lı voucher butonu eklendi
- **Geliş ve Dönüş listelerinde** de aynı voucher butonu mevcut
- Split-button dropdown yapısı: Ana buton TR voucher açar, dropdown ile dil seçilebilir
- Sadece dönüş kaydı varsa `ret_id`, sadece geliş varsa `out_id`, ikisi varsa her ikisi parametre olarak gönderiliyor

### 1.2 Voucher Tasarım Düzeltmeleri
- `langUrl()` fatal redeclaration hatası → fonksiyon `voucherLangUrl()` olarak yeniden adlandırıldı
- PHP `trim(null)` deprecated uyarısı → null coalescing ile düzeltildi
- **Departure section:** uçuş saati büyük ve belirgin, otelden alış saati altta küçük not ile (TR/EN/DE/RU çevirili)
- **Hem geliş hem dönüş** voucher'ında transfer fiyatı görünür hale getirildi
- **Şirket telefonu** (contact_phone) WhatsApp tıklanabilir buton olarak logonun altında — yeşil çerçeveli, 25D366 renk
- PDF olarak yazdırıldığında WhatsApp linki rengini ve çerçevesini koruyor (`-webkit-print-color-adjust: exact`)
- **Title** müşteri adıyla başlatıldı: `[Müşteri Adı] — [Site Adı] — Voucher #[Booking No]` — PDF dosya adı otomatik müşteri adıyla başlıyor

---

## 2. Site Ayarları (Settings) Modülü

- **Logo upload alanı** site_logo için image type olarak eklendi
- "Headers already sent" uyarısı → POST işlemleri header.php include'undan önce yapılacak şekilde dosya yeniden düzenlendi
- File upload `$_FILES['settings_files'][$key]` notasyonuyla, hidden input mevcut değeri korur
- Migration: `019_add_site_logo_setting.sql` (INSERT IGNORE for site_logo, site_favicon)

---

## 3. Sidebar Yeniden Düzenleme

### 3.1 Daraltılabilir Sidebar
- Toggle butonu ile sidebar daraltılabiliyor (260px → 68px)
- Daraltıldığında sadece ikonlar görünüyor, hover'da flyout submenu açılıyor
- localStorage ile durum kalıcı tutuluyor
- **Rezervasyonlar** ikon hizalama bug'ı → `justify-content: center !important` + iç span'de `gap: 0 !important` ile çözüldü
- Mobile için ayrı responsive davranış (translateX)

### 3.2 Menü Sıralaması
- **Dashboard** ilk sırada
- **Tur Yönetimi** Dashboard'dan hemen sonra:
  - Rezervasyonlar (en üstte) → Tüm Rezervasyonlar (1.) / Geliş (2.) / Dönüş (3.)
  - Turlar, Transferler, Araçlar, Oteller, **Dış Partnerler**
- **İçerik Yönetimi**, Blog & Medya, Ayarlar takip ediyor

---

## 4. Rezervasyon Tablosu — UI İyileştirmeleri

### 4.1 Tarih Navigasyonu (Header)
- Header'da tarih input'u **ortalandı**, solunda `←` (önceki gün), sağında `→` (sonraki gün) butonu
- Sayfa açılışında **bugün otomatik seçili** geliyor ve tablo o güne göre filtreleniyor
- "X" butonu ile filtre temizlenir → tüm rezervasyonlar görünür
- DataTable filtresi "Tüm Rezervasyonlar"da hem **Geliş Tarihi** hem **Gidiş Tarihi** sütunlarını kontrol ediyor (sadece dönüş kaydı olanlar gizlenmiyor)

### 4.2 İşlem Sütunu
- "Tüm Rezervasyonlar" görünümünde:
  - **Geliş varsa** → mavi geliş detay/sil butonları
  - **Dönüş varsa** → açık mavi dönüş detay/sil butonları
  - **Her satırda** voucher butonu görünüyor (out/ret hangisi varsa onun ID'si ile)
- **İş Durumu** butonu (clipboard-check ikonu) eklendi — modal açar, hem operasyon durumunu hem rezervasyon bilgilerini gösterir
- Geliş/Dönüş İş Durumu butonları artık doğru kayda göre koşullu görünüyor

### 4.3 Operasyonel Hücre (Ops Cell)
- **İş yapıldı** ve **Dışarıya verildi** checkbox'ları
- Dışarıya verildi işaretlenince **outsource modal** açılıyor (büyük, modal-lg)
- Tutar input'u küçültüldü (60px width, 0.72rem font, padding 1px 4px)
- Partner adı altta küçük yazı olarak görünüyor

---

## 5. Outsource (Dışarıya Verme) Sistemi

### 5.1 Outsource Modal
- **Üst bilgi grid'i (8 alan):** Rezervasyon No, Müşteri, Tarih, Saat, Otel/Adres, Araç, Kişi, Uçuş No
- **Sarı çerçeveli prominent fiyat satırı** altta
- Ad Soyad/Firma + Tutar input'ları yan yana (col-md-5 + col-md-4 + col-md-3 toggle)
- Modal-lg boyut
- Cancel'da checkbox geri uncheck oluyor (`outsourceSaved` flag ile)

### 5.2 Dönüş Rezervasyonlarında Otelden Alış Saati
- Bilgi grid'inde "Otelden Alış Saati" alanı (sadece dönüş için)
- Input: Ad Soyad ile Tutar arasında "Otelden Alış" time input'u (sadece dönüş için)
- **Migration:** `021_add_outsource_pickup_time.sql` — yeni `outsource_pickup_time TIME` kolonu (pickup_time'a dokunmuyor)

### 5.3 Outsource Partners (Dış Partnerler) Sistemi
- **Migration:** `022_create_outsource_partners.sql` — name, phone, notes, is_active
- **API:** `admin/api/outsource_partners.php` — list, create, update, delete, toggle_active
- **Yönetim sayfası:** `admin/outsource_partners.php` — hotels.php benzeri UI (ekle/düzenle/pasife al/sil)
- Sidebar'da "Dış Partnerler" Tur Yönetimi altında

### 5.4 Outsource Modal'da Partner Select2
- "Ad Soyad / Firma" input'u → Select2 dropdown'a çevrildi
- Yanına `+` butonu — quick-add modal ile yeni partner eklenebiliyor
- Yeni eklenen partner otomatik seçili geliyor
- Tags özelliği ile listede olmayan isim de yazılabiliyor (manuel giriş için)
- Telefon Select2'da gizli, sadece İş Durumu modalında "Kime" satırının altında gösteriliyor

### 5.5 Foreign Key İlişkilendirmesi
- **Migration:** `023_add_outsource_partner_id.sql` — bookings tablosuna `outsource_partner_id` eklendi
- Listeden seçince hem ID hem isim kaydediliyor → güvenilir JOIN, geçmiş takibi
- Elle yazılan isimde ID null kalıyor → geriye uyumluluk
- `get_ops` API ID üzerinden JOIN yapıyor, fallback olarak isimle eşleşiyor
- Partner silinse bile geçmiş `outsource_name` korunuyor

### 5.6 Clear Outsource (Checkbox Uncheck)
- "Dışarıya verildi" checkbox'u kaldırılınca tek API call ile (`clear_outsource`) tüm alanlar sıfırlanıyor:
  - `is_outsourced=0`, `outsource_name=NULL`, `outsource_partner_id=NULL`, `outsource_price=NULL`, `outsource_pickup_time=NULL`

### 5.7 PHP 7.4 PDO Bug Fix
- Production'da `is_completed` ve `is_outsourced` her zaman "Evet" görünüyordu
- Sebep: PHP 7.4 PDO `"0"` string olarak dönüyor, JS'de truthy
- Fix: `get_ops` API'sinde değerler `(int)` cast ediliyor

---

## 6. WhatsApp Mesaj Entegrasyonu

### 6.1 Outsource Modal'da WP Toggle
- Tutarın yanında **"WP Mesaj At"** toggle butonu (yeşil çerçeveli, 38px sabit yükseklik)
- Partner seçilince telefon varsa otomatik aktif, yoksa devre dışı + "(numara yok)" hint
- Tek satıra sığacak şekilde white-space:nowrap

### 6.2 Mesaj Formatı
- **Geliş:** `ANTALYA AİRPORT AYT --> Otel`, tarih-saat, kişi sayısı, *Rezervasyon Tutarı TAHSİLAT*, Hakediş, *YOLCULAR* listesi
- **Dönüş:** `Otel --> ANTALYA AİRPORT AYT`, *OTELDEN ALINIŞ kalın*, Uçuş bilgileri, kişi, tutar, hakediş, yolcular
- WhatsApp `*metin*` ile bold biçimleme
- Yolcular `get_passengers` API'sinden çekiliyor, tek tek listeleniyor

### 6.3 Açılış Mantığı
- Kaydet → toggle açık + telefon varsa `wa.me/[NUMARA]?text=...` yeni sekmede açılıyor
- Telefon `[^0-9]` regex ile temizleniyor

---

## 7. İş Durumu Modal'ı

- Modal-lg boyut
- **Üstte:** Mavi çerçeveli rezervasyon bilgileri grid'i (Rezervasyon No, Müşteri, Tarih, Uçuş Saati, Otel, Araç, Kişi, Uçuş No, Tutar)
- **Dönüş rezervasyonlarında:** Otelden Alış Saati de gösteriliyor
- **Altta:** "Operasyon Durumu" başlığıyla badge'ler (Evet/Hayır)
- **Dışarıya verilmişse:** Sarı kutuda Kime (telefon ile), Alış Saati, Verilen Tutar
- Bilgiler `bookingsData` JS array'inden anında render ediliyor (AJAX beklemez)
- Operasyon durumu `get_ops` API'sinden geliyor

---

## 8. Yeni Rezervasyon Ekleme Modalı

### 8.1 Sadece Dönüş Rezervasyonu Desteği
- Daha önce dönüş seçilse de hem geliş hem dönüş kaydı oluşturuyordu
- **Geliş Transferi Ekle** ve **Dönüş Transferi Ekle** olmak üzere iki ayrı toggle eklendi
- Dönüş view'inde sadece dönüş açık geliyor → tek kayıt oluşturuluyor
- En az biri seçili olmak zorunda (otomatik kontrol)
- API: `has_outbound` ve `has_return` flag'lerine göre koşullu insert

### 8.2 Form Düzeni
- **Üstte:** Transfer + Araç (yarı yarı, col-md-6)
- **Altta:** Müşteri Bilgileri
- **Geliş Transferi bölümünde:** Otel/Adres select2 + Geliş Fiyatı yan yana
- Geliş Fiyatı'nın yeri Transfer/Araç satırından çıkarılıp Otel satırına taşındı

### 8.3 Hızlı Otel Ekleme
- Her otel select'in yanında yeşil **+** butonu
- Modal: Otel Adı (zorunlu) + Adres + Mesafe + Telefon
- Hotels API `create` ile kaydediliyor
- Hem geliş hem dönüş select'lerine eklenip tıklanan select'te otomatik seçiliyor

### 8.4 Uçuş Numarası Otomatik Büyük Harf
- Geliş, Dönüş ve Edit modallarındaki tüm `flight_number` input'ları `flight-number-upper` class'ı ile
- CSS `text-transform: uppercase` görsel için, JS `input` event ile gerçek değeri büyük harfe çeviriyor
- Cursor pozisyonu korunuyor

---

## 9. Rezervasyon Düzenleme Modalı (Edit)

### 9.1 Yapısal Değişiklik (Add Modal Benzeri)
- Üst bilgi (Rezervasyon No, Tür, Oluşturulma) ve Durum sekmesi korundu
- **Tur/Transfer ve Araç:** Readonly text → Select2 dropdown'lar
- **Yön başlığı:** Geliş ise primary mavi "Geliş Uçuş Bilgileri", dönüş ise info mavi "Dönüş Uçuş Bilgileri"
- Otel/Adres: Select2 + hızlı otel ekleme (+) butonu
- **Otelden Alış Saati** sadece dönüş rezervasyonlarında görünüyor

### 9.2 Fiyat & Para Birimi
- Fiyat ve para birimi tek input-group içinde, otel satırının yanında
- **Geliş modalında:** Otel(7) + Fiyat(5) = 12 (alış saati gizli, fiyat genişlemiş)
- **Dönüş modalında:** Otel(7) + Otelden Alış(2) + Fiyat(3) = 12

### 9.3 API Güncellemesi
- `update` action'ı artık `destination_id` ve `vehicle_id`'yi de güncelliyor

---

## 10. Dashboard (admin/index.php)

- Eski dashboard içeriği (stat cards + son rezervasyonlar/mesajlar) kaldırıldı
- Bookings.php içeriği reuse ediliyor (`BOOKINGS_AS_DASHBOARD` constant ile)
- **Varsayılan tab:** Tüm Rezervasyonlar
- **Tarih filtresi otomatik bugüne ayarlanmıyor** (boş geliyor, kullanıcı seçince filtreliyor)
- Bookings.php her zaman header/footer include eder, dashboard sadece flag set edip include yapar
- jQuery yükleme sırası bozulmadan çalışıyor

---

## 11. Diğer İyileştirmeler

### 11.1 Destinations Tablosu Vehicle Count Bug
- `destinations.php` satır 737 → `COUNT(*)` araç sayısını dil/para birimi kombinasyonu sayısı olarak döndürüyordu (3 araç → 9 görünüyordu)
- Fix: `COUNT(DISTINCT vehicle_id)` — şimdi doğru sayıyor

---

## Veritabanı Migrationları (Sırasıyla)

| # | Dosya | Açıklama |
|---|-------|----------|
| 019 | `019_add_site_logo_setting.sql` | site_logo, site_favicon settings ekle |
| 020 | `020_add_outsource_name.sql` | bookings.outsource_name kolonu |
| 021 | `021_add_outsource_pickup_time.sql` | bookings.outsource_pickup_time kolonu |
| 022 | `022_create_outsource_partners.sql` | outsource_partners tablosu |
| 023 | `023_add_outsource_partner_id.sql` | bookings.outsource_partner_id kolonu |

---

## Şu Anda Bulunduğumuz Nokta

✅ Rezervasyon listesi (geliş / dönüş / tüm) tamam
✅ Yeni rezervasyon ekleme (sadece geliş / sadece dönüş / her ikisi) tamam
✅ Rezervasyon düzenleme (geliş / dönüş yönüne göre dinamik) tamam
✅ Outsource sistemi (partnerler + WhatsApp) tamam
✅ Voucher sistemi (4 dilli, müşteri adıyla PDF) tamam
✅ İş durumu takibi tamam
✅ Dashboard rezervasyon listesi tamam

## Sırada Olabilecekler (henüz yapılmadı)

- [ ] Toplu işlemler (multiple selection + bulk update/delete)
- [ ] Tarih aralığı filtresi (range picker)
- [ ] Müşteri arama/filtreleme (telefon, e-posta)
- [ ] Excel/PDF export
- [ ] Partner bazlı raporlama (hangi partnere toplam ne kadar iş verildi, kazançları)
- [ ] Aylık/haftalık özet dashboard widget'ları
- [ ] Otel bazlı transfer raporu
- [ ] Müşteri kaydı (recurring customer detection)
