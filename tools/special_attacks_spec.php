<?php
declare(strict_types=1);

/**
 * Спецификация для исполнителя: 130 особых атак, эффект которых не реализован.
 *
 * Группируем атаки по их данным (тип эффекта, знак, изменяемые статы), чтобы работу
 * можно было делать пачками, а не по одной атаке.
 *
 * Usage: php tools/special_attacks_spec.php
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$root = dirname(__DIR__);
$code = (string) file_get_contents($root . '/src/Game/BattleMoveEffectCatalog.php')
      . (string) file_get_contents($root . '/src/Game/BattleEngineService.php');
$key = static fn (string $name): string => trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9]+/i', ' ', strtolower(trim($name)))));
$codeKeys = $key($code);

$sql = "SELECT a.atac_id, a.atac_name, a.atac_tip, a.atac_power, a.atac_not, a.dop_effect,
               a.stati, a.chans_effect, a.chans_dop, a.critic,
               d.dop_effc, d.tip_s, d.setting,
               d.def AS d_def, d.atc AS d_atc, d.sdef AS d_sdef, d.satc AS d_satc, d.speed AS d_speed, d.acc AS d_acc, d.accuracy AS d_accuracy,
               (SELECT COUNT(*) FROM stat_attak s WHERE s.id_atk = a.atac_id) AS stat_rows
          FROM attac_power a
          LEFT JOIN attac_dop d ON d.id_attc = a.atac_id
         WHERE a.atac_id IN (SELECT id_attc FROM attac_dop UNION SELECT id_atk FROM stat_attak)
         ORDER BY a.atac_id";

$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$groups = [];
$implemented = [];
foreach ($rows as $row) {
    $name = (string) $row['atac_name'];
    if ($name === '') {
        continue;
    }
    if (str_contains($codeKeys, $key($name))) {
        $implemented[$name] = true;
        continue;
    }

    // Ключ группы описывает, что атака должна делать: тип эффекта, знак и какие статы меняет.
    $stats = [];
    foreach (['def' => 'DEF', 'atc' => 'ATK', 'sdef' => 'SDEF', 'satc' => 'SATK', 'speed' => 'SPD', 'acc' => 'ACC', 'accuracy' => 'ACCURACY'] as $column => $label) {
        $value = (int) ($row['d_' . $column] ?? 0);
        if ($value !== 0) {
            $stats[] = $label . ($value > 0 ? '+' : '') . $value;
        }
    }
    $status = (int) ($row['atac_not'] ?? 0);
    $parts = [
        'эффект=' . (string) ($row['dop_effc'] ?? '-'),
        'знак=' . (string) (($row['tip_s'] ?? '') === '' ? '-' : $row['tip_s']),
    ];
    if ($stats !== []) {
        $parts[] = 'статы:' . implode(',', $stats);
    }
    if ($status > 0) {
        $parts[] = 'статус=' . $status;
    }
    if ((int) $row['stat_rows'] > 0) {
        $parts[] = 'таблица stat_attak';
    }
    if ((int) ($row['stati'] ?? 0) > 0) {
        $parts[] = 'stati=' . (int) $row['stati'];
    }
    $groupKey = implode(' | ', $parts);
    $groups[$groupKey][] = $name;
}

uksort($groups, static fn (string $a, string $b): int => count($groups[$b]) <=> count($groups[$a]));

$out = [];
$out[] = '# 130 особых атак: спецификация на реализацию';
$out[] = '';
$out[] = 'Проверка: `tools/special_attacks_spec.php`. Атаки берутся из `attac_power` (937 строк),';
$out[] = 'особые эффекты — из `attac_dop` (133) и `stat_attak` (55).';
$out[] = '';
$out[] = '**Ключевой факт:** в новом движке особые эффекты поддержаны **по названию атаки**';
$out[] = '(`src/Game/BattleMoveEffectCatalog.php`: `\'spikes\' => \'spikes\'`, `\'sticky web\' => \'sticky_web\'`),';
$out[] = 'а не по номерам эффектов из таблиц. Всё, чего нет в каталоге по имени, в бою не работает.';
$out[] = '';
$out[] = 'Итого: атак с побочным эффектом в данных **' . count($rows) . '**, реализовано **' . count($implemented) . '**,';
$out[] = 'не реализовано **' . (count($rows) - count($implemented)) . '**. Ниже они разбиты на группы по данным.';
$out[] = '';
$out[] = '## Как делать';
$out[] = '';
$out[] = '1. Для каждой атаки добавить обработку в `BattleMoveEffectCatalog` (и, если эффект влияет на ход';
$out[] = '   боя, — в `BattleEngineService`), как это сделано для `spikes`, `stealth rock`, `sticky web`.';
$out[] = '2. Эффект брать из данных по колонкам `attac_dop.dop_effc`, `tip_s`, `setting` и множителям';
$out[] = '   `def/atc/sdef/satc/speed/acc`, а не выдумывать: у каждой атаки ниже они указаны.';
$out[] = '3. Проверять: `php -l`, гейт `tools/open_test_regression.php --profile=quick --login=Tacos --password=jungheinrick`';
$out[] = '   (ожидание `Passed: 25, failed: 0, skipped: 0`), живой бой — PvE хотя бы на одной атаке из группы.';
$out[] = '';
$out[] = '## Группы (по убыванию количества атак)';
$out[] = '';

foreach ($groups as $groupKey => $names) {
    $out[] = '### ' . $groupKey . ' — атак: ' . count($names);
    $out[] = '';
    foreach ($names as $name) {
        $out[] = '- ' . $name;
    }
    $out[] = '';
}

$path = $root . '/docs/design/SPECIAL_ATTACKS_SPEC.md';
file_put_contents($path, implode("\n", $out) . "\n");

echo 'всего атак с эффектом: ' . count($rows) . ', реализовано: ' . count($implemented) . ', в спецификации: ' . (count($rows) - count($implemented)) . "\n";
echo 'групп: ' . count($groups) . "\n";
foreach (array_slice($groups, 0, 6, true) as $groupKey => $names) {
    echo '  ' . count($names) . ' атак: ' . $groupKey . "\n";
}
echo 'записано: docs/design/SPECIAL_ATTACKS_SPEC.md' . "\n";
