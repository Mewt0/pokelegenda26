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
        $messages = $this->chatRepository->findMessages($userId, $roomId, $afterId);

        return array_map(function ($msg) {
            return [
                'id'          => (int) $msg['id'],
                'author_id'   => (int) ($msg['resolved_author_id'] ?? $msg['author_id'] ?? 0),
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

    public function latestVisibleMessageId(int $userId, int $roomId): int
    {
        return $this->chatRepository->latestVisibleMessageId($userId, $roomId);
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

        if (str_starts_with($text, '/')) {
            return $this->handleModeratorCommand($userId, $login, $roomId, $text);
        }

        $mute = $this->chatRepository->activeMuteForUser($userId);
        if ($mute) {
            $until = (int) ($mute['expires_at'] ?? 0);
            $untilText = $until > 0 ? date('d.m.Y H:i', $until) : 'бессрочно';
            return [
                'ok' => false,
                'message' => 'Чат закрыт для вас до ' . $untilText . '. Причина: ' . (string) ($mute['reason'] ?? 'модерация'),
            ];
        }

        $lastSent = (int) $this->session->get('chat_last_sent_time', 0);
        if (time() - $lastSent < 2) {
            return ['ok' => false, 'message' => 'Вы пишете слишком быстро. Пожалуйста, подождите.'];
        }

        $lastText = (string) $this->session->get('chat_last_sent_text', '');
        if ($text === $lastText) {
            return ['ok' => false, 'message' => 'Вы уже отправили такое сообщение.'];
        }

        $tipe = (int) ($options['tipe'] ?? self::TIPE_ALL);
        $allowedTipes = [self::TIPE_ALL, self::TIPE_BATTLE, self::TIPE_TRADE, self::TIPE_CLAN];
        if (!in_array($tipe, $allowedTipes, true)) {
            $tipe = self::TIPE_ALL;
        }

        $userto = (int) ($options['userto'] ?? 0);
        $private = (int) ($options['private'] ?? 0) === 1 ? 1 : 0;

        if ($private === 1) {
            if ($userto <= 0) {
                return ['ok' => false, 'message' => 'Выберите получателя для личного сообщения.'];
            }
            if ($userto === $userId) {
                return ['ok' => false, 'message' => 'Вы не можете отправить личное сообщение самому себе.'];
            }
        } else {
            $userto = 0;
        }

        $ok = $this->chatRepository->addMessage([
            'author_id' => $userId,
            'author'    => $login,
            'userto'    => $userto,
            'private'   => $private,
            'text'      => $text,
            'tipe'      => $tipe,
            'room'      => $roomId,
        ]);

        if ($ok) {
            $this->session->put('chat_last_sent_time', time());
            $this->session->put('chat_last_sent_text', $text);
        }

        return $ok ? ['ok' => true] : ['ok' => false, 'message' => 'Ошибка базы данных.'];
    }

    public function sendSystemMessage(string $text, int $roomId = 0, int $tipe = self::TIPE_SYSTEM): void
    {
        $this->chatRepository->addMessage([
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
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function handleModeratorCommand(int $userId, string $login, int $roomId, string $text): array
    {
        $parts = preg_split('/\s+/', $text, 4) ?: [];
        $command = mb_strtolower(ltrim((string) ($parts[0] ?? ''), '/'));
        $commands = ['mute', 'unmute', 'ban', 'unban', 'warn'];
        if (!in_array($command, $commands, true)) {
            return ['ok' => false, 'message' => 'Неизвестная команда чата.'];
        }

        if (!$this->chatRepository->canModerate($userId)) {
            return ['ok' => false, 'message' => 'У вас нет прав модерации.'];
        }

        $targetToken = trim((string) ($parts[1] ?? ''));
        if ($targetToken === '') {
            return ['ok' => false, 'message' => 'Укажите игрока. Пример: /mute niga 15minut Нарушение правил.'];
        }

        $target = $this->chatRepository->findUserForModeration($targetToken);
        if (!$target) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        if ((int) $target['id'] === $userId && in_array($command, ['mute', 'ban'], true)) {
            return ['ok' => false, 'message' => 'Нельзя выдать наказание самому себе.'];
        }

        $durationToken = trim((string) ($parts[2] ?? ''));
        $reason = trim((string) ($parts[3] ?? ''));

        if (in_array($command, ['unmute', 'unban'], true)) {
            $reason = trim($durationToken . ' ' . $reason);
            $action = $command === 'unmute' ? 'mute' : 'ban';
            $count = $this->chatRepository->revokePunishments($userId, (int) $target['id'], $action, $reason);
            if ($command === 'unban') {
                $count += $this->chatRepository->unbanIpForUser($target);
            }
            $this->sendSystemMessage(sprintf(
                '%s снял %s с игрока %s. %s',
                $login,
                $command === 'unmute' ? 'мут' : 'бан',
                (string) $target['login'],
                $reason !== '' ? 'Причина: ' . $reason : ''
            ), $roomId);

            return ['ok' => true, 'message' => $count > 0 ? 'Наказание снято.' : 'Активное наказание не найдено.'];
        }

        if ($command === 'warn') {
            $reason = trim($durationToken . ' ' . $reason);
            if ($reason === '') {
                $reason = 'Предупреждение модератора.';
            }
            $this->chatRepository->createPunishment($userId, $target, 'warn', time(), $reason, 'chat', 0);
            $this->sendSystemMessage(sprintf('%s выдал предупреждение игроку %s. Причина: %s', $login, (string) $target['login'], $reason), $roomId);
            $this->sendSystemPrivateMessage(
                (int) $target['id'],
                sprintf('Вам выдано предупреждение модератором %s. Причина: %s', $login, $reason)
            );
            return ['ok' => true, 'message' => 'Предупреждение выдано.'];
        }

        $expiresAt = $this->parseDuration($durationToken);
        if ($expiresAt === null) {
            return ['ok' => false, 'message' => 'Укажите срок: 15minut, 1h, 2d или perm.'];
        }
        if ($reason === '') {
            $reason = 'Нарушение правил.';
        }

        $this->chatRepository->createPunishment(
            $userId,
            $target,
            $command,
            $expiresAt,
            $reason,
            $command === 'mute' ? 'chat' : 'game'
        );
        if ($command === 'ban') {
            $this->chatRepository->banIpForUser($target);
        }

        $untilText = $expiresAt > 0 ? date('d.m.Y H:i', $expiresAt) : 'бессрочно';
        $this->sendSystemMessage(sprintf(
            '%s выдал %s игроку %s до %s. Причина: %s',
            $login,
            $command === 'mute' ? 'мут' : 'бан',
            (string) $target['login'],
            $untilText,
            $reason
        ), $roomId);
        $this->sendSystemPrivateMessage(
            (int) $target['id'],
            sprintf(
                'Вы нарушили правила и получили %s от модератора %s до %s. Причина: %s',
                $command === 'mute' ? 'мут чата' : 'бан',
                $login,
                $untilText,
                $reason
            )
        );

        return ['ok' => true, 'message' => $command === 'mute' ? 'Мут выдан.' : 'Бан выдан.'];
    }

    private function sendSystemPrivateMessage(int $toUserId, string $text): void
    {
        if ($toUserId <= 0 || trim($text) === '') {
            return;
        }

        $this->chatRepository->addMessage([
            'author_id' => 0,
            'author'    => 'System',
            'userto'    => $toUserId,
            'private'   => 1,
            'text'      => $text,
            'tipe'      => self::TIPE_SYSTEM,
            'room'      => 0,
        ]);
    }

    private function parseDuration(string $duration): ?int
    {
        $duration = mb_strtolower(trim($duration));
        if ($duration === '') {
            return null;
        }
        if (in_array($duration, ['0', 'perm', 'perma', 'permanent', 'forever', 'навсегда', 'бессрочно'], true)) {
            return 0;
        }

        if (!preg_match('/^(\d+)\s*([a-zа-я]+)$/iu', $duration, $matches)) {
            return null;
        }

        $amount = max(1, (int) $matches[1]);
        $unit = mb_strtolower($matches[2]);
        $seconds = match (true) {
            in_array($unit, ['m', 'min', 'mins', 'minute', 'minutes', 'minut', 'мин', 'минута', 'минут', 'минуты'], true) => $amount * 60,
            in_array($unit, ['h', 'hr', 'hour', 'hours', 'час', 'часа', 'часов'], true) => $amount * 3600,
            in_array($unit, ['d', 'day', 'days', 'д', 'день', 'дня', 'дней'], true) => $amount * 86400,
            in_array($unit, ['w', 'week', 'weeks', 'н', 'неделя', 'недели', 'недель'], true) => $amount * 604800,
            default => 0,
        };

        return $seconds > 0 ? time() + $seconds : null;
    }
}
