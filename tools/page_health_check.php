<?php
/**
 * Проверка здоровья страниц: обходит список маршрутов авторизованным запросом
 * и ищет в ответе признаки PHP-ошибок. Нужна потому, что часть страниц не покрыта
 * smoke-набором: сломанный шаблон отдаёт 200 с HTML-ошибкой внутри, и это легко
 * не заметить.
 *
 * Использование:
 *   php tools/page_health_check.php --login=Tacos --password=... [--base=http://pokemonchic.com]
 */

$options = getopt('', ['login::', 'password::', 'base::']);
$base = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login = (string) ($options['login'] ?? getenv('SMOKE_LOGIN') ?: '');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');

if ($login === '' || $password === '') {
    fwrite(STDERR, "Нужны --login и --password (или SMOKE_LOGIN/SMOKE_PASSWORD).\n");
    exit(2);
}

$jar = tempnam(sys_get_temp_dir(), 'pghealth');

function pageRequest(string $url, ?array $post = null, ?string $referer = null): array
{
    global $jar;
    $ch = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HEADER => true,
    ];
    if ($post !== null) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($post);
    }
    if ($referer !== null) {
        $options[CURLOPT_REFERER] = $referer;
    }
    curl_setopt_array($ch, $options);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    unset($ch);

    return ['status' => $status, 'body' => substr((string) $raw, $headerSize)];
}

$home = pageRequest($base . '/');
preg_match('/name=["\']_csrf["\'][^>]*value=["\']([^"\']+)["\']/i', $home['body'], $m);
$csrf = $m[1] ?? '';
pageRequest($base . '/login', ['_csrf' => $csrf, 'LOGIN' => $login, 'PASSWORD' => $password], $base . '/');

$pages = [
    '/game',
    '/game/admin',
    '/game/items',
    '/game/pokemon',
    '/game/quests',
    '/game/commission',
    '/game/messages',
    '/game/eggs',
    '/game/events',
    '/game/tournaments',
    '/game/transport',
    '/game/diamond-shop',
    '/game/profile',
    '/game/market/items',
    '/game/market/pokemon',
];

$markers = ['Fatal error', 'Parse error', 'Warning:', 'Notice:', 'Deprecated:', 'Uncaught'];
$bad = 0;

foreach ($pages as $page) {
    $response = pageRequest($base . $page);
    $found = [];
    foreach ($markers as $marker) {
        if (str_contains($response['body'], $marker)) {
            $found[] = $marker;
        }
    }
    $size = strlen($response['body']);
    if ($found !== []) {
        $bad++;
        printf("[FAIL] %-24s status=%d size=%d маркеры: %s\n", $page, $response['status'], $size, implode(', ', $found));
    } else {
        printf("[ OK ] %-24s status=%d size=%d\n", $page, $response['status'], $size);
    }
}

@unlink($jar);
printf("\nПроверено страниц: %d, с ошибками: %d\n", count($pages), $bad);
exit($bad === 0 ? 0 : 1);
