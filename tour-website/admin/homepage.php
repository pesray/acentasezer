<?php
/**
 * Anasayfa & Tema Yönetimi (Sunline teması)
 *
 * - Tema renkleri: Claude Design "Tweaks" panelinin aynısı (Hazır Tema / Arka Plan / Kart Rengi / Vurgu)
 *   açık ve koyu tema için ayrı ayrı; ziyaretçinin cihazına göre otomatik geçiş.
 * - Anasayfa bölümleri ve site geneli metinler her dil için ayrı (TR / EN / DE / RU ...).
 * - Kayıt: sections (section_type = sun_*) + section_translations (dile özel ayarlar JSON)
 * - Bölge kartları ve fiyatlar Tur Yönetimi > Transferler ekranından gelir (burada düzenlenmez).
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once INCLUDES_PATH . 'sunline/core.php';

$db = getDB();
$homepage = $db->query("SELECT * FROM pages WHERE is_homepage = 1 LIMIT 1")->fetch();
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, sort_order")->fetchAll();
$defaultLang = 'tr';
foreach ($languages as $l) { if ($l['is_default']) { $defaultLang = $l['code']; break; } }
$types = sl_section_types();
$typeLabels = [
    'global' => 'Genel', 'hero' => 'Hero', 'regions' => 'Bölgeler & Fiyatlar', 'info' => 'Bilgilendirme',
    'guests' => 'Misafirlerimiz', 'reviews' => 'Google Yorumları', 'blog' => 'Blog', 'faq' => 'Sıkça Sorulan Sorular',
];

function hp_str($v, $max = 2000) {
    return mb_substr(trim((string)$v), 0, $max);
}

function hp_rows($arr, $fields, $max = 60) {
    $out = [];
    if (!is_array($arr)) return $out;
    foreach ($arr as $r) {
        if (!is_array($r)) continue;
        $row = [];
        $empty = true;
        foreach ($fields as $f => $len) {
            $row[$f] = hp_str($r[$f] ?? '', $len);
            if ($row[$f] !== '') $empty = false;
        }
        if (!$empty) $out[] = $row;
        if (count($out) >= $max) break;
    }
    return $out;
}

function hp_icon($v) {
    $v = trim((string)$v);
    return preg_match('/^bi-[a-z0-9-]+$/', $v) ? $v : 'bi-check2-circle';
}

/* ======================================================================
 * KAYDET
 * ====================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $homepage) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.');
        header('Location: ' . ADMIN_URL . '/homepage.php');
        exit;
    }

    try {
        $db->beginTransaction();

        $existing = [];
        $st = $db->prepare("SELECT id, section_type FROM sections WHERE page_id = ? ORDER BY id");
        $st->execute([$homepage['id']]);
        foreach ($st->fetchAll() as $r) {
            if (!isset($existing[$r['section_type']])) $existing[$r['section_type']] = (int)$r['id'];
        }

        $insSec = $db->prepare("INSERT INTO sections (page_id, section_key, section_type, title, settings, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $updSec = $db->prepare("UPDATE sections SET settings = ?, is_active = ? WHERE id = ?");
        $upTr = $db->prepare("
            INSERT INTO section_translations (section_id, language_code, title, settings)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title), settings = VALUES(settings)
        ");

        $presets = sl_theme_presets();
        $B = isset($_POST['base']) && is_array($_POST['base']) ? $_POST['base'] : [];
        $TR = isset($_POST['tr']) && is_array($_POST['tr']) ? $_POST['tr'] : [];

        foreach ($types as $i => $type) {
            $stype = 'sun_' . $type;
            if (!isset($existing[$stype])) {
                $insSec->execute([$homepage['id'], $stype, $stype, 'Sunline · ' . $typeLabels[$type], '{}', 100 + $i]);
                $existing[$stype] = (int)$db->lastInsertId();
            }
            $sid = $existing[$stype];
            $b = isset($B[$type]) && is_array($B[$type]) ? $B[$type] : [];

            // ---- dilden bağımsız ayarlar
            $base = [];
            if ($type === 'global') {
                $pal = function ($p, $fb) use ($presets) {
                    $p = is_array($p) ? $p : [];
                    $preset = isset($presets[$p['preset'] ?? '']) ? $p['preset'] : '';
                    return [
                        'preset' => $preset,
                        'bg' => sl_hex($p['bg'] ?? '', $fb['bg']),
                        'card' => sl_hex($p['card'] ?? '', $fb['card']),
                        'accent' => sl_hex($p['accent'] ?? '', $fb['accent']),
                    ];
                };
                // Para birimi & ekstralar: sitede tek listelenen para birimi + araçta nakit ödeme seçenekleri
                $cur = strtoupper(trim((string)($b['currency'] ?? 'EUR')));
                if (!preg_match('/^[A-Z]{3}$/', $cur)) $cur = 'EUR';
                $payC = [];
                foreach ((array)($b['pay_currencies'] ?? []) as $pc) {
                    $pc = strtoupper(trim((string)$pc));
                    if (preg_match('/^[A-Z]{3}$/', $pc)) $payC[] = $pc;
                }
                if (!in_array($cur, $payC, true)) array_unshift($payC, $cur);
                $feeMap = function ($m) {
                    $o = [];
                    foreach ((array)$m as $fc => $fv) {
                        $fc = strtoupper(trim((string)$fc));
                        $fv = str_replace(',', '.', trim((string)$fv));
                        if (preg_match('/^[A-Z]{3}$/', $fc) && $fv !== '' && is_numeric($fv) && (float)$fv >= 0) $o[$fc] = round((float)$fv, 2);
                    }
                    return $o;
                };
                $base = [
                    'mode' => in_array($b['mode'] ?? '', ['auto', 'light', 'dark'], true) ? $b['mode'] : 'auto',
                    'toggle' => !empty($b['toggle']) ? 1 : 0,
                    'light' => $pal($b['light'] ?? [], $presets['krem_siyah']),
                    'dark' => $pal($b['dark'] ?? [], $presets['gece']),
                    'logo_light' => hp_str($b['logo_light'] ?? '', 255),
                    'logo_dark' => hp_str($b['logo_dark'] ?? '', 255),
                    'discount' => max(0, min(90, (float)str_replace(',', '.', (string)($b['discount'] ?? 0)))),
                    'whatsapp' => preg_replace('/[^\d+]/', '', (string)($b['whatsapp'] ?? '')),
                    'currency' => $cur,
                    'pay_currencies' => array_values(array_unique($payC)),
                    'free_seats' => !empty($b['free_seats']) ? 1 : 0,
                    'fee_per_leg' => (string)($b['fee_per_leg'] ?? '1') === '0' ? 0 : 1,
                    'seat_fee' => $feeMap($b['seat_fee'] ?? []),
                    'booster_fee' => $feeMap($b['booster_fee'] ?? []),
                ];
            } elseif ($type === 'hero') {
                $mz = [];
                foreach ((array)($b['mosaic'] ?? []) as $p) {
                    $p = hp_str($p, 500);
                    if ($p !== '' && !in_array($p, $mz, true)) $mz[] = $p;
                }
                $base = [
                    'image' => hp_str($b['image'] ?? '', 500),
                    'style' => (string)($b['style'] ?? '') === 'mosaic' ? 'mosaic' : 'image',
                    'brand' => hp_str($b['brand'] ?? '', 60),
                    'mosaic' => array_slice($mz, 0, 60),
                ];
            } elseif ($type === 'regions') {
                $filters = [];
                foreach ((array)($b['filters'] ?? []) as $f) {
                    if (!is_array($f)) continue;
                    $k = preg_replace('/[^a-z0-9_-]/', '', strtolower(hp_str($f['k'] ?? '', 30)));
                    if ($k === '' || $k === 'all' || $k === 'x') continue;
                    $labels = [];
                    foreach ($languages as $l) $labels[$l['code']] = hp_str($f['l'][$l['code']] ?? '', 40);
                    $filters[] = ['k' => $k, 'm' => hp_str($f['m'] ?? '', 1000), 'l' => $labels];
                }
                $all = [];
                foreach ($languages as $l) $all[$l['code']] = hp_str($b['all_label'][$l['code']] ?? '', 40);
                $base = ['only_featured' => !empty($b['only_featured']) ? 1 : 0, 'filters' => $filters, 'all_label' => $all];
            } elseif ($type === 'guests') {
                $photos = [];
                foreach ((array)($b['photos'] ?? []) as $p) {
                    $p = hp_str($p, 500);
                    if ($p !== '') $photos[] = $p;
                }
                $base = ['photos' => array_slice($photos, 0, 40)];
            } elseif ($type === 'reviews') {
                $pos = (string)($b['position'] ?? 'after_blog');
                $rt = str_replace(',', '.', trim((string)($b['rating'] ?? '')));
                $rt = ($rt !== '' && is_numeric($rt) && (float)$rt > 0) ? number_format(min(5, (float)$rt), 1, '.', '') : '';
                $url = hp_str($b['url'] ?? '', 500);
                if ($url !== '' && !preg_match('#^https://#i', $url)) $url = '';
                $base = [
                    'position' => in_array($pos, ['after_info', 'after_blog', 'after_faq'], true) ? $pos : 'after_blog',
                    'rating' => $rt,
                    'count' => max(0, min(100000, (int)($b['count'] ?? 0))),
                    'url' => $url,
                    'limit' => max(1, min(50, (int)($b['limit'] ?? 12))),
                ];
            } elseif ($type === 'blog') {
                $base = ['limit' => max(1, min(12, (int)($b['limit'] ?? 6)))];
            }

            $active = $type === 'global' ? 1 : (!empty($_POST['active'][$type]) ? 1 : 0);
            $updSec->execute([json_encode($base, JSON_UNESCAPED_UNICODE), $active, $sid]);

            // ---- dile bağlı içerik
            foreach ($languages as $l) {
                $code = $l['code'];
                if (!isset($TR[$code][$type]) || !is_array($TR[$code][$type])) continue;
                $L = $TR[$code][$type];
                $s = [];
                switch ($type) {
                    case 'global':
                        $s = [
                            'promo' => hp_rows($L['promo'] ?? [], ['b' => 80, 't' => 200]),
                            'nav' => hp_rows($L['nav'] ?? [], ['l' => 60, 'u' => 300]),
                            'cta_label' => hp_str($L['cta_label'] ?? '', 60),
                            'cta_url' => hp_str($L['cta_url'] ?? '', 300),
                            'note' => hp_str($L['note'] ?? '', 300),
                            'trust' => array_map(function ($r) { $r['i'] = hp_icon($r['i']); return $r; }, hp_rows($L['trust'] ?? [], ['i' => 60, 't' => 120], 8)),
                            'footer_about' => hp_str($L['footer_about'] ?? '', 600),
                            'footer_links' => hp_rows($L['footer_links'] ?? [], ['l' => 80, 'u' => 300], 12),
                            'keywords' => hp_str($L['keywords'] ?? '', 4000),
                        ];
                        break;
                    case 'hero':
                    case 'regions':
                        $s = ['eyebrow' => hp_str($L['eyebrow'] ?? '', 120), 'title' => hp_str($L['title'] ?? '', 160), 'subtitle' => hp_str($L['subtitle'] ?? '', 400)];
                        break;
                    case 'info':
                        $s = [
                            'eyebrow' => hp_str($L['eyebrow'] ?? '', 120), 'title' => hp_str($L['title'] ?? '', 160),
                            'subtitle' => hp_str($L['subtitle'] ?? '', 1200), 'sign' => hp_str($L['sign'] ?? '', 200),
                            'items' => array_map(function ($r) { $r['i'] = hp_icon($r['i']); return $r; }, hp_rows($L['items'] ?? [], ['i' => 60, 't' => 160, 'p' => 1200], 12)),
                        ];
                        break;
                    case 'guests':
                    case 'blog':
                        $s = ['eyebrow' => hp_str($L['eyebrow'] ?? '', 120), 'title' => hp_str($L['title'] ?? '', 160)];
                        break;
                    case 'reviews':
                        $s = [
                            'eyebrow' => hp_str($L['eyebrow'] ?? '', 120), 'title' => hp_str($L['title'] ?? '', 160),
                            'subtitle' => hp_str($L['subtitle'] ?? '', 400), 'link_label' => hp_str($L['link_label'] ?? '', 80),
                        ];
                        break;
                    case 'faq':
                        $s = ['eyebrow' => hp_str($L['eyebrow'] ?? '', 120), 'title' => hp_str($L['title'] ?? '', 160), 'items' => hp_rows($L['items'] ?? [], ['q' => 300, 'a' => 3000], 30)];
                        break;
                }
                $title = $s['title'] ?? ($typeLabels[$type] ?? $type);
                $upTr->execute([$sid, $code, mb_substr((string)$title, 0, 250), json_encode($s, JSON_UNESCAPED_UNICODE)]);
            }
        }

        // ---- Anasayfa SEO (page_translations)
        $seoStmt = $db->prepare("
            INSERT INTO page_translations (page_id, language_code, title, slug, meta_title, meta_description, meta_keywords)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title), meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description), meta_keywords = VALUES(meta_keywords)
        ");
        foreach ((array)($_POST['seo'] ?? []) as $code => $seo) {
            if (!is_array($seo)) continue;
            $mt = hp_str($seo['meta_title'] ?? '', 120);
            $seoStmt->execute([
                $homepage['id'], $code, $mt !== '' ? $mt : ($homepage['title'] ?: 'Anasayfa'),
                $code === $defaultLang ? '/' : 'home-' . $code,
                $mt !== '' ? $mt : null,
                hp_str($seo['meta_description'] ?? '', 320) ?: null,
                hp_str($seo['meta_keywords'] ?? '', 255) ?: null,
            ]);
        }

        $db->commit();
        require_once INCLUDES_PATH . 'indexnow.php';
        setFlashMessage('success', 'Anasayfa ve tema ayarları kaydedildi.' . indexnow_ping('home'));
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        setFlashMessage('error', 'Kaydedilemedi: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/homepage.php' . (!empty($_POST['_tab']) ? '#' . preg_replace('/[^a-z0-9-]/', '', $_POST['_tab']) : ''));
    exit;
}

/* ======================================================================
 * GÖRÜNÜM
 * ====================================================================== */
$pageTitle = 'Anasayfa & Tema';
require_once __DIR__ . '/includes/header.php';

if (!$homepage) {
    echo '<div class="alert alert-danger">Anasayfa bulunamadı: <code>pages</code> tablosunda <code>is_homepage = 1</code> olan kayıt yok.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Mevcut (varsayılanlarla birleşmiş) değerler
$val = [];
foreach ($languages as $l) {
    foreach ($types as $type) $val[$l['code']][$type] = sl_section($type, $l['code']);
}
$baseVal = [];
foreach ($types as $type) $baseVal[$type] = sl_section($type, $defaultLang);
$theme = sl_theme_config();
$G = $baseVal['global'];
$lightPreset = $G['light']['preset'] ?? '';
$darkPreset = $G['dark']['preset'] ?? '';

$seoData = [];
$st = $db->prepare("SELECT language_code, meta_title, meta_description, meta_keywords FROM page_translations WHERE page_id = ?");
$st->execute([$homepage['id']]);
foreach ($st->fetchAll() as $r) $seoData[$r['language_code']] = $r;

$presets = sl_theme_presets();
$swatches = sl_theme_swatches();
$featuredCount = count(sl_bookable_destinations(true));

// Para birimi & ekstralar: Transferler ekranında hangi para biriminde kaç transferin fiyatı var
$curList = sl_known_currencies();
$curStats = [];
try {
    $cst = $db->query("SELECT UPPER(TRIM(currency)) AS c, COUNT(DISTINCT destination_id) AS n FROM destination_vehicles WHERE price > 0 GROUP BY UPPER(TRIM(currency))");
    foreach ($cst->fetchAll() as $r) {
        if (!preg_match('/^[A-Z]{3}$/', (string)$r['c'])) continue;
        $curStats[$r['c']] = (int)$r['n'];
        if (!in_array($r['c'], $curList, true)) $curList[] = $r['c'];
    }
} catch (Exception $e) {}
$defCur = sl_currency();
if (!in_array($defCur, $curList, true)) $curList[] = $defCur;
$payList = sl_pay_currencies();
$feeCfg = sl_fees();

/** Tekrarlanabilir satır listesi */
function hp_repeater($base, $rows, $fields, $addLabel, $minRows = 0) {
    $render = function ($row, $i) use ($base, $fields) {
        $h = '<div class="rep-row"><div class="row g-2 align-items-start">';
        foreach ($fields as $f) {
            $name = $base . '[' . $i . '][' . str_replace('.', '][', $f['f']) . ']';
            $v = $row;
            foreach (explode('.', $f['f']) as $part) $v = is_array($v) && isset($v[$part]) ? $v[$part] : '';
            $v = is_array($v) ? '' : (string)$v;
            $h .= '<div class="' . $f['col'] . '">';
            if (!empty($f['label'])) $h .= '<label class="form-label-sm">' . e($f['label']) . '</label>';
            if (($f['type'] ?? 'text') === 'textarea') {
                $h .= '<textarea class="form-control form-control-sm" rows="' . (int)($f['rows'] ?? 2) . '" data-f="' . e($f['f']) . '" name="' . e($name) . '">' . e($v) . '</textarea>';
            } elseif (($f['type'] ?? '') === 'icon') {
                $h .= '<div class="input-group input-group-sm"><span class="input-group-text ico-prev"><i class="bi ' . e($v ?: 'bi-question') . '"></i></span><input class="form-control ico-in" list="biIcons" data-f="' . e($f['f']) . '" name="' . e($name) . '" value="' . e($v) . '" placeholder="bi-..."></div>';
            } else {
                $h .= '<input class="form-control form-control-sm" data-f="' . e($f['f']) . '" name="' . e($name) . '" value="' . e($v) . '"' . (!empty($f['ph']) ? ' placeholder="' . e($f['ph']) . '"' : '') . '>';
            }
            $h .= '</div>';
        }
        $h .= '<div class="col-auto rep-tools"><button type="button" class="btn btn-sm btn-light rep-up" title="Yukarı"><i class="bi bi-arrow-up"></i></button><button type="button" class="btn btn-sm btn-light rep-down" title="Aşağı"><i class="bi bi-arrow-down"></i></button><button type="button" class="btn btn-sm btn-outline-danger rep-del" title="Sil"><i class="bi bi-trash"></i></button></div>';
        return $h . '</div></div>';
    };
    $rows = is_array($rows) ? array_values($rows) : [];
    while (count($rows) < $minRows) $rows[] = [];
    $out = '<div class="rep" data-base="' . e($base) . '"><div class="rep-rows">';
    foreach ($rows as $i => $r) $out .= $render($r, $i);
    $out .= '</div><template class="rep-tpl">' . $render([], '__i__') . '</template>';
    $out .= '<button type="button" class="btn btn-sm btn-outline-primary rep-add mt-2"><i class="bi bi-plus-lg me-1"></i>' . e($addLabel) . '</button></div>';
    return $out;
}

function hp_text($name, $value, $ph = '', $max = 0) {
    return '<input class="form-control" name="' . e($name) . '" value="' . e((string)$value) . '"' . ($ph !== '' ? ' placeholder="' . e($ph) . '"' : '') . ($max ? ' maxlength="' . (int)$max . '"' : '') . '>';
}

function hp_area($name, $value, $rows = 3, $ph = '') {
    return '<textarea class="form-control" rows="' . (int)$rows . '" name="' . e($name) . '"' . ($ph !== '' ? ' placeholder="' . e($ph) . '"' : '') . '>' . e((string)$value) . '</textarea>';
}

function hp_card_open($id, $icon, $color, $title, $summary, $activeName = null, $active = true) {
    $h = '<div class="hp-card" id="' . e($id) . '"><div class="hp-head" role="button" tabindex="0">';
    $h .= '<div class="d-flex align-items-center gap-3"><span class="hp-ico" style="background:' . e($color) . '"><i class="bi ' . e($icon) . '"></i></span><div><div class="hp-title">' . e($title) . '</div><div class="hp-sum">' . e($summary) . '</div></div></div>';
    $h .= '<div class="d-flex align-items-center gap-3">';
    if ($activeName) {
        $h .= '<label class="toggle-switch mb-0" onclick="event.stopPropagation()"><input type="checkbox" name="' . e($activeName) . '" value="1"' . ($active ? ' checked' : '') . '><span class="toggle-slider"></span></label>';
    }
    $h .= '<i class="bi bi-chevron-down hp-chev"></i></div></div><div class="hp-body">';
    return $h;
}
?>
<style>
.hp-top{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
.hp-sticky{position:sticky;top:64px;z-index:20;background:var(--bs-body-bg);padding:10px 0;margin-bottom:10px;border-bottom:1px solid var(--sidebar-border)}
.lang-tabs-bar{display:flex;gap:4px;background:var(--hover-bg);padding:4px;border-radius:10px;flex-wrap:wrap}
.lang-tab-btn{background:transparent;border:0;padding:6px 14px;border-radius:7px;font-size:.85rem;font-weight:600;color:var(--text-muted);display:inline-flex;align-items:center;gap:6px}
.lang-tab-btn.active{background:var(--sidebar-bg);color:var(--primary);box-shadow:var(--shadow-sm)}
.lang-pane{display:none}.lang-pane.active{display:block}
.hp-card{border:1px solid var(--sidebar-border);border-radius:14px;margin-bottom:14px;background:var(--sidebar-bg);overflow:hidden}
.hp-head{padding:14px 18px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:var(--hover-bg);gap:10px}
.hp-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;flex-shrink:0}
.hp-title{font-weight:700;font-size:15px}
.hp-sum{font-size:.8rem;color:var(--text-muted)}
.hp-body{display:none;padding:18px;border-top:1px solid var(--sidebar-border)}
.hp-card.open .hp-body{display:block}
.hp-chev{transition:transform .2s;color:var(--text-muted)}
.hp-card.open .hp-chev{transform:rotate(180deg)}
.form-label-sm{font-size:11.5px;font-weight:700;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.3px;display:block}
.hp-note{font-size:.8rem;color:var(--text-muted)}
.hp-sub{font-weight:700;font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin:18px 0 8px}
.rep-row{border:1px dashed var(--sidebar-border);border-radius:10px;padding:10px;margin-bottom:8px;background:var(--bs-body-bg)}
.rep-tools{display:flex;gap:4px;padding-top:20px}
.toggle-switch{position:relative;width:44px;height:24px;flex-shrink:0;display:inline-block}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;transition:.3s;border-radius:24px}
.toggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:#fff;transition:.3s;border-radius:50%}
input:checked+.toggle-slider{background:#10b981}
input:checked+.toggle-slider:before{transform:translateX(20px)}
/* Tweaks (Claude Design) kopyası */
.tw{background:#141416;color:#eee;border:1px solid rgba(255,255,255,.12);border-radius:16px;box-shadow:0 12px 34px rgba(0,0,0,.25);overflow:hidden;height:100%}
.tw header{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.1);font-weight:700}
.tw section{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06)}
.tw section:last-child{border-bottom:0}
.tw h6{font-size:.68rem;letter-spacing:.16em;color:#999;text-transform:uppercase;margin:0 0 8px}
.tw .sw{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.tw .sw button{width:30px;height:30px;border-radius:8px;border:2px solid rgba(255,255,255,.15);padding:0}
.tw .sw button.on{border-color:#fff;outline:2px solid #888;outline-offset:1px}
.tw .sw input[type=color]{width:30px;height:30px;border:2px dashed rgba(255,255,255,.3);border-radius:8px;padding:0;background:none;cursor:pointer}
.tw .pre{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.tw .pre button{display:flex;align-items:center;gap:7px;background:#1e1e21;border:1px solid rgba(255,255,255,.1);color:#ddd;border-radius:9px;padding:6px 8px;font-size:.75rem;text-align:left}
.tw .pre button.on{border-color:#fff}
.tw .pre i{width:22px;height:14px;border-radius:4px;flex-shrink:0;display:inline-block}
.tw .hexv{font-family:monospace;font-size:.72rem;color:#aaa;margin-left:4px}
.pv{border-radius:14px;overflow:hidden;border:1px solid rgba(0,0,0,.15);background:var(--bg);color:var(--text);font-family:system-ui,sans-serif;margin-top:12px}
.pv-h{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-bottom:1px solid var(--line);font-size:.72rem}
.pv-h b{color:var(--acc-t)}
.pv-h span{background:linear-gradient(135deg,var(--cta1),var(--cta2));color:#0b0b0c;font-weight:800;border-radius:7px;padding:3px 8px}
.pv-b{padding:12px}
.pv-e{font-size:.6rem;letter-spacing:.2em;font-weight:800;color:var(--acc-t);text-transform:uppercase}
.pv-t{font-weight:800;font-size:1rem;margin:2px 0 8px}
.pv-c{background:linear-gradient(135deg,var(--card),var(--card2));border-radius:12px;padding:10px;color:var(--ctext)}
.pv-c small{color:var(--red);font-weight:800;font-size:.6rem;letter-spacing:.14em}
.pv-r{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin:6px 0}
.pv-r div{border:1px solid var(--cline);border-radius:8px;padding:6px;font-size:.6rem;font-weight:800;color:var(--cmuted)}
.pv-r div:first-child{border-color:var(--acc-c)}
.pv-r b{display:block;color:var(--acc-c);font-size:1rem}
.pv-btn{background:linear-gradient(135deg,var(--cta1),var(--cta2));color:#0b0b0c;text-align:center;font-weight:800;border-radius:9px;padding:6px;font-size:.75rem}
.mode-opt{display:flex;gap:8px;flex-wrap:wrap}
.mode-opt label{border:1px solid var(--sidebar-border);border-radius:10px;padding:8px 12px;cursor:pointer;font-weight:600;font-size:.88rem}
.mode-opt input{margin-right:6px}
.gst-list{display:flex;flex-wrap:wrap;gap:10px}
.gst-it{position:relative;width:96px;height:128px;border-radius:10px;overflow:hidden;border:1px solid var(--sidebar-border);background:var(--hover-bg)}
.gst-it img{width:100%;height:100%;object-fit:cover}
.gst-it button{position:absolute;top:4px;right:4px;width:24px;height:24px;border-radius:50%;border:0;background:rgba(0,0,0,.65);color:#fff;font-size:.8rem;line-height:1}
.gst-add{width:96px;height:128px;border-radius:10px;border:2px dashed var(--sidebar-border);background:transparent;color:var(--text-muted);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;font-size:.8rem}
.img-prev{max-height:70px;border-radius:8px;margin-top:6px;border:1px solid var(--sidebar-border)}
.gst-list .mz-it,.gst-list .mz-add{width:84px;height:84px}
.mz-big{position:absolute;left:4px;bottom:4px;background:#dc3545;color:#fff;font-size:.65rem;font-weight:600;padding:1px 6px;border-radius:6px}
.char-count{font-size:11px;color:var(--text-muted)}
.char-count.over{color:#dc2626;font-weight:700}
.cur-chk{border:1px solid var(--sidebar-border);border-radius:10px;padding:8px 12px;cursor:pointer;font-weight:600;font-size:.88rem;display:inline-flex;align-items:center;gap:8px;margin:0}
.cur-chk small{color:var(--text-muted);font-weight:500}
.fee-tbl{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--sidebar-border);border-radius:12px;overflow:hidden}
.fee-tbl th,.fee-tbl td{padding:8px 10px;border-bottom:1px solid var(--sidebar-border);vertical-align:middle}
.fee-tbl tr:last-child td{border-bottom:0}
.fee-tbl th{font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.3px;background:var(--hover-bg)}
.fee-tbl td:first-child{font-weight:700;white-space:nowrap}
.fee-tbl .form-control{max-width:140px}
</style>

<datalist id="biIcons">
  <?php foreach (['bi-whatsapp','bi-camera-video','bi-qr-code-scan','bi-wifi','bi-cash-coin','bi-shield-check','bi-cash-stack','bi-arrow-counterclockwise','bi-person-arms-up','bi-airplane','bi-airplane-engines','bi-headset','bi-car-front','bi-clock','bi-star','bi-geo-alt','bi-telephone','bi-check2-circle','bi-award','bi-snow','bi-briefcase','bi-people','bi-translate','bi-credit-card','bi-hand-thumbs-up','bi-gem','bi-lightning-charge','bi-map'] as $ic): ?>
  <option value="<?= $ic ?>"></option>
  <?php endforeach; ?>
</datalist>

<form method="post" id="hpForm" autocomplete="off">
  <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
  <input type="hidden" name="_tab" id="hpTab" value="">

  <div class="hp-top">
    <div class="d-flex align-items-center gap-3">
      <div style="width:48px;height:48px;background:linear-gradient(135deg,#c9a227,#FFD700);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="bi bi-palette text-dark" style="font-size:24px"></i></div>
      <div>
        <h1 class="h4 mb-0 fw-bold">Anasayfa & Tema</h1>
        <small class="text-muted">Sunline teması · renkler, anasayfa bölümleri, menü, footer ve SEO — her dil için ayrı</small>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= e(SITE_URL) ?>/" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>Siteyi Aç</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Tümünü Kaydet</button>
    </div>
  </div>

  <div class="hp-sticky d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <div class="lang-tabs-bar" id="langTabsBar">
      <?php foreach ($languages as $i => $l): ?>
      <button type="button" class="lang-tab-btn <?= $i === 0 ? 'active' : '' ?>" data-lang="<?= e($l['code']) ?>"><span><?= e($l['flag']) ?></span><?= e($l['native_name']) ?><?php if ($l['is_default']): ?><span class="badge bg-primary ms-1" style="font-size:.6rem">Varsayılan</span><?php endif; ?></button>
      <?php endforeach; ?>
    </div>
    <span class="hp-note"><i class="bi bi-info-circle me-1"></i>Dil sekmesi; menü, metin ve SSS alanlarının hangi dilde düzenlendiğini belirler. Renkler ve görseller tüm diller için ortaktır.</span>
  </div>

  <?php /* ============================ TEMA RENKLERİ ============================ */ ?>
  <?= hp_card_open('tema', 'bi-palette2', '#c9a227', 'Tema Renkleri', 'Açık ve koyu tema · Claude Design Tweaks paneli · ziyaretçinin cihazına göre otomatik') ?>
    <div class="mb-3">
      <span class="form-label-sm">Görünüm</span>
      <div class="mode-opt">
        <label><input type="radio" name="base[global][mode]" value="auto" <?= $theme['mode'] === 'auto' ? 'checked' : '' ?>>Otomatik (cihaza göre açık/koyu)</label>
        <label><input type="radio" name="base[global][mode]" value="light" <?= $theme['mode'] === 'light' ? 'checked' : '' ?>>Her zaman açık</label>
        <label><input type="radio" name="base[global][mode]" value="dark" <?= $theme['mode'] === 'dark' ? 'checked' : '' ?>>Her zaman koyu</label>
      </div>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" role="switch" id="twToggle" name="base[global][toggle]" value="1" <?= $theme['toggle'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="twToggle">Sitenin üst menüsünde ziyaretçiye açık/koyu tema düğmesi göster (Otomatik modda)</label>
      </div>
    </div>
    <div class="row g-3">
      <?php foreach (['light' => ['☀️ Açık Tema', $lightPreset], 'dark' => ['🌙 Koyu Tema', $darkPreset]] as $pk => $pInfo): $pal = $theme[$pk]; ?>
      <div class="col-xl-6">
        <div class="tw" data-pal="<?= $pk ?>">
          <header><span><?= $pInfo[0] ?></span><span class="hexv" data-hex></span></header>
          <input type="hidden" name="base[global][<?= $pk ?>][preset]" value="<?= e($pInfo[1]) ?>" data-k="preset">
          <input type="hidden" name="base[global][<?= $pk ?>][bg]" value="<?= e($pal['bg']) ?>" data-k="bg">
          <input type="hidden" name="base[global][<?= $pk ?>][card]" value="<?= e($pal['card']) ?>" data-k="card">
          <input type="hidden" name="base[global][<?= $pk ?>][accent]" value="<?= e($pal['accent']) ?>" data-k="accent">
          <section><h6>Hazır Tema</h6><div class="pre">
            <?php foreach ($presets as $key => $p): ?>
            <button type="button" data-preset="<?= e($key) ?>" data-bg="<?= e($p['bg']) ?>" data-card="<?= e($p['card']) ?>" data-accent="<?= e($p['accent']) ?>"><i style="background:linear-gradient(90deg,<?= e($p['bg']) ?> 50%,<?= e($p['accent']) ?> 50%)"></i><?= e($p['n']) ?></button>
            <?php endforeach; ?>
          </div></section>
          <?php foreach (['bg' => 'Arka Plan', 'card' => 'Kart Rengi', 'accent' => 'Vurgu (Fiyat & Buton)'] as $sk => $sl): ?>
          <section><h6><?= $sl ?></h6><div class="sw" data-sw="<?= $sk ?>">
            <?php foreach ($swatches[$sk] as $c): ?><button type="button" style="background:<?= e($c) ?>" title="<?= e($c) ?>" data-c="<?= e(strtolower($c)) ?>"></button><?php endforeach; ?>
            <input type="color" value="<?= e($pal[$sk]) ?>" title="Özel renk seç" data-custom="<?= $sk ?>">
          </div></section>
          <?php endforeach; ?>
          <section>
            <h6>Önizleme</h6>
            <div class="pv">
              <div class="pv-h"><b>SunLine</b><span>Rezervasyon</span></div>
              <div class="pv-b">
                <div class="pv-e">Bölgeler & Fiyatlar</div>
                <div class="pv-t">Transfer Fiyatları</div>
                <div class="pv-c"><small>ÖZEL FİYAT</small>
                  <div class="pv-r"><div>GİDİŞ-DÖNÜŞ<b>70€</b></div><div>TEK YÖN<b>35€</b></div></div>
                  <div class="pv-btn">Rezervasyon</div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label class="form-label-sm">Logo — açık zemin için (boş = tema logosu)</label>
        <div class="input-group"><input class="form-control" id="logoL" name="base[global][logo_light]" value="<?= e($G['logo_light'] ?? '') ?>" placeholder="assets logosu kullanılır"><button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('logoL','image')"><i class="bi bi-folder2-open"></i></button></div>
      </div>
      <div class="col-md-6">
        <label class="form-label-sm">Logo — koyu zemin için (boş = beyaz yazılı tema logosu)</label>
        <div class="input-group"><input class="form-control" id="logoD" name="base[global][logo_dark]" value="<?= e($G['logo_dark'] ?? '') ?>" placeholder="assets logosu kullanılır"><button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('logoD','image')"><i class="bi bi-folder2-open"></i></button></div>
      </div>
    </div>
  </div></div>

  <?php /* ============================ GENEL ============================ */ ?>
  <?= hp_card_open('genel', 'bi-sliders', '#0ea5e9', 'Genel Ayarlar', 'Kampanya (üstü çizili fiyat) · WhatsApp · menü · duyuru şeridi · footer') ?>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label-sm">Kampanya indirimi gösterimi (%)</label>
        <div class="input-group"><input class="form-control" type="number" step="0.5" min="0" max="90" name="base[global][discount]" value="<?= e((string)($G['discount'] ?? 0)) ?>"><span class="input-group-text">%</span></div>
        <div class="hp-note mt-1">Kartlardaki üstü çizili "eski fiyat" ve "İNDİRİM" rozeti bu orana göre hesaplanır. <b>0</b> yazarsanız gösterilmez. (Ör. 12,5 → 35€ yerine <del>40€</del> 35€)</div>
      </div>
      <div class="col-md-4">
        <label class="form-label-sm">WhatsApp numarası</label>
        <input class="form-control" name="base[global][whatsapp]" value="<?= e($G['whatsapp'] ?? '') ?>" placeholder="<?= e(getSetting('contact_phone', '+90 5XX XXX XX XX')) ?>">
        <div class="hp-note mt-1">Boş bırakılırsa Site Ayarları'ndaki telefon kullanılır.</div>
      </div>
      <div class="col-md-4">
        <div class="alert alert-info small mb-0"><i class="bi bi-geo-alt me-1"></i>Anasayfadaki bölge kartları <b>Tur Yönetimi › Transferler</b> ekranında <b>"Öne Çıkan"</b> işaretli transferlerdir (şu an <?= (int)$featuredCount ?> adet). Fiyatlar da oradan gelir.</div>
      </div>
    </div>

    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['global']; $n = 'tr[' . $c . '][global]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="hp-sub"><?= e($l['flag'] . ' ' . $l['native_name']) ?> — Üst duyuru şeridi (kırmızı kayan bant)</div>
      <?= hp_repeater($n . '[promo]', $v['promo'] ?? [], [
          ['f' => 'b', 'label' => 'Vurgulu (sarı) kısım', 'col' => 'col-md-4', 'ph' => 'Mayıs sonuna kadar:'],
          ['f' => 't', 'label' => 'Metin', 'col' => 'col-md', 'ph' => 'Ücretsiz İptal & Araçta Nakit Ödeme İmkânı!'],
      ], 'Duyuru ekle') ?>

      <div class="hp-sub">Üst menü</div>
      <?= hp_repeater($n . '[nav]', $v['nav'] ?? [], [
          ['f' => 'l', 'label' => 'Menü adı', 'col' => 'col-md-4'],
          ['f' => 'u', 'label' => 'Bağlantı', 'col' => 'col-md', 'ph' => '{home} · #bolgeler · {blog} · {contact} · /sayfa · https://...'],
      ], 'Menü öğesi ekle') ?>
      <div class="row g-2 mt-1">
        <div class="col-md-4"><label class="form-label-sm">Sağdaki buton metni</label><?= hp_text($n . '[cta_label]', $v['cta_label'] ?? '', '', 60) ?></div>
        <div class="col-md-8"><label class="form-label-sm">Buton bağlantısı</label><?= hp_text($n . '[cta_url]', $v['cta_url'] ?? '', '#bolgeler') ?></div>
      </div>
      <div class="hp-note mt-2">Kısaltmalar: <code>{home}</code> anasayfa, <code>{transfers}</code> tüm transferler, <code>{tours}</code> turlar, <code>{blog}</code>, <code>{contact}</code>, <code>{faq}</code>, <code>{page:kvkk}</code> (Sayfalar'daki bir sayfa), <code>#bolgeler</code> <code>#bilgi</code> <code>#misafir</code> <code>#sss</code> (anasayfa bölümleri).</div>

      <div class="hp-sub">Footer</div>
      <div class="mb-2"><label class="form-label-sm">Hakkımızda metni</label><?= hp_area($n . '[footer_about]', $v['footer_about'] ?? '', 2) ?></div>
      <label class="form-label-sm">Hızlı bağlantılar</label>
      <?= hp_repeater($n . '[footer_links]', $v['footer_links'] ?? [], [
          ['f' => 'l', 'label' => 'Bağlantı adı', 'col' => 'col-md-4'],
          ['f' => 'u', 'label' => 'Adres', 'col' => 'col-md', 'ph' => '{faq} · {page:kvkk} · /sayfa'],
      ], 'Bağlantı ekle') ?>

      <div class="hp-sub">Alt anahtar kelime şeridi (SEO iç linkleri)</div>
      <?= hp_area($n . '[keywords]', $v['keywords'] ?? '', 4, "Boş bırakırsanız öne çıkan bölgelerden otomatik oluşturulur.\nHer satıra bir kelime: side transfer | {transfers}\nveya: belek transfer | /transferler/belek-transfer") ?>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ PARA BİRİMİ & EKSTRALAR ============================ */ ?>
  <?= hp_card_open('odeme', 'bi-cash-coin', '#16a34a', 'Para Birimi & Ekstralar', 'Sitede listelenen fiyat (' . $defCur . ') · araçta nakit ödeme seçenekleri · çocuk koltuğu / yükseltici ücreti') ?>
    <div class="row g-3">
      <div class="col-lg-5">
        <label class="form-label-sm" for="curSel">Sitede listelenen para birimi (tüm dillerde)</label>
        <select class="form-select" id="curSel" name="base[global][currency]">
          <?php foreach ($curList as $c): ?>
          <option value="<?= e($c) ?>" <?= $c === $defCur ? 'selected' : '' ?>><?= e($c . ' ' . sl_currency_symbol($c)) ?> — <?= isset($curStats[$c]) ? (int)$curStats[$c] . ' transferde fiyat var' : 'fiyat girilmemiş' ?></option>
          <?php endforeach; ?>
        </select>
        <div class="hp-note mt-1">Kartlar, bölge sayfaları ve rezervasyon her dilde bu para biriminde gösterilir. Fiyatlar <b>Tur Yönetimi › Transferler</b> ekranında (hangi dil sekmesinde olursa olsun) bu para birimi seçilerek girilen fiyatlardır. Bir transferde bu para biriminde fiyat yoksa sırasıyla EUR, USD, GBP, TRY fiyatı gösterilir.</div>
      </div>
      <div class="col-lg-7">
        <span class="form-label-sm">Onay adımında "araçta nakit" ödeme seçenekleri</span>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($curList as $c): ?>
          <label class="cur-chk"><input class="form-check-input m-0" type="checkbox" name="base[global][pay_currencies][]" value="<?= e($c) ?>" <?= in_array($c, $payList, true) ? 'checked' : '' ?>><?= e($c . ' ' . sl_currency_symbol($c)) ?><?php if ($c === $defCur): ?><small>varsayılan</small><?php endif; ?></label>
          <?php endforeach; ?>
        </div>
        <div class="hp-note mt-1">Müşterinin karşısına son adımda varsayılan para birimi <b>seçili</b> gelir; işaretli diğerleri seçenek olarak çıkar (yalnız o araç için Transferler ekranında fiyatı girilmiş olanlar). Rezervasyon, seçilen para birimi ve o para birimindeki fiyatla kaydedilir. Varsayılan para birimi her zaman seçenektir.</div>
      </div>
    </div>

    <div class="hp-sub">Çocuk koltuğu & yükseltici ek ücreti</div>
    <div class="row g-3 align-items-start">
      <div class="col-lg-5">
        <div class="form-check form-switch mb-3">
          <input type="hidden" name="base[global][free_seats]" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="feeFree" name="base[global][free_seats]" value="1" <?= $feeCfg['free'] > 0 ? 'checked' : '' ?>>
          <label class="form-check-label" for="feeFree"><b>1 adet</b> çocuk koltuğu <b>veya</b> yükseltici ücretsiz (toplamda bir tanesi)</label>
        </div>
        <span class="form-label-sm">Ücret nasıl uygulanır?</span>
        <div class="mode-opt">
          <label><input type="radio" name="base[global][fee_per_leg]" value="1" <?= $feeCfg['perLeg'] ? 'checked' : '' ?>>Transfer başına (gidiş-dönüşte 2 kez)</label>
          <label><input type="radio" name="base[global][fee_per_leg]" value="0" <?= !$feeCfg['perLeg'] ? 'checked' : '' ?>>Rezervasyon başına (1 kez)</label>
        </div>
        <div class="hp-note mt-2">Ücretsiz hak önce pahalı olana uygulanır. Ücret, rezervasyonun toplam fiyatına eklenir ve yönetim notunda dökümü yazılır. Yükseltici adedi müşteri notunun başına <code>YÜKSELTİCİ: N</code> olarak eklenir (WhatsApp şablonlarındaki <code>{NOTES}</code> ile sürücüye de gider).</div>
      </div>
      <div class="col-lg-7">
        <div class="table-responsive">
          <table class="fee-tbl">
            <thead><tr><th>Para birimi</th><th>Çocuk koltuğu (adet başı)</th><th>Yükseltici (adet başı)</th></tr></thead>
            <tbody>
              <?php foreach ($curList as $c): ?>
              <tr>
                <td><?= e($c . ' ' . sl_currency_symbol($c)) ?></td>
                <td><input class="form-control form-control-sm" type="number" step="0.01" min="0" inputmode="decimal" name="base[global][seat_fee][<?= e($c) ?>]" value="<?= isset($feeCfg['seat'][$c]) ? e(rtrim(rtrim(number_format($feeCfg['seat'][$c], 2, '.', ''), '0'), '.')) : '' ?>" placeholder="otomatik"></td>
                <td><input class="form-control form-control-sm" type="number" step="0.01" min="0" inputmode="decimal" name="base[global][booster_fee][<?= e($c) ?>]" value="<?= isset($feeCfg['booster'][$c]) ? e(rtrim(rtrim(number_format($feeCfg['booster'][$c], 2, '.', ''), '0'), '.')) : '' ?>" placeholder="otomatik"></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="hp-note mt-1">Ücreti en azından varsayılan para biriminde (<?= e($defCur) ?>) girin. Boş bırakılan para biriminde ücret, girilen ücretten aracın fiyat oranıyla otomatik çevrilir (ör. araç 50 EUR / 2.000 TRY ise 5 EUR → 200 TRY). <b>0</b> = ücretsiz. Hiç ücret girilmezse tüm koltuklar ücretsizdir.</div>
      </div>
    </div>
  </div></div>

  <?php /* ============================ HERO + HIZLI FORM ============================ */ ?>
  <?= hp_card_open('hero', 'bi-image', '#6366f1', 'Hero & Hızlı Fiyat Formu', 'Büyük görsel ya da misafir fotoğraf mozaiği, başlık, alt başlık · form altı not ve güven rozetleri', 'active[hero]', $baseVal['hero']['_active']) ?>
    <div class="row g-3 mb-2">
      <div class="col-md-8">
        <label class="form-label-sm">Hero arka plan görseli (tüm diller)</label>
        <div class="input-group"><input class="form-control" id="heroImg" name="base[hero][image]" value="<?= e($baseVal['hero']['image'] ?? '') ?>"><button type="button" class="btn btn-outline-secondary" id="heroPick"><i class="bi bi-folder2-open"></i></button></div>
        <div class="hp-note mt-1">Medya kütüphanesinden seçin veya tam adres yapıştırın. Önerilen: 1800px genişlik, sıkıştırılmış JPG/WebP.</div>
      </div>
      <div class="col-md-4"><img src="<?= e(trim((string)($baseVal['hero']['image'] ?? '')) !== '' ? getMediaUrl($baseVal['hero']['image']) : '') ?>" class="img-prev" id="heroImgPrev" alt="" <?= trim((string)($baseVal['hero']['image'] ?? '')) === '' ? 'hidden' : '' ?>></div>
    </div>
    <?php
      $mzSel = (string)($baseVal['hero']['style'] ?? 'image') === 'mosaic';
      $mzPhotos = array_values(array_filter((array)($baseVal['hero']['mosaic'] ?? []), function ($p) { return trim((string)$p) !== ''; }));
      // 150 KB'dan büyük dosya girişi yavaşlatır → panelde "büyük" etiketi
      $mzBig = function ($p) {
          $p = (string)$p;
          if ($p === '' || strpos($p, 'http') === 0 || strpos($p, '..') !== false) return false;
          $f = UPLOADS_PATH . $p;
          return is_file($f) && filesize($f) > 150 * 1024;
      };
    ?>
    <div class="hp-sub">Giriş görünümü</div>
    <div class="row g-3 mb-2">
      <div class="col-md-5">
        <label class="form-label-sm">Görünüm</label>
        <select class="form-select" name="base[hero][style]">
          <option value="image"<?= $mzSel ? '' : ' selected' ?>>Tek arka plan görseli</option>
          <option value="mosaic"<?= $mzSel ? ' selected' : '' ?>>Misafir fotoğraf mozaiği</option>
        </select>
      </div>
      <div class="col-md-7"><label class="form-label-sm">Mozaikte büyük yazı (tüm diller) — vurgulanacak kısmı *yıldız* içine alın</label><?= hp_text('base[hero][brand]', $baseVal['hero']['brand'] ?? '', 'SUNLINE *VIP*', 60) ?></div>
    </div>
    <label class="form-label-sm">Mozaik fotoğrafları (tüm diller) — <span id="mzCount"><?= count($mzPhotos) ?></span> / 60</label>
    <div class="gst-list" id="mzList">
      <?php foreach ($mzPhotos as $p): ?>
      <div class="gst-it mz-it"><img src="<?= e(getMediaUrl($p)) ?>" alt="" loading="lazy"><input type="hidden" name="base[hero][mosaic][]" value="<?= e($p) ?>"><button type="button" class="gst-del" title="Kaldır">×</button><?php if ($mzBig($p)): ?><span class="mz-big" title="Dosya 150 KB'dan büyük — giriş yavaşlar">büyük</span><?php endif; ?></div>
      <?php endforeach; ?>
      <button type="button" class="gst-add mz-add" id="mzAdd"><i class="bi bi-plus-lg"></i>Ekle</button>
    </div>
    <div class="hp-note mt-1 mb-3">Kare, yaklaşık 400 px ve küçük boyutlu (50 KB altı) fotoğraflar önerilir; birden çok fotoğraf aynı anda seçilebilir. En az 15–20 fotoğraf önerilir; aynı foto yan yana gelmeyecek şekilde otomatik dizilir. Mozaik seçiliyken arka plan görseli ve alt başlık kullanılmaz, "Başlık (H1)" büyük yazının altında küçük satır olarak görünür. Fotoğraf yoksa tek görsel gösterilir.</div>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['hero']; $gv = $val[$c]['global']; $n = 'tr[' . $c . ']'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[hero][eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-6"><label class="form-label-sm">Başlık (H1) — vurgulanacak kısmı *yıldız* içine alın</label><?= hp_text($n . '[hero][title]', $v['title'] ?? '', 'Antalya *VIP Transfer*') ?></div>
        <div class="col-12"><label class="form-label-sm">Alt başlık</label><?= hp_area($n . '[hero][subtitle]', $v['subtitle'] ?? '', 2) ?></div>
        <div class="col-12"><label class="form-label-sm">Formun altındaki not</label><?= hp_text($n . '[global][note]', $gv['note'] ?? '') ?></div>
      </div>
      <div class="hp-sub">Güven rozetleri</div>
      <?= hp_repeater($n . '[global][trust]', $gv['trust'] ?? [], [
          ['f' => 'i', 'label' => 'İkon', 'col' => 'col-md-3', 'type' => 'icon'],
          ['f' => 't', 'label' => 'Metin', 'col' => 'col-md'],
      ], 'Rozet ekle') ?>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ BÖLGELER ============================ */ ?>
  <?= hp_card_open('bolgeler', 'bi-geo-alt', '#f59e0b', 'Bölgeler & Fiyatlar', 'Kart başlıkları ve filtre düğmeleri (kartlar Tur Yönetimi › Transferler\'den gelir)', 'active[regions]', $baseVal['regions']['_active']) ?>
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" role="switch" id="onlyFeat" name="base[regions][only_featured]" value="1" <?= !empty($baseVal['regions']['only_featured']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="onlyFeat">Anasayfada sadece "Öne Çıkan" transferleri göster (kapalıysa tüm transferler)</label>
    </div>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['regions']; $n = 'tr[' . $c . '][regions]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
        <div class="col-12"><label class="form-label-sm">Açıklama</label><?= hp_area($n . '[subtitle]', $v['subtitle'] ?? '', 2) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="hp-sub">Filtre düğmeleri (tüm diller)</div>
    <div class="hp-note mb-2">Bir transfer, başlığında/konumunda (ör. "Antalya / Manavgat / Side") aşağıdaki kelimelerden biri geçiyorsa o filtreye girer. Kartı olmayan filtre sitede gizlenir.</div>
    <div class="row g-2 mb-2">
      <?php foreach ($languages as $l): ?>
      <div class="col-6 col-md-3"><label class="form-label-sm">"Tümü" — <?= e($l['native_name']) ?></label><input class="form-control form-control-sm" name="base[regions][all_label][<?= e($l['code']) ?>]" value="<?= e($baseVal['regions']['all_label'][$l['code']] ?? ($l['code'] === 'tr' ? 'Tümü' : 'All')) ?>"></div>
      <?php endforeach; ?>
    </div>
    <?php
    $filterFields = [['f' => 'k', 'label' => 'Kod', 'col' => 'col-md-2', 'ph' => 'kemer']];
    foreach ($languages as $l) $filterFields[] = ['f' => 'l.' . $l['code'], 'label' => 'Ad (' . strtoupper($l['code']) . ')', 'col' => 'col-6 col-md'];
    $filterFields[] = ['f' => 'm', 'label' => 'Eşleşen kelimeler (virgülle)', 'col' => 'col-12', 'type' => 'textarea', 'rows' => 2];
    echo hp_repeater('base[regions][filters]', $baseVal['regions']['filters'] ?? [], $filterFields, 'Filtre ekle');
    ?>
  </div></div>

  <?php /* ============================ BİLGİLENDİRME ============================ */ ?>
  <?= hp_card_open('bilgi', 'bi-patch-check', '#10b981', 'Bilgilendirme', 'Profesyonel hizmet ve karşılama standartları (6 madde)', 'active[info]', $baseVal['info']['_active']) ?>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['info']; $n = 'tr[' . $c . '][info]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
        <div class="col-12"><label class="form-label-sm">Açıklama</label><?= hp_area($n . '[subtitle]', $v['subtitle'] ?? '', 3) ?></div>
        <div class="col-12"><label class="form-label-sm">İmza</label><?= hp_text($n . '[sign]', $v['sign'] ?? '') ?></div>
      </div>
      <div class="hp-sub">Maddeler</div>
      <?= hp_repeater($n . '[items]', $v['items'] ?? [], [
          ['f' => 'i', 'label' => 'İkon', 'col' => 'col-md-3', 'type' => 'icon'],
          ['f' => 't', 'label' => 'Başlık', 'col' => 'col-md'],
          ['f' => 'p', 'label' => 'Metin', 'col' => 'col-12', 'type' => 'textarea', 'rows' => 2],
      ], 'Madde ekle') ?>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ MİSAFİRLER ============================ */ ?>
  <?= hp_card_open('misafir', 'bi-people', '#8b5cf6', 'Misafirlerimiz', 'Misafir fotoğrafları (fotoğraf yoksa bölüm gizlenir)', 'active[guests]', $baseVal['guests']['_active']) ?>
    <label class="form-label-sm">Fotoğraflar (tüm diller) — dikey (3:4) fotoğraflar en iyi sonucu verir</label>
    <div class="gst-list" id="gstList">
      <?php foreach ((array)($baseVal['guests']['photos'] ?? []) as $p): ?>
      <div class="gst-it"><img src="<?= e(getMediaUrl($p)) ?>" alt=""><input type="hidden" name="base[guests][photos][]" value="<?= e($p) ?>"><button type="button" class="gst-del" title="Kaldır">×</button></div>
      <?php endforeach; ?>
      <button type="button" class="gst-add" id="gstAdd"><i class="bi bi-plus-lg"></i>Ekle</button>
    </div>
    <input type="hidden" id="gstTmp">
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['guests']; $n = 'tr[' . $c . '][guests]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?> mt-3" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ GOOGLE YORUMLARI ============================ */
  $rvB = $baseVal['reviews'];
  $rvAll = 0; $rvVis = 0; $rvTbl = true;
  try {
      $rq = $db->query("SELECT COUNT(*) AS a, COALESCE(SUM(is_visible = 1 AND TRIM(content) <> ''), 0) AS v FROM google_reviews")->fetch();
      $rvAll = (int)$rq['a']; $rvVis = (int)$rq['v'];
  } catch (Exception $e) { $rvTbl = false; }
  $rvPos = $rvB['position'] ?? 'after_blog';
  ?>
  <?= hp_card_open('gyorum', 'bi-google', '#ea4335', 'Google Yorumları', 'Müşteri yorumları alanı — aç/kapat, yeri, puan ve başlıklar (yorumların kendisi İçerik › Google Yorumları ekranından)', 'active[reviews]', $rvB['_active']) ?>
    <?php if (!$rvTbl): ?>
    <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Yorum tablosu veritabanında yok — <code>migrations/036_google_yorumlari.sql</code> çalıştırılmalı. O zamana kadar alan sitede görünmez.</div>
    <?php else: ?>
    <div class="alert alert-secondary small d-flex flex-wrap align-items-center gap-2">
      <span><i class="bi bi-chat-square-quote me-1"></i>Sitede görünen yorum: <b><?= $rvVis ?></b> / toplam <?= $rvAll ?></span>
      <a class="btn btn-sm btn-outline-primary ms-auto" href="<?= ADMIN_URL ?>/google-reviews.php"><i class="bi bi-pencil-square me-1"></i>Yorumları yönet (ekle · gizle · sırala)</a>
    </div>
    <?php endif; ?>
    <div class="hp-note mb-3">Sağ üstteki anahtar alanı açar/kapatır. Görünür yorum yoksa alan sitede kendiliğinden gizlenir. Menüde <code>#yorumlar</code> bağlantısı varsa alan kapalıyken menüde de görünmez.</div>
    <div class="row g-3 mb-2">
      <div class="col-md-4">
        <label class="form-label-sm">Anasayfadaki yeri</label>
        <select class="form-select" name="base[reviews][position]">
          <option value="after_info" <?= $rvPos === 'after_info' ? 'selected' : '' ?>>Bilgilendirme'den sonra (Misafirlerimiz'in yeri)</option>
          <option value="after_blog" <?= $rvPos === 'after_blog' ? 'selected' : '' ?>>Blog'dan sonra, SSS'den önce</option>
          <option value="after_faq" <?= $rvPos === 'after_faq' ? 'selected' : '' ?>>En alt (SSS'den sonra)</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label-sm">Google puanı</label>
        <input class="form-control" name="base[reviews][rating]" value="<?= e(str_replace('.', ',', (string)($rvB['rating'] ?? ''))) ?>" placeholder="5,0" inputmode="decimal" maxlength="4">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label-sm">Google'daki yorum sayısı</label>
        <input class="form-control" type="number" min="0" name="base[reviews][count]" value="<?= (int)($rvB['count'] ?? 0) ?: '' ?>" placeholder="34">
      </div>
      <div class="col-md-3">
        <label class="form-label-sm">Kaydırmada en fazla kart</label>
        <input class="form-control" type="number" min="1" max="50" name="base[reviews][limit]" value="<?= (int)($rvB['limit'] ?? 12) ?>">
      </div>
      <div class="col-12">
        <label class="form-label-sm">Google'daki yorum sayfası (isteğe bağlı)</label>
        <input class="form-control" name="base[reviews][url]" value="<?= e($rvB['url'] ?? '') ?>" placeholder="https://www.google.com/maps?cid=...">
        <div class="hp-note mt-1">Doluysa alanın altında "Google'da tüm yorumları gör" düğmesi çıkar. Boş bırakılırsa düğme gösterilmez. Puan boşsa üstteki puan satırı gizlenir; sayı boşsa yalnız sayı gizlenir.</div>
      </div>
    </div>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['reviews']; $n = 'tr[' . $c . '][reviews]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?> mt-2" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Açıklama</label><?= hp_text($n . '[subtitle]', $v['subtitle'] ?? '') ?></div>
        <div class="col-md-4"><label class="form-label-sm">Google düğmesinin yazısı</label><?= hp_text($n . '[link_label]', $v['link_label'] ?? '') ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ BLOG ============================ */ ?>
  <?= hp_card_open('blogb', 'bi-journal-text', '#ec4899', 'Blog', 'Anasayfada son yazılar (yazılar İçerik › Blog ekranından)', 'active[blog]', $baseVal['blog']['_active']) ?>
    <div class="row g-3 mb-2"><div class="col-md-3"><label class="form-label-sm">Gösterilecek yazı sayısı</label><input class="form-control" type="number" min="1" max="12" name="base[blog][limit]" value="<?= (int)($baseVal['blog']['limit'] ?? 6) ?>"></div></div>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['blog']; $n = 'tr[' . $c . '][blog]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ SSS ============================ */ ?>
  <?= hp_card_open('sss', 'bi-question-circle', '#14b8a6', 'Sıkça Sorulan Sorular', 'Anasayfa SSS — Google ve yapay zekâ cevapları için yapısal veri olarak da yayınlanır', 'active[faq]', $baseVal['faq']['_active']) ?>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $v = $val[$c]['faq']; $n = 'tr[' . $c . '][faq]'; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label-sm">Üst küçük yazı</label><?= hp_text($n . '[eyebrow]', $v['eyebrow'] ?? '') ?></div>
        <div class="col-md-8"><label class="form-label-sm">Başlık</label><?= hp_text($n . '[title]', $v['title'] ?? '') ?></div>
      </div>
      <div class="hp-sub">Sorular</div>
      <?= hp_repeater($n . '[items]', $v['items'] ?? [], [
          ['f' => 'q', 'label' => 'Soru', 'col' => 'col-12'],
          ['f' => 'a', 'label' => 'Cevap', 'col' => 'col-12', 'type' => 'textarea', 'rows' => 2],
      ], 'Soru ekle') ?>
    </div>
    <?php endforeach; ?>
  </div></div>

  <?php /* ============================ SEO ============================ */ ?>
  <?= hp_card_open('seo', 'bi-search', '#4f46e5', 'Anasayfa SEO', 'Google başlığı ve açıklaması — boş bırakılırsa dile göre hazır SEO metni kullanılır') ?>
    <?php foreach ($languages as $i => $l): $c = $l['code']; $s = $seoData[$c] ?? []; ?>
    <div class="lang-pane <?= $i === 0 ? 'active' : '' ?>" data-lang-pane="<?= e($c) ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">Meta başlık (≈60 karakter)</label>
          <input class="form-control cc" data-max="60" name="seo[<?= e($c) ?>][meta_title]" value="<?= e($s['meta_title'] ?? '') ?>" placeholder="<?= e(sl_t('seo_home_t', [], $c)) ?>">
          <div class="char-count"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Anahtar kelimeler (virgülle, isteğe bağlı)</label>
          <input class="form-control" name="seo[<?= e($c) ?>][meta_keywords]" value="<?= e($s['meta_keywords'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label-sm">Meta açıklama (≈160 karakter)</label>
          <textarea class="form-control cc" data-max="160" rows="2" name="seo[<?= e($c) ?>][meta_description]" placeholder="<?= e(sl_t('seo_home_d', [], $c)) ?>"><?= e($s['meta_description'] ?? '') ?></textarea>
          <div class="char-count"></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="alert alert-secondary small mt-3 mb-0"><i class="bi bi-lightbulb me-1"></i>Transfer (bölge) sayfalarının SEO başlık/açıklaması <b>Tur Yönetimi › Transferler</b> içindeki her transferin <b>Meta Başlık / Meta Açıklama</b> alanlarından gelir; boşsa "<i>Side Transfer | Antalya Havalimanı – Side VIP Transfer</i>" gibi dile göre otomatik üretilir. Otomatik şablonlar <b>Sistem › Çeviriler › sunline</b> grubunda düzenlenebilir.</div>
  </div></div>

  <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Tümünü Kaydet</button>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('hpForm');

  // Kartları aç/kapat (açık kart adres çubuğunda hatırlanır)
  document.querySelectorAll('.hp-card .hp-head').forEach(function (h) {
    h.addEventListener('click', function () {
      var card = h.closest('.hp-card');
      card.classList.toggle('open');
      if (card.classList.contains('open')) { history.replaceState(null, '', '#' + card.id); document.getElementById('hpTab').value = card.id; }
    });
    h.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); h.click(); } });
  });
  var open = location.hash ? document.getElementById(location.hash.slice(1)) : null;
  (open && open.classList.contains('hp-card') ? open : document.getElementById('tema')).classList.add('open');
  if (open) document.getElementById('hpTab').value = open.id;

  // Dil sekmeleri
  document.querySelectorAll('.lang-tab-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      var lang = b.dataset.lang;
      document.querySelectorAll('.lang-tab-btn').forEach(function (x) { x.classList.toggle('active', x === b); });
      document.querySelectorAll('.lang-pane').forEach(function (p) { p.classList.toggle('active', p.dataset.langPane === lang); });
    });
  });

  // Tekrarlanabilir listeler
  function reindex(rep) {
    var base = rep.dataset.base;
    rep.querySelectorAll(':scope > .rep-rows > .rep-row').forEach(function (row, i) {
      row.querySelectorAll('[data-f]').forEach(function (inp) {
        inp.name = base + '[' + i + '][' + inp.dataset.f.split('.').join('][') + ']';
      });
    });
  }
  document.addEventListener('click', function (e) {
    var add = e.target.closest('.rep-add');
    if (add) {
      var rep = add.closest('.rep');
      var tpl = rep.querySelector(':scope > template.rep-tpl');
      var wrap = document.createElement('div');
      wrap.innerHTML = tpl.innerHTML.trim();
      rep.querySelector(':scope > .rep-rows').appendChild(wrap.firstChild);
      reindex(rep);
      return;
    }
    var row = e.target.closest('.rep-row');
    if (!row) return;
    var rep2 = row.closest('.rep');
    if (e.target.closest('.rep-del')) { row.remove(); reindex(rep2); }
    else if (e.target.closest('.rep-up') && row.previousElementSibling) { row.parentNode.insertBefore(row, row.previousElementSibling); reindex(rep2); }
    else if (e.target.closest('.rep-down') && row.nextElementSibling) { row.parentNode.insertBefore(row.nextElementSibling, row); reindex(rep2); }
  });
  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('ico-in')) {
      var p = e.target.parentNode.querySelector('.ico-prev i');
      if (p) p.className = 'bi ' + (e.target.value || 'bi-question');
    }
  });
  form.addEventListener('submit', function () { document.querySelectorAll('.rep').forEach(reindex); });

  // Karakter sayacı
  document.querySelectorAll('.cc').forEach(function (inp) {
    var out = inp.parentNode.querySelector('.char-count');
    var upd = function () { var n = inp.value.length, m = +inp.dataset.max; out.textContent = n + ' / ' + m + ' karakter'; out.classList.toggle('over', n > m); };
    inp.addEventListener('input', upd); upd();
  });

  // Hero görsel önizleme
  var heroImg = document.getElementById('heroImg'), heroPrev = document.getElementById('heroImgPrev');
  var uploadsUrl = <?= json_encode(UPLOADS_URL) ?>;
  var mediaUrl = function (p) { return /^https?:\/\//.test(p) ? p : uploadsUrl + p; };
  var syncHero = function () { if (heroImg.value) { heroPrev.src = mediaUrl(heroImg.value); heroPrev.hidden = false; } else heroPrev.hidden = true; };
  heroImg.addEventListener('change', syncHero);
  document.getElementById('heroPick').addEventListener('click', function () {
    openMediaPicker('heroImg', 'image');
    mediaPickerCallback = function (m) { heroImg.value = m.file_path; syncHero(); };
  });

  // Misafir fotoğrafları
  var gstList = document.getElementById('gstList'), gstAdd = document.getElementById('gstAdd');
  gstAdd.addEventListener('click', function () {
    openMediaPicker('gstTmp', 'image');
    mediaPickerCallback = function (m) {
      var d = document.createElement('div');
      d.className = 'gst-it';
      d.innerHTML = '<img alt=""><input type="hidden" name="base[guests][photos][]"><button type="button" class="gst-del" title="Kaldır">×</button>';
      d.querySelector('img').src = m.url;
      d.querySelector('input').value = m.file_path;
      gstList.insertBefore(d, gstAdd);
    };
  });
  gstList.addEventListener('click', function (e) { if (e.target.classList.contains('gst-del')) e.target.closest('.gst-it').remove(); });

  // Giriş mozaiği fotoğrafları (çoklu seçim; aynı foto iki kez eklenmez; en fazla 60)
  var mzList = document.getElementById('mzList'), mzAdd = document.getElementById('mzAdd'), mzCount = document.getElementById('mzCount');
  var mzSync = function () { mzCount.textContent = mzList.querySelectorAll('.mz-it').length; };
  mzAdd.addEventListener('click', function () {
    openMediaPickerMulti('image', function (items) {
      var have = {};
      mzList.querySelectorAll('.mz-it input').forEach(function (i) { have[i.value] = true; });
      items.forEach(function (m) {
        if (!m || !m.file_path || have[m.file_path] || mzList.querySelectorAll('.mz-it').length >= 60) return;
        have[m.file_path] = true;
        var d = document.createElement('div');
        d.className = 'gst-it mz-it';
        d.innerHTML = '<img alt=""><input type="hidden" name="base[hero][mosaic][]"><button type="button" class="gst-del" title="Kaldır">×</button>';
        d.querySelector('img').src = m.url;
        d.querySelector('input').value = m.file_path;
        mzList.insertBefore(d, mzAdd);
      });
      mzSync();
    });
  });
  mzList.addEventListener('click', function (e) { if (e.target.classList.contains('gst-del')) { e.target.closest('.gst-it').remove(); mzSync(); } });

  // ===== Tema renkleri (Claude Design Tweaks paneli ile aynı hesaplama) =====
  function rgb(h) { h = h.replace('#', ''); return [0, 2, 4].map(function (i) { return parseInt(h.substr(i, 2), 16); }); }
  function lum(h) { var c = rgb(h); return .2126 * c[0] / 255 + .7152 * c[1] / 255 + .0722 * c[2] / 255; }
  function mix(h, amt) { var t = amt > 0 ? 255 : 0, a = Math.abs(amt); return '#' + rgb(h).map(function (v) { return Math.round(v + (t - v) * a).toString(16).padStart(2, '0'); }).join(''); }
  function rel(h) { return rgb(h).map(function (v) { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); }).reduce(function (s, v, i) { return s + v * [.2126, .7152, .0722][i]; }, 0); }
  function contrast(a, b) { var l1 = rel(a), l2 = rel(b); return (Math.max(l1, l2) + .05) / (Math.min(l1, l2) + .05); }
  function readable(fg, bg, min) { if (contrast(fg, bg) >= min) return fg; var dark = rel(bg) > .35; for (var s = .05; s <= .9; s += .05) { var c = mix(fg, dark ? -s : s); if (contrast(c, bg) >= min) return c; } return dark ? '#141414' : '#ffffff'; }
  function vars(bg, card, acc) {
    var lb = lum(bg) > .5, lc = lum(card) > .5;
    return { '--bg': bg, '--bg2': mix(bg, lb ? -.04 : .04), '--card': card, '--card2': mix(card, lc ? -.05 : .07), '--accent': acc,
      '--cta1': mix(acc, -.2), '--cta2': mix(acc, .12), '--text': lb ? '#141414' : '#ffffff', '--line': lb ? 'rgba(0,0,0,.1)' : 'rgba(255,255,255,.09)',
      '--red': lc ? '#d11a1a' : '#ff4d4d', '--ctext': lc ? '#141414' : '#ffffff', '--cmuted': lc ? '#6b6560' : '#b9b9b9', '--cline': lc ? 'rgba(0,0,0,.12)' : 'rgba(255,255,255,.12)',
      '--acc-t': readable(acc, bg, 4.5), '--acc-c': readable(acc, card, 3) };
  }
  document.querySelectorAll('.tw[data-pal]').forEach(function (tw) {
    var get = function (k) { return tw.querySelector('input[data-k="' + k + '"]'); };
    function paint() {
      var bg = get('bg').value, card = get('card').value, acc = get('accent').value, preset = get('preset').value;
      var v = vars(bg, card, acc), pv = tw.querySelector('.pv');
      Object.keys(v).forEach(function (k) { pv.style.setProperty(k, v[k]); });
      tw.querySelectorAll('.pre button').forEach(function (b) { b.classList.toggle('on', b.dataset.preset === preset); });
      ['bg', 'card', 'accent'].forEach(function (k) {
        var cur = get(k).value.toLowerCase();
        tw.querySelectorAll('.sw[data-sw="' + k + '"] button').forEach(function (b) { b.classList.toggle('on', b.dataset.c === cur); });
        var ci = tw.querySelector('input[data-custom="' + k + '"]'); if (ci) ci.value = cur;
      });
      tw.querySelector('[data-hex]').textContent = bg + ' · ' + card + ' · ' + acc;
    }
    tw.querySelectorAll('.pre button').forEach(function (b) {
      b.addEventListener('click', function () {
        get('preset').value = b.dataset.preset; get('bg').value = b.dataset.bg.toLowerCase(); get('card').value = b.dataset.card.toLowerCase(); get('accent').value = b.dataset.accent.toLowerCase();
        paint();
      });
    });
    tw.querySelectorAll('.sw').forEach(function (sw) {
      var k = sw.dataset.sw;
      sw.querySelectorAll('button').forEach(function (b) {
        b.addEventListener('click', function () { get(k).value = b.dataset.c; get('preset').value = ''; paint(); });
      });
      var ci = sw.querySelector('input[type=color]');
      if (ci) ci.addEventListener('input', function () { get(k).value = ci.value.toLowerCase(); get('preset').value = ''; paint(); });
    });
    paint();
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
