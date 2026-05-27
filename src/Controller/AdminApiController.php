<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Repository\BattleReplayRepository;
use Pokemon8\Repository\BossRepository;
use Pokemon8\Repository\BugReportRepository;
use Pokemon8\Repository\EconomyGuardRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class AdminApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private AdminRepository $admin,
        private ?BossRepository $bosses = null,
        private ?BattleReplayRepository $replays = null,
        private ?EconomyGuardRepository $economyGuard = null,
        private ?BugReportRepository $bugReports = null,
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

    public function gmCenter(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'gmCenter' => $this->admin->gmCenter()]);
    }

    public function qaSeedTools(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'qaSeedTools' => $this->admin->qaSeedTools()]);
    }

    public function runQaSeedTool(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->qaSeedRun($adminId, $request->post));
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

    public function bosses(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if ($this->bosses === null) {
            return $this->json(['ok' => true, 'bosses' => []]);
        }

        return $this->json(['ok' => true, 'bosses' => $this->bosses->adminBosses($request->input('q'))]);
    }

    public function saveBoss(Request $request): Response
    {
        return $this->mutate($request, function (int $adminId) use ($request): array {
            if ($this->bosses === null) {
                return ['ok' => false, 'message' => 'Сервис боссов не подключен.'];
            }
            return $this->bosses->saveBoss($adminId, $request->post);
        });
    }

    public function deleteBoss(Request $request): Response
    {
        return $this->mutate($request, function (int $adminId) use ($request): array {
            if ($this->bosses === null) {
                return ['ok' => false, 'message' => 'Сервис боссов не подключен.'];
            }
            return $this->bosses->deleteBoss(
                $adminId,
                (int) $request->input('id', '0'),
                $request->input('confirm')
            );
        });
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

    public function commissionDashboard(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true, 'dashboard' => $this->admin->commissionDashboard($this->commissionFilters($request))]);
    }

    public function commissionLots(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->admin->commissionLots($request->input('q'), $perPage, $offset, $this->commissionFilters($request));
        return $this->json([
            'ok' => true,
            'rows' => $result['rows'],
            'lots' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
            'dashboard' => $this->admin->commissionDashboard($this->commissionFilters($request)),
        ]);
    }

    public function commissionLogs(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->admin->commissionLogs($request->input('q'), $perPage, $offset, $this->commissionFilters($request));
        return $this->json([
            'ok' => true,
            'rows' => $result['rows'],
            'logs' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
            'dashboard' => $this->admin->commissionDashboard($this->commissionFilters($request)),
        ]);
    }

    public function commissionPriceHistory(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json(['ok' => true] + $this->admin->commissionPriceHistory($this->commissionFilters($request)));
    }

    public function commissionReturns(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->admin->commissionReturns($request->input('q'), $perPage, $offset, $this->commissionFilters($request));
        return $this->json([
            'ok' => true,
            'rows' => $result['rows'],
            'returns' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
        ]);
    }

    public function reviewCommissionRisk(Request $request): Response
    {
        return $this->mutate($request, fn (int $adminId) => $this->admin->reviewCommissionRisk($adminId, $request->post));
    }

    public function economyGuardAlerts(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if ($this->economyGuard === null) {
            return $this->json(['ok' => false, 'error' => 'economy_guard_unavailable'], 503);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->economyGuard->alerts([
            'q' => $request->input('q'),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'severity' => $request->input('severity'),
            'user_id' => (int) $request->input('user_id', '0'),
        ], $perPage, $offset);

        return $this->json([
            'ok' => true,
            'alerts' => $result['rows'],
            'rows' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
        ]);
    }

    public function economyGuardScan(Request $request): Response
    {
        return $this->mutate($request, function (int $adminId) use ($request): array {
            if ($this->economyGuard === null) {
                return ['ok' => false, 'message' => 'Economy Guard unavailable.'];
            }
            $dryRun = (string) ($request->post['dry_run'] ?? '0') === '1';
            $limit = max(10, min(1000, (int) ($request->post['limit'] ?? 200)));
            return ['ok' => true, 'summary' => $this->economyGuard->scan($dryRun, $limit)];
        });
    }

    public function economyGuardReview(Request $request): Response
    {
        return $this->mutate($request, function (int $adminId) use ($request): array {
            if ($this->economyGuard === null) {
                return ['ok' => false, 'message' => 'Economy Guard unavailable.'];
            }
            return $this->economyGuard->review(
                $adminId,
                (int) ($request->post['alert_id'] ?? 0),
                (string) ($request->post['status'] ?? 'reviewed'),
                (string) ($request->post['note'] ?? '')
            );
        });
    }

    public function battleReplays(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if ($this->replays === null) {
            return $this->json([
                'ok' => true,
                'rows' => [],
                'replays' => [],
                'pagination' => $this->pagination(1, 80, 0),
                'dashboard' => ['installed' => false],
            ]);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->replays->adminList($request->input('q'), $perPage, $offset);
        return $this->json([
            'ok' => true,
            'rows' => $result['rows'],
            'replays' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
            'dashboard' => $this->replays->adminDashboard(),
        ]);
    }

    public function battleReplayView(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if ($this->replays === null) {
            return $this->json(['ok' => false, 'message' => 'Battle Replay repository is not configured.'], 503);
        }

        $battleId = (int) $request->input('battle_id', '0');
        $payload = $this->replays->replayForBattle($battleId, $this->authorizedAdminId(), true);
        return $this->json($payload, !empty($payload['ok']) ? 200 : 404);
    }

    public function bugReports(Request $request): Response
    {
        if (!$this->authorized()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if ($this->bugReports === null) {
            return $this->json([
                'ok' => true,
                'reports' => [],
                'rows' => [],
                'pagination' => $this->pagination(1, 80, 0),
                'dashboard' => ['ready' => false],
            ]);
        }

        [$page, $perPage, $offset] = $this->pageParams($request, 80);
        $result = $this->bugReports->adminList($request->input('q'), $perPage, $offset, $this->bugReportFilters($request));
        return $this->json([
            'ok' => true,
            'reports' => $result['rows'],
            'rows' => $result['rows'],
            'pagination' => $this->pagination($page, $perPage, (int) $result['total']),
            'dashboard' => $this->bugReports->adminSummary(),
        ]);
    }

    public function updateBugReportStatus(Request $request): Response
    {
        return $this->mutate($request, function (int $adminId) use ($request): array {
            if ($this->bugReports === null) {
                return ['ok' => false, 'message' => 'Bug Reporter repository is not configured.'];
            }
            return $this->bugReports->updateStatus($adminId, $request->post);
        });
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

    private function commissionFilters(Request $request): array
    {
        $keys = [
            'period', 'date_from', 'date_to', 'status', 'object_type', 'category', 'seller', 'buyer',
            'seller_id', 'buyer_id', 'object_id', 'lot_id', 'action', 'legacy', 'price_min', 'price_max',
            'system_only', 'risky', 'sort', 'q',
        ];
        $filters = [];
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }
        return $filters;
    }

    private function bugReportFilters(Request $request): array
    {
        $keys = ['status', 'severity', 'user', 'battle_id', 'date_from', 'date_to', 'q'];
        $filters = [];
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }
        return $filters;
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
