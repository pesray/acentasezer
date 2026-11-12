-- =====================================================================
-- Migration 027: Rezervasyon sihirbazı — yeni adım sırası, yükseltici, ödeme para birimi
-- Tarih: 2026-11-12 · Sahip: Site Yönetimi
--
-- İsteğe bağlı: kod bu SQL olmadan da çalışır (metinlerin varsayılanı kodda).
-- Bu SQL yeni metinleri Admin > Sistem > Çeviriler > sunline grubuna ekler (düzenlenebilsin diye).
-- Var olan kayıtlara dokunmaz (INSERT IGNORE). Şema değişikliği YOK.
--  · Adım sırası: Transfer → Yolcular → Araç → Onay (hw1..hw4, vh_for, too_few_seats)
--  · Yükseltici + çocuk koltuğu ek ücreti (booster, seat_free_note, seat_fee_note, per_leg, per_booking, extras, free)
--  · Araçta nakit ödeme para birimi seçimi (pay_cur, pay_cur_note)
--  · Zorunlu uçuş numarası + (i) bilgi balonu (info, flight_info, flight_info_dep, err_flight)
-- Ücretler ve para birimleri: Admin > İçerik > Anasayfa & Tema > Para Birimi & Ekstralar (SQL gerekmez).
-- Eski how1..how4 anahtarları artık kullanılmıyor (zararsız).
-- =====================================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO `translations` (`language_code`, `trans_group`, `trans_key`, `trans_value`) VALUES
('tr', 'sunline', 'too_few_seats', 'Bu araçta en fazla {n} çocuk koltuğu var'),
('tr', 'sunline', 'vh_for', 'Seçiminiz: {pax} · {trip}'),
('tr', 'sunline', 'hw1', 'Transfer tipini (gidiş-dönüş / tek yön) seçin; uçuş tarihinizi, saatinizi ve otelinizi girin.'),
('tr', 'sunline', 'hw2', 'Yolcu sayısını, iletişim bilgilerinizi ve yolcu isimlerini yazın.'),
('tr', 'sunline', 'hw3', 'Yolcu sayınıza uygun aracı seçin.'),
('tr', 'sunline', 'hw4', 'Rezervasyonu gönderin; WhatsApp üzerinden onaylayalım.'),
('tr', 'sunline', 'booster', 'Yükseltici'),
('tr', 'sunline', 'seat_free_note', '1 adet çocuk koltuğu veya yükseltici ücretsizdir.'),
('tr', 'sunline', 'seat_fee_note', 'Sonrakiler: çocuk koltuğu {cs}, yükseltici {b} ({per}).'),
('tr', 'sunline', 'per_leg', 'transfer başına'),
('tr', 'sunline', 'per_booking', 'rezervasyon başına'),
('tr', 'sunline', 'extras', 'Ekstralar'),
('tr', 'sunline', 'free', 'ücretsiz'),
('tr', 'sunline', 'pay_cur', 'Ödeme para birimi (araçta nakit)'),
('tr', 'sunline', 'pay_cur_note', 'Seçtiğiniz para biriminde, transfer sonunda araçta nakit ödersiniz.'),
('tr', 'sunline', 'info', 'Bilgi'),
('tr', 'sunline', 'flight_info', 'Uçuş numarası biniş kartınızda veya bilet / rezervasyon onayınızda yazar (ör. TK 2412). Uçuşunuzu takip edip rötar olursa sizi yeni iniş saatinde karşılamak için kullanırız.'),
('tr', 'sunline', 'flight_info_dep', 'Uçuş numarası bilet / rezervasyon onayınızda yazar (ör. TK 2413). Otelden alınış saatinizi uçuşunuza göre planlamak için kullanırız.'),
('tr', 'sunline', 'err_flight', 'Lütfen geçerli bir uçuş numarası girin (ör. TK 2412).'),
('en', 'sunline', 'too_few_seats', 'This vehicle has max. {n} child seats'),
('en', 'sunline', 'vh_for', 'Your trip: {pax} · {trip}'),
('en', 'sunline', 'hw1', 'Choose the transfer type (round trip / one way) and enter your flight date, time and hotel.'),
('en', 'sunline', 'hw2', 'Enter the number of passengers, your contact details and passenger names.'),
('en', 'sunline', 'hw3', 'Choose a vehicle that fits your group.'),
('en', 'sunline', 'hw4', 'Send your booking — we confirm it via WhatsApp.'),
('en', 'sunline', 'booster', 'Booster seat'),
('en', 'sunline', 'seat_free_note', '1 child seat or booster seat is free.'),
('en', 'sunline', 'seat_fee_note', 'Additional: child seat {cs}, booster seat {b} ({per}).'),
('en', 'sunline', 'per_leg', 'per transfer'),
('en', 'sunline', 'per_booking', 'per booking'),
('en', 'sunline', 'extras', 'Extras'),
('en', 'sunline', 'free', 'free'),
('en', 'sunline', 'pay_cur', 'Payment currency (cash in the car)'),
('en', 'sunline', 'pay_cur_note', 'You pay in cash in the car at the end of the transfer, in the currency you choose.'),
('en', 'sunline', 'info', 'Info'),
('en', 'sunline', 'flight_info', 'You will find your flight number on your boarding pass or ticket / booking confirmation (e.g. TK 2412). We use it to track your flight and meet you at the new time if it is delayed.'),
('en', 'sunline', 'flight_info_dep', 'You will find it on your ticket / booking confirmation (e.g. TK 2413). We use it to plan your hotel pickup time around your flight.'),
('en', 'sunline', 'err_flight', 'Please enter a valid flight number (e.g. TK 2412).'),
('de', 'sunline', 'too_few_seats', 'Dieses Fahrzeug hat max. {n} Kindersitze'),
('de', 'sunline', 'vh_for', 'Ihre Auswahl: {pax} · {trip}'),
('de', 'sunline', 'hw1', 'Wählen Sie die Transferart (hin & zurück / einfach) und geben Sie Flugdatum, Uhrzeit und Hotel ein.'),
('de', 'sunline', 'hw2', 'Tragen Sie Personenzahl, Kontaktdaten und die Namen der Reisenden ein.'),
('de', 'sunline', 'hw3', 'Wählen Sie ein Fahrzeug, das zu Ihrer Gruppe passt.'),
('de', 'sunline', 'hw4', 'Senden Sie die Buchung — wir bestätigen per WhatsApp.'),
('de', 'sunline', 'booster', 'Sitzerhöhung'),
('de', 'sunline', 'seat_free_note', '1 Kindersitz oder 1 Sitzerhöhung ist gratis.'),
('de', 'sunline', 'seat_fee_note', 'Weitere: Kindersitz {cs}, Sitzerhöhung {b} ({per}).'),
('de', 'sunline', 'per_leg', 'pro Fahrt'),
('de', 'sunline', 'per_booking', 'pro Buchung'),
('de', 'sunline', 'extras', 'Extras'),
('de', 'sunline', 'free', 'gratis'),
('de', 'sunline', 'pay_cur', 'Zahlungswährung (bar im Fahrzeug)'),
('de', 'sunline', 'pay_cur_note', 'Sie zahlen am Ende des Transfers bar im Fahrzeug in der gewählten Währung.'),
('de', 'sunline', 'info', 'Info'),
('de', 'sunline', 'flight_info', 'Die Flugnummer finden Sie auf Ihrer Bordkarte bzw. Ihrem Ticket / Ihrer Buchungsbestätigung (z. B. TK 2412). Damit verfolgen wir Ihren Flug und holen Sie bei Verspätung zur neuen Zeit ab.'),
('de', 'sunline', 'flight_info_dep', 'Sie finden sie auf Ihrem Ticket / Ihrer Buchungsbestätigung (z. B. TK 2413). Damit planen wir die Abholzeit am Hotel passend zu Ihrem Flug.'),
('de', 'sunline', 'err_flight', 'Bitte geben Sie eine gültige Flugnummer ein (z. B. TK 2412).'),
('ru', 'sunline', 'too_few_seats', 'В этом автомобиле не более {n} детск. кресел'),
('ru', 'sunline', 'vh_for', 'Ваш выбор: {pax} · {trip}'),
('ru', 'sunline', 'hw1', 'Выберите тип трансфера (туда и обратно / в одну сторону) и укажите дату и время рейса и отель.'),
('ru', 'sunline', 'hw2', 'Укажите количество пассажиров, контактные данные и имена пассажиров.'),
('ru', 'sunline', 'hw3', 'Выберите автомобиль, подходящий для вашей группы.'),
('ru', 'sunline', 'hw4', 'Отправьте бронирование — мы подтвердим его в WhatsApp.'),
('ru', 'sunline', 'booster', 'Бустер'),
('ru', 'sunline', 'seat_free_note', '1 детское кресло или бустер — бесплатно.'),
('ru', 'sunline', 'seat_fee_note', 'Дополнительно: детское кресло {cs}, бустер {b} ({per}).'),
('ru', 'sunline', 'per_leg', 'за поездку'),
('ru', 'sunline', 'per_booking', 'за бронирование'),
('ru', 'sunline', 'extras', 'Дополнительно'),
('ru', 'sunline', 'free', 'бесплатно'),
('ru', 'sunline', 'pay_cur', 'Валюта оплаты (наличными в машине)'),
('ru', 'sunline', 'pay_cur_note', 'Вы оплачиваете наличными в машине по окончании трансфера в выбранной валюте.'),
('ru', 'sunline', 'info', 'Информация'),
('ru', 'sunline', 'flight_info', 'Номер рейса указан в посадочном талоне или билете / подтверждении бронирования (например, TK 2412). По нему мы отслеживаем рейс и встретим вас в новое время, если рейс задержится.'),
('ru', 'sunline', 'flight_info_dep', 'Он указан в билете / подтверждении бронирования (например, TK 2413). По нему мы планируем время выезда из отеля.'),
('ru', 'sunline', 'err_flight', 'Введите корректный номер рейса (например, TK 2412).');

-- Geri alma:
-- DELETE FROM `translations` WHERE `trans_group` = 'sunline' AND `trans_key` IN ('too_few_seats','vh_for','hw1','hw2','hw3','hw4','booster','seat_free_note','seat_fee_note','per_leg','per_booking','extras','free','pay_cur','pay_cur_note','info','flight_info','flight_info_dep','err_flight');
