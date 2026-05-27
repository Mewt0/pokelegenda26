<?php
declare(strict_types=1);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = trim((string) ($_GET['user'] ?? $_GET['login'] ?? ''));

$query = [];
if ($id > 0) {
    $query['id'] = $id;
} elseif ($user !== '') {
    $query['user'] = $user;
}

$target = '/game/profile';
if ($query !== []) {
    $target .= '?' . http_build_query($query);
}

header('Cache-Control: no-store');
header('Location: ' . $target, true, 302);
exit;
