/* Sunline — çerez onayı (Google Consent Mode v2) + WhatsApp / telefon / e-posta tık ölçümü.
 * Yalnız Site Ayarları > SEO'da bir ölçüm kimliği girildiyse yüklenir (includes/footer.php).
 * Varsayılan "reddedildi" header.php'de verilir; burada yalnız ziyaretçinin seçimi uygulanır.
 * Rezervasyon sihirbazına dokunmaz (yalnız sayfadaki bağlantı tıklarını dinler). */
(function () {
  'use strict';
  var KEY = 'sl-consent', YEAR = 31536e6;
  var box = document.getElementById('slCk');
  if (!box) return;
  var ymId = +(box.getAttribute('data-ym') || 0), ymOn = false;

  function gt() { if (typeof window.gtag === 'function') window.gtag.apply(null, arguments); }

  function read() {
    try {
      var p = (localStorage.getItem(KEY) || '').split('.');
      if ((p[0] === 'granted' || p[0] === 'denied') && Date.now() - (+p[1] || 0) < YEAR) return p[0];
    } catch (e) {}
    return '';
  }

  function loadYm() {
    if (!ymId || ymOn) return;
    ymOn = true;
    (function (m, e, t, r, i, k, a) {
      m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); };
      m[i].l = 1 * new Date();
      k = e.createElement(t); a = e.getElementsByTagName(t)[0];
      k.async = 1; k.src = r; a.parentNode.insertBefore(k, a);
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
    window.ym(ymId, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true });
  }

  /* Reddedince daha önce yazılmış ölçüm çerezlerini sil */
  function wipe() {
    var host = location.hostname.replace(/^www\./, '');
    document.cookie.split(';').forEach(function (c) {
      var n = c.split('=')[0].trim();
      if (!/^(_ga|_gid|_gat|_gcl|_ym)/.test(n)) return;
      ['', ';domain=' + host, ';domain=.' + host].forEach(function (d) {
        document.cookie = n + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/' + d;
      });
    });
  }

  function choose(v) {
    var g = v === 'granted' ? 'granted' : 'denied';
    try { localStorage.setItem(KEY, g + '.' + Date.now()); } catch (e) {}
    gt('consent', 'update', { ad_storage: g, ad_user_data: g, ad_personalization: g, analytics_storage: g });
    if (g === 'granted') loadYm(); else wipe();
    box.hidden = true;
  }

  var cur = read();
  if (cur === 'granted') loadYm();
  if (!cur) box.hidden = false;

  box.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-ck]');
    if (b) choose(b.getAttribute('data-ck'));
  });

  document.addEventListener('click', function (ev) {
    if (!ev.target.closest) return;
    if (ev.target.closest('[data-ck-open]')) {
      ev.preventDefault();
      box.hidden = false;
      var f = box.querySelector('[data-ck="granted"]');
      if (f) f.focus();
      return;
    }
    var a = ev.target.closest('a[href]');
    if (!a) return;
    var h = a.getAttribute('href') || '', name = '';
    if (/^tel:/i.test(h)) name = 'phone_click';
    else if (/^(https?:\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)\/|whatsapp:)/i.test(h)) name = 'whatsapp_click';
    else if (/^mailto:/i.test(h)) name = 'email_click';
    if (!name) return;
    var where = a.classList.contains('wa') ? 'float' : a.closest('.ftr') ? 'footer' : a.closest('.hdr') ? 'header' : a.closest('.modal') ? 'modal' : 'page';
    gt('event', name, { link_location: where });
    if (ymOn && typeof window.ym === 'function') window.ym(ymId, 'reachGoal', name);
  }, true);

  /* Rezervasyon tamamlandı (sunline.js done() → 'sl:booking'). Yalnız rezervasyon no + tutar gider; kişisel bilgi gitmez. */
  document.addEventListener('sl:booking', function (ev) {
    var d = ev.detail || {};
    var nos = (d.numbers || []).join(',');
    var cur = String(d.currency || '').toUpperCase();
    if (cur === 'TL') cur = 'TRY';
    var p = { transaction_id: nos, value: +d.value || 0, items: [{ item_name: String(d.destination || 'Transfer'), quantity: 1 }] };
    if (/^[A-Z]{3}$/.test(cur)) p.currency = cur;
    gt('event', 'purchase', p);
    if (ymOn && typeof window.ym === 'function') window.ym(ymId, 'reachGoal', 'booking', { order_price: p.value, currency: cur });
  });
})();
