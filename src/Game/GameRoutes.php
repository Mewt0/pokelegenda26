<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class GameRoutes
{
    public const LEGACY_TO_NEW = [
        'start' => '/game',
        'live' => '/game',
        'map' => '/game',
        'char' => '/game/location',
        'charWork' => '/game',
        'chat' => '/game/chat',
        'mapusers' => '/game/location/users',
        'buttons' => '/game/actions',
        'gameload' => '/game/events',
        'fight_pve' => '/game/battle/pve',
        'fight_pvp' => '/game/battle/pvp',
        'trenInfo' => '/game/trainers',
        'pokedex' => '/game/pokedex',
        'atk' => '/game/attacks',
        'friends' => '/game/friends',
        'quest_list' => '/game/quests',
        'moderpanel' => '/game/moderation',
        'admingo' => '/game/admin',
        'pokemon' => '/game/pokemon',
        'sends' => '/game/messages',
        'users' => '/game/trainers',
        'items' => '/game/items',
        'eventsNewYear' => '/game/events/new-year',
        'eggs' => '/game/eggs',
        'profile' => '/game/profile',
        'diamond_shop' => '/game/diamond-shop',
        'rinok' => '/game/market/items',
        'clans' => '/game/clans',
        'pokerinok' => '/game/market/pokemon',
    ];

    public const MODULES = [
        'location' => ['title' => 'Локация', 'status' => 'partial', 'legacy' => 'char'],
        'chat' => ['title' => 'Чат', 'status' => 'todo', 'legacy' => 'chat'],
        'location/users' => ['title' => 'Игроки на локации', 'status' => 'partial', 'legacy' => 'mapusers'],
        'actions' => ['title' => 'Панель действий', 'status' => 'todo', 'legacy' => 'buttons'],
        'events' => ['title' => 'Игровые события', 'status' => 'todo', 'legacy' => 'gameload'],
        'battle/pve' => ['title' => 'PvE бой', 'status' => 'todo', 'legacy' => 'fight_pve'],
        'battle/pvp' => ['title' => 'PvP бой', 'status' => 'todo', 'legacy' => 'fight_pvp'],
        'trainers' => ['title' => 'Тренеры', 'status' => 'todo', 'legacy' => 'users/trenInfo'],
        'pokedex' => ['title' => 'Покедекс', 'status' => 'todo', 'legacy' => 'pokedex'],
        'attacks' => ['title' => 'Атаки', 'status' => 'todo', 'legacy' => 'atk'],
        'friends' => ['title' => 'Друзья', 'status' => 'todo', 'legacy' => 'friends'],
        'quests' => ['title' => 'Квесты', 'status' => 'todo', 'legacy' => 'quest_list'],
        'moderation' => ['title' => 'Модерация', 'status' => 'todo', 'legacy' => 'moderpanel'],
        'admin' => ['title' => 'Админка', 'status' => 'todo', 'legacy' => 'admingo'],
        'pokemon' => ['title' => 'Покемоны', 'status' => 'todo', 'legacy' => 'pokemon'],
        'messages' => ['title' => 'Сообщения', 'status' => 'todo', 'legacy' => 'sends'],
        'items' => ['title' => 'Инвентарь', 'status' => 'todo', 'legacy' => 'items'],
        'events/new-year' => ['title' => 'Новогодние события', 'status' => 'todo', 'legacy' => 'eventsNewYear'],
        'eggs' => ['title' => 'Яйца', 'status' => 'todo', 'legacy' => 'eggs'],
        'profile' => ['title' => 'Профиль', 'status' => 'todo', 'legacy' => 'profile'],
        'diamond-shop' => ['title' => 'Алмазный магазин', 'status' => 'todo', 'legacy' => 'diamond_shop'],
        'market/items' => ['title' => 'Рынок предметов', 'status' => 'todo', 'legacy' => 'rinok'],
        'clans' => ['title' => 'Кланы', 'status' => 'todo', 'legacy' => 'clans'],
        'market/pokemon' => ['title' => 'Рынок покемонов', 'status' => 'todo', 'legacy' => 'pokerinok'],
    ];

    public static function newPathForLegacy(string $go): ?string
    {
        return self::LEGACY_TO_NEW[$go] ?? null;
    }

    public static function module(string $slug): ?array
    {
        return self::MODULES[$slug] ?? null;
    }
}
