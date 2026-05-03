<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class ProfileRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function profile(int $viewerId, int $profileId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.login, u.online, u.onlinetime, u.datereg, u.avatars, u.groups, u.rang,
                    u.rang_a, u.rang_b, u.rang_c, u.count_poke, u.count_poke_s, u.info, u.gender,
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

        return [
            'user' => $this->formatUser($user, $normalDex, $shinyDex),
            'party' => $this->activeParty($profileId),
            'awards' => $this->presents($profileId, 1),
            'gifts' => $this->presents($profileId, 2),
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
            'online' => (int) ($user['online'] ?? 0) === 1,
            'lastOnline' => (int) ($user['onlinetime'] ?? 0),
            'registeredAt' => (string) ($user['datereg'] ?? ''),
            'avatar' => '/img/ava/' . $avatarFile . '.png',
            'group' => $this->groupName((int) ($user['groups'] ?? 6), (int) $user['id']),
            'rank' => (string) ($user['rang'] ?? 'Новичок'),
            'pvpRating' => $rangA,
            'pveRating' => $rangB,
            'questRating' => (int) ($user['rang_c'] ?? 0),
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
            'SELECT id, basenum, names, lvl, hp_my, hp_max, tips
               FROM pok_user
              WHERE users = :user AND active = 1
              ORDER BY startepoke DESC, id ASC
              LIMIT 6'
        );
        $stmt->execute(['user' => $profileId]);

        $party = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $party[] = [
                'id' => (int) $row['id'],
                'baseNum' => (int) $row['basenum'],
                'name' => strip_tags((string) $row['names']),
                'level' => (int) $row['lvl'],
                'hp' => max(0, (int) $row['hp_my']),
                'hpMax' => max(1, (int) $row['hp_max']),
                'tips' => (string) ($row['tips'] ?? 'normal'),
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

    private function countDistinctPokemon(int $profileId, string $tips): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT basenum) FROM pok_user WHERE users = :user AND tips = :tips'
        );
        $stmt->execute(['user' => $profileId, 'tips' => $tips]);

        return (int) ($stmt->fetchColumn() ?: 0);
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
