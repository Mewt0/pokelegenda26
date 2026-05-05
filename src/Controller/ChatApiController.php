<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\ChatService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Throwable;

final class ChatApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private ChatService $chatService,
        private LocationRepository $locationRepository
    ) {
    }

    public function messages(Request $request): Response
    {
        try {
            $userId = (int) $this->session->get('id', 0);
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            $afterId = max(0, (int) $request->input('after_id', '0'));
            $userState = $this->locationRepository->findUserState($userId);
            if ($userState === null) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            $roomId = (int) ($userState['buildmy'] ?? 1);
            if ($request->input('sync_only', '0') === '1') {
                return $this->json([
                    'ok' => true,
                    'messages' => [],
                    'lastId' => $this->chatService->latestVisibleMessageId($userId, $roomId),
                ]);
            }

            $messages = $this->chatService->getMessages($userId, $roomId, $afterId);

            return $this->json([
                'ok' => true,
                'messages' => $messages,
                'lastId' => !empty($messages) ? (int) end($messages)['id'] : $afterId,
            ]);
        } catch (Throwable $e) {
            error_log('[ChatApiController::messages] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Ошибка сервера при получении сообщений.'], 500);
        }
    }

    public function send(Request $request): Response
    {
        try {
            $userId = (int) $this->session->get('id', 0);
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            if (!$this->csrf->validate($request->input('_csrf'))) {
                return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела.'], 419);
            }

            $userState = $this->locationRepository->findUserState($userId);
            if ($userState === null) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            $result = $this->chatService->sendMessage(
                $userId,
                (string) ($userState['login'] ?? 'Unknown'),
                (int) ($userState['buildmy'] ?? 1),
                $request->input('text'),
                [
                    'tipe' => (int) $request->input('tipe', '1'),
                    'userto' => (int) $request->input('userto', '0'),
                    'private' => (int) $request->input('private', '0'),
                ]
            );

            return $this->json($result);
        } catch (Throwable $e) {
            error_log('[ChatApiController::send] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Ошибка сервера при отправке сообщения.'], 500);
        }
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
