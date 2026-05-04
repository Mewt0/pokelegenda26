<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\ChatRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class ChatApiController
{
    private const CHANNELS = [
        'all' => 1,
        'trade' => 2,
        'battle' => 3,
        'clan' => 4,
    ];

    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private ChatRepository $chat,
    ) {
    }

    public function messages(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $prefs = $this->chat->getUserChatPreferences($userId);
        $messages = $this->chat->listMessages(
            $userId,
            (int) ($prefs['buildmy'] ?? 1),
            (int) ($prefs['mychat'] ?? 1)
        );

        $formatted = array_map(function (array $m) {
            return [
                'id' => (int) $m['id'],
                'from_user_id' => (int) ($m['from_user_id'] ?? 0),
                'from_name' => (string) ($m['from_name'] ?: $m['author']),
                'to_user_id' => (int) $m['userto'],
                'to_name' => (string) ($m['to_name'] ?? ''),
                'text' => $this->toUtf8((string) $m['text']),
                'channel' => $this->tipeToChannel((int) $m['tipe'], (int) $m['private']),
                'created_at' => date('Y-m-d H:i:s', (int) $m['ts']),
                'ts' => (int) $m['ts'],
                'private' => (int) $m['private'] === 1,
            ];
        }, $messages);

        return $this->json([
            'ok' => true,
            'rows' => $formatted,
            'scope' => (int) ($prefs['mychat'] ?? 1) === 2 ? 'room' : 'all',
        ]);
    }

    public function send(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        $userLogin = (string) $this->session->get('login', '');
        if ($userId <= 0 || $userLogin === '') {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        $text = trim((string) $request->input('text', ''));
        if ($text === '') {
            return $this->json(['ok' => false, 'error' => 'empty', 'message' => 'Введите текст сообщения.'], 422);
        }

        $channel = (string) $request->input('channel', 'all');
        $recipientId = (int) $request->input('to_user_id', 0);
        $isPrivate = $channel === 'private' || $recipientId > 0;

        if ($isPrivate) {
            if ($recipientId <= 0) {
                return $this->json(['ok' => false, 'error' => 'no_recipient', 'message' => 'Получатель не выбран.'], 422);
            }
            if ($recipientId === $userId) {
                return $this->json(['ok' => false, 'error' => 'self_pm', 'message' => 'Нельзя писать самому себе.'], 422);
            }
            // In real app we might check if user is online, but for now we just allow sending.
        }

        $prefs = $this->chat->getUserChatPreferences($userId);
        $type = self::CHANNELS[$channel] ?? 1;
        $room = (int) ($prefs['buildmy'] ?? 1);

        // Convert back to CP1251 for legacy DB storage if necessary,
        // but modern part uses UTF-8.
        // We'll assume DB is now UTF-8 or Connection.php handles it.
        $ok = $this->chat->saveMessage(
            $userLogin,
            $recipientId,
            $isPrivate ? 1 : 0,
            time(),
            $text,
            $type,
            $room
        );

        if (!$ok) {
            return $this->json(['ok' => false, 'error' => 'db', 'message' => 'Ошибка базы данных.'], 500);
        }

        return $this->json(['ok' => true]);
    }

    public function scope(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf'], 419);
        }

        $scopeStr = (string) $request->input('scope', 'all');
        $scopeVal = $scopeStr === 'room' ? 2 : 1;

        $this->chat->updateUserChatScope($userId, $scopeVal);

        return $this->json(['ok' => true, 'scope' => $scopeStr]);
    }

    private function tipeToChannel(int $tipe, int $private): string
    {
        if ($private === 1) {
            return 'private';
        }

        return match ($tipe) {
            2 => 'trade',
            3 => 'battle',
            4 => 'clan',
            default => 'all',
        };
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    private function toUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }
}
