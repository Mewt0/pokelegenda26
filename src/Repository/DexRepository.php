<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class DexRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function searchPokemon(string $query = '', int $limit = 1000): array
    {
        $limit = max(1, min(1500, $limit));
        $query = trim($query);

        $sql = 'SELECT p.id, p.Name, p.Element, p.SubElement, p.Code,
                       pb.title, pb.hp, pb.atk, pb.def, pb.satk, pb.sdef, pb.speed,
                       pb.evolution_lvl, pb.evolution_type
                  FROM pokemon p
             LEFT JOIN poke_base pb ON pb.id = p.id';

        if ($query !== '') {
            $sql .= ' WHERE p.Name LIKE :q_name OR pb.title LIKE :q_title OR p.Code LIKE :q_code OR p.id = :id';
        }

        $sql .= ' ORDER BY p.id ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        if ($query !== '') {
            $like = '%' . $query . '%';
            $stmt->bindValue(':q_name', $like);
            $stmt->bindValue(':q_title', $like);
            $stmt->bindValue(':q_code', $like);
            $stmt->bindValue(':id', ctype_digit($query) ? (int)$query : 0, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'pokemonSummary'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function pokemon(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, pb.title, pb.img, pb.hp AS base_hp, pb.atk AS base_atk, pb.def AS base_def,
                    pb.satk AS base_satk, pb.sdef AS base_sdef, pb.speed AS base_speed,
                    pb.exp AS base_exp, pb.evolution_lvl, pb.evolution_type, pb.evol_a
               FROM pokemon p
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE p.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $data = $this->pokemonSummary($row);
        $data['baseExp'] = (int)($row['Base'] ?? $row['base_exp'] ?? 0);
        $data['description'] = $this->pokemonDescription($id, (string)$data['name']);
        $data['info'] = [
            'generation' => $this->generationById($id),
            'category' => $this->pokemonMetaValue($id, ['category', 'class', 'species'], 'Pokémon'),
        ];
        $data['evolutions'] = $this->evolutionChain($id);
        $data['evolutionOptions'] = $this->evolutionOptions($id);
        $data['evolution'] = $this->evolutionTextFromChain($data['evolutions']);
        $data['learnset'] = $this->pokemonLearnset($id);
        $data['eggMoves'] = $this->pokemonEggMoves($id);
        $data['habitats'] = $this->pokemonHabitats($id);

        return $data;
    }

    public function searchAttacks(string $query = '', int $limit = 1200): array
    {
        $limit = max(1, min(1500, $limit));
        $query = trim($query);

        $select = 'SELECT atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
                          critic, priorety, titles, atac_tittle, tittle_effect, chans_dop, chans_effect';
        $from = ' FROM attac_power';

        if ($query === '') {
            $stmt = $this->db->prepare($select . $from . ' ORDER BY atac_id ASC LIMIT :limit');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $id = (int) (preg_replace('/\D+/', '', $query) ?: 0);
            $compact = '%' . mb_strtolower((string) preg_replace('/[^a-z0-9а-яё]+/iu', '', $query), 'UTF-8') . '%';
            $category = $this->categoryFromSearch($query);
            $stmt = $this->db->prepare(
                $select . $from .
                ' WHERE atac_name LIKE :q_name
                    OR atac_tip LIKE :q_type
                    OR titles LIKE :q_titles
                    OR atac_tittle LIKE :q_title
                    OR tittle_effect LIKE :q_effect
                    OR atac_id = :id
                    OR (:category_filter > 0 AND atac_categori = :category_value)
                    OR LOWER(REPLACE(REPLACE(REPLACE(atac_name, " ", ""), "-", ""), "_", "")) LIKE :compact
                  ORDER BY atac_id ASC LIMIT :limit'
            );
            $like = '%' . $query . '%';
            $stmt->bindValue(':q_name', $like);
            $stmt->bindValue(':q_type', $like);
            $stmt->bindValue(':q_titles', $like);
            $stmt->bindValue(':q_title', $like);
            $stmt->bindValue(':q_effect', $like);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':category_filter', $category, PDO::PARAM_INT);
            $stmt->bindValue(':category_value', $category, PDO::PARAM_INT);
            $stmt->bindValue(':compact', $compact);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }

        return array_map([$this, 'attackSummary'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    private function categoryFromSearch(string $query): int
    {
        $q = mb_strtolower(trim($query), 'UTF-8');
        if ($q === '') {
            return 0;
        }
        if (str_contains($q, 'физ') || str_contains($q, 'physical')) {
            return 1;
        }
        if (str_contains($q, 'спец') || str_contains($q, 'special')) {
            return 2;
        }
        if (str_contains($q, 'стат') || str_contains($q, 'status')) {
            return 3;
        }
        return 0;
    }

    public function attack(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM attac_power WHERE atac_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $data = $this->attackSummary($row);
        $shortDescription = trim((string)($row['atac_tittle'] ?: $row['titles'] ?: $row['tittle_effect'] ?: ''));
        $details = trim((string)($row['titles'] ?: $row['tittle_effect'] ?: $row['atac_tittle'] ?: ''));
        $data['description'] = $shortDescription;
        $data['details'] = $details;
        $data['effectText'] = trim((string)($row['tittle_effect'] ?: $row['titles'] ?: ''));
        $data['categoryName'] = $this->categoryName((int)($row['atac_categori'] ?? 0));
        $data['targetName'] = $this->targetName((int)($row['atac_goal'] ?? 1));
        $data['tags'] = $this->attackTags($row, $details . ' ' . $shortDescription);
        $data['flags'] = [
            'stati' => (int)($row['stati'] ?? 0),
            'attacEffecti' => (int)($row['attac_effecti'] ?? 0),
            'status' => (int)($row['atac_not'] ?? 0),
            'secondaryChance' => (int)($row['chans_dop'] ?? 0),
            'effectChance' => (int)($row['chans_effect'] ?? 0),
            'dopEffect' => (string)($row['dop_effect'] ?? ''),
        ];
        $data['statEffects'] = $this->attackStatEffects($id);
        $data['secondaryEffects'] = $this->attackSecondaryEffects($id, $data['flags']);
        $data['learnedBy'] = $this->attackLearnedBy($id);

        return $data;
    }

    private function pokemonSummary(array $row): array
    {
        $id = (int)($row['id'] ?? 0);
        $code = $this->pokemonCode($row, $id);
        $name = $this->cleanPokemonName((string)($row['title'] ?? ''), (string)($row['Name'] ?? 'Pokemon'), $code);

        return [
            'id' => $id,
            'number' => $code,
            'code' => $code,
            'name' => $name,
            'types' => $this->pokemonTypes($row),
            'sprites' => $this->pokemonSprites($code, $name),
            'stats' => [
                'hp' => (int)($row['base_hp'] ?? $row['hp'] ?? $row['HP'] ?? 0),
                'atk' => (int)($row['base_atk'] ?? $row['atk'] ?? $row['Attack'] ?? 0),
                'def' => (int)($row['base_def'] ?? $row['def'] ?? $row['Defense'] ?? 0),
                'spAtk' => (int)($row['base_satk'] ?? $row['satk'] ?? $row['spatk'] ?? 0),
                'spDef' => (int)($row['base_sdef'] ?? $row['sdef'] ?? $row['spdef'] ?? 0),
                'speed' => (int)($row['base_speed'] ?? $row['speed'] ?? $row['Speed'] ?? 0),
            ],
        ];
    }

    private function pokemonCode(array $row, int $id): string
    {
        $raw = trim((string)($row['Code'] ?? ''));
        if ($raw !== '') {
            return str_pad(preg_replace('/\D+/', '', $raw) ?: (string)$id, 3, '0', STR_PAD_LEFT);
        }
        return str_pad((string)$id, 3, '0', STR_PAD_LEFT);
    }

    private function cleanPokemonName(string $title, string $fallback, string $code): string
    {
        $name = trim($title);
        if ($name !== '') {
            $name = preg_replace('/^#?' . preg_quote($code, '/') . '\s*/', '', $name) ?: $name;
            $name = preg_replace('/^#?0*\d+\s*/', '', $name) ?: $name;
        }
        if (trim($name) === '') {
            $name = trim($fallback) ?: 'Pokemon';
        }
        return $name;
    }

    private function pokemonTypes(array $row): array
    {
        $types = [];
        foreach ([(string)($row['Element'] ?? ''), (string)($row['SubElement'] ?? '')] as $type) {
            $type = trim($type);
            if ($type !== '' && strtolower($type) !== 'none' && !in_array($type, $types, true)) {
                $types[] = $type;
            }
        }
        return $types ?: ['Normal'];
    }

    private function pokemonSprites(string $code, string $name = ''): array
    {
        $num = (string) max(0, (int) $code);
        $normal = $this->existingPublicPath('/Pok/normal/' . $num . '.png');
        $shiny = $this->existingPublicPath('/Pok/shine/' . $num . '.png');
        $fallbackNormal = $this->existingPublicPath('/Pok/pok/' . $code . '.gif');
        $fallbackShiny = $this->existingPublicPath('/Pok/shiny/' . $code . '.gif');
        $assetNormal = $this->pokemonAssetPath($name, false);
        $assetShiny = $this->pokemonAssetPath($name, true);

        return [
            'normal' => $normal ?: ($assetNormal ?: ($fallbackNormal ?: '/public/img/ui/dex/pokedex.png')),
            'shiny' => $shiny ?: ($assetShiny ?: ($assetNormal ?: ($fallbackShiny ?: '/public/img/ui/dex/pokedex.png'))),
            'fallbackNormal' => $fallbackNormal,
            'fallbackShiny' => $fallbackShiny,
        ];
    }

    private function existingPublicPath(string $path): string
    {
        if (!defined('APP_ROOT')) {
            return $path;
        }
        return is_file(APP_ROOT . $path) ? $path : '';
    }

    private function pokemonAssetPath(string $name, bool $small): string
    {
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $name));
        if ($slug === '') {
            return '';
        }
        $path = ($small ? '/public/img/pokemon/small/' : '/public/img/pokemon/art/') . $slug . '.png';
        if (defined('APP_ROOT') && !is_file(APP_ROOT . $path)) {
            return '';
        }
        return $path;
    }

    private function pokemonDescription(int $id, string $name): string
    {
        // Server DB may not contain a normalized description table yet.
        // Keep a clean fallback so the UI never shows a broken/empty block.
        $tryTables = [
            ['pokemon_description', 'poke_id', 'description'],
            ['pokedex', 'poke_id', 'description'],
            ['pokedex', 'id', 'description'],
        ];
        foreach ($tryTables as [$table, $idCol, $textCol]) {
            try {
                $stmt = $this->db->prepare("SELECT `$textCol` FROM `$table` WHERE `$idCol` = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $value = trim((string)($stmt->fetchColumn() ?: ''));
                if ($value !== '') {
                    return $value;
                }
            } catch (Throwable) {
                // Optional legacy table is absent. Ignore.
            }
        }
        return $name . ' — покемон, данные которого загружены из нового покедекса.';
    }

    private function pokemonMetaValue(int $id, array $columns, string $fallback): string
    {
        $tables = ['pokemon_meta', 'pokedex'];
        foreach ($tables as $table) {
            foreach ($columns as $column) {
                try {
                    $stmt = $this->db->prepare("SELECT `$column` FROM `$table` WHERE `poke_id` = :poke_id OR `id` = :row_id LIMIT 1");
                    $stmt->execute(['poke_id' => $id, 'row_id' => $id]);
                    $value = trim((string)($stmt->fetchColumn() ?: ''));
                    if ($value !== '') {
                        return $value;
                    }
                } catch (Throwable) {
                    // Optional table/column is absent.
                }
            }
        }
        return $fallback;
    }

    private function generationById(int $id): int
    {
        return match (true) {
            $id <= 151 => 1,
            $id <= 251 => 2,
            $id <= 386 => 3,
            $id <= 493 => 4,
            $id <= 649 => 5,
            $id <= 721 => 6,
            $id <= 809 => 7,
            $id <= 905 => 8,
            default => 9,
        };
    }

    private function evolutionChain(int $id): array
    {
        $root = $this->findEvolutionRoot($id);
        $chain = [];
        $current = $root;
        $previousLevel = null;
        $guard = 0;

        while ($current > 0 && $guard++ < 10) {
            $row = $this->basePokemonRow($current);
            if (!$row) {
                break;
            }
            $summary = $this->pokemonSummary($row);
            $summary['level'] = $previousLevel;
            $summary['selected'] = $current === $id;
            $chain[] = $summary;

            $next = (int)($row['evolution_type'] ?? 0);
            $previousLevel = (int)($row['evolution_lvl'] ?? 0) ?: null;
            if ($next <= 0 || $next === $current) {
                break;
            }
            $current = $next;
        }

        return $chain;
    }

    private function findEvolutionRoot(int $id): int
    {
        $current = $id;
        $guard = 0;
        while ($guard++ < 10) {
            $stmt = $this->db->prepare('SELECT id FROM poke_base WHERE evolution_type = :id LIMIT 1');
            $stmt->execute(['id' => $current]);
            $parent = (int)($stmt->fetchColumn() ?: 0);
            if ($parent <= 0 || $parent === $current) {
                break;
            }
            $current = $parent;
        }
        return $current;
    }

    private function basePokemonRow(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.Name, p.Element, p.SubElement, p.Code,
                    pb.title, pb.hp AS base_hp, pb.atk AS base_atk, pb.def AS base_def,
                    pb.satk AS base_satk, pb.sdef AS base_sdef, pb.speed AS base_speed,
                    pb.evolution_lvl, pb.evolution_type
               FROM pokemon p
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE p.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function evolutionTextFromChain(array $chain): array
    {
        if (count($chain) <= 1) {
            return ['next' => 0, 'level' => 0, 'text' => 'Эволюции нет или не добавлена.'];
        }
        $parts = [];
        foreach ($chain as $index => $evo) {
            $prefix = $index > 0 && !empty($evo['level']) ? 'Lv.' . (int)$evo['level'] . ' → ' : '';
            $parts[] = $prefix . '#' . ($evo['code'] ?? $evo['id']) . ' ' . ($evo['name'] ?? 'Pokemon');
        }
        return ['next' => (int)($chain[1]['id'] ?? 0), 'level' => (int)($chain[1]['level'] ?? 0), 'text' => implode(' / ', $parts)];
    }

    private function evolutionOptions(int $id): array
    {
        $rows = [];
        foreach ($this->levelEvolutionOptions($id) as $row) {
            $rows[] = $row;
        }
        foreach ($this->itemEvolutionMap() as $fromId => $targets) {
            foreach ($targets as $target) {
                if ($fromId === $id || (int) $target['to'] === $id) {
                    $rows[] = $this->buildEvolutionOption($fromId, (int) $target['to'], [
                        'kind' => 'item',
                        'itemId' => (int) $target['itemId'],
                        'itemName' => (string) $target['itemName'],
                        'condition' => (string) ($target['condition'] ?? ''),
                    ]);
                }
            }
        }
        foreach ($this->specialEvolutionMap() as $fromId => $targets) {
            foreach ($targets as $target) {
                if ($fromId === $id || (int) $target['to'] === $id) {
                    $rows[] = $this->buildEvolutionOption($fromId, (int) $target['to'], [
                        'kind' => 'condition',
                        'condition' => (string) $target['condition'],
                    ]);
                }
            }
        }

        $seen = [];
        $result = [];
        foreach ($rows as $row) {
            if ($row === null) {
                continue;
            }
            $key = (int) ($row['from']['id'] ?? 0) . ':' . (int) ($row['to']['id'] ?? 0) . ':' . (string) ($row['requirement']['kind'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $row['selected'] = (int) ($row['from']['id'] ?? 0) === $id || (int) ($row['to']['id'] ?? 0) === $id;
            $result[] = $row;
        }

        return $result;
    }

    private function levelEvolutionOptions(int $id): array
    {
        $rows = [];
        $stmt = $this->db->prepare(
            'SELECT id, evolution_type, evolution_lvl
               FROM poke_base
              WHERE (id = :source_id AND evolution_type > 0)
                 OR evolution_type = :target_id
              ORDER BY id ASC'
        );
        $stmt->execute(['source_id' => $id, 'target_id' => $id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $fromId = (int) ($row['id'] ?? 0);
            $toId = (int) ($row['evolution_type'] ?? 0);
            if ($fromId <= 0 || $toId <= 0 || $fromId === $toId) {
                continue;
            }
            $rows[] = $this->buildEvolutionOption($fromId, $toId, [
                'kind' => 'level',
                'level' => (int) ($row['evolution_lvl'] ?? 0),
                'condition' => ((int) ($row['evolution_lvl'] ?? 0) > 0 ? 'Уровень ' . (int) ($row['evolution_lvl'] ?? 0) : 'Уровневая эволюция'),
            ]);
        }
        return $rows;
    }

    private function buildEvolutionOption(int $fromId, int $toId, array $requirement): ?array
    {
        $from = $this->basePokemonRow($fromId);
        $to = $this->basePokemonRow($toId);
        if (!$from || !$to) {
            return null;
        }
        $fromSummary = $this->pokemonSummary($from);
        $toSummary = $this->pokemonSummary($to);
        if (($requirement['kind'] ?? '') === 'item') {
            $itemId = (int) ($requirement['itemId'] ?? 0);
            $requirement['itemIcon'] = $this->itemIconPath($itemId);
        }

        return [
            'from' => $fromSummary,
            'to' => $toSummary,
            'requirement' => $requirement,
        ];
    }

    private function itemIconPath(int $itemId): string
    {
        if ($itemId <= 0) {
            return '/public/img/ui/menu-inventory.png';
        }
        $indexed = $this->indexedItemIconPath($itemId);
        if ($indexed !== '') {
            return $indexed;
        }
        $public = '/public/img/items/' . $itemId . '.png';
        if ($this->existingPublicPath($public) !== '') {
            return $public;
        }
        $legacy = '/img/items/' . $itemId . '.png';
        if ($this->existingPublicPath($legacy) !== '') {
            return $legacy;
        }
        return '/public/img/ui/menu-inventory.png';
    }

    private function indexedItemIconPath(int $itemId): string
    {
        static $index = null;
        if ($index === null) {
            $index = [];
            if (defined('APP_ROOT')) {
                $path = APP_ROOT . '/public/img/items/index.json';
                if (is_file($path)) {
                    $decoded = json_decode((string) file_get_contents($path), true);
                    if (is_array($decoded)) {
                        $index = $decoded;
                    }
                }
            }
        }

        $file = (string) ($index[(string) $itemId] ?? '');
        if ($file === '') {
            return '';
        }
        $public = '/public/img/items/' . basename($file);
        return $this->existingPublicPath($public);
    }

    private function itemEvolutionMap(): array
    {
        return [
            25 => [['to' => 26, 'itemId' => 40, 'itemName' => 'Громовой камень']],
            30 => [['to' => 31, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            33 => [['to' => 34, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            35 => [['to' => 36, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            37 => [['to' => 38, 'itemId' => 41, 'itemName' => 'Огненный камень']],
            39 => [['to' => 40, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            44 => [
                ['to' => 45, 'itemId' => 43, 'itemName' => 'Лиственный камень'],
                ['to' => 182, 'itemId' => 6, 'itemName' => 'Солнечный камень'],
            ],
            58 => [['to' => 59, 'itemId' => 41, 'itemName' => 'Огненный камень']],
            61 => [['to' => 62, 'itemId' => 42, 'itemName' => 'Водный камень']],
            70 => [['to' => 71, 'itemId' => 43, 'itemName' => 'Лиственный камень']],
            90 => [['to' => 91, 'itemId' => 42, 'itemName' => 'Водный камень']],
            102 => [['to' => 103, 'itemId' => 43, 'itemName' => 'Лиственный камень']],
            120 => [['to' => 121, 'itemId' => 42, 'itemName' => 'Водный камень']],
            133 => [
                ['to' => 134, 'itemId' => 42, 'itemName' => 'Водный камень'],
                ['to' => 135, 'itemId' => 40, 'itemName' => 'Громовой камень'],
                ['to' => 136, 'itemId' => 41, 'itemName' => 'Огненный камень'],
            ],
            271 => [['to' => 272, 'itemId' => 42, 'itemName' => 'Водный камень']],
            274 => [['to' => 275, 'itemId' => 43, 'itemName' => 'Лиственный камень']],
            300 => [['to' => 301, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            315 => [['to' => 407, 'itemId' => 8, 'itemName' => 'Светящийся камень']],
            511 => [['to' => 512, 'itemId' => 43, 'itemName' => 'Лиственный камень']],
            513 => [['to' => 514, 'itemId' => 41, 'itemName' => 'Огненный камень']],
            515 => [['to' => 516, 'itemId' => 42, 'itemName' => 'Водный камень']],
            517 => [['to' => 518, 'itemId' => 44, 'itemName' => 'Лунный камень']],
            603 => [['to' => 604, 'itemId' => 40, 'itemName' => 'Громовой камень']],
        ];
    }

    private function specialEvolutionMap(): array
    {
        return [
            133 => [
                ['to' => 196, 'condition' => 'Дневное время + 100% счастья'],
                ['to' => 197, 'condition' => 'Ночное время + 100% счастья'],
            ],
            193 => [['to' => 469, 'condition' => '50% счастья + Ancient Power']],
            220 => [['to' => 221, 'condition' => 'Уровень 33']],
            221 => [['to' => 473, 'condition' => '50% счастья + Ancient Power']],
            406 => [['to' => 315, 'condition' => '100% счастья']],
            527 => [['to' => 528, 'condition' => '100% счастья']],
        ];
    }

    private function pokemonLearnset(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atc_lvl AS level,
                    aw.atac_id AS id,
                    aw.atac_name AS name,
                    aw.atac_tip AS type,
                    aw.atac_categori AS category,
                    aw.atac_power AS power,
                    aw.atac_accuracy AS accuracy,
                    aw.atac_pp AS pp
               FROM attac_poke ap
         INNER JOIN attac_power aw ON aw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :id
              ORDER BY ap.atc_lvl ASC, ap.atac_id ASC'
        );
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['categoryName'] = $this->categoryName((int)($row['category'] ?? 0));
        }
        return $rows;
    }

    private function pokemonEggMoves(int $id): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT aw.atac_id AS id,
                        aw.atac_name AS name,
                        aw.atac_tip AS type,
                        aw.atac_categori AS category,
                        aw.atac_power AS power,
                        aw.atac_accuracy AS accuracy,
                        aw.atac_pp AS pp
                   FROM attac_egg ae
             INNER JOIN attac_power aw ON aw.atac_id = ae.atac_id
                  WHERE ae.poke_base_id = :id
                  ORDER BY aw.atac_id ASC'
            );
            $stmt->execute(['id' => $id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }

        foreach ($rows as &$row) {
            $row['categoryName'] = $this->categoryName((int)($row['category'] ?? 0));
        }
        unset($row);

        return $rows;
    }

    private function pokemonHabitats(int $id): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT b.id, b.title, pb.lvl, pb.chance
                   FROM pokebuild pb
             INNER JOIN build b ON b.id = pb.building
                  WHERE pb.baseid = :id AND pb.poimka = 1
                  ORDER BY b.id ASC
                  LIMIT 80'
            );
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function attackSummary(array $row): array
    {
        return [
            'id' => (int)($row['atac_id'] ?? $row['id'] ?? 0),
            'name' => (string)($row['atac_name'] ?? 'Move'),
            'type' => (string)($row['atac_tip'] ?? 'Normal'),
            'category' => (int)($row['atac_categori'] ?? 1),
            'categoryName' => $this->categoryName((int)($row['atac_categori'] ?? 1)),
            'pp' => (int)($row['atac_pp'] ?? 0),
            'power' => (int)($row['atac_power'] ?? 0),
            'accuracy' => (int)($row['atac_accuracy'] ?? 0),
            'priority' => (int)($row['priorety'] ?? 0),
            'crit' => (string)($row['critic'] ?? '3'),
            'short' => (string)($row['titles'] ?? $row['atac_tittle'] ?? $row['tittle_effect'] ?? ''),
            'secondaryChance' => (int)($row['chans_dop'] ?? 0),
        ];
    }

    private function attackLearnedBy(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, COALESCE(NULLIF(p.Name, ""), NULLIF(pb.title, "")) AS name, p.Code AS code, ap.atc_lvl AS level
               FROM attac_poke ap
         INNER JOIN pokemon p ON p.id = ap.poke_base_id
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE ap.atac_id = :id
              ORDER BY ap.atc_lvl ASC, p.id ASC
              LIMIT 160'
        );
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $code = $this->pokemonCode(['Code' => $row['code'] ?? ''], (int)($row['id'] ?? 0));
            $row['code'] = $code;
            $row['sprite'] = $this->pokemonSprites($code, (string)($row['name'] ?? ''))['normal'];
        }
        unset($row);

        return $rows;
    }

    private function attackStatEffects(int $id): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM stat_attak WHERE id_atk = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            $row = false;
        }
        if (!$row) {
            return [];
        }

        $effects = [];
        $fields = [
            'atc' => 'Атака',
            'satc' => 'Спец. атака',
            'def' => 'Защита',
            'sdef' => 'Спец. защита',
            'speed' => 'Скорость',
            'acc' => 'Ловкость',
            'accuracy' => 'Точность',
        ];
        foreach ($fields as $field => $label) {
            $value = (int)($row[$field] ?? 0);
            if ($value > 0 && in_array((string)($row['tip'] ?? ''), ['plus', 'minus'], true)) {
                $effects[] = ['target' => 'self', 'kind' => (string)$row['tip'], 'stat' => $label, 'label' => 'Себе: ' . $label, 'value' => ((string)$row['tip'] === 'minus' ? '-' : '+') . $value, 'description' => $label . ' пользователя изменяется на ' . $value . ' ступ.'];
            }
            $valueB = (int)($row[$field . '_b'] ?? 0);
            if ($valueB > 0 && in_array((string)($row['tip_b'] ?? ''), ['plus', 'minus'], true)) {
                $effects[] = ['target' => 'enemy', 'kind' => (string)$row['tip_b'], 'stat' => $label, 'label' => 'Враг: ' . $label, 'value' => ((string)$row['tip_b'] === 'minus' ? '-' : '+') . $valueB, 'description' => $label . ' цели изменяется на ' . $valueB . ' ступ.'];
            }
        }

        return $effects;
    }

    private function attackSecondaryEffects(int $id, array $flags): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM attac_dop WHERE id_attc = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            $row = false;
        }
        if (!$row) {
            return [];
        }

        $chance = (int)($flags['secondaryChance'] ?? 0);
        if ($chance <= 0) {
            $chance = (int)($flags['effectChance'] ?? 0);
        }
        $setting = (int)($row['setting'] ?? 0);
        $effects = [];

        if ($setting === 1 && (int)($row['dop_effc'] ?? 0) > 0) {
            $statusId = (int)$row['dop_effc'];
            if ($chance > 0) {
                $name = $this->statusName($statusId);
                $effects[] = [
                    'label' => $name,
                    'chance' => $chance . '%',
                    'description' => 'Есть шанс наложить статус «' . $name . '» на цель.',
                ];
            }
        } elseif ($setting === 2 || $setting === 3) {
            $kindRaw = (string)($row['tip_s'] ?? '');
            $kind = $kindRaw === 'plus' ? '+' : '-';
            $target = $setting === 3 ? 'себе' : 'врагу';
            foreach (['atc' => 'Атака', 'satc' => 'Спец. атака', 'def' => 'Защита', 'sdef' => 'Спец. защита', 'speed' => 'Скорость', 'acc' => 'Ловкость', 'accuracy' => 'Точность'] as $field => $label) {
                $value = (int)($row[$field] ?? 0);
                if ($value > 0) {
                    $effects[] = [
                        'label' => $label,
                        'chance' => $chance > 0 ? $chance . '%' : '—',
                        'description' => 'Изменяет ' . mb_strtolower($label, 'UTF-8') . ' ' . $target . ' на ' . $kind . $value . ' ступ.',
                    ];
                }
            }
        }

        return $effects;
    }

    private function attackTags(array $row, string $text): array
    {
        $lower = mb_strtolower($text, 'UTF-8');
        $name = mb_strtolower((string)($row['atac_name'] ?? ''), 'UTF-8');
        $category = (int)($row['atac_categori'] ?? 0);
        $dopEffect = (string)($row['dop_effect'] ?? '');
        $cool1 = (int)($row['cool_attak'] ?? 0);
        $cool2 = (int)($row['cool_attak2'] ?? 0);

        // В серверной БД нет отдельной нормализованной таблицы меток атак.
        // Поэтому сначала пытаемся брать смысл из описания/legacy flags,
        // но оставляем результат безопасным: это только отображение в инфодексе, не боевая математика.
        $contact = $category === 1
            && !str_contains($lower, 'не имеет контакта')
            && !str_contains($lower, 'без контакта')
            && !str_contains($lower, 'дистанц')
            && !str_contains($lower, 'волной')
            && !str_contains($lower, 'луч')
            && !str_contains($lower, 'порош')
            && !str_contains($lower, 'спорами')
            && !str_contains($lower, 'звук')
            && !str_contains($lower, 'песк');

        $blocked = !str_contains($lower, 'не блокируется')
            && !str_contains($lower, 'игнорирует защит')
            && $category !== 3;

        $reflectable = $category === 3
            || str_contains($lower, 'понижает')
            || str_contains($lower, 'усыпляет')
            || str_contains($lower, 'парализ')
            || str_contains($lower, 'отрав')
            || str_contains($lower, 'замешатель')
            || str_contains($lower, 'спутан');

        return [
            'contact' => $contact,
            'blocked' => $blocked,
            'reflectable' => $reflectable,
            'multiHit' => $cool1 > 1 || $cool2 > 1 || str_contains($lower, '2-5') || str_contains($lower, 'два раза') || str_contains($name, 'double'),
            'twoTurn' => $dopEffect === 'dopropusk' || str_contains($lower, 'двухход') || str_contains($lower, 'заряжается'),
            'recoil' => str_contains($lower, 'отдач') || str_contains($lower, 'наносит') && str_contains($lower, 'использующему'),
        ];
    }

    private function statusName(int $statusId): string
    {
        if ($statusId <= 0) {
            return '';
        }
        try {
            $stmt = $this->db->prepare('SELECT tittle_status FROM `status` WHERE id_status = :id LIMIT 1');
            $stmt->execute(['id' => $statusId]);
            $name = trim((string)($stmt->fetchColumn() ?: ''));
            if ($name !== '') {
                return $name;
            }
        } catch (Throwable) {
            // Optional in some local test DBs.
        }
        return 'Статус #' . $statusId;
    }

    private function categoryName(int $category): string
    {
        return match ($category) {
            1 => 'Физическая',
            2 => 'Специальная',
            default => 'Статусная',
        };
    }

    private function targetName(int $target): string
    {
        return match ($target) {
            1 => 'Противник',
            2 => 'На себя',
            3 => 'Вся вражеская команда',
            4 => 'Вся команда пользователя',
            5 => 'Всё поле',
            default => 'Цель #' . $target,
        };
    }
}
