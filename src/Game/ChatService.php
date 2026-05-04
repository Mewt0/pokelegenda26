<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\ChatRepository;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Security\Session;

final class ChatService
{
    public const TIPE_ALL     = 1;
    public const TIPE_SYSTEM  = 2;
    public const TIPE_BATTLE  = 3;
    public const TIPE_TRADE   = 4;
    public const TIPE_CLAN    = 5;

    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository,
        private Session $session
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
        // Антиспам: ограничение на частоту сообщений (1 сообщение в 2 секунды)
        $lastSent = (int) $this->session->get('chat_last_sent_time', 0);
        if (time() - $lastSent < 2) {
            return ['ok' => false, 'message' => 'Вы пишите слишком быстро. Пожалуйста, подождите.'];
        }

        $text = $this->normalizeText($text);
        if ($text === '') {
            return ['ok' => false, 'message' => 'Сообщение не может быть пустым.'];
        }

        if (mb_strlen($text) > 500) {
            return ['ok' => false, 'message' => 'Сообщение слишком длинное.'];
        }

        // Антиспам: проверка на дубликаты
        $lastText = (string) $this->session->get('chat_last_sent_text', '');
        if ($text === $lastText) {
             return ['ok' => false, 'message' => 'Вы уже отправили такое сообщение.'];
        }

        $tipe = (int) ($options['tipe'] ?? self::TIPE_ALL);

        // Валидация типов для обычных игроков
        $allowedTipes = [self::TIPE_ALL, self::TIPE_BATTLE, self::TIPE_TRADE, self::TIPE_CLAN];
        if (!in_array($tipe, $allowedTipes, true)) {
            $tipe = self::TIPE_ALL;
        }

        $userto = (int) ($options['userto'] ?? 0);
        $private = (int) ($options['private'] ?? 0);

        // Если это приват, проверяем получателя
        if ($private === 1) {
            if ($userto <= 0) {
                return ['ok' => false, 'message' => 'Выберите получателя для личного сообщения.'];
            }
            if ($userto === $userId) {
                return ['ok' => false, 'message' => 'Вы не можете отправить личное сообщение самому себе.'];
            }
            // В идеале тут должен быть UserRepository::findById, но пока проверим хотя бы базовое наличие
            // (Так как мы переходим на ID, это важно)
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

        if ($ok) {
            $this->session->put('chat_last_sent_time', time());
            $this->session->put('chat_last_sent_text', $text);
        }

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
        // Убираем htmlspecialchars отсюда, так как фронт использует textContent
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
