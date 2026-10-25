-- Import-only world registry. Missing legacy references intentionally have no FK.
CREATE TABLE IF NOT EXISTS world_graph_sources (
  location_id INT NOT NULL,
  sort_order INT NOT NULL,
  PRIMARY KEY (location_id),
  UNIQUE KEY uq_world_source_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS world_graph_edges (
  source_id INT NOT NULL,
  target_id INT NOT NULL,
  sort_order INT NOT NULL,
  PRIMARY KEY (source_id, sort_order),
  UNIQUE KEY uq_world_directed_edge (source_id, target_id),
  KEY idx_world_target (target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS world_location_content (
  location_id INT NOT NULL,
  sort_order INT NOT NULL,
  content_json LONGTEXT NOT NULL,
  source_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (location_id),
  UNIQUE KEY uq_world_content_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (name, value, updated_by, updated_at)
VALUES ('world.graph.db', '0', 0, UNIX_TIMESTAMP()),
       ('world.content.db', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE name = VALUES(name);
