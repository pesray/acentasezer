<?php
/**
 * Tur detay sayfası (veriler Tur Yönetimi > Turlar ekranından gelir)
 * Rezervasyon formu eski API alanlarıyla çalışır (booking_type = tour).
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$slug = $_GET['slug'] ?? '';
$lang = sl_lang();
$db = getDB();

$stmt = $db->prepare("
    SELECT t.*, COALESCE(tt.title, t.title) AS title, COALESCE(tt.slug, t.slug) AS slug,
           COALESCE(tt.description, t.description) AS description,
           COALESCE(tt.content, t.content) AS content,
           COALESCE(tt.meta_title, t.meta_title) AS meta_title,
           COALESCE(tt.meta_description, t.meta_description) AS meta_description
    FROM tours t
    LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
    WHERE (t.slug = ? OR tt.slug = ?) AND t.status = 'published'
    LIMIT 1
");
$stmt->execute([$lang, $slug, $slug]);
$tour = $stmt->fetch();

if (!$tour) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Araçlar ve fiyatlar (önce bu dil, yoksa herhangi bir dil)
$vehicles = [];
try {
    $st = $db->prepare("SELECT v.*, tv.price, tv.currency FROM tour_vehicles tv JOIN vehicles v ON tv.vehicle_id = v.id WHERE tv.tour_id = ? AND tv.language_code = ? AND v.is_active = 1 ORDER BY v.sort_order, tv.price");
    $st->execute([$tour['id'], $lang]);
    $vehicles = $st->fetchAll();
    if (!$vehicles) {
        $st = $db->prepare("SELECT v.*, MIN(tv.price) AS price, MAX(tv.currency) AS currency FROM tour_vehicles tv JOIN vehicles v ON tv.vehicle_id = v.id WHERE tv.tour_id = ? AND v.is_active = 1 GROUP BY v.id ORDER BY v.sort_order");
        $st->execute([$tour['id']]);
        $vehicles = $st->fetchAll();
    }
} catch (Exception $e) {}
$vmeta = sl_vehicles();

$T = [
    'tr' => ['vehicles' => 'Araç Seçenekleri', 'form' => 'Tur Rezervasyonu', 'select' => 'Seç', 'name' => 'Ad Soyad', 'email' => 'E-posta', 'phone' => 'Telefon / WhatsApp',
        'pickup_location' => 'Alınış yeri (otel)', 'pickup_date' => 'Tur tarihi', 'pickup_time' => 'Alınış saati', 'return_time' => 'Dönüş saati', 'notes' => 'Notunuz',
        'send' => 'Rezervasyon Yap', 'pick_vehicle' => 'Lütfen önce bir araç seçin.', 'ok' => 'Rezervasyonunuz alındı! En kısa sürede sizinle iletişime geçeceğiz.', 'no' => 'Rezervasyon No', 'per' => 'araç başı'],
    'en' => ['vehicles' => 'Vehicle Options', 'form' => 'Tour Booking', 'select' => 'Select', 'name' => 'Full name', 'email' => 'Email', 'phone' => 'Phone / WhatsApp',
        'pickup_location' => 'Pickup location (hotel)', 'pickup_date' => 'Tour date', 'pickup_time' => 'Pickup time', 'return_time' => 'Return time', 'notes' => 'Your note',
        'send' => 'Book Now', 'pick_vehicle' => 'Please select a vehicle first.', 'ok' => 'Your booking has been received! We will contact you shortly.', 'no' => 'Booking No.', 'per' => 'per vehicle'],
    'de' => ['vehicles' => 'Fahrzeugoptionen', 'form' => 'Tourbuchung', 'select' => 'Wählen', 'name' => 'Vor- und Nachname', 'email' => 'E-Mail', 'phone' => 'Telefon / WhatsApp',
        'pickup_location' => 'Abholort (Hotel)', 'pickup_date' => 'Tourdatum', 'pickup_time' => 'Abholzeit', 'return_time' => 'Rückfahrzeit', 'notes' => 'Ihre Anmerkung',
        'send' => 'Jetzt buchen', 'pick_vehicle' => 'Bitte wählen Sie zuerst ein Fahrzeug.', 'ok' => 'Ihre Buchung ist eingegangen! Wir melden uns in Kürze.', 'no' => 'Buchungsnr.', 'per' => 'pro Fahrzeug'],
    'ru' => ['vehicles' => 'Варианты автомобилей', 'form' => 'Бронирование экскурсии', 'select' => 'Выбрать', 'name' => 'Имя и фамилия', 'email' => 'E-mail', 'phone' => 'Телефон / WhatsApp',
        'pickup_location' => 'Место посадки (отель)', 'pickup_date' => 'Дата экскурсии', 'pickup_time' => 'Время выезда', 'return_time' => 'Время возвращения', 'notes' => 'Примечание',
        'send' => 'Забронировать', 'pick_vehicle' => 'Сначала выберите автомобиль.', 'ok' => 'Бронирование принято! Мы скоро свяжемся с вами.', 'no' => 'Номер брони', 'per' => 'за автомобиль'],
];
$t = $T[$lang] ?? $T['en'];

$prefix = getPageSlugByKey('tours');
$tourUrl = langUrl($prefix . '/' . $tour['slug']);
$listTitle = sl_t('tours_t');
$img = !empty($tour['image']) ? getMediaUrl($tour['image']) : '';

$seoTitle = (!empty($tour['meta_title']) ? $tour['meta_title'] : $tour['title']) . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = !empty($tour['meta_description']) ? $tour['meta_description'] : (string)$tour['description'];
$canonicalUrl = $tourUrl;
if ($img !== '') $ogImage = $img;
$bodyClass = 'sl-tour';
$schemaVehicles = [];
foreach ($vehicles as $v) $schemaVehicles[] = ['price' => (float)$v['price'], 'currency' => $v['currency'], 'name' => sl_vehicle_name($v)];
$pageSchema = [
    getBreadcrumbSchema([['name' => $listTitle, 'url' => langUrl($prefix)], ['name' => $tour['title'], 'url' => $tourUrl]]),
    getTourSchema($tour, $tourUrl, $schemaVehicles),
];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $tour['title'], 'sub' => $tour['description'] ?? '', 'crumbs' => [[$listTitle, langUrl($prefix)], [$tour['title'], $tourUrl]], 'img' => $img, 'size' => 'sm', 'eyebrow' => sl_t('tours_e')]);
?>
<section class="page-body">
  <div class="container">
    <?php if (!empty($tour['content'])): ?>
    <div class="prose mb-5" style="max-width:900px"><?= $tour['content'] ?></div>
    <?php endif; ?>

    <?php if ($vehicles): ?>
    <div class="eyebrow"><?= e($tour['title']) ?></div>
    <h2 class="sec-t"><?= e($t['vehicles']) ?></h2>
    <div class="vlist" id="tourVehicles">
      <?php foreach ($vehicles as $v): $m = $vmeta[(int)$v['id']] ?? null; ?>
      <div class="vrow">
        <div class="vrow-img"><i class="bi bi-car-front"></i><?php if (!empty($v['image'])): ?><img src="<?= e(getMediaUrl($v['image'])) ?>" alt="<?= e(sl_vehicle_name($v)) ?>" loading="lazy" width="400" height="250" onerror="this.remove()"><?php endif; ?></div>
        <div>
          <h3><?= e(sl_vehicle_name($v)) ?></h3>
          <div class="vspec">
            <span><i class="bi bi-people-fill"></i><?= e(sl_t('cap_pax', ['n' => (int)$v['capacity']])) ?></span>
            <span><i class="bi bi-briefcase-fill"></i><?= e(sl_t('cap_lug', ['n' => (int)$v['luggage_capacity']])) ?></span>
          </div>
          <?php if ($m && $m['sv']): ?><div class="vsv"><?php foreach (array_slice($m['sv'], 0, 8) as $s): ?><span><?= e($s['n']) ?></span><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="vprice">
          <div class="pp"><?= e($t['per']) ?> <b><?= e(sl_money((float)$v['price'], $v['currency'])) ?></b></div>
          <button type="button" class="btn btn-cta js-tour-v" data-id="<?= (int)$v['id'] ?>" data-name="<?= e(sl_vehicle_name($v)) ?>" data-cap="<?= (int)$v['capacity'] ?>" data-cs="<?= (int)$v['child_seat_capacity'] ?>" data-price="<?= (float)$v['price'] ?>" data-cur="<?= e($v['currency']) ?>"><?= e($t['select']) ?></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="box mt-4" id="tourForm" hidden>
      <div class="wz-h"><?= e($t['form']) ?> · <span id="tfVehicle"></span></div>
      <div id="tfAlert" class="mb-3" hidden></div>
      <form class="fm row g-3" id="tourInquiryForm" novalidate>
        <input type="hidden" name="booking_type" value="tour">
        <input type="hidden" name="tour_id" value="<?= (int)$tour['id'] ?>">
        <input type="hidden" name="vehicle_id" id="tfVid" value="">
        <div class="col-md-4"><label class="d-block" for="tfName"><?= e($t['name']) ?> *</label><input class="form-control" id="tfName" name="full_name" required autocomplete="name"></div>
        <div class="col-md-4"><label class="d-block" for="tfEmail"><?= e($t['email']) ?> *</label><input class="form-control" id="tfEmail" name="email" type="email" required autocomplete="email"></div>
        <div class="col-md-4"><label class="d-block" for="tfPhone"><?= e($t['phone']) ?> *</label><input class="form-control" id="tfPhone" name="phone" type="tel" required autocomplete="tel"></div>
        <div class="col-md-6"><label class="d-block" for="tfLoc"><?= e($t['pickup_location']) ?> *</label><input class="form-control" id="tfLoc" name="pickup_location" required></div>
        <div class="col-6 col-md-2"><label class="d-block" for="tfDate"><?= e($t['pickup_date']) ?> *</label><input class="form-control" id="tfDate" name="pickup_date" type="date" min="<?= date('Y-m-d') ?>" required></div>
        <div class="col-6 col-md-2"><label class="d-block" for="tfTime"><?= e($t['pickup_time']) ?> *</label><input class="form-control" id="tfTime" name="pickup_time" type="time" required></div>
        <div class="col-6 col-md-2"><label class="d-block" for="tfRet"><?= e($t['return_time']) ?></label><input class="form-control" id="tfRet" name="return_time" type="time"></div>
        <div class="col-4 col-md-2"><label class="d-block" for="tfA"><?= sl_e('adults') ?></label><input class="form-control" id="tfA" name="adults" type="number" min="1" value="1" inputmode="numeric"></div>
        <div class="col-4 col-md-2"><label class="d-block" for="tfC"><?= sl_e('children') ?></label><input class="form-control" id="tfC" name="children" type="number" min="0" value="0" inputmode="numeric"></div>
        <div class="col-4 col-md-2"><label class="d-block" for="tfS"><?= sl_e('child_seat') ?></label><input class="form-control" id="tfS" name="child_seat" type="number" min="0" value="0" inputmode="numeric"></div>
        <div class="col-12"><label class="d-block" for="tfNotes"><?= e($t['notes']) ?></label><textarea class="form-control" id="tfNotes" name="notes" rows="3"></textarea></div>
        <div class="col-12"><button type="submit" class="btn btn-cta" id="tfSend"><?= e($t['send']) ?></button></div>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('tourInquiryForm');
  if (!form) return;
  var box = document.getElementById('tourForm'), alertBox = document.getElementById('tfAlert');
  var price = 0, cur = 'EUR', cap = 16;
  document.querySelectorAll('.js-tour-v').forEach(function (b) {
    b.addEventListener('click', function () {
      document.getElementById('tfVid').value = b.dataset.id;
      document.getElementById('tfVehicle').textContent = b.dataset.name;
      price = parseFloat(b.dataset.price) || 0; cur = b.dataset.cur || 'EUR'; cap = parseInt(b.dataset.cap, 10) || 16;
      document.getElementById('tfA').max = cap; document.getElementById('tfC').max = cap;
      document.getElementById('tfS').max = parseInt(b.dataset.cs, 10) || 0;
      box.hidden = false;
      box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
  function show(ok, html) {
    alertBox.hidden = false;
    alertBox.className = 'alert-sl ' + (ok ? 'ok-msg' : 'err-msg') + ' mb-3';
    alertBox.innerHTML = html;
  }
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!document.getElementById('tfVid').value) { show(false, <?= json_encode($t['pick_vehicle'], JSON_UNESCAPED_UNICODE) ?>); return; }
    var bad = Array.prototype.slice.call(form.querySelectorAll('[required]')).filter(function (i) { return !i.value.trim(); });
    if (bad.length) { bad[0].classList.add('is-invalid'); bad[0].focus(); show(false, <?= json_encode(sl_t('err_required'), JSON_UNESCAPED_UNICODE) ?>); return; }
    var btn = document.getElementById('tfSend'); btn.disabled = true;
    var fd = new FormData(form); fd.append('total_price', price); fd.append('currency', cur);
    fetch(<?= json_encode(SITE_URL . '/api/booking') ?>, { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(function (d) {
      if (d.success) { show(true, <?= json_encode($t['ok'], JSON_UNESCAPED_UNICODE) ?> + '<br><strong>' + <?= json_encode($t['no'], JSON_UNESCAPED_UNICODE) ?> + ': ' + (d.booking_number || '') + '</strong>'); form.hidden = true; }
      else { show(false, d.message || <?= json_encode(sl_t('err_generic'), JSON_UNESCAPED_UNICODE) ?>); btn.disabled = false; }
    }).catch(function () { show(false, <?= json_encode(sl_t('err_generic'), JSON_UNESCAPED_UNICODE) ?>); btn.disabled = false; });
  });
  form.addEventListener('input', function (e) { e.target.classList.remove('is-invalid'); });
});
</script>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
