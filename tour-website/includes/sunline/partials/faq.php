<?php
/**
 * SSS akordeonu. Değişken: $items = [['q' => ..., 'a' => ...], ...]
 */
if (empty($items)) return;
?>
<div class="faq-list">
  <?php $i = 0; foreach ($items as $it): if (trim((string)($it['q'] ?? '')) === '') continue; ?>
  <details class="faq-it"<?= $i === 0 ? ' open' : '' ?>>
    <summary><?= e($it['q']) ?></summary>
    <div class="faq-a"><?= sl_paragraphs($it['a'] ?? '') ?></div>
  </details>
  <?php $i++; endforeach; ?>
</div>
