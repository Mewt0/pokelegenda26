<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class AdminApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private AdminRepository $admin,
    ) {
    }

    public function overview(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json([
            'ok' => true,
            'overview' => $this->admin->overview(),
            'legacyModules' => $this->admin->legacyModules(),
        ]);
    }

    public function lookups(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'lookups' => $this->admin->lookups()]);
    }

    public function items(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json([
            'ok' => true,
            'items' => $this->admin->items($request->input('q'), (int) $request->input('limit', '60')),
        ]);
    }

    public function saveItem(Request $request): Response
    {
        $adminId = $this->authorizedAdminId();
        if ($adminId <= 0) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->admin->saveItem($adminId, $request->post));
    }

    public function dropRules(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'rules' => $this->admin->dropRules()]);
    }

    public function saveDropRule(Request $request): Response
    {
        $adminId = $this->authorizedAdminId();
        if ($adminId <= 0) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->admin->saveDropRule($adminId, $request->post));
    }

    public function deleteDropRule(Request $request): Response
    {
        $adminId = $this->authorizedAdminId();
        if ($adminId <= 0) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->admin->deleteDropRule($adminId, (int) $request->input('id', '0')));
    }

    private function authorized(): bool
    {
        return $this->authorizedAdminId() > 0;
    }

    private function authorizedAdminId(): int
    {
        $userId = (int) $this->session->get('id', 0);
        return $this->admin->canAccess($userId) ? $userId : 0;
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
