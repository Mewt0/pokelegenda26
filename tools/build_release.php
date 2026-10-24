<?php
declare(strict_types=1);

// Package an exact working-copy snapshot, including approved uncommitted runtime
// files, without shipping local secrets, historical dumps or agent workspaces.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
if (!class_exists(ZipArchive::class)) { fwrite(STDERR, "ext-zip is required.\n"); exit(2); }
$root = dirname(__DIR__);
$files = [];
$roots = ['admin', 'app', 'config', 'css', 'database/migrations', 'images', 'img', 'include', 'new_diz', 'new_diz4', 'pok', 'public', 'script', 'src', 'views'];
foreach ($roots as $dir) {
    if (!is_dir($root . '/' . $dir)) { continue; }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->isLink()) { continue; }
        $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        // TASK-030: retired JSON encoder is PHP<8 syntax and only referenced by
        // legacy battle routes that terminate with HTTP410. Preserve source,
        // exclude it from the supported runtime artifact.
        if ($path === 'include/function/Services_JSON.php') { continue; }
        if (basename($path) !== '.htaccess' && preg_match('~(?:^|/)(?:\.[^/]+|node_modules|prototypes)(?:/|$)|\.bak(?:-|$)|\.(?:zip|rar|7z|log|py|ps1)$~i', $path)) { continue; }
        if (str_ends_with(strtolower($path), '.sql') && !str_starts_with($path, 'database/migrations/')) { continue; }
        $files[$path] = $file->getPathname();
    }
}
foreach (['.htaccess', '.env.example', 'composer.json', 'favicon.ico', 'index.php', 'game.php', 'autoriz.php', 'attak_pokes.php', 'ban.php', 'events.php', 'itemsinpage.class3.php', 'mailTo.php', 'moder.func.php', 'page.php', 'pInf.php', 'robotsms.php', 'style.php'] as $path) {
    if (is_file($root . '/' . $path)) { $files[$path] = $root . '/' . $path; }
}
ksort($files);
$dir = $root . '/storage/releases';
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) { throw new RuntimeException('Cannot create release directory.'); }
$artifact = $dir . '/pokemonchic_rc_' . date('Ymd_His') . '.zip';
$zip = new ZipArchive();
if ($zip->open($artifact, ZipArchive::CREATE | ZipArchive::EXCL) !== true) { throw new RuntimeException('Cannot create release archive.'); }
$manifest = [];
foreach ($files as $path => $file) {
    if (!$zip->addFile($file, $path)) { throw new RuntimeException('Cannot package ' . $path); }
    $manifest[$path] = ['bytes' => filesize($file), 'sha256' => hash_file('sha256', $file)];
}
$zip->addFromString('RELEASE_MANIFEST.json', json_encode(['created_at' => date('c'), 'php_target' => '8.5.11', 'source' => 'working-copy snapshot; not a Git tag', 'files' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$zip->addFromString('DEPLOY.txt', "Target: PHP 8.5.11, Apache mod_rewrite, MySQL 5.5-compatible PDO.\nConfigure a private .env using .env.example; production must keep APP_DEBUG=0, APP_ENV=production, QA_TOOLS_ENABLED=0.\nDo not expose this archive, RELEASE_MANIFEST.json or DEPLOY.txt from the web root.\nApply migrations and take a backup using operator tooling outside the public tree.\nBefore opening beta: full regression on target, desktop/mobile QA and provider-specific HTTPS/SMTP/jobs checks.\n");
$zip->close();
echo json_encode(['artifact' => $artifact, 'files' => count($files), 'bytes' => filesize($artifact), 'sha256' => hash_file('sha256', $artifact), 'excluded' => ['.env', 'backups', 'logs', 'tools', 'docs', '_incoming', '_qa-*', '.git', '.hybrid', '.codebase-memory', 'prototype assets']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
