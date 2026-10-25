<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;
use RuntimeException;

/** DB-only world data. No legacy/config fallback is permitted inside these readers. */
final class WorldDataRepository
{
    public function __construct(private PDO $db)
    {
    }

    public static function runtimeGraph(PDO $db, string $root): LocationGraph
    {
        return (new self($db))->flagEnabled('world.graph.db')
            ? LocationGraph::fromDatabase($db)
            : LocationGraph::fromLegacyData($root . '/include/data.world.php');
    }

    /** @return array<int, array<string, mixed>> */
    public static function runtimeContentData(PDO $db, string $root): array
    {
        $reader = new self($db);
        if ($reader->flagEnabled('world.content.db')) {
            return $reader->locationContent();
        }
        $path = $root . '/config/location_content.php';
        $content = is_file($path) ? require $path : [];
        return is_array($content) ? $content : [];
    }

    public static function runtimeContent(PDO $db, string $root): LocationContentRepository
    {
        return new LocationContentRepository(self::runtimeContentData($db, $root));
    }

    public function flagEnabled(string $name): bool
    {
        if (!in_array($name, ['world.graph.db', 'world.content.db'], true)) {
            throw new RuntimeException('Unknown world reader flag');
        }
        $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        return (string) $stmt->fetchColumn() === '1';
    }

    /** @return array<int, list<int>> */
    public function graphEdges(): array
    {
        $edges = [];
        foreach ($this->db->query('SELECT location_id FROM world_graph_sources ORDER BY sort_order')->fetchAll() as $row) {
            $edges[(int) $row['location_id']] = [];
        }
        foreach ($this->db->query('SELECT source_id, target_id FROM world_graph_edges ORDER BY source_id, sort_order')->fetchAll() as $row) {
            $from = (int) $row['source_id'];
            if (!array_key_exists($from, $edges)) {
                throw new RuntimeException('World edge source is missing');
            }
            $edges[$from][] = (int) $row['target_id'];
        }
        if ($edges === []) {
            throw new RuntimeException('World graph has not been imported');
        }
        return $edges;
    }

    /** @return array<int, array<string, mixed>> */
    public function locationContent(): array
    {
        $locations = [];
        foreach ($this->db->query('SELECT location_id, content_json, source_hash FROM world_location_content ORDER BY sort_order')->fetchAll() as $row) {
            if (!hash_equals((string) $row['source_hash'], hash('sha256', (string) $row['content_json']))) {
                throw new RuntimeException('World content integrity check failed');
            }
            $content = json_decode($row['content_json'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($content)) {
                throw new RuntimeException('Invalid world content');
            }
            $locations[(int) $row['location_id']] = $content;
        }
        if ($locations === []) {
            throw new RuntimeException('World content has not been imported');
        }
        return $locations;
    }
}
