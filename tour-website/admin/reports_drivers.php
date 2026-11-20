<?php
/**
 * Raporlar › Şoförler — şoförlerin cirosu
 *
 * Kapsam: kendi aracımızla yapılan işler (dışarıya verilenler partner raporunda; iptal + silinen hariç).
 *  - Şoför = rezervasyondaki "Şoför Ata" kaydı (booking_drivers). Atanmamış işler "(Şoför atanmamış)" satırında.
 *  - Gerçekleşen (tamamlanan) = "İş yapıldı" işaretli işler; Bekleyen = henüz işaretlenmemiş; Tahmini = ikisi.
 *  - Toplamlar Genel rapordaki "Kendimiz yaptık" + "Bekleyen" ile aynıdır (aynı filtrede).
 *  - Para birimleri ayrı toplanır (kur çevrimi yok).
 * Kurallar ve ortak parçalar: includes/rapor_ortak.php. Bu sayfa yalnız OKUR.
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

define('RAPOR_SAYFASI', 1);
require_once __DIR__ . '/includes/rapor_ortak.php';

$db = getDB();
$soforler = rp_soforler($db);

// Şoför filtresi: '' = tümü, 'yok' = atanmamış, rakam = şoför id (listede olmalı)
$soforSecili = '';
if (isset($_GET['sofor']) && is_string($_GET['sofor'])) {
    if ($_GET['sofor'] === 'yok') {
        $soforSecili = 'yok';
    } elseif (ctype_digit($_GET['sofor'])) {
        foreach ($soforler as $s) {
            if ((string)$s['id'] === $_GET['sofor']) $soforSecili = $_GET['sofor'];
        }
    }
}

$rows = rp_satirlar($db, $from, $to, $tur);

/* ------------------------------------------------------------------ */
/*  Kendi işlerimiz + şoför filtresi                                   */
/* ------------------------------------------------------------------ */
$soforBilgi = [0 => ['ad' => '(Şoför atanmamış)', 'plaka' => '']];
foreach ($soforler as $s) {
    $soforBilgi[(int)$s['id']] = ['ad' => $s['ad'] !== '' ? $s['ad'] : ('Şoför #' . $s['id']), 'plaka' => (string)$s['plate']];
}

$secilen = [];
$disariSoforlu = 0;
foreach ($rows as $r) {
    $k = rp_kategori($r);
    if ($k === 'iptal') continue;
    $sid = (int)($r['sofor_id'] ?? 0);
    if ($soforSecili === 'yok' && $sid !== 0) continue;
    if ($soforSecili !== '' && $soforSecili !== 'yok' && $sid !== (int)$soforSecili) continue;
    if ($k === 'disari') {
        if ($sid > 0) $disariSoforlu++;
        continue; // dışarıya verilen iş şoför cirosuna girmez
    }
    if (!isset($soforBilgi[$sid])) {
        $soforBilgi[$sid] = ['ad' => $r['sofor'] ?: ('Şoför #' . $sid), 'plaka' => (string)$r['plaka']];
    }
    $r['k'] = $k;
    $r['sid'] = $sid;
    $secilen[] = $r;
}

if (($_GET['export'] ?? '') === 'csv') {
    rp_csv_gonder($secilen, 'sofor_raporu');
}

/* ------------------------------------------------------------------ */
/*  Toplamlar                                                          */
/* ------------------------------------------------------------------ */
$bosIki = function () { return ['yapildi' => ['n' => 0, 't' => 0.0], 'bekleyen' => ['n' => 0, 't' => 0.0]]; };
$say = ['yapildi' => 0, 'bekleyen' => 0];
$sofor = [];    // sid => ['yapildi' => n, 'bekleyen' => n, 'gercek' => [pb => t]]
$soforPb = [];  // pb => sid => kategori => [n, t]
$ayPb = [];     // pb => 'Y-m' => sid => [n, t]  (yalnız tamamlanan)
$islerPb = [];  // pb => satırlar
$pbSayi = [];
$atanmamisTamam = 0;
$fiyatsiz = 0;

foreach ($secilen as $r) {
    $k = $r['k'];
    $sid = $r['sid'];
    $pb = rp_pb($r['currency']);
    $tutar = max(0, (float)$r['total_price']);
    if ($tutar <= 0) $fiyatsiz++;
    if ($k === 'yapildi' && $sid === 0) $atanmamisTamam++;
    $say[$k]++;
    $pbSayi[$pb] = ($pbSayi[$pb] ?? 0) + 1;

    if (!isset($sofor[$sid])) $sofor[$sid] = ['yapildi' => 0, 'bekleyen' => 0, 'gercek' => []];
    $sofor[$sid][$k]++;
    if ($k === 'yapildi') $sofor[$sid]['gercek'][$pb] = ($sofor[$sid]['gercek'][$pb] ?? 0) + $tutar;

    if (!isset($soforPb[$pb][$sid])) $soforPb[$pb][$sid] = $bosIki();
    $soforPb[$pb][$sid][$k]['n']++;
    $soforPb[$pb][$sid][$k]['t'] += $tutar;

    if ($k === 'yapildi') {
        $ym = $r['tarih'] ? substr($r['tarih'], 0, 7) : '0000-00';
        if (!isset($ayPb[$pb][$ym][$sid])) $ayPb[$pb][$ym][$sid] = ['n' => 0, 't' => 0.0];
        $ayPb[$pb][$ym][$sid]['n']++;
        $ayPb[$pb][$ym][$sid]['t'] += $tutar;
    }

    $islerPb[$pb][] = [
        'tarih' => $r['tarih'], 'no' => $r['booking_number'], 'tip' => $r['tip'], 'k' => $k, 'sid' => $sid,
        'musteri' => $r['customer_name'], 'guzergah' => $r['guzergah'], 'tutar' => $tutar,
    ];
}

// Sıra: en çok iş tamamlayan önce, "atanmamış" en sonda
$sira = array_keys($sofor);
usort($sira, function ($a, $b) use ($sofor) {
    if ($a === 0) return 1;
    if ($b === 0) return -1;
    return ($sofor[$b]['yapildi'] <=> $sofor[$a]['yapildi']) ?: ($sofor[$b]['bekleyen'] <=> $sofor[$a]['bekleyen']);
});

$pbler = rp_sirala_anahtar(array_keys($pbSayi));
$aktifPb = $pbler[0] ?? '';
if ($pbSayi) { $tmp = $pbSayi; arsort($tmp); $aktifPb = (string)key($tmp); }

$toplamN = $say['yapildi'] + $say['bekleyen'];
$oran = $toplamN > 0 ? round($say['yapildi'] * 100 / $toplamN) : 0;
$soforluSayi = count(array_filter(array_keys($sofor), function ($s) { return $s !== 0; }));
$dKatAd = ['yapildi' => 'Tamamlandı', 'bekleyen' => 'Bekleyen'];

$pageTitle = 'Raporlar — Şoförler';
require_once __DIR__ . '/includes/header.php';
rp_stil();
rp_ust('sofor', $soforler, $soforSecili);
?>

<!-- İş sayıları -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <?php
    $kartlar = [
        ['Toplam iş', $toplamN, 'kendi aracımızla (dışarı verilen hariç)', 'bi-calculator', 'primary'],
        ['Tamamlanan', $say['yapildi'], '"İş yapıldı" işaretli', 'bi-check2-circle', 'success'],
        ['Bekleyen', $say['bekleyen'], 'henüz işaretlenmedi', 'bi-hourglass-split', 'warning'],
        ['Şoför', $soforluSayi, 'bu aralıkta işi olan', 'bi-person-badge', 'info'],
        ['Şoförsüz tamamlanan', $atanmamisTamam, 'şoför atanmamış', 'bi-person-x', $atanmamisTamam > 0 ? 'danger' : 'secondary'],
    ];
    foreach ($kartlar as $i => $kt): ?>
    <div class="col">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <div class="rp-label"><?= e($kt[0]) ?></div>
                        <div class="rp-big" data-sayi="<?= e($kt[0]) ?>"><?= (int)$kt[1] ?></div>
                        <div class="rp-note"><?= e($kt[2]) ?></div>
                    </div>
                    <div class="stat-icon bg-<?= $kt[4] ?>-subtle text-<?= $kt[4] ?>"><i class="bi <?= $kt[3] ?>"></i></div>
                </div>
                <?php if ($i === 1): ?>
                <div class="progress rp-progress mt-2" role="progressbar" aria-label="Tamamlanma" aria-valuenow="<?= (int)$oran ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-success" style="width: <?= (int)$oran ?>%"></div>
                </div>
                <div class="rp-note mt-1">%<?= (int)$oran ?> tamamlandı</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!$secilen): ?>
<div class="card table-card mb-4"><div class="card-body text-center text-body-secondary py-5">Bu aralıkta kendi aracımızla yapılan iş yok.</div></div>
<?php else: ?>

<!-- Şoför cirosu özeti: her para birimi ayrı sütun -->
<div class="card table-card mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Şoför cirosu — gerçekleşen (tamamlanan işler)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover rp-table rp-ozet mb-2" id="rpSoforOzet">
                <thead class="table-light">
                    <tr>
                        <th>Şoför</th>
                        <th>Plaka</th>
                        <th class="text-end">Toplam iş</th>
                        <th class="text-end">Tamamlanan</th>
                        <th class="text-end">Bekleyen</th>
                        <?php foreach ($pbler as $pb): ?>
                        <th class="text-end">Ciro <?= e($pbSem[$pb] ?? $pb) ?> <?= e($pb) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $alt = ['yapildi' => 0, 'bekleyen' => 0, 'gercek' => []];
                    foreach ($sira as $sid):
                        $s = $sofor[$sid];
                        $alt['yapildi'] += $s['yapildi'];
                        $alt['bekleyen'] += $s['bekleyen']; ?>
                    <tr<?= $sid === 0 ? ' class="table-warning"' : '' ?>>
                        <td class="fw-semibold"><?= e($soforBilgi[$sid]['ad']) ?></td>
                        <td><?= $soforBilgi[$sid]['plaka'] !== '' ? e($soforBilgi[$sid]['plaka']) : '<span class="text-body-secondary">—</span>' ?></td>
                        <td class="text-end"><?= (int)($s['yapildi'] + $s['bekleyen']) ?></td>
                        <td class="text-end"><?= (int)$s['yapildi'] ?></td>
                        <td class="text-end"><?= (int)$s['bekleyen'] ?></td>
                        <?php foreach ($pbler as $pb):
                            $v = $s['gercek'][$pb] ?? 0.0;
                            $n = $soforPb[$pb][$sid]['yapildi']['n'] ?? 0;
                            $alt['gercek'][$pb] = ($alt['gercek'][$pb] ?? 0) + $v; ?>
                        <td class="text-end rp-col-gercek" data-sofor="<?= (int)$sid ?>" data-pb="<?= e($pb) ?>" data-t="<?= number_format($v, 2, '.', '') ?>" data-n="<?= (int)$n ?>">
                            <?= $n > 0 ? e(rp_para($v, $pb)) : '<span class="text-body-secondary fw-normal">—</span>' ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">TOPLAM</td>
                        <td class="text-end"><?= (int)($alt['yapildi'] + $alt['bekleyen']) ?></td>
                        <td class="text-end"><?= (int)$alt['yapildi'] ?></td>
                        <td class="text-end"><?= (int)$alt['bekleyen'] ?></td>
                        <?php foreach ($pbler as $pb): ?>
                        <td class="text-end" data-toplam-pb="<?= e($pb) ?>" data-t="<?= number_format($alt['gercek'][$pb] ?? 0, 2, '.', '') ?>"><?= e(rp_para($alt['gercek'][$pb] ?? 0, $pb)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="rp-note">
            Ciro = şoförün <strong>tamamladığı</strong> ("İş yapıldı" işaretli) işlerin tutarı. Her para birimi ayrı sütundur, birbirine eklenmez.
            Dışarıya verilen işler burada yoktur (Genel rapor › partnerlere göre).
        </div>
    </div>
</div>

<?php rp_pb_secici($pbler, $aktifPb, $pbSayi); ?>

<?php foreach ($pbler as $pb):
    $sp = $soforPb[$pb] ?? [];
    $siraPb = array_values(array_filter($sira, function ($s) use ($sp) { return isset($sp[$s]); }));
    $ay = $ayPb[$pb] ?? [];
    krsort($ay);
    $ayKolon = array_values(array_filter($siraPb, function ($s) use ($ay) {
        foreach ($ay as $satir) { if (isset($satir[$s])) return true; }
        return false;
    }));
    $is = $islerPb[$pb] ?? [];
    $isimPb = ($pbSem[$pb] ?? '') . ' ' . $pb; ?>
<div class="rp-pane" data-rp-pane="<?= e($pb) ?>" <?= $pb === $aktifPb ? '' : 'hidden' ?>>

    <div class="row g-4 mb-4">
        <!-- Şoförlere göre -->
        <div class="col-12 col-xxl-5">
            <div class="card table-card h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Şoförlere göre <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover rp-table mb-0 rp-sofor-pb" data-pb="<?= e($pb) ?>">
                            <thead class="table-light">
                                <tr>
                                    <th>Şoför</th>
                                    <th class="text-end">İş</th>
                                    <th class="text-end">Tahmini</th>
                                    <th class="text-end">Gerçekleşen</th>
                                    <th class="text-end">Bekleyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $tS = $bosIki();
                                foreach ($siraPb as $sid):
                                    $x = $sp[$sid];
                                    foreach ($x as $kk => $b) { $tS[$kk]['n'] += $b['n']; $tS[$kk]['t'] += $b['t']; }
                                    $xT = rp_birlesik($x['yapildi'], $x['bekleyen']); ?>
                                <tr<?= $sid === 0 ? ' class="table-warning"' : '' ?>>
                                    <td class="fw-semibold"><?= e($soforBilgi[$sid]['ad']) ?></td>
                                    <td class="text-end"><?= (int)$xT['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($xT, $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($x['yapildi'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($x['bekleyen'], $pb) ?></td>
                                </tr>
                                <?php endforeach;
                                $tST = rp_birlesik($tS['yapildi'], $tS['bekleyen']); ?>
                            </tbody>
                            <tfoot>
                                <tr data-toplam-t="<?= number_format($tST['t'], 2, '.', '') ?>" data-toplam-n="<?= (int)$tST['n'] ?>" data-gercek-t="<?= number_format($tS['yapildi']['t'], 2, '.', '') ?>">
                                    <td>TOPLAM</td>
                                    <td class="text-end"><?= (int)$tST['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($tST, $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($tS['yapildi'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tS['bekleyen'], $pb) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aylık gerçekleşen ciro, şoför sütunlarıyla -->
        <div class="col-12 col-xxl-7">
            <div class="card table-card h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Aylık gerçekleşen ciro <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
                <div class="card-body">
                    <?php if (!$ay): ?>
                    <div class="text-center text-body-secondary py-4">Bu para biriminde tamamlanan iş yok.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover rp-table mb-0 rp-sofor-aylik" data-pb="<?= e($pb) ?>">
                            <thead class="table-light">
                                <tr>
                                    <th>Ay</th>
                                    <?php foreach ($ayKolon as $sid): ?>
                                    <th class="text-end"><?= e($soforBilgi[$sid]['ad']) ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-end">TOPLAM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $kolTop = [];
                                $genel = ['n' => 0, 't' => 0.0];
                                foreach ($ay as $ym => $satir):
                                    $satirTop = ['n' => 0, 't' => 0.0]; ?>
                                <tr>
                                    <td class="fw-semibold"><?= e(rp_ay_ad($ym)) ?></td>
                                    <?php foreach ($ayKolon as $sid):
                                        $c = $satir[$sid] ?? ['n' => 0, 't' => 0.0];
                                        $satirTop = rp_birlesik($satirTop, $c);
                                        $kolTop[$sid] = rp_birlesik($kolTop[$sid] ?? ['n' => 0, 't' => 0.0], $c); ?>
                                    <td class="text-end"><?= rp_tutar($c, $pb) ?></td>
                                    <?php endforeach;
                                    $genel = rp_birlesik($genel, $satirTop); ?>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($satirTop, $pb) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr data-toplam-t="<?= number_format($genel['t'], 2, '.', '') ?>">
                                    <td>TOPLAM</td>
                                    <?php foreach ($ayKolon as $sid): ?>
                                    <td class="text-end"><?= rp_tutar($kolTop[$sid] ?? ['n' => 0, 't' => 0.0], $pb) ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($genel, $pb) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- İşlem listesi -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>İşlem listesi <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
        <div class="card-body">
            <table class="table table-hover rp-table rp-isler mb-0 w-100" data-pb="<?= e($pb) ?>">
                <thead class="table-light">
                    <tr>
                        <th>Tarih</th>
                        <th>Rez. No</th>
                        <th>Tür</th>
                        <th>Şoför</th>
                        <th>Müşteri</th>
                        <th>Güzergah</th>
                        <th>Durum</th>
                        <th class="text-end">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($is as $x): ?>
                    <tr data-tutar="<?= number_format($x['tutar'], 2, '.', '') ?>" data-k="<?= $x['k'] ?>" data-kad="<?= e($dKatAd[$x['k']]) ?>" data-sofor="<?= (int)$x['sid'] ?>">
                        <td data-order="<?= e((string)$x['tarih']) ?>"><?= $x['tarih'] ? e(date('d.m.Y', strtotime($x['tarih']))) : '—' ?></td>
                        <td><?= e($x['no']) ?></td>
                        <td><?= e($tipAd[$x['tip']]) ?></td>
                        <td class="fw-semibold"><?= e($soforBilgi[$x['sid']]['ad']) ?></td>
                        <td><?= e($x['musteri']) ?></td>
                        <td><?= e($x['guzergah']) ?></td>
                        <td><span class="badge bg-<?= $katRenk[$x['k']] ?>-subtle text-<?= $katRenk[$x['k']] ?>-emphasis"><?= e($dKatAd[$x['k']]) ?></span></td>
                        <td class="text-end" data-order="<?= number_format($x['tutar'], 2, '.', '') ?>"><?= e(rp_para($x['tutar'], $pb)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="rp-f-n"><?= count($is) ?> işlem</td>
                        <td colspan="4" class="rp-f-kat"></td>
                        <td class="text-end rp-f-tutar"></td>
                    </tr>
                </tfoot>
            </table>
            <div class="rp-note mt-2">Listede şoför adı aratırsanız alttaki toplam o şoförün işlerini gösterir.</div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Kontrol edilmesi gerekenler -->
<div class="card table-card mb-4">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2"></i>Kontrol edilmesi gerekenler</h6></div>
    <div class="card-body">
        <?php
        $kontroller = [
            ['bi-person-x', 'Tamamlanmış, şoför atanmamış', $atanmamisTamam, '"(Şoför atanmamış)" satırında görünür; rezervasyondan şoför atayın.'],
            ['bi-box-arrow-up-right', 'Dışarıya verilmiş ama şoför atanmış', $disariSoforlu, 'Şoför cirosuna sayılmadı (işi partner yaptı).'],
            ['bi-currency-exchange', 'Tutarı 0 olan iş', $fiyatsiz, 'Ciroya eklenemedi; rezervasyondan fiyatı girin.'],
        ];
        ?>
        <div class="row g-3">
            <?php foreach ($kontroller as $kt): ?>
            <div class="col-12 col-md-4">
                <div class="d-flex justify-content-between align-items-start gap-2 h-100 p-3 rounded border">
                    <div>
                        <div class="fw-semibold"><i class="bi <?= e($kt[0]) ?> me-2 text-body-secondary"></i><?= e($kt[1]) ?></div>
                        <div class="rp-note"><?= e($kt[3]) ?></div>
                    </div>
                    <span class="badge rounded-pill fs-6 <?= $kt[2] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>"><?= (int)$kt[2] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$extraJs = rp_js();
require_once __DIR__ . '/includes/footer.php';
