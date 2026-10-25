<?php
declare(strict_types=1);
namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\Service\AdminGrantException;
use Pokemon8\Service\AdminItemGrantV2Service;

final class AdminGrantApiController
{
    public function __construct(private Session $session, private Csrf $csrf, private AdminRepository $admin,
        private AdminItemGrantV2Service $grants) {}

    public function handle(Request $request, string $action): Response
    {
        try {
            $adminId = (int) $this->session->get('id', 0);
            if (!$this->admin->canAccess($adminId)) {
                throw new AdminGrantException('forbidden', 403);
            }
            if ($request->method !== 'POST' || !in_array($action, ['preview', 'confirm'], true)) {
                throw new AdminGrantException('method_not_allowed', 405);
            }
            if (!$this->csrf->validate($request->input('_csrf'))) {
                throw new AdminGrantException('csrf', 419);
            }
            $input = $request->post;
            unset($input['_csrf']);
            $body = $action === 'preview' ? $this->grants->preview($adminId, $input) : $this->grants->confirm($adminId, $input);
            return $this->json($body, 200);
        } catch (AdminGrantException $e) {
            return $this->json(['ok' => false, 'error' => $e->errorCode], $e->httpStatus);
        }
    }

    private function json(array $body, int $status): Response
    {
        return new Response(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), $status,
            ['Content-Type' => 'application/json; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
