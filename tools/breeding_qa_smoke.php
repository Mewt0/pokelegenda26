<?php
declare(strict_types=1);

/**
 * Two-session breeding QA smoke for Tacos/NIGA.
 *
 * Usage:
 *   php tools/breeding_qa_smoke.php --password=jungheinrick
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}
if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Pokemon8\Support\Env::load(APP_ROOT . '/.env');
$options = parseOptions($argv);
$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
if ($password === '') {
    fwrite(STDERR, "Missing --password.\n");
    exit(2);
}

$db = Pokemon8\Database\Connection::make(require APP_ROOT . '/config/database.php');
$results = [];

try {
    ob_start();
    require APP_ROOT . '/tools/prepare_breeding_qa.php';
    ob_end_clean();

    $tacos = loginClient($baseUrl, 'Tacos', $password, $results, 'tacos');
    $niga = loginClient($baseUrl, 'NIGA', $password, $results, 'niga');
    $tacosId = findUserId($db, 'Tacos');
    $nigaId = findUserId($db, 'NIGA');
    $db->prepare('UPDATE users SET battleid = 0, pve = 0, pvp = 0, trade = 0 WHERE id IN (:a, :b)')
        ->execute(['a' => $tacosId, 'b' => $nigaId]);
    $db->prepare('UPDATE pokemon_breeding_requests SET status = "expired", result_message = "QA cleanup" WHERE status = "pending" AND requester_id IN (:a, :b)')
        ->execute(['a' => $tacosId, 'b' => $nigaId]);

    $ids = [
        'tacos_bulba_m' => pokemonByName($db, $tacosId, 'QA Breed Male%'),
        'tacos_pidgey_m' => pokemonByName($db, $tacosId, 'QA Breed Incompatible Male%'),
        'tacos_magnemite' => pokemonByName($db, $tacosId, 'QA Breed Genderless%'),
        'tacos_legendary' => pokemonByName($db, $tacosId, 'QA Breed Legendary Blocked%'),
        'niga_bulba_f' => pokemonByName($db, $nigaId, 'QA Breed Female%'),
        'niga_ditto' => pokemonByName($db, $nigaId, 'QA Breed Ditto%'),
        'niga_magnemite' => pokemonByName($db, $nigaId, 'QA Breed Genderless Partner%'),
    ];
    foreach ($ids as $label => $id) {
        assertTrue($results, 'prep.' . $label, $id > 0, 'id=' . $id);
    }

    $state = jsonCheck($tacos, $results, 'api.state.tacos', 'GET', '/api/pokemon/breeding');
    assertTrue($results, 'api.state.tacos.candidates', count($state['candidates'] ?? []) >= 4, 'candidates=' . count($state['candidates'] ?? []));

    $compatible = createAndAccept($tacos, $niga, $db, $results, $ids['tacos_bulba_m'], $ids['niga_bulba_f']);
    assertTrue($results, 'compatible.egg.owner', eggOwner($db, $compatible) === $nigaId, 'egg=' . $compatible . ' owner=' . eggOwner($db, $compatible));
    assertTrue($results, 'compatible.egg.parents', eggHasParents($db, $compatible, $ids['tacos_bulba_m'], $ids['niga_bulba_f']), 'egg=' . $compatible);

    $requestId = createRequest($tacos, $results, $ids['tacos_pidgey_m']);
    $bad = jsonCheck($niga, $results, 'incompatible.accept', 'POST', '/api/pokemon/breeding/respond', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
        'action' => 'accept',
        'pokemon_id' => (string) $ids['niga_bulba_f'],
    ]);
    assertTrue($results, 'incompatible.blocked', ($bad['ok'] ?? true) === false, (string) ($bad['message'] ?? ''));
    decline($niga, $results, $requestId);

    $requestId = createRequest($tacos, $results, $ids['tacos_magnemite']);
    $bad = jsonCheck($niga, $results, 'genderless.normal.accept', 'POST', '/api/pokemon/breeding/respond', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
        'action' => 'accept',
        'pokemon_id' => (string) $ids['niga_bulba_f'],
    ]);
    assertTrue($results, 'genderless.normal.blocked', ($bad['ok'] ?? true) === false, (string) ($bad['message'] ?? ''));
    decline($niga, $results, $requestId);

    $dittoEgg = createAndAccept($tacos, $niga, $db, $results, $ids['tacos_magnemite'], $ids['niga_ditto']);
    assertTrue($results, 'genderless.ditto.owner', eggOwner($db, $dittoEgg) === $tacosId, 'egg=' . $dittoEgg . ' owner=' . eggOwner($db, $dittoEgg));

    $extractEgg = createAndAccept($tacos, $niga, $db, $results, $ids['tacos_magnemite'], $ids['niga_magnemite']);
    assertTrue($results, 'extract.egg.owner', eggOwner($db, $extractEgg) === $tacosId, 'egg=' . $extractEgg . ' owner=' . eggOwner($db, $extractEgg));
    assertTrue($results, 'extract.egg.ivs', eggIvWithinMutationWindow($db, $extractEgg, 20), 'egg=' . $extractEgg);

    $before = itemCount($db, $tacosId, Pokemon8\Repository\BreedingRepository::DITTO_EXTRACT_ITEM_ID);
    $essence = jsonCheck($tacos, $results, 'extract.direct.blocked', 'POST', '/api/pokemon/breeding/ditto-essence', [
        '_csrf' => $tacos->csrf,
        'pokemon_id' => (string) $ids['tacos_magnemite'],
    ]);
    assertTrue($results, 'extract.direct.not_used', ($essence['ok'] ?? true) === false, (string) ($essence['message'] ?? ''));
    $after = itemCount($db, $tacosId, Pokemon8\Repository\BreedingRepository::DITTO_ESSENCE_ITEM_ID);
    assertTrue($results, 'extract.direct.not_consumed', $after === $before, 'before=' . $before . ' after=' . $after);

    $beforeFail = itemCount($db, $tacosId, Pokemon8\Repository\BreedingRepository::DITTO_ESSENCE_ITEM_ID);
    $fail = jsonCheck($tacos, $results, 'extract.legendary.fail', 'POST', '/api/pokemon/breeding/ditto-essence', [
        '_csrf' => $tacos->csrf,
        'pokemon_id' => (string) $ids['tacos_legendary'],
    ]);
    assertTrue($results, 'extract.legendary.blocked', ($fail['ok'] ?? true) === false, (string) ($fail['message'] ?? ''));
    assertTrue($results, 'extract.fail.not_consumed', itemCount($db, $tacosId, Pokemon8\Repository\BreedingRepository::DITTO_ESSENCE_ITEM_ID) === $beforeFail, 'count=' . itemCount($db, $tacosId, Pokemon8\Repository\BreedingRepository::DITTO_ESSENCE_ITEM_ID));

    $eggs = jsonCheck($tacos, $results, 'eggs.tacos', 'GET', '/api/eggs');
    assertTrue($results, 'eggs.tacos.visible', count($eggs['eggs'] ?? []) > 0, 'eggs=' . count($eggs['eggs'] ?? []));

    $incubatorBefore = itemCount($db, $tacosId, Pokemon8\Repository\EggRepository::INCUBATOR_ITEM_ID);
    $readyBefore = eggReadyAt($db, $extractEgg);
    $incubate = jsonCheck($tacos, $results, 'eggs.incubate', 'POST', '/api/eggs/incubate', [
        '_csrf' => $tacos->csrf,
        'egg_id' => (string) $extractEgg,
    ]);
    $readyAfter = eggReadyAt($db, $extractEgg);
    $incubatorAfter = itemCount($db, $tacosId, Pokemon8\Repository\EggRepository::INCUBATOR_ITEM_ID);
    assertTrue($results, 'eggs.incubate.ok', ($incubate['ok'] ?? false) === true, (string) ($incubate['message'] ?? ''));
    assertTrue($results, 'eggs.incubate.halved', $readyAfter > time() && $readyAfter < $readyBefore, 'before=' . $readyBefore . ' after=' . $readyAfter);
    assertTrue($results, 'eggs.incubator.consumed', $incubatorAfter === $incubatorBefore - 1, 'before=' . $incubatorBefore . ' after=' . $incubatorAfter);
} catch (Throwable $e) {
    $results[] = ['label' => 'fatal', 'ok' => false, 'detail' => $e->getMessage()];
}

printResults($results);

function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--') && str_contains($arg, '=')) {
            [$key, $value] = explode('=', substr($arg, 2), 2);
            $options[$key] = $value;
        }
    }
    return $options;
}

function loginClient(string $baseUrl, string $login, string $password, array &$results, string $label): QaClient
{
    $client = new QaClient($baseUrl);
    $home = $client->request('GET', '/');
    $csrf = extractCsrf($home['body']);
    assertTrue($results, $label . '.csrf', $csrf !== '', 'home csrf');
    $client->request('POST', '/login', ['_csrf' => $csrf, 'LOGIN' => $login, 'PASSWORD' => $password]);
    $game = $client->request('GET', '/game');
    $client->csrf = extractCsrf($game['body']);
    assertTrue($results, $label . '.login', $game['status'] === 200 && $client->csrf !== '', 'game csrf');
    return $client;
}

function createAndAccept(QaClient $tacos, QaClient $niga, PDO $db, array &$results, int $first, int $second): int
{
    $requestId = createRequest($tacos, $results, $first);
    $accept = jsonCheck($niga, $results, 'pair.accept.' . $requestId, 'POST', '/api/pokemon/breeding/respond', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
        'action' => 'accept',
        'pokemon_id' => (string) $second,
    ]);
    $eggId = (int) ($accept['egg_id'] ?? 0);
    assertTrue($results, 'pair.accept.ok.' . $requestId, ($accept['ok'] ?? false) === true && $eggId > 0, (string) ($accept['message'] ?? ''));
    assertTrue($results, 'pair.accept.db.' . $requestId, requestStatus($db, $requestId) === 'accepted', 'status=' . requestStatus($db, $requestId));
    return $eggId;
}

function createRequest(QaClient $client, array &$results, int $pokemonId): int
{
    $payload = jsonCheck($client, $results, 'request.create.' . $pokemonId, 'POST', '/api/pokemon/breeding/request', [
        '_csrf' => $client->csrf,
        'target' => 'NIGA',
        'pokemon_id' => (string) $pokemonId,
    ]);
    $requestId = (int) ($payload['request_id'] ?? 0);
    assertTrue($results, 'request.create.ok.' . $pokemonId, ($payload['ok'] ?? false) === true && $requestId > 0, (string) ($payload['message'] ?? ''));
    return $requestId;
}

function decline(QaClient $client, array &$results, int $requestId): void
{
    jsonCheck($client, $results, 'request.decline.' . $requestId, 'POST', '/api/pokemon/breeding/respond', [
        '_csrf' => $client->csrf,
        'request_id' => (string) $requestId,
        'action' => 'decline',
        'pokemon_id' => '0',
    ]);
}

function jsonCheck(QaClient $client, array &$results, string $label, string $method, string $path, array $data = []): array
{
    $response = $client->request($method, $path, $data);
    $json = json_decode($response['body'], true);
    $ok = $response['status'] >= 200 && $response['status'] < 300 && is_array($json);
    assertTrue($results, $label, $ok, 'HTTP ' . $response['status']);
    return is_array($json) ? $json : [];
}

function pokemonByName(PDO $db, int $userId, string $like): int
{
    $stmt = $db->prepare('SELECT id FROM pok_user WHERE users = :user AND names LIKE :name ORDER BY id ASC LIMIT 1');
    $stmt->execute(['user' => $userId, 'name' => $like]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function findUserId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function itemCount(PDO $db, int $userId, int $itemId): int
{
    $stmt = $db->prepare('SELECT COALESCE(SUM(count), 0) FROM items_users WHERE user_id = :user AND item_id = :item');
    $stmt->execute(['user' => $userId, 'item' => $itemId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function requestStatus(PDO $db, int $requestId): string
{
    $stmt = $db->prepare('SELECT status FROM pokemon_breeding_requests WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $requestId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function eggOwner(PDO $db, int $eggId): int
{
    $stmt = $db->prepare('SELECT users_egg FROM eggs WHERE id_egg = :egg LIMIT 1');
    $stmt->execute(['egg' => $eggId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function eggReadyAt(PDO $db, int $eggId): int
{
    $stmt = $db->prepare('SELECT dtime FROM eggs WHERE id_egg = :egg LIMIT 1');
    $stmt->execute(['egg' => $eggId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function eggHasParents(PDO $db, int $eggId, int $one, int $two): bool
{
    $stmt = $db->prepare('SELECT parent_one_id, parent_two_id FROM eggs WHERE id_egg = :egg LIMIT 1');
    $stmt->execute(['egg' => $eggId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    return (int) ($row['parent_one_id'] ?? 0) === $one && (int) ($row['parent_two_id'] ?? 0) === $two;
}

function eggIvWithinMutationWindow(PDO $db, int $eggId, int $parentIv): bool
{
    $stmt = $db->prepare('SELECT hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv FROM eggs WHERE id_egg = :egg LIMIT 1');
    $stmt->execute(['egg' => $eggId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    foreach (['hp_iv', 'atk_iv', 'def_iv', 'satk_iv', 'sdef_iv', 'speed_iv'] as $field) {
        $value = (int) ($row[$field] ?? -999);
        if ($value < max(0, $parentIv - 2) || $value > min(31, $parentIv + 2)) {
            return false;
        }
    }
    return true;
}

function extractCsrf(string $html): string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (preg_match('/data-csrf="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return '';
}

function assertTrue(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
}

function printResults(array $results): void
{
    $failed = 0;
    foreach ($results as $result) {
        $ok = (bool) ($result['ok'] ?? false);
        if (!$ok) {
            $failed++;
        }
        echo ($ok ? '[OK] ' : '[FAIL] ') . $result['label'] . (($result['detail'] ?? '') !== '' ? ' - ' . $result['detail'] : '') . PHP_EOL;
    }
    echo PHP_EOL . 'Breeding QA smoke: ' . (count($results) - $failed) . '/' . count($results) . ' passed.' . PHP_EOL;
    if ($failed > 0) {
        exit(1);
    }
}

final class QaClient
{
    public string $csrf = '';
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'breed_qa_cookie_') ?: '';
    }

    public function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 20,
        ]);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $body = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => $body];
    }
}
