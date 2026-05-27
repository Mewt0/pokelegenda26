<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');

$db = Connection::make(require APP_ROOT . '/config/database.php');
$mode = strtolower((string) ($argv[1] ?? 'status'));
$minutes = max(5, min(240, (int) ($argv[2] ?? 90)));
$chance = max(1.0, min(100.0, (float) ($argv[3] ?? 85)));
$tag = 'QA_TEMP_GAMEPLAY_DROP';
$now = time();

if (!tableExists($db, 'admin_drop_rules') || !tableExists($db, 'game_event_boosts')) {
    fwrite(STDERR, "Required tables admin_drop_rules/game_event_boosts are missing.\n");
    exit(1);
}

if ($mode === 'on') {
    enableQaDrop($db, $tag, $now, $minutes, $chance);
    printStatus($db, $tag);
    exit(0);
}

if ($mode === 'off') {
    $db->prepare('UPDATE admin_drop_rules SET enabled = 0, updated_at = :time WHERE note LIKE :tag')
        ->execute(['time' => $now, 'tag' => $tag . '%']);
    $db->prepare('UPDATE game_event_boosts SET enabled = 0, ends_at = :ends, updated_at = :updated WHERE note LIKE :tag')
        ->execute(['ends' => $now, 'updated' => $now, 'tag' => $tag . '%']);
    printStatus($db, $tag);
    exit(0);
}

if ($mode === 'status') {
    printStatus($db, $tag);
    exit(0);
}

fwrite(STDERR, "Usage: php tools/qa_drop_mode.php on [minutes] [chancePercent]\n");
fwrite(STDERR, "       php tools/qa_drop_mode.php off\n");
fwrite(STDERR, "       php tools/qa_drop_mode.php status\n");
exit(1);

function enableQaDrop(PDO $db, string $tag, int $now, int $minutes, float $chance): void
{
    $expires = $now + ($minutes * 60);

    $db->prepare(
        'INSERT INTO game_event_boosts (title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note, created_by, created_at, updated_at)
         VALUES ("QA: ускоренный дроп", "drop", 20.00, "pve", :starts, :ends, 1, :note, 0, :created, :updated)'
    )->execute([
        'starts' => $now,
        'ends' => $expires,
        'note' => $tag . ': auto-off by ends_at',
        'created' => $now,
        'updated' => $now,
    ]);

    foreach ([13 => 'QA: перо Pidgeotto', 14 => 'QA: перо Spearow'] as $itemId => $label) {
        if (!itemExists($db, $itemId)) {
            continue;
        }

        $existing = $db->prepare('SELECT id FROM admin_drop_rules WHERE item_id = :item AND note LIKE :note LIMIT 1');
        $existing->execute(['item' => $itemId, 'note' => $tag . '%']);
        $ruleId = (int) ($existing->fetchColumn() ?: 0);

        if ($ruleId > 0) {
            $db->prepare(
                'UPDATE admin_drop_rules
                    SET chance_percent = :chance, min_count = 1, max_count = 3, enabled = 1, updated_at = :time
                  WHERE id = :id LIMIT 1'
            )->execute(['chance' => $chance, 'time' => $now, 'id' => $ruleId]);
            continue;
        }

        $db->prepare(
            'INSERT INTO admin_drop_rules
                (item_id, location_id, pokebuild_id, pokemon_base_id, source_type, chance_percent,
                 min_count, max_count, time_start, time_end, quest_id, quest_process, quest_complete,
                 enabled, note, created_by, created_at, updated_at)
             VALUES
                (:item, 0, 0, 0, "wild", :chance, 1, 3, "00:00:00", "23:59:59", 0, 0, 0,
                 1, :note, 0, :created, :updated)'
        )->execute([
            'item' => $itemId,
            'chance' => $chance,
            'note' => $tag . ': ' . $label,
            'created' => $now,
            'updated' => $now,
        ]);
    }
}

function printStatus(PDO $db, string $tag): void
{
    echo "QA drop mode status\n";

    $boosts = $db->prepare(
        'SELECT id, title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note
           FROM game_event_boosts
          WHERE note LIKE :tag
          ORDER BY id DESC'
    );
    $boosts->execute(['tag' => $tag . '%']);
    foreach ($boosts->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        echo 'BOOST ', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    }

    $rules = $db->prepare(
        'SELECT r.id, r.item_id, i.name, r.chance_percent, r.min_count, r.max_count, r.enabled, r.note
           FROM admin_drop_rules r
           LEFT JOIN items i ON i.id = r.item_id
          WHERE r.note LIKE :tag
          ORDER BY r.id ASC'
    );
    $rules->execute(['tag' => $tag . '%']);
    foreach ($rules->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        echo 'RULE ', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    }
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
    );
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function itemExists(PDO $db, int $itemId): bool
{
    $stmt = $db->prepare('SELECT 1 FROM items WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $itemId]);
    return (bool) $stmt->fetchColumn();
}
