-- =====================================================================
-- Migration 029: SEO içerik düzeltmeleri (yalnız veri; şema değişikliği YOK)
-- Tarih: 2026-11-17 · Sahip: Site Yönetimi
--
-- Tekrar çalıştırmaya dayanıklı: her satır yalnız "hâlâ eski/boş" ise değişir;
-- admin'den elle değiştirilmiş içeriğe dokunmaz.
--  1) Hakkımızda (Sayfalar > id 2): boş sayfaya TR içerik + EN/DE/RU çeviri
--     (yeni adresler: /en/about-us, /de/ueber-uns, /ru/o-nas — sitemap/hreflang otomatik)
--  2) Kundu konumu: Muratpaşa → Aksu
--  3) Anasayfa meta açıklaması (TR/EN) 160 karakter altına (Google'da kesilmesin)
--  4) Turlar: yer tutucu metinler (açıklama de / turlar), Almanca adres "turlar-de" → "touren",
--     RU kaydının dil kodu "RU" → "ru" ve boşluklu adresi; fiyatı 0 ve açıklaması boş
--     deneme turu "Land Of Legends" yayından kaldırılır (taslak). Geri açmak: Admin > Turlar > Durum: Yayında.
-- =====================================================================

SET NAMES utf8mb4;

-- 1) Hakkımızda — TR (varsayılan dil `pages` tablosunda; admin de buradan okur/yazar)
UPDATE `pages` SET
  `content` = '<h2>SunLine VIP Transfer kimdir?</h2>
<p>SunLine VIP Transfer, Antalya Havalimanı (AYT) ile Side, Belek, Alanya, Kemer, Lara ve Kundu başta olmak üzere bölgedeki otel ve tatil köyleri arasında özel VIP transfer hizmeti veren bir Antalya firmasıdır. Merkezimiz Side''de (Manavgat) bulunur; resmi unvanımız Manavgat Mes Turizm Ticaret Limited Şirketi''dir.</p>
<p>Amacımız basit: uçaktan indiğiniz andan otelinizin kapısına kadar yolculuğunuzu sakin, güvenli ve şeffaf hale getirmek. Her transfer özeldir; aracı başka yolcularla paylaşmazsınız.</p>
<h2>Nasıl çalışıyoruz?</h2>
<ul>
<li><strong>Araç başı sabit fiyat:</strong> Fiyatlarımız kişi başı değil, araç başıdır. Rezervasyonda gördüğünüz fiyat yolculuk sonunda değişmez.</li>
<li><strong>Ücretsiz uçuş takibi:</strong> Uçuşunuz rötar yapsa bile şoförünüz sizi bekler; terminal çıkışında QR biletiniz veya PNR numaranızla karşılanırsınız.</li>
<li><strong>Ödeme araçta:</strong> Ön ödeme yoktur. Ödemeyi transfer sonunda araçta nakit olarak (EUR, USD, GBP veya TRY) yaparsınız.</li>
<li><strong>Ücretsiz iptal:</strong> Planınız değişirse rezervasyonunuzu ücretsiz iptal edebilir ya da değiştirebilirsiniz. Ayrıntılar: <a href="/tr/ucretsiz-iptal-ve-degisiklik">Ücretsiz iptal ve değişiklik</a>.</li>
<li><strong>Aileler için:</strong> İlk çocuk koltuğu ücretsizdir.</li>
<li><strong>7/24 hizmet:</strong> Gece ya da gündüz her uçuşa transfer planlarız; WhatsApp ve telefonla 7/24 ulaşabilirsiniz.</li>
</ul>
<h2>Araçlarımız</h2>
<p>Küçük aileler için konforlu Mercedes Vito VIP araçlardan kalabalık gruplar için Sprinter ve Crafter''a kadar farklı kapasitelerde, klimalı araçlarla hizmet veriyoruz. Yolcu ve bagaj sayınıza uygun aracı rezervasyon sırasında kendiniz seçersiniz.</p>
<h2>Hizmet verdiğimiz bölgeler</h2>
<p>Antalya Havalimanı''ndan Side, Kumköy, Çolaklı, Evrenseki, Sorgun, Belek, Kadriye, Boğazkent, Kızılağaç, Kızılot, Okurcalar, Avsallar, Türkler, Alanya, Kemer, Tekirova, Lara, Kundu ve daha birçok noktaya transfer yapıyoruz. Tüm bölgeler ve güncel fiyatlar için <a href="/tr/transferler">Transferler</a> sayfasına bakabilirsiniz.</p>
<h2>Bize ulaşın</h2>
<p>Türkçe, İngilizce, Almanca ve Rusça hizmet veriyoruz. Sorularınız için <a href="/tr/iletisim">İletişim</a> sayfasından bize WhatsApp, telefon veya e-posta ile ulaşabilirsiniz.</p>',
  `excerpt` = 'Antalya Havalimanı''ndan otelinize özel VIP transfer: araç başı sabit fiyat, ücretsiz uçuş takibi, terminalde karşılama ve araçta ödeme.',
  `meta_title` = 'Hakkımızda | SunLine VIP Transfer Antalya',
  `meta_description` = 'SunLine VIP Transfer: Antalya Havalimanı''ndan Side, Belek, Alanya ve Kemer''e özel VIP transfer. Araç başı sabit fiyat, ücretsiz iptal, 7/24 hizmet.'
WHERE `id` = 2 AND `slug` = 'hakkimizda' AND (`content` IS NULL OR `content` = '');

-- 1b) Hakkımızda — EN / DE / RU (yalnız çeviri yoksa eklenir)
INSERT IGNORE INTO `page_translations` (`page_id`, `language_code`, `title`, `slug`, `content`, `excerpt`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(2, 'en', 'About Us', 'about-us',
'<h2>Who is SunLine VIP Transfer?</h2>
<p>SunLine VIP Transfer is an Antalya-based company providing private VIP transfers between Antalya Airport (AYT) and hotels and resorts in the region, including Side, Belek, Alanya, Kemer, Lara and Kundu. Our office is in Side (Manavgat); our registered company name is Manavgat Mes Turizm Ticaret Limited Şirketi.</p>
<p>Our goal is simple: to make your journey from the moment you land to the door of your hotel calm, safe and transparent. Every transfer is private; you never share the vehicle with other passengers.</p>
<h2>How we work</h2>
<ul>
<li><strong>Fixed price per vehicle:</strong> our prices are per vehicle, not per person. The price you see when booking does not change at the end of the ride.</li>
<li><strong>Free flight tracking:</strong> even if your flight is delayed, your driver waits for you; you are met at the terminal exit with your QR ticket or PNR number.</li>
<li><strong>Pay in the car:</strong> no prepayment. You pay in cash in the vehicle at the end of the transfer (EUR, USD, GBP or TRY).</li>
<li><strong>Free cancellation:</strong> if your plans change, you can cancel or change your booking free of charge. Details: <a href="/en/free-cancellation">Free cancellation</a>.</li>
<li><strong>For families:</strong> the first child seat is free.</li>
<li><strong>24/7 service:</strong> we arrange transfers for every flight, day or night, and you can reach us 24/7 via WhatsApp and phone.</li>
</ul>
<h2>Our vehicles</h2>
<p>From comfortable Mercedes Vito VIP vans for families to Sprinter and Crafter minibuses for larger groups, all our vehicles are air-conditioned. You choose the vehicle that fits your passengers and luggage when you book.</p>
<h2>Where we operate</h2>
<p>We drive from Antalya Airport to Side, Kumköy, Çolaklı, Evrenseki, Sorgun, Belek, Kadriye, Boğazkent, Kızılağaç, Kızılot, Okurcalar, Avsallar, Türkler, Alanya, Kemer, Tekirova, Lara, Kundu and many more destinations. See all regions and current prices on our <a href="/en/transfers">Transfers</a> page.</p>
<h2>Contact us</h2>
<p>We speak Turkish, English, German and Russian. For any question, reach us via WhatsApp, phone or e-mail on our <a href="/en/contact">Contact</a> page.</p>',
'Private VIP transfer from Antalya Airport to your hotel: fixed price per vehicle, free flight tracking, meet & greet at the terminal and payment in the car.',
'About Us | SunLine VIP Transfer Antalya',
'SunLine VIP Transfer: private VIP transfers from Antalya Airport to Side, Belek, Alanya and Kemer. Fixed price per vehicle, free cancellation, 24/7.',
NULL),
(2, 'de', 'Über uns', 'ueber-uns',
'<h2>Wer ist SunLine VIP Transfer?</h2>
<p>SunLine VIP Transfer ist ein Unternehmen aus Antalya, das private VIP-Transfers zwischen dem Flughafen Antalya (AYT) und Hotels und Ferienanlagen der Region anbietet, unter anderem in Side, Belek, Alanya, Kemer, Lara und Kundu. Unser Büro befindet sich in Side (Manavgat); unser eingetragener Firmenname lautet Manavgat Mes Turizm Ticaret Limited Şirketi.</p>
<p>Unser Ziel ist einfach: Ihre Fahrt von der Landung bis zur Tür Ihres Hotels ruhig, sicher und transparent zu machen. Jeder Transfer ist privat; Sie teilen das Fahrzeug nie mit anderen Fahrgästen.</p>
<h2>So arbeiten wir</h2>
<ul>
<li><strong>Festpreis pro Fahrzeug:</strong> Unsere Preise gelten pro Fahrzeug, nicht pro Person. Der Preis, den Sie bei der Buchung sehen, ändert sich am Ende der Fahrt nicht.</li>
<li><strong>Kostenlose Flugüberwachung:</strong> Auch bei Verspätung wartet Ihr Fahrer auf Sie; Sie werden am Terminalausgang mit Ihrem QR-Ticket oder Ihrer PNR-Nummer empfangen.</li>
<li><strong>Bezahlung im Fahrzeug:</strong> Keine Vorauszahlung. Sie zahlen am Ende des Transfers bar im Fahrzeug (EUR, USD, GBP oder TRY).</li>
<li><strong>Kostenlose Stornierung:</strong> Wenn sich Ihre Pläne ändern, können Sie Ihre Buchung kostenlos stornieren oder ändern. Details: <a href="/de/kostenlose-stornierung">Kostenlose Stornierung</a>.</li>
<li><strong>Für Familien:</strong> Der erste Kindersitz ist kostenlos.</li>
<li><strong>Service rund um die Uhr:</strong> Wir planen Transfers für jeden Flug, ob Tag oder Nacht, und sind per WhatsApp und Telefon 24/7 erreichbar.</li>
</ul>
<h2>Unsere Fahrzeuge</h2>
<p>Vom komfortablen Mercedes Vito VIP für Familien bis zu Sprinter und Crafter für größere Gruppen – alle unsere Fahrzeuge sind klimatisiert. Das passende Fahrzeug für Ihre Personen- und Gepäckzahl wählen Sie bei der Buchung selbst.</p>
<h2>Unsere Regionen</h2>
<p>Wir fahren vom Flughafen Antalya nach Side, Kumköy, Çolaklı, Evrenseki, Sorgun, Belek, Kadriye, Boğazkent, Kızılağaç, Kızılot, Okurcalar, Avsallar, Türkler, Alanya, Kemer, Tekirova, Lara, Kundu und zu vielen weiteren Zielen. Alle Regionen und aktuellen Preise finden Sie auf unserer Seite <a href="/de/transfers">Transfers</a>.</p>
<h2>Kontakt</h2>
<p>Wir sprechen Türkisch, Englisch, Deutsch und Russisch. Bei Fragen erreichen Sie uns per WhatsApp, Telefon oder E-Mail über unsere <a href="/de/kontakt">Kontaktseite</a>.</p>',
'Privater VIP-Transfer vom Flughafen Antalya zu Ihrem Hotel: Festpreis pro Fahrzeug, kostenlose Flugüberwachung, Empfang am Terminal und Zahlung im Fahrzeug.',
'Über uns | SunLine VIP Transfer Antalya',
'SunLine VIP Transfer: privater VIP-Transfer vom Flughafen Antalya nach Side, Belek, Alanya und Kemer. Festpreis pro Fahrzeug, kostenlose Stornierung.',
NULL),
(2, 'ru', 'О нас', 'o-nas',
'<h2>Кто такие SunLine VIP Transfer?</h2>
<p>SunLine VIP Transfer — компания из Анталии, которая выполняет индивидуальные VIP трансферы между аэропортом Анталии (AYT) и отелями региона, в том числе в Сиде, Белеке, Аланье, Кемере, Ларе и Кунду. Наш офис находится в Сиде (Манавгат); официальное название компании — Manavgat Mes Turizm Ticaret Limited Şirketi.</p>
<p>Наша цель проста: сделать вашу дорогу от посадки самолёта до дверей отеля спокойной, безопасной и прозрачной. Каждый трансфер индивидуальный — вы не делите автомобиль с другими пассажирами.</p>
<h2>Как мы работаем</h2>
<ul>
<li><strong>Фиксированная цена за автомобиль:</strong> цена указана за автомобиль, а не за человека. Цена, которую вы видите при бронировании, не меняется в конце поездки.</li>
<li><strong>Бесплатное отслеживание рейса:</strong> даже если рейс задерживается, водитель вас дождётся; встреча у выхода из терминала по QR-билету или номеру PNR.</li>
<li><strong>Оплата в машине:</strong> без предоплаты. Вы оплачиваете наличными в автомобиле в конце трансфера (EUR, USD, GBP или TRY).</li>
<li><strong>Бесплатная отмена:</strong> если планы изменились, бронирование можно бесплатно отменить или изменить. Подробнее: <a href="/ru/besplatnaya-otmena">Бесплатная отмена</a>.</li>
<li><strong>Для семей:</strong> первое детское кресло бесплатно.</li>
<li><strong>Круглосуточно:</strong> мы организуем трансфер к любому рейсу, днём и ночью; с нами можно связаться 24/7 по WhatsApp и телефону.</li>
</ul>
<h2>Наши автомобили</h2>
<p>От комфортабельных Mercedes Vito VIP для семей до Sprinter и Crafter для больших групп — все наши автомобили с кондиционером. Подходящий автомобиль по количеству пассажиров и багажа вы выбираете сами при бронировании.</p>
<h2>Где мы работаем</h2>
<p>Мы возим из аэропорта Анталии в Сиде, Кумкёй, Чолаклы, Эвренсеки, Соргун, Белек, Кадрие, Богазкент, Кызылагач, Кызылот, Окурджалар, Авсаллар, Тюрклер, Аланью, Кемер, Текирову, Лару, Кунду и во многие другие места. Все направления и актуальные цены — на странице <a href="/ru/transfery">Трансферы</a>.</p>
<h2>Свяжитесь с нами</h2>
<p>Мы говорим на турецком, английском, немецком и русском языках. По любым вопросам пишите нам в WhatsApp, звоните или отправляйте e-mail — все контакты на странице <a href="/ru/kontakty">Контакты</a>.</p>',
'Индивидуальный VIP трансфер из аэропорта Анталии в ваш отель: цена за автомобиль, бесплатное отслеживание рейса, встреча в терминале и оплата в машине.',
'О нас | SunLine VIP Transfer Анталия',
'SunLine VIP Transfer: индивидуальный VIP трансфер из аэропорта Анталии в Сиде, Белек, Аланью и Кемер. Цена за автомобиль, бесплатная отмена.',
NULL);

-- 2) Kundu konumu (Kundu Aksu ilçesine bağlı)
UPDATE `destinations` SET `location` = 'Antalya / Aksu / Kundu'
WHERE `id` = 13 AND `location` = 'Antalya / Muratpaşa / Kundu';

-- 3) Anasayfa meta açıklaması — 160 karakter altı
UPDATE `translations` SET `trans_value` = 'Antalya Havalimanı''ndan otelinize özel VIP transfer. Araç başı sabit fiyat, QR/PNR ile karşılama, ücretsiz iptal, araçta ödeme. Side, Belek, Alanya, Kemer.'
WHERE `trans_group` = 'sunline' AND `trans_key` = 'seo_home_d' AND `language_code` = 'tr'
  AND `trans_value` = 'Antalya Havalimanı''ndan otelinize özel VIP transfer. Araç başı sabit fiyat, QR/PNR ile karşılama, ücretsiz iptal ve araçta ödeme. Side, Belek, Alanya, Kemer transfer.';
UPDATE `translations` SET `trans_value` = 'Private VIP transfer from Antalya Airport (AYT) to your hotel. Fixed price per vehicle, meet & greet, free cancellation, pay in the car. Side, Belek.'
WHERE `trans_group` = 'sunline' AND `trans_key` = 'seo_home_d' AND `language_code` = 'en'
  AND `trans_value` = 'Private VIP transfers from Antalya Airport (AYT) to your hotel. Fixed price per vehicle, QR/PNR meet & greet, free cancellation, pay in the car. Side, Belek, Alanya, Kemer.';

-- 4) Turlar listesi: yer tutucu metinler + Almanca adres
UPDATE `page_setting_translations` pst JOIN `page_settings` ps ON ps.id = pst.page_setting_id AND ps.page_key = 'tours'
SET pst.`title` = 'Touren', pst.`slug` = 'touren', pst.`subtitle` = 'Touren und Ausflüge in Antalya mit privatem VIP-Transfer von SunLine.'
WHERE pst.`language_code` = 'de' AND pst.`slug` = 'turlar-de';
UPDATE `page_setting_translations` pst JOIN `page_settings` ps ON ps.id = pst.page_setting_id AND ps.page_key = 'tours'
SET pst.`subtitle` = 'SunLine VIP Transfer ile Antalya''da tur ve gezilere özel VIP transfer.'
WHERE pst.`language_code` = 'tr' AND pst.`subtitle` = 'turlar';
UPDATE `page_setting_translations` pst JOIN `page_settings` ps ON ps.id = pst.page_setting_id AND ps.page_key = 'tours'
SET pst.`title` = 'Tours', pst.`subtitle` = 'Tours and excursions in Antalya with private VIP transfer by SunLine.'
WHERE pst.`language_code` = 'en' AND pst.`title` = 'tours' COLLATE utf8mb4_bin;
INSERT IGNORE INTO `page_setting_translations` (`page_setting_id`, `language_code`, `title`, `slug`, `subtitle`)
SELECT ps.id, 'ru', 'Туры', 'tury', 'Туры и экскурсии в Анталии с индивидуальным VIP трансфером SunLine.'
FROM `page_settings` ps WHERE ps.page_key = 'tours';

-- 4b) Land Of Legends: RU kaydı (dil kodu büyük harf + boşluklu adres) ve yayından kaldırma
UPDATE `tour_translations` SET `language_code` = 'ru', `slug` = 'land-of-legends'
WHERE `tour_id` = 9 AND `language_code` = 'RU' COLLATE utf8mb4_bin;
UPDATE `tours` SET `status` = 'draft'
WHERE `id` = 9 AND `status` = 'published' AND (`description` IS NULL OR `description` = '') AND `price` = 0;
