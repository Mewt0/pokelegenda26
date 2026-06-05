<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');

$evolutions = new PokemonEvolutionRepository($db);
$inventory = new InventoryRepository($db, $evolutions);
$safeStorage = new SafeStorageRepository($db);
$inventory->setSafeStorageRepository($safeStorage);
$rewards = new RewardRepository($db, $safeStorage);
$inventory->setRewardRepository($rewards);
$quests = new QuestRepository($db);
$locations = new LocationRepository($db);
$pokemon = new PokemonRepository($db);
$content = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
$npcs = new NpcDialogService($db, $locations, $quests, $inventory, $pokemon, $content, $rewards);

$results = [];
$userId = userId($db, 'Tacos');
assertTrue($results, 'user.tacos.exists', $userId > 0, 'id=' . $userId);

$starterDefinition = $quests->definition(1);
assertTrue($results, 'quest.starter.definition', $starterDefinition !== [] && (int) ($starterDefinition['enabled'] ?? 0) === 1);
assertTrue($results, 'quest.starter.steps', count($quests->steps(1)) >= 4, 'steps=' . count($quests->steps(1)));

$battleDefinition = $quests->definition(101);
$battleSteps = $quests->steps(101);
assertTrue($results, 'quest.battle.definition', $battleDefinition !== [] && (int) ($battleDefinition['enabled'] ?? 0) === 1);
assertTrue($results, 'quest.battle.step', hasStepAction($battleSteps, 'fpe_first_battle'));

$spikeDefinition = $quests->definition(2);
$spikeSteps = $quests->steps(2);
assertTrue($results, 'quest.spike.definition', $spikeDefinition !== [] && (int) ($spikeDefinition['enabled'] ?? 0) === 1);
assertTrue($results, 'quest.spike.steps.complete', count($spikeSteps) >= 7, 'steps=' . count($spikeSteps));
foreach (['spike_story', 'old_woman_story', 'old_woman_turnin', 'amira_lake', 'airen_lake', 'articuno_cliffs', 'airen_finish'] as $actionKey) {
    assertTrue($results, 'quest.spike.step.' . $actionKey, hasStepAction($spikeSteps, $actionKey));
}

$dailyDefinition = $quests->definition(6);
assertTrue($results, 'quest.repeatable.definition', $dailyDefinition !== [] && (int) ($dailyDefinition['repeatable'] ?? 0) === 1);
assertTrue($results, 'quest.reward.cooldown.metadata', isset($dailyDefinition['reward_json']) && str_contains((string) $dailyDefinition['reward_json'], 'cooldown'));

if ($userId <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    prepareUser($db, $userId);
    $quests->createOrUpdate($userId, 1, 10, 1);

    setLocation($db, $userId, 1);
    $spikeStart = $npcs->action($userId, 1, ['quest_npc' => '3', 'do' => '1'], 'quest_start:2:2');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.start.ui', ($spikeStart['ok'] ?? false) === true, dialogText($spikeStart));
    assertTrue($results, 'quest.spike.state.started', $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 2 && (int) ($spikeState['gotov'] ?? 0) === 0);

    $spikeJournal = questFromJournal($quests->journalForUser($userId), 2);
    assertTrue($results, 'quest.spike.journal.steps', count($spikeJournal['steps'] ?? []) >= 7, json_encode($spikeJournal['steps'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    assertTrue($results, 'quest.spike.journal.map_hint', isset(($spikeJournal['steps'][1] ?? [])['navigation']['targetLocationId']));

    setLocation($db, $userId, 7);
    $oldWomanStart = $npcs->action($userId, 7, ['quest_npc' => '1', 'do' => '4'], 'quest_update:2:3:0');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.old_woman.accept', ($oldWomanStart['ok'] ?? false) === true, dialogText($oldWomanStart));
    assertTrue($results, 'quest.spike.old_woman.state', $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 3);

    createTempPokemon($db, $userId, 16, 10, 1);
    for ($i = 0; $i < 3; $i++) {
        createTempPokemon($db, $userId, 48, 25, 1, 9);
    }
    $oldWomanTurnIn = $npcs->action($userId, 7, ['quest_npc' => '1', 'do' => '1'], 'quest_old_woman_turnin');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.old_woman.turnin', ($oldWomanTurnIn['ok'] ?? false) === true, dialogText($oldWomanTurnIn));
    assertTrue($results, 'quest.spike.old_woman.turnin_state', $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 4);

    setLocation($db, $userId, 11);
    $amira = $npcs->action($userId, 11, ['quest_npc' => '1', 'do' => '3'], 'quest_update:2:5:0');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.amira.progress', ($amira['ok'] ?? false) === true && $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 5, dialogText($amira));

    $airen = $npcs->action($userId, 11, ['quest_npc' => '2', 'do' => '5'], 'quest_update:2:6:0');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.airen.progress', ($airen['ok'] ?? false) === true && $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 6, dialogText($airen));

    setLocation($db, $userId, 13);
    $articuno = $npcs->action($userId, 13, ['quest_npc' => '1', 'do' => '2'], 'quest_update:2:7:0');
    $spikeState = $quests->findForUser($userId, 2);
    assertTrue($results, 'quest.spike.articuno.progress', ($articuno['ok'] ?? false) === true && $spikeState !== null && (int) ($spikeState['process'] ?? 0) === 7, dialogText($articuno));

    setLocation($db, $userId, 11);
    $coinsBeforeSpikeFinish = $inventory->countItem($userId, 1);
    $moonBeforeSpikeFinish = $inventory->countItem($userId, 44);
    $spikeFinish = $npcs->action($userId, 11, ['quest_npc' => '2', 'do' => '1'], 'quest_airen_complete');
    assertTrue($results, 'quest.spike.finish.ui', ($spikeFinish['ok'] ?? false) === true, dialogText($spikeFinish));
    assertTrue($results, 'quest.spike.completed', $quests->isCompleted($userId, 2));
    assertTrue($results, 'quest.spike.reward.coins', $inventory->countItem($userId, 1) >= $coinsBeforeSpikeFinish + 15000);
    assertTrue($results, 'quest.spike.reward.moon_stone', $inventory->countItem($userId, 44) >= $moonBeforeSpikeFinish + 1);

    setLocation($db, $userId, 3);
    for ($i = 0; $i < 5; $i++) {
        createTempPokemon($db, $userId, 11, 9, 1);
    }

    $coinsBefore = $inventory->countItem($userId, 1);
    $ballsBefore = $inventory->countItem($userId, 3);
    $notificationsBefore = countNotifications($db, $userId);

    $start = $npcs->action($userId, 3, ['quest_npc' => '2', 'do' => '1'], 'quest_metapod_start');
    $stateAfterStart = $quests->findForUser($userId, 6);
    assertTrue($results, 'quest.repeatable.start.ui', ($start['ok'] ?? false) === true, (string) ($start['message'] ?? ''));
    assertTrue($results, 'quest.repeatable.state.active', $stateAfterStart !== null && (int) ($stateAfterStart['process'] ?? 0) === 10 && (int) ($stateAfterStart['gotov'] ?? 0) === 0);

    $secondStart = $quests->startFromDefinition($userId, 6);
    assertTrue($results, 'quest.repeatable.active.not_restarted', ($secondStart['ok'] ?? false) === false, (string) ($secondStart['message'] ?? ''));

    $turnIn = $npcs->action($userId, 3, ['quest_npc' => '2', 'do' => '1'], 'quest_metapod_turnin');
    $stateAfterTurnIn = $quests->findForUser($userId, 6);
    assertTrue($results, 'quest.repeatable.turnin.ui', ($turnIn['ok'] ?? false) === true, (string) ($turnIn['message'] ?? ''));
    assertTrue($results, 'quest.reward.coins', $inventory->countItem($userId, 1) >= $coinsBefore + 10000, 'before=' . $coinsBefore . ', after=' . $inventory->countItem($userId, 1));
    assertTrue($results, 'quest.reward.balls', $inventory->countItem($userId, 3) >= $ballsBefore + 10, 'before=' . $ballsBefore . ', after=' . $inventory->countItem($userId, 3));
    assertTrue($results, 'quest.reward.notification', countNotifications($db, $userId) > $notificationsBefore);
    assertTrue($results, 'quest.cooldown.state', $stateAfterTurnIn !== null && (int) ($stateAfterTurnIn['process'] ?? 0) === 1 && (int) ($stateAfterTurnIn['time'] ?? 0) > time());

    $cooldownJournal = questFromJournal($quests->journalForUser($userId), 6);
    assertTrue($results, 'quest.cooldown.journal.status', ($cooldownJournal['status'] ?? '') === 'cooldown', (string) ($cooldownJournal['status'] ?? 'missing'));
    assertTrue($results, 'quest.cooldown.journal.can_start_false', ($cooldownJournal['can_start'] ?? true) === false);
    assertTrue($results, 'quest.journal.progress_present', isset($cooldownJournal['progress']['percent']), json_encode($cooldownJournal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    assertTrue($results, 'quest.journal.reward_view_present', array_key_exists('reward_view', $cooldownJournal), json_encode($cooldownJournal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    assertTrue($results, 'quest.journal.step_progress_present', isset(($cooldownJournal['steps'][0] ?? [])['progress']['percent']), json_encode($cooldownJournal['steps'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $track = $quests->trackQuest($userId, 6);
    $trackedJournal = $quests->journalForUser($userId);
    $trackedQuest = questFromJournal($trackedJournal, 6);
    assertTrue($results, 'quest.track.set', ($track['ok'] ?? false) === true && (int) ($trackedJournal['tracked_quest_id'] ?? 0) === 6, (string) ($track['message'] ?? ''));
    assertTrue($results, 'quest.track.flag', ($trackedQuest['tracked'] ?? false) === true);

    $untrack = $quests->trackQuest($userId, 0);
    assertTrue($results, 'quest.track.clear', ($untrack['ok'] ?? false) === true && $quests->trackedQuestId($userId) === 0, (string) ($untrack['message'] ?? ''));

    $cooldownStart = $npcs->action($userId, 3, ['quest_npc' => '2', 'do' => '1'], 'quest_metapod_start');
    $stateAfterCooldownStart = $quests->findForUser($userId, 6);
    assertTrue(
        $results,
        'quest.cooldown.start.blocked',
        ($cooldownStart['ok'] ?? false) === true && str_contains(dialogText($cooldownStart), 'позже'),
        dialogText($cooldownStart)
    );
    assertTrue($results, 'quest.cooldown.not_reset', $stateAfterCooldownStart !== null && (int) ($stateAfterCooldownStart['process'] ?? 0) === 1 && (int) ($stateAfterCooldownStart['time'] ?? 0) > time());

    $db->prepare('UPDATE quest SET process = 1, gotov = 0, time = :time WHERE user_id = :user AND quest_id = 6 LIMIT 1')
        ->execute(['time' => time() - 1, 'user' => $userId]);
    $readyJournal = questFromJournal($quests->journalForUser($userId), 6);
    assertTrue($results, 'quest.repeatable.ready.status', ($readyJournal['status'] ?? '') === 'available', (string) ($readyJournal['status'] ?? 'missing'));
    assertTrue($results, 'quest.repeatable.ready.can_start', ($readyJournal['can_start'] ?? false) === true);

    $repeatStart = $quests->startFromDefinition($userId, 6);
    $stateAfterRepeatStart = $quests->findForUser($userId, 6);
    assertTrue($results, 'quest.repeatable.restart.after_cooldown', ($repeatStart['ok'] ?? false) === true, (string) ($repeatStart['message'] ?? ''));
    assertTrue($results, 'quest.repeatable.restart.state', $stateAfterRepeatStart !== null && (int) ($stateAfterRepeatStart['process'] ?? 0) === 10 && (int) ($stateAfterRepeatStart['time'] ?? 0) === 0);
} catch (Throwable $e) {
    fail($results, 'fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function prepareUser(PDO $db, int $userId): void
{
    $db->prepare('UPDATE users SET buildmy = 3, pve = 0, pvp = 0, trade = 0, battleid = 0, pve_button = 0, atack_poke = 0 WHERE id = :user LIMIT 1')
        ->execute(['user' => $userId]);
    $db->prepare('DELETE FROM quest WHERE user_id = :user AND quest_id IN (1, 2, 6)')
        ->execute(['user' => $userId]);
}

function setLocation(PDO $db, int $userId, int $locationId): void
{
    $db->prepare('UPDATE users SET buildmy = :location, pve = 0, pvp = 0, trade = 0, battleid = 0 WHERE id = :user LIMIT 1')
        ->execute(['location' => $locationId, 'user' => $userId]);
}

function createTempPokemon(PDO $db, int $userId, int $baseId, int $level, int $active, int $nature = 16): int
{
    $baseStmt = $db->prepare('SELECT title, hp, atk, def, satk, sdef, speed, ability_key FROM poke_base WHERE id = :base LIMIT 1');
    $baseStmt->execute(['base' => $baseId]);
    $base = $baseStmt->fetch(PDO::FETCH_ASSOC) ?: [
        'title' => 'Pokemon #' . $baseId,
        'hp' => 45,
        'atk' => 45,
        'def' => 45,
        'satk' => 45,
        'sdef' => 45,
        'speed' => 45,
        'ability_key' => null,
    ];
    $pokemonId = nextId($db, 'pok_user', 'id');
    $hp = max(1, (int) round((((1 + ((int) $base['hp'] * 2) + 100) * ($level / 100)) + 10)));
    $stat = static fn (int $value): int => max(1, (int) round((((1 + ($value * 2)) * ($level / 100)) + 5)));
    $db->prepare(
        'INSERT INTO pok_user
            (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
             reproduction, happy, datemay, usersone, sprz, item, ability_key)
         VALUES
            (:id, :user, :base, :name, :active, 0, :level, 1, :nature, :hp_my, :hp_max, 0, 100,
             :atk, :def, :satk, :sdef, :speed, 0, 0, 0, 0, 0, 0,
             1, 1, 1, 1, 1, 1, "normal", 0, 0,
             0, 0, NOW(), :user_one, 0, 0, :ability)'
    )->execute([
        'id' => $pokemonId,
        'user' => $userId,
        'base' => $baseId,
        'name' => (string) $base['title'],
        'active' => $active,
        'level' => $level,
        'nature' => $nature,
        'hp_my' => $hp,
        'hp_max' => $hp,
        'atk' => $stat((int) $base['atk']),
        'def' => $stat((int) $base['def']),
        'satk' => $stat((int) $base['satk']),
        'sdef' => $stat((int) $base['sdef']),
        'speed' => $stat((int) $base['speed']),
        'user_one' => $userId,
        'ability' => $base['ability_key'] ?? null,
    ]);

    return $pokemonId;
}

function questFromJournal(array $journal, int $questId): array
{
    foreach (($journal['quests'] ?? []) as $quest) {
        if ((int) ($quest['id'] ?? 0) === $questId) {
            return $quest;
        }
    }

    return [];
}

function dialogText(array $payload): string
{
    return (string) ($payload['npc']['text'] ?? $payload['message'] ?? '');
}

function hasStepAction(array $steps, string $action): bool
{
    foreach ($steps as $step) {
        if ((string) ($step['action_key'] ?? '') === $action) {
            return true;
        }
    }

    return false;
}

function countNotifications(PDO $db, int $userId): int
{
    if (!tableExists($db, 'game_notifications')) {
        return 0;
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM game_notifications WHERE user_id = :user');
    $stmt->execute(['user' => $userId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function nextId(PDO $db, string $table, string $column): int
{
    return (int) ($db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
    );
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function assertTrue(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['status' => $ok ? 'OK' : 'FAIL', 'label' => $label, 'detail' => $detail];
}

function fail(array &$results, string $label, string $detail): void
{
    $results[] = ['status' => 'FAIL', 'label' => $label, 'detail' => $detail];
}

function hasFailures(array $results): bool
{
    foreach ($results as $row) {
        if (($row['status'] ?? '') === 'FAIL') {
            return true;
        }
    }
    return false;
}

function printResults(array $results): void
{
    $failures = 0;
    foreach ($results as $row) {
        if ($row['status'] === 'FAIL') {
            $failures++;
        }
        printf("[%s] %s%s\n", $row['status'], $row['label'], $row['detail'] !== '' ? ' - ' . $row['detail'] : '');
    }
    printf("\nResult: %d checks, %d failures.\n", count($results), $failures);
}
