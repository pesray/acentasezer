# Site Yönetimi — Sistem İncelemesi & Plan

> Frontend (sunlineviptransfer.com) içerik yönetimi için yapılacak işlerin yol haritası.

---

## Mevcut Durum Analizi

### ✅ Çalışan Yapılar

**Veritabanı (016_create_languages_table.sql):**
- `languages` tablosu: code, name, native_name, flag, is_default, is_active, is_rtl, sort_order
- `*_translations` tabloları: page, destination, tour, section, menu_item, slider, testimonial, feature, blog_post, faq
- `translations` tablosu: UI metinleri (header, footer, general gruplarında)

**Frontend Çoklu Dil Sistemi:**
- `getCurrentLang()` — URL prefix → cookie → Accept-Language → default
- `langUrl($path, $lang)` — dil prefix'li URL üretimi
- `__($key, $group)` — UI çevirisi
- `*_translations` JOIN'leri tüm helper fonksiyonlarda mevcut (`getPageSections`, `getFeaturedDestinations`, vb.)
- `COALESCE(translation, fallback)` ile dile özgü içerik veya base fallback

**Section Sistemi:**
- `pages.is_homepage = 1` → ana sayfa
- `sections` tablosu: page_id, section_key, section_type, title, subtitle, content, settings (JSON), background_image, background_video, is_active
- 6 section tipi: hero, why_us, featured_destinations, featured_tours, testimonials, cta
- Her section için `includes/sections/{type}.php` template dosyası
- `getPageSections()` çevirileri JOIN ediyor

### ❌ Sorunlar

1. **`admin/homepage.php` çoklu dil desteklemiyor:**
   - Form sadece base `sections` tablosuna yazıyor
   - `section_translations` tablosuna hiç yazmıyor
   - Sayfa üstündeki dil seçici cosmetik — hiçbir şey yapmıyor
   - SEO meta'lar `pages` tablosuna yazıyor (page_translations'a değil)

2. **Sliders hero için kullanılmıyor:**
   - `getHeroData()` `sliders` tablosundan çekiyor ama hero section'da `sections.background_image/video` kullanılıyor
   - İkisi paralel çalışıyor, kafa karışıklığı

3. **Mevcut admin dilleri pasif:**
   - Hardcoded "TR / EN" dropdown var, gerçek `languages` tablosundan çekmiyor
   - Yeni dil eklenince otomatik görünmez

4. **SEO eksikleri:**
   - Open Graph (og:image, og:title, og:description) yok
   - Schema.org structured data yok
   - Canonical URL kontrolü yok
   - hreflang tag'ler `getAlternateLanguageUrl()` mevcut ama header'larda kullanılıyor mu kontrol edilecek
   - Sitemap.xml yok

5. **Tour-template entegrasyonu:**
   - tour-template/index.html'deki tüm section'lar PHP template'lerine taşınmış ama bazı detaylar eksik olabilir
   - Bazı section'lar (gallery, features grid, contact form) eksik olabilir

---

## Yapılacaklar — Sıralı Plan

### Faz 1: Anasayfa Yönetimi Çoklu Dil Düzeltmesi (ÖNCELİKLİ)

#### 1.1 Admin homepage.php yeniden yapılandırma
- Üstte gerçek dil seçici (sekme/tab tarzı) — `languages` tablosundan dinamik
- Her dil için ayrı form alanları (title, subtitle, content)
- AJAX'sız basit yaklaşım: Tüm dillerin formları aynı sayfada tab'larla
- Submit'te:
  - Base table (`sections`): default dil verileri (settings, is_active, background_image, background_video gibi dil-bağımsız)
  - `section_translations`: her dil için title, subtitle, content, settings (dil-bağımlı)
- SEO bölümü: `page_translations` tablosuna her dil için ayrı kayıt

#### 1.2 Section translations migration kontrolü
- `section_translations.settings` JSON alanı var mı kontrol et — varsa kullan
- Yoksa migration ekle (button text'leri, label'lar için)

#### 1.3 Frontend doğrulama
- Dil değiştirince anasayfa içeriği gerçekten değişiyor mu?
- Boş çeviri varsa default fallback çalışıyor mu?

### Faz 2: SEO Sistemini Güçlendir

#### 2.1 Header SEO meta tag enjeksiyonu
- Title, meta description, meta keywords (sayfa bazlı)
- Open Graph: og:title, og:description, og:image, og:url, og:type
- Twitter Card: twitter:card, twitter:title, twitter:description, twitter:image
- Canonical URL
- hreflang tag'ler (her aktif dil için)

#### 2.2 Schema.org structured data
- Organization (footer veya header'da)
- BreadcrumbList (her detail sayfada)
- TourPackage (tur detay)
- Place / TouristDestination (destinasyon detay)
- LocalBusiness / TravelAgency

#### 2.3 Sitemap.xml otomatik üretici
- `/sitemap.xml` endpoint'i
- Tüm aktif dillerdeki sayfalar, turlar, destinasyonlar, blog yazıları
- `<xhtml:link rel="alternate" hreflang>` her URL için
- robots.txt güncellemesi

#### 2.4 Admin Settings'e SEO sekmesi
- Default OG image, Twitter handle, FB app ID, Google Analytics ID, Google Tag Manager
- Schema.org organization bilgileri

### Faz 3: Diğer İçerik Yönetim Sayfaları (Çoklu Dil)

Sıraya göre:
1. Sliders (slider_translations — title, subtitle, button_text, button_url)
2. Menus & Menu Items (menu_item_translations)
3. Pages (page_translations — title, slug, content, SEO)
4. Sections (general edit page — page bağımsız sections için)
5. Destinations (destination_translations)
6. Tours (tour_translations)
7. Testimonials, Features, Blog Posts, FAQs, Gallery

### Faz 4: Frontend Site Polish

- tour-template/index.html ile karşılaştırma — eksik bölümler
- Tüm sayfaların (about, contact, privacy, terms, blog, gallery) PHP template'lerinin admin'den yönetilebilirliği
- Form çalışmaları (booking, contact, newsletter)
- Performans (image lazy load, asset versioning)

### Faz 5: Dashboard & Raporlama

- Gerçek dashboard widget'ları (stat cards, mini charts)
- Rezervasyon raporları (gelir, partner bazlı, dönemsel)
- Trafik raporları (Google Analytics entegrasyonu)

---

## Kullanıcı Senaryoları (Test Checklist)

### Dil ekleme/çıkarma
- [ ] Admin → Diller → Yeni dil ekle (örn: Almanca de aktifleştir)
- [ ] Anasayfa yönetimine git → Almanca tab görünüyor mu?
- [ ] Tüm content yönetim sayfalarında otomatik görünüyor mu?
- [ ] Dil pasifleştirilince frontend'de gizleniyor mu?

### Anasayfa düzenleme
- [ ] TR dilinde başlık değiştir, kaydet
- [ ] Frontend TR'de değişti mi?
- [ ] EN dilinde başlık başka değer yap
- [ ] Frontend EN'de farklı mı?
- [ ] Bir dilde boş bırakılan alan default'a düşüyor mu?
- [ ] Section gizle/göster checkbox'ı çalışıyor mu?

### SEO
- [ ] Sayfa başlığı (browser tab) dile göre değişiyor mu?
- [ ] View source'ta hreflang tag'leri var mı?
- [ ] Open Graph tag'leri doğru mu (Facebook debugger)?
- [ ] Sitemap.xml açılıyor mu, tüm dilleri içeriyor mu?

---

## Teknik Notlar

- **PHP 7.4 uyumlu** kod yazılacak (`match()` yok, `str_contains` yok, vb.)
- Migration dosyaları sıralı (021, 022 zaten kullanıldı, sonraki 024+ olabilir)
- Tüm güncellemeler `admin/markdown/proje_kurallari.md`'ye işlenecek
- UI tasarımı admin için Inter font + dark mode uyumlu (mevcut sistem)
- Form validasyonu: client + server-side
- Tüm AJAX işlemlerinde CSRF token kullanılacak
- Slug üretimi: Türkçe karakter normalizasyonu (mevcut `admin.js`'de var)

---

## Sonraki Adım

**Şuanda:** Sistem tam incelendi, plan hazır. Anasayfa yönetimi (homepage.php) çoklu dil desteğine kavuşturulacak.

**Bekleniyor:** Kullanıcıdan onay → Faz 1.1 başlat (admin/homepage.php yeniden yazımı)
