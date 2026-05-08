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

    public function dashboard(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'dashboard' => $this->admin->dashboard()]);
    }

    public function lookups(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        $type = $request->input('type', '');
        $query = $request->input('q', '');
        if ($type !== '') {
            return $this->json([
                'ok' => true,
                'type' => $type,
                'query' => $query,
                'rows' => $this->admin->lookupByType($type, $query),
            ]);
        }

        return $this->json(['ok' => true, 'lookups' => $this->admin->lookups()]);
    }

    public function legacyMap(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        $rows = $this->admin->legacyModules($request->input('q'));
        $summary = [
            'total' => count($rows),
            'todo' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'TODO_REWRITE')),
            'partial' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'PARTIAL_NEW')),
            'done' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'DONE')),
        ];

        return $this->json([
            'ok' => true,
            'legacyModules' => $rows,
            'rows' => $rows,
            'summary' => $summary,
        ]);
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

    public function wildSlots(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'slots' => $this->admin->wildSlots($request->input('q'))]);
    }

    public function saveWildSlot(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveWildSlot($adminId, $request->post));
    }

    public function deleteWildSlot(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteWildSlot(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function users(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'users' => $this->admin->users($request->input('q'))]);
    }

    public function saveUser(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveUser($adminId, $request->post));
    }

    public function banUser(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->banUser($adminId, $request->post));
    }

    public function grantItem(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->grantItem($adminId, $request->post));
    }

    public function deleteItem(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteItem(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function marketItems(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'items' => $this->admin->marketItems($request->input('q'))]);
    }

    public function saveMarketItem(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveMarketItem($adminId, $request->post));
    }

    public function deleteMarketItem(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteMarketItem($adminId, (int) $request->input('item_id', '0')));
    }

    public function locations(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'locations' => $this->admin->locations($request->input('q'))]);
    }

    public function saveLocation(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveLocation($adminId, $request->post));
    }

    public function deleteLocation(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteLocation(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function pokemon(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $filters = [
            'login' => $request->input('login'),
            'user_id' => $request->input('user_id'),
            'pokemon_id' => $request->input('pokemon_id'),
            'base_id' => $request->input('base_id'),
            'name' => $request->input('name'),
            'level_min' => $request->input('level_min'),
            'level_max' => $request->input('level_max'),
            'training_stage' => $request->input('training_stage'),
            'training_stat' => $request->input('training_stat'),
            'shiny' => $request->input('shiny'),
            'active' => $request->input('active'),
            'held_item_id' => $request->input('held_item_id'),
        ];
        $query = $request->input('q');
        $total = $this->admin->playerPokemonTotal($query, $filters);
        $rows = $this->admin->playerPokemon($query, $perPage, $offset, $filters);

        return $this->json([
            'ok' => true,
            'rows' => $rows,
            'pokemon' => $rows,
            'pagination' => $this->pagination($page, $perPage, $total),
            'filters' => $filters,
            'summary' => ['total' => $total],
        ]);
    }

    public function savePokemon(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->savePlayerPokemon($adminId, $request->post));
    }

    public function grantPokemon(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->grantPokemon($adminId, $request->post));
    }

    public function deletePokemon(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deletePlayerPokemon(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function attacks(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 100);
        $query = $request->input('q');
        $filters = [
            'type' => $request->input('type'),
            'category' => $request->input('category'),
            'power_min' => $request->input('power_min'),
            'power_max' => $request->input('power_max'),
            'accuracy_min' => $request->input('accuracy_min'),
            'accuracy_max' => $request->input('accuracy_max'),
            'pp_min' => $request->input('pp_min'),
            'pp_max' => $request->input('pp_max'),
            'effect' => $request->input('effect'),
        ];
        $total = $this->admin->attacksTotal($query, $filters);
        $rows = $this->admin->attacks($query, $perPage, $offset, $filters);

        return $this->json([
            'ok' => true,
            'rows' => $rows,
            'attacks' => $rows,
            'pagination' => $this->pagination($page, $perPage, $total),
            'filters' => $filters,
            'summary' => ['total' => $total],
        ]);
    }

    public function saveAttack(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveAttack($adminId, $request->post));
    }

    public function saveAttackLearn(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveAttackLearn($adminId, $request->post));
    }

    public function deleteAttack(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteAttack(
            $adminId,
            (int) $request->input('atac_id', '0'),
            $request->input('confirm')
        ));
    }

    public function news(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'news' => $this->admin->news($request->input('q'))]);
    }

    public function saveNews(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveNews($adminId, $request->post));
    }

    public function deleteNews(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteNews(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function events(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'events' => $this->admin->events($request->input('q'))]);
    }

    public function saveEvent(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveEvent($adminId, $request->post));
    }

    public function deleteEvent(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteEvent(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function tournaments(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json([
            'ok' => true,
            'tournaments' => $this->admin->tournaments($request->input('q')),
        ]);
    }

    public function saveTournament(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveTournament($adminId, $request->post));
    }

    public function deleteTournament(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteTournament(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function saveTournamentParticipant(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveTournamentParticipant($adminId, $request->post));
    }

    public function medals(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json([
            'ok' => true,
            'medals' => $this->admin->medals($request->input('q')),
        ]);
    }

    public function saveMedal(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveMedal($adminId, $request->post));
    }

    public function deleteMedal(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->deleteMedal(
            $adminId,
            (int) $request->input('id', '0'),
            $request->input('confirm')
        ));
    }

    public function awardMedal(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->awardMedal($adminId, $request->post));
    }

    public function moderation(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'moderation' => $this->admin->moderation()]);
    }

    public function moderationAction(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->moderationAction($adminId, $request->post));
    }

    public function audit(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'audit' => $this->admin->auditRows()]);
    }

    public function settings(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'settings' => $this->admin->settings()]);
    }

    public function saveSettings(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->saveSettings($adminId, $request->post));
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

    private function mutate(Request $request, callable $callback): Response
    {
        $adminId = $this->authorizedAdminId();
        if ($adminId <= 0) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($callback($adminId));
    }

    private function pageParams(Request $request, int $defaultPerPage): array
    {
        $page = max(1, (int) $request->input('page', '1'));
        $perPage = max(10, min(200, (int) $request->input('per_page', (string) $defaultPerPage)));
        return [$page, $perPage, ($page - 1) * $perPage];
    }

    private function pagination(int $page, int $perPage, int $total): array
    {
        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'pages' => max(1, (int) ceil($total / max(1, $perPage))),
        ];
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
