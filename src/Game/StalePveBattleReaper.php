<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;
use LogicException;
use Throwable;

final class StalePveBattleReaper
{
    /** Fallback lifetime for missing, zero, or implausibly old battle timestamps. */
    public const FALLBACK_TTL_SECONDS = 7200;
    private const MAX_REASONABLE_AGE_SECONDS = 315360000; // 10 years

    public function __construct(private PDO $db)
    {
    }

    /** @return array{dryRun:bool,candidates:int,changed:int,battleIds:list<int>,userIds:list<int>,fallbackTtlSeconds:int} */
    public function reap(bool $dryRun = true, int $limit = 0, bool $manageTransactions = true): array
    {
        if (!$dryRun && !$manageTransactions && !$this->db->inTransaction()) {
            throw new LogicException('Stale PvE battle reaper requires an active transaction when transaction management is disabled.');
        }

        $now = time();
        $sql = 'SELECT b.id, b.user_1, b.times
                  FROM battles b
                  JOIN users u ON u.id = b.user_1
                 WHERE b.batl_tip = "pve" AND b.pobeda = 0 AND b.id > 0
                   AND b.user_1 > 0
                   AND NOT EXISTS (SELECT 1 FROM boss_battle_sessions bs WHERE bs.battle_id = b.id)
                   AND ' . self::expiryPredicateSql('b');
        if ($limit > 0) {
            $sql .= ' ORDER BY b.id ASC LIMIT ' . max(1, min($limit, 10000));
        } else {
            $sql .= ' ORDER BY b.id ASC';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $battleIds = array_map(static fn (array $row): int => (int) $row['id'], $candidates);
        $userIds = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['user_1'], $candidates)));
        $changed = 0;

        if (!$dryRun) {
            foreach ($candidates as $candidate) {
                $battleId = (int) $candidate['id'];
                $userId = (int) $candidate['user_1'];
                if ($battleId <= 0 || $userId <= 0) {
                    continue;
                }
                if ($manageTransactions) {
                    $this->db->beginTransaction();
                }
                try {
                    $lock = $this->db->prepare('SELECT b.id, b.times FROM battles b WHERE b.id = :id AND b.batl_tip = "pve" AND b.pobeda = 0 AND b.user_1 = :user AND NOT EXISTS (SELECT 1 FROM boss_battle_sessions bs WHERE bs.battle_id = b.id) AND ' . self::expiryPredicateSql('b') . ' FOR UPDATE');
                    $lock->execute(['id' => $battleId, 'user' => $userId]);
                    $battle = $lock->fetch(PDO::FETCH_ASSOC);
                    if (!$battle) {
                        if ($manageTransactions) {
                            $this->db->commit();
                        }
                        continue;
                    }

                    $close = $this->db->prepare('UPDATE battles SET pobeda = -1 WHERE id = :id AND user_1 = :user AND batl_tip = "pve" AND pobeda = 0 AND NOT EXISTS (SELECT 1 FROM boss_battle_sessions bs WHERE bs.battle_id = battles.id) AND ' . self::expiryPredicateSql('battles'));
                    $close->execute(['id' => $battleId, 'user' => $userId]);
                    if ($close->rowCount() > 0) {
                        $closed = $close->rowCount();
                        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :battle AND pokeid LIKE "v:%" AND mess = "helping_hand"')
                            ->execute(['battle' => $battleId]);
                        $this->db->prepare('UPDATE battle_transformations SET active = 0, reverted_at = :now WHERE battle_id = :battle AND active = 1')
                            ->execute(['now' => $now, 'battle' => $battleId]);
                        // A PvP flag is deliberately preserved; only the matching PvE pointer is cleared.
                        $clear = $this->db->prepare('UPDATE users SET pve = 0, battleid = 0 WHERE id = :user AND battleid = :battle AND pve = 1');
                        $clear->execute(['user' => $userId, 'battle' => $battleId]);
                        $changed += $closed;
                    }
                    if ($manageTransactions) {
                        $this->db->commit();
                    }
                } catch (Throwable $e) {
                    if ($manageTransactions && $this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    throw $e;
                }
            }
        }

        return [
            'dryRun' => $dryRun,
            'candidates' => count($candidates),
            'changed' => $changed,
            'battleIds' => $battleIds,
            'userIds' => $userIds,
            'fallbackTtlSeconds' => self::FALLBACK_TTL_SECONDS,
        ];
    }

    public static function expiryPredicateSql(string $alias): string
    {
        if (!in_array($alias, ['b', 'battles'], true)) {
            throw new LogicException('Unexpected battle table alias.');
        }
        $oldest = 'UNIX_TIMESTAMP() - ' . self::MAX_REASONABLE_AGE_SECONDS;
        // Ordinary PvE uses an absolute deadline. If it is invalid, allow two
        // hours from a known creation time; unknown age is never safe to reap.
        return '((' . $alias . '.times > 0 AND ' . $alias . '.times >= ' . $oldest
            . ' AND ' . $alias . '.times <= UNIX_TIMESTAMP()) OR (('
            . $alias . '.times <= 0 OR ' . $alias . '.times < ' . $oldest . ') AND '
            . $alias . '.time > 0 AND ' . $alias . '.time <= UNIX_TIMESTAMP() - ' . self::FALLBACK_TTL_SECONDS . '))';
    }
}
