<?php
declare(strict_types=1);

/**
 * Authorized HTTP smoke checks for the new game API.
 *
 * Usage:
 *   php tools/http_smoke.php --base=http://pokemonchic.com --login=Tacos --password=...
 *   php tools/http_smoke.php --login=Tacos --password=... --expect-happiness=3
 *   php tools/http_smoke.php --login=Tacos --password=... --mutate-daycare
 *   php tools/http_smoke.php --login=Tacos --password=... --battle=catch
 *
 * Default mode is read-only except for one CSRF-negative request that is expected
 * to be rejected. Mutating checks are opt-in.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required for HTTP smoke checks.\n");
    exit(2);
}

$options = parseOptions($argv);
if (isset($options['help'])) {
    echo helpText();
    exit(0);
}

$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login = (string) ($options['login'] ?? getenv('SMOKE_LOGIN') ?: '');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
$expectHappiness = isset($options['expect-happiness']) ? (float) $options['expect-happiness'] : null;
$mutateDaycare = isset($options['mutate-daycare']);
$battleMode = (string) ($options['battle'] ?? 'none');

if ($login === '' || $password === '') {
    fwrite(STDERR, "Missing --login/--password or SMOKE_LOGIN/SMOKE_PASSWORD.\n\n" . helpText());
    exit(2);
}

$client = new SmokeClient($baseUrl);
$results = [];

try {
    $home = $client->request('GET', '/');
    $loginCsrf = extractCsrf($home['body']);
    assertTrue($results, 'auth.csrf.home', $loginCsrf !== '', 'CSRF token found on /');

    $client->request('POST', '/login', [
        '_csrf' => $loginCsrf,
        'LOGIN' => $login,
        'PASSWORD' => $password,
    ]);

    $game = $client->request('GET', '/game');
    $csrf = extractCsrf($game['body']);
    assertTrue($results, 'auth.login.game', $game['status'] === 200 && $csrf !== '', 'Logged in and game CSRF found');

    htmlCheck($client, $results, 'page.quests', '/game/quests', 'Квесты');
    htmlCheck($client, $results, 'page.eggs', '/game/eggs', 'Яйца');
    htmlCheck($client, $results, 'page.pokemon_market', '/game/market/pokemon', 'Комиссионная лавка');

    [$state, $stateResponse] = jsonCheck($client, $results, 'game.state', 'GET', '/api/game/state');
    $locationId = (int) ($state['location']['id'] ?? 0);
    assertTrue($results, 'game.location', $locationId > 0, 'location_id=' . $locationId);

    [$events] = jsonCheck($client, $results, 'events.active', 'GET', '/api/events/active');
    $happinessMultiplier = (float) ($events['effectiveMultipliers']['pve']['happiness']['multiplier'] ?? 1);
    $happinessDetail = 'pve.happiness=' . number_format($happinessMultiplier, 2, '.', '');
    if ($expectHappiness !== null) {
        assertTrue(
            $results,
            'events.happiness.expected',
            abs($happinessMultiplier - $expectHappiness) < 0.001,
            $happinessDetail . ', expected=' . $expectHappiness
        );
    } else {
        assertTrue($results, 'events.happiness.present', $happinessMultiplier >= 1.0, $happinessDetail);
    }

    [$quests] = jsonCheck($client, $results, 'quests.journal', 'GET', '/api/quests');
    assertTrue(
        $results,
        'quests.journal.shape',
        isset($quests['quests'], $quests['summary']) && is_array($quests['quests']) && is_array($quests['summary']),
        'quests=' . count($quests['quests'] ?? [])
    );

    [$inventoryAll] = jsonCheck($client, $results, 'inventory.page.all', 'GET', '/api/inventory/page?page=1');
    assertTrue($results, 'inventory.categories', count($inventoryAll['categories'] ?? []) >= 8, 'categories=' . count($inventoryAll['categories'] ?? []));

    foreach (['balls', 'consumables', 'evolution', 'tm', 'quest', 'drop'] as $category) {
        [$payload] = jsonCheck($client, $results, 'inventory.category.' . $category, 'GET', '/api/inventory/page?category=' . rawurlencode($category));
        assertTrue($results, 'inventory.category.' . $category . '.shape', isset($payload['items']) && is_array($payload['items']), 'items=' . count($payload['items'] ?? []));
    }

    [$moves] = jsonCheck($client, $results, 'pokemon.moves', 'GET', '/api/pokemon/moves');
    $pokemon = is_array($moves['pokemon'] ?? null) ? $moves['pokemon'] : [];
    assertTrue($results, 'pokemon.moves.shape', count($pokemon) > 0, 'pokemon=' . count($pokemon));

    [$battleInventory] = jsonCheck($client, $results, 'inventory.battle', 'GET', '/api/inventory/battle');
    assertTrue($results, 'inventory.battle.shape', isset($battleInventory['items']) && is_array($battleInventory['items']), 'battle_items=' . count($battleInventory['items'] ?? []));

    [$eggs] = jsonCheck($client, $results, 'eggs.list', 'GET', '/api/eggs');
    assertTrue($results, 'eggs.list.shape', isset($eggs['eggs']) && is_array($eggs['eggs']), 'eggs=' . count($eggs['eggs'] ?? []));

    [$pokemonMarket] = jsonCheck($client, $results, 'pokemon_market.list', 'GET', '/api/market/pokemon');
    assertTrue($results, 'pokemon_market.list.shape', isset($pokemonMarket['lots']) && is_array($pokemonMarket['lots']), 'lots=' . count($pokemonMarket['lots'] ?? []));

    [$commissionLots] = jsonCheck($client, $results, 'commission.lots', 'GET', '/api/commission/lots');
    assertTrue($results, 'commission.lots.shape', isset($commissionLots['lots'], $commissionLots['categories']) && is_array($commissionLots['lots']) && is_array($commissionLots['categories']), 'lots=' . count($commissionLots['lots'] ?? []));
    [$commissionSearch] = jsonCheck($client, $results, 'commission.lots.search', 'GET', '/api/commission/lots?q=Kyogre');
    assertTrue($results, 'commission.lots.search.shape', isset($commissionSearch['lots']) && is_array($commissionSearch['lots']), 'lots=' . count($commissionSearch['lots'] ?? []));
    [$commissionSellable] = jsonCheck($client, $results, 'commission.sellable', 'GET', '/api/commission/sellable');
    assertTrue($results, 'commission.sellable.shape', isset($commissionSellable['items'], $commissionSellable['pokemon'], $commissionSellable['eggs']), 'items=' . count($commissionSellable['items'] ?? []));
    [$commissionMy] = jsonCheck($client, $results, 'commission.my', 'GET', '/api/commission/my');
    assertTrue($results, 'commission.my.shape', isset($commissionMy['active'], $commissionMy['history']) && is_array($commissionMy['active']) && is_array($commissionMy['history']), 'active=' . count($commissionMy['active'] ?? []));

    [$profileCard] = jsonCheck($client, $results, 'profile.card', 'GET', '/api/profile/card');
    $profile = is_array($profileCard['profile'] ?? null) ? $profileCard['profile'] : [];
    assertTrue(
        $results,
        'profile.card.shape',
        isset($profile['user'], $profile['uid'], $profile['avatar'], $profile['rank'], $profile['clan'], $profile['party'], $profile['activeTeam'], $profile['gifts'], $profile['gymBadges'], $profile['badges'], $profile['badgeSummary'], $profile['friends'], $profile['social'])
            && is_array($profile['party'])
            && is_array($profile['activeTeam'])
            && is_array($profile['gifts'])
            && is_array($profile['gymBadges'])
            && is_array($profile['badges'])
            && is_array($profile['badgeSummary'])
            && is_array($profile['friends'])
            && is_array($profile['social']),
        'uid=' . (int) ($profile['uid'] ?? 0) . ', party=' . count($profile['party'] ?? []) . ', badges=' . count($profile['gymBadges'] ?? [])
    );
    assertTrue(
        $results,
        'profile.card.social_self',
        ($profile['social']['status'] ?? '') === 'self' && ($profile['viewerOwnsProfile'] ?? false) === true,
        'status=' . (string) ($profile['social']['status'] ?? 'missing')
    );
    $partyHeldHidden = true;
    foreach (($profile['party'] ?? []) as $pokemon) {
        if (array_key_exists('heldItem', $pokemon)) {
            $partyHeldHidden = false;
            break;
        }
    }
    assertTrue(
        $results,
        'profile.card.held_items_hidden',
        $partyHeldHidden,
        'party=' . count($profile['party'] ?? [])
    );

    [$history] = jsonCheck($client, $results, 'battle.history', 'GET', '/api/battle/history');
    assertTrue($results, 'battle.history.shape', isset($history['history']) && is_array($history['history']), 'history=' . count($history['history'] ?? []));

    $csrfFail = $client->request('POST', '/api/pokemon/nursery', ['pokemon_id' => '0', 'action' => 'store'], false);
    assertTrue($results, 'security.csrf.negative', $csrfFail['status'] === 419, 'status=' . $csrfFail['status']);

    $invalidNursery = $client->request('POST', '/api/pokemon/nursery', [
        '_csrf' => $csrf,
        'pokemon_id' => '0',
        'action' => 'store',
    ]);
    $invalidNurseryJson = decodeJson($invalidNursery['body']);
    assertTrue(
        $results,
        'daycare.validation',
        $invalidNursery['status'] === 200 && ($invalidNurseryJson['ok'] ?? true) === false,
        (string) ($invalidNurseryJson['message'] ?? 'validated')
    );

    if ($mutateDaycare) {
        runDaycareRoundtrip($client, $results, $csrf, $pokemon);
    } else {
        skip($results, 'daycare.roundtrip', 'read-only; pass --mutate-daycare to store/take one pokemon');
    }

    if ($locationId > 0) {
        $heal = $client->request('POST', '/api/location/npc/action', [
            '_csrf' => $csrf,
            'location_id' => (string) $locationId,
            'npc' => '1',
            'do_npc' => 'pc',
            'action' => 'joy_heal',
        ]);
        $healJson = decodeJson($heal['body']);
        if (($healJson['ok'] ?? false) === true) {
            assertTrue($results, 'heal.npc', true, (string) ($healJson['npc']['text'] ?? 'healed'));
        } else {
            skip($results, 'heal.npc', (string) ($healJson['message'] ?? 'current location has no Sister Joy'));
        }
    }

    runBattleSmoke($client, $results, $csrf, $battleMode);
} catch (Throwable $e) {
    fail($results, 'fatal', $e->getMessage());
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

/**
 * @return array<string,mixed>
 */
function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $options[$key] = $value;
        } else {
            $options[$arg] = true;
        }
    }
    return $options;
}

function helpText(): string
{
    return <<<TXT
Authorized Pokemon 8.0 HTTP smoke.

Options:
  --base=http://pokemonchic.com       Base URL, default SMOKE_BASE_URL or http://pokemonchic.com
  --login=Tacos                       Login, default SMOKE_LOGIN
  --password=secret                   Password, default SMOKE_PASSWORD
  --expect-happiness=3                Assert active PvE happiness multiplier equals this value
  --mutate-daycare                    Store one non-starter Pokemon and take it back
  --battle=none|state|force|catch|finish
                                      none/state are read-only; force/catch/finish are mutating

TXT;
}

function extractCsrf(string $html): string
{
    if (preg_match('/name=["\']_csrf["\'][^>]*value=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (preg_match('/data-csrf=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return '';
}

/**
 * @return array{0:array<string,mixed>,1:array<string,mixed>}
 */
function jsonCheck(SmokeClient $client, array &$results, string $label, string $method, string $path, array $data = []): array
{
    $response = $client->request($method, $path, $data);
    $json = decodeJson($response['body']);
    $ok = $response['status'] >= 200 && $response['status'] < 300 && $json !== [];
    assertTrue($results, $label, $ok, 'status=' . $response['status']);
    return [$json, $response];
}

function htmlCheck(SmokeClient $client, array &$results, string $label, string $path, string $needle): void
{
    $response = $client->request('GET', $path);
    assertTrue(
        $results,
        $label,
        $response['status'] === 200 && str_contains($response['body'], $needle),
        'status=' . $response['status']
    );
}

/**
 * @return array<string,mixed>
 */
function decodeJson(string $body): array
{
    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * @param list<array<string,string>> $results
 * @param list<array<string,mixed>> $pokemon
 */
function runDaycareRoundtrip(SmokeClient $client, array &$results, string $csrf, array $pokemon): void
{
    $candidate = null;
    foreach ($pokemon as $row) {
        if (!empty($row['active']) && empty($row['starter'])) {
            $candidate = $row;
            break;
        }
    }
    if ($candidate === null) {
        skip($results, 'daycare.roundtrip', 'no active non-starter pokemon');
        return;
    }

    $id = (int) ($candidate['id'] ?? 0);
    $store = $client->request('POST', '/api/pokemon/nursery', [
        '_csrf' => $csrf,
        'pokemon_id' => (string) $id,
        'action' => 'store',
    ]);
    $storeJson = decodeJson($store['body']);
    if (($storeJson['ok'] ?? false) !== true) {
        fail($results, 'daycare.store', (string) ($storeJson['message'] ?? 'store failed'));
        return;
    }
    assertTrue($results, 'daycare.store', true, (string) ($storeJson['message'] ?? 'stored'));

    $take = $client->request('POST', '/api/pokemon/nursery', [
        '_csrf' => $csrf,
        'pokemon_id' => (string) $id,
        'action' => 'take',
    ]);
    $takeJson = decodeJson($take['body']);
    assertTrue($results, 'daycare.take', ($takeJson['ok'] ?? false) === true, (string) ($takeJson['message'] ?? 'take checked'));
}

function runBattleSmoke(SmokeClient $client, array &$results, string $csrf, string $mode): void
{
    if (!in_array($mode, ['none', 'state', 'force', 'catch', 'finish'], true)) {
        fail($results, 'battle.mode', 'unknown mode ' . $mode);
        return;
    }

    if ($mode === 'none') {
        skip($results, 'battle.rewards.catch', 'read-only; pass --battle=catch or --battle=finish for mutating battle checks');
        return;
    }

    $startedBySmoke = false;
    if (in_array($mode, ['force', 'catch', 'finish'], true)) {
        $force = $client->request('POST', '/api/battle/pve/force', ['_csrf' => $csrf]);
        $forceJson = decodeJson($force['body']);
        if (($forceJson['wildEncounter']['started'] ?? false) === true) {
            $startedBySmoke = true;
            assertTrue($results, 'battle.force', true, 'battleId=' . (int) ($forceJson['wildEncounter']['battleId'] ?? 0));
        } elseif ($force['status'] === 403) {
            skip($results, 'battle.force', 'requires admin session');
        } else {
            skip($results, 'battle.force', (string) ($forceJson['wildEncounter']['message'] ?? $forceJson['message'] ?? 'not started'));
        }
    }

    [$state] = jsonCheck($client, $results, 'battle.pve.state', 'GET', '/api/battle/pve/state');
    if (empty($state['active']) && empty($state['finished'])) {
        skip($results, 'battle.active', 'no active battle');
        return;
    }

    $moves = $state['battle']['moves'] ?? $state['moves'] ?? [];
    assertTrue($results, 'battle.moves.not_empty', is_array($moves) && count($moves) > 0, 'moves=' . count(is_array($moves) ? $moves : []));

    if ($mode === 'force') {
        if ($startedBySmoke) {
            cleanupPveBattle($client, $results, $csrf);
        }
        return;
    }

    if ($mode === 'catch') {
        [$bag] = jsonCheck($client, $results, 'battle.catch.bag', 'GET', '/api/inventory/battle');
        $ball = null;
        foreach (($bag['items'] ?? []) as $item) {
            $id = (int) ($item['item_id'] ?? 0);
            $text = mb_strtolower((string) (($item['name'] ?? '') . ' ' . ($item['tittle'] ?? '') . ' ' . ($item['category'] ?? '')), 'UTF-8');
            if (in_array($id, [3, 90004, 90005], true) || str_contains($text, 'бол') || str_contains($text, 'ball')) {
                $ball = $item;
                break;
            }
        }
        if ($ball === null) {
            skip($results, 'battle.catch', 'no battle ball item');
            return;
        }
        $catch = $client->request('POST', '/api/battle/pve/action', [
            '_csrf' => $csrf,
            'action' => 'ball',
            'item_user_id' => (string) (int) ($ball['id'] ?? 0),
        ]);
        $catchJson = decodeJson($catch['body']);
        assertTrue($results, 'battle.catch.action', $catch['status'] === 200 && isset($catchJson['ok']), (string) ($catchJson['message'] ?? $catchJson['result'] ?? 'catch checked'));
        if ($startedBySmoke) {
            if (!empty($catchJson['finished']) || empty($catchJson['active'])) {
                ackPveBattle($client, $results, $csrf);
            } else {
                cleanupPveBattle($client, $results, $csrf);
            }
        }
        return;
    }

    if ($mode === 'finish') {
        for ($i = 0; $i < 25; $i++) {
            [$state] = jsonCheck($client, $results, 'battle.finish.state.' . $i, 'GET', '/api/battle/pve/state');
            if (!empty($state['finished']) || empty($state['active'])) {
                assertTrue($results, 'battle.finish.final', !empty($state['finished']) || empty($state['active']), 'finished');
                assertTrue($results, 'battle.finish.rewards.shape', isset($state['rewards']) || isset($state['battle']), 'rewards/battle present');
                if ($startedBySmoke) {
                    ackPveBattle($client, $results, $csrf);
                }
                return;
            }
            $moves = $state['battle']['moves'] ?? [];
            $move = null;
            if (is_array($moves)) {
                foreach ($moves as $candidate) {
                    if (is_array($candidate) && (int) ($candidate['id'] ?? 0) > 0 && (int) ($candidate['power'] ?? 0) > 0 && (int) ($candidate['pp'] ?? 1) > 0) {
                        $move = $candidate;
                        break;
                    }
                }
                $move ??= $moves[0] ?? null;
            }
            if (!is_array($move) || (int) ($move['id'] ?? 0) <= 0) {
                fail($results, 'battle.finish.move', 'no usable move');
                return;
            }
            $client->request('POST', '/api/battle/pve/action', [
                '_csrf' => $csrf,
                'action' => 'attack',
                'move_id' => (string) (int) $move['id'],
            ]);
        }
        fail($results, 'battle.finish.timeout', 'battle did not finish in 25 turns');
        if ($startedBySmoke) {
            cleanupPveBattle($client, $results, $csrf);
        }
    }
}

function cleanupPveBattle(SmokeClient $client, array &$results, string $csrf): void
{
    $escape = $client->request('POST', '/api/battle/pve/action', [
        '_csrf' => $csrf,
        'action' => 'escape',
    ]);
    $escapeJson = decodeJson($escape['body']);
    assertTrue($results, 'battle.cleanup.escape', $escape['status'] === 200 && isset($escapeJson['ok']), (string) ($escapeJson['message'] ?? $escapeJson['result'] ?? 'escape checked'));
    ackPveBattle($client, $results, $csrf);
}

function ackPveBattle(SmokeClient $client, array &$results, string $csrf): void
{
    $ack = $client->request('POST', '/api/battle/pve/ack-end', ['_csrf' => $csrf]);
    $ackJson = decodeJson($ack['body']);
    assertTrue($results, 'battle.cleanup.ack', $ack['status'] === 200 && (($ackJson['ok'] ?? false) === true), (string) ($ackJson['message'] ?? 'ack checked'));
}

/**
 * @param list<array<string,string>> $results
 */
function assertTrue(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['status' => $ok ? 'OK' : 'FAIL', 'label' => $label, 'detail' => $detail];
}

/**
 * @param list<array<string,string>> $results
 */
function fail(array &$results, string $label, string $detail): void
{
    $results[] = ['status' => 'FAIL', 'label' => $label, 'detail' => $detail];
}

/**
 * @param list<array<string,string>> $results
 */
function skip(array &$results, string $label, string $detail): void
{
    $results[] = ['status' => 'SKIP', 'label' => $label, 'detail' => $detail];
}

/**
 * @param list<array<string,string>> $results
 */
function hasFailures(array $results): bool
{
    foreach ($results as $row) {
        if (($row['status'] ?? '') === 'FAIL') {
            return true;
        }
    }
    return false;
}

/**
 * @param list<array<string,string>> $results
 */
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

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'pokemon8_smoke_cookie_') ?: '';
    }

    public function __destruct()
    {
        if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    /**
     * @param array<string,string> $data
     * @return array{status:int,body:string,url:string}
     */
    public function request(string $method, string $path, array $data = [], bool $follow = true): array
    {
        $method = strtoupper($method);
        $url = $this->baseUrl . (str_starts_with($path, '/') ? $path : '/' . $path);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to init curl.');
        }

        $headers = ['User-Agent: Pokemon8Smoke/1.0'];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => $follow,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/x-www-form-urlencoded;charset=UTF-8']));
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP error: ' . $error);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => substr((string) $raw, $headerSize),
            'url' => $effectiveUrl,
        ];
    }
}
