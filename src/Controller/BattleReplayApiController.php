<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\BattleReplayRepository;
use Pokemon8\Security\Session;

final class BattleReplayApiController
{
    public function __construct(
        private Session $session,
        private BattleReplayRepository $replays,
    ) {
    }

    public function show(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $battleId = (int) $request->input('battle_id', '0');
        $payload = $this->replays->replayForBattle($battleId, $userId, false);
        return $this->json($payload, !empty($payload['ok']) ? 200 : (($payload['error'] ?? '') === 'forbidden' ? 403 : 404));
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
