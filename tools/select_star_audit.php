<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

$root = dirname(__DIR__);
$expected = 49;
$selfTest = in_array('--self-test', $argv, true);

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--expected=')) {
        $expected = (int) substr($arg, strlen('--expected='));
    }
}

$matches = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/src', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        continue;
    }

    foreach ($lines as $index => $line) {
        if (preg_match('/\bSELECT\s+\*/i', $line) === 1) {
            $matches[] = [
                'file' => $relative,
                'line' => $index + 1,
                'sql' => trim($line),
            ];
        }
    }
}

foreach ($matches as $match) {
    echo $match['file'] . ':' . $match['line'] . ': ' . $match['sql'] . PHP_EOL;
}

$count = count($matches);
echo 'Total SELECT * in src: ' . $count . PHP_EOL;

if ($selfTest) {
    if ($count !== $expected) {
        fwrite(STDERR, 'Self-test failed: expected ' . $expected . ', got ' . $count . PHP_EOL);
        exit(1);
    }
    echo 'Self-test OK: expected ' . $expected . PHP_EOL;
}
