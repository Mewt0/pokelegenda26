<?php
declare(strict_types=1);

/**
 * Р РµР°Р»СЊРЅС‹Рµ РґС‹СЂС‹ РІ СЌС„С„РµРєС‚Р°С… Р°С‚Р°Рє вЂ” РїРѕ С‚РѕРјСѓ, С‡С‚Рѕ РґРІРёР¶РѕРє С‡РёС‚Р°РµС‚ РЅР° СЃР°РјРѕРј РґРµР»Рµ.
 *
 * Р”РІРёР¶РѕРє (РїСЂРѕРІРµСЂРµРЅРѕ РїРѕ BattleRepository):
 *   findMoveSecondaryEffects: Р±РµСЂС‘С‚ attac_dop, РЅРѕ РїСЂРё chance <= 0 (chans_dop Рё chans_effect
 *     РѕР±Р° РЅСѓР»Рё) Р’РћР—Р’Р РђР©РђР•Рў РџРЈРЎРўРћ вЂ” СЌС„С„РµРєС‚ РЅРµ РїСЂРёРјРµРЅСЏРµС‚СЃСЏ РІРѕРІСЃРµ;
 *     setting 1 -> СЃС‚Р°С‚СѓСЃ, 2 -> СЃС‚Р°С‚С‹ С†РµР»Рё, 3 -> СЃС‚Р°С‚С‹ Р°С‚Р°РєСѓСЋС‰РµРіРѕ; РёРЅС‹Рµ Р·РЅР°С‡РµРЅРёСЏ РёРіРЅРѕСЂРёСЂСѓСЋС‚СЃСЏ.
 *   findMoveStatEffects: Р±РµСЂС‘С‚ stat_attak Рё Р¶С‘СЃС‚РєРѕ СЃС‡РёС‚Р°РµС‚ tip -> self, tip_b -> enemy,
 *     РЅРµ РіР»СЏРґСЏ РЅР° РєРѕР»РѕРЅРєСѓ stati (Р° РѕРЅР°, РїРѕ РґР°РЅРЅС‹Рј, Рё РµСЃС‚СЊ СѓРєР°Р·Р°С‚РµР»СЊ С†РµР»Рё).
 *
 * Usage: php tools/attack_effects_gaps.php
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "== 1. РќР°СЃС‚СЂРѕР№РєРё СЌС„С„РµРєС‚РѕРІ: РєР°РєРёРµ Р·РЅР°С‡РµРЅРёСЏ setting РµСЃС‚СЊ РІ РґР°РЅРЅС‹С… ==\n";
foreach ($db->query('SELECT setting, COUNT(*) c FROM attac_dop GROUP BY setting ORDER BY c DESC') as $row) {
    $note = match ((int) $row['setting']) {
        1 => 'СЃС‚Р°С‚СѓСЃ (РѕР±СЂР°Р±Р°С‚С‹РІР°РµС‚СЃСЏ)',
        2 => 'СЃС‚Р°С‚С‹ С†РµР»Рё (РѕР±СЂР°Р±Р°С‚С‹РІР°РµС‚СЃСЏ)',
        3 => 'СЃС‚Р°С‚С‹ Р°С‚Р°РєСѓСЋС‰РµРіРѕ (РѕР±СЂР°Р±Р°С‚С‹РІР°РµС‚СЃСЏ)',
        default => 'РќР• РћР‘Р РђР‘РђРўР«Р’РђР•РўРЎРЇ РґРІРёР¶РєРѕРј',
    };
    echo "  setting={$row['setting']}: {$row['c']} Р·Р°РїРёСЃРµР№ вЂ” $note\n";
}

echo "\n== 2. РђС‚Р°РєРё СЃ СЌС„С„РµРєС‚РѕРј, РЅРѕ С€Р°РЅСЃРѕРј 0: РґРІРёР¶РѕРє РёС… РїСЂРѕРїСѓСЃРєР°РµС‚ ==\n";
$sql = "SELECT a.atac_id, a.atac_name, a.chans_dop, a.chans_effect, d.dop_effc, d.setting, d.tip_s
          FROM attac_dop d
          JOIN attac_power a ON a.atac_id = d.id_attc
         WHERE COALESCE(a.chans_dop, 0) <= 0 AND COALESCE(a.chans_effect, 0) <= 0
         ORDER BY a.atac_name";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo '  РЅР°Р№РґРµРЅРѕ: ' . count($rows) . "\n";
foreach ($rows as $row) {
    echo "   - {$row['atac_name']} (effect={$row['dop_effc']}, setting={$row['setting']}, tip_s={$row['tip_s']})\n";
}

echo "\n== 3. stat_attak: РіРґРµ РєРѕР»РѕРЅРєР° stati РїСЂРѕС‚РёРІРѕСЂРµС‡РёС‚ С‚РѕРјСѓ, С‡С‚Рѕ С‡РёС‚Р°РµС‚ РґРІРёР¶РѕРє ==\n";
$sql = "SELECT s.id_atk, a.atac_name, a.stati, s.tip, s.tip_b
          FROM stat_attak s
          JOIN attac_power a ON a.atac_id = s.id_atk
         ORDER BY a.stati, a.atac_name";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$mismatch = [];
foreach ($rows as $row) {
    $stati = (int) $row['stati'];
    $tip = (string) $row['tip'];
    $tipB = (string) $row['tip_b'];
    // Р”РІРёР¶РѕРє: tip РІСЃРµРіРґР° Рє СЃРµР±Рµ, tip_b РІСЃРµРіРґР° Рє С†РµР»Рё. Р•СЃР»Рё stati=2 (С†РµР»СЊ), Р° РґР°РЅРЅС‹Рµ Р»РµР¶Р°С‚ РІ tip вЂ”
    // СЌС„С„РµРєС‚ РїСЂРёРјРµРЅРёС‚СЃСЏ Рє СЃР°РјРѕРјСѓ Р°С‚Р°РєСѓСЋС‰РµРјСѓ: СЌС‚Рѕ Рё РµСЃС‚СЊ РѕС€РёР±РєР°.
    if ($stati === 2 && $tip !== '' && $tip !== '0' && ($tipB === '' || $tipB === '0')) {
        $mismatch[] = $row;
    }
}
echo '  РІСЃРµРіРѕ СЃС‚СЂРѕРє stat_attak: ' . count($rows) . ', РїРѕРґРѕР·СЂРёС‚РµР»СЊРЅС‹С…: ' . count($mismatch) . "\n";
foreach ($mismatch as $row) {
    echo "   - {$row['atac_name']} (stati={$row['stati']}, tip={$row['tip']}, tip_b={$row['tip_b']})\n";
}
