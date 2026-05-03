<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final readonly class RankingRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function fighters(): array
    {
        return $this->db
            ->query('SELECT id, login, rang_b FROM users WHERE rang_b > 0 AND activation = 1 AND groups NOT IN (1,7) ORDER BY rang_b DESC LIMIT 10')
            ->fetchAll();
    }

    public function pokedex(): array
    {
        return $this->db
            ->query('SELECT id, login, count_poke FROM users WHERE activation = 1 AND id != 3 AND groups NOT IN (1,7) AND count_poke > 1 ORDER BY count_poke DESC LIMIT 10')
            ->fetchAll();
    }
}
