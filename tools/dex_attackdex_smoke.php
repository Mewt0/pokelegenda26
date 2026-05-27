<?php
declare(strict_types=1);

/**
 * Authorized smoke for Pokedex / AttackDex APIs and battle-form dex records.
 *
 * Usage:
 *   php tools/dex_attackdex_smoke.php --login=Tacos --password=...
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

$options = parseOptions($argv);
$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login = (string) ($options['login'] ?? getenv('SMOKE_LOGIN') ?: '');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
if ($login === '' || $password === '') {
    fwrite(STDERR, "Missing --login/--password.\n");
    exit(2);
}

$client = new SmokeClient($baseUrl);
$results = [];

try {
    $home = $client->request('GET', '/');
    $csrf = extractCsrf($home['body']);
    assertTrue($results, 'auth.csrf.home', $csrf !== '', 'csrf=' . ($csrf !== '' ? 'yes' : 'no'));
    $client->request('POST', '/login', [
        '_csrf' => $csrf,
        'LOGIN' => $login,
        'PASSWORD' => $password,
    ]);

    $game = $client->request('GET', '/game');
    assertTrue(
        $results,
        'page.game.dex_overlay',
        $game['status'] === 200 && str_contains($game['body'], 'id="dexOverlay"') && str_contains($game['body'], 'dex-overlay.js'),
        'status=' . $game['status']
    );

    [$kyogreList] = jsonCheck($client, $results, 'api.dex.pokemon.search_kyogre', '/api/dex/pokemon?q=Kyogre&limit=20');
    assertTrue($results, 'api.dex.pokemon.search_kyogre.shape', hasItemId($kyogreList['items'] ?? [], 382), 'items=' . count($kyogreList['items'] ?? []));

    [$primalList] = jsonCheck($client, $results, 'api.dex.pokemon.form_primal', '/api/dex/pokemon?form=primal&limit=50');
    assertTrue(
        $results,
        'api.dex.pokemon.form_primal.items',
        hasItemId($primalList['items'] ?? [], 5017) && hasItemId($primalList['items'] ?? [], 5018),
        'ids=' . implode(',', array_slice(array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $primalList['items'] ?? []), 0, 8))
    );

    [$megaList] = jsonCheck($client, $results, 'api.dex.pokemon.form_mega', '/api/dex/pokemon?form=mega&limit=1000');
    assertTrue($results, 'api.dex.pokemon.form_mega.items', count($megaList['items'] ?? []) >= 20 && hasItemId($megaList['items'] ?? [], 5019), 'items=' . count($megaList['items'] ?? []));

    [$baseKyogre] = jsonCheck($client, $results, 'api.dex.pokemon.show_382', '/api/dex/pokemon/show?id=382');
    $baseForms = $baseKyogre['pokemon']['forms'] ?? [];
    assertTrue($results, 'api.dex.pokemon.show_382.forms', hasItemId($baseForms, 5017), 'forms=' . count($baseForms));

    foreach ([
        5017 => ['base' => 382, 'ability' => 'primordial_sea', 'name' => 'Primal Kyogre'],
        5018 => ['base' => 383, 'ability' => 'desolate_land', 'name' => 'Primal Groudon'],
        5019 => ['base' => 384, 'ability' => 'delta_stream', 'name' => 'Mega Rayquaza'],
    ] as $formId => $expected) {
        [$payload] = jsonCheck($client, $results, 'api.dex.pokemon.show_' . $formId, '/api/dex/pokemon/show?id=' . $formId);
        $pokemon = $payload['pokemon'] ?? [];
        assertTrue(
            $results,
            'api.dex.form_' . $formId . '.contract',
            (int) ($pokemon['id'] ?? 0) === $formId
                && (int) ($pokemon['baseId'] ?? 0) === (int) $expected['base']
                && (bool) ($pokemon['isForm'] ?? false) === true
                && (string) ($pokemon['ability']['key'] ?? '') === (string) $expected['ability'],
            'base=' . (int) ($pokemon['baseId'] ?? 0) . ', ability=' . (string) ($pokemon['ability']['key'] ?? '')
        );
        assertTrue(
            $results,
            'api.dex.form_' . $formId . '.content_inherited',
            count($pokemon['learnset'] ?? []) > 0 && isset($pokemon['sprites']['normal']),
            'learnset=' . count($pokemon['learnset'] ?? []) . ', sprite=' . (string) ($pokemon['sprites']['normal'] ?? '')
        );
    }

    [$dragonAscentList] = jsonCheck($client, $results, 'api.attackdex.search_dragon_ascent', '/api/dex/attacks?q=Dragon%20Ascent&limit=20');
    assertTrue($results, 'api.attackdex.search_dragon_ascent.items', hasItemId($dragonAscentList['items'] ?? [], 570), 'items=' . count($dragonAscentList['items'] ?? []));

    [$attack] = jsonCheck($client, $results, 'api.attackdex.show_570', '/api/dex/attack/show?id=570');
    $learnedBy = $attack['attack']['learnedBy'] ?? [];
    assertTrue(
        $results,
        'api.attackdex.show_570.contract',
        (int) ($attack['attack']['id'] ?? 0) === 570
            && (string) ($attack['attack']['type'] ?? '') === 'Flying'
            && hasItemId($learnedBy, 384),
        'learnedBy=' . count($learnedBy)
    );

    [$waterSpecial] = jsonCheck($client, $results, 'api.attackdex.filters_water_special', '/api/dex/attacks?type=Water&category=special&power_min=100&limit=100');
    assertTrue($results, 'api.attackdex.filters_water_special.shape', count($waterSpecial['items'] ?? []) > 0, 'items=' . count($waterSpecial['items'] ?? []));

    [$rayMoves] = jsonCheck($client, $results, 'api.attackdex.filter_pokemon_rayquaza', '/api/dex/attacks?pokemon=Rayquaza&limit=200');
    assertTrue($results, 'api.attackdex.filter_pokemon_rayquaza.items', hasItemId($rayMoves['items'] ?? [], 570), 'items=' . count($rayMoves['items'] ?? []));
} catch (Throwable $e) {
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function parseOptions(array $argv): array
{
    $result = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $result[$key] = $value;
        } else {
            $result[$arg] = true;
        }
    }
    return $result;
}

function jsonCheck(SmokeClient $client, array &$results, string $name, string $path): array
{
    $response = $client->request('GET', $path);
    $payload = json_decode($response['body'], true);
    $ok = $response['status'] === 200 && is_array($payload) && ($payload['ok'] ?? false) === true;
    assertTrue($results, $name, $ok, 'status=' . $response['status']);
    return [is_array($payload) ? $payload : [], $response];
}

function hasItemId(array $rows, int $id): bool
{
    foreach ($rows as $row) {
        if ((int) ($row['id'] ?? 0) === $id) {
            return true;
        }
    }
    return false;
}

function extractCsrf(string $html): string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/<meta\s+name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Dex/AttackDex smoke: %d/%d passed.\n", $passed, count($results));
}

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'dex_smoke_cookie_') ?: (sys_get_temp_dir() . '/dex_smoke_cookie.txt');
    }

    /** @param array<string,string|int|float|bool> $data */
    public function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json, text/html;q=0.9,*/*;q=0.8'],
        ]);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('HTTP request failed: ' . $error);
        }

        return ['status' => $status, 'body' => (string) $body];
    }
}
