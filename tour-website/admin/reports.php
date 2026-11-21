<?php
/**
 * Raporlar › Genel rapor — tahmini ciro, net ciro, partner hakedişi, pass
 * Kurallar ve hesap: includes/rapor_ortak.php (rp_is_hesap, rp_genel_hesapla)
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

// Kur yalnız hakedişi farklı para biriminde girilmiş iş varsa çekilir
$kurGerekli = rp_kur_gerekli($rows);
$kur = $kurGerekli ? rp_kurlar() : null;
$h = rp_genel_hesapla($rows, $kur);
$say = $h['say'];

$pbler = rp_sirala_anahtar(array_keys($h['ozet']));
$pbSayi = [];
foreach ($pbler as $pb) $pbSayi[$pb] = $h['ozet'][$pb]['tahmini']['n'];
$aktifPb = $pbler[0] ?? '';
if ($pbSayi) { $sirali = $pbSayi; arsort($sirali); $aktifPb = (string)key($sirali); } // en çok işin olduğu para birimi

$tahminiN = $say['yapildi'] + $say['disari'] + $say['bekleyen'];
$gercekN = $say['yapildi'] + $say['disari'];
$oran = $tahminiN > 0 ? round($gercekN * 100 / $tahminiN) : 0;

$alanlar = ['tahmini' => 'Tahmini ciro', 'net' => 'Net ciro', 'hakedis' => 'Partner hakedişi', 'pass' => 'Pass'];

/** Satır toplamı için: bir kümeyi diğerine ekle */
function rp_alan_topla(&$hedef, $set) {
    foreach ($set as $alan => $b) {
        $hedef[$alan]['n'] += $b['n'];
        $hedef[$alan]['t'] += $b['t'];
    }
}

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

<?php if ($h['kurYok'] > 0): ?>
<div class="alert alert-warning d-flex gap-2 align-items-start" role="alert" id="rpKurUyari">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div><strong>Güncel döviz kuru alınamadı</strong> (TCMB'ye ulaşılamadı). Hakedişi farklı para biriminde girilen <strong><?= (int)$h['kurYok'] ?> işin</strong> pass'ı hesaplanamadı; bu işler net ciroya ve pass'a eklenmedi (partner hakedişinde görünür). Birkaç dakika sonra sayfayı yenileyin.</div>
</div>
<?php endif; ?>

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
                        <?php foreach ($alanlar as $alan => $ad): ?>
                        <th class="text-end"><?= e($ad) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $topN = array_fill_keys(array_keys($alanlar), 0);
                    foreach ($pbler as $pb):
                        $o = $h['ozet'][$pb]; ?>
                    <tr>
                        <td><span class="rp-pb"><span class="rp-pb-sem"><?= e($pbSem[$pb] ?? $pb) ?></span><?= e($pb) ?> <span class="fw-normal text-body-secondary small"><?= e($pbAd[$pb] ?? '') ?></span></span></td>
                        <?php foreach ($alanlar as $alan => $ad):
                            $b = $o[$alan];
                            $topN[$alan] += $b['n']; ?>
                        <td class="text-end <?= $alan === 'net' ? 'rp-col-gercek' : '' ?>" data-k="<?= $alan ?>" data-pb="<?= e($pb) ?>" data-t="<?= number_format($b['t'], 2, '.', '') ?>" data-n="<?= (int)$b['n'] ?>">
                            <?= rp_tutar($b, $pb) ?>
                            <?php if ($b['n'] > 0): ?><span class="rp-n"><?= (int)$b['n'] ?> iş</span><?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Toplam iş sayısı</td>
                        <?php foreach ($alanlar as $alan => $ad): ?>
                        <td class="text-end"><?= (int)$topN[$alan] ?> iş</td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="rp-note">
            <strong>Tahmini ciro</strong> = sisteme girilen işlerin bedeli, her iş kendi para biriminde (potansiyel kazanç) ·
            <strong>Net ciro</strong> = "iş yapıldı" işlerin bedelinin tamamı + dışarıya verilen işlerin pass'ı ·
            <strong>Partner hakedişi</strong> = işi dışarıya verdiğimiz tutar ·
            <strong>Pass</strong> = iş bedeli − partner hakedişi (ör. 40 € iş, dışarıya 30 € → hakediş 30 €, pass 10 €).
            Dışarıya verilen ve "iş yapıldı" da işaretli iş net ciroya bir kez (pass olarak) girer.
            Para birimleri birbirine eklenmez.
        </div>
        <?php if ($kur && $h['cevrilen'] > 0): ?>
        <div class="rp-note mt-2" id="rpKur" data-kur="<?= e(json_encode($kur['kur'])) ?>" data-tarih="<?= e($kur['tarih']) ?>" data-cevrilen="<?= (int)$h['cevrilen'] ?>">
            <i class="bi bi-currency-exchange me-1"></i><strong>Kur:</strong> <?= e($kur['kaynak']) ?><?= $kur['tarih'] !== '' ? ' ' . e($kur['tarih']) : '' ?> —
            <?= e(rp_kur_yazi($kur, 'EUR')) ?> · <?= e(rp_kur_yazi($kur, 'USD')) ?> · <?= e(rp_kur_yazi($kur, 'GBP')) ?>.
            <?php if (!empty($kur['eski'])): ?><span class="text-warning-emphasis" id="rpKurEski">TCMB'ye şu an ulaşılamadı; en son alınan kur kullanıldı.</span><?php endif; ?>
            Hakedişi farklı para biriminde girilen <strong><?= (int)$h['cevrilen'] ?> işte</strong> iş bedeli bu güncel kurla hakediş para birimine çevrildi
            (ör. 40 € iş, 1.500 ₺ hakediş: 40 € × kur − 1.500 ₺ = pass ₺); pass o para biriminde net ciroya eklendi.
            Bu işlerin bedeli kendi para biriminin tahmini cirosunda kalır; bu yüzden bir para biriminin net cirosu tahmini cirosundan büyük olabilir.
            Kurla çevrilen tutarlar kuruşlu çıkabilir.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php rp_pb_secici($pbler, $aktifPb, $pbSayi); ?>

<?php foreach ($pbler as $pb):
    $ay = $h['aylar'][$pb] ?? [];
    krsort($ay);
    $tp = $h['tipler'][$pb] ?? [];
    $pt = $h['partner'][$pb] ?? [];
    uasort($pt, function ($a, $b) { return $b['n'] - $a['n'] ?: ($b['hakedis'] <=> $a['hakedis']); });
    $is = $h['isler'][$pb] ?? [];
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
                                    <?php foreach ($alanlar as $ad): ?><th class="text-end"><?= e($ad) ?></th><?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $tAy = rp_bos_alan();
                                foreach ($ay as $ym => $a):
                                    rp_alan_topla($tAy, $a); ?>
                                <tr>
                                    <td class="fw-semibold"><?= e(rp_ay_ad($ym)) ?></td>
                                    <td class="text-end"><?= (int)$a['tahmini']['n'] ?></td>
                                    <?php foreach ($alanlar as $alan => $ad): ?>
                                    <td class="text-end <?= $alan === 'net' ? 'rp-col-gercek' : '' ?>"><?= rp_tutar($a[$alan], $pb) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr data-toplam-n="<?= (int)$tAy['tahmini']['n'] ?>"<?php foreach ($alanlar as $alan => $ad): ?> data-toplam-<?= $alan ?>="<?= number_format($tAy[$alan]['t'], 2, '.', '') ?>"<?php endforeach; ?>>
                                    <td>TOPLAM</td>
                                    <td class="text-end"><?= (int)$tAy['tahmini']['n'] ?></td>
                                    <?php foreach ($alanlar as $alan => $ad): ?>
                                    <td class="text-end <?= $alan === 'net' ? 'rp-col-gercek' : '' ?>"><?= rp_tutar($tAy[$alan], $pb) ?></td>
                                    <?php endforeach; ?>
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
                                    <th class="text-end">Tahmini ciro</th>
                                    <th class="text-end">Net ciro</th>
                                    <th class="text-end">Pass</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $tTip = rp_bos_alan();
                                foreach (['arrival', 'depar', 'ara'] as $tk):
                                    if (!isset($tp[$tk])) continue;
                                    $t = $tp[$tk];
                                    rp_alan_topla($tTip, $t); ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($tipAd[$tk]) ?></td>
                                    <td class="text-end"><?= (int)$t['tahmini']['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($t['tahmini'], $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($t['net'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($t['pass'], $pb) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>TOPLAM</td>
                                    <td class="text-end"><?= (int)$tTip['tahmini']['n'] ?></td>
                                    <td class="text-end"><?= rp_tutar($tTip['tahmini'], $pb) ?></td>
                                    <td class="text-end rp-col-gercek"><?= rp_tutar($tTip['net'], $pb) ?></td>
                                    <td class="text-end"><?= rp_tutar($tTip['pass'], $pb) ?></td>
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
            <div class="text-center text-body-secondary py-4">Bu para biriminde partner hakedişi ya da pass yok.</div>
            <?php else:
                $pTop = ['n' => 0, 'hakedis' => 0.0, 'pass' => 0.0, 'passN' => 0];
                foreach ($pt as $p) foreach ($pTop as $kk => $_) $pTop[$kk] += $p[$kk]; ?>
            <div class="table-responsive">
                <table class="table table-hover rp-table rp-dt mb-0" data-pb="<?= e($pb) ?>">
                    <thead class="table-light">
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">İş</th>
                            <th class="text-end">Partner hakedişi</th>
                            <th class="text-end">Pass</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pt as $pad => $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($pad) ?></td>
                            <td class="text-end"><?= (int)$p['n'] ?></td>
                            <td class="text-end" data-order="<?= number_format($p['hakedis'], 2, '.', '') ?>"><?= $p['hakedis'] > 0 ? e(rp_para($p['hakedis'], $pb)) : '<span class="text-body-secondary">—</span>' ?></td>
                            <td class="text-end fw-semibold" data-order="<?= number_format($p['pass'], 2, '.', '') ?>"><?= rp_tutar(['n' => $p['passN'], 't' => $p['pass']], $pb) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr data-partner-toplam-n="<?= (int)$pTop['n'] ?>" data-partner-toplam-hakedis="<?= number_format($pTop['hakedis'], 2, '.', '') ?>" data-partner-toplam-pass="<?= number_format($pTop['pass'], 2, '.', '') ?>">
                            <td>TOPLAM (<?= count($pt) ?> partner)</td>
                            <td class="text-end"><?= (int)$pTop['n'] ?></td>
                            <td class="text-end"><?= e(rp_para($pTop['hakedis'], $pb)) ?></td>
                            <td class="text-end"><?= rp_tutar(['n' => $pTop['passN'], 't' => $pTop['pass']], $pb) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="rp-note mt-2">Pass = iş bedeli − partner hakedişi. Hakediş başka para biriminde girildiyse iş bedeli güncel kurla o para birimine çevrilir; pass hakedişin para biriminde görünür.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- İşlem listesi -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>İşlem listesi <span class="text-body-secondary fw-normal">· <?= e($isimPb) ?></span></h6></div>
        <div class="card-body">
            <?php if (!$is): ?>
            <div class="text-center text-body-secondary py-4">Bu para biriminde transfer yok.</div>
            <?php else:
                $asilN = 0;
                foreach ($is as $x) if (!$x['ek']) $asilN++; ?>
            <table class="table table-hover rp-table rp-isler mb-0 w-100" data-pb="<?= e($pb) ?>">
                <thead class="table-light">
                    <tr>
                        <th>Tarih</th>
                        <th>Rez. No</th>
                        <th>Tür</th>
                        <th>Müşteri</th>
                        <th>Güzergah</th>
                        <th>Durum</th>
                        <th class="text-end">İş bedeli</th>
                        <th class="text-end">Partner hakedişi</th>
                        <th class="text-end">Net ciroya giren</th>
                        <th>Partner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($is as $x):
                        // Bu dökümün (pb) toplamlarına giren kısımlar
                        $bedelBu = $x['ek'] ? 0.0 : $x['bedel'];
                        $hkBu = $x['hakedis'] && $x['hakedis'][0] === $pb ? $x['hakedis'][1] : 0.0;
                        $netBu = $x['net'] && $x['net'][0] === $pb ? $x['net'][1] : 0.0; ?>
                    <tr data-bedel="<?= number_format($bedelBu, 2, '.', '') ?>" data-hk="<?= number_format($hkBu, 2, '.', '') ?>" data-net="<?= number_format($netBu, 2, '.', '') ?>" data-ek="<?= $x['ek'] ? '1' : '0' ?>" data-k="<?= $x['k'] ?>" data-kad="<?= e($katAd[$x['k']]) ?>">
                        <td data-order="<?= e((string)$x['tarih']) ?>"><?= $x['tarih'] ? e(date('d.m.Y', strtotime($x['tarih']))) : '—' ?></td>
                        <td><?= e($x['no']) ?></td>
                        <td><?= e($tipAd[$x['tip']]) ?></td>
                        <td><?= e($x['musteri']) ?></td>
                        <td><?= e($x['guzergah']) ?></td>
                        <td><span class="badge bg-<?= $katRenk[$x['k']] ?>-subtle text-<?= $katRenk[$x['k']] ?>-emphasis"><?= e($katAd[$x['k']]) ?></span></td>
                        <td class="text-end" data-order="<?= number_format($bedelBu, 2, '.', '') ?>">
                            <?php if ($x['ek']): ?>
                            <span class="text-body-secondary"><?= e(rp_para($x['bedel'], $x['pb'])) ?></span><span class="rp-n"><?= e($x['pb']) ?> dökümünde</span>
                            <?php else: ?>
                            <?= e(rp_para($x['bedel'], $pb)) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end" data-order="<?= number_format($hkBu, 2, '.', '') ?>">
                            <?php if ($x['k'] !== 'disari'): ?>
                            <?php elseif (!$x['hakedis']): ?>
                            <span class="text-warning-emphasis" title="Verilen Tutar girilmemiş">girilmemiş</span>
                            <?php else: ?>
                            <?= e(rp_para($x['hakedis'][1], $x['hakedis'][0])) ?><?php if ($x['hakedis'][0] !== $pb): ?><span class="rp-n"><?= e($x['hakedis'][0]) ?> dökümünde</span><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold" data-order="<?= number_format($netBu, 2, '.', '') ?>">
                            <?php if ($x['k'] === 'bekleyen'): ?>
                            <span class="text-body-secondary fw-normal">— bekliyor</span>
                            <?php elseif ($x['kurYok']): ?>
                            <span class="text-warning-emphasis fw-normal" title="Güncel kur alınamadı">kur yok</span>
                            <?php elseif ($x['net'][0] !== $pb): ?>
                            <span class="text-body-secondary fw-normal"><?= e(rp_para($x['net'][1], $x['net'][0])) ?></span><span class="rp-n"><?= e($x['net'][0]) ?> dökümünde</span>
                            <?php else: ?>
                            <span class="<?= $netBu <= -0.005 ? 'text-danger' : '' ?>"><?= e(rp_para($netBu, $pb)) ?></span>
                            <?php if ($x['k'] === 'disari'): ?><span class="rp-n fw-normal">pass<?php if ($x['cevrim']): ?>: <?= e(rp_para($x['cevrim'][0], $x['cevrim'][1])) ?> = <?= e(rp_para($x['cevrim'][2], $x['cevrim'][3])) ?> − <?= e(rp_para($x['hk'], $x['hpb'])) ?><?php endif; ?></span><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e($x['partner']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="rp-f-n"><?= (int)$asilN ?> işlem</td>
                        <td colspan="4" class="rp-f-kat"></td>
                        <td class="text-end rp-f-bedel"></td>
                        <td class="text-end rp-f-hk"></td>
                        <td class="text-end rp-f-net"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="rp-note mt-2">
                Alttaki toplamlar, listede aradığınız / süzdüğünüz işlemlere göre güncellenir (bu para biriminde):
                iş bedeli toplamı = tahmini ciro, partner hakedişi toplamı = partner hakedişi, net toplamı = net ciro.
                "Hakediş" satırları: işin bedeli başka para biriminde, hakedişi ve pass'ı bu para biriminde.
            </div>
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
            ['bi-currency-exchange', 'Tutarı 0 olan transfer', $h['fiyatsiz'], 'Ciroya 0 olarak girdi; rezervasyondan fiyatı girin.'],
            ['bi-receipt', 'Dışarıya verilmiş, Verilen Tutar girilmemiş', $h['verilenEksik'], 'Hakediş düşülemedi, pass\'a işin bedelinin tamamı girdi; İş Durumu\'ndan Verilen Tutar\'ı girin.'],
            ['bi-exclamation-triangle', 'Pass eksi çıkan iş', $h['passEksi'], 'Verilen Tutar iş bedelinden fazla; tutar ya da para birimi yanlış girilmiş olabilir.'],
            ['bi-person-x', 'Dışarıya verilmiş, partner seçilmemiş', $h['partnerYok'], 'Partner dökümünde "(Partner girilmemiş)" olarak görünür.'],
            ['bi-x-circle', 'İptal edilen (hesaba katılmadı)', $say['iptal'], 'Silinen rezervasyonlar zaten hariç.'],
        ];
        ?>
        <div class="row g-3">
            <?php foreach ($kontroller as $kt): ?>
            <div class="col-12 col-md-6 col-xl">
                <div class="d-flex justify-content-between align-items-start gap-2 h-100 p-3 rounded border" data-kontrol="<?= e($kt[1]) ?>">
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
