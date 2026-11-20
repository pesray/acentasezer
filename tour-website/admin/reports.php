<?php
/**
 * Raporlar › Genel rapor — tahmini ciro ve gerçekleşen (net) ciro
 * Kurallar ve ortak parçalar: includes/rapor_ortak.php
 * Bu sayfa yalnız OKUR; rezervasyon verisini değiştirmez.
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

define('RAPOR_SAYFASI', 1);
require_once __DIR__ . '/includes/rapor_ortak.php';

$db = getDB();
$rows = rp_satirlar($db, $from, $to, $tur);

if (($_GET['export'] ?? '') === 'csv') {
    rp_csv_gonder($rows, 'rapor');
}

/* ------------------------------------------------------------------ */
/*  Toplamlar — her para birimi ayrı (kur çevrimi yok)                 */
/* ------------------------------------------------------------------ */
$say = ['yapildi' => 0, 'disari' => 0, 'bekleyen' => 0, 'iptal' => 0]; // iş sayıları (para biriminden bağımsız)
$ozet = [];      // pb => kategori => [n, t]
$hakedis = [];   // hakediş pb => [n, t]
$aylar = [];     // pb => 'Y-m' => kategori => [n, t]
$tipler = [];    // pb => tip => kategori => [n, t]
$partner = [];   // pb => ad => [n, tahsilat, hakedis]
$isler = [];     // pb => satırlar (işlem listesi)
$hakedisEksik = 0;
$partnerYok = 0;
$fiyatsiz = 0;

foreach ($rows as $r) {
    $k = rp_kategori($r);
    $say[$k]++;
    if ($k === 'iptal') continue;

    $pb = rp_pb($r['currency']);
    $tutar = max(0, (float)$r['total_price']);
    if ($tutar <= 0) $fiyatsiz++;
    $ym = $r['tarih'] ? substr($r['tarih'], 0, 7) : '0000-00';
    $tip = $r['tip'];

    if (!isset($ozet[$pb])) $ozet[$pb] = rp_bos_set();
    $ozet[$pb][$k]['n']++;
    $ozet[$pb][$k]['t'] += $tutar;

    if (!isset($aylar[$pb][$ym])) $aylar[$pb][$ym] = rp_bos_set();
    $aylar[$pb][$ym][$k]['n']++;
    $aylar[$pb][$ym][$k]['t'] += $tutar;

    if (!isset($tipler[$pb][$tip])) $tipler[$pb][$tip] = rp_bos_set();
    $tipler[$pb][$tip][$k]['n']++;
    $tipler[$pb][$tip][$k]['t'] += $tutar;

    $pad = '';
    $hk = 0.0;
    $hpb = '';
    if ($k === 'disari') {
        $pad = trim((string)$r['partner']);
        if ($pad === '') { $partnerYok++; $pad = '(Partner girilmemiş)'; }
        if (!isset($partner[$pb][$pad])) $partner[$pb][$pad] = ['n' => 0, 'tahsilat' => 0.0, 'hakedis' => 0.0];
        $partner[$pb][$pad]['n']++;
        $partner[$pb][$pad]['tahsilat'] += $tutar;

        $hk = max(0, (float)$r['outsource_price']);
        $hpb = rp_pb($r['outsource_currency'] ?: $r['currency']);
        if ($hk > 0) {
            if (!isset($hakedis[$hpb])) $hakedis[$hpb] = ['n' => 0, 't' => 0.0];
            $hakedis[$hpb]['n']++;
            $hakedis[$hpb]['t'] += $hk;
            // hakediş, girildiği para biriminin dökümünde sayılır
            if (!isset($partner[$hpb][$pad])) $partner[$hpb][$pad] = ['n' => 0, 'tahsilat' => 0.0, 'hakedis' => 0.0];
            $partner[$hpb][$pad]['hakedis'] += $hk;
        } else {
            $hakedisEksik++;
        }
    }

    $isler[$pb][] = [
        'tarih' => $r['tarih'], 'no' => $r['booking_number'], 'tip' => $tip, 'k' => $k,
        'musteri' => $r['customer_name'], 'guzergah' => $r['guzergah'], 'tutar' => $tutar,
        'partner' => $pad, 'hk' => $hk, 'hpb' => $hpb,
    ];
}

$pbler = rp_sirala_anahtar(array_unique(array_merge(array_keys($ozet), array_keys($hakedis))));
$pbSayi = [];
foreach ($ozet as $pb => $o) $pbSayi[$pb] = $o['yapildi']['n'] + $o['disari']['n'] + $o['bekleyen']['n'];
$aktifPb = $pbler[0] ?? '';
if ($pbSayi) { arsort($pbSayi); $aktifPb = (string)key($pbSayi); } // en çok işin olduğu para birimi

$tahminiN = $say['yapildi'] + $say['disari'] + $say['bekleyen'];
$gercekN = $say['yapildi'] + $say['disari'];
$oran = $tahminiN > 0 ? round($gercekN * 100 / $tahminiN) : 0;

$pageTitle = 'Raporlar';
require_once __DIR__ . '/includes/header.php';
rp_stil();
rp_ust('genel');
?>

<!-- İş sayıları (para biriminden bağımsız) -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <?php
    $kartlar = [
        ['Toplam transfer', $tahminiN, 'tahmini ciroya giren', 'bi-calculator', 'primary'],
        ['Gerçekleşen', $gercekN, 'yapıldı + dışarıya verildi', 'bi-cash-stack', 'success'],
        ['Kendimiz yaptık', $say['yapildi'], '"İş yapıldı" işaretli', 'bi-check2-circle', 'success'],
        ['Dışarıya verildi', $say['disari'], 'partnere verilen', 'bi-box-arrow-up-right', 'info'],
        ['Bekleyen', $say['bekleyen'], 'henüz işaretlenmedi', 'bi-hourglass-split', 'warning'],
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
                <div class="progress rp-progress mt-2" role="progressbar" aria-label="Gerçekleşme" aria-valuenow="<?= (int)$oran ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-success" style="width: <?= (int)$oran ?>%"></div>
                </div>
                <div class="rp-note mt-1">%<?= (int)$oran ?> gerçekleşti</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!$pbler): ?>
<div class="card table-card mb-4"><div class="card-body text-center text-body-secondary py-5">Bu aralıkta transfer yok.</div></div>
<?php else: ?>

<!-- Ciro özeti: her para birimi kendi satırında -->
<div class="card table-card mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i>Ciro özeti — para birimine göre</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table rp-table rp-ozet mb-2" id="rpOzet">
                <thead class="table-light">
                    <tr>
                        <th>Para birimi</th>
                        <th class="text-end">Tahmini ciro</th>
                        <th class="text-end">Kendimiz yaptık</th>
                        <th class="text-end">Dışarıya verildi</th>
                        <th class="text-end">Gerçekleşen (net) ciro</th>
                        <th class="text-end">Bekleyen</th>
                        <th class="text-end">Partner hakedişi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $topN = ['tahmini' => 0, 'yapildi' => 0, 'disari' => 0, 'gercek' => 0, 'bekleyen' => 0, 'hakedis' => 0];
                    foreach ($pbler as $pb):
                        $o = $ozet[$pb] ?? rp_bos_set();
                        $hucre = [
                            'tahmini'  => rp_birlesik($o['yapildi'], $o['disari'], $o['bekleyen']),
                            'yapildi'  => $o['yapildi'],
                            'disari'   => $o['disari'],
                            'gercek'   => rp_birlesik($o['yapildi'], $o['disari']),
                            'bekleyen' => $o['bekleyen'],
                            'hakedis'  => $hakedis[$pb] ?? ['n' => 0, 't' => 0.0],
                        ];
                        foreach ($hucre as $hk => $b) $topN[$hk] += $b['n']; ?>
                    <tr>
                        <td><span class="rp-pb"><span class="rp-pb-sem"><?= e($pbSem[$pb] ?? $pb) ?></span><?= e($pb) ?> <span class="fw-normal text-body-secondary small"><?= e($pbAd[$pb] ?? '') ?></span></span></td>
                        <?php foreach ($hucre as $hk => $b): ?>
                        <td class="text-end <?= $hk === 'gercek' ? 'rp-col-gercek' : '' ?>" data-k="<?= $hk ?>" data-pb="<?= e($pb) ?>" data-t="<?= number_format($b['t'], 2, '.', '') ?>" data-n="<?= (int)$b['n'] ?>">
                            <?= rp_tutar($b, $pb) ?>
                            <?php if ($b['n'] > 0): ?><span class="rp-n"><?= (int)$b['n'] ?> <?= $hk === 'hakedis' ? 'hakediş' : 'iş' ?></span><?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Toplam iş sayısı</td>
                        <td class="text-end"><?= (int)$topN['tahmini'] ?> iş</td>
                        <td class="text-end"><?= (int)$topN['yapildi'] ?> iş</td>
                        <td class="text-end"><?= (int)$topN['disari'] ?> iş</td>
                        <td class="text-end"><?= (int)$topN['gercek'] ?> iş</td>
                        <td class="text-end"><?= (int)$topN['bekleyen'] ?> iş</td>
                        <td class="text-end"><?= (int)$topN['hakedis'] ?> hakediş</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="rp-note">
            <strong>Gerçekleşen</strong> = Kendimiz yaptık + Dışarıya verildi · <strong>Tahmini</strong> = Gerçekleşen + Bekleyen ·
            Para birimleri birbirine eklenmez, kur çevrimi yapılmaz. Partner hakedişi, girildiği para biriminde sayılır.
        </div>
    </div>
</div>

<?php rp_pb_secici($pbler, $aktifPb, $pbSayi); ?>

<?php foreach ($pbler as $pb):
    $ay = $aylar[$pb] ?? [];
    krsort($ay);
    $tp = $tipler[$pb] ?? [];
    $pt = $partner[$pb] ?? [];
    uasort($pt, function ($a, $b) { return $b['n'] - $a['n'] ?: ($b['hakedis'] <=> $a['hakedis']); });
    $is = $isler[$pb] ?? [];
    $isimPb = ($pbSem[$pb] ?? '') . ' ' . $pb; ?>
<div class="rp-pane" data-rp-pane="<?= e($pb) ?>" <?= $pb === $aktifPb ? '' : 'hidden' ?>>

    <div class="row g-4 mb-4">
        <!-- Aylık döküm -->
        <div class="col-12 col-xxl-7">
            <div class="card table-card h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Aylık döküm <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
                <div class="card-body">
                    <?php if (!$ay): ?>
                    <div class="text-center text-body-secondary py-4">Bu para biriminde transfer yok.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover rp-table mb-0 rp-aylik" data-pb="<?= e($pb) ?>">
                            <thead class="table-light">
                                <tr>
                                    <th>Ay</th>
                                    <th class="text-end">İş</th>
                                    <th class="text-end">Tahmini</th>
                                    <th class="text-end">Kendimiz yaptık</th>
                                    <th class="text-end">Dışarıya verildi</th>
                                    <th class="text-end">Gerçekleşen</th>
                                    <th class="text-end">Bekleyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $tAy = rp_bos_set();
                                foreach ($ay as $ym => $a):
                                    foreach ($a as $kk => $b) { $tAy[$kk]['n'] += $b['n']; $tAy[$kk]['t'] += $b['t']; }
                                    $aT = rp_birlesik($a['yapildi'], $a['disari'], $a['bekleyen']); ?>
                                <tr>
                                    <td class="fw-semibold"><?= e(rp_ay_ad($ym)) ?></td>
                                    <td class="text-end"><?= (int)$aT['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($aT, $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($a['yapildi'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($a['disari'], $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar(rp_birlesik($a['yapildi'], $a['disari']), $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($a['bekleyen'], $pb) ?></td>
                                </tr>
                                <?php endforeach;
                                $tT = rp_birlesik($tAy['yapildi'], $tAy['disari'], $tAy['bekleyen']); ?>
                            </tbody>
                            <tfoot>
                                <tr data-toplam-t="<?= number_format($tT['t'], 2, '.', '') ?>" data-toplam-n="<?= (int)$tT['n'] ?>">
                                    <td>TOPLAM</td>
                                    <td class="text-end"><?= (int)$tT['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($tT, $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tAy['yapildi'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tAy['disari'], $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar(rp_birlesik($tAy['yapildi'], $tAy['disari']), $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tAy['bekleyen'], $pb) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Transfer türüne göre -->
        <div class="col-12 col-xxl-5">
            <div class="card table-card h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-signpost-split me-2"></i>Transfer türüne göre <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
                <div class="card-body">
                    <?php if (!$tp): ?>
                    <div class="text-center text-body-secondary py-4">Bu para biriminde transfer yok.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover rp-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tür</th>
                                    <th class="text-end">İş</th>
                                    <th class="text-end">Tahmini</th>
                                    <th class="text-end">Gerçekleşen</th>
                                    <th class="text-end">Bekleyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $tTip = rp_bos_set();
                                foreach (['arrival', 'depar', 'ara'] as $tk):
                                    if (!isset($tp[$tk])) continue;
                                    $t = $tp[$tk];
                                    foreach ($t as $kk => $b) { $tTip[$kk]['n'] += $b['n']; $tTip[$kk]['t'] += $b['t']; }
                                    $tA = rp_birlesik($t['yapildi'], $t['disari'], $t['bekleyen']); ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($tipAd[$tk]) ?></td>
                                    <td class="text-end"><?= (int)$tA['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($tA, $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar(rp_birlesik($t['yapildi'], $t['disari']), $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($t['bekleyen'], $pb) ?></td>
                                </tr>
                                <?php endforeach;
                                $tTT = rp_birlesik($tTip['yapildi'], $tTip['disari'], $tTip['bekleyen']); ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>TOPLAM</td>
                                    <td class="text-end"><?= (int)$tTT['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($tTT, $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar(rp_birlesik($tTip['yapildi'], $tTip['disari']), $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tTip['bekleyen'], $pb) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Partnerlere göre -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Dışarıya verilen işler — partnerlere göre <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
        <div class="card-body">
            <?php if (!$pt): ?>
            <div class="text-center text-body-secondary py-4">Bu para biriminde dışarıya verilen iş ya da hakediş yok.</div>
            <?php else:
                $pTop = ['n' => 0, 'tahsilat' => 0.0, 'hakedis' => 0.0];
                foreach ($pt as $p) { $pTop['n'] += $p['n']; $pTop['tahsilat'] += $p['tahsilat']; $pTop['hakedis'] += $p['hakedis']; } ?>
            <div class="table-responsive">
                <table class="table table-hover rp-table rp-dt mb-0" data-pb="<?= e($pb) ?>">
                    <thead class="table-light">
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">İş</th>
                            <th class="text-end">Müşteriden alınan</th>
                            <th class="text-end">Partner hakedişi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pt as $pad => $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($pad) ?></td>
                            <td class="text-end"><?= (int)$p['n'] ?></td>
                            <td class="text-end" data-order="<?= number_format($p['tahsilat'], 2, '.', '') ?>"><?= $p['tahsilat'] > 0 ? e(rp_para($p['tahsilat'], $pb)) : '<span class="text-body-secondary">—</span>' ?></td>
                            <td class="text-end" data-order="<?= number_format($p['hakedis'], 2, '.', '') ?>"><?= $p['hakedis'] > 0 ? e(rp_para($p['hakedis'], $pb)) : '<span class="text-body-secondary">—</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>TOPLAM (<?= count($pt) ?> partner)</td>
                            <td class="text-end"><?= (int)$pTop['n'] ?></td>
                            <td class="text-end"><?= e(rp_para($pTop['tahsilat'], $pb)) ?></td>
                            <td class="text-end"><?= e(rp_para($pTop['hakedis'], $pb)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="rp-note mt-2">Hakediş, girildiği para biriminin dökümünde görünür (ör. € ile alınan bir işin hakedişi ₺ girildiyse ₺ dökümünde sayılır).</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- İşlem listesi -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>İşlem listesi <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
        <div class="card-body">
            <?php if (!$is): ?>
            <div class="text-center text-body-secondary py-4">Bu para biriminde transfer yok.</div>
            <?php else: ?>
            <table class="table table-hover rp-table rp-isler mb-0 w-100" data-pb="<?= e($pb) ?>">
                <thead class="table-light">
                    <tr>
                        <th>Tarih</th>
                        <th>Rez. No</th>
                        <th>Tür</th>
                        <th>Müşteri</th>
                        <th>Güzergah</th>
                        <th>Durum</th>
                        <th class="text-end">Tutar</th>
                        <th>Partner</th>
                        <th class="text-end">Hakediş</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($is as $x): ?>
                    <tr data-tutar="<?= number_format($x['tutar'], 2, '.', '') ?>" data-hk="<?= number_format($x['hk'], 2, '.', '') ?>" data-hpb="<?= e($x['hpb']) ?>" data-k="<?= $x['k'] ?>" data-kad="<?= e($katAd[$x['k']]) ?>">
                        <td data-order="<?= e((string)$x['tarih']) ?>"><?= $x['tarih'] ? e(date('d.m.Y', strtotime($x['tarih']))) : '—' ?></td>
                        <td><?= e($x['no']) ?></td>
                        <td><?= e($tipAd[$x['tip']]) ?></td>
                        <td><?= e($x['musteri']) ?></td>
                        <td><?= e($x['guzergah']) ?></td>
                        <td><span class="badge bg-<?= $katRenk[$x['k']] ?>-subtle text-<?= $katRenk[$x['k']] ?>-emphasis"><?= e($katAd[$x['k']]) ?></span></td>
                        <td class="text-end" data-order="<?= number_format($x['tutar'], 2, '.', '') ?>"><?= e(rp_para($x['tutar'], $pb)) ?></td>
                        <td><?= e($x['partner']) ?></td>
                        <td class="text-end" data-order="<?= number_format($x['hk'], 2, '.', '') ?>"><?= $x['hk'] > 0 ? e(rp_para($x['hk'], $x['hpb'])) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="rp-f-n"><?= count($is) ?> işlem</td>
                        <td colspan="4" class="rp-f-kat"></td>
                        <td class="text-end rp-f-tutar"></td>
                        <td></td>
                        <td class="text-end rp-f-hk"></td>
                    </tr>
                </tfoot>
            </table>
            <div class="rp-note mt-2">Alttaki toplamlar, listede aradığınız / süzdüğünüz işlemlere göre güncellenir.</div>
            <?php endif; ?>
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
            ['bi-currency-exchange', 'Tutarı 0 olan transfer', $fiyatsiz, 'Ciroya eklenemedi; rezervasyondan fiyatı girin.'],
            ['bi-receipt', 'Dışarıya verilmiş, hakediş girilmemiş', $hakedisEksik, 'Partnerin hakedişi bilinmiyor.'],
            ['bi-person-x', 'Dışarıya verilmiş, partner seçilmemiş', $partnerYok, 'Partner dökümünde "(Partner girilmemiş)" olarak görünür.'],
            ['bi-x-circle', 'İptal edilen (hesaba katılmadı)', $say['iptal'], 'Silinen rezervasyonlar zaten hariç.'],
        ];
        ?>
        <div class="row g-3">
            <?php foreach ($kontroller as $kt): ?>
            <div class="col-12 col-md-6 col-xl-3">
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
        <div class="rp-note mt-3">Gidiş-dönüş rezervasyon iki transfer olarak sayılır (her yön kendi tutarıyla).</div>
    </div>
</div>

<?php
$extraJs = rp_js();
require_once __DIR__ . '/includes/footer.php';
