<?php
declare(strict_types=1);

/**
 * Finds local asset references that point to missing files and reports unreferenced
 * files under public/img and public/css.
 */

$options = getopt('', ['json', 'strict', 'ignore-orphans', 'root:']);
$projectRoot = isset($options['root'])
    ? normalizePath((string) $options['root'])
    : normalizePath(dirname(__DIR__));

if ($projectRoot === null || !is_dir($projectRoot)) {
    fwrite(STDERR, "Invalid project root.\n");
    exit(2);
}

$json = array_key_exists('json', $options);
$strict = array_key_exists('strict', $options);
$ignoreOrphans = array_key_exists('ignore-orphans', $options);

$sourceFiles = collectSourceFiles($projectRoot);
$assetFiles = collectAssetFiles($projectRoot);
$references = [];
$broken = [];

foreach ($sourceFiles as $source) {
    $content = file_get_contents($source);
    if ($content === false) {
        continue;
    }

    foreach (extractReferences($content, $source) as $reference) {
        $resolved = resolveReference($reference['target'], $source, $projectRoot);
        if ($resolved === null) {
            continue;
        }

        $relativeTarget = toRelativePath($resolved, $projectRoot);
        $references[$relativeTarget] = true;

        if (!is_file($resolved)) {
            $broken[] = [
                'source' => toRelativePath($source, $projectRoot),
                'line' => $reference['line'],
                'target' => normalizeSlashes($reference['target']),
                'resolved' => $relativeTarget,
            ];
        }
    }
}

$orphans = [];
if (!$ignoreOrphans) {
    foreach ($assetFiles as $asset) {
        $relativeAsset = toRelativePath($asset, $projectRoot);
        if (!isset($references[$relativeAsset])) {
            $orphans[] = [
                'path' => $relativeAsset,
                'size' => filesize($asset) ?: 0,
            ];
        }
    }
}

usort($broken, static fn (array $a, array $b): int => [$a['source'], $a['line'], $a['target']] <=> [$b['source'], $b['line'], $b['target']]);
usort($orphans, static fn (array $a, array $b): int => $a['path'] <=> $b['path']);

$stats = [
    'sources' => count($sourceFiles),
    'assets' => count($assetFiles),
    'references' => count($references),
    'broken' => count($broken),
    'orphans' => count($orphans),
];

if ($json) {
    echo json_encode(
        ['broken' => $broken, 'orphans' => $orphans, 'stats' => $stats],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . "\n";
} else {
    printHumanReport($broken, $orphans, $stats, $ignoreOrphans);
}

exit((count($broken) > 0 || ($strict && count($orphans) > 0)) ? 1 : 0);

function collectSourceFiles(string $root): array
{
    $files = [];
    foreach ([
        ['views', 'php'],
        ['public/js', 'js'],
        ['script', 'js'],
        ['public/css', 'css'],
    ] as [$dir, $extension]) {
        $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        $files = array_merge($files, collectFiles($path, [$extension]));
    }

    $index = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($index)) {
        $files[] = $index;
    }

    sort($files);
    return array_values(array_unique($files));
}

function collectAssetFiles(string $root): array
{
    $files = array_merge(
        collectFiles($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'img', null),
        collectFiles($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css', null)
    );
    sort($files);
    return $files;
}

/**
 * @param list<string>|null $extensions
 * @return list<string>
 */
function collectFiles(string $directory, ?array $extensions): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        $path = normalizePath($file->getPathname());
        if ($path === null) {
            continue;
        }
        if ($extensions !== null && !in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }
        $files[] = $path;
    }

    return $files;
}

/**
 * @return list<array{target:string,line:int}>
 */
function extractReferences(string $content, string $source): array
{
    $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    $matches = [];

    if ($extension === 'php') {
        collectPatternReferences('/\b(?:src|href)\s*=\s*(["\'])(.*?)\1/isu', $content, $matches, 2);
        collectPatternReferences('/(["\'])(\/public\/[^"\']+|public\/[^"\']+|img\/[^"\']+|assets\/[^"\']+)\1/isu', $content, $matches, 2);
        $styleBlocks = [];
        collectPatternReferences('/\bstyle\s*=\s*(["\'])(.*?)\1/isu', $content, $styleBlocks, 2);
        foreach ($styleBlocks as $style) {
            collectPatternReferences('/url\(\s*(["\']?)([^)"\']+)\1\s*\)/isu', $style['target'], $matches, 2, $style['line']);
        }
    } elseif ($extension === 'css') {
        collectPatternReferences('/url\(\s*(["\']?)([^)"\']+)\1\s*\)/isu', $content, $matches, 2);
    } elseif ($extension === 'js') {
        collectPatternReferences('/(["\'])(\/public\/[^"\']+|public\/[^"\']+|img\/[^"\']+|assets\/[^"\']+)\1/isu', $content, $matches, 2);
    }

    $references = [];
    foreach ($matches as $match) {
        $target = cleanTarget($match['target']);
        if ($target === null || shouldIgnoreTarget($target)) {
            continue;
        }
        $references[] = ['target' => $target, 'line' => $match['line']];
    }

    return $references;
}

/**
 * @param array<int,array{target:string,line:int}> $matches
 */
function collectPatternReferences(string $pattern, string $content, array &$matches, int $targetGroup, int $lineOffset = 0): void
{
    if (!preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        return;
    }

    foreach ($found as $match) {
        $target = $match[$targetGroup][0] ?? '';
        $offset = $match[$targetGroup][1] ?? 0;
        $line = $lineOffset + substr_count(substr($content, 0, $offset), "\n") + 1;
        $matches[] = ['target' => html_entity_decode($target, ENT_QUOTES | ENT_HTML5), 'line' => $line];
    }
}

function cleanTarget(string $target): ?string
{
    $target = trim($target);
    if ($target === '') {
        return null;
    }
    $target = preg_split('/[?#]/', $target, 2)[0] ?? '';
    $target = trim($target);

    return $target === '' ? null : $target;
}

function shouldIgnoreTarget(string $target): bool
{
    $lower = strtolower($target);
    foreach (['http://', 'https://', '//', 'data:', '#', 'mailto:', 'tel:', 'javascript:', 'blob:'] as $prefix) {
        if (str_starts_with($lower, $prefix)) {
            return true;
        }
    }

    foreach (['<?=', '{{', '${', "'+", '" +', '%s'] as $dynamicMarker) {
        if (str_contains($target, $dynamicMarker)) {
            return true;
        }
    }

    if (str_ends_with($target, '/') || str_contains($target, '<') || str_contains($target, '>') || str_contains($target, ',')) {
        return true;
    }

    $path = parse_url($target, PHP_URL_PATH);
    $extension = is_string($path) ? pathinfo($path, PATHINFO_EXTENSION) : '';
    if ($extension === '') {
        return true;
    }

    return false;
}

function resolveReference(string $target, string $source, string $root): ?string
{
    $target = str_replace('\\', '/', $target);
    if (str_starts_with($target, '/')) {
        return normalizePath($root . DIRECTORY_SEPARATOR . ltrim($target, '/'));
    }

    if (str_starts_with($target, 'public/')) {
        return normalizePath($root . DIRECTORY_SEPARATOR . $target);
    }

    if (str_starts_with($target, 'img/')) {
        return normalizePath($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $target);
    }

    if (str_starts_with($target, 'assets/')) {
        return normalizePath($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $target);
    }

    $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    if ($extension === 'css') {
        return normalizePath(dirname($source) . DIRECTORY_SEPARATOR . $target);
    }

    if (preg_match('/^(?:css|js|img|fonts)\//i', $target) === 1) {
        return normalizePath($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $target);
    }

    return null;
}

function normalizePath(string $path): ?string
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $prefix = '';
    if (preg_match('/^[A-Za-z]:\\\\/', $path, $drive)) {
        $prefix = $drive[0];
        $path = substr($path, strlen($prefix));
    } elseif (str_starts_with($path, DIRECTORY_SEPARATOR)) {
        $prefix = DIRECTORY_SEPARATOR;
        $path = ltrim($path, DIRECTORY_SEPARATOR);
    }

    $parts = [];
    foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }

    return $prefix . implode(DIRECTORY_SEPARATOR, $parts);
}

function toRelativePath(string $path, string $root): string
{
    $path = normalizeSlashes($path);
    $root = rtrim(normalizeSlashes($root), '/') . '/';

    if (str_starts_with(strtolower($path), strtolower($root))) {
        return substr($path, strlen($root));
    }

    return $path;
}

function normalizeSlashes(string $path): string
{
    return str_replace('\\', '/', $path);
}

/**
 * @param list<array{source:string,line:int,target:string,resolved:string}> $broken
 * @param list<array{path:string,size:int}> $orphans
 * @param array{sources:int,assets:int,references:int,broken:int,orphans:int} $stats
 */
function printHumanReport(array $broken, array $orphans, array $stats, bool $ignoreOrphans): void
{
    echo "Asset registry check\n";
    echo "Sources: {$stats['sources']}; assets: {$stats['assets']}; references: {$stats['references']}\n";
    echo "Broken references: {$stats['broken']}\n";

    if ($broken !== []) {
        foreach ($broken as $item) {
            echo "{$item['source']}:{$item['line']} -> {$item['target']} ({$item['resolved']})\n";
        }
    } else {
        echo "No broken references found.\n";
    }

    if ($ignoreOrphans) {
        echo "Orphans: ignored\n";
        return;
    }

    echo "Orphans: {$stats['orphans']}\n";
    foreach ($orphans as $item) {
        echo "{$item['path']} ({$item['size']} bytes)\n";
    }
}
