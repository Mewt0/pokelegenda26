<?php
declare(strict_types=1);

/**
 * Dump the legacy quest content (read-only) so it can be ported into the new layer.
 *
 *   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\quest_legacy_dump.php
 */

require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "== quest_list (full text) ==\n";
foreach ($db->query('SELECT * FROM quest_list ORDER BY id_q') as $row) {
    echo "--- id_q={$row['id_q']} dop={$row['dop']}\n";
    echo "name: {$row['name_q']}\n";
    echo "text: {$row['text']}\n\n";
}

echo "== quest_process (legacy steps) ==\n";
foreach ($db->query('SELECT * FROM quest_process ORDER BY q_id, process') as $row) {
    echo "q_id={$row['q_id']} process={$row['process']} text={$row['text']}\n";
}

echo "\n== quest_events ==\n";
foreach ($db->query('SELECT * FROM quest_events ORDER BY quest, etapes') as $row) {
    echo "id={$row['id']} quest={$row['quest']} users={$row['users']} etapes={$row['etapes']} "
        . "e1={$row['count_event_1']} e2={$row['count_event_2']} time={$row['time']}\n";
}

echo "\n== quest_poke sample (first 10) ==\n";
foreach ($db->query('SELECT * FROM quest_poke ORDER BY questid, id LIMIT 10') as $row) {
    echo "questid={$row['questid']} userid={$row['userid']} pokenum={$row['pokenum']} "
        . "cool={$row['coolpokemin']}-{$row['coolpokemax']}\n";
}

echo "\n== distinct quests referenced by quest_poke ==\n";
foreach ($db->query('SELECT questid, COUNT(*) c FROM quest_poke GROUP BY questid ORDER BY questid') as $row) {
    echo "questid={$row['questid']} rows={$row['c']}\n";
}

echo "\n== user progress distribution (legacy quest table) ==\n";
foreach ($db->query('SELECT quest_id, COUNT(*) users, MAX(process) max_process, SUM(gotov) done FROM quest GROUP BY quest_id ORDER BY quest_id') as $row) {
    echo "quest_id={$row['quest_id']} users={$row['users']} max_process={$row['max_process']} done={$row['done']}\n";
}
