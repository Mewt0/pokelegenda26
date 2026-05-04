<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\ChatService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

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
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $afterId = (int) $request->input('after_id', '0');

        // Получаем текущую комнату игрока
        $userState = $this->locationRepository.findUserState($userId);
        $roomId = (int) ($userState['buildmy'] ?? 1);

        $messages = $this->chatService.getMessages($userId, $roomId, $afterId);

        return $this->json([
            'ok'       => true,
            'messages' => $messages,
            'lastId'   => !empty($messages) ? end($messages)['id'] : $afterId,
        ]);
    }

    public function send(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела.'], 419);
        }

        $text = $request->input('text');
        $tipe = (int) $request->input('tipe', '1');
        $userto = (int) $request->input('userto', '0');
        $private = (int) $request->input('private', '0');

        $userState = $this->locationRepository.findUserState($userId);
        $roomId = (int) ($userState['buildmy'] ?? 1);
        $login = (string) ($userState['login'] ?? 'Unknown');

        $result = $this->chatService.sendMessage($userId, $login, $roomId, $text, [
            'tipe'    => $tipe,
            'userto'  => $userto,
            'private' => $private,
        ]);

        return $this->json($result);
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
