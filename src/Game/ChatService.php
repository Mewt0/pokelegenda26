<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\ChatRepository;
use Pokemon8\Repository\UserRepository;

final class ChatService
{
    public const TIPE_ALL     = 1;
    public const TIPE_SYSTEM  = 2;
    public const TIPE_BATTLE  = 3;
    public const TIPE_TRADE   = 4;
    public const TIPE_CLAN    = 5;

    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository
    ) {
    }

    public function getMessages(int $userId, int $roomId, int $afterId = 0): array
    {
        $messages = $this->chatRepository.findMessages($userId, $roomId, $afterId);

        return array_map(function ($msg) {
            return [
                'id'          => (int) $msg['id'],
                'author_id'   => (int) ($msg['author_id'] ?? 0),
                'author_name' => $msg['author_name'] ?? $msg['author'] ?? 'System',
                'to_id'       => (int) $msg['userto'],
                'to_name'     => $msg['to_name'] ?? '',
                'text'        => $msg['text'],
                'tipe'        => (int) $msg['tipe'],
                'private'     => (int) $msg['private'] === 1,
                'room'        => (int) $msg['room'],
                'time'        => (int) $msg['time'],
            ];
        }, $messages);
    }

    public function sendMessage(int $userId, string $login, int $roomId, string $text, array $options = []): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            return ['ok' => false, 'message' => 'Сообщение не может быть пустым.'];
        }

        if (mb_strlen($text) > 500) {
            return ['ok' => false, 'message' => 'Сообщение слишком длинное.'];
        }

        $tipe = (int) ($options['tipe'] ?? self::TIPE_ALL);
        $userto = (int) ($options['userto'] ?? 0);
        $private = (int) ($options['private'] ?? 0);

        // Если это приват, проверяем получателя
        if ($private === 1 && $userto > 0) {
            // Можно добавить проверку на существование пользователя
        }

        $data = [
            'author_id' => $userId,
            'author'    => $login,
            'userto'    => $userto,
            'private'   => $private,
            'text'      => $text,
            'tipe'      => $tipe,
            'room'      => $roomId,
        ];

        $ok = $this->chatRepository.addMessage($data);

        return $ok ? ['ok' => true] : ['ok' => false, 'message' => 'Ошибка базы данных.'];
    }

    public function sendSystemMessage(string $text, int $roomId = 0, int $tipe = self::TIPE_SYSTEM): void
    {
        $this->chatRepository.addMessage([
            'author_id' => 0,
            'author'    => 'System',
            'userto'    => 0,
            'private'   => 0,
            'text'      => $text,
            'tipe'      => $tipe,
            'room'      => $roomId,
        ]);
    }

    private function normalizeText(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
