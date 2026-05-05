<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class FriendRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function userExists(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE id = :id AND activation = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);

        return (bool) $stmt->fetchColumn();
    }

    public function areFriends(int $userId, int $friendId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM friends WHERE id_user = :user_id AND id_my_friend = :friend_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'friend_id' => $friendId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function outgoingRequestExists(int $userId, int $targetId): bool
    {
        return $this->requestExists($userId, $targetId);
    }

    public function incomingRequestExists(int $userId, int $fromId): bool
    {
        return $this->requestExists($fromId, $userId);
    }

    public function createRequest(int $userId, int $targetId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO friends_zayv (id_user, id_user_to, code, time)
             VALUES (:user_id, :target_id, :code, :time)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'target_id' => $targetId,
            'code' => bin2hex(random_bytes(16)),
            'time' => time(),
        ]);
    }

    public function acceptRequest(int $userId, int $fromId): void
    {
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare(
                'DELETE FROM friends_zayv WHERE id_user = :from_id AND id_user_to = :user_id'
            );
            $delete->execute([
                'from_id' => $fromId,
                'user_id' => $userId,
            ]);

            $this->insertFriendPair($userId, $fromId);
            $this->insertFriendPair($fromId, $userId);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function declineRequest(int $userId, int $fromId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM friends_zayv WHERE id_user = :from_id AND id_user_to = :user_id'
        );
        $stmt->execute([
            'from_id' => $fromId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function removeFriend(int $userId, int $friendId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM friends
              WHERE (id_user = :user_id AND id_my_friend = :friend_id)
                 OR (id_user = :friend_id_reverse AND id_my_friend = :user_id_reverse)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'friend_id_reverse' => $friendId,
            'user_id_reverse' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function incomingRequests(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.id_fz, f.id_user, f.id_user_to, f.time, u.login
               FROM friends_zayv f
               JOIN users u ON u.id = f.id_user
              WHERE f.id_user_to = :user_id
              ORDER BY f.time DESC
              LIMIT 50'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function status(int $userId, int $targetId): string
    {
        if ($userId === $targetId) {
            return 'self';
        }

        if ($this->areFriends($userId, $targetId)) {
            return 'friends';
        }

        if ($this->outgoingRequestExists($userId, $targetId)) {
            return 'outgoing';
        }

        if ($this->incomingRequestExists($userId, $targetId)) {
            return 'incoming';
        }

        return 'none';
    }

    private function requestExists(int $fromId, int $toId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM friends_zayv WHERE id_user = :from_id AND id_user_to = :to_id LIMIT 1'
        );
        $stmt->execute([
            'from_id' => $fromId,
            'to_id' => $toId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    private function insertFriendPair(int $userId, int $friendId): void
    {
        if ($this->areFriends($userId, $friendId)) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO friends (id_user, id_my_friend, tip) VALUES (:user_id, :friend_id, 1)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'friend_id' => $friendId,
        ]);
    }
}
