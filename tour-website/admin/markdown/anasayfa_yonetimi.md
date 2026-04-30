# Anasayfa İçerik Yönetimi — Yapılanlar

## Tamamlandı (Faz 1.1)

### `admin/homepage.php` — Çoklu Dil Desteği

**Önceden:**
- Form sadece `sections` base table'a yazıyordu
- Üstteki dil dropdown'u kozmetik (TR/EN hardcoded), hiçbir şey yapmıyordu
- SEO meta'lar `pages` tablosuna gidiyordu (yanlış — `page_translations` olmalıydı)

**Şimdi:**

#### Dinamik Dil Tab'ları
- `languages` tablosundan aktif diller çekiliyor (sıralı, default başta)
- Her dil için ayrı **tab butonu** üst kısımda — bayrak + native_name + Varsayılan badge
- Yeni dil eklenince/aktifleşince otomatik tab görünüyor — hardcoded yok
- Tab tıklayınca tüm SEO ve section input'ları o dile geçiyor (`.lang-pane.active`)

#### SEO Bölümü (Çoklu Dil)
- **Her dil için ayrı SEO**:
  - URL Slug (`page_translations.slug`)
  - Meta Başlık (60 karakter sayacı)
  - Meta Açıklama (160 karakter sayacı)
  - Anahtar Kelimeler
- `INSERT ... ON DUPLICATE KEY UPDATE` — yeni dil eklenince auto-insert, mevcut dil güncellenir
- Default dil için slug "/" (kök), diğer diller için "home-{code}" varsayılan
- Karakter sayacı renkli geri bildirim (limit aşılırsa kırmızı)

#### Section Yönetimi (Çoklu Dil)

**Dil-bağımsız alanlar (her dil için ortak):**
- Background Image (hero, why_us, cta için)
- Background Video (hero için)
- `is_active` (görünürlük toggle)

**Dil-bağımlı alanlar:**
- title, subtitle, content
- settings JSON (button text/url, form labels, stats, limit, vb.)

**Submit mantığı:**
1. `sections` base table → default dilin değerleri (fallback için) + dil-bağımsız alanlar
2. `section_translations` → her dil için ayrı `INSERT ... ON DUPLICATE KEY UPDATE`
3. Tek formda tüm diller, tek "Kaydet" tuşu

#### 6 Section Tipi Tam Destekli
- **Hero:** Title, subtitle, video, image, 2 buton (text+url), rezervasyon formu toggle, form başlığı
- **Why Us:** Title, content, image (ortak), experience badge/text, 3 stat (sayı + label)
- **Featured Destinations:** Title, subtitle, limit, sadece-öne-çıkanlar toggle
- **Featured Tours:** Title, subtitle, limit, featured-only, view-all button toggle, view-all url
- **Testimonials:** Title, subtitle, limit, autoplay delay
- **CTA:** Title, subtitle (rozet), content, image (ortak), 2 buton, telefon, contact label

### Frontend Düzeltme — `includes/sections.php`

`getPageSections()` fonksiyonuna `settings` için COALESCE eklendi:
```php
COALESCE(st.settings, s.settings) as settings
```
Artık dile özgü settings (örn: TR'de buton metni "Şimdi Rezervasyon", EN'de "Book Now") doğru çekiliyor.

## Test Edilmesi Gerekenler

- [ ] TR dilinde anasayfa düzenle → frontend TR'de değişti mi?
- [ ] EN tab'ına geç, başka değer gir → kaydet → EN frontend'de farklı mı?
- [ ] Boş bırakılan dil alanı default fallback'e mi düşüyor?
- [ ] Görünürlük toggle çalışıyor mu (her dilde ortak)?
- [ ] Yeni dil eklendiğinde otomatik tab görünüyor mu?
- [ ] SEO meta tag'leri her dilde farklı mı? (frontend tarafında)

## Sırada (Faz 1.2 / 1.3)

- Frontend `header.php` SEO tag'lerinin `page_translations`'tan çekildiğini doğrula
- Open Graph + Twitter Card + hreflang tag'leri eklenmemişse ekle
- `sections/hero.php` ve diğer template'lerin `$settings`'i doğru okuduğunu test et
