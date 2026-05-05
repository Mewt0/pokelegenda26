<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class TrainingRepository
{
    public const TRAINING_ITEM_ID = 330;
    public const WEAKENING_ITEM_ID = 678;
    public const DIAMOND_ITEM_ID = 2;
    public const COIN_ITEM_ID = 1;

    /** @var list<string> */
    private const STATS = ['atk', 'def', 'satk', 'sdef', 'speed'];

    /** @return array<int,array{name:string,bonus:int,success:float,immuneSuccess:float,weaken:float}> */
    public static function stages(): array
    {
        return [
            0 => ['name' => 'Без тренировки', 'bonus' => 0, 'success' => 100.0, 'immuneSuccess' => 100.0, 'weaken' => 0.0],
            1 => ['name' => 'Начальная', 'bonus' => 10, 'success' => 65.0, 'immuneSuccess' => 100.0, 'weaken' => 1.0],
            2 => ['name' => 'Расширенная', 'bonus' => 18, 'success' => 43.0, 'immuneSuccess' => 45.0, 'weaken' => 3.0],
            3 => ['name' => 'Мастерская', 'bonus' => 25, 'success' => 11.0, 'immuneSuccess' => 12.0, 'weaken' => 6.0],
            4 => ['name' => 'Знаменитая', 'bonus' => 31, 'success' => 6.0, 'immuneSuccess' => 6.5, 'weaken' => 10.0],
            5 => ['name' => 'Легендарная', 'bonus' => 36, 'success' => 3.0, 'immuneSuccess' => 3.2, 'weaken' => 55.0],
            6 => ['name' => 'Именная', 'bonus' => 40, 'success' => 1.9, 'immuneSuccess' => 2.3, 'weaken' => 90.0],
        ];
    }

    public function __construct(private PDO $db, private InventoryRepository $inventory)
    {
    }

    public function train(int $userId, int $pokemonId, bool $boosted = false): array
    {
        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }
        if (!$this->inventory->removeItem($userId, self::TRAINING_ITEM_ID, 1)) {
            return ['ok' => false, 'message' => 'Нужен Набор тренировки.'];
        }

        $pokemon = $this->findPokemonForUpdate($userId, $pokemonId);
        if ($pokemon === null) {
            $this->inventory->addItem($userId, self::TRAINING_ITEM_ID, 1);
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }

        $stage = max(0, min(6, (int) ($pokemon['training_stage'] ?? 0)));
        if ($stage >= 6) {
            return ['ok' => false, 'message' => 'У этого покемона уже именная тренировка.'];
        }

        $targetStage = $stage + 1;
        $meta = self::stages()[$targetStage];
        $chance = $boosted ? (float) $meta['immuneSuccess'] : (float) $meta['success'];

        if (!$this->roll($chance)) {
            $weakened = false;
            if ($stage > 0 && $this->roll((float) $meta['weaken'])) {
                $stage--;
                $weakened = true;
                $this->saveTraining($pokemonId, $stage, (string) ($pokemon['training_stat'] ?? ''), (string) ($pokemon['training_named_effect'] ?? ''), (int) ($pokemon['training_tamed'] ?? 0) === 1);
            }
            return [
                'ok' => true,
                'trained' => false,
                'weakened' => $weakened,
                'message' => $weakened
                    ? 'Тренировка сорвалась, уровень тренировки ослаб.'
                    : 'Тренировка не удалась.',
                'training' => $this->trainingInfo($this->findPokemonForUpdate($userId, $pokemonId) ?: $pokemon),
            ];
        }

        $stat = self::STATS[random_int(0, count(self::STATS) - 1)];
        $effect = (string) ($pokemon['training_named_effect'] ?? '');
        if ($targetStage >= 6 && $effect === '') {
            $effect = $this->randomNamedEffect();
        }
        $this->saveTraining($pokemonId, $targetStage, $stat, $effect, $targetStage >= 6 || (int) ($pokemon['training_tamed'] ?? 0) === 1);

        return [
            'ok' => true,
            'trained' => true,
            'message' => sprintf(
                'Тренировка успешна: %s, бонус +%d%% к %s.',
                self::stages()[$targetStage]['name'],
                (int) self::stages()[$targetStage]['bonus'],
                self::statLabel($stat)
            ),
            'training' => $this->trainingInfo($this->findPokemonForUpdate($userId, $pokemonId) ?: $pokemon),
        ];
    }

    public function weaken(int $userId, int $pokemonId): array
    {
        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }
        if (!$this->inventory->removeItem($userId, self::WEAKENING_ITEM_ID, 1)) {
            return ['ok' => false, 'message' => 'Нужен Набор ослабления.'];
        }

        $pokemon = $this->findPokemonForUpdate($userId, $pokemonId);
        if ($pokemon === null) {
            $this->inventory->addItem($userId, self::WEAKENING_ITEM_ID, 1);
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }

        $stage = max(0, min(6, (int) ($pokemon['training_stage'] ?? 0)));
        if ($stage <= 0) {
            return ['ok' => false, 'message' => 'У покемона нет тренировки для ослабления.'];
        }

        $this->saveTraining(
            $pokemonId,
            $stage - 1,
            (string) ($pokemon['training_stat'] ?? ''),
            (string) ($pokemon['training_named_effect'] ?? ''),
            true
        );

        return [
            'ok' => true,
            'message' => 'Тренировка ослаблена на одну стадию. Стат сохранен, покемон приручен.',
            'training' => $this->trainingInfo($this->findPokemonForUpdate($userId, $pokemonId) ?: $pokemon),
        ];
    }

    public function buy(int $userId, string $shop, int $itemId, int $count = 1): array
    {
        $count = max(1, min(99, $count));
        if (!in_array($itemId, [self::TRAINING_ITEM_ID, self::WEAKENING_ITEM_ID], true)) {
            return ['ok' => false, 'message' => 'Этот предмет здесь не продается.'];
        }

        $currency = $shop === 'diamond' ? self::DIAMOND_ITEM_ID : self::COIN_ITEM_ID;
        $price = $shop === 'diamond' ? 10 : 500000;
        $total = $price * $count;
        if (!$this->inventory->removeItem($userId, $currency, $total)) {
            return ['ok' => false, 'message' => $shop === 'diamond' ? 'Не хватает алмазов.' : 'Не хватает монет.'];
        }

        $this->inventory->addItem($userId, $itemId, $count);

        return [
            'ok' => true,
            'message' => sprintf('Покупка успешна: %s x%d.', $this->itemName($itemId), $count),
            'itemId' => $itemId,
            'count' => $count,
            'currencyItemId' => $currency,
            'price' => $total,
        ];
    }

    public static function statLabel(string $stat): string
    {
        return match ($stat) {
            'atk' => 'Атаке',
            'def' => 'Защите',
            'satk' => 'Спец. атаке',
            'sdef' => 'Спец. защите',
            'speed' => 'Скорости',
            default => 'случайному стату',
        };
    }

    public function trainingInfo(array $pokemon): array
    {
        $stage = max(0, min(6, (int) ($pokemon['training_stage'] ?? 0)));
        $stat = (string) ($pokemon['training_stat'] ?? '');
        $meta = self::stages()[$stage];

        return [
            'stage' => $stage,
            'stageName' => $meta['name'],
            'bonus' => (int) $meta['bonus'],
            'stat' => $stat,
            'statLabel' => self::statLabel($stat),
            'namedEffect' => (string) ($pokemon['training_named_effect'] ?? ''),
            'tamed' => (int) ($pokemon['training_tamed'] ?? 0) === 1,
        ];
    }

    private function findPokemonForUpdate(int $userId, int $pokemonId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, users, names, training_stage, training_stat, training_named_effect, training_tamed
               FROM pok_user
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        return $stmt->fetch() ?: null;
    }

    private function saveTraining(int $pokemonId, int $stage, string $stat, string $effect, bool $tamed): void
    {
        $stage = max(0, min(6, $stage));
        $this->db->prepare(
            'UPDATE pok_user
                SET training_stage = :stage,
                    training_stat = :stat,
                    training_named_effect = :effect,
                    training_tamed = :tamed,
                    training_updated_at = :updated
              WHERE id = :pokemon
              LIMIT 1'
        )->execute([
            'stage' => $stage,
            'stat' => $stage > 0 ? $stat : '',
            'effect' => $stage >= 6 ? $effect : '',
            'tamed' => $tamed ? 1 : 0,
            'updated' => time(),
            'pokemon' => $pokemonId,
        ]);
    }

    private function userIsBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT pve, pvp, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $user = $stmt->fetch();
        return !$user || (int) ($user['pve'] ?? 0) > 0 || (int) ($user['pvp'] ?? 0) > 0 || (int) ($user['trade'] ?? 0) > 0;
    }

    private function roll(float $chance): bool
    {
        return random_int(1, 1000) <= (int) round(max(0.0, min(100.0, $chance)) * 10);
    }

    private function randomNamedEffect(): string
    {
        $effects = ['burn', 'paralyze', 'freeze', 'poison', 'confuse', 'fear'];
        return $effects[random_int(0, count($effects) - 1)];
    }

    private function itemName(int $itemId): string
    {
        return $itemId === self::WEAKENING_ITEM_ID ? 'Набор ослабления' : 'Набор тренировки';
    }
}
