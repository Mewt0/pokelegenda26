<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Game\PokemonFormCatalog;
use Pokemon8\Security\PasswordHasher;

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

        $settings = $this->profileSettings($profileId);
        $viewerOwnsProfile = $viewerId === $profileId;
        $formattedUser = $this->formatUser($user, $normalDex, $shinyDex);
        $appearance = [
            'background' => $settings['profileBackground'] !== '' ? $settings['profileBackground'] : 'classic',
            'frame' => $settings['profileFrame'] !== '' ? $settings['profileFrame'] : 'classic',
            'title' => $settings['title'],
        ];
        if (!$viewerOwnsProfile && !$settings['showOnline']) {
            $formattedUser['online'] = false;
            $formattedUser['lastOnline'] = 0;
        }
        $partyVisible = $viewerOwnsProfile || $settings['showPartyPublic'];
        $party = $partyVisible ? $this->activeParty($profileId) : [];
        $achievementsVisible = $viewerOwnsProfile || $settings['showAchievements'];
        $giftsVisible = $viewerOwnsProfile || $settings['showGifts'];
        $awards = $achievementsVisible ? $this->presents($profileId, 1) : [];
        $gifts = $giftsVisible ? $this->presents($profileId, 2) : [];
        $gymBadges = $achievementsVisible ? $this->gymBadges($profileId) : [];
        $friends = $this->friends($profileId);

        return [
            'user' => $formattedUser,
            'uid' => $formattedUser['id'],
            'avatar' => $formattedUser['avatar'],
            'rank' => $formattedUser['rank'],
            'clan' => $formattedUser['clan'],
            'appearance' => $appearance,
            'party' => $party,
            'activeTeam' => $party,
            'privacy' => [
                'showPartyPublic' => $settings['showPartyPublic'],
                'showOnline' => $settings['showOnline'],
                'allowPm' => $settings['allowPm'],
                'allowFriendRequests' => $settings['allowFriendRequests'],
                'showGifts' => $settings['showGifts'],
                'showAchievements' => $settings['showAchievements'],
                'partyVisible' => $partyVisible,
                'partyHidden' => !$partyVisible,
                'giftsVisible' => $giftsVisible,
                'achievementsVisible' => $achievementsVisible,
            ],
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
            'social' => $this->socialState($viewerId, $profileId, $settings),
            'viewerOwnsProfile' => $viewerOwnsProfile,
        ];
    }

    public function settingsForUser(int $userId): array
    {
        return $this->settingsPayload($userId);
    }

    public function saveSettings(int $userId, array $input): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'Нужно войти в игру.'];
        }
        if (!$this->tableExists('user_settings')) {
            return ['ok' => false, 'message' => 'Настройки профиля пока недоступны: миграция user_settings не применена.'];
        }

        $this->ensureSettingsRow($userId);
        $section = strtolower(trim((string) ($input['section'] ?? '')));
        if ($section === '' && array_key_exists('show_party_public', $input)) {
            $section = 'privacy';
        }

        return match ($section) {
            'account' => $this->saveAccountSettings($userId, $input),
            'profile' => $this->saveProfileSettings($userId, $input),
            'privacy' => $this->savePrivacySettings($userId, $input),
            'interface' => $this->saveInterfaceSettings($userId, $input),
            'notifications' => $this->saveNotificationSettings($userId, $input),
            default => ['ok' => false, 'message' => 'Раздел настроек пока не поддерживается.'],
        };
    }

    private function settingsPayload(int $userId): array
    {
        $settings = $this->profileSettings($userId);
        $account = $this->accountInfo($userId);
        $profile = $this->profileInfo($userId, $settings);

        return $settings + [
            'account' => $account,
            'profile' => $profile,
            'options' => [
                'themes' => [
                    ['value' => 'light', 'label' => 'Светлая'],
                    ['value' => 'dark', 'label' => 'Тёмная'],
                    ['value' => 'auto', 'label' => 'Авто'],
                ],
                'uiSizes' => [
                    ['value' => 'compact', 'label' => 'Компактный'],
                    ['value' => 'normal', 'label' => 'Обычный'],
                    ['value' => 'large', 'label' => 'Крупный'],
                ],
                'backgrounds' => [
                    ['value' => 'classic', 'label' => 'Классический'],
                    ['value' => 'kanto', 'label' => 'Канто'],
                    ['value' => 'forest', 'label' => 'Лес'],
                    ['value' => 'ocean', 'label' => 'Океан'],
                    ['value' => 'arena', 'label' => 'Арена'],
                ],
                'frames' => [
                    ['value' => 'classic', 'label' => 'Классическая'],
                    ['value' => 'blue', 'label' => 'Синяя'],
                    ['value' => 'gold', 'label' => 'Золотая'],
                    ['value' => 'shadow', 'label' => 'Тёмная'],
                ],
                'titles' => $this->availableTitles($userId),
            ],
            'future' => [
                'twoFactor' => false,
                'telegram' => false,
                'discord' => false,
                'vk' => false,
                'googleAuthenticator' => false,
                'loginHistory' => false,
                'activeSessions' => false,
                'profileLayout' => false,
            ],
        ];
    }

    private function saveAccountSettings(int $userId, array $input): array
    {
        $messages = [];
        $account = $this->accountInfo($userId, true);
        if ($account === []) {
            return ['ok' => false, 'message' => 'Аккаунт не найден.'];
        }

        $newLogin = trim((string) ($input['new_login'] ?? ''));
        if ($newLogin !== '' && strcasecmp($newLogin, (string) ($account['login'] ?? '')) !== 0) {
            return [
                'ok' => false,
                'message' => 'Смена логина пока выключена: нужно отдельное правило, чтобы не сломать legacy-связи, почту, сделки и логи.',
                'settings' => $this->settingsPayload($userId),
            ];
        }

        $newEmail = $this->normalizeOptionalEmail((string) ($input['new_email'] ?? ''));
        $repeatEmail = $this->normalizeOptionalEmail((string) ($input['repeat_email'] ?? ''));
        if ($newEmail !== null || $repeatEmail !== null) {
            if ($newEmail === null || $repeatEmail === null || strcasecmp($newEmail, $repeatEmail) !== 0) {
                return ['ok' => false, 'message' => 'Новый email и повтор email должны совпадать.', 'settings' => $this->settingsPayload($userId)];
            }
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'message' => 'Укажи корректный email.', 'settings' => $this->settingsPayload($userId)];
            }
            if ($this->emailExists($newEmail, $userId)) {
                return ['ok' => false, 'message' => 'Этот email уже привязан к другому аккаунту.', 'settings' => $this->settingsPayload($userId)];
            }
            $stmt = $this->db->prepare(
                'UPDATE users SET email = :email, email_verified_at = 0 WHERE id = :id LIMIT 1'
            );
            $stmt->execute(['email' => $newEmail, 'id' => $userId]);
            $messages[] = 'Email сохранён. Подтверждение можно будет подключить отдельным письмом.';
        }

        $oldPassword = (string) ($input['old_password'] ?? '');
        $newPassword = (string) ($input['new_password'] ?? '');
        $repeatPassword = (string) ($input['repeat_password'] ?? '');
        if ($oldPassword !== '' || $newPassword !== '' || $repeatPassword !== '') {
            if ($oldPassword === '' || $newPassword === '' || $repeatPassword === '') {
                return ['ok' => false, 'message' => 'Для смены пароля заполни старый пароль, новый пароль и повтор.', 'settings' => $this->settingsPayload($userId)];
            }
            $len = mb_strlen($newPassword, 'UTF-8');
            if ($len < 6 || $len > 72) {
                return ['ok' => false, 'message' => 'Новый пароль должен быть от 6 до 72 символов.', 'settings' => $this->settingsPayload($userId)];
            }
            if (!hash_equals($newPassword, $repeatPassword)) {
                return ['ok' => false, 'message' => 'Новый пароль и повтор не совпадают.', 'settings' => $this->settingsPayload($userId)];
            }
            $hasher = new PasswordHasher();
            if (!$hasher->verify($oldPassword, (string) ($account['password'] ?? ''))) {
                return ['ok' => false, 'message' => 'Старый пароль указан неверно.', 'settings' => $this->settingsPayload($userId)];
            }
            $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id LIMIT 1');
            $stmt->execute(['password' => $hasher->hash($newPassword), 'id' => $userId]);
            $messages[] = 'Пароль изменён.';
        }

        return [
            'ok' => true,
            'message' => $messages ? implode(' ', $messages) : 'Данные аккаунта без изменений.',
            'settings' => $this->settingsPayload($userId),
        ];
    }

    private function saveProfileSettings(int $userId, array $input): array
    {
        $description = trim((string) ($input['description'] ?? ''));
        if (mb_strlen($description, 'UTF-8') > 500) {
            $description = mb_substr($description, 0, 500, 'UTF-8');
        }
        $background = $this->choice((string) ($input['profile_background'] ?? 'classic'), ['classic', 'kanto', 'forest', 'ocean', 'arena'], 'classic');
        $frame = $this->choice((string) ($input['profile_frame'] ?? 'classic'), ['classic', 'blue', 'gold', 'shadow'], 'classic');
        $title = mb_substr(trim((string) ($input['title'] ?? '')), 0, 64, 'UTF-8');

        $stmt = $this->db->prepare('UPDATE users SET info = :info WHERE id = :id LIMIT 1');
        $stmt->execute(['info' => $description, 'id' => $userId]);

        $stmt = $this->db->prepare(
            'UPDATE user_settings
                SET profile_background = :background,
                    profile_frame = :frame,
                    profile_title = :title,
                    updated_at = :updated
              WHERE user_id = :user
              LIMIT 1'
        );
        $stmt->execute([
            'background' => $background,
            'frame' => $frame,
            'title' => $title,
            'updated' => time(),
            'user' => $userId,
        ]);

        return ['ok' => true, 'message' => 'Настройки профиля сохранены.', 'settings' => $this->settingsPayload($userId)];
    }

    private function savePrivacySettings(int $userId, array $input): array
    {
        $current = $this->profileSettings($userId);
        $settings = [
            'show_online' => $this->boolInput($input, 'show_online', $current['showOnline']),
            'allow_pm' => $this->boolInput($input, 'allow_pm', $current['allowPm']),
            'allow_friend_requests' => $this->boolInput($input, 'allow_friend_requests', $current['allowFriendRequests']),
            'show_gifts' => $this->boolInput($input, 'show_gifts', $current['showGifts']),
            'show_achievements' => $this->boolInput($input, 'show_achievements', $current['showAchievements']),
            'show_party_public' => $this->boolInput($input, 'show_party_public', $current['showPartyPublic']),
        ];
        $this->updateSettingsColumns($userId, $settings);
        $this->syncLegacyPartySetting($userId, (bool) $settings['show_party_public']);

        return [
            'ok' => true,
            'message' => $settings['show_party_public'] ? 'Настройки приватности сохранены. Команда видна.' : 'Настройки приватности сохранены. Команда скрыта.',
            'settings' => $this->settingsPayload($userId),
        ];
    }

    private function saveInterfaceSettings(int $userId, array $input): array
    {
        $current = $this->profileSettings($userId);
        $this->updateSettingsColumns($userId, [
            'theme' => $this->choice((string) ($input['theme'] ?? $current['theme']), ['light', 'dark', 'auto'], 'auto'),
            'ui_size' => $this->choice((string) ($input['ui_size'] ?? $current['uiSize']), ['compact', 'normal', 'large'], 'normal'),
            'sounds' => $this->boolInput($input, 'sounds', $current['sounds']),
            'animations' => $this->boolInput($input, 'animations', $current['animations']),
        ]);

        return ['ok' => true, 'message' => 'Настройки интерфейса сохранены.', 'settings' => $this->settingsPayload($userId)];
    }

    private function saveNotificationSettings(int $userId, array $input): array
    {
        $current = $this->profileSettings($userId);
        $this->updateSettingsColumns($userId, [
            'notify_messages' => $this->boolInput($input, 'notify_messages', $current['notifyMessages']),
            'notify_friends' => $this->boolInput($input, 'notify_friends', $current['notifyFriends']),
            'notify_gifts' => $this->boolInput($input, 'notify_gifts', $current['notifyGifts']),
            'notify_clan' => $this->boolInput($input, 'notify_clan', $current['notifyClan']),
            'notify_system' => $this->boolInput($input, 'notify_system', $current['notifySystem']),
        ]);

        return ['ok' => true, 'message' => 'Настройки уведомлений сохранены.', 'settings' => $this->settingsPayload($userId)];
    }

    private function updateSettingsColumns(int $userId, array $columns): void
    {
        if ($columns === []) {
            return;
        }

        $allowed = [
            'show_online', 'allow_pm', 'allow_friend_requests', 'show_gifts', 'show_achievements',
            'show_party_public', 'theme', 'ui_size', 'sounds', 'animations', 'notify_messages',
            'notify_friends', 'notify_gifts', 'notify_clan', 'notify_system', 'profile_background',
            'profile_frame', 'profile_title',
        ];
        $sets = [];
        $params = ['user' => $userId, 'updated' => time()];
        foreach ($columns as $column => $value) {
            if (!in_array($column, $allowed, true)) {
                continue;
            }
            $sets[] = $column . ' = :' . $column;
            $params[$column] = is_bool($value) ? ($value ? 1 : 0) : $value;
        }
        if ($sets === []) {
            return;
        }
        $sets[] = 'updated_at = :updated';

        $stmt = $this->db->prepare(
            'UPDATE user_settings SET ' . implode(', ', $sets) . ' WHERE user_id = :user LIMIT 1'
        );
        $stmt->execute($params);
    }

    private function ensureSettingsRow(int $userId): void
    {
        if ($userId <= 0 || !$this->tableExists('user_settings')) {
            return;
        }

        $legacyParty = true;
        if ($this->tableExists('user_profile_settings')) {
            $stmt = $this->db->prepare('SELECT show_party_public FROM user_profile_settings WHERE user_id = :user LIMIT 1');
            $stmt->execute(['user' => $userId]);
            $value = $stmt->fetchColumn();
            if ($value !== false) {
                $legacyParty = (int) $value === 1;
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO user_settings (user_id, show_party_public, updated_at)
             VALUES (:user, :party, :updated)
             ON DUPLICATE KEY UPDATE user_id = user_id'
        );
        $stmt->execute([
            'user' => $userId,
            'party' => $legacyParty ? 1 : 0,
            'updated' => time(),
        ]);
    }

    private function syncLegacyPartySetting(int $userId, bool $showPartyPublic): void
    {
        if (!$this->tableExists('user_profile_settings')) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO user_profile_settings (user_id, show_party_public, updated_at)
             VALUES (:user, :show_party_public, :updated_at)
             ON DUPLICATE KEY UPDATE show_party_public = VALUES(show_party_public), updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'user' => $userId,
            'show_party_public' => $showPartyPublic ? 1 : 0,
            'updated_at' => time(),
        ]);
    }

    private function boolInput(array $input, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $input)) {
            return $default;
        }
        $raw = strtolower(trim((string) $input[$key]));
        return in_array($raw, ['1', 'true', 'on', 'yes'], true);
    }

    private function choice(string $value, array $allowed, string $default): string
    {
        $value = strtolower(trim($value));
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function normalizeOptionalEmail(string $email): ?string
    {
        $email = trim(mb_strtolower($email, 'UTF-8'));
        return $email === '' || $email === 'none@mail.ru' ? null : $email;
    }

    private function emailExists(string $email, int $exceptUserId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) AND id != :except LIMIT 1'
        );
        $stmt->execute(['email' => $email, 'except' => $exceptUserId]);
        return (bool) $stmt->fetchColumn();
    }

    private function accountInfo(int $userId, bool $includePassword = false): array
    {
        $columns = $includePassword ? 'id, login, email, email_verified_at, password' : 'id, login, email, email_verified_at';
        $stmt = $this->db->prepare('SELECT ' . $columns . ' FROM users WHERE id = :id AND activation = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $email = (string) ($row['email'] ?? '');
        $payload = [
            'id' => (int) $row['id'],
            'login' => (string) $row['login'],
            'email' => $email,
            'emailMasked' => $this->maskEmail($email),
            'emailVerified' => (int) ($row['email_verified_at'] ?? 0) > 0,
            'twoFactorEnabled' => false,
            'loginChangeEnabled' => false,
        ];
        if ($includePassword) {
            $payload['password'] = (string) ($row['password'] ?? '');
        }

        return $payload;
    }

    private function profileInfo(int $userId, array $settings): array
    {
        $stmt = $this->db->prepare('SELECT info, avatars, rang FROM users WHERE id = :id AND activation = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch() ?: [];

        return [
            'description' => trim(strip_tags((string) ($row['info'] ?? ''))),
            'avatarId' => (int) ($row['avatars'] ?? 0),
            'avatarUploadEnabled' => false,
            'background' => $settings['profileBackground'],
            'frame' => $settings['profileFrame'],
            'title' => $settings['title'],
            'rankTitle' => (string) ($row['rang'] ?? 'Новичок'),
        ];
    }

    private function availableTitles(int $userId): array
    {
        $profile = $this->profile($userId, $userId);
        $user = $profile['user'] ?? [];
        $raw = array_unique(array_filter([
            'Тренер',
            (string) ($user['rank'] ?? ''),
            (string) ($user['pvpTitle'] ?? ''),
            (string) ($user['pveTitle'] ?? ''),
        ]));

        return array_map(static fn (string $title): array => ['value' => $title, 'label' => $title], $raw);
    }

    private function maskEmail(string $email): string
    {
        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) {
            return '';
        }
        [$name, $domain] = explode('@', $email, 2);
        $first = mb_substr($name, 0, 1, 'UTF-8');
        return $first . '***@' . $domain;
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
            'SELECT pu.id, pu.basenum, pu.names, pu.lvl, pu.hp_my, pu.hp_max, pu.tips
               FROM pok_user pu
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

    private function profileSettings(int $profileId): array
    {
        $defaults = [
            'showOnline' => true,
            'allowPm' => true,
            'allowFriendRequests' => true,
            'showGifts' => true,
            'showAchievements' => true,
            'showPartyPublic' => true,
            'theme' => 'auto',
            'uiSize' => 'normal',
            'sounds' => true,
            'animations' => true,
            'notifyMessages' => true,
            'notifyFriends' => true,
            'notifyGifts' => true,
            'notifyClan' => true,
            'notifySystem' => true,
            'profileBackground' => 'classic',
            'profileFrame' => 'classic',
            'title' => '',
        ];

        if ($profileId <= 0) {
            return $defaults;
        }

        if ($this->tableExists('user_settings')) {
            $this->ensureSettingsRow($profileId);
            $stmt = $this->db->prepare('SELECT * FROM user_settings WHERE user_id = :user LIMIT 1');
            $stmt->execute(['user' => $profileId]);
            $row = $stmt->fetch();
            if ($row) {
                return [
                    'showOnline' => (int) ($row['show_online'] ?? 1) === 1,
                    'allowPm' => (int) ($row['allow_pm'] ?? 1) === 1,
                    'allowFriendRequests' => (int) ($row['allow_friend_requests'] ?? 1) === 1,
                    'showGifts' => (int) ($row['show_gifts'] ?? 1) === 1,
                    'showAchievements' => (int) ($row['show_achievements'] ?? 1) === 1,
                    'showPartyPublic' => (int) ($row['show_party_public'] ?? 1) === 1,
                    'theme' => (string) ($row['theme'] ?? 'auto'),
                    'uiSize' => (string) ($row['ui_size'] ?? 'normal'),
                    'sounds' => (int) ($row['sounds'] ?? 1) === 1,
                    'animations' => (int) ($row['animations'] ?? 1) === 1,
                    'notifyMessages' => (int) ($row['notify_messages'] ?? 1) === 1,
                    'notifyFriends' => (int) ($row['notify_friends'] ?? 1) === 1,
                    'notifyGifts' => (int) ($row['notify_gifts'] ?? 1) === 1,
                    'notifyClan' => (int) ($row['notify_clan'] ?? 1) === 1,
                    'notifySystem' => (int) ($row['notify_system'] ?? 1) === 1,
                    'profileBackground' => (string) ($row['profile_background'] ?? 'classic'),
                    'profileFrame' => (string) ($row['profile_frame'] ?? 'classic'),
                    'title' => (string) ($row['profile_title'] ?? ''),
                ];
            }
        }

        if ($this->tableExists('user_profile_settings')) {
            $stmt = $this->db->prepare(
                'SELECT show_party_public FROM user_profile_settings WHERE user_id = :user LIMIT 1'
            );
            $stmt->execute(['user' => $profileId]);
            $value = $stmt->fetchColumn();
            if ($value !== false) {
                $defaults['showPartyPublic'] = (int) $value === 1;
            }
        }

        return $defaults;
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

    private function socialState(int $viewerId, int $profileId, array $settings): array
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
            'canMessage' => $viewerId > 0 && $profileId > 0 && $viewerId !== $profileId && $settings['allowPm'],
            'canBattle' => $viewerId > 0 && $profileId > 0 && $viewerId !== $profileId,
            'canRequestFriend' => $status === 'none' && $settings['allowFriendRequests'],
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
            $key = (string) $row['badge_key'];
            $title = (string) $row['title'];
            $leader = (string) ($row['leader_name'] ?? '');
            $badges[] = [
                'id' => (int) $row['id'],
                'key' => $key,
                'title' => $title,
                'leader' => $leader,
                'description' => $this->gymBadgeDescription($key, $title, $leader),
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

    private function gymBadgeDescription(string $key, string $title, string $leader): string
    {
        $leaderLabel = match ($key) {
            'boulder' => 'Брока',
            'cascade' => 'Мисти',
            'thunder' => 'Лейтенанта Сёрджа',
            'rainbow' => 'Эрики',
            'soul' => 'Коги',
            'marsh' => 'Сабрины',
            'volcano' => 'Блейна',
            'earth' => 'Джованни',
            default => $leader,
        };
        $type = match ($key) {
            'boulder' => 'каменного гим-лидера',
            'cascade' => 'водного гим-лидера',
            'thunder' => 'электрического гим-лидера',
            'rainbow' => 'травяного гим-лидера',
            'soul' => 'ядовитого гим-лидера',
            'marsh' => 'психического гим-лидера',
            'volcano' => 'огненного гим-лидера',
            'earth' => 'земляного гим-лидера',
            default => 'гим-лидера',
        };

        return $leaderLabel !== '' ? $title . ' — значок ' . $type . ' ' . $leaderLabel . '.' : $title . ' — значок ' . $type . '.';
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
