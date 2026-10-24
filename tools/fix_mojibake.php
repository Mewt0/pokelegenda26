<?php
declare(strict_types=1);

/**
 * Починка битых текстов (двойная кодировка) построчно.
 *
 * Двойная кодировка лечится обратным преобразованием UTF-8 -> Windows-1251. Делать
 * это со всем файлом нельзя: корректные строки сломаются. Поэтому трогаем только те
 * строки, где найден однозначный признак (Р/С + символ, которого там быть не может),
 * и только если после преобразования строка становится «чище».
 *
 * Usage:
 *   php tools/fix_mojibake.php <файл>            # показать, что будет
 *   php tools/fix_mojibake.php <файл> --apply    # применить
 */
require __DIR__ . '/../src/Support/Autoload.php';

$root = dirname(__DIR__);
$pattern = '/[РС][^\x{0400}-\x{04FF}\sA-Za-z0-9]/u';

$path = $argv[1] ?? '';
$apply = in_array('--apply', $argv, true);
if ($path === '' || !is_file($path)) {
    fwrite(STDERR, "Укажите существующий файл.\n");
    exit(2);
}

$raw = (string) file_get_contents($path);
$lines = explode("\n", $raw);
$fixed = 0;

foreach ($lines as $index => $line) {
    if (!preg_match($pattern, $line)) {
        continue;
    }
    $decoded = @iconv('UTF-8', 'Windows-1251//IGNORE', $line);
    if ($decoded === false) {
        continue;
    }
    // Проверяем, что стало лучше: признак ушёл, а кириллицы не убавилось.
    $before = preg_match_all('/[\x{0400}-\x{04FF}]/u', $line);
    $after = preg_match_all('/[\x{0400}-\x{04FF}]/u', $decoded);
    if (preg_match($pattern, $decoded) || $after < $before) {
        echo '  ПРОПУСК (не стало лучше): ' . trim(mb_substr($line, 0, 90)) . PHP_EOL;
        continue;
    }
    echo '  ' . ($index + 1) . ': ' . trim(mb_substr($line, 0, 60)) . PHP_EOL
        . '   -> ' . trim(mb_substr($decoded, 0, 60)) . PHP_EOL;
    $lines[$index] = $decoded;
    $fixed++;
}

echo "исправлено строк: $fixed" . ($apply ? '' : ' (предпросмотр, добавь --apply)') . PHP_EOL;
if ($apply && $fixed > 0) {
    file_put_contents($path, implode("\n", $lines));
}
