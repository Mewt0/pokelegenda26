<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

const NEW_LAYER_DIRS = ['src', 'public', 'views', 'app', 'config', 'tools'];
const LEGACY_DIRS = ['include', 'admin', 'css', 'script'];
const SKIP_DIR_PARTS = [
    '.git',
    '.codex_',
    'downloaded_',
    'new_diz',
    'pok',
    'tmp',
    'storage',
];

$options = parseOptions($argv);
$root = normalizePath((string) ($options['root'] ?? dirname(__DIR__)));
$top = max(1, (int) ($options['top'] ?? 20));
$json = (bool) ($options['json'] ?? false);
$out = isset($options['out']) ? (string) $options['out'] : null;

if (!is_dir($root)) {
    fwrite(STDERR, "Root does not exist: {$root}\n");
    exit(2);
}

$report = buildReport($root, $top);
$content = $json ? json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL : renderMarkdown($report);

if ($out !== null && $out !== '') {
    $outPath = makeAbsolutePath($out, $root);
    $dir = dirname($outPath);
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        fwrite(STDERR, "Cannot create output directory: {$dir}\n");
        exit(2);
    }
    file_put_contents($outPath, $content);
    echo "Report written: " . relativePath($outPath, $root) . PHP_EOL;
} else {
    echo $content;
}

/**
 * @param array<int,string> $argv
 * @return array<string,mixed>
 */
function parseOptions(array $argv): array
{
    $options = ['json' => false];
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--json') {
            $options['json'] = true;
            continue;
        }
        if (str_starts_with($arg, '--top=')) {
            $options['top'] = substr($arg, 6);
            continue;
        }
        if (str_starts_with($arg, '--root=')) {
            $options['root'] = substr($arg, 7);
            continue;
        }
        if (str_starts_with($arg, '--out=')) {
            $options['out'] = substr($arg, 6);
            continue;
        }
        fwrite(STDERR, "Unknown option: {$arg}\n");
        exit(2);
    }

    return $options;
}

/**
 * @return array<string,mixed>
 */
function buildReport(string $root, int $top): array
{
    $lint = runLint($root);
    $log = analyzeErrorLog($root, $top);

    return [
        'generated_at' => date('c'),
        'root' => $root,
        'php_binary' => PHP_BINARY,
        'lint' => $lint,
        'log' => $log,
        'candidates' => buildCandidates($lint, $log),
    ];
}

/**
 * @return array<string,mixed>
 */
function runLint(string $root): array
{
    $newFiles = collectPhpFiles($root, NEW_LAYER_DIRS, false);
    $legacyFiles = array_merge(
        collectPhpFiles($root, LEGACY_DIRS, false),
        collectRootPhpFiles($root)
    );
    $legacyFiles = array_values(array_unique($legacyFiles));
    sort($newFiles, SORT_STRING);
    sort($legacyFiles, SORT_STRING);

    return [
        'new_layer' => lintFiles($root, $newFiles),
        'legacy' => lintFiles($root, $legacyFiles),
    ];
}

/**
 * @param array<int,string> $dirs
 * @return array<int,string>
 */
function collectPhpFiles(string $root, array $dirs, bool $rootOnly): array
{
    $files = [];
    foreach ($dirs as $dir) {
        $base = $root . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($base)) {
            continue;
        }
        if ($rootOnly) {
            foreach (glob($base . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
                if (!shouldSkipPath($file, $root)) {
                    $files[] = normalizePath($file);
                }
            }
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
                continue;
            }
            $path = normalizePath($fileInfo->getPathname());
            if (!shouldSkipPath($path, $root)) {
                $files[] = $path;
            }
        }
    }

    return $files;
}

/**
 * @return array<int,string>
 */
function collectRootPhpFiles(string $root): array
{
    $files = [];
    foreach (glob($root . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
        if (!shouldSkipPath($file, $root)) {
            $files[] = normalizePath($file);
        }
    }

    return $files;
}

function shouldSkipPath(string $path, string $root): bool
{
    $relative = str_replace('\\', '/', relativePath($path, $root));
    foreach (SKIP_DIR_PARTS as $part) {
        if (str_contains($relative, $part)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<int,string> $files
 * @return array<string,mixed>
 */
function lintFiles(string $root, array $files): array
{
    $failed = [];
    foreach ($files as $file) {
        $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
        $output = [];
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            $failed[] = [
                'file' => relativePath($file, $root),
                'error' => trim(implode("\n", $output)),
            ];
        }
    }

    return [
        'checked' => count($files),
        'failed_count' => count($failed),
        'failed' => $failed,
    ];
}

/**
 * @return array<string,mixed>
 */
function analyzeErrorLog(string $root, int $top): array
{
    $logPath = $root . DIRECTORY_SEPARATOR . 'log_php_errors.txt';
    if (!is_file($logPath)) {
        return [
            'path' => 'log_php_errors.txt',
            'exists' => false,
            'entries' => 0,
            'unparsed' => 0,
            'groups' => [],
            'focus' => [],
            'top_files' => [],
            'message_types' => [],
            'layer_counts' => ['new_layer' => 0, 'legacy' => 0, 'unknown' => 0],
        ];
    }

    $groups = [];
    $files = [];
    $messageTypes = [];
    $focus = [
        'Fatal error' => [],
        'Uncaught' => [],
        'Deprecated' => [],
        'Warning: Undefined' => [],
        'Trying to access array offset' => [],
    ];
    $layerCounts = ['new_layer' => 0, 'legacy' => 0, 'unknown' => 0];
    $entries = 0;
    $unparsed = 0;

    $handle = fopen($logPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Cannot read {$logPath}");
    }

    while (($line = fgets($handle)) !== false) {
        $parsed = parseLogLine($line, $root);
        if ($parsed === null) {
            if (trim($line) !== '') {
                $unparsed++;
            }
            continue;
        }
        $entries++;
        $key = $parsed['type'] . "\0" . $parsed['normalized_message'] . "\0" . $parsed['file'];
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'type' => $parsed['type'],
                'message' => $parsed['normalized_message'],
                'file' => $parsed['file'],
                'count' => 0,
                'first_at' => $parsed['time'],
                'last_at' => $parsed['time'],
            ];
        }
        $groups[$key]['count']++;
        $groups[$key]['last_at'] = $parsed['time'];

        $files[$parsed['file']] = ($files[$parsed['file']] ?? 0) + 1;

        $messageKey = $parsed['type'] . ': ' . $parsed['normalized_message'];
        $messageTypes[$messageKey] = ($messageTypes[$messageKey] ?? 0) + 1;

        $layer = classifyLayer($parsed['file']);
        $layerCounts[$layer]++;

        foreach (array_keys($focus) as $focusKey) {
            $focusHaystack = $parsed['type'] . ': ' . $parsed['message'];
            if (str_contains($focusHaystack, $focusKey)) {
                $focusKeyFull = $focusKey . "\0" . $key;
                $focus[$focusKey][$focusKeyFull] = $groups[$key];
            }
        }
    }
    fclose($handle);

    foreach ($focus as $focusKey => $items) {
        $focus[$focusKey] = topRows(array_values($items), $top);
    }

    return [
        'path' => 'log_php_errors.txt',
        'exists' => true,
        'bytes' => filesize($logPath) ?: 0,
        'entries' => $entries,
        'unparsed' => $unparsed,
        'groups' => topRows(array_values($groups), $top),
        'focus' => $focus,
        'top_files' => topCountMap($files, 20),
        'message_types' => topCountMap($messageTypes, 30),
        'layer_counts' => $layerCounts,
    ];
}

/**
 * @return array<string,string>|null
 */
function parseLogLine(string $line, string $root): ?array
{
    $pattern = '/^\[(?<time>[^\]]+)\]\s+PHP\s+(?<type>[^:]+):\s+(?<message>.*)\s+in\s+(?<file>.+)\s+on\s+line\s+(?<line>\d+)\s*$/u';
    if (!preg_match($pattern, trim($line), $matches)) {
        return null;
    }

    return [
        'time' => $matches['time'],
        'type' => trim($matches['type']),
        'message' => trim($matches['message']),
        'normalized_message' => normalizeMessage(trim($matches['message'])),
        'file' => normalizeLogFile($matches['file'], $root),
        'line' => $matches['line'],
    ];
}

function normalizeMessage(string $message): string
{
    $message = preg_replace('/\bline\s+\d+\b/i', 'line <N>', $message) ?? $message;
    $message = preg_replace('/\b\d+\b/', '<N>', $message) ?? $message;
    $message = preg_replace('/"[^"]*"/', '"<value>"', $message) ?? $message;
    $message = preg_replace("/'[^']*'/", "'<value>'", $message) ?? $message;
    $message = preg_replace('/\s+/', ' ', $message) ?? $message;

    return trim($message);
}

function normalizeLogFile(string $file, string $root): string
{
    $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($file));
    $rootNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root);
    if (str_starts_with(strtolower($file), strtolower($rootNormalized))) {
        return relativePath($file, $root);
    }

    $needle = 'pokemonchic.com' . DIRECTORY_SEPARATOR;
    $pos = stripos($file, $needle);
    if ($pos !== false) {
        return str_replace('\\', '/', substr($file, $pos + strlen($needle)));
    }

    return str_replace('\\', '/', $file);
}

function classifyLayer(string $file): string
{
    $normalized = str_replace('\\', '/', $file);
    foreach (NEW_LAYER_DIRS as $dir) {
        if (str_starts_with($normalized, $dir . '/')) {
            return 'new_layer';
        }
    }
    foreach (LEGACY_DIRS as $dir) {
        if (str_starts_with($normalized, $dir . '/')) {
            return 'legacy';
        }
    }
    if (!str_contains($normalized, '/')) {
        return 'legacy';
    }

    return 'unknown';
}

/**
 * @param array<int,array<string,mixed>> $rows
 * @return array<int,array<string,mixed>>
 */
function topRows(array $rows, int $top): array
{
    usort($rows, static function (array $a, array $b): int {
        return ((int) ($b['count'] ?? 0) <=> (int) ($a['count'] ?? 0))
            ?: strcmp((string) ($a['file'] ?? ''), (string) ($b['file'] ?? ''));
    });

    return array_slice($rows, 0, $top);
}

/**
 * @param array<string,int> $counts
 * @return array<int,array{key:string,count:int}>
 */
function topCountMap(array $counts, int $top): array
{
    arsort($counts, SORT_NUMERIC);
    $rows = [];
    foreach (array_slice($counts, 0, $top, true) as $key => $count) {
        $rows[] = ['key' => (string) $key, 'count' => (int) $count];
    }

    return $rows;
}

/**
 * @param array<string,mixed> $lint
 * @param array<string,mixed> $log
 * @return array<int,array<string,mixed>>
 */
function buildCandidates(array $lint, array $log): array
{
    $candidates = [];
    foreach (($lint['new_layer']['failed'] ?? []) as $failure) {
        $candidates[] = [
            'file' => $failure['file'],
            'reason' => 'lint failure',
            'estimate' => str_contains((string) $failure['error'], 'unexpected') ? 'requires analysis' : '1 line',
        ];
    }

    foreach (($log['groups'] ?? []) as $group) {
        $file = (string) ($group['file'] ?? '');
        if (classifyLayer($file) !== 'new_layer') {
            continue;
        }
        $candidates[] = [
            'file' => $file,
            'reason' => (string) ($group['type'] ?? '') . ': ' . (string) ($group['message'] ?? ''),
            'count' => (int) ($group['count'] ?? 0),
            'estimate' => guessEstimate((string) ($group['message'] ?? '')),
        ];
    }

    return array_slice($candidates, 0, 30);
}

function guessEstimate(string $message): string
{
    if (str_contains($message, 'Undefined') || str_contains($message, 'Trying to access array offset')) {
        return '1 line';
    }

    return 'requires analysis';
}

/**
 * @param array<string,mixed> $report
 */
function renderMarkdown(array $report): string
{
    $lines = [];
    $lines[] = '# PHP health report';
    $lines[] = '';
    $lines[] = '- Generated: `' . $report['generated_at'] . '`';
    $lines[] = '- Root: `' . str_replace('\\', '/', (string) $report['root']) . '`';
    $lines[] = '- PHP: `' . str_replace('\\', '/', (string) $report['php_binary']) . '`';
    $lines[] = '';

    $lint = $report['lint'];
    $lines[] = '## Summary';
    $lines[] = '';
    $lines[] = '| Area | Checked | Failed |';
    $lines[] = '|---|---:|---:|';
    $lines[] = '| New layer | ' . $lint['new_layer']['checked'] . ' | ' . $lint['new_layer']['failed_count'] . ' |';
    $lines[] = '| Legacy | ' . $lint['legacy']['checked'] . ' | ' . $lint['legacy']['failed_count'] . ' |';
    $lines[] = '';

    $lines[] = '## New Layer Lint Failures';
    $lines[] = '';
    appendLintTable($lines, $lint['new_layer']['failed']);

    $lines[] = '';
    $lines[] = '## Legacy Lint Failures';
    $lines[] = '';
    appendLintTable($lines, $lint['legacy']['failed']);

    $log = $report['log'];
    $lines[] = '';
    $lines[] = '## Error Log Top Groups';
    $lines[] = '';
    $lines[] = '- Log exists: `' . (($log['exists'] ?? false) ? 'yes' : 'no') . '`';
    $lines[] = '- Entries parsed: `' . ($log['entries'] ?? 0) . '`, unparsed lines: `' . ($log['unparsed'] ?? 0) . '`';
    $lines[] = '- Layer split: new_layer=`' . ($log['layer_counts']['new_layer'] ?? 0) . '`, legacy=`' . ($log['layer_counts']['legacy'] ?? 0) . '`, unknown=`' . ($log['layer_counts']['unknown'] ?? 0) . '`';
    $lines[] = '';
    appendGroupTable($lines, $log['groups'] ?? []);

    $lines[] = '';
    $lines[] = '## Error Log Top Files';
    $lines[] = '';
    appendCountTable($lines, $log['top_files'] ?? [], 'File');

    $lines[] = '';
    $lines[] = '## Message Types Top 30';
    $lines[] = '';
    appendCountTable($lines, $log['message_types'] ?? [], 'Message');

    $lines[] = '';
    $lines[] = '## Focus Buckets';
    foreach (($log['focus'] ?? []) as $name => $rows) {
        $lines[] = '';
        $lines[] = '### ' . $name;
        $lines[] = '';
        appendGroupTable($lines, $rows);
    }

    $lines[] = '';
    $lines[] = '## Candidates For Fixing';
    $lines[] = '';
    $lines[] = '| File | Reason | Estimate |';
    $lines[] = '|---|---|---|';
    foreach ($report['candidates'] as $candidate) {
        $reason = (string) ($candidate['reason'] ?? '');
        if (isset($candidate['count'])) {
            $reason .= ' (' . $candidate['count'] . ' entries)';
        }
        $lines[] = '| `' . md((string) $candidate['file']) . '` | ' . md($reason) . ' | ' . md((string) $candidate['estimate']) . ' |';
    }
    if ($report['candidates'] === []) {
        $lines[] = '| - | No new-layer candidates found in lint failures or top log groups. | - |';
    }

    $lines[] = '';
    $lines[] = '## Legacy - Do Not Touch';
    $lines[] = '';
    $lines[] = '- Legacy lint failures: `' . $lint['legacy']['failed_count'] . '` / `' . $lint['legacy']['checked'] . '`.';
    $lines[] = '- Legacy log entries: `' . ($log['layer_counts']['legacy'] ?? 0) . '`.';
    $lines[] = '';

    return implode(PHP_EOL, $lines);
}

/**
 * @param array<int,string> $lines
 * @param array<int,array<string,string>> $failures
 */
function appendLintTable(array &$lines, array $failures): void
{
    $lines[] = '| File | Error |';
    $lines[] = '|---|---|';
    if ($failures === []) {
        $lines[] = '| - | No failures. |';
        return;
    }
    foreach ($failures as $failure) {
        $lines[] = '| `' . md($failure['file']) . '` | ' . md($failure['error']) . ' |';
    }
}

/**
 * @param array<int,string> $lines
 * @param array<int,array<string,mixed>> $groups
 */
function appendGroupTable(array &$lines, array $groups): void
{
    $lines[] = '| Count | First | Last | Type | File | Message |';
    $lines[] = '|---:|---|---|---|---|---|';
    if ($groups === []) {
        $lines[] = '| 0 | - | - | - | - | - |';
        return;
    }
    foreach ($groups as $group) {
        $lines[] = '| ' . (int) $group['count'] . ' | ' . md((string) $group['first_at']) . ' | ' . md((string) $group['last_at']) . ' | ' . md((string) $group['type']) . ' | `' . md((string) $group['file']) . '` | ' . md((string) $group['message']) . ' |';
    }
}

/**
 * @param array<int,string> $lines
 * @param array<int,array{key:string,count:int}> $rows
 */
function appendCountTable(array &$lines, array $rows, string $label): void
{
    $lines[] = '| ' . $label . ' | Count |';
    $lines[] = '|---|---:|';
    if ($rows === []) {
        $lines[] = '| - | 0 |';
        return;
    }
    foreach ($rows as $row) {
        $lines[] = '| `' . md($row['key']) . '` | ' . $row['count'] . ' |';
    }
}

function md(string $value): string
{
    return str_replace(["\r", "\n", '|'], [' ', ' ', '\\|'], $value);
}

function makeAbsolutePath(string $path, string $root): string
{
    if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
        return normalizePath($path);
    }

    return normalizePath($root . DIRECTORY_SEPARATOR . $path);
}

function normalizePath(string $path): string
{
    $real = realpath($path);
    if ($real !== false) {
        return $real;
    }

    return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function relativePath(string $path, string $root): string
{
    $path = str_replace('\\', '/', normalizePath($path));
    $root = rtrim(str_replace('\\', '/', normalizePath($root)), '/');
    if (str_starts_with(strtolower($path), strtolower($root . '/'))) {
        return substr($path, strlen($root) + 1);
    }

    return $path;
}
