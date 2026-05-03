<?php
/**
 * Çeviri Yönetimi — Admin Panel
 * translations tablosundaki tüm çevirileri gruplu şekilde yönet
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Aktif diller
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, sort_order")->fetchAll();
$defaultLang = 'tr';
foreach ($languages as $l) { if ($l['is_default']) { $defaultLang = $l['code']; break; } }

// POST — Kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        setFlashMessage('error', 'Güvenlik doğrulaması başarısız.');
        header('Location: ' . ADMIN_URL . '/translations.php');
        exit;
    }

    $action = $_POST['action'] ?? 'save';

    try {
        $db->beginTransaction();

        if ($action === 'save' && isset($_POST['trans']) && is_array($_POST['trans'])) {
            $upsertStmt = $db->prepare("
                INSERT INTO translations (language_code, trans_group, trans_key, trans_value)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE trans_value = VALUES(trans_value)
            ");
            foreach ($_POST['trans'] as $group => $keys) {
                foreach ($keys as $key => $langs) {
                    foreach ($langs as $langCode => $value) {
                        $upsertStmt->execute([$langCode, $group, $key, trim($value)]);
                    }
                }
            }
        }

        if ($action === 'add' && !empty($_POST['new_group']) && !empty($_POST['new_key'])) {
            $newGroup = trim($_POST['new_group']);
            $newKey   = trim($_POST['new_key']);
            $insertStmt = $db->prepare("
                INSERT IGNORE INTO translations (language_code, trans_group, trans_key, trans_value)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($languages as $lang) {
                $val = trim($_POST['new_values'][$lang['code']] ?? '');
                $insertStmt->execute([$lang['code'], $newGroup, $newKey, $val]);
            }
        }

        if ($action === 'delete' && !empty($_POST['del_group']) && !empty($_POST['del_key'])) {
            $delStmt = $db->prepare("DELETE FROM translations WHERE trans_group = ? AND trans_key = ?");
            $delStmt->execute([trim($_POST['del_group']), trim($_POST['del_key'])]);
        }

        $db->commit();
        setFlashMessage('success', 'Çeviriler kaydedildi.');
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }

    header('Location: ' . ADMIN_URL . '/translations.php' . (!empty($_GET['group']) ? '?group=' . urlencode($_GET['group']) : ''));
    exit;
}

// Tüm çevirileri çek ve grupla
$allTrans = $db->query("SELECT * FROM translations ORDER BY trans_group, trans_key, language_code")->fetchAll();
$grouped = [];
$groups  = [];
foreach ($allTrans as $row) {
    $grouped[$row['trans_group']][$row['trans_key']][$row['language_code']] = $row['trans_value'];
    if (!in_array($row['trans_group'], $groups)) {
        $groups[] = $row['trans_group'];
    }
}
sort($groups);

// Aktif grup filtresi
$activeGroup = $_GET['group'] ?? ($groups[0] ?? '');

// Grup ikonları
$groupIcons = [
    'header'  => 'bi-layout-text-window-reverse',
    'footer'  => 'bi-layout-sidebar-inset-reverse',
    'general' => 'bi-globe',
];

$pageTitle = 'Çeviri Yönetimi';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.trans-group-tabs {
    display: flex; gap: 6px; flex-wrap: wrap;
    background: var(--hover-bg); padding: 6px; border-radius: 10px; margin-bottom: 20px;
}
.trans-group-tab {
    padding: 7px 16px; border-radius: 8px; border: 0; background: transparent;
    font-size: .85rem; font-weight: 600; color: var(--text-muted); cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: all .18s;
    text-decoration: none;
}
.trans-group-tab:hover { color: var(--text-base); background: rgba(0,0,0,.04); }
.trans-group-tab.active { background: var(--sidebar-bg); color: var(--primary); box-shadow: var(--shadow-sm); }
[data-bs-theme="dark"] .trans-group-tab:hover { background: rgba(255,255,255,.06); }

.trans-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.trans-table th {
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--text-muted); padding: 10px 12px; border-bottom: 2px solid var(--sidebar-border);
    position: sticky; top: 0; background: var(--sidebar-bg); z-index: 1;
}
.trans-table td {
    padding: 6px 8px; border-bottom: 1px solid var(--sidebar-border); vertical-align: middle;
}
.trans-table .key-cell {
    font-family: 'Courier New', monospace; font-size: .82rem; font-weight: 600;
    color: var(--primary); white-space: nowrap; min-width: 160px;
}
.trans-table .lang-input {
    width: 100%; border: 1px solid transparent; background: transparent;
    padding: 6px 10px; border-radius: 6px; font-size: .85rem; color: var(--text-base);
    transition: all .15s;
}
.trans-table .lang-input:hover { border-color: var(--sidebar-border); background: var(--hover-bg); }
.trans-table .lang-input:focus {
    outline: none; border-color: var(--primary); background: var(--sidebar-bg);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.trans-table tr:hover td { background: var(--hover-bg); }
.btn-delete-key {
    border: 0; background: transparent; color: var(--text-muted); cursor: pointer;
    padding: 4px; border-radius: 4px; font-size: .85rem; transition: all .15s;
}
.btn-delete-key:hover { color: #ef4444; background: rgba(239,68,68,.08); }
.btn-auto-translate {
    border: 0; background: transparent; color: var(--text-muted); cursor: pointer;
    padding: 4px; border-radius: 4px; font-size: .85rem; transition: all .15s;
}
.btn-auto-translate:hover { color: #0ea5e9; background: rgba(14,165,233,.08); }
.btn-auto-translate.loading { pointer-events: none; opacity: .5; }
.btn-auto-translate .spinner-border { width: 14px; height: 14px; border-width: 2px; }
.btn-translate-all {
    font-size: .8rem; padding: 5px 12px; border-radius: 6px;
    display: inline-flex; align-items: center; gap: 5px;
}

.add-card {
    background: var(--sidebar-bg); border: 1px dashed var(--sidebar-border);
    border-radius: 10px; padding: 16px; margin-top: 16px;
}
.stats-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: .72rem;
    background: rgba(99,102,241,.1); color: var(--primary); padding: 2px 8px;
    border-radius: 20px; font-weight: 600;
}
</style>

<form method="post" id="transForm">
    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
    <input type="hidden" name="action" value="save" id="formAction">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-chat-square-text text-white" style="font-size:24px;"></i>
            </div>
            <div>
                <h1 class="h4 mb-0 fw-bold">Çeviri Yönetimi</h1>
                <small class="text-muted"><?= count($allTrans) ?> çeviri · <?= count($groups) ?> grup · <?= count($languages) ?> dil</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-info btn-translate-all" onclick="translateAllRows()" title="Tüm boş alanları otomatik çevir">
                <i class="bi bi-translate me-1"></i>Tümünü Çevir
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-2"></i>Kaydet
            </button>
        </div>
    </div>

    <!-- Grup Tabları -->
    <div class="trans-group-tabs">
        <?php foreach ($groups as $g): ?>
        <a href="?group=<?= urlencode($g) ?>" class="trans-group-tab <?= $g === $activeGroup ? 'active' : '' ?>">
            <i class="bi <?= $groupIcons[$g] ?? 'bi-tag' ?>"></i>
            <?= e($g) ?>
            <span class="stats-badge"><?= count($grouped[$g] ?? []) ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Çeviri Tablosu -->
    <?php if ($activeGroup && isset($grouped[$activeGroup])): ?>
    <div class="card" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive" style="max-height:65vh;overflow-y:auto;">
            <table class="trans-table">
                <thead>
                    <tr>
                        <th style="width:180px;">Anahtar</th>
                        <?php foreach ($languages as $lang): ?>
                        <th><?= e($lang['flag']) ?> <?= e($lang['native_name']) ?></th>
                        <?php endforeach; ?>
                        <th style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped[$activeGroup] as $key => $langValues): ?>
                    <tr>
                        <td class="key-cell"><?= e($key) ?></td>
                        <?php foreach ($languages as $lang): ?>
                        <td>
                            <input type="text" class="lang-input"
                                   name="trans[<?= e($activeGroup) ?>][<?= e($key) ?>][<?= e($lang['code']) ?>]"
                                   value="<?= e($langValues[$lang['code']] ?? '') ?>"
                                   placeholder="<?= e($lang['code']) ?>...">
                        </td>
                        <?php endforeach; ?>
                        <td class="text-nowrap">
                            <button type="button" class="btn-auto-translate" title="Bu satırı çevir" onclick="translateRow(this)">
                                <i class="bi bi-translate"></i>
                            </button>
                            <button type="button" class="btn-delete-key" title="Sil" onclick="deleteKey('<?= e($activeGroup) ?>', '<?= e($key) ?>')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php elseif (empty($groups)): ?>
    <div class="alert alert-info">Henüz çeviri eklenmemiş. Aşağıdan yeni çeviri ekleyebilirsiniz.</div>
    <?php endif; ?>
</form>

<!-- Yeni Çeviri Ekle -->
<div class="add-card">
    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2"></i>Yeni Çeviri Ekle</h6>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
        <input type="hidden" name="action" value="add">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Grup</label>
                <select name="new_group" class="form-select form-select-sm" id="newGroupSelect">
                    <?php foreach ($groups as $g): ?>
                    <option value="<?= e($g) ?>" <?= $g === $activeGroup ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                    <option value="__custom__">+ Yeni Grup</option>
                </select>
                <input type="text" class="form-control form-control-sm mt-1 d-none" id="customGroupInput" placeholder="Grup adı (snake_case)">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Anahtar</label>
                <input type="text" name="new_key" class="form-control form-control-sm" placeholder="çeviri_anahtarı" required>
            </div>
            <?php foreach ($languages as $lang): ?>
            <div class="col">
                <label class="form-label small fw-semibold"><?= e($lang['flag']) ?> <?= e($lang['code']) ?></label>
                <input type="text" name="new_values[<?= e($lang['code']) ?>]" class="form-control form-control-sm" placeholder="<?= e($lang['native_name']) ?>">
            </div>
            <?php endforeach; ?>
            <div class="col-auto d-flex gap-1">
                <button type="button" class="btn btn-outline-info btn-sm" onclick="translateNewRow(this)" title="Otomatik çevir">
                    <i class="bi bi-translate"></i>
                </button>
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Ekle</button>
            </div>
        </div>
    </form>
</div>

<!-- Sil Formu (hidden) -->
<form method="post" id="deleteForm">
    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="del_group" id="delGroup">
    <input type="hidden" name="del_key" id="delKey">
</form>

<script>
// Silme
function deleteKey(group, key) {
    if (!confirm('Bu çeviri anahtarını tüm dillerden silmek istediğinize emin misiniz?\n\n[' + group + '] ' + key)) return;
    document.getElementById('delGroup').value = group;
    document.getElementById('delKey').value = key;
    document.getElementById('deleteForm').submit();
}

// Yeni grup seçimi
document.getElementById('newGroupSelect').addEventListener('change', function() {
    var custom = document.getElementById('customGroupInput');
    if (this.value === '__custom__') {
        custom.classList.remove('d-none');
        custom.required = true;
        custom.focus();
    } else {
        custom.classList.add('d-none');
        custom.required = false;
    }
});

// Custom grup adını select'e aktar
document.getElementById('customGroupInput').addEventListener('input', function() {
    var select = document.getElementById('newGroupSelect');
    var opt = select.querySelector('option[value="__custom__"]');
    if (this.value) {
        opt.value = this.value;
        opt.textContent = '+ ' + this.value;
        select.value = this.value;
    }
});

// ============ DeepL Otomatik Çeviri ============
var CSRF_TOKEN = '<?= e(generateCSRFToken()) ?>';
var DEFAULT_LANG = '<?= e($defaultLang) ?>';
var ALL_LANGS = <?= json_encode(array_map(function($l){ return $l['code']; }, $languages)) ?>;

// Tek satırı çevir
function translateRow(btn) {
    var tr = btn.closest('tr');
    var inputs = tr.querySelectorAll('.lang-input');
    var sourceInput = null;
    var targets = [];

    inputs.forEach(function(inp) {
        var name = inp.name;
        var match = name.match(/\[([a-z]{2})\]$/);
        if (!match) return;
        var lang = match[1];
        if (lang === DEFAULT_LANG) {
            sourceInput = inp;
        } else {
            targets.push({ input: inp, lang: lang });
        }
    });

    if (!sourceInput || !sourceInput.value.trim()) {
        alert('Önce ' + DEFAULT_LANG.toUpperCase() + ' dilindeki metni yazın.');
        return;
    }

    var emptyTargets = targets.filter(function(t) { return !t.input.value.trim(); });
    if (emptyTargets.length === 0) emptyTargets = targets;

    var targetLangs = emptyTargets.map(function(t) { return t.lang; }).join(',');
    btn.classList.add('loading');
    btn.innerHTML = '<span class="spinner-border"></span>';

    var fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('entity', 'translations');
    fd.append('action', 'deepl_translate_batch');
    fd.append('text', sourceInput.value.trim());
    fd.append('source_lang', DEFAULT_LANG);
    fd.append('target_langs', targetLangs);

    fetch('<?= ADMIN_URL ?>/api/handler.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success && res.data && res.data.translations) {
                emptyTargets.forEach(function(t) {
                    var val = res.data.translations[t.lang];
                    if (val) {
                        t.input.value = val;
                        t.input.style.background = 'rgba(16,185,129,.08)';
                        setTimeout(function() { t.input.style.background = ''; }, 1500);
                    }
                });
            } else {
                alert(res.message || 'Çeviri hatası');
            }
        })
        .catch(function(err) { alert('Bağlantı hatası: ' + err.message); })
        .finally(function() {
            btn.classList.remove('loading');
            btn.innerHTML = '<i class="bi bi-translate"></i>';
        });
}

// Tüm satırları çevir (boş alanları doldur)
function translateAllRows() {
    var btns = document.querySelectorAll('.btn-auto-translate');
    var delay = 0;
    btns.forEach(function(btn) {
        var tr = btn.closest('tr');
        var inputs = tr.querySelectorAll('.lang-input');
        var hasSource = false;
        var hasEmpty = false;
        inputs.forEach(function(inp) {
            var match = inp.name.match(/\[([a-z]{2})\]$/);
            if (!match) return;
            if (match[1] === DEFAULT_LANG && inp.value.trim()) hasSource = true;
            if (match[1] !== DEFAULT_LANG && !inp.value.trim()) hasEmpty = true;
        });
        if (hasSource && hasEmpty) {
            setTimeout(function() { translateRow(btn); }, delay);
            delay += 350;
        }
    });
}

// Yeni satır çevir
function translateNewRow(btn) {
    var card = btn.closest('.add-card');
    var sourceInput = card.querySelector('input[name="new_values[' + DEFAULT_LANG + ']"]');
    if (!sourceInput || !sourceInput.value.trim()) {
        alert('Önce ' + DEFAULT_LANG.toUpperCase() + ' dilindeki metni yazın.');
        return;
    }

    var targets = [];
    ALL_LANGS.forEach(function(lang) {
        if (lang === DEFAULT_LANG) return;
        var inp = card.querySelector('input[name="new_values[' + lang + ']"]');
        if (inp) targets.push({ input: inp, lang: lang });
    });

    var targetLangs = targets.map(function(t) { return t.lang; }).join(',');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    var fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('entity', 'translations');
    fd.append('action', 'deepl_translate_batch');
    fd.append('text', sourceInput.value.trim());
    fd.append('source_lang', DEFAULT_LANG);
    fd.append('target_langs', targetLangs);

    fetch('<?= ADMIN_URL ?>/api/handler.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success && res.data && res.data.translations) {
                targets.forEach(function(t) {
                    var val = res.data.translations[t.lang];
                    if (val) t.input.value = val;
                });
            } else {
                alert(res.message || 'Çeviri hatası');
            }
        })
        .catch(function(err) { alert('Bağlantı hatası: ' + err.message); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-translate"></i>';
        });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
