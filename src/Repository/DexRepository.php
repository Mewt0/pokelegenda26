<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class DexRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function searchPokemon(string $query = '', int $limit = 60): array
    {
        $limit = max(1, min(150, $limit));
        $query = trim($query);

        $sql = 'SELECT p.id, p.Name, pb.title, p.Element, p.SubElement, p.HP, p.Attack, p.Defense, p.Speed,
                       p.spatk, p.spdef, p.NextEvo, p.Evolve, p.Code, pb.evolution_lvl, pb.evolution_type
                  FROM pokemon p
             LEFT JOIN poke_base pb ON pb.id = p.id';

        if ($query !== '') {
            $sql .= ' WHERE p.Name LIKE :q OR pb.title LIKE :q OR p.Code LIKE :q OR p.id = :id';
        }
        $sql .= ' ORDER BY p.id ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        if ($query !== '') {
            $like = '%' . $query . '%';
            $stmt->bindValue(':q', $like);
            $stmt->bindValue(':id', ctype_digit($query) ? (int) $query : 0, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'pokemonSummary'], $stmt->fetchAll() ?: []);
    }

    public function pokemon(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT p.*, pb.title, pb.img, pb.evolution_lvl, pb.evolution_type, pb.evol_a, pb.exp AS base_exp
               FROM pokemon p
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE p.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $data = $this->pokemonSummary($row);
        $data['baseExp'] = (int) ($row['Base'] ?? $row['base_exp'] ?? 0);
        $data['evolution'] = $this->evolutionInfo($row);
        $data['learnset'] = $this->pokemonLearnset($id);
        $data['eggMoves'] = $this->pokemonEggMoves($id);
        $data['habitats'] = $this->pokemonHabitats($id);
        return $data;
    }

    public function searchAttacks(string $query = '', int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $query = trim($query);
        if ($query === '') {
            $stmt = $this->db->prepare(
                'SELECT atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy, critic, priorety, titles, atac_tittle, tittle_effect
                   FROM attac_power
                  ORDER BY atac_id ASC
                  LIMIT :limit'
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $like = '%' . $query . '%';
            $stmt = $this->db->prepare(
                'SELECT atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy, critic, priorety, titles, atac_tittle, tittle_effect
                   FROM attac_power
                  WHERE atac_name LIKE :q OR atac_tip LIKE :q OR titles LIKE :q OR atac_id = :id
                  ORDER BY atac_id ASC
                  LIMIT :limit'
            );
            $stmt->bindValue(':q', $like);
            $stmt->bindValue(':id', ctype_digit($query) ? (int) $query : 0, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }

        return array_map([$this, 'attackSummary'], $stmt->fetchAll() ?: []);
    }

    public function attack(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM attac_power WHERE atac_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $data = $this->attackSummary($row);
        $data['description'] = (string) ($row['titles'] ?: $row['atac_tittle'] ?: $row['tittle_effect'] ?: '');
        $data['categoryName'] = $this->categoryName((int) ($row['atac_categori'] ?? 0));
        $data['targetName'] = $this->targetName((int) ($row['atac_goal'] ?? 1));
        $data['flags'] = [
            'stati' => (int) ($row['stati'] ?? 0),
            'attacEffecti' => (int) ($row['attac_effecti'] ?? 0),
            'status' => (int) ($row['atac_not'] ?? 0),
            'secondaryChance' => (int) ($row['chans_dop'] ?? 0),
            'effectChance' => (int) ($row['chans_effect'] ?? 0),
            'dopEffect' => (string) ($row['dop_effect'] ?? ''),
        ];
        $data['statEffects'] = $this->attackStatEffects($id);
        $data['secondaryEffects'] = $this->attackSecondaryEffects($id, $data['flags']);
        $data['learnedBy'] = $this->attackLearnedBy($id);
        return $data;
    }

    private function pokemonSummary(array $row): array
    {
        $id = (int) ($row['id'] ?? 0);
        $name = trim((string) ($row['title'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($row['Name'] ?? 'Pokemon'));
        }
        return [
            'id' => $id,
            'code' => (string) ($row['Code'] ?? str_pad((string) $id, 3, '0', STR_PAD_LEFT)),
            'name' => $name,
            'types' => array_values(array_filter([(string) ($row['Element'] ?? 'Normal'), (string) ($row['SubElement'] ?? '')], fn ($v) => trim($v) !== '' && strtolower(trim($v)) !== 'none')),
            'stats' => [
                'hp' => (int) ($row['HP'] ?? 0),
                'atk' => (int) ($row['Attack'] ?? 0),
                'def' => (int) ($row['Defense'] ?? 0),
                'spAtk' => (int) ($row['spatk'] ?? 0),
                'spDef' => (int) ($row['spdef'] ?? 0),
                'speed' => (int) ($row['Speed'] ?? 0),
            ],
        ];
    }

    private function attackSummary(array $row): array
    {
        return [
            'id' => (int) ($row['atac_id'] ?? $row['id'] ?? 0),
            'name' => (string) ($row['atac_name'] ?? 'Move'),
            'type' => (string) ($row['atac_tip'] ?? 'Normal'),
            'category' => (int) ($row['atac_categori'] ?? 1),
            'categoryName' => $this->categoryName((int) ($row['atac_categori'] ?? 1)),
            'pp' => (int) ($row['atac_pp'] ?? 0),
            'power' => (int) ($row['atac_power'] ?? 0),
            'accuracy' => (int) ($row['atac_accuracy'] ?? 0),
            'priority' => (int) ($row['priorety'] ?? 0),
            'crit' => (string) ($row['critic'] ?? '3'),
            'short' => (string) ($row['titles'] ?? $row['atac_tittle'] ?? $row['tittle_effect'] ?? ''),
        ];
    }

    private function pokemonLearnset(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atc_lvl AS level, aw.atac_id AS id, aw.atac_name AS name, aw.atac_tip AS type, aw.atac_categori AS category, aw.atac_power AS power, aw.atac_accuracy AS accuracy, aw.atac_pp AS pp
               FROM attac_poke ap
         INNER JOIN attac_power aw ON aw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :id
              ORDER BY ap.atc_lvl ASC, ap.atac_id ASC'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll() ?: [];
    }

    private function pokemonEggMoves(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT aw.atac_id AS id, aw.atac_name AS name, aw.atac_tip AS type
               FROM attac_egg ae
         INNER JOIN attac_power aw ON aw.atac_id = ae.atac_id
              WHERE ae.poke_base_id = :id
              ORDER BY aw.atac_id ASC'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll() ?: [];
    }

    private function pokemonHabitats(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.id, b.title
               FROM pokebuild pb
         INNER JOIN build b ON b.id = pb.building
              WHERE pb.baseid = :id AND pb.poimka = 1
              ORDER BY b.id ASC
              LIMIT 80'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll() ?: [];
    }

    private function attackLearnedBy(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, COALESCE(NULLIF(pb.title, ""), p.Name) AS name, ap.atc_lvl AS level
               FROM attac_poke ap
         INNER JOIN pokemon p ON p.id = ap.poke_base_id
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE ap.atac_id = :id
              ORDER BY ap.atc_lvl ASC, p.id ASC
              LIMIT 120'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll() ?: [];
    }

    private function attackStatEffects(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM stat_attak WHERE id_atk = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }
        $effects = [];
        $fields = [
            'atc' => 'Атака', 'satc' => 'Спец. атака', 'def' => 'Защита', 'sdef' => 'Спец. защита', 'speed' => 'Скорость', 'acc' => 'Ловкость', 'accuracy' => 'Точность',
        ];
        foreach ($fields as $field => $label) {
            $value = (int) ($row[$field] ?? 0);
            if ($value > 0 && in_array((string) ($row['tip'] ?? ''), ['plus', 'minus'], true)) {
                $effects[] = ['target' => 'self', 'kind' => (string) $row['tip'], 'stat' => $label, 'value' => $value];
            }
            $valueB = (int) ($row[$field . '_b'] ?? 0);
            if ($valueB > 0 && in_array((string) ($row['tip_b'] ?? ''), ['plus', 'minus'], true)) {
                $effects[] = ['target' => 'enemy', 'kind' => (string) $row['tip_b'], 'stat' => $label, 'value' => $valueB];
            }
        }
        return $effects;
    }

    private function attackSecondaryEffects(int $id, array $flags): array
    {
        $stmt = $this->db->prepare('SELECT * FROM attac_dop WHERE id_attc = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $chance = (int) ($flags['secondaryChance'] ?? 0);
        if ($chance <= 0) {
            $chance = (int) ($flags['effectChance'] ?? 0);
        }
        $setting = (int) ($row['setting'] ?? 0);
        $desc = [];
        if ($setting === 1 && (int) ($row['dop_effc'] ?? 0) > 0) {
            $desc[] = 'Статус #' . (int) $row['dop_effc'] . ($chance > 0 ? ' — шанс ' . $chance . '%' : '');
        } elseif ($setting === 2 || $setting === 3) {
            $kind = (string) ($row['tip_s'] ?? '') === 'plus' ? '+' : '-';
            $target = $setting === 3 ? 'себе' : 'врагу';
            foreach (['atc' => 'Атака', 'satc' => 'Спец. атака', 'def' => 'Защита', 'sdef' => 'Спец. защита', 'speed' => 'Скорость', 'acc' => 'Ловкость', 'accuracy' => 'Точность'] as $field => $label) {
                $value = (int) ($row[$field] ?? 0);
                if ($value > 0) {
                    $desc[] = $target . ': ' . $label . ' ' . $kind . $value . ($chance > 0 ? ' — шанс ' . $chance . '%' : '');
                }
            }
        }
        return array_map(fn ($text) => ['description' => $text], $desc);
    }

    private function evolutionInfo(array $row): array
    {
        $nextId = (int) ($row['evolution_type'] ?? $row['NextEvo'] ?? 0);
        $level = (int) ($row['evolution_lvl'] ?? $row['Evolve'] ?? 0);
        if ($nextId <= 0) {
            return ['next' => 0, 'level' => 0, 'text' => 'Эволюции нет или не добавлена.'];
        }
        $stmt = $this->db->prepare('SELECT COALESCE(NULLIF(pb.title, ""), p.Name) AS name FROM pokemon p LEFT JOIN poke_base pb ON pb.id = p.id WHERE p.id = :id LIMIT 1');
        $stmt->execute(['id' => $nextId]);
        $name = (string) ($stmt->fetchColumn() ?: ('#' . $nextId));
        return ['next' => $nextId, 'level' => $level, 'text' => ($level > 0 ? $level . '-lvl => ' : '') . '#' . $nextId . ' ' . $name];
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
