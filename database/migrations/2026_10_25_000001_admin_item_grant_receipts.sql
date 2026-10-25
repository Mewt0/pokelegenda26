CREATE TABLE IF NOT EXISTS admin_grant_receipts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  admin_id INT NOT NULL,
  idempotency_key VARCHAR(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  payload_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  preview_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  correlation_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  target_user_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  before_qty BIGINT NOT NULL,
  after_qty BIGINT NOT NULL,
  reason VARCHAR(500) NOT NULL,
  result VARCHAR(24) NOT NULL,
  response_json LONGTEXT NOT NULL,
  created_at INT NOT NULL,
  completed_at INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_grant_key (admin_id, idempotency_key),
  UNIQUE KEY uq_admin_grant_public (public_id),
  UNIQUE KEY uq_admin_grant_preview (preview_hash),
  KEY idx_admin_grant_correlation (correlation_id),
  KEY idx_admin_grant_target (target_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (name, value, updated_by, updated_at)
VALUES ('admin.grant.v2', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE name = VALUES(name);
