<?php
if (function_exists('http_response_code')) http_response_code(410);
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><title>Legacy route removed</title>'
   . '<h1>Старый маршрут отключён</h1>'
   . '<p><code>events.php</code> больше не используется. События доступны на '
   . '<a href="/game/events">/game/events</a>.</p>';
exit;
