<?php
declare(strict_types=1);

/** Syntax-only gate for maintained PHP; executes none of the scanned scripts. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$files = [];
foreach (['src', 'views', 'config', 'tools'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $entry) {
        if ($entry->isFile() && !$entry->isLink() && strtolower($entry->getExtension()) === 'php') { $files[] = $entry->getPathname(); }
    }
}
foreach (glob($root . '/*.php') ?: [] as $path) { $files[] = $path; }
$files[] = $root . '/public/index.php';
$files = array_values(array_unique($files));
sort($files);
$failed = [];
foreach ($files as $file) {
    $process = proc_open([PHP_BINARY, '-d', 'short_open_tag=1', '-l', $file], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (!is_resource($process)) { throw new RuntimeException('Unable to start PHP lint'); }
    $message = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
        $failed[] = $relative;
        fwrite(STDERR, $relative . ': ' . trim($message) . "\n");
    }
}
printf("PHP syntax: %d/%d passed (PHP %s). Legacy include/admin trees are outside this gate.\n", count($files) - count($failed), count($files), PHP_VERSION);
exit($failed ? 1 : 0);
