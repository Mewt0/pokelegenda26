<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Repository\TransportRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');

$content = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
$rawContent = require APP_ROOT . '/config/location_content.php';
$locations = new LocationRepository($db);
$evolutions = new PokemonEvolutionRepository($db);
$inventory = new InventoryRepository($db, $evolutions);
$safeStorage = new SafeStorageRepository($db);
$inventory->setSafeStorageRepository($safeStorage);
$rewards = new RewardRepository($db, $safeStorage);
$inventory->setRewardRepository($rewards);
$quests = new QuestRepository($db);
$pokemon = new PokemonRepository($db);
$npcs = new NpcDialogService($db, $locations, $quests, $inventory, $pokemon, $content, $rewards);

$results = [];
$userId = userId($db, 'Tacos');
assertTrue($results, 'user.tacos.exists', $userId > 0, 'id=' . $userId);

if ($userId <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    $totalNpcs = 0;
    $legacyDescriptions = [];
    $failedDialogs = [];
    $technicalDialogs = [];
    $badChoices = [];
    $emptyRows = [];

    foreach ($rawContent as $locationId => $rawLocation) {
        $locationId = (int) $locationId;
        $room = $content->find($locationId);
        if (hasTechnicalRuntimeText((string) ($room['about'] ?? ''))) {
            $legacyDescriptions[] = $locationId;
        }

        foreach (($room['npcs'] ?? []) as $npc) {
            $totalNpcs++;
            $params = is_array($npc['params'] ?? null) ? $npc['params'] : [];
            if ((string) ($npc['title'] ?? '') === '' || $params === []) {
                $emptyRows[] = sprintf('%d:%s', $locationId, (string) ($npc['title'] ?? ''));
                continue;
            }

            prepareUser($db, $userId, $locationId);
            $dialog = $npcs->open($userId, $locationId, $params);
            $title = (string) ($dialog['npc']['title'] ?? '');
            $text = (string) ($dialog['npc']['text'] ?? '');
            $choices = is_array($dialog['npc']['choices'] ?? null) ? $dialog['npc']['choices'] : [];
            if (($dialog['ok'] ?? false) !== true || trim($title) === '' || trim($text) === '' || $choices === []) {
                $failedDialogs[] = sprintf('%d:%s:%s', $locationId, (string) ($npc['title'] ?? ''), (string) ($dialog['message'] ?? 'empty dialog'));
                continue;
            }

            $payload = json_encode($dialog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
            if (hasTechnicalRuntimeText($payload)) {
                $technicalDialogs[] = sprintf('%d:%s', $locationId, $title);
            }

            foreach ($choices as $choice) {
                if (
                    empty($choice['close'])
                    && (string) ($choice['route'] ?? '') === ''
                    && (string) ($choice['action'] ?? '') === ''
                    && !is_array($choice['params'] ?? null)
                ) {
                    $badChoices[] = sprintf('%d:%s:%s', $locationId, $title, (string) ($choice['label'] ?? ''));
                }
            }
        }
    }

    prepareUser($db, $userId, TransportRepository::PLANE_LOCATION_ID);
    foreach ([['npc' => 'flight_status'], ['npc' => 'flight_exit']] as $params) {
        $dialog = $npcs->open($userId, TransportRepository::PLANE_LOCATION_ID, $params);
        assertTrue(
            $results,
            'npc.flight.' . (string) $params['npc'],
            ($dialog['ok'] ?? false) === true && hasChoiceRoute($dialog, '/game/transport') && !hasTechnicalRuntimeText(json_encode($dialog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''),
            (string) ($dialog['npc']['text'] ?? $dialog['message'] ?? '')
        );
    }

    assertTrue($results, 'npc.map.count', $totalNpcs >= 80, 'count=' . $totalNpcs);
    assertTrue($results, 'npc.map.no_empty_rows', $emptyRows === [], implode('; ', $emptyRows));
    assertTrue($results, 'npc.location_descriptions.no_legacy_runtime', $legacyDescriptions === [], implode(',', $legacyDescriptions));
    assertTrue($results, 'npc.dialogs.open_all', $failedDialogs === [], implode('; ', $failedDialogs));
    assertTrue($results, 'npc.dialogs.no_technical_runtime_text', $technicalDialogs === [], implode('; ', $technicalDialogs));
    assertTrue($results, 'npc.dialogs.choices_have_action', $badChoices === [], implode('; ', $badChoices));
} catch (Throwable $e) {
    fail($results, 'npc.runtime.fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function prepareUser(PDO $db, int $userId, int $locationId): void
{
    $db->prepare(
        'UPDATE users
            SET buildmy = :location, pve = 0, pvp = 0, trade = 0, battleid = 0, pve_button = 0, atack_poke = 0
          WHERE id = :user
          LIMIT 1'
    )->execute(['location' => $locationId, 'user' => $userId]);
}

function hasTechnicalRuntimeText(string $value): bool
{
    foreach (['game.php?go=', '/game.php', '<form', '$_SESSION', 'legacy', 'iframe', 'PHP', 'PvE API', 'NPC engine'] as $marker) {
        if (stripos($value, $marker) !== false) {
            return true;
        }
    }

    return false;
}

function hasChoiceRoute(array $dialog, string $route): bool
{
    foreach (($dialog['npc']['choices'] ?? []) as $choice) {
        if ((string) ($choice['route'] ?? '') === $route) {
            return true;
        }
    }

    return false;
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function fail(array &$results, string $name, string $details): void
{
    assertTrue($results, $name, false, $details);
}

function hasFailures(array $results): bool
{
    foreach ($results as $result) {
        if (!($result['ok'] ?? false)) {
            return true;
        }
    }

    return false;
}

function printResults(array $results): void
{
    foreach ($results as $result) {
        echo sprintf(
            "[%s] %s%s\n",
            ($result['ok'] ?? false) ? 'OK' : 'FAIL',
            $result['name'] ?? 'check',
            ($result['details'] ?? '') !== '' ? ' — ' . $result['details'] : ''
        );
    }
    $passed = count(array_filter($results, static fn (array $result): bool => (bool) ($result['ok'] ?? false)));
    echo sprintf("Summary: %d/%d passed\n", $passed, count($results));
}
