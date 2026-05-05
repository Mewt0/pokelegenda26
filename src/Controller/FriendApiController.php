<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\FriendRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Throwable;

final class FriendApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private FriendRepository $friends
    ) {
    }

    public function status(Request $request): Response
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $targetId = max(0, (int) $request->input('user_id', '0'));
        if ($targetId <= 0 || !$this->friends->userExists($targetId)) {
            return $this->json(['ok' => false, 'message' => 'Игрок не найден.'], 404);
        }

        return $this->json([
            'ok' => true,
            'status' => $this->friends->status($userId, $targetId),
        ]);
    }

    public function requests(Request $request): Response
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json([
            'ok' => true,
            'requests' => array_map(static fn (array $row): array => [
                'id' => (int) $row['id_fz'],
                'user_id' => (int) $row['id_user'],
                'login' => (string) $row['login'],
                'time' => (int) $row['time'],
            ], $this->friends->incomingRequests($userId)),
        ]);
    }

    public function request(Request $request): Response
    {
        return $this->mutating($request, function (int $userId, int $targetId): array {
            if ($userId === $targetId) {
                return ['ok' => false, 'message' => 'Нельзя добавить в друзья самого себя.'];
            }

            if (!$this->friends->userExists($targetId)) {
                return ['ok' => false, 'message' => 'Игрок не найден.'];
            }

            $status = $this->friends->status($userId, $targetId);
            if ($status === 'friends') {
                return ['ok' => true, 'status' => 'friends', 'message' => 'Этот игрок уже у вас в друзьях.'];
            }

            if ($status === 'outgoing') {
                return ['ok' => true, 'status' => 'outgoing', 'message' => 'Заявка уже отправлена.'];
            }

            if ($status === 'incoming') {
                $this->friends->acceptRequest($userId, $targetId);
                return ['ok' => true, 'status' => 'friends', 'message' => 'Встречная заявка принята, игрок добавлен в друзья.'];
            }

            $this->friends->createRequest($userId, $targetId);
            return ['ok' => true, 'status' => 'outgoing', 'message' => 'Заявка в друзья отправлена.'];
        });
    }

    public function accept(Request $request): Response
    {
        return $this->mutating($request, function (int $userId, int $targetId): array {
            if (!$this->friends->incomingRequestExists($userId, $targetId)) {
                return ['ok' => false, 'message' => 'Входящая заявка не найдена.'];
            }

            $this->friends->acceptRequest($userId, $targetId);
            return ['ok' => true, 'status' => 'friends', 'message' => 'Игрок добавлен в друзья.'];
        });
    }

    public function decline(Request $request): Response
    {
        return $this->mutating($request, function (int $userId, int $targetId): array {
            if (!$this->friends->declineRequest($userId, $targetId)) {
                return ['ok' => false, 'message' => 'Входящая заявка не найдена.'];
            }

            return ['ok' => true, 'status' => 'none', 'message' => 'Заявка отклонена.'];
        });
    }

    public function remove(Request $request): Response
    {
        return $this->mutating($request, function (int $userId, int $targetId): array {
            if (!$this->friends->removeFriend($userId, $targetId)) {
                return ['ok' => false, 'message' => 'Игрок не найден в списке друзей.'];
            }

            return ['ok' => true, 'status' => 'none', 'message' => 'Игрок удалён из друзей.'];
        });
    }

    private function mutating(Request $request, callable $handler): Response
    {
        try {
            $userId = $this->currentUserId();
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            if (!$this->csrf->validate($request->input('_csrf'))) {
                return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела.'], 419);
            }

            $targetId = max(0, (int) $request->input('user_id', '0'));
            if ($targetId <= 0) {
                return $this->json(['ok' => false, 'message' => 'Игрок не выбран.'], 422);
            }

            return $this->json($handler($userId, $targetId));
        } catch (Throwable $e) {
            error_log('[FriendApiController] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Ошибка сервера при работе с друзьями.'], 500);
        }
    }

    private function currentUserId(): int
    {
        return (int) $this->session->get('id', 0);
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
