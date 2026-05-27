<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Game\PokemonFormCatalog;

final class ProfileRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function idByLogin(string $login): int
    {
        $login = trim($login);
        if ($login === '') {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT id
               FROM users
              WHERE LOWER(login) = LOWER(:login) AND activation = 1
              LIMIT 1'
        );
        $stmt->execute(['login' => $login]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function profile(int $viewerId, int $profileId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.login, u.email, u.email_verified_at, u.online, u.onlinetime, u.datereg, u.avatars, u.groups, u.rang,
                    u.rang_a, u.rang_b, u.rang_c, u.karma_score, u.count_poke, u.count_poke_s, u.info, u.gender,
                    u.clanid, u.clan_point, u.status_klan, u.buildmy, u.youtuber, u.prefics,
                    b.title AS build_title, t.townName AS town_name,
                    c.clan_name, c.clan_img, c.clan_reputation
               FROM users u
               LEFT JOIN build b ON b.id = u.buildmy
               LEFT JOIN towns t ON t.id = b.town
               LEFT JOIN clans c ON c.id_clan = u.clanid
              WHERE u.id = :id AND u.activation = 1
              LIMIT 1'
        );
        $stmt->execute(['id' => $profileId]);
        $user = $stmt->fetch();
        if (!$user) {
            return null;
        }

        $normalDex = $this->countDistinctPokemon($profileId, 'normal');
        $shinyDex = $this->countDistinctPokemon($profileId, 'shine');

        $formattedUser = $this->formatUser($user, $normalDex, $shinyDex);
        $party = $this->activeParty($profileId);
        $awards = $this->presents($profileId, 1);
        $gifts = $this->presents($profileId, 2);
        $gymBadges = $this->gymBadges($profileId);
        $friends = $this->friends($profileId);

        return [
            'user' => $formattedUser,
            'uid' => $formattedUser['id'],
            'avatar' => $formattedUser['avatar'],
            'rank' => $formattedUser['rank'],
            'clan' => $formattedUser['clan'],
            'party' => $party,
            'activeTeam' => $party,
            'awards' => $awards,
            'gifts' => $gifts,
            'gymBadges' => $gymBadges,
            'badges' => $gymBadges,
            'friends' => $friends,
            'badgeSummary' => [
                'gym' => count($gymBadges),
                'awards' => count($awards),
                'gifts' => count($gifts),
                'friends' => count($friends),
            ],
            'social' => $this->socialState($viewerId, $profileId),
            'viewerOwnsProfile' => $viewerId === $profileId,
        ];
    }

    private function formatUser(array $user, int $normalDex, int $shinyDex): array
    {
        $avatar = (int) ($user['avatars'] ?? 0);
        $avatarFile = $avatar > 0 ? str_pad((string) $avatar, 3, '0', STR_PAD_LEFT) : '001';
        $rangA = (int) ($user['rang_a'] ?? 0);
        $rangB = (int) ($user['rang_b'] ?? 0);

        return [
            'id' => (int) $user['id'],
            'login' => (string) $user['login'],
            'email' => (string) ($user['email'] ?? ''),
            'emailVerified' => (int) ($user['email_verified_at'] ?? 0) > 0,
            'online' => (int) ($user['online'] ?? 0) === 1,
            'lastOnline' => (int) ($user['onlinetime'] ?? 0),
            'registeredAt' => (string) ($user['datereg'] ?? ''),
            'avatar' => '/img/ava/' . $avatarFile . '.png',
            'group' => $this->groupName((int) ($user['groups'] ?? 6), (int) $user['id']),
            'rank' => (string) ($user['rang'] ?? 'Новичок'),
            'pvpRating' => $rangA,
            'pveRating' => $rangB,
            'questRating' => (int) ($user['rang_c'] ?? 0),
            'karma' => $this->karmaInfo((int) ($user['karma_score'] ?? 0), (int) ($user['groups'] ?? 6)),
            'pvpTitle' => $this->pvpTitle($rangA, $rangB),
            'pveTitle' => $this->pveTitle($rangB, $rangA),
            'normalDex' => $normalDex,
            'shinyDex' => $shinyDex,
            'info' => trim(strip_tags((string) ($user['info'] ?? ''))),
            'gender' => (int) ($user['gender'] ?? 0),
            'town' => (string) ($user['town_name'] ?? 'Неизвестно'),
            'location' => (string) ($user['build_title'] ?? 'Неизвестно'),
            'clan' => [
                'id' => (int) ($user['clanid'] ?? 0),
                'name' => (string) ($user['clan_name'] ?? ''),
                'image' => (string) ($user['clan_img'] ?? ''),
                'status' => (string) ($user['status_klan'] ?? ''),
                'points' => (int) ($user['clan_point'] ?? 0),
                'reputation' => (int) ($user['clan_reputation'] ?? 0),
            ],
            'rankImage' => $this->rankImage($rangA, $rangB),
        ];
    }

    private function activeParty(int $profileId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.id, pu.basenum, pu.names, pu.lvl, pu.hp_my, pu.hp_max, pu.tips,
                    COALESCE(ip.id_items, pu.item, 0) AS held_item_id,
                    held.name AS held_item_name,
                    held.tittle AS held_item_title
               FROM pok_user pu
          LEFT JOIN items_poke ip ON ip.id_poke = pu.id
          LEFT JOIN items held ON held.id = COALESCE(ip.id_items, pu.item, 0)
              WHERE pu.users = :user AND pu.active = 1
              ORDER BY pu.startepoke DESC, pu.id ASC
              LIMIT 6'
        );
        $stmt->execute(['user' => $profileId]);

        $party = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $party[] = [
                'id' => (int) $row['id'],
                'baseNum' => (int) $row['basenum'],
                'formId' => (int) $row['basenum'],
                'dexNumber' => PokemonFormCatalog::displayBaseId((int) $row['basenum']),
                'displayBaseNum' => PokemonFormCatalog::displayBaseId((int) $row['basenum']),
                'formKey' => PokemonFormCatalog::formKey((int) $row['basenum'], (string) $row['names']),
                'isForm' => PokemonFormCatalog::isForm((int) $row['basenum']),
                'name' => strip_tags((string) $row['names']),
                'level' => (int) $row['lvl'],
                'hp' => max(0, (int) $row['hp_my']),
                'hpMax' => max(1, (int) $row['hp_max']),
                'tips' => (string) ($row['tips'] ?? 'normal'),
                'heldItem' => [
                    'id' => (int) ($row['held_item_id'] ?? 0),
                    'name' => strip_tags((string) ($row['held_item_name'] ?? '')),
                    'title' => strip_tags((string) ($row['held_item_title'] ?? '')),
                    'image' => $this->itemIconPath((int) ($row['held_item_id'] ?? 0)),
                ],
            ];
        }

        return $party;
    }

    private function presents(int $profileId, int $type): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.idpresent, pu.coments, ps.name, ps.title
               FROM presents_users pu
               INNER JOIN presents_sistem ps ON ps.id = pu.idpresent
              WHERE pu.usertoid = :user AND pu.tippresent = :type
              ORDER BY pu.id DESC
              LIMIT 16'
        );
        $stmt->execute(['user' => $profileId, 'type' => $type]);

        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $items[] = [
                'id' => (int) $row['idpresent'],
                'name' => (string) $row['name'],
                'title' => (string) $row['title'],
                'comment' => (string) ($row['coments'] ?? ''),
                'image' => '/img/present/' . (int) $row['idpresent'] . '.png',
            ];
        }

        return $items;
    }

    private function friends(int $profileId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.login, u.groups, u.online
               FROM friends f
               INNER JOIN users u ON u.id = f.id_my_friend
              WHERE f.id_user = :user AND u.activation = 1
              ORDER BY u.online DESC, u.login ASC
              LIMIT 18'
        );
        $stmt->execute(['user' => $profileId]);

        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $items[] = [
                'id' => (int) $row['id'],
                'login' => (string) $row['login'],
                'group' => $this->groupName((int) ($row['groups'] ?? 6), (int) $row['id']),
                'online' => (int) ($row['online'] ?? 0) === 1,
            ];
        }

        return $items;
    }

    private function socialState(int $viewerId, int $profileId): array
    {
        $status = 'none';
        if ($viewerId <= 0 || $profileId <= 0) {
            $status = 'none';
        } elseif ($viewerId === $profileId) {
            $status = 'self';
        } elseif ($this->friendRowExists($viewerId, $profileId)) {
            $status = 'friends';
        } elseif ($this->friendRequestExists($viewerId, $profileId)) {
            $status = 'outgoing';
        } elseif ($this->friendRequestExists($profileId, $viewerId)) {
            $status = 'incoming';
        }

        return [
            'viewerId' => $viewerId,
            'profileId' => $profileId,
            'status' => $status,
            'own' => $status === 'self',
            'canMessage' => $viewerId > 0 && $profileId > 0 && $viewerId !== $profileId,
            'canBattle' => $viewerId > 0 && $profileId > 0 && $viewerId !== $profileId,
            'canRequestFriend' => $status === 'none',
            'canAcceptFriend' => $status === 'incoming',
            'canRemoveFriend' => $status === 'friends',
        ];
    }

    private function friendRowExists(int $userId, int $friendId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM friends WHERE id_user = :user AND id_my_friend = :friend LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'friend' => $friendId]);
        return (bool) $stmt->fetchColumn();
    }

    private function friendRequestExists(int $fromId, int $toId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM friends_zayv WHERE id_user = :from_user AND id_user_to = :to_user LIMIT 1'
        );
        $stmt->execute(['from_user' => $fromId, 'to_user' => $toId]);
        return (bool) $stmt->fetchColumn();
    }

    private function gymBadges(int $profileId): array
    {
        if (!$this->tableExists('gym_badges') || !$this->tableExists('user_gym_badges')) {
            return [];
        }

        $hasIssuedAt = $this->columnExists('user_gym_badges', 'issued_at');
        $hasRewardType = $this->columnExists('user_gym_badges', 'reward_type');
        $hasBattleSource = $this->columnExists('user_gym_badges', 'source_battle_id');
        $hasQuestSource = $this->columnExists('user_gym_badges', 'source_quest_id');
        $hasBuild = $this->tableExists('build');
        $issuedExpr = $hasIssuedAt ? 'COALESCE(NULLIF(ugb.issued_at, 0), ugb.awarded_at)' : 'ugb.awarded_at';
        $rewardExpr = $hasRewardType ? 'ugb.reward_type' : "'gym_badge'";
        $battleExpr = $hasBattleSource ? 'ugb.source_battle_id' : '0';
        $questExpr = $hasQuestSource ? 'ugb.source_quest_id' : '0';
        $locationSelect = $hasBuild ? 'b.title AS location_name' : "'' AS location_name";
        $locationJoin = $hasBuild ? 'LEFT JOIN build b ON b.id = gb.location_id' : '';

        $stmt = $this->db->prepare(
            'SELECT gb.id, gb.badge_key, gb.title, gb.leader_name, gb.location_id, gb.icon_item_id,
                    ' . $locationSelect . ',
                    ugb.source_type, ugb.source_id, ugb.awarded_by, ugb.awarded_at,
                    ' . $issuedExpr . ' AS issued_at,
                    ' . $rewardExpr . ' AS reward_type,
                    ' . $battleExpr . ' AS source_battle_id,
                    ' . $questExpr . ' AS source_quest_id
               FROM user_gym_badges ugb
         INNER JOIN gym_badges gb ON gb.id = ugb.badge_id
                    ' . $locationJoin . '
              WHERE ugb.user_id = :user
              ORDER BY issued_at DESC, gb.id ASC
              LIMIT 24'
        );
        $stmt->execute(['user' => $profileId]);

        $badges = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $iconItemId = (int) ($row['icon_item_id'] ?? 0);
            $badges[] = [
                'id' => (int) $row['id'],
                'key' => (string) $row['badge_key'],
                'title' => (string) $row['title'],
                'leader' => (string) ($row['leader_name'] ?? ''),
                'location' => (string) ($row['location_name'] ?? ''),
                'locationId' => (int) ($row['location_id'] ?? 0),
                'iconItemId' => $iconItemId,
                'image' => $this->itemIconPath($iconItemId),
                'rewardType' => (string) ($row['reward_type'] ?? 'gym_badge'),
                'sourceType' => (string) ($row['source_type'] ?? ''),
                'sourceId' => (int) ($row['source_id'] ?? 0),
                'sourceBattleId' => (int) ($row['source_battle_id'] ?? 0),
                'sourceQuestId' => (int) ($row['source_quest_id'] ?? 0),
                'source' => [
                    'type' => (string) ($row['source_type'] ?? ''),
                    'id' => (int) ($row['source_id'] ?? 0),
                    'battleId' => (int) ($row['source_battle_id'] ?? 0),
                    'questId' => (int) ($row['source_quest_id'] ?? 0),
                ],
                'awardedBy' => (int) ($row['awarded_by'] ?? 0),
                'awardedAt' => (int) ($row['awarded_at'] ?? 0),
                'issuedAt' => (int) ($row['issued_at'] ?? 0),
                'issued_at' => (int) ($row['issued_at'] ?? 0),
            ];
        }

        return $badges;
    }

    private function countDistinctPokemon(int $profileId, string $tips): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT basenum) FROM pok_user WHERE users = :user AND tips = :tips'
        );
        $stmt->execute(['user' => $profileId, 'tips' => $tips]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function itemIconPath(int $itemId): string
    {
        if ($itemId <= 0) {
            return '/public/img/ui/menu-profile.png';
        }

        $indexed = $this->itemIconIndex()[(string) $itemId] ?? '';
        if ($indexed !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . basename((string) $indexed))) {
            return '/public/img/items/' . basename((string) $indexed);
        }

        if (defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . $itemId . '.png')) {
            return '/public/img/items/' . $itemId . '.png';
        }

        return '/public/img/ui/menu-inventory.png';
    }

    /** @return array<string,string> */
    private function itemIconIndex(): array
    {
        static $index = null;
        if (is_array($index)) {
            return $index;
        }

        $index = [];
        if (!defined('APP_ROOT')) {
            return $index;
        }

        $path = APP_ROOT . '/public/img/items/index.json';
        if (!is_file($path)) {
            return $index;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return $index;
        }

        foreach ($decoded as $key => $file) {
            if (is_string($file) && $file !== '') {
                $index[(string) $key] = $file;
            }
        }

        return $index;
    }

    private function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
               FROM information_schema.tables
              WHERE table_schema = DATABASE() AND table_name = :table
              LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        return (bool) $stmt->fetchColumn();
    }

    private function columnExists(string $table, string $column): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
               FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column
              LIMIT 1'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (bool) $stmt->fetchColumn();
    }

    private function groupName(int $group, int $id): string
    {
        if ($id === 1 && $group === 1) {
            return 'Главный администратор';
        }
        if ($id === 3 && $group === 1) {
            return 'Система';
        }

        return match ($group) {
            1 => 'Администратор',
            2 => 'Полицейский',
            3 => 'Модератор',
            4 => 'Наставник',
            5 => 'Гим-лидер',
            7 => 'Заключенный',
            8 => 'Куратор турниров',
            10 => 'Забанен',
            default => 'Тренер',
        };
    }

    private function karmaInfo(int $score, int $group): array
    {
        $state = ($score <= -10 || in_array($group, [7, 10], true)) ? 'bad' : ($score >= 10 ? 'good' : 'neutral');
        return [
            'score' => $score,
            'state' => $state,
            'title' => match ($state) {
                'bad' => 'Плохая репутация',
                'good' => 'Хорошая репутация',
                default => 'Нейтральная репутация',
            },
        ];
    }

    private function pvpTitle(int $rangA, int $rangB): string
    {
        $title = 'Новичок';
        foreach ([250 => 'Начинающий', 8000 => 'Странствующий', 110000 => 'Опытный', 180000 => 'Узнаваемый', 250000 => 'Великий', 380000 => 'Непобедимый', 500000 => 'Легендарный'] as $score => $name) {
            if ($rangA >= $score) {
                $title = $name;
            }
        }
        if ($rangB > 100000 && $rangA > 800000) {
            $title = 'League Of Pokemons';
        }
        return $title;
    }

    private function pveTitle(int $rangB, int $rangA): string
    {
        $title = $rangB < -500 ? 'Неудачник' : 'Искатель';
        foreach ([250 => 'Тренер', 5000 => 'Покетренер', 11000 => 'Профи', 20000 => 'Покепрофи', 30000 => 'Мастер', 50000 => 'Покемастер'] as $score => $name) {
            if ($rangB >= $score) {
                $title = $name;
            }
        }
        if ($rangA > 800000 && $rangB > 100000) {
            $title = 'League Of Pokemons';
        }
        return $title;
    }

    private function rankImage(int $rangA, int $rangB): string
    {
        $conditions = [
            [1000000, 50000, 10],
            [500000, 45000, 9],
            [450000, 38000, 8],
            [380000, 30000, 7],
            [340000, 25000, 6],
            [250000, 18000, 5],
            [180000, 15000, 4],
            [110000, 11000, 3],
            [8000, 5000, 2],
            [250, 250, 1],
        ];

        foreach ($conditions as [$pvp, $pve, $image]) {
            if ($rangA > $pvp && $rangB > $pve) {
                return '/img/info/rang/' . $image . '.png';
            }
        }

        return '/img/info/rang/0.png';
    }
}
