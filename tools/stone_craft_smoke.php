<?php
declare(strict_types=1);

// Test-only namespace override: production RNG and source have no test hooks.
namespace Pokemon8\Game {
    function random_int(int $min, int $max): int {
        return $GLOBALS['stoneCraftQaRoll'] ?? \random_int($min, $max);
    }
}
namespace {
use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Repository\AdminGrantReceiptRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
Env::load(APP_ROOT . '/.env');
if (!AdminGrantReceiptRepository::localQa()) { fwrite(STDERR, "Local QA only.\n"); exit(2); }
$config = require APP_ROOT . '/config/database.php';
$source = Connection::make($config);
$schema = 'pokemonchic_stone_qa_' . bin2hex(random_bytes(6));
$created = false;
$passed = 0;
function checkCraft(bool $ok, string $name): void {
    if (!$ok) { throw new RuntimeException('FAIL ' . $name); }
    $GLOBALS['passed']++;
    echo "PASS $name\n";
}
try {
    $source->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $qaConfig = $config;
    $qaConfig['database'] = $schema;
    $db = Connection::make($qaConfig);
    $sourceName = str_replace('`', '``', $config['database']);
    foreach (['users', 'items', 'items_users', 'quest'] as $table) {
        $db->exec('CREATE TABLE `' . $table . '` LIKE `' . $sourceName . '`.`' . $table . '`');
    }
    $db->exec('INSERT INTO users SELECT * FROM `' . $sourceName . '`.users WHERE id = 1');
    $db->exec('INSERT INTO items SELECT * FROM `' . $sourceName . '`.items WHERE id BETWEEN 24 AND 44');
    $db->exec('UPDATE users SET activation=1, buildmy=1, pve=0, pvp=0, trade=0 WHERE id=1');
    $inventory = new InventoryRepository($db);
    $content = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
    $service = new NpcDialogService($db, new LocationRepository($db), new QuestRepository($db), $inventory, new PokemonRepository($db), $content);
    $params = ['quest_npc'=>'3', 'do'=>'3'];
    $opened = $service->open(1, 1, $params);
    $recipes = array_filter($opened['npc']['choices'] ?? [], static fn(array $c): bool => str_starts_with($c['action'] ?? '', 'craft_stone:'));
    checkCraft(!empty($opened['ok']) && count($recipes) === 5, 'spike.opens_five_recipes');
    checkCraft(count(array_filter($recipes, static fn(array $c): bool => !empty($c['disabled']))) === 5, 'missing_materials.disable_all');
    foreach ([1=>2, 2=>4, 4=>4] as $step=>$next) {
        $story = $service->open(1, 1, ['quest_npc'=>'3', 'do'=>(string)$step]);
        checkCraft(($story['npc']['choices'][0]['params']['do'] ?? '') === (string)$next, 'story.step_' . $step . '_not_craft');
    }
    $before = $db->query('SELECT * FROM items_users')->fetchAll();
    foreach ([[], ['quest_npc'=>'2'], ['npc'=>'2'], ['quest_npc'=>'3','npc'=>'1','do_npc'=>'pc']] as $bad) {
        checkCraft(empty($service->action(1, 1, $bad, 'craft_stone:fire')['ok']), 'forged_npc.rejected');
    }
    checkCraft(empty($service->action(1, 28, $params, 'craft_stone:fire')['ok']), 'remote_location.rejected');
    $db->exec('UPDATE users SET buildmy=11 WHERE id=1');
    checkCraft(empty($service->action(1, 11, $params, 'craft_stone:fire')['ok']), 'other_location.rejected');
    $db->exec('UPDATE users SET buildmy=1 WHERE id=1');
    checkCraft(empty($service->action(1, 1, $params, 'craft_stone:unknown')['ok']), 'unknown_recipe.rejected');
    $missing = $service->action(1, 1, $params, 'craft_stone:fire');
    checkCraft(str_contains($missing['npc']['text'] ?? '', 'Не хватает ресурсов'), 'missing_materials.no_attempt');
    checkCraft($db->query('SELECT * FROM items_users')->fetchAll() === $before, 'invalid_requests.no_inventory_writes');
    $recipes = [
        'thunder'=>[40,[39=>1,24=>5,29=>3,34=>1]],
        'fire'=>[41,[39=>1,25=>5,30=>3,35=>1]],
        'water'=>[42,[39=>1,26=>5,31=>3,36=>1]],
        'leaf'=>[43,[39=>1,27=>5,32=>3,37=>1]],
        'moon'=>[44,[39=>1,28=>5,33=>3,38=>1]],
    ];
    foreach ($recipes as $key=>[$output,$parts]) {
        foreach ([0,10] as $roll) {
            // Only the disposable schema is reset; live tables are never modified.
            $db->exec('DELETE FROM items_users');
            foreach ($parts as $item=>$count) { $inventory->addItem(1, $item, $count); }
            $GLOBALS['stoneCraftQaRoll'] = $roll;
            $result = $service->action(1, 1, $params, 'craft_stone:' . $key);
            checkCraft(!empty($result['ok']) && str_contains($result['npc']['text'] ?? '', $roll === 10 ? 'Успешно изготовлено' : 'Крафт не удался'), $key . '.outcome_' . $roll);
            checkCraft($inventory->countItem(1,$output) === ($roll === 10 ? 1 : 0), $key . '.exact_output_' . $roll);
            checkCraft(array_sum(array_map(static fn(int $id): int => $inventory->countItem(1,$id), array_keys($parts))) === 0, $key . '.materials_consumed_' . $roll);
            $return = $service->open(1,1,$result['npc']['choices'][0]['params']);
            checkCraft(count($return['npc']['choices'] ?? []) === 6, $key . '.return_to_recipes_' . $roll);
        }
    }
    unset($GLOBALS['stoneCraftQaRoll']);
    $db->exec('UPDATE users SET buildmy=28 WHERE id=1');
    checkCraft(empty($service->action(1,28,$params,'craft_stone:fire')['ok']), 'location_28.unconfigured_crafter_rejected');
    $fixture = require APP_ROOT . '/config/location_content.php';
    $fixture[28]['npcs'][] = ['title'=>'Мастеровой', 'params'=>['quest_npc'=>'3']];
    $configuredService = new NpcDialogService($db, new LocationRepository($db), new QuestRepository($db), $inventory, new PokemonRepository($db), new LocationContentRepository($fixture));
    checkCraft(count($configuredService->open(1,28,$params)['npc']['choices'] ?? []) === 6, 'location_28.configured_dispatch_supported');
    checkCraft((int)$db->query('SELECT COUNT(*) FROM quest')->fetchColumn() === 0, 'quest_progress.unchanged');
    echo "Stone craft: $passed/$passed\n";
} finally {
    if ($created && preg_match('/\Apokemonchic_stone_qa_[a-f0-9]{12}\z/D', $schema)) {
        $source->exec('DROP DATABASE `' . $schema . '`');
    }
}
}
