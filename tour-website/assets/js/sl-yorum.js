/*!
 * Sunline — Google yorumları alanı (includes/sunline/reviews.php)
 * - Oklarla kart kaydırma (telefonda parmakla kaydırma CSS scroll-snap ile)
 * - Taşma yoksa oklar gizli; uçlarda ilgili ok pasif
 * - Uzun yorumlarda "Devamını oku / Daha az göster"
 * Rezervasyon betiğinden (sunline.js) bağımsızdır.
 */
(function () {
  'use strict';

  function init(sec) {
    if (sec.getAttribute('data-rv-ok')) return;
    sec.setAttribute('data-rv-ok', '1');

    var track = sec.querySelector('.rv-track');
    var nav = sec.querySelector('.rv-nav');
    var prev = sec.querySelector('.rv-prev');
    var next = sec.querySelector('.rv-next');
    if (!track) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var target = null; // okla gidilen durak (önceki kaydırma bitmeden tekrar basılırsa kayma olmasın)

    function maxLeft() { return Math.max(0, track.scrollWidth - track.clientWidth); }

    /** Duraklar: her kartın başı (en sona sığanlar) + gerekiyorsa en son konum */
    function stops() {
      var cards = track.querySelectorAll('.rv-card');
      var max = maxLeft();
      var out = [];
      if (!cards.length) return [0];
      var base = cards[0].offsetLeft;
      for (var i = 0; i < cards.length; i++) {
        var x = cards[i].offsetLeft - base;
        if (x > max + 2) break;
        out.push(Math.min(x, max));
      }
      if (out[out.length - 1] < max - 2) out.push(max);
      return out;
    }

    function nearest(list, x) {
      var best = 0;
      for (var i = 1; i < list.length; i++) if (Math.abs(list[i] - x) < Math.abs(list[best] - x)) best = i;
      return best;
    }

    function go(dir) {
      var list = stops();
      var from = target !== null && target < list.length ? target : nearest(list, track.scrollLeft);
      var i = Math.max(0, Math.min(list.length - 1, from + dir));
      target = i;
      track.scrollTo({ left: list[i], behavior: reduce ? 'auto' : 'smooth' });
      if (prev) prev.disabled = i === 0;
      if (next) next.disabled = i === list.length - 1;
    }

    function update() {
      var max = maxLeft();
      if (nav) nav.hidden = !(max > 2);
      if (target !== null) return; // okla gidiliyor; durum go() içinde ayarlandı
      if (prev) prev.disabled = track.scrollLeft <= 2;
      if (next) next.disabled = track.scrollLeft >= max - 2;
    }

    function more() {
      Array.prototype.forEach.call(sec.querySelectorAll('.rv-card'), function (card) {
        var txt = card.querySelector('.rv-txt');
        var btn = card.querySelector('.rv-more');
        if (!txt || !btn || card.classList.contains('open')) return;
        btn.hidden = !(txt.scrollHeight > txt.clientHeight + 2);
      });
    }

    if (prev) prev.addEventListener('click', function () { go(-1); });
    if (next) next.addEventListener('click', function () { go(1); });
    track.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
      else if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    });

    // Ziyaretçi kendisi kaydırırsa (parmak / tekerlek) ok hedefi bırakılır
    ['pointerdown', 'wheel', 'touchstart'].forEach(function (ev) {
      track.addEventListener(ev, function () { target = null; }, { passive: true });
    });

    var t = null, idle = null;
    track.addEventListener('scroll', function () {
      clearTimeout(idle);
      idle = setTimeout(function () {
        // kaydırma durdu: hedefe varıldıysa serbest bırak
        if (target !== null) {
          var list = stops();
          if (Math.abs(track.scrollLeft - list[Math.min(target, list.length - 1)]) <= 2) target = null;
        }
        update();
      }, 160);
      if (t) return;
      t = window.requestAnimationFrame(function () { t = null; update(); });
    }, { passive: true });

    sec.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest('.rv-more') : null;
      if (!btn) return;
      var card = btn.closest('.rv-card');
      var open = card.classList.toggle('open');
      btn.textContent = open ? btn.getAttribute('data-less') : btn.getAttribute('data-more');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      update();
    });

    var r = null;
    window.addEventListener('resize', function () {
      clearTimeout(r);
      r = setTimeout(function () { more(); update(); }, 150);
    });

    more();
    update();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { more(); update(); });
    window.addEventListener('load', function () { more(); update(); });
  }

  function boot() {
    Array.prototype.forEach.call(document.querySelectorAll('.rv-sec'), init);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
