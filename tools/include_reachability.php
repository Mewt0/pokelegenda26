<?php
declare(strict_types=1);

/**
 * Reachability of the legacy include/ tree.
 *
 * The legacy code pulls files in three different ways, and all three are handled:
 *   1) by literal path   — require_once __DIR__ . '/include/function/config.php',
 *                          require_once('include/files/shapka.php'), $_SERVER['DOCUMENT_ROOT'].'/include/...'
 *   2) by relative name  — inside include/rooms/101.php: include ("npc/npc_13.php")
 *   3) by a dynamically built path — include/files/char.world.php:
 *                          $filesLoc = "include/rooms/".$myrow['buildmy'].".php"
 *
 * A file counts as reachable if its project-relative path, its path without the leading
 * "include/", or its basename appears anywhere in the project outside that file itself,
 * or if it sits in a directory that legacy code builds dynamically.
 *
 * Limits: basename matching can mark a file reachable that is only mentioned in a comment.
 * The tool is deliberately conservative — it never claims a file is dead unless nothing at
 * all mentions it.
 *
 *   php tools/include_reachability.php
 *   php tools/include_reachability.php --json
 */

$root = str_replace('\\', '/', dirname(__DIR__));

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

$json = in_array('--json', $argv, true);

$includeFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/include', FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->isFile()) {
        $includeFiles[] = str_replace('\\', '/', $file->getPathname());
    }
}
sort($includeFiles);

// Everything that can request a legacy file, plus the legacy tree itself (relative includes).
$sources = [];
$skipDirs = ['include', 'storage', 'tmp', 'downloaded_assets', 'downloaded_assets_sorted', 'downloaded_images', 'new_diz', 'new_diz4'];
$it = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function (SplFileInfo $current) use ($skipDirs): bool {
            if (!$current->isDir()) {
                return true;
            }
            return !in_array($current->getFilename(), $skipDirs, true) && $current->getFilename() !== '.git';
        }
    )
);
foreach ($it as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'json', 'inc'], true)) {
        $sources[] = str_replace('\\', '/', $file->getPathname());
    }
}
foreach (glob($root . '/.htaccess') ?: [] as $path) {
    $sources[] = str_replace('\\', '/', $path);
}
foreach ($includeFiles as $path) {
    $sources[] = $path;
}

$dynamicallyLoaded = ['include/rooms/' => true];

$live = [];
$dead = [];
foreach ($includeFiles as $path) {
    $rel = str_replace($root . '/', '', $path);
    $short = preg_replace('#^include/#', '', $rel);
    $base = basename($rel);

    if (isset($dynamicallyLoaded[dirname($rel) . '/'])) {
        $live[] = $rel;
        continue;
    }

    $found = false;
    foreach ($sources as $source) {
        if ($source === $path) {
            continue;
        }
        $text = @file_get_contents($source);
        if ($text === false) {
            continue;
        }
        if (str_contains($text, $rel) || str_contains($text, $short) || str_contains($text, $base)) {
            $found = true;
            break;
        }
    }

    if ($found) {
        $live[] = $rel;
    } else {
        $dead[] = $rel;
    }
}

if ($json) {
    echo json_encode([
        'include_total' => count($includeFiles),
        'live' => $live,
        'dead' => $dead,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit(0);
}

printf("include/ files : %d\n", count($includeFiles));
printf("reachable      : %d\n", count($live));
printf("unreachable    : %d\n", count($dead));
echo "\nunreachable files (nothing in the project mentions them):\n";
foreach ($dead as $rel) {
    printf("  %-58s %6d bytes\n", $rel, (int) filesize($root . '/' . $rel));
}
echo "\ndynamically loaded directories treated as live:\n";
foreach (array_keys($dynamicallyLoaded) as $dir) {
    echo '  ', $dir, "\n";
}
