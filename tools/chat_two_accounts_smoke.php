<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}
if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

$options = parseOptions($argv);
$baseUrl = rtrim((string) ($options['base'] ?? 'http://pokemonchic.com'), '/');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
if ($password === '') {
    fwrite(STDERR, "Set SMOKE_PASSWORD or provide --password.\n");
    exit(2);
}
$delay = 3;
$results = [];

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/src/Support/Autoload.php';
require_once dirname(__DIR__) . '/src/Database/Connection.php';
$dbConfig = require dirname(__DIR__) . '/config/database.php';
$db = Pokemon8\Database\Connection::make($dbConfig);

$tacos = new SmokeClient($baseUrl);
$niga = new SmokeClient($baseUrl);
$originalNigaLocation = null;

try {
    $tacosCsrf = login($tacos, 'Tacos', $password);
    $nigaCsrf = login($niga, 'NIGA', $password);
    ok($results, 'auth.tacos', $tacosCsrf !== '', 'game CSRF loaded');
    ok($results, 'auth.niga', $nigaCsrf !== '', 'game CSRF loaded');

    $users = usersByLogin($db, ['Tacos', 'NIGA']);
    $tacosId = (int) ($users['Tacos']['id'] ?? 0);
    $nigaId = (int) ($users['NIGA']['id'] ?? 0);
    ok($results, 'users.lookup', $tacosId > 0 && $nigaId > 0, 'Tacos=' . $tacosId . ', NIGA=' . $nigaId);
    $originalNigaLocation = (int) ($users['NIGA']['buildmy'] ?? 0);
    $testRoom = (int) ($users['Tacos']['buildmy'] ?? 0);
    if ($tacosId <= 0 || $nigaId <= 0 || $testRoom <= 0 || $originalNigaLocation <= 0) {
        throw new RuntimeException('Missing test users or valid locations.');
    }
    $db->prepare('UPDATE users SET buildmy = :room WHERE id = :id AND login = "NIGA" LIMIT 1')
        ->execute(['room' => $testRoom, 'id' => $nigaId]);

    $stamp = (string) time();
    $generalA = "TASK045 general from Tacos $stamp";
    sendChat($tacos, $tacosCsrf, $generalA, 1);
    waitFlood($delay);
    ok($results, 'general.tacos.visible_to_niga', hasMessage($niga, $generalA), $generalA);

    $generalB = "TASK045 reply from NIGA $stamp";
    sendChat($niga, $nigaCsrf, $generalB, 1);
    waitFlood($delay);
    ok($results, 'general.niga.visible_to_tacos', hasMessage($tacos, $generalB), $generalB);

    $private = "TASK045 private NIGA only $stamp";
    sendChat($tacos, $tacosCsrf, $private, 1, ['private' => '1', 'userto' => (string) $nigaId]);
    waitFlood($delay);
    $nigaMessages = messages($niga);
    $tacosMessages = messages($tacos);
    ok($results, 'private.visible_to_recipient', findMessage($nigaMessages, $private, true) !== null, 'NIGA receives private');
    ok($results, 'private.not_public', findMessage($tacosMessages, $private, false) === null, 'not in public filter');

    $mention = "TASK045 hello @NIGA mention $stamp";
    sendChat($tacos, $tacosCsrf, $mention, 1);
    waitFlood($delay);
    $mentionMsg = findMessage(messages($niga), $mention, false);
    ok($results, 'mention.text_preserved', $mentionMsg !== null && str_contains((string) $mentionMsg['text'], '@NIGA'), (string) ($mentionMsg['text'] ?? 'missing'));

    $trade = "TASK045 trade selling pokemon $stamp";
    sendChat($tacos, $tacosCsrf, $trade, 4);
    waitFlood($delay);
    $tradeMsg = findMessage(messages($niga), $trade, false);
    ok($results, 'channel.trade.tipe', $tradeMsg !== null && (int) ($tradeMsg['tipe'] ?? 0) === 4, 'tipe=' . (int) ($tradeMsg['tipe'] ?? 0));

    $battle = "CHAT-QA battle channel $stamp";
    sendChat($tacos, $tacosCsrf, $battle, 3);
    waitFlood($delay);
    $battleMsg = findMessage(messages($niga), $battle, false);
    ok($results, 'channel.battle.tipe', $battleMsg !== null && (int) ($battleMsg['tipe'] ?? 0) === 3, 'tipe=' . (int) ($battleMsg['tipe'] ?? 0));

    $pokemon = chatTeam($tacos);
    ok($results, 'pokemon.team_picker_api', count($pokemon) > 0, 'team=' . count($pokemon));
    if (count($pokemon) > 0) {
        $pokeId = (int) $pokemon[0]['id'];
        $pokeText = "TASK045 pokemon card [[poke:$pokeId]] $stamp";
        sendChat($tacos, $tacosCsrf, $pokeText, 1);
        waitFlood($delay);
        $pokeMsg = findMessage(messages($tacos), $pokeText, false);
        $card = is_array($pokeMsg['pokemon_card'] ?? null) ? $pokeMsg['pokemon_card'] : [];
        ok($results, 'pokemon.card.present', ($card['available'] ?? false) === true, 'pokemon_id=' . $pokeId);
        ok($results, 'pokemon.card.full_shape', isset($card['name'], $card['level'], $card['gender'], $card['nature'], $card['ability'], $card['iv'], $card['ev'], $card['stats'], $card['moves'], $card['shiny']), 'keys=' . implode(',', array_keys($card)));
        $peerMessage = findMessage(messages($niga), $pokeText, false);
        ok($results, 'pokemon.card.visible_to_peer', ($peerMessage['pokemon_card']['available'] ?? false) === true, 'recipient sees the author pokemon, not own team');

        $rawText = "CHAT-QA raw pokemon #$pokeId $stamp";
        if (isset($pokemon[1]['id'])) $rawText .= ' #' . (int) $pokemon[1]['id'];
        sendChat($tacos, $tacosCsrf, $rawText, 1);
        waitFlood($delay);
        $canonical = Pokemon8\Game\ChatService::normalizePokemonMentions($rawText);
        $rawMsg = findMessage(messages($niga), $canonical, false);
        ok($results, 'pokemon.hash_id.normalized', $rawMsg !== null && str_contains((string) $rawMsg['text'], '[[poke:' . $pokeId . ']]'));
        $cards = $rawMsg['pokemon_cards'] ?? [];
        ok($results, 'pokemon.hash_id.cards', count($cards) === (isset($pokemon[1]['id']) ? 2 : 1) && count(array_filter($cards, static fn (array $card): bool => ($card['available'] ?? false) === true)) === count($cards));

        $foreignTeam = chatTeam($niga);
        if ($foreignTeam !== []) {
            $foreignId = (int) $foreignTeam[0]['id'];
            $denied = $tacos->request('POST', '/api/chat/messages', ['_csrf' => $tacosCsrf, 'text' => 'CHAT-QA unauthorized #' . $foreignId, 'tipe' => '1']);
            $deniedBody = json_decode($denied['body'], true);
            ok($results, 'pokemon.foreign_hash.rejected', $denied['status'] === 422 && ($deniedBody['ok'] ?? true) === false);
        }
        $storedQuery = $db->prepare('SELECT id FROM pok_user WHERE users = :user AND active = 0 ORDER BY id LIMIT 1');
        $storedQuery->execute(['user' => $tacosId]);
        $storedId = (int) $storedQuery->fetchColumn();
        if ($storedId > 0) {
            $storedText = "CHAT-QA stored pokemon #$storedId $stamp";
            sendChat($tacos, $tacosCsrf, $storedText, 1);
            waitFlood($delay);
            $storedMsg = findMessage(messages($niga), Pokemon8\Game\ChatService::normalizePokemonMentions($storedText), false);
            ok($results, 'pokemon.stored_card.visible_to_peer', ($storedMsg['pokemon_card']['available'] ?? false) === true && (int) ($storedMsg['pokemon_card']['id'] ?? 0) === $storedId);
        }
    }

    $clanResult = clanCheck($db, $tacos, $niga, $tacosCsrf, $stamp, $delay, $results);
    ok($results, 'channel.clan.implemented', $clanResult, $clanResult ? 'tipe=5 visible to clan peer' : 'no same-clan setup available or no server-side clan isolation');
} catch (Throwable $e) {
    ok($results, 'exception', false, $e::class . ': ' . $e->getMessage());
} finally {
    if ($originalNigaLocation !== null && $originalNigaLocation > 0) {
        $db->prepare('UPDATE users SET buildmy = :room WHERE login = "NIGA" LIMIT 1')
            ->execute(['room' => $originalNigaLocation]);
        $restored = usersByLogin($db, ['Tacos', 'NIGA']);
        ok($results, 'fixture.location.restored', (int) ($restored['NIGA']['buildmy'] ?? 0) === $originalNigaLocation);
    }
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--')) {
            $arg = substr($arg, 2);
            [$key, $value] = array_pad(explode('=', $arg, 2), 2, '1');
            $options[$key] = $value;
        }
    }
    return $options;
}

function login(SmokeClient $client, string $login, string $password): string
{
    $home = $client->request('GET', '/');
    $csrf = extractCsrf($home['body']);
    $client->request('POST', '/login', ['_csrf' => $csrf, 'LOGIN' => $login, 'PASSWORD' => $password]);
    $game = $client->request('GET', '/game');
    return extractCsrf($game['body']);
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

function sendChat(SmokeClient $client, string $csrf, string $text, int $tipe, array $extra = []): array
{
    $payload = ['_csrf' => $csrf, 'text' => $text, 'tipe' => (string) $tipe] + $extra;
    $response = $client->request('POST', '/api/chat/messages', $payload);
    $json = json_decode($response['body'], true);
    if (!is_array($json) || ($json['ok'] ?? false) !== true) {
        throw new RuntimeException('Chat send failed: ' . $response['body']);
    }
    return $json;
}

function messages(SmokeClient $client): array
{
    $response = $client->request('GET', '/api/chat/messages?after_id=0');
    $json = json_decode($response['body'], true);
    return is_array($json['messages'] ?? null) ? $json['messages'] : [];
}

function hasMessage(SmokeClient $client, string $text): bool
{
    return findMessage(messages($client), $text, null) !== null;
}

function findMessage(array $messages, string $text, ?bool $private): ?array
{
    foreach ($messages as $message) {
        if (!str_contains((string) ($message['text'] ?? ''), $text)) {
            continue;
        }
        if ($private !== null && (bool) ($message['private'] ?? false) !== $private) {
            continue;
        }
        return $message;
    }
    return null;
}

function chatTeam(SmokeClient $client): array
{
    $response = $client->request('GET', '/api/chat/team-pokemon');
    $json = json_decode($response['body'], true);
    return is_array($json['pokemon'] ?? null) ? $json['pokemon'] : [];
}

function usersByLogin(PDO $db, array $logins): array
{
    $stmt = $db->prepare('SELECT id, login, buildmy, clanid, status_klan FROM users WHERE login IN (?, ?)');
    $stmt->execute($logins);
    $rows = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rows[(string) $row['login']] = $row;
    }
    return $rows;
}

function clanCheck(PDO $db, SmokeClient $tacos, SmokeClient $niga, string $csrf, string $stamp, int $delay, array &$results): bool
{
    $users = usersByLogin($db, ['Tacos', 'NIGA']);
    $tacosClan = (int) ($users['Tacos']['clanid'] ?? 0);
    $nigaClan = (int) ($users['NIGA']['clanid'] ?? 0);
    if ($tacosClan <= 0 && $nigaClan <= 0) {
        ok($results, 'channel.clan.setup', false, 'neither test account is in a clan; no public clan join API found');
        return false;
    }

    $targetClan = $tacosClan > 0 ? $tacosClan : $nigaClan;
    $original = $users['NIGA'] ?? [];
    $changed = false;
    if ($nigaClan !== $targetClan) {
        $db->prepare('UPDATE users SET clanid = :clan, status_klan = 1 WHERE login = "NIGA" LIMIT 1')->execute(['clan' => $targetClan]);
        $changed = true;
    }

    try {
        $text = "TASK045 clan hello $stamp";
        sendChat($tacos, $csrf, $text, 5);
        waitFlood($delay);
        $msg = findMessage(messages($niga), $text, false);
        $visibleToClan = $msg !== null && (int) ($msg['tipe'] ?? 0) === 5;
        $db->prepare('UPDATE users SET clanid = 0, status_klan = 0 WHERE login = "NIGA" LIMIT 1')->execute();
        $hiddenFromOutsider = findMessage(messages($niga), $text, false) === null;
        ok($results, 'channel.clan.visible_to_member', $visibleToClan, 'same clan sees tipe=5');
        ok($results, 'channel.clan.hidden_from_outsider', $hiddenFromOutsider, 'different clan does not receive tipe=5');
        return $visibleToClan && $hiddenFromOutsider;
    } finally {
        if ($original !== []) {
            $db->prepare('UPDATE users SET clanid = :clan, status_klan = :status WHERE login = "NIGA" LIMIT 1')
                ->execute(['clan' => (int) ($original['clanid'] ?? 0), 'status' => (string) ($original['status_klan'] ?? '')]);
        }
    }
}

function waitFlood(int $seconds): void
{
    sleep($seconds);
}

function ok(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['status' => $ok ? 'OK' : 'FAIL', 'label' => $label, 'detail' => $detail];
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
    printf("TOTAL: %d checks, %d failures\n", count($results), $failures);
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

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'pokemon8_chat_cookie_') ?: '';
    }

    public function __destruct()
    {
        if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    public function request(string $method, string $path, array $data = [], bool $follow = true): array
    {
        $url = $this->baseUrl . (str_starts_with($path, '/') ? $path : '/' . $path);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to init curl.');
        }
        $headers = ['User-Agent: Pokemon8ChatSmoke/1.0'];
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
        if (strtoupper($method) !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/x-www-form-urlencoded;charset=UTF-8']));
        }
        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            unset($ch);
            throw new RuntimeException($error);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        unset($ch);
        return ['status' => $status, 'body' => substr((string) $raw, $headerSize)];
    }
}
