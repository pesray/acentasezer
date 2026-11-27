<?php
/**
 * Sunline teması — Google yorumları alanı
 *
 * Veri: google_reviews tablosu (admin > İçerik > Google Yorumları; migrations/036)
 * Ayar: sections.section_type = sun_reviews (admin > Anasayfa & Tema > Google Yorumları)
 *       base: position, rating, count, url, limit · dil: eyebrow, title, subtitle, link_label
 *
 * Yalnız "görünür" ve metni olan yorumlar gösterilir. Tablo yoksa (SQL henüz çalışmadıysa)
 * alan sessizce gizlenir, site etkilenmez.
 * Yorumlara yıldız şeması (Review/AggregateRating) EKLENMEZ: Google, işletmenin kendi
 * sitesindeki yorumlarda bunu göstermiyor ve kural ihlali sayıyor.
 *
 * PHP 7.4 uyumlu.
 */

/** Görünür yorumlar (sıra: sort_order, yeni tarih önce) — istek başına bir kez okunur */
function sl_reviews_visible() {
    static $rows = null;
    if ($rows !== null) return $rows;
    $rows = [];
    try {
        $st = getDB()->query("SELECT id, author_name, author_photo, rating, content, language_code, review_date
            FROM google_reviews
            WHERE is_visible = 1 AND TRIM(content) <> ''
            ORDER BY sort_order ASC, review_date DESC, id DESC");
        $rows = $st->fetchAll();
    } catch (Exception $e) {
        $rows = [];
    }
    return $rows;
}

/** Sayfanın dilinde yazılmış yorumlar öne alınır (kendi aralarındaki sıra korunur) */
function sl_reviews_for_lang($lang, $limit = 0) {
    $same = [];
    $other = [];
    foreach (sl_reviews_visible() as $r) {
        if (strtolower((string)$r['language_code']) === $lang) $same[] = $r; else $other[] = $r;
    }
    $all = array_merge($same, $other);
    return $limit > 0 ? array_slice($all, 0, $limit) : $all;
}

/** Rusça çoğul: 1 отзыв, 2 отзыва, 5 отзывов */
function sl_ru_plural($n, $one, $few, $many) {
    $n = abs((int)$n);
    $m10 = $n % 10;
    $m100 = $n % 100;
    if ($m10 === 1 && $m100 !== 11) return $one;
    if ($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) return $few;
    return $many;
}

/** "3 ay önce" / "3 months ago" / "vor 3 Monaten" / "3 месяца назад" */
function sl_review_ago($date, $lang) {
    $date = trim((string)$date);
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) return '';
    try {
        $d = new DateTime(substr($date, 0, 10));
        $now = new DateTime('today');
    } catch (Exception $e) {
        return '';
    }
    if ($d > $now) $d = clone $now;
    $diff = $now->diff($d);
    $days = (int)$diff->days;
    $months = (int)$diff->y * 12 + (int)$diff->m;

    if ($days < 1) { $unit = 'today'; $n = 0; }
    elseif ($days < 7) { $unit = 'day'; $n = $days; }
    elseif ($months < 1) { $unit = 'week'; $n = max(1, (int)floor($days / 7)); }
    elseif ($months < 12) { $unit = 'month'; $n = $months; }
    else { $unit = 'year'; $n = (int)$diff->y; }

    switch ($lang) {
        case 'tr':
            $w = ['today' => 'bugün', 'day' => 'gün', 'week' => 'hafta', 'month' => 'ay', 'year' => 'yıl'];
            return $unit === 'today' ? $w['today'] : $n . ' ' . $w[$unit] . ' önce';
        case 'de':
            $w = ['day' => ['Tag', 'Tagen'], 'week' => ['Woche', 'Wochen'], 'month' => ['Monat', 'Monaten'], 'year' => ['Jahr', 'Jahren']];
            return $unit === 'today' ? 'heute' : 'vor ' . $n . ' ' . $w[$unit][$n === 1 ? 0 : 1];
        case 'ru':
            $w = [
                'day' => ['день', 'дня', 'дней'], 'week' => ['неделю', 'недели', 'недель'],
                'month' => ['месяц', 'месяца', 'месяцев'], 'year' => ['год', 'года', 'лет'],
            ];
            if ($unit === 'today') return 'сегодня';
            return $n . ' ' . sl_ru_plural($n, $w[$unit][0], $w[$unit][1], $w[$unit][2]) . ' назад';
        default:
            if ($unit === 'today') return 'today';
            return $n . ' ' . $unit . ($n === 1 ? '' : 's') . ' ago';
    }
}

/** "34 yorum" / "34 reviews" / "34 Bewertungen" / "34 отзыва" */
function sl_review_count_label($n, $lang) {
    $n = (int)$n;
    switch ($lang) {
        case 'tr': return $n . ' yorum';
        case 'de': return $n . ' ' . ($n === 1 ? 'Bewertung' : 'Bewertungen');
        case 'ru': return $n . ' ' . sl_ru_plural($n, 'отзыв', 'отзыва', 'отзывов');
        default:   return $n . ' ' . ($n === 1 ? 'review' : 'reviews');
    }
}

/** 5 → "5,0" (en: "5.0") */
function sl_review_score($r, $lang) {
    return number_format((float)$r, 1, $lang === 'en' ? '.' : ',', '');
}

/** Baş harf avatarının rengi (isimden sabit) */
function sl_review_color($name) {
    $pal = ['#5b4fc4', '#1a73e8', '#0b8043', '#e37400', '#c5221f', '#8430ce', '#00796b', '#5f6368'];
    return $pal[abs(crc32((string)$name)) % count($pal)];
}

function sl_review_initial($name) {
    $name = trim((string)$name);
    if ($name === '') return '?';
    return mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
}

/** n adet yıldız (dolu / boş) */
function sl_review_stars($n) {
    $n = max(0, min(5, (int)$n));
    $h = '';
    for ($i = 1; $i <= 5; $i++) {
        $h .= '<svg class="' . ($i <= $n ? 'on' : 'off') . '" viewBox="0 0 20 20" aria-hidden="true"><use href="#rv-star"/></svg>';
    }
    return $h;
}

function sl_google_g($cls = 'rv-g') {
    return '<svg class="' . $cls . '" viewBox="0 0 48 48" aria-hidden="true">'
        . '<path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/>'
        . '<path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>'
        . '<path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>'
        . '<path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/>'
        . '</svg>';
}

/**
 * Anasayfa yorum alanı. $padTop0: önceki bölüm düz (bantsız) ise üst boşluk kaldırılır.
 * Gösterilecek yorum yoksa hiçbir şey basmaz ve false döner.
 */
function sl_render_reviews($sec, $padTop0 = false) {
    $lang = sl_lang();
    $limit = max(1, min(50, (int)($sec['limit'] ?? 12)));
    $items = sl_reviews_for_lang($lang, $limit);
    if (!$items) return false;

    $rating = (float)str_replace(',', '.', (string)($sec['rating'] ?? ''));
    $rating = $rating > 0 ? min(5, $rating) : 0;
    $count = (int)($sec['count'] ?? 0);
    $url = trim((string)($sec['url'] ?? ''));
    if ($url !== '' && !preg_match('#^https://#i', $url)) $url = '';
    $linkLabel = trim((string)($sec['link_label'] ?? ''));
    $scoreTxt = $rating > 0 ? sl_review_score($rating, $lang) : '';
    ?>
<section class="sec rv-sec<?= $padTop0 ? ' pt-0' : '' ?>" id="yorumlar" aria-labelledby="rvTitle">
  <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><symbol id="rv-star" viewBox="0 0 20 20"><path d="M10 1.4l2.65 5.37 5.93.86-4.29 4.18 1.01 5.9L10 14.92l-5.3 2.79 1.01-5.9L1.42 7.63l5.93-.86z"/></symbol></svg>
  <div class="container">
    <div class="rv-box">
      <div class="rv-head">
        <?php if (trim((string)($sec['eyebrow'] ?? '')) !== ''): ?><div class="eyebrow"><?= e($sec['eyebrow']) ?></div><?php endif; ?>
        <h2 class="sec-t" id="rvTitle"><?= e($sec['title'] ?? '') ?></h2>
        <?php if ($scoreTxt !== ''): ?>
        <div class="rv-sum">
          <?= sl_google_g('rv-g rv-g-lg') ?>
          <b class="rv-score"><?= e($scoreTxt) ?></b>
          <span class="rv-stars-sum" role="img" aria-label="<?= e(sl_t('rv_stars', ['n' => $scoreTxt])) ?>">
            <span class="rv-stars-bg"><?= sl_review_stars(0) ?></span>
            <span class="rv-stars-fg" style="width:<?= e(rtrim(rtrim(number_format($rating / 5 * 100, 2, '.', ''), '0'), '.')) ?>%"><?= sl_review_stars(5) ?></span>
          </span>
          <?php if ($count > 0): ?><span class="rv-count"><?= e(sl_review_count_label($count, $lang)) ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (trim((string)($sec['subtitle'] ?? '')) !== ''): ?><p class="rv-sub"><?= e($sec['subtitle']) ?></p><?php endif; ?>
      </div>
      <div class="rv-nav" hidden>
        <button type="button" class="rv-btn rv-prev" aria-label="<?= sl_e('rv_prev') ?>" disabled><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
        <button type="button" class="rv-btn rv-next" aria-label="<?= sl_e('rv_next') ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
      </div>
      <div class="rv-track" tabindex="0" role="list" aria-label="<?= e($sec['title'] ?? '') ?>">
        <?php foreach ($items as $r):
            $rl = strtolower(trim((string)$r['language_code']));
            $ago = sl_review_ago($r['review_date'], $lang);
            $photo = trim((string)$r['author_photo']);
        ?>
        <article class="rv-card" role="listitem">
          <div class="rv-top">
            <span class="rv-st" role="img" aria-label="<?= e(sl_t('rv_stars', ['n' => (int)$r['rating']])) ?>"><?= sl_review_stars((int)$r['rating']) ?></span>
            <svg class="rv-q" viewBox="0 0 32 32" aria-hidden="true"><path d="M13 7C7.5 9 4 13.4 4 19.2 4 23.3 6.4 26 9.8 26c3 0 5.2-2.2 5.2-5.1 0-2.8-2-4.8-4.7-4.8-.5 0-1 .1-1.4.2.6-3.2 3.1-5.8 6.6-7.2L13 7zm14 0c-5.5 2-9 6.4-9 12.2 0 4.1 2.4 6.8 5.8 6.8 3 0 5.2-2.2 5.2-5.1 0-2.8-2-4.8-4.7-4.8-.5 0-1 .1-1.4.2.6-3.2 3.1-5.8 6.6-7.2L27 7z"/></svg>
          </div>
          <p class="rv-txt"<?= preg_match('/^[a-z]{2,3}$/', $rl) ? ' lang="' . e($rl) . '"' : '' ?>><?= nl2br(e(trim((string)$r['content']))) ?></p>
          <button type="button" class="rv-more" hidden data-more="<?= sl_e('rv_more') ?>" data-less="<?= sl_e('rv_less') ?>"><?= sl_e('rv_more') ?></button>
          <div class="rv-foot">
            <?php if ($photo !== ''): ?>
            <img class="rv-av" src="<?= e(getMediaUrl($photo)) ?>" alt="" loading="lazy" decoding="async" width="44" height="44">
            <?php else: ?>
            <span class="rv-av" style="background:<?= e(sl_review_color($r['author_name'])) ?>" aria-hidden="true"><?= e(sl_review_initial($r['author_name'])) ?></span>
            <?php endif; ?>
            <div class="rv-who">
              <b><?= e($r['author_name']) ?></b>
              <small><?= e($ago !== '' ? $ago . ' · ' . sl_t('rv_google') : sl_t('rv_google')) ?></small>
            </div>
            <?= sl_google_g('rv-g') ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php if ($url !== '' && $linkLabel !== ''): ?>
      <div class="rv-all"><a class="btn btn-ghost" href="<?= e($url) ?>" target="_blank" rel="noopener"><?= sl_google_g('rv-g') ?><?= e($linkLabel) ?></a></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<script src="<?= ASSETS_URL ?>js/sl-yorum.js?v=<?= SL_ASSET_VER ?>" defer></script>
<?php
    return true;
}
