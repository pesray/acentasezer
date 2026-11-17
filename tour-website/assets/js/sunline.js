/*!
 * Sunline VIP Transfer — site betiği
 * - Açık/koyu tema düğmesi, duyuru şeridi, bölge filtreleri, hızlı fiyat formu
 * - Adım adım rezervasyon sihirbazı (Transfer → Yolcular → Araç → Onay)
 *   Fiyatlar sitenin para biriminde; onay adımında "araçta nakit" ödeme para birimi seçilir
 * Veri: window.SL (sayfaya PHP tarafından gömülür)
 */
(function () {
  'use strict';

  var SL = window.SL || {};
  var doc = document;
  var root = doc.documentElement;

  function $(s, r) { return (r || doc).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || doc).querySelectorAll(s)); }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function T(k, v) {
    var s = (SL.t && SL.t[k]) || k;
    if (v) for (var x in v) if (Object.prototype.hasOwnProperty.call(v, x)) s = s.split('{' + x + '}').join(v[x]);
    return s;
  }
  function fmt(n, cur) {
    n = +n || 0;
    var d = Math.abs(n - Math.round(n)) < 0.001 ? 0 : 2;
    var parts = n.toFixed(d).split('.');
    var th = (SL.num && SL.num.th) || '.', dp = (SL.num && SL.num.dp) || ',';
    var num = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, th) + (parts[1] ? dp + parts[1] : '');
    cur = String(cur || 'EUR').toUpperCase();
    var sym = { EUR: '€', USD: '$', GBP: '£', TRY: '₺', TL: '₺', RUB: '₽' }[cur] || cur;
    if (cur === 'USD' || cur === 'GBP') return sym + num;
    if (sym.length > 1) return num + ' ' + sym;
    return num + sym;
  }
  function oldPrice(p) {
    var pct = +SL.disc || 0;
    if (pct <= 0 || p <= 0) return 0;
    var raw = Math.round(p * (1 + pct / 100) * 1e6) / 1e6;
    var st = p < 100 ? 5 : (p < 1000 ? 10 : (p < 10000 ? 100 : 500));
    var o = Math.ceil(raw / st) * st;
    if (o <= p) o = p + st;
    return o;
  }

  /* ------------------------------------------------------------------
   * Açık / koyu tema düğmesi
   * ------------------------------------------------------------------ */
  function effectiveTheme() {
    var a = root.getAttribute('data-theme');
    if (a === 'light' || a === 'dark') return a;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  function syncThemeColor(mode) {
    if (!SL.themeBg) return;
    $$('meta[name="theme-color"]').forEach(function (m) { m.setAttribute('content', SL.themeBg[mode] || m.getAttribute('content')); });
  }
  $$('.thm').forEach(function (b) {
    b.addEventListener('click', function () {
      var next = effectiveTheme() === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('sl-theme', next); } catch (e) {}
      syncThemeColor(next);
    });
  });
  if (root.getAttribute('data-theme')) syncThemeColor(root.getAttribute('data-theme'));

  /* ------------------------------------------------------------------
   * Duyuru şeridi
   * ------------------------------------------------------------------ */
  var promo = $('#promo');
  if (promo) {
    var px = $('.promo-x', promo);
    if (px) px.addEventListener('click', function () {
      promo.classList.add('is-hidden');
      try { sessionStorage.setItem('sl-promo', '1'); } catch (e) {}
    });
  }

  /* ------------------------------------------------------------------
   * Bölge filtreleri
   * ------------------------------------------------------------------ */
  $$('.flt').forEach(function (bar) {
    var scope = bar.closest('section') || doc;
    bar.addEventListener('click', function (e) {
      var b = e.target.closest('button[data-f]');
      if (!b) return;
      $$('button', bar).forEach(function (x) { x.classList.toggle('on', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      var f = b.getAttribute('data-f');
      $$('.rc[data-r]', scope).forEach(function (card) {
        card.hidden = !(f === 'all' || card.getAttribute('data-r') === f);
      });
    });
  });

  /* ------------------------------------------------------------------
   * Hızlı fiyat formu (NEREDEN / NEREYE / YOLCU)
   * ------------------------------------------------------------------ */
  var qFrom = $('#qFrom'), qTo = $('#qTo'), qPax = $('#qPax'), qBtn = $('#qBtn'), qNote = $('#qNote');
  if (qFrom && qTo && qBtn) {
    var noteSpan = qNote ? $('span', qNote) : null;
    var noteText = noteSpan ? noteSpan.textContent : '';
    var setNote = function (msg, err) {
      if (!noteSpan) return;
      noteSpan.textContent = msg || noteText;
      qNote.classList.toggle('is-err', !!err);
      qNote.style.color = err ? 'var(--red)' : '';
    };
    var pair = function (changed, other) {
      if (changed.value && changed.value !== 'ayt' && (!other.value || other.value !== 'ayt')) other.value = 'ayt';
      else if (changed.value === 'ayt' && other.value === 'ayt') other.value = '';
      setNote('');
    };
    qFrom.addEventListener('change', function () { pair(qFrom, qTo); });
    qTo.addEventListener('change', function () { pair(qTo, qFrom); });
    qBtn.addEventListener('click', function () {
      var f = qFrom.value, t = qTo.value;
      if (!f || !t || f === t) { setNote(T('pick'), true); return; }
      if (f !== 'ayt' && t !== 'ayt') { setNote(T('pick_airport'), true); return; }
      setNote('');
      var arr = f === 'ayt';
      openBooking(arr ? t : f, { pax: qPax ? parseInt(qPax.value, 10) || 1 : 1, trip: arr ? 'rt' : 'ow', dir: arr ? 'arr' : 'dep' });
    });
  }

  /* ------------------------------------------------------------------
   * Rezervasyon tetikleyicileri
   * ------------------------------------------------------------------ */
  doc.addEventListener('click', function (e) {
    var el = e.target.closest('[data-book]');
    if (!el) return;
    var id = el.getAttribute('data-book');
    if (!SL.dests || !SL.dests[id]) return; // veri yoksa bağlantı normal çalışsın
    e.preventDefault();
    openBooking(id, { vehicle: el.getAttribute('data-vehicle') || '' });
  });

  /* ------------------------------------------------------------------
   * Rezervasyon sihirbazı
   * ------------------------------------------------------------------ */
  var modalEl = $('#bm'), modal = null, S = null;
  var BOOST_MAX = 20; // yükseltici için teknik üst sınır (sunucuyla aynı)

  function vehiclesFor(d) {
    var ids = Object.keys(d.p || {}).filter(function (v) { return SL.veh && SL.veh[v]; });
    ids.sort(function (a, b) { return d.p[a][0] - d.p[b][0]; });
    return ids;
  }
  function legPrice(vid) { return S && S.d.p[vid] ? +S.d.p[vid][0] : 0; }
  function curOf(vid) { return S && S.d.p[vid] ? S.d.p[vid][1] : 'EUR'; }
  function mult() { return S.trip === 'rt' ? 2 : 1; }
  // Bu bölge için fiyatı olan araçların en büyük kapasitesi (yolcu adımındaki sınır)
  function capMax() { var m = 1; vehiclesFor(S.d).forEach(function (v) { m = Math.max(m, SL.veh[v].cap); }); return m; }
  function seatMax() { var m = 0; vehiclesFor(S.d).forEach(function (v) { m = Math.max(m, SL.veh[v].cs); }); return m; }
  // Araç, girilen yolcu ve çocuk koltuğu sayısına uygun mu?
  function fits(vid) { var v = SL.veh[vid]; return !!v && v.cap >= S.a + S.c && v.cs >= S.s; }
  // Seçili araç yoksa: yolcu sayısına uyan en ucuz aracın fiyatı
  function cheapestFit() {
    var ids = vehiclesFor(S.d);
    for (var i = 0; i < ids.length; i++) if (fits(ids[i])) return { p: legPrice(ids[i]), c: curOf(ids[i]), v: ids[i] };
    return ids.length ? { p: legPrice(ids[0]), c: curOf(ids[0]), v: ids[0] } : null;
  }
  function paxText() {
    return S.a + ' ' + T('adults') + (S.c ? ', ' + S.c + ' ' + T('children') : '') +
      (S.s ? ', ' + S.s + ' ' + T('child_seat') : '') + (S.b ? ', ' + S.b + ' ' + T('booster') : '');
  }

  /* --- Para birimi ve koltuk ücretleri (sitede tek para birimi, ödemede seçim) --- */
  function curDisp() { return (S && S.d.cur) || SL.cur || 'EUR'; }
  function priceIn(cur, vid) {
    var m = S.d.pc && S.d.pc[cur];
    if (m && m[vid] != null) return +m[vid];
    var p = S.d.p && S.d.p[vid];
    return p && p[1] === cur ? +p[0] : 0;
  }
  function curVid() { var c; return S.v || ((c = cheapestFit()) ? c.v : ''); }
  function niceRound(x) { return x < 100 ? Math.round(x) : Math.round(x / 10) * 10; }
  // Ücret o para biriminde girilmemişse, girilmiş olandan aracın fiyat oranıyla çevrilir (sunucuyla aynı)
  function feeIn(kind, cur, vid) {
    var map = (SL.fees && SL.fees[kind]) || {};
    if (map[cur] != null) return +map[cur];
    var keys = Object.keys(map);
    if (!keys.length) return 0;
    var from = map[SL.cur] != null ? SL.cur : keys[0];
    var pTo = priceIn(cur, vid), pFrom = priceIn(from, vid);
    if (pTo <= 0 || pFrom <= 0) return 0;
    return niceRound(map[from] * pTo / pFrom);
  }
  // Bir yön için ek koltuk ücreti; toplam "free" adet ücretsiz (önce pahalı olana)
  function extrasIn(cur, vid) {
    var fs = feeIn('seat', cur, vid), fb = feeIn('booster', cur, vid), free = +((SL.fees && SL.fees.free) || 0);
    var s = S.s, b = S.b, freeS, freeB;
    if (fs >= fb) { freeS = Math.min(s, free); freeB = Math.min(b, free - freeS); }
    else { freeB = Math.min(b, free); freeS = Math.min(s, free - freeB); }
    return { amt: (s - freeS) * fs + (b - freeB) * fb, paidS: s - freeS, paidB: b - freeB, fs: fs, fb: fb };
  }
  function perLeg() { return !(SL.fees && SL.fees.perLeg === false); }
  function extrasTotal(cur, vid) {
    var ex = extrasIn(cur, vid).amt;
    return perLeg() ? ex * mult() : ex;
  }
  function totalIn(cur, vid) {
    var p = priceIn(cur, vid);
    return p ? p * mult() + extrasTotal(cur, vid) : 0;
  }
  // Ödemede sunulacak para birimleri: ayarlarda açık olanlardan bu araç için fiyatı girilmiş olanlar
  function payCurs(vid) {
    var list = (SL.payCurs || []).filter(function (c) { return priceIn(c, vid) > 0; });
    var d = curDisp();
    if (priceIn(d, vid) > 0 && list.indexOf(d) < 0) list.unshift(d);
    return list;
  }
  // Seçili ödeme para birimi (varsayılan: sitenin para birimi, yoksa bölgenin gösterim para birimi)
  function ensurePay() {
    if (!S.v) return curDisp();
    var list = payCurs(S.v);
    if (list.indexOf(S.pay) < 0) {
      S.pay = list.indexOf(SL.cur) >= 0 ? SL.cur : (list.indexOf(curDisp()) >= 0 ? curDisp() : (list[0] || curDisp()));
    }
    return S.pay;
  }
  function upper(s) {
    try { return String(s).toLocaleUpperCase(SL.lang === 'tr' ? 'tr-TR' : undefined); } catch (e) { return String(s).toUpperCase(); }
  }
  // Yazarken büyük harfe çevir (imleç yerinde kalsın)
  function upInput(inp) {
    var v = inp.value, u = upper(v);
    if (v === u) return;
    var s = inp.selectionStart, e = inp.selectionEnd;
    inp.value = u;
    if (u.length === v.length && s != null) { try { inp.setSelectionRange(s, e); } catch (x) {} }
  }
  // Uçuş no: büyük harf, yalnız harf/rakam/boşluk/tire
  function flightInput(inp) {
    var v = inp.value, u = v.toUpperCase().replace(/[^A-Z0-9 -]/g, '');
    if (v === u) return;
    var s = inp.selectionStart, d = v.length - u.length;
    inp.value = u;
    if (s != null) { var p = Math.max(0, s - d); try { inp.setSelectionRange(p, p); } catch (x) {} }
  }
  function flightOk(s) {
    var c = String(s || '').toUpperCase().replace(/[\s-]+/g, '');
    return /^(?=.*\d)[A-Z0-9]{3,8}$/.test(c);
  }

  /* --- (i) bilgi balonu --- */
  function placeTip(col, t) {
    var body = $('#bmBody'), b = $('.ib', col);
    if (!body) return;
    t.style.left = '0px';
    var br = body.getBoundingClientRect(), cr = col.getBoundingClientRect();
    var w = Math.max(160, Math.min(300, br.width - 32)), left = 0;
    t.style.width = w + 'px';
    if (cr.left + w > br.right - 16) left = br.right - 16 - w - cr.left;
    if (cr.left + left < br.left + 16) left = br.left + 16 - cr.left;
    t.style.left = left + 'px';
    if (b) { var bx = b.getBoundingClientRect(); t.style.setProperty('--ax', Math.round(bx.left + bx.width / 2 - cr.left - left) + 'px'); }
  }
  function tipShow(col, on, pin) {
    var t = $('.ib-tip', col), b = $('.ib', col);
    if (!t || !b) return;
    if (on) {
      $$('.has-tip.tip-on').forEach(function (o) { if (o !== col) tipShow(o, false); });
      col.classList.add('tip-on');
      if (pin) col.classList.add('tip-pin');
      placeTip(col, t);
    } else {
      col.classList.remove('tip-on');
      col.classList.remove('tip-pin');
    }
    b.setAttribute('aria-expanded', on ? 'true' : 'false');
  }
  doc.addEventListener('click', function (e) {
    if (e.target.closest && (e.target.closest('.ib') || e.target.closest('.ib-tip'))) return;
    $$('.has-tip.tip-on').forEach(function (c) { tipShow(c, false); });
  });
  function tripText() {
    return S.trip === 'rt' ? T('rt') : (T('ow') + ' · ' + (S.dir === 'arr' ? T('dir_arr') : T('dir_dep')));
  }

  function openBooking(destId, o) {
    if (!modalEl || !window.bootstrap || !SL.dests) return;
    var d = SL.dests[destId];
    if (!d) return;
    o = o || {};
    S = {
      id: String(destId), d: d, v: '', trip: o.trip === 'ow' ? 'ow' : 'rt', dir: o.dir === 'dep' ? 'dep' : 'arr',
      step: 1, a: Math.max(1, parseInt(o.pax, 10) || 1), c: 0, s: 0, b: 0, pay: '', an: [], cn: [], a1auto: true, busy: false, done: false,
      t0: Date.now()
    };
    if (o.vehicle && d.p[o.vehicle] && SL.veh[o.vehicle]) S.v = String(o.vehicle);
    build();
    modal = modal || new window.bootstrap.Modal(modalEl);
    modal.show();
  }
  window.slOpenBooking = openBooking;

  // Adım sırası: 1 Transfer · 2 Yolcular · 3 Araç · 4 Onay
  // (çeviri anahtarları anlamına göre: step2 = Transfer, step3 = Yolcular, step1 = Araç, step4 = Onay)
  function stepsHtml() {
    var keys = ['step2', 'step3', 'step1', 'step4'];
    var h = '<ol class="wz-steps" aria-label="steps">';
    for (var i = 1; i <= 4; i++) h += '<li data-s="' + i + '"><span>' + i + '</span>' + esc(T(keys[i - 1])) + '</li>';
    return h + '</ol>';
  }

  function vehicleHtml(vid) {
    var v = SL.veh[vid];
    var img = '<i class="bi bi-car-front"></i>' + (v.img ? '<img src="' + esc(v.img) + '" alt="' + esc(v.n) + '" loading="lazy" onerror="this.remove()">' : '');
    var spec = '<span><i class="bi bi-people-fill"></i>' + esc(T('cap_pax', { n: v.cap })) + '</span>' +
      '<span><i class="bi bi-briefcase-fill"></i>' + esc(T('cap_lug', { n: v.lug })) + '</span>' +
      (v.cs ? '<span><i class="bi bi-person-arms-up"></i>' + esc(T('cap_seat', { n: v.cs })) + '</span>' : '');
    var sv = (v.sv && v.sv.length) ? '<span class="vh-sv d-block">' + esc(v.sv.join(' · ')) + '</span>' : '';
    return '<button type="button" class="vh" data-v="' + esc(vid) + '" aria-pressed="false">' +
      '<span class="vh-img">' + img + '</span>' +
      '<span class="vh-i d-block"><span class="vh-n d-block">' + esc(v.n) + '</span><span class="vh-s">' + spec + '</span>' + sv +
      '<span class="vh-warn" hidden></span></span>' +
      '<span class="vh-p"><small class="vh-tl"></small><b class="vh-now"></b><del class="vh-old"></del></span></button>';
  }

  // tip: etiketin yanında (i) bilgi balonu (kredi kartı CVV bilgisindeki gibi)
  function field(id, label, input, col, req, hint, tip) {
    var lbl = '<label class="' + (tip ? 'd-inline-block' : 'd-block') + '" for="' + id + '">' + esc(label) +
      (req ? '<span class="req">*</span>' : ' <small class="opacity-75">' + esc(T('optional')) + '</small>') + '</label>';
    if (tip) {
      lbl += '<button type="button" class="ib" aria-label="' + esc(T('info') + ': ' + label) + '" aria-expanded="false" aria-controls="' + id + 'Tip">i</button>' +
        '<span class="ib-tip" id="' + id + 'Tip" role="tooltip">' + esc(tip) + '</span>';
    }
    return '<div class="' + col + (tip ? ' has-tip' : '') + '">' + lbl + input + (hint ? '<div class="hint">' + esc(hint) + '</div>' : '') + '</div>';
  }

  function build() {
    var d = S.d, ids = vehiclesFor(d);
    $('#bmT').textContent = T('airport_short') + ' ⇄ ' + d.n;
    var body = $('#bmBody'), foot = $('#bmFoot');

    if (!ids.length) {
      var wa = SL.wa ? 'https://wa.me/' + SL.wa + '?text=' + encodeURIComponent(d.t) : '';
      body.innerHTML = '<div class="ok"><i class="bi bi-chat-dots"></i><h4>' + esc(d.t) + '</h4><p>' + esc(T('no_price')) + '</p>' +
        (wa ? '<a class="btn btn-wa" target="_blank" rel="noopener" href="' + esc(wa) + '"><i class="bi bi-whatsapp me-2"></i>' + esc(T('wa_write')) + '</a>' : '') + '</div>';
      foot.innerHTML = '';
      foot.hidden = true;
      return;
    }
    foot.hidden = false;

    var today = SL.today || '';
    var place = esc(d.n), ap = esc(T('airport_short'));

    // 1. adım: transfer tipi + yön (en üstte) ve uçuş/otel bilgileri
    var tripHtml = '<div class="wz-h">' + esc(T('trip')) + '</div>' +
      '<div class="trip">' +
      '<input type="radio" name="tt" id="tt1" value="rt"><label for="tt1">' + esc(T('rt')) + '<b id="ttRt"></b><small id="ttRtO"></small></label>' +
      '<input type="radio" name="tt" id="tt2" value="ow"><label for="tt2">' + esc(T('ow')) + '<b id="ttOw"></b><small id="ttOwO"></small></label>' +
      '</div>' +
      '<div class="seg" id="dirSeg" role="radiogroup" aria-label="' + esc(T('direction')) + '">' +
      '<input type="radio" name="dir" id="dr1" value="arr"><label for="dr1"><i class="bi bi-airplane-engines me-1"></i>' + esc(T('dir_arr')) + '</label>' +
      '<input type="radio" name="dir" id="dr2" value="dep"><label for="dr2"><i class="bi bi-building me-1"></i>' + esc(T('dir_dep')) + '</label>' +
      '</div>';

    // 3. adım: yolcu sayısına göre araç seçimi
    var p3 = '<div class="wz-pane" data-p="3">' +
      '<div class="wz-h">' + esc(T('choose_vehicle')) + '</div>' +
      '<div class="vh-info" id="vhInfo"></div>' +
      '<div class="vh-list" id="vhList">' + ids.map(vehicleHtml).join('') + '</div>' +
      '</div>';

    var legA = '<div class="leg" id="legA"><div class="wz-sub"><i class="bi bi-airplane-engines"></i>' + esc(T('arrival_title')) + ' · ' + ap + ' → ' + place + '</div><div class="row g-3">' +
      field('aDate', T('flight_date'), '<input class="form-control" type="date" id="aDate" min="' + esc(today) + '" data-req="1">', 'col-12 col-sm-6 col-lg-4', true) +
      field('aTime', T('arr_time'), '<input class="form-control" type="time" id="aTime" data-req="1">', 'col-6 col-lg-4', true) +
      field('aFl', T('flight'), '<input class="form-control upc" id="aFl" placeholder="' + esc(T('flight_ph')) + '" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="12" data-req="1" data-flight="1" aria-describedby="aFlTip">', 'col-6 col-lg-4', true, '', T('flight_info')) +
      field('aHotel', T('hotel'), '<input class="form-control" id="aHotel" placeholder="' + esc(T('hotel_ph')) + '" data-req="1" maxlength="250">', 'col-12', true) +
      '</div></div>';
    var legD = '<div class="leg" id="legD"><div class="wz-sub"><i class="bi bi-airplane"></i>' + esc(T('departure_title')) + ' · ' + place + ' → ' + ap + '</div><div class="row g-3">' +
      field('dDate', T('flight_date'), '<input class="form-control" type="date" id="dDate" min="' + esc(today) + '" data-req="1">', 'col-12 col-sm-6 col-lg-4', true) +
      field('dTime', T('dep_time'), '<input class="form-control" type="time" id="dTime" data-req="1">', 'col-6 col-lg-4', true) +
      field('dFl', T('flight'), '<input class="form-control upc" id="dFl" placeholder="' + esc(T('flight_ph')) + '" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="12" data-req="1" data-flight="1" aria-describedby="dFlTip">', 'col-6 col-lg-4', true, '', T('flight_info_dep')) +
      field('dPick', T('pickup_time'), '<input class="form-control" type="time" id="dPick">', 'col-12 col-sm-6 col-lg-4', false, T('pickup_hint')) +
      field('dHotel', T('hotel'), '<input class="form-control" id="dHotel" placeholder="' + esc(T('hotel_ph')) + '" data-req="1" maxlength="250">', 'col-12 col-lg-8', true) +
      '</div></div>';
    var p1 = '<div class="wz-pane" data-p="1">' + tripHtml + '<div class="mt-4">' + legA + legD + '</div></div>';

    var stepper = function (key, label, sub) {
      return '<div class="stp" data-k="' + key + '"><span>' + esc(label) + (sub ? '<small>' + esc(sub) + '</small>' : '') + '</span>' +
        '<span class="stp-c"><button type="button" data-op="-" aria-label="-">−</button><output aria-live="polite">0</output><button type="button" data-op="+" aria-label="+">+</button></span></div>';
    };
    // 2. adım: yolcu sayıları, iletişim, yolcu isimleri
    var p2 = '<div class="wz-pane" data-p="2">' +
      '<div class="wz-h">' + esc(T('passengers')) + '</div>' +
      '<div class="stp-row">' + stepper('a', T('adults')) + stepper('c', T('children')) + stepper('s', T('child_seat')) + stepper('b', T('booster')) + '</div>' +
      '<div class="seat-note" id="seatNote" hidden></div>' +
      '<div class="wz-h mt-4">' + esc(T('contact_title')) + '</div>' +
      '<div class="row g-3">' +
      field('wName', T('name'), '<input class="form-control" id="wName" autocomplete="name" data-req="1" maxlength="120">', 'col-12 col-md-6', true) +
      field('wPhone', T('phone'), '<input class="form-control" id="wPhone" type="tel" inputmode="tel" autocomplete="tel" placeholder="' + esc(T('phone_ph')) + '" data-req="1" maxlength="40">', 'col-12 col-md-6', true) +
      field('wEmail', T('email'), '<input class="form-control" id="wEmail" type="email" inputmode="email" autocomplete="email" maxlength="150">', 'col-12', false) +
      '</div>' +
      '<div class="wz-h mt-4">' + esc(T('pax_title')) + '</div>' +
      '<div class="hint mb-2" style="font-size:.8rem">' + esc(T('pax_hint')) + '</div>' +
      '<div class="pax-list" id="paxList"></div>' +
      '</div>';

    var termsText = SL.terms && SL.terms.text ? esc(SL.terms.text) : '';
    var p4 = '<div class="wz-pane" data-p="4">' +
      '<div class="wz-h">' + esc(T('summary')) + '</div>' +
      '<dl class="sum" id="sumBox"></dl>' +
      '<div class="pay" id="payBox"></div>' +
      (SL.alert ? '<div class="wz-alert"><i class="bi bi-info-circle-fill"></i><span>' + esc(SL.alert) + '</span></div>' : '') +
      '<div class="mt-3"><label class="d-block" for="wNotes">' + esc(T('notes')) + ' <small class="opacity-75">' + esc(T('optional')) + '</small></label>' +
      '<textarea class="form-control" id="wNotes" rows="2" maxlength="1000" placeholder="' + esc(T('notes_ph')) + '"></textarea></div>' +
      '<div class="wz-terms"><input type="checkbox" id="wTerms"><label for="wTerms">' + esc((SL.terms && SL.terms.check) || '') +
      (termsText ? ' <a href="#" id="wTermsLink">' + esc((SL.terms && SL.terms.title) || '') + '</a>' : '') + '</label></div>' +
      (termsText ? '<div class="wz-termtext" id="wTermText" hidden>' + termsText + '</div>' : '') +
      '<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden"><label for="wWeb">Website</label><input type="text" id="wWeb" tabindex="-1" autocomplete="off"></div>' +
      '</div>';

    body.innerHTML = '<form id="wzForm" novalidate>' + stepsHtml() + p1 + p2 + p3 + p4 + '<div class="wz-pane" data-p="5" id="wzDone"></div><div class="wz-err" id="wzErr" role="alert"></div></form>';
    foot.innerHTML = '<div class="wz-tot"><small>' + esc(T('total')) + ' · ' + esc(T('cash')) + '</small><b id="wTot">—</b></div>' +
      '<div class="wz-btns"><button type="button" class="btn btn-ghost" id="wBack">' + esc(T('back')) + '</button><button type="button" class="btn btn-cta" id="wNext">' + esc(T('next')) + '</button></div>';

    clampPax();
    bind();
    go(1);
  }

  function bind() {
    var body = $('#bmBody');
    $('#tt' + (S.trip === 'rt' ? '1' : '2')).checked = true;
    $('#dr' + (S.dir === 'arr' ? '1' : '2')).checked = true;

    $$('input[name="tt"]', body).forEach(function (r) { r.addEventListener('change', function () { S.trip = r.value; refresh(); }); });
    $$('input[name="dir"]', body).forEach(function (r) { r.addEventListener('change', function () { S.dir = r.value; refresh(); }); });

    $('#vhList').addEventListener('click', function (e) {
      var b = e.target.closest('.vh');
      if (!b || b.classList.contains('off')) return;
      S.v = b.getAttribute('data-v');
      refresh();
      clearErr();
    });

    $$('.stp', body).forEach(function (st) {
      st.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-op]');
        if (!b || b.disabled) return;
        var k = st.getAttribute('data-k'), plus = b.getAttribute('data-op') === '+';
        collectNames();
        S[k] = Math.max(0, S[k] + (plus ? 1 : -1));
        clampPax();
        refresh();
      });
    });

    // 1. yetişkin = iletişim adı (büyük harfle)
    var name = $('#wName');
    name.addEventListener('input', function () {
      if (S.a1auto) {
        var f = $('#paxList input[data-t="a"][data-i="0"]'), u = upper(name.value);
        if (f) f.value = u;
        S.an[0] = u;
      }
    });
    // Yolcu isimleri yazarken büyük harfe çevrilir
    var pl = $('#paxList');
    var onName = function (e) {
      var inp = e.target;
      if (!inp.matches || !inp.matches('input[data-t]')) return;
      if (!e.isComposing) upInput(inp);
      if (inp.getAttribute('data-t') === 'a' && inp.getAttribute('data-i') === '0') S.a1auto = inp.value === upper($('#wName').value);
    };
    pl.addEventListener('input', onName);
    pl.addEventListener('compositionend', onName);

    // Uçuş numarası: büyük harf
    $$('[data-flight]', body).forEach(function (inp) {
      inp.addEventListener('input', function (e) { if (!e.isComposing) flightInput(inp); });
      inp.addEventListener('compositionend', function () { flightInput(inp); });
      inp.addEventListener('blur', function () { inp.value = inp.value.trim().replace(/\s{2,}/g, ' '); });
    });

    // (i) bilgi balonları: üzerine gelince / dokununca
    $$('.has-tip', body).forEach(function (col) {
      var b = $('.ib', col);
      if (!b) return;
      b.addEventListener('click', function (e) { e.preventDefault(); tipShow(col, !col.classList.contains('tip-pin'), true); });
      b.addEventListener('mouseenter', function () { if (!col.classList.contains('tip-on')) tipShow(col, true); });
      b.addEventListener('mouseleave', function () { if (!col.classList.contains('tip-pin')) tipShow(col, false); });
      b.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && col.classList.contains('tip-on')) { e.stopPropagation(); tipShow(col, false); }
      });
    });

    // Ödeme para birimi (4. adım)
    $('#payBox').addEventListener('change', function (e) {
      if (e.target.name !== 'pc') return;
      S.pay = e.target.value;
      refresh();
    });

    var aHotel = $('#aHotel'), dHotel = $('#dHotel');
    aHotel.addEventListener('change', function () { if (!dHotel.value) dHotel.value = aHotel.value; });

    var tl = $('#wTermsLink');
    if (tl) tl.addEventListener('click', function (e) { e.preventDefault(); var t = $('#wTermText'); if (t) t.hidden = !t.hidden; });

    $('#wBack').addEventListener('click', function () { if (S.step > 1) go(S.step - 1); });
    $('#wNext').addEventListener('click', next);
    $('#wzForm').addEventListener('submit', function (e) { e.preventDefault(); next(); });
    body.addEventListener('input', function (e) { if (e.target.classList.contains('is-invalid')) e.target.classList.remove('is-invalid'); });
  }

  // Yolcu adımı araç seçiminden önce: sınır, bu bölgedeki en büyük aracın kapasitesi
  function clampPax() {
    var cap = capMax();
    S.a = Math.min(Math.max(1, S.a), cap);
    S.c = Math.min(Math.max(0, S.c), Math.max(0, cap - S.a));
    S.s = Math.min(Math.max(0, S.s), Math.min(S.c, seatMax()));
    S.b = Math.min(Math.max(0, S.b), BOOST_MAX); // yükseltici: araç/çocuk sayısıyla sınırlı değil
  }

  function collectNames() {
    $$('#paxList input[data-t="a"]').forEach(function (i) { S.an[+i.getAttribute('data-i')] = i.value; });
    $$('#paxList input[data-t="c"]').forEach(function (i) { S.cn[+i.getAttribute('data-i')] = i.value; });
  }

  function renderNames() {
    var list = $('#paxList');
    if (!list) return;
    var nm = $('#wName') ? $('#wName').value : '';
    if (S.a1auto) S.an[0] = upper(nm);
    var h = '', attrs = ' autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="120"';
    for (var i = 0; i < S.a; i++) {
      h += '<div class="pax-row"><span class="badge-n"><i class="bi bi-person-fill"></i>' + esc(T('adult_n', { n: i + 1 })) + '</span>' +
        '<input class="form-control upc" data-t="a" data-i="' + i + '" value="' + esc(upper(S.an[i] || '')) + '" placeholder="' + esc(T('name')) + '"' + attrs + ' aria-label="' + esc(T('adult_n', { n: i + 1 })) + '"></div>';
    }
    for (var j = 0; j < S.c; j++) {
      h += '<div class="pax-row"><span class="badge-n"><i class="bi bi-person-arms-up"></i>' + esc(T('child_n', { n: j + 1 })) + '</span>' +
        '<input class="form-control upc" data-t="c" data-i="' + j + '" value="' + esc(upper(S.cn[j] || '')) + '" placeholder="' + esc(T('name')) + ' ' + esc(T('optional')) + '"' + attrs + ' aria-label="' + esc(T('child_n', { n: j + 1 })) + '"></div>';
    }
    list.innerHTML = h;
  }

  function refresh() {
    var body = $('#bmBody');
    if (!body || !S) return;
    if ($('#paxList')) collectNames();
    // Yolcu sayısı değiştiyse ve seçili araç artık yetmiyorsa seçimi kaldır
    if (S.v && !fits(S.v)) S.v = '';
    var m = mult();
    // Transfer tipi kartları: seçili aracın (yoksa yolcu sayısına uyan en ucuz aracın) fiyatı
    var base = S.v ? { p: legPrice(S.v), c: curOf(S.v) } : cheapestFit();
    if (base) {
      var rt = base.p * 2, ow = base.p, oRt = oldPrice(rt), oOw = oldPrice(ow);
      $('#ttRt').innerHTML = esc(fmt(rt, base.c)) + (oRt ? '<del>' + esc(fmt(oRt, base.c)) + '</del>' : '');
      $('#ttOw').innerHTML = esc(fmt(ow, base.c)) + (oOw ? '<del>' + esc(fmt(oOw, base.c)) + '</del>' : '');
    }
    $('#dirSeg').hidden = S.trip !== 'ow';

    // Araç listesi (3. adım): yolcu / çocuk koltuğu sayısına uymayanlar pasif
    var paxNeed = S.a + S.c;
    var info = $('#vhInfo');
    if (info) info.innerHTML = '<i class="bi bi-people-fill"></i>' + esc(T('vh_for', { pax: paxText(), trip: tripText() }));
    $$('.vh', body).forEach(function (b) {
      var vid = b.getAttribute('data-v'), v = SL.veh[vid], p = legPrice(vid) * m, c = curOf(vid), o = oldPrice(p);
      var small = v.cap < paxNeed, fewSeats = !small && v.cs < S.s, off = small || fewSeats;
      var on = vid === S.v && !off;
      b.classList.toggle('on', on);
      b.classList.toggle('off', off);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      b.setAttribute('aria-disabled', off ? 'true' : 'false');
      var warn = $('.vh-warn', b);
      warn.textContent = small ? T('too_small') : (fewSeats ? T('too_few_seats', { n: v.cs }) : '');
      warn.hidden = !off;
      $('.vh-tl', b).textContent = S.trip === 'rt' ? T('rt') : T('ow');
      $('.vh-now', b).textContent = fmt(p, c);
      var od = $('.vh-old', b);
      od.textContent = o ? fmt(o, c) : '';
      od.hidden = !o;
    });

    // Transfer ayakları
    var needA = S.trip === 'rt' || S.dir === 'arr', needD = S.trip === 'rt' || S.dir === 'dep';
    $('#legA').hidden = !needA;
    $('#legD').hidden = !needD;

    // Stepper'lar (sınır: bu bölgedeki en büyük araç; yükseltici sınırsız)
    var cap = capMax(), scap = Math.min(S.c, seatMax()), shown = 0;
    $$('.stp', body).forEach(function (st) {
      var k = st.getAttribute('data-k'), val = S[k];
      $('output', st).textContent = val;
      var min = k === 'a' ? 1 : 0;
      var max = k === 'a' ? cap - S.c : (k === 'c' ? cap - S.a : (k === 's' ? scap : BOOST_MAX));
      $('[data-op="-"]', st).disabled = val <= min;
      $('[data-op="+"]', st).disabled = val >= max;
      if (k === 's') st.hidden = seatMax() < 1;
      if (!st.hidden) shown++;
    });
    var sr = $('.stp-row', body);
    if (sr) sr.style.setProperty('--n', shown);
    // Çocuk koltuğu / yükseltici ücret bilgisi (gösterim para biriminde)
    var sn = $('#seatNote');
    if (sn) {
      var cv = curVid(), cd = curDisp();
      var fsd = cv ? feeIn('seat', cd, cv) : 0, fbd = cv ? feeIn('booster', cd, cv) : 0;
      var note = '';
      if (fsd > 0 || fbd > 0) {
        note = (+((SL.fees && SL.fees.free) || 0) > 0 ? T('seat_free_note') + ' ' : '') +
          T('seat_fee_note', { cs: fsd > 0 ? fmt(fsd, cd) : T('free'), b: fbd > 0 ? fmt(fbd, cd) : T('free'), per: perLeg() ? T('per_leg') : T('per_booking') });
      }
      sn.innerHTML = note ? '<i class="bi bi-info-circle"></i><span>' + esc(note) + '</span>' : '';
      sn.hidden = !note;
    }
    renderNames();

    // Toplam (araç + ek koltuk); 4. adımda seçilen ödeme para biriminde
    var tot = $('#wTot'), tv = curVid();
    if (tv) {
      var tc = S.step >= 4 ? ensurePay() : curDisp();
      var vp = priceIn(tc, tv) * m, ex = extrasTotal(tc, tv), to = S.v ? oldPrice(vp) : 0;
      tot.innerHTML = esc(fmt(vp + ex, tc)) + (to ? '<del>' + esc(fmt(to + ex, tc)) + '</del>' : '');
    }
    if (S.step === 4) summary();
  }

  function go(n) {
    S.step = n;
    clearErr();
    $$('.wz-pane', $('#bmBody')).forEach(function (p) { p.classList.toggle('on', +p.getAttribute('data-p') === n); });
    $$('.wz-steps li', $('#bmBody')).forEach(function (li) {
      var s = +li.getAttribute('data-s');
      li.classList.toggle('on', s === n);
      li.classList.toggle('done', s < n);
    });
    var back = $('#wBack'), nx = $('#wNext');
    back.hidden = n === 1 || n === 5;
    nx.textContent = n === 4 ? T('send') : T('next');
    if (n === 5) { $('#bmFoot').hidden = true; }
    refresh();
    var b = $('#bmBody');
    if (b) b.scrollTop = 0;
    if (n === 4) summary();
  }

  function showErr(msg, el) {
    var e = $('#wzErr');
    e.textContent = msg;
    e.classList.add('on');
    if (el) { el.classList.add('is-invalid'); try { el.focus({ preventScroll: false }); } catch (x) { el.focus(); } }
    else e.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
  function clearErr() {
    var e = $('#wzErr');
    if (e) { e.classList.remove('on'); e.textContent = ''; }
  }

  function val(id) { var el = $('#' + id); return el ? el.value.trim() : ''; }
  function fdate(s) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    try { return new Intl.DateTimeFormat(SL.lang || 'tr', { day: '2-digit', month: 'short', year: 'numeric', weekday: 'short' }).format(new Date(s + 'T00:00:00')); } catch (e) { return s; }
  }

  function validate(step) {
    clearErr();
    $$('.is-invalid', $('#bmBody')).forEach(function (x) { x.classList.remove('is-invalid'); });
    if (step === 1) {
      var legs = [];
      if (!$('#legA').hidden) legs.push('#legA');
      if (!$('#legD').hidden) legs.push('#legD');
      for (var i = 0; i < legs.length; i++) {
        var req = $$(legs[i] + ' [data-req]');
        for (var j = 0; j < req.length; j++) {
          if (!req[j].value.trim()) { showErr(T('err_required'), req[j]); return false; }
        }
        var fl = $(legs[i] + ' [data-flight]');
        if (fl && !flightOk(fl.value)) { showErr(T('err_flight'), fl); return false; }
      }
      var today = SL.today || '';
      if (!$('#legA').hidden && today && val('aDate') < today) { showErr(T('err_date'), $('#aDate')); return false; }
      if (!$('#legD').hidden && today && val('dDate') < today) { showErr(T('err_date'), $('#dDate')); return false; }
      if (S.trip === 'rt' && val('dDate') < val('aDate')) { showErr(T('err_order'), $('#dDate')); return false; }
      return true;
    }
    if (step === 2) {
      var nm = $('#wName'), ph = $('#wPhone'), em = $('#wEmail');
      if (nm.value.trim().length < 2) { showErr(T('err_required'), nm); return false; }
      if (ph.value.replace(/\D/g, '').length < 7) { showErr(T('err_phone'), ph); return false; }
      if (em.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(em.value.trim())) { showErr(T('err_email'), em); return false; }
      collectNames();
      var ins = $$('#paxList input[data-t="a"]');
      for (var k = 0; k < ins.length; k++) {
        if (ins[k].value.trim().length < 2) { showErr(T('err_names'), ins[k]); return false; }
      }
      return true;
    }
    if (step === 3) {
      if (!S.v) { showErr(T('err_vehicle')); return false; }
      if (!fits(S.v)) { showErr(T('err_capacity')); return false; }
      return true;
    }
    if (step === 4) {
      if (!$('#wTerms').checked) { showErr(T('err_terms'), $('#wTerms')); return false; }
      return true;
    }
    return true;
  }

  function summary() {
    var box = $('#sumBox');
    if (!box) return;
    collectNames();
    var v = SL.veh[S.v] || { n: '-' };
    var cur = ensurePay(), m = mult();
    var rows = [];
    rows.push([T('route'), T('airport_short') + ' ⇄ ' + S.d.n]);
    rows.push([T('trip'), tripText()]);
    rows.push([T('vehicle'), v.n + (S.v ? ' · ' + fmt(priceIn(cur, S.v) * m, cur) : '')]);
    if (!$('#legA').hidden) {
      rows.push([T('arrival_title'), esc([fdate(val('aDate')), val('aTime'), val('aFl').toUpperCase()].filter(Boolean).join(' · ')) + '<br>' + esc(val('aHotel')), true]);
    }
    if (!$('#legD').hidden) {
      var dp = val('dPick') ? ' · ' + T('pickup_time') + ': ' + val('dPick') : '';
      rows.push([T('departure_title'), esc([fdate(val('dDate')), val('dTime'), val('dFl').toUpperCase()].filter(Boolean).join(' · ') + dp) + '<br>' + esc(val('dHotel')), true]);
    }
    var pax = S.a + ' ' + T('adults') + (S.c ? ', ' + S.c + ' ' + T('children') : '');
    var names = S.an.slice(0, S.a).concat(S.cn.slice(0, S.c)).filter(function (x) { return x && x.trim(); }).map(upper);
    rows.push([T('passengers'), esc(pax) + (names.length ? '<br><small>' + esc(names.join(', ')) + '</small>' : ''), true]);
    // Ekstralar: çocuk koltuğu / yükseltici (1 adedi ücretsiz)
    if (S.s + S.b > 0) {
      var it = [];
      if (S.s) it.push(S.s + ' ' + T('child_seat'));
      if (S.b) it.push(S.b + ' ' + T('booster'));
      var ex = S.v ? extrasIn(cur, S.v) : { amt: 0 }, exT = S.v ? extrasTotal(cur, S.v) : 0;
      var exTxt = exT > 0 ? '+' + fmt(exT, cur) + (perLeg() && m > 1 ? ' (' + fmt(ex.amt, cur) + ' × ' + m + ')' : '') : T('free');
      rows.push([T('extras'), it.join(', ') + ' · ' + exTxt]);
    }
    rows.push([T('name'), val('wName') + ' · ' + val('wPhone') + (val('wEmail') ? ' · ' + val('wEmail') : '')]);
    rows.push([T('total'), '<b style="color:var(--accent)">' + esc(fmt(S.v ? totalIn(cur, S.v) : 0, cur)) + '</b> · ' + esc(T('cash')), true]);
    box.innerHTML = rows.map(function (r) {
      return '<div><dt>' + esc(r[0]) + '</dt><dd>' + (r[2] ? r[1] : esc(r[1])) + '</dd></div>';
    }).join('');
    renderPay();
  }

  // "Araçta nakit" ödeme para birimi seçimi: varsayılan seçili, diğerleri seçenek
  function renderPay() {
    var pb = $('#payBox');
    if (!pb) return;
    if (!S.v) { pb.innerHTML = ''; pb.removeAttribute('data-k'); return; }
    var list = payCurs(S.v), cur = ensurePay(), key = list.join(',');
    if (!list.length) { pb.innerHTML = ''; pb.removeAttribute('data-k'); return; }
    if (pb.getAttribute('data-k') !== key) {
      var h = '<div class="wz-sub"><i class="bi bi-cash-coin"></i>' + esc(T('pay_cur')) + '</div>' +
        '<div class="pay-c" role="radiogroup" aria-label="' + esc(T('pay_cur')) + '" data-n="' + list.length + '" style="--pc:' + Math.min(list.length, 4) + '">';
      list.forEach(function (c) {
        h += '<input type="radio" name="pc" id="pc' + esc(c) + '" value="' + esc(c) + '"><label for="pc' + esc(c) + '"><b></b><small>' + esc(c) + '</small></label>';
      });
      pb.innerHTML = h + '</div><div class="hint">' + esc(T('pay_cur_note')) + '</div>';
      pb.setAttribute('data-k', key);
    }
    list.forEach(function (c) {
      var r = $('#pc' + c, pb);
      if (!r) return;
      r.checked = c === cur;
      $('b', r.nextElementSibling).textContent = fmt(totalIn(c, S.v), c);
    });
  }

  function next() {
    if (!S || S.busy) return;
    if (!validate(S.step)) return;
    if (S.step < 4) { go(S.step + 1); return; }
    submit();
  }

  function submit() {
    var btn = $('#wNext');
    S.busy = true;
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span>' + esc(T('sending'));
    collectNames();

    var fd = new FormData();
    var tripVal = S.trip === 'rt' ? 'roundtrip' : (S.dir === 'arr' ? 'arrival' : 'departure');
    var add = function (k, v) { fd.append(k, v == null ? '' : v); };
    add('booking_type', 'transfer');
    add('lang', SL.lang || '');
    add('destination_id', S.id);
    add('vehicle_id', S.v);
    add('trip', tripVal);
    add('arr_date', val('aDate')); add('arr_time', val('aTime')); add('arr_flight', val('aFl').toUpperCase()); add('arr_hotel', val('aHotel'));
    add('dep_date', val('dDate')); add('dep_time', val('dTime')); add('dep_flight', val('dFl').toUpperCase()); add('dep_pickup', val('dPick')); add('dep_hotel', val('dHotel'));
    add('adults', S.a); add('children', S.c); add('child_seat', S.s); add('booster', S.b);
    add('full_name', val('wName')); add('phone', val('wPhone')); add('email', val('wEmail'));
    S.an.slice(0, S.a).forEach(function (n) { add('pax_adult[]', upper(n || '')); });
    S.cn.slice(0, S.c).forEach(function (n) { add('pax_child[]', upper(n || '')); });
    add('notes', val('wNotes'));
    add('website', val('wWeb'));
    add('elapsed', Math.round((Date.now() - S.t0) / 1000));
    var pc = ensurePay();
    add('pay_currency', pc);
    add('client_total', totalIn(pc, S.v));

    fetch(SL.api, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { success: false }; }); })
      .then(function (res) {
        S.busy = false;
        if (res && res.success) {
          done(res);
        } else {
          btn.disabled = false;
          btn.textContent = T('send');
          showErr((res && res.message) || T('err_generic'));
        }
      })
      .catch(function () {
        S.busy = false;
        btn.disabled = false;
        btn.textContent = T('send');
        showErr(T('err_generic'));
      });
  }

  function done(res) {
    var nos = (res.booking_numbers || []).map(function (n) { return '<span>' + esc(n) + '</span>'; }).join('');
    var wa = res.wa_url || (SL.wa ? 'https://wa.me/' + SL.wa + '?text=' + encodeURIComponent(T('wa_msg', { no: (res.booking_numbers || []).join(', ') })) : '');
    $('#wzDone').innerHTML = '<div class="ok"><i class="bi bi-check-circle-fill"></i><h4>' + esc(T('ok')) + '</h4><p>' + esc(res.message || T('ok2')) + '</p>' +
      (nos ? '<div class="nos"><small>' + esc(T('booking_no')) + '</small>' + nos + '</div>' : '') +
      (wa ? '<div><a class="btn btn-wa" href="' + esc(wa) + '" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>' + esc(T('wa_write')) + '</a></div>' : '') + '</div>';
    $('.wz-steps', $('#bmBody')).hidden = true;
    S.done = true;
    go(5);
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: 'sl_booking', booking_numbers: res.booking_numbers || [], value: res.total || 0, currency: res.currency || '', destination: S.d.n });
      /* Ölçüm (yalnız haber verir; Google/Yandex'e gönderimi assets/js/sl-olcum.js yapar — rezervasyon akışını etkilemez) */
      document.dispatchEvent(new CustomEvent('sl:booking', { detail: { numbers: res.booking_numbers || [], value: res.total || 0, currency: res.currency || '', destination: S.d.n } }));
    } catch (e) {}
  }

  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      if (S && S.done) { $('#bmBody').innerHTML = ''; S = null; }
    });
  }
})();
