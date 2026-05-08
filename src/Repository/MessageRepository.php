<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class MessageRepository
{
    public function __construct(private PDO $db)
    {
    }

    /** @return list<array<string,mixed>> */
    public function inboxForUser(int $userId, int $limit = 50): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT s.id, s.users, s.inputusers, s.tema, s.text,
                    IF(COALESCE(ms.read_at, 0) > 0, 0, s.active) AS active,
                    s.date,
                    u.login AS sender_login
               FROM sends s
               LEFT JOIN users u ON u.id = s.inputusers
               LEFT JOIN mail_message_state ms ON ms.message_id = s.id AND ms.user_id = :state_user AND ms.folder = "inbox"
              WHERE s.users = :mail_user
                AND COALESCE(ms.archived_at, 0) = 0
                AND COALESCE(ms.deleted_at, 0) = 0
              ORDER BY s.id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':state_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':mail_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** @return list<array<string,mixed>> */
    public function sentForUser(int $userId, int $limit = 50): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT s.id, s.users, s.inputusers, s.tema, s.text, s.active, s.date,
                    u.login AS recipient_login
               FROM sends s
               LEFT JOIN users u ON u.id = s.users
               LEFT JOIN mail_message_state ms ON ms.message_id = s.id AND ms.user_id = :state_user AND ms.folder = "sent"
              WHERE s.inputusers = :mail_user
                AND COALESCE(ms.archived_at, 0) = 0
                AND COALESCE(ms.deleted_at, 0) = 0
              ORDER BY s.id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':state_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':mail_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** @return list<array<string,mixed>> */
    public function archiveForUser(int $userId, int $limit = 50): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT s.id, s.users, s.inputusers, s.tema, s.text,
                    IF(COALESCE(ms.read_at, 0) > 0, 0, s.active) AS active,
                    s.date, ms.folder, ms.archived_at, ms.deleted_at,
                    sender.login AS sender_login,
                    recipient.login AS recipient_login
               FROM mail_message_state ms
               INNER JOIN sends s ON s.id = ms.message_id
               LEFT JOIN users sender ON sender.id = s.inputusers
               LEFT JOIN users recipient ON recipient.id = s.users
              WHERE ms.user_id = :user
                AND (COALESCE(ms.archived_at, 0) > 0 OR COALESCE(ms.deleted_at, 0) > 0)
              ORDER BY GREATEST(COALESCE(ms.archived_at, 0), COALESCE(ms.deleted_at, 0), s.id) DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function send(int $senderId, string $recipient, string $subject, string $text): array
    {
        $recipient = trim($recipient);
        $subject = trim(strip_tags($subject));
        $text = trim(strip_tags($text));

        if ($senderId <= 0) {
            return ['ok' => false, 'message' => 'Нужно войти в игру.'];
        }
        if ($recipient === '') {
            return ['ok' => false, 'message' => 'Укажи получателя.'];
        }
        if ($subject === '') {
            return ['ok' => false, 'message' => 'Укажи тему письма.'];
        }
        if ($text === '') {
            return ['ok' => false, 'message' => 'Напиши текст письма.'];
        }
        if (mb_strlen($subject) > 120) {
            return ['ok' => false, 'message' => 'Тема слишком длинная.'];
        }
        if (mb_strlen($text) > 5000) {
            return ['ok' => false, 'message' => 'Письмо слишком длинное.'];
        }

        $target = $this->resolveRecipient($recipient);
        if (!$target) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        $targetId = (int) $target['id'];
        if ($targetId === $senderId) {
            return ['ok' => false, 'message' => 'Нельзя отправить письмо самому себе.'];
        }

        $id = $this->nextId('sends', 'id');
        $stmt = $this->db->prepare(
            'INSERT INTO sends (id, users, text, inputusers, tema, active, date)
             VALUES (:id, :users, :text, :inputusers, :tema, 1, CURDATE())'
        );
        $stmt->execute([
            'id' => $id,
            'users' => $targetId,
            'text' => $text,
            'inputusers' => $senderId,
            'tema' => $subject,
        ]);

        return [
            'ok' => true,
            'message' => 'Письмо отправлено.',
            'id' => $id,
            'recipient' => ['id' => $targetId, 'login' => (string) $target['login']],
        ];
    }

    public function markRead(int $userId, int $messageId): array
    {
        if ($userId <= 0 || $messageId <= 0) {
            return ['ok' => false, 'message' => 'Письмо не найдено.'];
        }

        if (!$this->messageBelongsTo($userId, $messageId, 'inbox')) {
            return ['ok' => false, 'message' => 'Письмо не найдено.'];
        }

        $this->upsertState($messageId, $userId, 'inbox', ['read_at' => time()]);
        $stmt = $this->db->prepare('UPDATE sends SET active = 0 WHERE id = :id AND users = :user LIMIT 1');
        $stmt->execute(['id' => $messageId, 'user' => $userId]);

        return ['ok' => true, 'message' => 'Письмо отмечено прочитанным.'];
    }

    public function delete(int $userId, int $messageId): array
    {
        if ($userId <= 0 || $messageId <= 0) {
            return ['ok' => false, 'message' => 'Письмо не найдено.'];
        }

        $folder = $this->messageBelongsTo($userId, $messageId, 'inbox') ? 'inbox' : '';
        if ($folder === '' && $this->messageBelongsTo($userId, $messageId, 'sent')) {
            $folder = 'sent';
        }
        if ($folder === '') {
            return ['ok' => false, 'message' => 'Письмо не найдено.'];
        }

        $this->upsertState($messageId, $userId, $folder, ['archived_at' => time()]);

        return ['ok' => true, 'message' => 'Письмо перемещено в архив.'];
    }

    public function unreadCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM sends s
               LEFT JOIN mail_message_state ms ON ms.message_id = s.id AND ms.user_id = :state_user AND ms.folder = "inbox"
              WHERE s.users = :mail_user
                AND s.active = 1
                AND COALESCE(ms.read_at, 0) = 0
                AND COALESCE(ms.archived_at, 0) = 0
                AND COALESCE(ms.deleted_at, 0) = 0'
        );
        $stmt->execute(['state_user' => $userId, 'mail_user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function messageBelongsTo(int $userId, int $messageId, string $folder): bool
    {
        $column = $folder === 'sent' ? 'inputusers' : 'users';
        $stmt = $this->db->prepare(sprintf('SELECT 1 FROM sends WHERE id = :id AND %s = :user LIMIT 1', $column));
        $stmt->execute(['id' => $messageId, 'user' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    /** @param array<string,int> $values */
    private function upsertState(int $messageId, int $userId, string $folder, array $values): void
    {
        $now = time();
        $readAt = (int) ($values['read_at'] ?? 0);
        $archivedAt = (int) ($values['archived_at'] ?? 0);
        $deletedAt = (int) ($values['deleted_at'] ?? 0);

        $stmt = $this->db->prepare(
            'INSERT INTO mail_message_state (message_id, user_id, folder, read_at, archived_at, deleted_at, created_at, updated_at)
             VALUES (:message, :user, :folder, :read_at, :archived_at, :deleted_at, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE
                read_at = IF(:read_at_update > 0, :read_at_update, read_at),
                archived_at = IF(:archived_at_update > 0, :archived_at_update, archived_at),
                deleted_at = IF(:deleted_at_update > 0, :deleted_at_update, deleted_at),
                updated_at = :updated_at_update'
        );
        $stmt->execute([
            'message' => $messageId,
            'user' => $userId,
            'folder' => $folder,
            'read_at' => $readAt,
            'archived_at' => $archivedAt,
            'deleted_at' => $deletedAt,
            'created_at' => $now,
            'updated_at' => $now,
            'read_at_update' => $readAt,
            'archived_at_update' => $archivedAt,
            'deleted_at_update' => $deletedAt,
            'updated_at_update' => $now,
        ]);
    }

    /** @return array<string,mixed> */
    private function resolveRecipient(string $recipient): array
    {
        if (ctype_digit($recipient)) {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) $recipient]);
        } else {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE login = :login LIMIT 1');
            $stmt->execute(['login' => $recipient]);
        }

        $row = $stmt->fetch();
        return is_array($row) ? $row : [];
    }

    private function nextId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }

        return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
    }
}
