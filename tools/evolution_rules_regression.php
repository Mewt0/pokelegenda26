<?php
declare(strict_types=1);

/** No live database connection: targeted evolution selection regression. */
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/src/Support/Autoload.php';

final class EvolutionMemoryPDO extends PDO
{
    public array $rules = [];
    public bool $knowsAncientPower = false;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if (!str_starts_with(ltrim($query), 'SELECT')) { throw new RuntimeException('Writes forbidden in regression'); }
        return new EvolutionMemoryStatement($this, $query);
    }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $statement = $this->prepare($query);
        $statement->execute();
        return $statement;
    }
}
final class EvolutionMemoryStatement extends PDOStatement
{
    private array $params = [];
    public function __construct(private EvolutionMemoryPDO $db, private string $sql) {}
    public function execute(?array $params = null): bool { $this->params = $params ?? []; return true; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        if (str_contains($this->sql, 'FROM attac_power')) { return [246]; }
        if (str_contains($this->sql, 'FROM pokemon_evolution_rules')) {
            return array_values(array_filter($this->db->rules, fn(array $rule): bool =>
                $rule['from_base_id'] === $this->params['base'] && ($rule['enabled'] ?? 1) === 1));
        }
        throw new RuntimeException('Unexpected SQL: ' . $this->sql);
    }
    public function fetchColumn(int $column = 0): mixed
    {
        if (str_contains($this->sql, 'FROM attac_my_poke')) { return $this->db->knowsAncientPower ? 1 : false; }
        throw new RuntimeException('Unexpected fetchColumn');
    }
}

$db = new EvolutionMemoryPDO();
$hour = 13;
$repo = new PokemonEvolutionRepository($db, static function () use (&$hour): int { return $hour; });
$select = new ReflectionMethod($repo, 'conditionEvolutionTarget');
$rule = static fn(int $from, int $to, string $condition, int $level = 0): array => [
    'id' => $to, 'from_base_id' => $from, 'to_base_id' => $to, 'trigger_type' => 'condition',
    'level_required' => $level, 'item_id' => 0, 'condition_text' => $condition, 'enabled' => 1,
];
$count = 0;
$assert = static function (bool $ok, string $label) use (&$count): void {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $label); }
    $count++;
};
$pokemon = ['id' => 1, 'basenum' => 133, 'lvl' => 10, 'happy' => 100];
$db->rules = [$rule(133, 196, 'Дневное время + 100% счастья'), $rule(133, 197, 'Ночное время + 100% счастья')];
foreach ([0 => null, 12 => null, 13 => 196, 18 => 196, 19 => 197, 23 => 197] as $time => $expected) {
    $hour = $time;
    $result = $select->invoke($repo, $pokemon);
    $assert(($result['toBaseId'] ?? null) === $expected, 'Eevee hour boundary ' . $time);
}
foreach ([13, 19] as $time) {
    $hour = $time;
    $assert($select->invoke($repo, array_replace($pokemon, ['happy' => 99])) === null, '99 happiness refuses at ' . $time);
}
foreach ([406 => 315, 527 => 528] as $from => $target) {
    $db->rules = [$rule($from, $target, '100% счастья')];
    foreach ([0, 99, 100] as $happy) {
        $result = $select->invoke($repo, array_replace($pokemon, ['basenum' => $from, 'happy' => $happy]));
        $assert(($result['toBaseId'] ?? null) === ($happy === 100 ? $target : null), 'happiness-only boundary ' . $from . '/' . $happy);
    }
}
foreach ([114 => 465, 193 => 469, 221 => 473] as $from => $target) {
    $db->rules = [$rule($from, $target, 'Нужно знать Ancient Power')];
    foreach ([false, true] as $known) {
        $db->knowsAncientPower = $known;
        $result = $select->invoke($repo, array_replace($pokemon, ['basenum' => $from]));
        $assert(($result['toBaseId'] ?? null) === ($known ? $target : null), 'Ancient Power ' . $from . '/' . (int)$known);
    }
}
$db->rules = [$rule(133, 196, '50% счастья + Ancient Power')];
$db->knowsAncientPower = true;
$assert($select->invoke($repo, array_replace($pokemon, ['happy' => 49])) === null, 'combined condition requires happiness');
$assert(($select->invoke($repo, array_replace($pokemon, ['happy' => 50]))['toBaseId'] ?? 0) === 196, 'combined condition passes at threshold');
$db->knowsAncientPower = false;
$assert($select->invoke($repo, $pokemon) === null, 'combined condition also requires move');
foreach (['', 'Unknown requirement', 'Ancient Power + Unknown requirement', '101% счастья'] as $condition) {
    $db->knowsAncientPower = true;
    $db->rules = [$rule(133, 196, $condition)];
    $assert($select->invoke($repo, $pokemon) === null, 'unknown/invalid requirement refuses: ' . $condition);
}
$db->rules = [$rule(133, 196, '100% счастья', 11)];
$assert($select->invoke($repo, $pokemon) === null, 'condition minimum level refuses');
$assert(($select->invoke($repo, array_replace($pokemon, ['lvl' => 11]))['toBaseId'] ?? 0) === 196, 'condition minimum level boundary');
$assert($select->invoke($repo, array_replace($pokemon, ['id' => 0])) === null, 'invalid pokemon refuses');
$assert($select->invoke($repo, array_replace($pokemon, ['basenum' => 0])) === null, 'invalid species refuses');
foreach ([['Дневное время (13:00–18:59) + 100% счастья', 13, 196], ['Ночное время (19:00–23:59) + 100% счастья', 19, 197]] as [$text, $time, $target]) {
    $hour = $time;
    $db->rules = [$rule(133, $target, $text)];
    $assert(($select->invoke($repo, $pokemon)['toBaseId'] ?? 0) === $target, 'annotated server hours recognized');
}
$memoryInventory = new InventoryRepository($db, $repo);
$badge = new ReflectionMethod($memoryInventory, 'itemCategoryKey');
$assert($badge->invoke($memoryInventory, ['item_id' => 4000000000, 'name' => 'Новый предмет', 'dopolnen' => 'evolution_item']) === 'evolution', 'new dynamic ID badge follows marker');
$assert($badge->invoke($memoryInventory, ['item_id' => 71, 'name' => 'Новый предмет', 'target_enabled' => 1, 'effect_key' => 'evolution_item']) === 'evolution', 'alias badge follows enabled target');
$assert($badge->invoke($memoryInventory, ['item_id' => 71, 'name' => 'Новый предмет', 'target_enabled' => 0, 'effect_key' => 'evolution_item']) !== 'evolution', 'disabled target does not grant badge');
foreach ([80, 81, 82, 83, 86, 87, 89] as $heldId) {
    $assert($badge->invoke($memoryInventory, ['item_id' => $heldId, 'name' => 'Твёрдый камень', 'tittle' => 'Раньше предмет эволюции', 'target_enabled' => 1, 'effect_key' => 'equip_held']) !== 'evolution', 'held badge does not follow old ID or descriptive text ' . $heldId);
}
echo "Evolution regression: {$count}/{$count} passed; no live DB connection.\n";

// Optional post-migration check: SELECT only. The parent applies migrations after backup.
if (in_array('--verify-live', $argv, true) || in_array('--verify-filter-only', $argv, true)) {
    define('APP_ROOT', dirname(__DIR__));
    Env::load(APP_ROOT . '/.env');
    $live = Connection::make(require APP_ROOT . '/config/database.php');
    $liveRepo = new PokemonEvolutionRepository($live);
    $inventory = new InventoryRepository($live, $liveRepo);
    $categoryMethod = new ReflectionMethod($inventory, 'categorySqlCondition');
    foreach ([['evolution_item', 'evolution', true], ['evolution_item', 'other', false], ['0', 'evolution', false], ['0', 'other', true]] as [$marker, $category, $expected]) {
        $condition = $categoryMethod->invoke($inventory, $category);
        $stmt = $live->prepare('SELECT 1 FROM (SELECT 4000000000 AS item_id) iu CROSS JOIN (SELECT :marker AS dopolnen, "Новый предмет" AS name) i WHERE ' . $condition);
        $stmt->execute(['marker' => $marker]);
        $assert(($stmt->fetchColumn() !== false) === $expected, 'data-driven filter ' . $marker . '/' . $category);
    }
    if (in_array('--verify-filter-only', $argv, true)) {
        echo "Evolution regression with read-only SQL filter: {$count}/{$count} passed.\n";
        exit(0);
    }
    $inCategory = static function (int $itemId, string $category) use ($live, $inventory, $categoryMethod): bool {
        $condition = $categoryMethod->invoke($inventory, $category);
        $stmt = $live->prepare('SELECT i.id FROM (SELECT :item_id AS item_id) iu JOIN items i ON i.id=iu.item_id WHERE ' . $condition);
        $stmt->execute(['item_id' => $itemId]);
        return $stmt->fetchColumn() !== false;
    };
    foreach ([
        [80, 'Острый клык', 207, 472], [81, 'Острый коготь для эволюции', 215, 461],
        [82, 'Жуткая ткань', 356, 477], [83, 'Глубинная чешуя', 366, 368],
        [86, 'Магматор', 126, 467], [87, 'Электрайзер', 125, 466], [89, 'Протектор', 112, 464],
    ] as [$oldId, $name, $from, $to]) {
        $stmt = $live->prepare('SELECT i.id FROM items i JOIN item_target_rules t ON t.item_id=i.id WHERE i.name=:name AND i.dopolnen="evolution_item" AND t.enabled=1 AND t.effect_key="evolution_item"');
        $stmt->execute(['name' => $name]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $assert(count($ids) === 1, 'one evolution-only item: ' . $name);
        $itemId = (int)$ids[0];
        $assert($itemId !== $oldId, 'held ID is preserved: ' . $name);
        $assert(($liveRepo->itemEvolutionTarget($from, $itemId, 1)['toBaseId'] ?? 0) === $to, 'restored target: ' . $name);
        $assert($liveRepo->itemEvolutionTarget(1, $itemId, 100) === null, 'wrong species refuses: ' . $name);
        $assert($liveRepo->itemEvolutionTarget($from, $oldId, 100) === null, 'held ID no longer evolves: ' . $name);
        $assert($inCategory($itemId, 'evolution'), 'new evolution item appears in filter: ' . $name);
        $assert(!$inCategory($itemId, 'other'), 'new evolution item excluded from other: ' . $name);
        $assert(!$inCategory($oldId, 'evolution'), 'ordinary held item excluded from evolution: ' . $name);
        foreach ([[$itemId, true], [$oldId, false]] as [$badgeItemId, $expectedEvolution]) {
            $stmt = $live->prepare('SELECT i.*, i.id AS item_id, t.enabled AS target_enabled, t.effect_key FROM items i LEFT JOIN item_target_rules t ON t.item_id=i.id AND t.enabled=1 WHERE i.id=:item_id');
            $stmt->execute(['item_id' => $badgeItemId]);
            $assert(($badge->invoke($inventory, $stmt->fetch(PDO::FETCH_ASSOC)) === 'evolution') === $expectedEvolution, 'live badge consistent with category ' . $badgeItemId);
        }
    }
    foreach ([[281, 475], [361, 478]] as [$from, $to]) {
        $assert(($liveRepo->itemEvolutionTarget($from, 71, 1)['toBaseId'] ?? 0) === $to, 'Dawn alias target ' . $from);
    }
    foreach ([[30, 31], [33, 34], [35, 36], [39, 40], [300, 301], [517, 518]] as [$from, $to]) {
        $assert(($liveRepo->itemEvolutionTarget($from, 332, 1)['toBaseId'] ?? 0) === $to, 'Moon alias target ' . $from);
    }
    $assert($liveRepo->itemEvolutionTarget(440, 332, 100) === null, 'Moon alias does not evolve Happiny');
    $assert($liveRepo->itemEvolutionTarget(0, 332, 1) === null, 'invalid species item selection refuses');
    $assert($liveRepo->itemEvolutionTarget(30, 0, 1) === null, 'invalid item selection refuses');
    $assert($live->query('SELECT effect_key FROM item_target_rules WHERE item_id=81')->fetchColumn() === 'equip_held', 'ordinary Razor Claw remains equip_held');
    echo "Evolution regression including live read-only selection: {$count}/{$count} passed.\n";
}
