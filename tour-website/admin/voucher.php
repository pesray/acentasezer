<?php
/**
 * Transfer Voucher - A4 Yazdırılabilir
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$outId = (int)($_GET['out_id'] ?? $_GET['booking_id'] ?? 0);
$retId = (int)($_GET['ret_id'] ?? 0);
$lang  = $_GET['lang'] ?? 'en';
if (!in_array($lang, ['tr', 'en', 'de', 'ru'])) $lang = 'en';

if (!$outId && !$retId) die('Geçersiz rezervasyon');

// Outbound booking
$out = null;
if ($outId) {
    $s = $db->prepare("
        SELECT b.*, CONCAT(v.brand,' ',v.model) AS vehicle_name, v.capacity,
               d.title AS destination_title
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN destinations d ON b.destination_id = d.id
        WHERE b.id = ?
    ");
    $s->execute([$outId]);
    $out = $s->fetch();
}

// Return booking
$ret = null;
if ($retId) {
    $s = $db->prepare("
        SELECT b.*, CONCAT(v.brand,' ',v.model) AS vehicle_name, v.capacity,
               d.title AS destination_title
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN destinations d ON b.destination_id = d.id
        WHERE b.id = ?
    ");
    $s->execute([$retId]);
    $ret = $s->fetch();
}

// Sadece ret varsa (ret'ten açıldı)
$base = $out ?: $ret;
if (!$base) die('Rezervasyon bulunamadı');

// Yolcular (önce outbound, yoksa return'dan)
$paxBookingId = $out ? $out['id'] : $ret['id'];
$paxStmt = $db->prepare("SELECT passenger_type, full_name FROM booking_passengers WHERE booking_id = ? ORDER BY passenger_type DESC, sort_order ASC");
$paxStmt->execute([$paxBookingId]);
$passengers = $paxStmt->fetchAll();

// Şirket bilgileri
$siteName  = getSetting('site_name', 'VIP Transfer');
$siteLogo  = getSetting('site_logo', '');
$sitePhone = getSetting('contact_phone', '');
$siteEmail = getSetting('contact_email', '');
$siteWeb   = getSetting('site_url', '');

// WhatsApp numarası: başındaki + ve boşlukları temizle
$waNumber = preg_replace('/[^0-9]/', '', $sitePhone);

// Dil etiketleri
$labels = [
    'tr' => [
        'title'          => 'TRANSFER VOUCHER',
        'booking_no'     => 'Rezervasyon No',
        'date'           => 'Tarih',
        'customer'       => 'Müşteri Bilgileri',
        'name'           => 'Ad Soyad',
        'phone'          => 'Telefon',
        'email'          => 'E-posta',
        'passengers'     => 'Yolcu Listesi',
        'adults'         => 'Yetişkin',
        'children'       => 'Çocuk',
        'arrival'        => 'GELİŞ TRANSFERİ',
        'departure'      => 'DÖNÜŞ TRANSFERİ',
        'flight_date'    => 'Uçuş Tarihi',
        'flight_time'    => 'İniş Saati',
        'flight_no'      => 'Uçuş No',
        'hotel'          => 'Otel / Adres',
        'pickup_time'    => 'Alış Saati',
        'vehicle'        => 'Araç',
        'route'          => 'Güzergah',
        'pax'            => 'Kişi Sayısı',
        'notes'          => 'Notlar',
        'child_seat'     => 'Çocuk Koltuğu',
        'departure_flight_time' => 'Kalkış Saati',
        'pickup_time_note'      => '(Otelden alış saatini değiştirmek için bizimle iletişime geçebilirsiniz.)',
        'price'          => 'Transfer Ücreti',
        'footer_thanks'  => 'Bizi tercih ettiğiniz için teşekkür ederiz.',
    ],
    'en' => [
        'title'          => 'TRANSFER VOUCHER',
        'booking_no'     => 'Booking No',
        'date'           => 'Date',
        'customer'       => 'Customer Information',
        'name'           => 'Full Name',
        'phone'          => 'Phone',
        'email'          => 'E-mail',
        'passengers'     => 'Passenger List',
        'adults'         => 'Adults',
        'children'       => 'Children',
        'arrival'        => 'ARRIVAL TRANSFER',
        'departure'      => 'DEPARTURE TRANSFER',
        'flight_date'    => 'Flight Date',
        'flight_time'    => 'Arrival Time',
        'flight_no'      => 'Flight No',
        'hotel'          => 'Hotel / Address',
        'pickup_time'    => 'Pickup Time',
        'vehicle'        => 'Vehicle',
        'route'          => 'Route',
        'pax'            => 'Passengers',
        'notes'          => 'Notes',
        'child_seat'     => 'Child Seat',
        'departure_flight_time' => 'Departure Time',
        'pickup_time_note'      => '(To change your hotel pickup time, please contact us.)',
        'price'          => 'Transfer Fee',
        'footer_thanks'  => 'Thank you for choosing us.',
    ],
    'de' => [
        'title'          => 'TRANSFER VOUCHER',
        'booking_no'     => 'Buchungsnummer',
        'date'           => 'Datum',
        'customer'       => 'Kundeninformationen',
        'name'           => 'Name',
        'phone'          => 'Telefon',
        'email'          => 'E-Mail',
        'passengers'     => 'Passagierliste',
        'adults'         => 'Erwachsene',
        'children'       => 'Kinder',
        'arrival'        => 'ANKUNFTSTRANSFER',
        'departure'      => 'ABREISTRANSFER',
        'flight_date'    => 'Flugdatum',
        'flight_time'    => 'Ankunftszeit',
        'flight_no'      => 'Flugnummer',
        'hotel'          => 'Hotel / Adresse',
        'pickup_time'    => 'Abholzeit',
        'vehicle'        => 'Fahrzeug',
        'route'          => 'Strecke',
        'pax'            => 'Passagiere',
        'notes'          => 'Anmerkungen',
        'child_seat'     => 'Kindersitz',
        'departure_flight_time' => 'Abflugzeit',
        'pickup_time_note'      => '(Um die Abholzeit vom Hotel zu ändern, kontaktieren Sie uns bitte.)',
        'price'          => 'Transfergebühr',
        'footer_thanks'  => 'Vielen Dank für Ihre Wahl.',
    ],
    'ru' => [
        'title'          => 'ВАУЧЕР НА ТРАНСФЕР',
        'booking_no'     => 'Номер бронирования',
        'date'           => 'Дата',
        'customer'       => 'Данные клиента',
        'name'           => 'Имя и фамилия',
        'phone'          => 'Телефон',
        'email'          => 'Эл. почта',
        'passengers'     => 'Список пассажиров',
        'adults'         => 'Взрослые',
        'children'       => 'Дети',
        'arrival'        => 'ТРАНСФЕР ПО ПРИБЫТИИ',
        'departure'      => 'ТРАНСФЕР ПРИ ОТЪЕЗДЕ',
        'flight_date'    => 'Дата рейса',
        'flight_time'    => 'Время прилёта',
        'flight_no'      => 'Номер рейса',
        'hotel'          => 'Отель / Адрес',
        'pickup_time'    => 'Время подачи',
        'vehicle'        => 'Транспорт',
        'route'          => 'Маршрут',
        'pax'            => 'Пассажиров',
        'notes'          => 'Примечания',
        'child_seat'     => 'Детское кресло',
        'departure_flight_time' => 'Время вылета',
        'pickup_time_note'      => '(Чтобы изменить время подачи от отеля, свяжитесь с нами.)',
        'price'          => 'Стоимость трансфера',
        'footer_thanks'  => 'Спасибо, что выбрали нас.',
    ],
];

$L = $labels[$lang];
$langNames = ['tr' => 'TR', 'en' => 'EN', 'de' => 'DE', 'ru' => 'RU'];

function voucherLangUrl($l, $outId, $retId) {
    $url = '?lang=' . $l;
    if ($outId) $url .= '&out_id=' . $outId;
    if ($retId) $url .= '&ret_id=' . $retId;
    return $url;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($siteName) ?> — Voucher #<?= e($base['booking_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #e8e8e8;
        }

        /* ─── Toolbar (yazdırılmaz) ─────────────────── */
        .toolbar {
            background: #2c3e50;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .toolbar a, .toolbar button {
            color: #fff;
            text-decoration: none;
            padding: 5px 12px;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,.3);
            background: transparent;
            cursor: pointer;
            font-size: 13px;
        }
        .toolbar a:hover, .toolbar button:hover { background: rgba(255,255,255,.15); }
        .toolbar a.active { background: #4e73df; border-color: #4e73df; }
        .toolbar .print-btn { background: #27ae60; border-color: #27ae60; font-weight: 700; }
        .toolbar .sep { color: rgba(255,255,255,.3); }

        /* ─── A4 Sayfa ──────────────────────────────── */
        .page-wrap {
            display: flex;
            justify-content: center;
            padding: 24px 0 48px;
        }

        .voucher {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            padding: 12mm 14mm 10mm;
            position: relative;
        }

        /* ─── Header ────────────────────────────────── */
        .v-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1c4b56;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .v-logo { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .v-logo img { max-height: 90px; max-width: 200px; }
        .v-logo .site-name { font-size: 20px; font-weight: 800; color: #1c4b56; }
        .v-title-block { text-align: right; }
        .v-title { font-size: 22px; font-weight: 900; color: #008cad; letter-spacing: 1px; }
        .v-booking-no { font-size: 13px; color: #555; margin-top: 4px; }
        .v-booking-no strong { color: #1c4b56; font-size: 15px; }

        /* ─── Info Row ──────────────────────────────── */
        .info-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-box {
            flex: 1;
            border: 1px solid #d0d9dd;
            border-radius: 6px;
            overflow: hidden;
        }
        .info-box-header {
            background: #1c4b56;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 5px 10px;
        }
        .info-box-body {
            padding: 8px 10px;
        }
        .info-line {
            display: flex;
            gap: 6px;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .info-label {
            color: #777;
            min-width: 90px;
            font-size: 11px;
        }
        .info-value { font-weight: 600; color: #1a1a1a; }

        /* ─── Transfer Sections ─────────────────────── */
        .transfer-section {
            border: 1px solid #d0d9dd;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .transfer-header {
            padding: 7px 12px;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: .8px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .transfer-header.arrival   { background: #e8f4f8; color: #0d6efd; border-bottom: 2px solid #0d6efd; }
        .transfer-header.departure { background: #e8f8f2; color: #198754; border-bottom: 2px solid #198754; }
        .transfer-body {
            padding: 8px 12px;
        }
        .transfer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 16px;
        }
        .transfer-grid.three-col { grid-template-columns: 1fr 1fr 1fr; }
        .t-line {
            display: flex;
            flex-direction: column;
            margin-bottom: 6px;
        }
        .t-label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
        .t-value { font-size: 13px; font-weight: 700; color: #1a1a1a; }
        .t-value.big { font-size: 16px; color: #1c4b56; }

        /* ─── Passenger table ───────────────────────── */
        .pax-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .pax-table th { background: #f0f4f6; color: #555; font-size: 10px; text-transform: uppercase; padding: 4px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .pax-table td { padding: 4px 8px; border-bottom: 1px solid #f0f0f0; }

        /* ─── Notes ─────────────────────────────────── */
        .notes-box {
            background: #fffbeb;
            border: 1px solid #f0d080;
            border-radius: 5px;
            padding: 7px 10px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .notes-box strong { color: #856404; font-size: 11px; text-transform: uppercase; }

        /* ─── Footer ────────────────────────────────── */
        .v-footer {
            border-top: 2px solid #1c4b56;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #666;
            margin-top: auto;
        }
        .v-footer .contact { display: flex; gap: 16px; }
        .v-footer .thanks { font-style: italic; color: #008cad; font-weight: 600; }

        /* ─── Print CSS ─────────────────────────────── */
        @page { size: A4 portrait; margin: 0; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .page-wrap { padding: 0; }
            .voucher { box-shadow: none; width: 210mm; min-height: 297mm; }
            a { color: inherit !important; text-decoration: none; }
            a[href^="https://wa.me"] {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 2px solid #25d366 !important;
                background: #f6fff8 !important;
            }
        }
    </style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
    <button class="print-btn" onclick="window.print()">🖨 Print / PDF</button>
    <span class="sep">|</span>
    <span style="font-size:11px;opacity:.7;">Dil / Language:</span>
    <?php foreach ($langNames as $lCode => $lName): ?>
    <a href="<?= voucherLangUrl($lCode, $outId, $retId) ?>" class="<?= $lang === $lCode ? 'active' : '' ?>"><?= $lName ?></a>
    <?php endforeach; ?>
    <span class="sep">|</span>
    <a href="javascript:window.close()" style="opacity:.7;">✕ Kapat</a>
</div>

<div class="page-wrap">
<div class="voucher">

    <!-- Header -->
    <div class="v-header">
        <div class="v-logo">
            <?php if ($siteLogo): ?>
            <img src="<?= e(UPLOADS_URL . ltrim($siteLogo, '/')) ?>" alt="<?= e($siteName) ?>">
            <?php else: ?>
            <div class="site-name"><?= e($siteName) ?></div>
            <?php endif; ?>
            <?php if ($sitePhone): ?>
            <a href="https://wa.me/<?= $waNumber ?>"
               style="display:inline-flex;align-items:center;gap:6px;font-size:15px;font-weight:800;color:#1c4b56;text-decoration:none;border:2px solid #25d366;border-radius:8px;padding:5px 12px;background:#f6fff8;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#25d366" viewBox="0 0 16 16">
                    <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                </svg>
                <?= e($sitePhone) ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="v-title-block">
            <div class="v-title"><?= $L['title'] ?></div>
            <div class="v-booking-no">
                <?= $L['booking_no'] ?>: <strong><?= e($base['booking_number']) ?></strong>
            </div>
            <div style="font-size:11px;color:#888;margin-top:3px;">
                <?= $L['date'] ?>: <?= date('d.m.Y') ?>
            </div>
        </div>
    </div>

    <!-- Müşteri + Yolcu Bilgileri -->
    <div class="info-row">
        <!-- Müşteri -->
        <div class="info-box">
            <div class="info-box-header"><?= $L['customer'] ?></div>
            <div class="info-box-body">
                <div class="info-line">
                    <span class="info-label"><?= $L['name'] ?></span>
                    <span class="info-value"><?= e($base['customer_name']) ?></span>
                </div>
                <?php if ($base['customer_phone']): ?>
                <div class="info-line">
                    <span class="info-label"><?= $L['phone'] ?></span>
                    <span class="info-value"><?= e($base['customer_phone']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($base['customer_email']): ?>
                <div class="info-line">
                    <span class="info-label"><?= $L['email'] ?></span>
                    <span class="info-value"><?= e($base['customer_email']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-line">
                    <span class="info-label"><?= $L['pax'] ?></span>
                    <span class="info-value">
                        <?= (int)$base['adults'] ?> <?= $L['adults'] ?>
                        <?php if ((int)$base['children'] > 0): ?>
                        + <?= (int)$base['children'] ?> <?= $L['children'] ?>
                        <?php endif; ?>
                        <?php if ((int)$base['child_seat'] > 0): ?>
                        (<?= (int)$base['child_seat'] ?> <?= $L['child_seat'] ?>)
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Yolcu Listesi -->
        <?php if (!empty($passengers)): ?>
        <div class="info-box">
            <div class="info-box-header"><?= $L['passengers'] ?></div>
            <div class="info-box-body" style="padding:0;">
                <table class="pax-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= $L['name'] ?></th>
                            <th><?= $L['adults'] ?>/<?= $L['children'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $paxI = 1; foreach ($passengers as $p): ?>
                        <tr>
                            <td><?= $paxI++ ?></td>
                            <td><?= e($p['full_name']) ?></td>
                            <td><?= $p['passenger_type'] === 'adult' ? $L['adults'] : $L['children'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- GELİŞ TRANSFERİ -->
    <?php if ($out): ?>
    <div class="transfer-section">
        <div class="transfer-header arrival">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M9.152 5.457 5.5 4.25l-.721 1.248 1.5.866-2.5 1.443-1.249-.72L1.28 8.335l3.652 1.208L6.09 7.818l1.5.866-1.5 2.598-1.5-.867-.75 1.3L6.09 12.8l5-2.887L9.152 5.457z"/></svg>
            <?= $L['arrival'] ?> — <?= e($out['booking_number']) ?>
        </div>
        <div class="transfer-body">
            <div class="transfer-grid three-col">
                <div class="t-line">
                    <span class="t-label"><?= $L['flight_date'] ?></span>
                    <span class="t-value big"><?= $out['flight_date'] ? date('d.m.Y', strtotime($out['flight_date'])) : '-' ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['flight_time'] ?></span>
                    <span class="t-value big"><?= $out['flight_time'] ? date('H:i', strtotime($out['flight_time'])) : '-' ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['flight_no'] ?></span>
                    <span class="t-value big"><?= e($out['flight_number'] ?: '-') ?></span>
                </div>
            </div>
            <div class="transfer-grid">
                <div class="t-line">
                    <span class="t-label"><?= $L['hotel'] ?></span>
                    <span class="t-value"><?= e($out['hotel_address'] ?: '-') ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['vehicle'] ?></span>
                    <span class="t-value"><?= e(trim($out['vehicle_name'] ?? '') ?: '-') ?></span>
                </div>
                <?php if ($out['destination_title']): ?>
                <div class="t-line">
                    <span class="t-label"><?= $L['route'] ?></span>
                    <span class="t-value"><?= e($out['destination_title']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ((float)($out['total_price'] ?? 0) > 0): ?>
                <div class="t-line">
                    <span class="t-label"><?= $L['price'] ?></span>
                    <span class="t-value" style="font-size:15px;font-weight:800;color:#1c4b56;">
                        <?= number_format((float)$out['total_price'], 2, ',', '.') ?> <?= e($out['currency'] ?? 'EUR') ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DÖNÜŞ TRANSFERİ -->
    <?php if ($ret): ?>
    <div class="transfer-section">
        <div class="transfer-header departure">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M6.848 10.543 10.5 11.75l.721-1.248-1.5-.866 2.5-1.443 1.249.72 1.25-2.164-3.652-1.208L9.91 8.182l-1.5-.866 1.5-2.598 1.5.867.75-1.3L9.91 3.2l-5 2.887 1.938 3.456z"/></svg>
            <?= $L['departure'] ?> — <?= e($ret['booking_number']) ?>
        </div>
        <div class="transfer-body">
            <div class="transfer-grid three-col">
                <div class="t-line">
                    <span class="t-label"><?= $L['flight_date'] ?></span>
                    <span class="t-value big"><?= $ret['flight_date'] ? date('d.m.Y', strtotime($ret['flight_date'])) : '-' ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['departure_flight_time'] ?></span>
                    <span class="t-value big"><?= $ret['flight_time'] ? date('H:i', strtotime($ret['flight_time'])) : '-' ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['flight_no'] ?></span>
                    <span class="t-value big"><?= e($ret['flight_number'] ?: '-') ?></span>
                </div>
            </div>
            <div class="transfer-grid">
                <div class="t-line">
                    <span class="t-label"><?= $L['hotel'] ?></span>
                    <span class="t-value"><?= e($ret['hotel_address'] ?: '-') ?></span>
                </div>
                <div class="t-line">
                    <span class="t-label"><?= $L['vehicle'] ?></span>
                    <span class="t-value"><?= e(trim($ret['vehicle_name'] ?? '') ?: '-') ?></span>
                </div>
                <?php if ($ret['destination_title']): ?>
                <div class="t-line">
                    <span class="t-label"><?= $L['route'] ?></span>
                    <span class="t-value"><?= e($ret['destination_title']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ((float)($ret['total_price'] ?? 0) > 0): ?>
                <div class="t-line">
                    <span class="t-label"><?= $L['price'] ?></span>
                    <span class="t-value" style="font-size:15px;font-weight:800;color:#1c4b56;">
                        <?= number_format((float)$ret['total_price'], 2, ',', '.') ?> <?= e($ret['currency'] ?? 'EUR') ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <div class="transfer-grid" style="margin-top:6px;">
                <div class="t-line">
                    <span class="t-label"><?= $L['pickup_time'] ?></span>
                    <span class="t-value big"><?= $ret['pickup_time'] ? date('H:i', strtotime($ret['pickup_time'])) : '-' ?></span>
                </div>
                <div class="t-line" style="grid-column:1/-1;">
                    <span class="t-value" style="font-size:0.75rem;color:#666;font-style:italic;"><?= $L['pickup_time_note'] ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notlar -->
    <?php $noteText = trim(($out ? ($out['notes'] ?? '') : '') ?: ($ret ? ($ret['notes'] ?? '') : '')); ?>
    <?php if ($noteText): ?>
    <div class="notes-box">
        <strong><?= $L['notes'] ?>:</strong> <?= nl2br(e($noteText)) ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="v-footer">
        <div class="contact">
            <?php if ($sitePhone): ?><a href="https://wa.me/<?= $waNumber ?>" style="color:inherit;text-decoration:none;">📞 <?= e($sitePhone) ?></a><?php endif; ?>
            <?php if ($siteEmail): ?><span>✉ <?= e($siteEmail) ?></span><?php endif; ?>
            <?php if ($siteWeb): ?><span>🌐 <?= e($siteWeb) ?></span><?php endif; ?>
        </div>
        <div class="thanks"><?= $L['footer_thanks'] ?></div>
    </div>

</div><!-- /voucher -->
</div><!-- /page-wrap -->

</body>
</html>
