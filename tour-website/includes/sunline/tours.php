<?php
/**
 * Turlar — ortak yardımcılar (pages/tours.php + pages/tour-detail.php)
 * Turlarda fiyat/araç yok; "Rezervasyon" düğmesi WhatsApp'a (Anasayfa & Tema > WhatsApp,
 * boşsa Site Ayarları > iletişim telefonu) tur adıyla hazır mesaj açar.
 */

require_once __DIR__ . '/core.php';

/** Tur sayfalarına özel metinler */
function sl_tour_t($key, $vars = []) {
    static $T = [
        'tr' => [
            'wa_msg' => 'Merhaba, {tour} turu için rezervasyon yapmak istiyorum.',
            'wa_info' => 'Merhaba, turlarınız hakkında bilgi almak istiyorum.',
            'about' => 'Tur Hakkında',
            'book_t' => 'Rezervasyon',
            'book_s' => 'Tarih ve kişi sayısını WhatsApp\'tan yazın, size hemen dönüş yapalım.',
            'others' => 'Diğer Turlar',
            'all' => 'Tüm turlar',
            'photos' => 'Fotoğraflar',
            'photo' => 'Fotoğraf',
            'prev' => 'Önceki',
            'next' => 'Sonraki',
            'close' => 'Kapat',
            'empty' => 'Turlarımız çok yakında burada. Bilgi almak için WhatsApp\'tan yazabilirsiniz.',
        ],
        'en' => [
            'wa_msg' => 'Hello, I would like to book the {tour} tour.',
            'wa_info' => 'Hello, I would like information about your tours.',
            'about' => 'About the Tour',
            'book_t' => 'Booking',
            'book_s' => 'Send us the date and number of guests on WhatsApp and we will get back to you right away.',
            'others' => 'Other Tours',
            'all' => 'All tours',
            'photos' => 'Photos',
            'photo' => 'Photo',
            'prev' => 'Previous',
            'next' => 'Next',
            'close' => 'Close',
            'empty' => 'Our tours are coming soon. Message us on WhatsApp for information.',
        ],
        'de' => [
            'wa_msg' => 'Hallo, ich möchte die Tour „{tour}“ buchen.',
            'wa_info' => 'Hallo, ich hätte gerne Informationen zu Ihren Touren.',
            'about' => 'Über die Tour',
            'book_t' => 'Reservierung',
            'book_s' => 'Schreiben Sie uns Datum und Personenzahl per WhatsApp – wir melden uns umgehend.',
            'others' => 'Weitere Touren',
            'all' => 'Alle Touren',
            'photos' => 'Fotos',
            'photo' => 'Foto',
            'prev' => 'Zurück',
            'next' => 'Weiter',
            'close' => 'Schließen',
            'empty' => 'Unsere Touren sind bald hier. Für Informationen schreiben Sie uns per WhatsApp.',
        ],
        'ru' => [
            'wa_msg' => 'Здравствуйте, хочу забронировать экскурсию «{tour}».',
            'wa_info' => 'Здравствуйте, хочу узнать о ваших экскурсиях.',
            'about' => 'Об экскурсии',
            'book_t' => 'Бронирование',
            'book_s' => 'Напишите нам в WhatsApp дату и количество человек — мы сразу ответим.',
            'others' => 'Другие экскурсии',
            'all' => 'Все экскурсии',
            'photos' => 'Фотографии',
            'photo' => 'Фото',
            'prev' => 'Назад',
            'next' => 'Далее',
            'close' => 'Закрыть',
            'empty' => 'Скоро здесь появятся наши экскурсии. Напишите нам в WhatsApp, чтобы узнать подробности.',
        ],
    ];
    $lang = sl_lang();
    $v = $T[$lang][$key] ?? ($T['en'][$key] ?? $key);
    foreach ($vars as $k => $val) $v = str_replace('{' . $k . '}', (string)$val, $v);
    return $v;
}

/** Tur için WhatsApp rezervasyon bağlantısı (numara yoksa iletişim sayfası) */
function sl_tour_book_url($title) {
    $wa = sl_wa_link(sl_tour_t('wa_msg', ['tour' => $title]));
    return $wa !== '' ? $wa : sl_page_url('contact');
}

/**
 * Yayındaki turlar (aktif dilde; çeviri yoksa varsayılan dil)
 * $exceptId verilirse o tur listeden çıkarılır.
 */
function sl_tours($exceptId = 0) {
    $lang = sl_lang();
    $out = [];
    try {
        $st = getDB()->prepare("
            SELECT t.id, t.image,
                   COALESCE(NULLIF(tt.title, ''), t.title) AS title,
                   COALESCE(NULLIF(tt.slug, ''), t.slug) AS slug,
                   COALESCE(tt.description, t.description) AS description
            FROM tours t
            LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
            WHERE t.status = 'published' AND t.id <> ?
            ORDER BY t.is_featured DESC, t.sort_order, t.id
        ");
        $st->execute([$lang, (int)$exceptId]);
        $prefix = getPageSlugByKey('tours');
        foreach ($st->fetchAll() as $r) {
            $r['url'] = langUrl($prefix . '/' . $r['slug']);
            $r['img'] = !empty($r['image']) ? getMediaUrl($r['image']) : sl_default_base()['hero']['image'];
            $r['book'] = sl_tour_book_url($r['title']);
            $out[] = $r;
        }
    } catch (Exception $e) {}
    return $out;
}

/** Tur kartı (transfer kartıyla aynı görünüm; fiyat yerine kısa açıklama) */
function sl_tour_card($t) {
    $desc = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$t['description'])));
    ?>
<article class="rc rc-tour">
  <a class="rc-media" href="<?= e($t['url']) ?>" title="<?= e($t['title']) ?>">
    <img src="<?= e($t['img']) ?>" alt="<?= e($t['title']) ?>" width="640" height="420" loading="lazy" decoding="async">
    <div class="rc-ov"><h3><?= e($t['title']) ?></h3></div>
  </a>
  <div class="rc-body">
    <?php if ($desc !== ''): ?><p class="rc-desc"><?= e(mb_strimwidth($desc, 0, 170, '…', 'UTF-8')) ?></p><?php endif; ?>
    <a class="btn btn-cta rc-book" href="<?= e($t['book']) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i><?= sl_e('book') ?></a>
  </div>
</article>
<?php
}

/**
 * Tur galerisi resimleri (Tur Yönetimi > Turlar > Galeri, tours.gallery JSON dizisi)
 * Dönüş: [['src' => tam adres, 'alt' => "Tur adı — Fotoğraf 1"], ...]; galeri yoksa []
 */
function sl_tour_gallery($tour) {
    $list = json_decode((string)($tour['gallery'] ?? ''), true);
    $out = [];
    if (!is_array($list)) return $out;
    foreach ($list as $p) {
        if (!is_string($p) || trim($p) === '') continue;
        $out[] = ['src' => getMediaUrl(trim($p)), 'alt' => $tour['title'] . ' — ' . sl_tour_t('photo') . ' ' . (count($out) + 1)];
    }
    return $out;
}

/** Galerideki tek resim bağlantısı (tıklayınca büyütme penceresi; JS yoksa resim doğrudan açılır) */
function sl_tour_gallery_item($g, $i, $more = 0) {
    ?><a class="tg-i" href="<?= e($g['src']) ?>" data-tg-i="<?= (int)$i ?>" aria-label="<?= e($g['alt']) ?>"><img src="<?= e($g['src']) ?>" alt="<?= e($g['alt']) ?>" width="600" height="450" loading="lazy" decoding="async"><?php if ($more > 0): ?><span class="tg-more">+<?= (int)$more ?></span><?php endif; ?></a><?php
}

/**
 * Galeri bloğu (ızgara: 3 sütun, telefonda 2; ilk 6 resim, 6.'da "+N").
 * Tüm resimler büyütme penceresinde gezilebilir.
 */
function sl_tour_gallery_html($items) {
    $n = count($items);
    if (!$n) return;
    ?>
<div class="tg-wrap" id="fotograflar">
  <h3 class="tg-t"><?= e(sl_tour_t('photos')) ?> <small>(<?= $n ?>)</small></h3>
  <div class="tg tg-grid n<?= min($n, 3) ?>"><?php foreach (array_slice($items, 0, 6) as $i => $g) sl_tour_gallery_item($g, $i, ($i === 5 && $n > 6) ? $n - 6 : 0); ?></div>
</div>
<?php
}

/** Büyütme penceresi + resim listesi (sayfada bir kez) */
function sl_tour_gallery_lightbox($items) {
    if (!$items) return;
    ?>
<div class="tg-lb" id="tgLb" role="dialog" aria-modal="true" aria-label="<?= e(sl_tour_t('photos')) ?>" hidden>
  <button type="button" class="tg-x" data-tg-x aria-label="<?= e(sl_tour_t('close')) ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  <button type="button" class="tg-p" data-tg-p aria-label="<?= e(sl_tour_t('prev')) ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
  <figure><img id="tgLbImg" alt=""><figcaption id="tgLbCap"></figcaption></figure>
  <button type="button" class="tg-n" data-tg-n aria-label="<?= e(sl_tour_t('next')) ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
</div>
<script type="application/json" id="tgData"><?= sl_json($items) ?></script>
<?php
}

/** Galeri / büyütme penceresi davranışı — footer'da $extraJs ile basılır */
function sl_tour_gallery_js() {
    return <<<'JS'
<script>
(function () {
  var lb = document.getElementById('tgLb'), data = document.getElementById('tgData');
  if (!lb || !data) return;
  var items;
  try { items = JSON.parse(data.textContent); } catch (e) { return; }
  if (!items || !items.length) return;
  var img = document.getElementById('tgLbImg'), cap = document.getElementById('tgLbCap');
  var cur = 0, last = null, sx = null, sy = null, tek = items.length < 2;
  lb.querySelector('[data-tg-p]').hidden = tek;
  lb.querySelector('[data-tg-n]').hidden = tek;
  function idx(i) { return (i % items.length + items.length) % items.length; }
  function show(i) {
    cur = idx(i);
    img.src = items[cur].src;
    img.alt = items[cur].alt;
    cap.textContent = (cur + 1) + ' / ' + items.length;
    if (!tek) { new Image().src = items[idx(cur + 1)].src; new Image().src = items[idx(cur - 1)].src; }
  }
  function open(i) {
    last = document.activeElement;
    show(i);
    lb.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    lb.querySelector('[data-tg-x]').focus();
  }
  function close() {
    lb.hidden = true;
    document.documentElement.style.overflow = '';
    img.removeAttribute('src');
    if (last && last.focus) last.focus();
  }
  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-tg-i]');
    if (a) { e.preventDefault(); open(parseInt(a.getAttribute('data-tg-i'), 10) || 0); }
  });
  lb.addEventListener('click', function (e) {
    if (e.target.closest('[data-tg-x]') || e.target === lb) close();
    else if (e.target.closest('[data-tg-p]')) show(cur - 1);
    else if (e.target.closest('[data-tg-n]')) show(cur + 1);
  });
  document.addEventListener('keydown', function (e) {
    if (lb.hidden) return;
    if (e.key === 'Escape') { close(); }
    else if (e.key === 'ArrowLeft' && !tek) { show(cur - 1); }
    else if (e.key === 'ArrowRight' && !tek) { show(cur + 1); }
    else if (e.key === 'Tab') {
      var b = Array.prototype.filter.call(lb.querySelectorAll('button'), function (x) { return !x.hidden; });
      var i = b.indexOf(document.activeElement);
      e.preventDefault();
      b[(i + (e.shiftKey ? -1 : 1) + b.length) % b.length].focus();
    }
  });
  lb.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
  lb.addEventListener('touchend', function (e) {
    if (sx === null) return;
    var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
    sx = null;
    if (!tek && Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) show(cur + (dx < 0 ? 1 : -1));
  }, { passive: true });
})();
</script>
JS;
}

/** Tur kartları için küçük stil (kartlar eşit boy, düğme altta) — sayfa <head>'ine $extraCss ile girer */
function sl_tour_css() {
    return '<style>'
        . '.rc-tour{display:flex;flex-direction:column}'
        . '.rc-tour .rc-body{flex:1;display:flex;flex-direction:column}'
        . '.rc-tour .rc-desc{color:var(--cmuted);font-size:.95rem;line-height:1.6;margin:0 0 16px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}'
        . '.rc-tour .rc-book{margin-top:auto}'
        . '.tour-empty{text-align:center;max-width:620px;margin:40px auto 0;color:var(--muted)}'
        . '</style>';
}

/** Tur galerisi + büyütme penceresi stili — yalnız galerisi olan tur sayfasında $extraCss'e eklenir */
function sl_tour_gallery_css() {
    return '<style>'
        // Galeri (ızgara)
        . '.tg-wrap{margin-top:2.4rem}'
        . '.tg-t{font-family:var(--ff-d);font-size:1.4rem;font-weight:700;margin:0 0 1rem}'
        . '.tg-t small{font-size:.9rem;font-weight:500;color:var(--muted);margin-left:.25rem}'
        . '.tg-i{position:relative;display:block;overflow:hidden;border-radius:14px;background:var(--bg2);cursor:zoom-in}'
        . '.tg-i img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .45s}'
        . '.tg-i:hover img{transform:scale(1.05)}'
        . '.tg-i:focus-visible{outline:3px solid var(--accent);outline-offset:2px}'
        . '.tg-more{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.55);color:#fff;font-size:1.6rem;font-weight:700}'
        . '.tg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}'
        . '.tg-grid .tg-i{aspect-ratio:4/3}'
        . '.tg-grid.n1{grid-template-columns:1fr}.tg-grid.n1 .tg-i{aspect-ratio:16/9}'
        . '.tg-grid.n2{grid-template-columns:1fr 1fr}'
        . '@media(max-width:575.98px){.tg-grid{grid-template-columns:1fr 1fr;gap:8px}.tg-grid.n1{grid-template-columns:1fr}}'
        // Büyütme penceresi
        . '.tg-lb{position:fixed;inset:0;z-index:2100;display:flex;align-items:center;justify-content:center;padding:60px 72px;background:rgba(8,8,8,.94)}'
        . '.tg-lb[hidden]{display:none}'
        . '.tg-lb figure{margin:0;display:flex;flex-direction:column;align-items:center;max-width:100%}'
        . '.tg-lb img{max-width:100%;max-height:calc(100vh - 150px);object-fit:contain;border-radius:8px;user-select:none}'
        . '.tg-lb figcaption{color:#ddd;font-size:.9rem;margin-top:12px}'
        . '.tg-lb button{position:absolute;width:48px;height:48px;border:0;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;font-size:1.3rem;display:flex;align-items:center;justify-content:center;cursor:pointer}'
        . '.tg-lb button[hidden]{display:none}'
        . '.tg-lb button:hover{background:rgba(255,255,255,.28)}'
        . '.tg-lb .tg-x{top:14px;right:14px}'
        . '.tg-lb .tg-p{left:14px;top:calc(50% - 24px)}'
        . '.tg-lb .tg-n{right:14px;top:calc(50% - 24px)}'
        . '@media(max-width:575.98px){.tg-lb{padding:60px 8px 84px}.tg-lb .tg-p,.tg-lb .tg-n{top:auto;bottom:18px}.tg-lb .tg-p{left:calc(50% - 60px)}.tg-lb .tg-n{right:calc(50% - 60px)}}'
        . '</style>';
}
