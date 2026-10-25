<?php
/**
 * Единая точка подключения CSS для страниц игры.
 *
 * Что делает:
 *   1. всегда подключает базовый слой — fonts.css (self-hosted Inter) и tokens.css (дизайн-токены);
 *   2. добавляет слои, переданные страницей в $headStyles, в указанном порядке;
 *   3. версию для cache-busting берёт из filemtime, поэтому «?v=...» больше не нужно
 *      обновлять руками при каждой правке CSS;
 *   4. подключает внешний defer-резолвер item-иконок и передаёт ему cache-busted URL индекса.
 *
 * Как использовать (в <head> страницы):
 *   $headStyles = ['/public/css/game-start.css', '/public/css/chat.css'];
 *   require APP_ROOT . '/views/components/head-styles.php';
 *
 * Путь передаётся от корня сайта, как он попадёт в href (например /public/css/...).
 */

$headStyles = isset($headStyles) && is_array($headStyles) ? $headStyles : [];
$headStyles = array_merge(
    ['/public/css/tokens.css', '/public/css/base.css', '/public/css/components.css', '/public/css/icons.css', '/public/css/fonts.css'],
    $headStyles
);

/**
 * Ссылка на статический ассет с версией по времени изменения файла.
 * Нужна, чтобы после деплоя игрок не остался на старом JS/CSS из кэша:
 * ручные «?v=20260603-...» обновлять забывали, и правки не доезжали до браузера.
 */
if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        $file = APP_ROOT . $path;
        $version = is_file($file) ? (string) filemtime($file) : '';

        return $version !== '' ? $path . '?v=' . $version : $path;
    }
}

foreach ($headStyles as $headStyle) {
    $headStyle = (string) $headStyle;
    if ($headStyle === '') {
        continue;
    }

    $headStylePath = APP_ROOT . $headStyle;
    $headStyleVersion = is_file($headStylePath) ? (string) filemtime($headStylePath) : '';
    $headStyleHref = $headStyle . ($headStyleVersion !== '' ? '?v=' . $headStyleVersion : '');

    echo '  <link rel="stylesheet" href="' . \Pokemon8\View\View::e($headStyleHref) . '">' . PHP_EOL;
}

$itemIconRuntimeSrc = asset_url('/public/js/item-icon-runtime.js');
$itemIconIndexSrc = asset_url('/public/img/items/index.json');
echo '  <script defer src="' . \Pokemon8\View\View::e($itemIconRuntimeSrc) . '" id="itemIconRuntime" data-index-src="' . \Pokemon8\View\View::e($itemIconIndexSrc) . '"></script>' . PHP_EOL;
