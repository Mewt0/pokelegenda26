<?php
// events.php - SSE поток событий для реального времени
session_start();
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
require_once ("include/function/globfanction.php");
date_default_timezone_set('Europe/Moscow');

if (empty($_SESSION['login']) || empty($_SESSION['password'])) {
  http_response_code(401);
  exit;
}

// Важно: отключаем сессию, чтобы не блокировать параллельные AJAX-запросы
session_write_close();

$db = db($config);

// Заголовки SSE
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Connection: keep-alive');
// Для Nginx: отключаем буферизацию, чтобы события приходили сразу
header('X-Accel-Buffering: no');

ignore_user_abort(true);
set_time_limit(0);

// Какую последнюю событий получил клиент
$lastId = 0;
if (isset($_SERVER['HTTP_LAST_EVENT_ID'])) {
  $lastId = (int)$_SERVER['HTTP_LAST_EVENT_ID'];
} elseif (isset($_GET['since'])) {
  $lastId = (int)$_GET['since'];
}

// Проверка пользователя (как в game.php)
$myrow = first('SELECT * FROM users WHERE login="%s" AND password="%s" AND activation=1', $_SESSION['login'], $_SESSION['password']);
if (!$myrow || empty($myrow['id'])) {
  http_response_code(401);
  exit;
}

$userId = (int)$myrow['id'];

// Функция отправки SSE-события
function sse_send($event, $id, $data) {
  // id: позволяет клиенту отслеживать последнее полученное событие
  if ($id) echo "id: {$id}\n";
  if ($event) echo "event: {$event}\n";
  echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
  @ob_flush(); @flush();
}

// Главный цикл long-run: держим соединение ~60 секунд, постоянно проверяя новые события
$started = time();
while (!connection_aborted() && (time() - $started) < 60) {
  // Выбираем новые события для этого пользователя
  $rows = all('SELECT id, type, payload_json 
               FROM events 
               WHERE user_id=%d AND id>%d 
               ORDER BY id ASC LIMIT 50', $userId, $lastId);

  if ($rows) {
    foreach ($rows as $r) {
      $payload = json_decode($r['payload_json'], true) ?: [];
      // Пример: разные типы событий
      // challenge - приглашение на бой
      // chat - новое сообщение в чате
      // battle_update - изменения в текущем бою
      $eventName = $r['type']; 
      $lastId = (int)$r['id'];
      sse_send($eventName, $lastId, $payload);
    }
  }

  // Отправляем пинг, чтобы клиент не разрывал соединение, даже если событий нет
  sse_send('ping', $lastId, ['t' => time()]);

  usleep(300000); // 300мс
}

// По окончании цикла соединение разрывается - клиент переподключится автоматически