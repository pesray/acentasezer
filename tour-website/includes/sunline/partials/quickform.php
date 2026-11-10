<?php
/**
 * Hızlı fiyat formu (NEREDEN / NEREYE / YOLCU) + güven rozetleri — temadaki .qf kutusu
 * İsteğe bağlı değişken: $preselect (bölge id) — transfer sayfasında "NEREYE" hazır seçili gelir
 */
$g = sl_section('global');
$dests = sl_bookable_destinations(false);
$maxPax = 1;
foreach (sl_vehicles() as $v) $maxPax = max($maxPax, (int)$v['cap']);
$maxPax = min(max($maxPax, 8), 20);
$pre = isset($preselect) ? (int)$preselect : 0;
$opts = function ($selectedAirport, $selectedDest) use ($dests) {
    $h = '<option value="">' . sl_e('sel') . '</option>';
    $h .= '<option value="ayt"' . ($selectedAirport ? ' selected' : '') . '>' . sl_e('airport') . '</option>';
    foreach ($dests as $d) {
        $h .= '<option value="' . (int)$d['id'] . '"' . ($selectedDest === (int)$d['id'] ? ' selected' : '') . '>' . e($d['place']) . '</option>';
    }
    return $h;
};
?>
<div class="qf">
  <div class="row g-3 align-items-end">
    <div class="col-md-4"><label class="d-block" for="qFrom"><?= sl_e('from') ?></label><select class="form-select" id="qFrom"><?= $opts($pre > 0, 0) ?></select></div>
    <div class="col-md-4"><label class="d-block" for="qTo"><?= sl_e('to') ?></label><select class="form-select" id="qTo"><?= $opts(false, $pre) ?></select></div>
    <div class="col-6 col-md-2"><label class="d-block" for="qPax"><?= sl_e('pax') ?></label><select class="form-select" id="qPax"><?php for ($i = 1; $i <= $maxPax; $i++): ?><option value="<?= $i ?>"><?= $i ?> <?= sl_e('person') ?></option><?php endfor; ?></select></div>
    <div class="col-6 col-md-2"><button type="button" class="btn btn-cta w-100" style="padding:.72rem" id="qBtn"><?= sl_e('see') ?></button></div>
  </div>
  <?php if (trim((string)($g['note'] ?? '')) !== ''): ?>
  <div class="qf-note" id="qNote"><i class="bi bi-qr-code" style="color:var(--accent)"></i><span><?= e($g['note']) ?></span></div>
  <?php else: ?>
  <div class="qf-note" id="qNote"><span></span></div>
  <?php endif; ?>
  <?php if (!empty($g['trust']) && is_array($g['trust'])): ?>
  <div class="trust">
    <?php foreach ($g['trust'] as $tr): if (trim((string)($tr['t'] ?? '')) === '') continue; ?>
    <span><i class="bi <?= e($tr['i'] ?? 'bi-check2-circle') ?>"></i><?= e($tr['t']) ?></span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
