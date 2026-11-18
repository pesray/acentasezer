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
