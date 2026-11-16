# SEO Oturum Özeti — 15–16 Kasım 2026

## 1. Başlangıç analizi
- Sitenin herkese açık sayfaları tarandı: 5 dil (DE/TR/EN/RU/AR), hreflang, canonical, schema (TaxiService, FAQ, Breadcrumb), hızlı açılış — teknik altyapı iyi.
- Ana sorunlar: 30 bölge sayfasının metni şablon/kopya içerik; blog boş (kaldırılacak); Kızılağaç DE slug hatası; Almanca tur URL'si Türkçe; Hakkımızda yalnız TR'de.

## 2. Arama motoru kurulumları
- **Google Search Console**: Mülkler doğrulanmamıştı. DNS (Alastyr — panel girişinde SSL hatası) yerine panel > Site Ayarları > SEO Ayarları'ndaki alanla HTML etiketi doğrulaması yapıldı. Site haritası gönderildi.
  - İlk veri (13 Kasım): 164 gösterim, 0 tıklama, ort. konum 16,5. Öne çıkan: Kundu ve Side (DE/EN).
- **Bing Webmaster**: GSC'den içe aktarıldı, sitemap geldi.
- **Yandex Webmaster**: Meta tag ile doğrulandı. Sitemap aracı Yandex tarafında geçici kapalı.
- **IndexNow**: Anahtar üretildi (16c2f348b75c36330328a00258c2de6b), kurulum notu yazılımcıya iletildi.
- Natro'da tespit: alan adı 5 Ocak 2027'de bitiyor, otomatik yenileme ve transfer koruması kapalı. DNS Alastyr'da.

## 3. Panel düzeltmeleri
- Araç hizmet adları DE/RU/AR'ye çevrildi ("de klima", "de su" taslakları düzeltildi).
- "Hizmetleri Kaydet" hizmetleri yeni ID'lerle yeniden oluşturduğu için araç bağlantıları koptu; 5 aracın hizmetleri yeniden işaretlendi (Minivan 1-4 ve 5-6: Gazete/Dergi hariç). Panel hatası yazılımcıya bildirilecek.
- Açıklama alanına `<p>` denemesi yapıldı; site HTML'i kaçışlı bastığı için geri alındı → paragraflar için yazılımcı `nl2br` yapmalı.

## 4. İçerik
- 30 bölgenin tamamına TR/EN/DE/RU özgün "Kısa Açıklama" metni yazıldı (120 sayfa). Metin sayfada hero altında (kısaltılmış) ve "Hakkında" bölümünde tam görünüyor.
  - İlk 6 öncelikli bölge (Kundu, Side, Belek, Lara, Kemer, Alanya): mesafe/süre ve hizmet detaylı ilk tarz — kullanıcı "böyle kalsın" dedi.
  - Kalan 24 bölge: kullanıcı isteğiyle km/süre yok, hizmetler tek kısa cümle, ağırlık bölgenin kendisinde.
  - Düzeltmeler: Kızılağaç konumu (Side'nin doğusu), Çenger (Kızılot'un hemen ardı), "VIP bus" → "VIP Sprinter", RU "Лунного парка" → парк «Лунный свет».
- 30 bölgenin Rusça başlıkları Kirilceye çevrildi (ör. "Трансфер Белек"); site başlık, H2 ve SSS'leri buna göre otomatik düzgün üretiyor.
- Eksik dil kayıtları oluşturuldu (Kadriye DE; Kızılot, Okurcalar EN; Kargıcak, Konyaaltı, Fethiye EN/DE/RU). Slug/adresler değişmedi.
- Son kontrol: 120 sayfa 200 OK, şablon metin kalmadı, RU başlıklar Kiril.

## 5. Yazılımcıya verilen komutlar (VS Code + Claude)
1. Açıklama paragrafları (lead-in'de \n\n → ayrı <p>, escape korunarak)
2. robots.txt'ye `Sitemap:` satırı
3. IndexNow kurulumu (anahtar dosyası + indexnow_submit + admin kaydında tetikleme)
4. "Hizmetleri Yönet" ID'leri koruyarak güncelleme

## 6. Kullanıcının yapacakları
- Natro'dan alan adını yenilemek / otomatik yenileme ve transfer korumasını açmak
- Kundu konumunu "Antalya / Aksu / Kundu" yapmak
- Google yorumlarını siteye eklemek, müşterilerden yorum istemek

## 7. Sonraki kontroller
- Yazılımcı her adımı bitirince canlı sitede doğrulama
- Yandex sitemap gönderimi (araç düzelince)
- 3–4 hafta sonra GSC ile sıralama karşılaştırması

## Oluşturulan dosyalar
indexnow-kurulum.md, bolge-metinleri-kundu-side.md, bolge-metinleri-belek-lara-kemer-alanya.md, bolge-metinleri-parti-1.md, bolge-metinleri-parti-2.md, bolge-metinleri-parti-3-4.md
