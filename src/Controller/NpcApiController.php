<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\NpcDialogService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class NpcApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private NpcDialogService $npcs,
    ) {
    }

    public function show(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->npcs->open(
            $userId,
            (int) $request->input('location_id'),
            $this->npcParams($request)
        ));
    }

    public function action(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->npcs->action(
            $userId,
            (int) $request->input('location_id'),
            $this->npcParams($request),
            $request->input('action')
        ));
    }

    private function npcParams(Request $request): array
    {
        $params = [];
        foreach (['npc', 'quest_npc', 'do', 'do_npc'] as $key) {
            $value = $request->input($key);
            if ($value !== '') {
                $params[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', $value) ?: '';
            }
        }

        return $params;
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
