<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
use Pokemon8\Game\ChatService;
$cases = [
    ['#3123124123', '[[poke:3123124123]]'],
    ['Вот #123, и #456!', 'Вот [[poke:123]], и [[poke:456]]!'],
    ['[[poke:123]]', '[[poke:123]]'],
    ['abc#123 #123abc ##123', 'abc#123 #123abc ##123'],
    ['#123456789012345678901234567890', '#123456789012345678901234567890'],
    ['#0 #0123 #-12', '#0 #0123 #-12'],
    ['[[user:29]] #123', '[[user:29]] [[poke:123]]'],
    ['<script>#123</script>', '<script>[[poke:123]]</script>'],
];
foreach ($cases as $i => [$input, $expected]) {
    $actual = ChatService::normalizePokemonMentions($input);
    if ($actual !== $expected || ChatService::normalizePokemonMentions($actual) !== $expected) {
        fwrite(STDERR, 'FAIL case ' . ($i + 1) . PHP_EOL); exit(1);
    }
}
echo 'Chat mention normalization: ' . count($cases) . '/' . count($cases) . ' passed (including idempotency).' . PHP_EOL;
