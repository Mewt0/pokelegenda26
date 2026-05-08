<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\MessageRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class MessageApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private MessageRepository $messages,
    ) {
    }

    public function inbox(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json([
            'ok' => true,
            'messages' => $this->messages->inboxForUser($userId, 100),
            'sent' => $this->messages->sentForUser($userId, 100),
            'archive' => $this->messages->archiveForUser($userId, 100),
            'unread' => $this->messages->unreadCount($userId),
        ]);
    }

    public function send(Request $request): Response
    {
        return $this->mutate($request, function (int $userId) use ($request): array {
            return $this->messages->send(
                $userId,
                $request->input('recipient'),
                $request->input('subject'),
                $request->input('text')
            );
        });
    }

    public function markRead(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->messages->markRead(
            $userId,
            (int) $request->input('id', '0')
        ));
    }

    public function delete(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->messages->delete(
            $userId,
            (int) $request->input('id', '0')
        ));
    }

    private function mutate(Request $request, callable $callback): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($callback($userId));
    }

    private function userId(): int
    {
        return (int) $this->session->get('id', 0);
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
