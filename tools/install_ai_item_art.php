<?php
declare(strict_types=1);

/**
 * Install Codex-generated item art from public/img/items/ai-20261025/raw/<key>.png.
 *
 * CLI only. Reads docs/reports/harness-item-art-jobs-20261025.json, alpha-trims each
 * completed image, fits the visible long side to 108 px on a 128x128 transparent canvas,
 * copies the result to every item ID assigned to the job, refreshes numeric
 * public/img/items/<ID>.png commission paths, updates public/img/items/index.json and
 * writes a manifest. Missing raw files are skipped, so partial batches are safe.
 *
 * No database writes. Existing index/numeric/generated files are backed up outside the
 * webroot before they are replaced. Originals are never deleted.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

const CANVAS_SIZE = 128;
const VISIBLE_SIDE = 108;
const ALPHA_CUTOFF = 112;
const MAX_SOURCE_SIDE = 4096;

function art_fail(string $message, int $code = 1): never
{
    fwrite(STDERR, 'ERROR: ' . $message . PHP_EOL);
    exit($code);
}

function art_info(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function art_slash(string $path): string
{
    return str_replace('\\', '/', $path);
}

function art_relative(string $root, string $path): string
{
    $root = rtrim(art_slash($root), '/');
    $path = art_slash($path);
    return str_starts_with($path, $root . '/') ? substr($path, strlen($root) + 1) : $path;
}

function art_remove_tree(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }
    $items = scandir($directory);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $directory . '/' . $item;
        if (is_dir($path)) {
            art_remove_tree($path);
        } elseif (is_file($path)) {
            @unlink($path);
        }
    }
    @rmdir($directory);
}

function art_hash_file(string $path): ?string
{
    $hash = @hash_file('sha256', $path);
    return is_string($hash) && $hash !== '' ? $hash : null;
}

function art_write_json_atomic(string $path, string $json): bool
{
    $temporary = $path . '.tmp-' . getmypid() . '-' . bin2hex(random_bytes(4));
    if (@file_put_contents($temporary, $json, LOCK_EX) === false) {
        @unlink($temporary);
        return false;
    }
    if (@rename($temporary, $path)) {
        return true;
    }
    // Windows may refuse to replace an existing file with rename().
    $copied = @copy($temporary, $path);
    @unlink($temporary);
    return $copied;
}

/**
 * @return 'copied'|'unchanged'|'failed'
 */
function art_install_copy(string $source, string $destination): string
{
    $sourceHash = art_hash_file($source);
    if ($sourceHash !== null && is_file($destination)) {
        $destinationHash = art_hash_file($destination);
        if ($destinationHash !== null && hash_equals($sourceHash, $destinationHash)) {
            return 'unchanged';
        }
    }

    $delays = [100000, 200000, 400000];
    for ($attempt = 1; $attempt <= 4; $attempt++) {
        if (@copy($source, $destination)) {
            return 'copied';
        }
        if ($attempt < 4) {
            usleep($delays[$attempt - 1]);
        }
    }
    return 'failed';
}

function art_print_skips(array $rows): void
{
    if ($rows === []) {
        return;
    }
    $reasons = [];
    foreach ($rows as $row) {
        $reason = isset($row['reason']) && is_string($row['reason']) ? $row['reason'] : 'unknown reason';
        $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
    }
    arsort($reasons);
    art_info('Skipped/pending: ' . count($rows) . ' job(s)');
    foreach ($reasons as $reason => $count) {
        art_info('  - ' . $count . ' x ' . $reason);
    }
    $sample = [];
    foreach ($rows as $row) {
        if (isset($row['key']) && is_string($row['key'])) {
            $sample[] = $row['key'];
        }
        if (count($sample) >= 6) {
            break;
        }
    }
    if ($sample !== []) {
        art_info('  first keys: ' . implode(', ', $sample));
    }
}

/**
 * @return array{ok: bool, visible: array{0:int,1:int}|null, message: string}
 */
function art_normalize(string $source, string $destination): array
{
    $bytes = @file_get_contents($source);
    if ($bytes === false || $bytes === '') {
        return ['ok' => false, 'visible' => null, 'message' => 'file is empty or unreadable'];
    }

    $image = @imagecreatefromstring($bytes);
    if ($image === false) {
        return ['ok' => false, 'visible' => null, 'message' => 'not a decodable image'];
    }

    $width = imagesx($image);
    $height = imagesy($image);
    if ($width < 1 || $height < 1 || $width > MAX_SOURCE_SIDE || $height > MAX_SOURCE_SIDE) {
        unset($image);
        return ['ok' => false, 'visible' => null, 'message' => 'unsupported image dimensions ' . $width . 'x' . $height];
    }

    if (!imageistruecolor($image) && !imagepalettetotruecolor($image)) {
        unset($image);
        return ['ok' => false, 'visible' => null, 'message' => 'cannot convert palette image to true colour'];
    }
    imagealphablending($image, false);
    imagesavealpha($image, true);

    $left = $width;
    $top = $height;
    $right = -1;
    $bottom = -1;
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgba = imagecolorat($image, $x, $y);
            $alpha = ($rgba >> 24) & 0x7F;
            if ($alpha >= ALPHA_CUTOFF) {
                continue;
            }
            if ($x < $left) {
                $left = $x;
            }
            if ($x > $right) {
                $right = $x;
            }
            if ($y < $top) {
                $top = $y;
            }
            if ($y > $bottom) {
                $bottom = $y;
            }
        }
    }

    if ($right < $left || $bottom < $top) {
        unset($image);
        return ['ok' => false, 'visible' => null, 'message' => 'image is fully transparent'];
    }

    $cropWidth = $right - $left + 1;
    $cropHeight = $bottom - $top + 1;
    $scale = VISIBLE_SIDE / max($cropWidth, $cropHeight);
    $visibleWidth = max(1, (int) round($cropWidth * $scale));
    $visibleHeight = max(1, (int) round($cropHeight * $scale));

    $canvas = imagecreatetruecolor(CANVAS_SIZE, CANVAS_SIZE);
    if ($canvas === false) {
        unset($image);
        return ['ok' => false, 'visible' => null, 'message' => 'cannot allocate 128x128 canvas'];
    }
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    if ($transparent === false) {
        unset($canvas, $image);
        return ['ok' => false, 'visible' => null, 'message' => 'cannot allocate transparent color'];
    }
    imagefill($canvas, 0, 0, $transparent);

    $destinationX = intdiv(CANVAS_SIZE - $visibleWidth, 2);
    $destinationY = intdiv(CANVAS_SIZE - $visibleHeight, 2);
    $copied = imagecopyresampled(
        $canvas,
        $image,
        $destinationX,
        $destinationY,
        $left,
        $top,
        $visibleWidth,
        $visibleHeight,
        $cropWidth,
        $cropHeight
    );
    unset($image);
    if (!$copied) {
        unset($canvas);
        return ['ok' => false, 'visible' => null, 'message' => 'cannot resample image'];
    }

    $saved = imagepng($canvas, $destination, 9);
    unset($canvas);
    clearstatcache(true, $destination);
    if (!$saved || !is_file($destination) || filesize($destination) < 1) {
        return ['ok' => false, 'visible' => null, 'message' => 'cannot write normalized PNG'];
    }

    return ['ok' => true, 'visible' => [$visibleWidth, $visibleHeight], 'message' => 'normalized'];
}

$options = getopt('', ['jobs::', 'raw::', 'version::', 'backup-root::', 'dry-run', 'help']) ?: [];

if (isset($options['help'])) {
    art_info('Usage: php tools/install_ai_item_art.php [--jobs=PATH] [--raw=DIR] [--version=ai-20261025]');
    art_info('       [--backup-root=E:/OSPanel/maintenance] [--dry-run]');
    art_info('Installs only raw/<key>.png files that exist and decode; missing jobs are skipped.');
    exit(0);
}

$root = dirname(__DIR__);
$jobsPath = isset($options['jobs']) && is_string($options['jobs']) && $options['jobs'] !== ''
    ? $options['jobs']
    : $root . '/docs/reports/harness-item-art-jobs-20261025.json';
$version = isset($options['version']) && is_string($options['version']) && $options['version'] !== ''
    ? $options['version']
    : 'ai-20261025';
$rawDirectory = isset($options['raw']) && is_string($options['raw']) && $options['raw'] !== ''
    ? $options['raw']
    : $root . '/public/img/items/' . $version . '/raw';
$backupBase = isset($options['backup-root']) && is_string($options['backup-root']) && $options['backup-root'] !== ''
    ? $options['backup-root']
    : 'E:/OSPanel/maintenance';
$dryRun = array_key_exists('dry-run', $options);

if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $version)) {
    art_fail('version must be a filename-safe slug, for example ai-20261025');
}

$rootNormalized = rtrim(art_slash(realpath($root) ?: $root), '/');
$generatedDirectory = $rootNormalized . '/public/img/items/' . $version;
$indexPath = $rootNormalized . '/public/img/items/index.json';
$manifestPath = $generatedDirectory . '/manifest.json';

$backupNormalized = rtrim(art_slash($backupBase), '/');
if (!preg_match('~^[A-Za-z]:/~', $backupNormalized) && !str_starts_with($backupNormalized, '/')) {
    $backupNormalized = $rootNormalized . '/' . $backupNormalized;
}
if (str_starts_with(strtolower($backupNormalized . '/'), strtolower($rootNormalized . '/'))) {
    art_fail('backup root must be outside the webroot: ' . $backupNormalized);
}

// The lock lives outside the webroot. Acquire it before reading index.json,
// manifest.json or the normalization cache, and hold it until process exit.
if (!is_dir($backupNormalized) && !mkdir($backupNormalized, 0755, true) && !is_dir($backupNormalized)) {
    art_fail('cannot create lock directory: ' . $backupNormalized);
}
$lockPath = $backupNormalized . '/item-icons-' . $version . '.lock';
$lockHandle = @fopen($lockPath, 'c');
if ($lockHandle === false) {
    art_fail('cannot open installer lock: ' . $lockPath);
}
if (!flock($lockHandle, LOCK_EX)) {
    art_fail('cannot acquire installer lock: ' . $lockPath);
}
// Intentionally no fclose(): the exclusive lock is held until process exit.
art_info('Lock: ' . $lockPath);

$cachePath = $backupNormalized . '/item-icons-' . $version . '-normalize-cache.json';

if (!is_file($jobsPath)) {
    art_fail('jobs file not found: ' . $jobsPath);
}
if (!is_dir($rawDirectory)) {
    art_fail('raw directory not found: ' . $rawDirectory . ' (Codex may not have generated any images yet)');
}

try {
    $jobDocument = json_decode((string) file_get_contents($jobsPath), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    art_fail('jobs JSON is invalid: ' . $exception->getMessage());
}
if (!is_array($jobDocument) || !isset($jobDocument['jobs']) || !is_array($jobDocument['jobs'])) {
    art_fail('jobs JSON must be an object with a jobs array');
}

$jobs = [];
$jobKeys = [];
$assignedIds = [];
foreach ($jobDocument['jobs'] as $position => $job) {
    if (!is_array($job)) {
        art_fail('job #' . $position . ' is not an object');
    }
    $key = isset($job['key']) && is_string($job['key']) ? $job['key'] : '';
    $ids = isset($job['ids']) && is_array($job['ids']) ? $job['ids'] : [];
    $name = isset($job['name']) && is_string($job['name']) ? trim($job['name']) : '';
    $subject = isset($job['subject']) && is_string($job['subject']) ? trim($job['subject']) : '';
    if ($key === '' || !preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
        art_fail('job #' . $position . ' has an unsafe or empty key');
    }
    if (isset($jobKeys[$key])) {
        art_fail('duplicate job key: ' . $key);
    }
    if ($name === '' || $subject === '') {
        art_fail('job ' . $key . ' must have a name and subject');
    }
    if ($ids === []) {
        art_fail('job ' . $key . ' has no ids');
    }
    $cleanIds = [];
    foreach ($ids as $id) {
        if (!is_int($id) || $id < 1) {
            art_fail('job ' . $key . ' contains a non-positive or non-integer id');
        }
        if (isset($assignedIds[$id])) {
            art_fail('item id ' . $id . ' is assigned more than once (jobs ' . $assignedIds[$id] . ' and ' . $key . ')');
        }
        $assignedIds[$id] = $key;
        $cleanIds[] = $id;
    }
    sort($cleanIds, SORT_NUMERIC);
    if ($key !== 'item-' . $cleanIds[0]) {
        art_fail('job key ' . $key . ' must equal item-<minimum id> (expected item-' . $cleanIds[0] . ')');
    }
    $jobKeys[$key] = true;
    $jobs[] = ['key' => $key, 'ids' => $cleanIds, 'name' => $name, 'subject' => $subject];
}

if ($jobs === []) {
    art_fail('jobs array is empty');
}

$planned = [];
$pending = [];
foreach ($jobs as $job) {
    $rawPath = rtrim(art_slash($rawDirectory), '/') . '/' . $job['key'] . '.png';
    if (!is_file($rawPath)) {
        $pending[] = ['key' => $job['key'], 'ids' => $job['ids'], 'reason' => 'raw file not found'];
        continue;
    }
    $planned[] = $job + ['raw' => $rawPath];
}

art_info('Jobs: ' . count($jobs) . '; item ids: ' . count($assignedIds) . '; raw images found: ' . count($planned) . '; pending: ' . count($pending));

if ($dryRun) {
    art_info('Dry run: ready=' . count($planned) . '; pending=' . count($pending) . '; nothing written.');
    art_print_skips($pending);
    exit(0);
}

if ($planned === []) {
    art_info('No completed raw images to install. Nothing was changed.');
    art_print_skips($pending);
    exit(0);
}

if (!is_file($indexPath)) {
    art_fail('index file not found: ' . $indexPath);
}
try {
    $index = json_decode((string) file_get_contents($indexPath), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    art_fail('index.json is invalid; refusing to overwrite it: ' . $exception->getMessage());
}
if (!is_array($index)) {
    art_fail('index.json must decode to an object/array');
}

// Read mutable metadata only after the exclusive lock is held.
$previousInstalled = [];
if (is_file($manifestPath)) {
    try {
        $previousDocument = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($previousDocument) && isset($previousDocument['installed']) && is_array($previousDocument['installed'])) {
            foreach ($previousDocument['installed'] as $row) {
                if (!is_array($row) || !isset($row['id'])) {
                    continue;
                }
                $id = (int) $row['id'];
                if ($id < 1 || !is_file($generatedDirectory . '/' . $id . '.png')) {
                    continue;
                }
                $row['id'] = $id;
                $previousInstalled[$id] = $row;
            }
        }
    } catch (JsonException $exception) {
        art_info('Warn: previous manifest is invalid; continuing without it.');
    }
}

$cache = ['version' => $version, 'entries' => []];
if (is_file($cachePath)) {
    try {
        $cacheDocument = json_decode((string) file_get_contents($cachePath), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($cacheDocument)
            && isset($cacheDocument['entries'])
            && is_array($cacheDocument['entries'])
            && ($cacheDocument['canvas'] ?? null) === [CANVAS_SIZE, CANVAS_SIZE]
            && (int) ($cacheDocument['visible_long_side'] ?? 0) === VISIBLE_SIDE
        ) {
            $cache['entries'] = $cacheDocument['entries'];
        } else {
            art_info('Warn: normalization cache shape/version mismatch; treating it as empty.');
        }
    } catch (JsonException $exception) {
        art_info('Warn: normalization cache is invalid; treating it as empty.');
    }
}

$stagingDirectory = $generatedDirectory . '/.stage-' . getmypid() . '-' . bin2hex(random_bytes(4));
if (!is_dir($stagingDirectory) && !mkdir($stagingDirectory, 0755, true) && !is_dir($stagingDirectory)) {
    art_fail('cannot create staging directory: ' . $stagingDirectory);
}

$installed = [];
$failed = $pending;
$cacheCandidates = [];
$jobMinIds = [];
$reusedJobs = 0;
$normalizedJobs = 0;
foreach ($planned as $job) {
    $jobMinIds[$job['key']] = $job['ids'][0];
    $stagedByJob = $stagingDirectory . '/' . $job['key'] . '.png';
    $rawHash = art_hash_file($job['raw']);
    if ($rawHash === null) {
        $failed[] = ['key' => $job['key'], 'ids' => $job['ids'], 'reason' => 'raw file could not be hashed'];
        continue;
    }

    $visible = null;
    $normalizedHash = null;
    $reused = false;
    $cached = $cache['entries'][$job['key']] ?? null;
    if (is_array($cached)
        && (string) ($cached['raw_sha256'] ?? '') === $rawHash
        && is_array($cached['visible'] ?? null)
        && count($cached['visible']) === 2
        && (int) ($cached['visible'][0] ?? 0) > 0
        && (int) ($cached['visible'][1] ?? 0) > 0
    ) {
        $candidate = $generatedDirectory . '/' . $job['ids'][0] . '.png';
        $cachedOutputHash = (string) ($cached['normalized_sha256'] ?? '');
        if ($cachedOutputHash !== '' && is_file($candidate)) {
            $actualHash = art_hash_file($candidate);
            if ($actualHash !== null && hash_equals($cachedOutputHash, $actualHash) && copy($candidate, $stagedByJob)) {
                $visible = [(int) $cached['visible'][0], (int) $cached['visible'][1]];
                $normalizedHash = $actualHash;
                $reused = true;
            }
        }
    }

    if (!$reused) {
        try {
            $result = art_normalize($job['raw'], $stagedByJob);
        } catch (Throwable $exception) {
            $result = ['ok' => false, 'visible' => null, 'message' => $exception->getMessage()];
        }
        if (!$result['ok']) {
            $failed[] = ['key' => $job['key'], 'ids' => $job['ids'], 'reason' => $result['message']];
            continue;
        }
        $visible = $result['visible'];
        $normalizedHash = art_hash_file($stagedByJob);
        if ($normalizedHash === null) {
            $failed[] = ['key' => $job['key'], 'ids' => $job['ids'], 'reason' => 'normalized file could not be hashed'];
            continue;
        }
    }

    if ($reused) {
        $reusedJobs++;
    } else {
        $normalizedJobs++;
    }

    $cacheCandidates[$job['key']] = [
        'raw_sha256' => $rawHash,
        'normalized_sha256' => $normalizedHash,
        'normalized_file' => $version . '/' . $job['ids'][0] . '.png',
        'visible' => $visible,
        'updated_at' => gmdate('c'),
    ];

    foreach ($job['ids'] as $id) {
        $staged = $stagingDirectory . '/' . $id . '.png';
        if (!copy($stagedByJob, $staged)) {
            $failed[] = ['key' => $job['key'], 'ids' => [$id], 'reason' => 'cannot stage shared job art'];
            continue;
        }
        $installed[$id] = [
            'id' => $id,
            'job_key' => $job['key'],
            'name' => $job['name'],
            'visible' => $visible,
            'staged' => $staged,
        ];
    }
}

if ($installed === []) {
    art_remove_tree($stagingDirectory);
    art_info('No valid images to install. Nothing was changed.');
    art_print_skips($failed);
    exit(0);
}

$backupDirectory = $backupNormalized . '/item-icons-before-' . $version . '-' . date('Ymd-His');
if (is_dir($backupDirectory)) {
    $backupDirectory .= '-' . bin2hex(random_bytes(2));
}
foreach ([$backupDirectory, $backupDirectory . '/numeric', $backupDirectory . '/generated'] as $directoryToMake) {
    if (!is_dir($directoryToMake) && !mkdir($directoryToMake, 0755, true) && !is_dir($directoryToMake)) {
        art_remove_tree($stagingDirectory);
        art_fail('cannot create backup directory: ' . $directoryToMake);
    }
}
if (!copy($indexPath, $backupDirectory . '/index.json')) {
    art_remove_tree($stagingDirectory);
    art_fail('cannot back up index.json to ' . $backupDirectory);
}
if (is_file($jobsPath)) {
    copy($jobsPath, $backupDirectory . '/jobs.json');
}

foreach ($installed as $id => $row) {
    $numericPath = $rootNormalized . '/public/img/items/' . $id . '.png';
    $generatedPath = $generatedDirectory . '/' . $id . '.png';
    if (is_file($numericPath)) {
        copy($numericPath, $backupDirectory . '/numeric/' . $id . '.png');
    }
    if (is_file($generatedPath)) {
        copy($generatedPath, $backupDirectory . '/generated/' . $id . '.png');
    }
}

$manifestRows = [];
$installedJobKeys = [];
$unchangedWrites = 0;
foreach ($installed as $id => $row) {
    $generatedPath = $generatedDirectory . '/' . $id . '.png';
    $numericPath = $rootNormalized . '/public/img/items/' . $id . '.png';
    $generatedResult = art_install_copy($row['staged'], $generatedPath);
    if ($generatedResult === 'failed') {
        $failed[] = ['key' => $row['job_key'], 'ids' => [$id], 'reason' => 'cannot write generated file'];
        continue;
    }
    if ($generatedResult === 'unchanged') {
        $unchangedWrites++;
    }
    $numericResult = art_install_copy($row['staged'], $numericPath);
    if ($numericResult === 'failed') {
        $failed[] = ['key' => $row['job_key'], 'ids' => [$id], 'reason' => 'cannot write numeric file'];
        continue;
    }
    if ($numericResult === 'unchanged') {
        $unchangedWrites++;
    }
    $index[(string) $id] = $version . '/' . $id . '.png';
    $installedJobKeys[$row['job_key']] = true;
    $manifestRows[] = [
        'id' => $id,
        'job_key' => $row['job_key'],
        'name' => $row['name'],
        'file' => $version . '/' . $id . '.png',
        'numeric' => 'public/img/items/' . $id . '.png',
        'canvas' => [CANVAS_SIZE, CANVAS_SIZE],
        'visible_size' => $row['visible'],
    ];
}

if ($manifestRows === []) {
    art_remove_tree($stagingDirectory);
    art_fail('no files were installed');
}

// Keep rows installed by earlier runs in the manifest and in the reported count.
$allInstalled = $previousInstalled;
foreach ($manifestRows as $row) {
    $allInstalled[(int) $row['id']] = $row;
}
ksort($allInstalled, SORT_NUMERIC);
$allInstalled = array_values($allInstalled);

$indexJson = json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
if (file_put_contents($indexPath, $indexJson) === false) {
    art_remove_tree($stagingDirectory);
    art_fail('cannot write index.json; backup is at ' . $backupDirectory);
}

$manifest = [
    'version' => $version,
    'generated_at' => gmdate('c'),
    'jobs_source' => art_relative($rootNormalized, $jobsPath),
    'raw_directory' => art_relative($rootNormalized, $rawDirectory),
    'generated_directory' => $version,
    'index' => 'public/img/items/index.json',
    'backup_directory' => art_slash($backupDirectory),
    'cache' => art_slash($cachePath),
    'canvas' => [CANVAS_SIZE, CANVAS_SIZE],
    'visible_long_side' => VISIBLE_SIDE,
    'job_count' => count($jobs),
    'assigned_id_count' => count($assignedIds),
    'installed_id_count' => count($allInstalled),
    'this_run_installed_count' => count($manifestRows),
    'reused_job_count' => $reusedJobs,
    'normalized_job_count' => $normalizedJobs,
    'unchanged_copy_count' => $unchangedWrites,
    'installed' => $allInstalled,
    'skipped' => $failed,
];
$manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
if (file_put_contents($manifestPath, $manifestJson) === false) {
    art_remove_tree($stagingDirectory);
    art_fail('cannot write manifest.json; backup is at ' . $backupDirectory);
}
copy($manifestPath, $backupDirectory . '/manifest.json');

// Persist the normalization cache only after index/manifest writes succeeded.
$cacheUpdates = [];
foreach ($cacheCandidates as $key => $entry) {
    if (isset($installedJobKeys[$key])
        && isset($jobMinIds[$key])
        && is_file($generatedDirectory . '/' . $jobMinIds[$key] . '.png')
    ) {
        $cacheUpdates[$key] = $entry;
    }
}
if ($cacheUpdates !== []) {
    $cache['entries'] = array_merge($cache['entries'], $cacheUpdates);
    ksort($cache['entries'], SORT_STRING);
    $cacheDocument = [
        'version' => $version,
        'updated_at' => gmdate('c'),
        'canvas' => [CANVAS_SIZE, CANVAS_SIZE],
        'visible_long_side' => VISIBLE_SIDE,
        'entries' => $cache['entries'],
    ];
    $cacheJson = json_encode($cacheDocument, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
    if (!art_write_json_atomic($cachePath, $cacheJson)) {
        art_info('Warn: could not persist normalization cache: ' . $cachePath);
    }
}

art_remove_tree($stagingDirectory);
art_info('Installed this run: ' . count($manifestRows) . ' item icons from ' . count($installedJobKeys) . ' jobs; total installed: ' . count($allInstalled) . '.');
art_info('Unchanged copies skipped: ' . $unchangedWrites . '; normalization: ' . $reusedJobs . ' reused, ' . $normalizedJobs . ' normalized; cache entries: ' . count($cache['entries']) . '.');
art_info('Index: ' . $indexPath);
art_info('Manifest: ' . $manifestPath);
art_info('Backup: ' . $backupDirectory);
art_print_skips($failed);
