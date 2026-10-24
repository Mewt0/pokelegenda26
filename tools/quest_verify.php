<?php
declare(strict_types=1);

require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "== quests 2,3,4,7,8,150+ ==\n";
$stmt = $db->query('SELECT id, title, enabled, reward_json FROM quest_definitions WHERE id IN (2,3,4,7,8) OR id >= 150 ORDER BY id');
foreach ($stmt as $row) {
    echo sprintf("#%-4s enabled=%s  %-34s %s\n", $row['id'], $row['enabled'], $row['title'], $row['reward_json']);
}

echo "\n== steps for quest 8 ==\n";
$stmt = $db->query('SELECT step_no, title, action_key, required_process FROM quest_steps WHERE quest_id = 8 ORDER BY step_no');
foreach ($stmt as $row) {
    echo sprintf("  %d. %-40s action=%-22s process=%s\n", $row['step_no'], $row['title'], $row['action_key'], $row['required_process']);
}

echo "\n== total quests / steps ==\n";
echo 'quests: ' . (int) $db->query('SELECT COUNT(*) FROM quest_definitions')->fetchColumn() . "\n";
echo 'steps: ' . (int) $db->query('SELECT COUNT(*) FROM quest_steps')->fetchColumn() . "\n";
