<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
use RuntimeException;

final class StarterQuestRewardService
{
    public const PACKS = [
        1 => ['items'=>[1=>5000,3=>10,10=>3,15=>1], 'rank'=>1, 'nextQuestId'=>101, 'nextProcess'=>1],
        101 => ['items'=>[1=>2000,3=>5,10=>2,15=>1], 'rank'=>1, 'nextQuestId'=>102, 'nextProcess'=>10],
        102 => ['items'=>[1=>3000,90111=>1], 'rank'=>1, 'nextQuestId'=>103, 'nextProcess'=>10],
        103 => ['items'=>[1=>2000,3=>5], 'rank'=>1, 'nextQuestId'=>0, 'nextProcess'=>0],
    ];

    public function __construct(private PDO $db, private QuestRepository $quests, private ?RewardRepository $rewards = null)
    {
        $this->rewards ??= new RewardRepository($db);
    }

    public function pack(int $questId): ?array
    {
        return self::PACKS[$questId] ?? null;
    }

    public function summary(int $questId): string
    {
        $parts = [];
        $stmt = $this->db->prepare('SELECT name FROM items WHERE id=? LIMIT 1');
        foreach (($this->pack($questId)['items'] ?? []) as $id=>$count) {
            $stmt->execute([$id]);
            $parts[] = (string)($stmt->fetchColumn() ?: ('Предмет #' . $id)) . ' ×' . $count;
        }
        return implode(', ', $parts);
    }

    /** All success is provisional when an enclosing transaction is still open. */
    public function claim(int $userId, int $questId, ?callable $grantStarter = null): array
    {
        $pack = $this->pack($questId);
        if ($pack === null || $userId <= 0) {
            return ['claimed'=>false];
        }
        $owns = !$this->db->inTransaction();
        $savepoint = 'fpe_' . bin2hex(random_bytes(6));
        if ($owns) { $this->db->beginTransaction(); }
        else { $this->db->exec('SAVEPOINT ' . $savepoint); }
        try {
            $user = $this->db->prepare('SELECT id,buildmy FROM users WHERE id=? AND activation=1 FOR UPDATE');
            $user->execute([$userId]);
            $account = $user->fetch();
            $quest = $this->quests->findForUpdate($userId, $questId);
            $receipt = $this->db->prepare('SELECT id FROM starter_quest_reward_claims WHERE user_id=? AND quest_id=? FOR UPDATE');
            $receipt->execute([$userId,$questId]);
            $result = ['claimed'=>false];
            if ($receipt->fetchColumn()) {
                $result['already_claimed'] = true;
            } elseif ($quest !== null && (int)$quest['gotov'] === 1) {
                $result['legacy_completed'] = true;
            } elseif ($account && $quest !== null && $this->quests->canStart($userId,$questId)
                && (int)$quest['process'] >= ($questId === 1 ? 4 : 10)) {
                if ($questId === 1 && ((int)$account['buildmy'] !== 3 || !$grantStarter)) {
                    throw new RuntimeException('Starter claim requires Professor Oak');
                }
                if ($questId === 102 && (int)$account['buildmy'] !== 16) {
                    throw new RuntimeException('Town reward requires arrival');
                }
                $key = 'fpe:v1:user:' . $userId . ':quest:' . $questId;
                $pokemonId = 0;
                if ($questId === 1) {
                    $starter = $this->db->prepare('SELECT id FROM pok_user WHERE users=? AND startepoke=1 LIMIT 1 FOR UPDATE');
                    $starter->execute([$userId]);
                    if ($starter->fetchColumn()) { throw new RuntimeException('Starter already exists'); }
                    $pokemonId = (int)$grantStarter($quest,$key);
                    if ($pokemonId <= 0) { throw new RuntimeException('Starter creation failed'); }
                }
                $reward = $this->rewards->grantPipeline($userId,['items'=>$pack['items']], 'starter_quest', (string)$questId, [
                    'operation_key'=>$key, 'title'=>'Награда новичка: ' . $this->summary($questId),
                    'throw_on_fail'=>true, 'email'=>false,
                ]);
                if (empty($reward['ok'])) { throw new RuntimeException('Starter reward failed'); }
                $this->quests->updateState($userId,$questId,$questId === 1 ? 10 : 20,1);
                $this->quests->addQuestRank($userId,$pack['rank']);
                if ($pack['nextQuestId'] > 0) {
                    $this->quests->startIfAvailable($userId,$pack['nextQuestId'],$pack['nextProcess']);
                }
                $receipt = $this->db->prepare('INSERT INTO starter_quest_reward_claims (user_id,quest_id,operation_key,reward_json,created_at) VALUES (?,?,?,?,?)');
                $receipt->execute([$userId,$questId,$key,json_encode(['pack'=>$pack,'pokemon_id'=>$pokemonId,'transaction_id'=>$reward['transaction_id']],JSON_THROW_ON_ERROR),time()]);
                $result = ['claimed'=>true,'pokemon_id'=>$pokemonId,'operation_key'=>$key];
            }
            if ($owns) { $this->db->commit(); }
            else { $this->db->exec('RELEASE SAVEPOINT ' . $savepoint); }
            return $result;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                if ($owns) { $this->db->rollBack(); }
                else {
                    $this->db->exec('ROLLBACK TO SAVEPOINT ' . $savepoint);
                    $this->db->exec('RELEASE SAVEPOINT ' . $savepoint);
                }
            }
            throw $e;
        }
    }
}
