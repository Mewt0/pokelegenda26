CREATE TABLE IF NOT EXISTS starter_quest_reward_claims (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  quest_id INT NOT NULL,
  operation_key VARCHAR(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  reward_json TEXT NOT NULL,
  created_at INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_starter_user_quest (user_id,quest_id),
  UNIQUE KEY uq_starter_operation (operation_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE quest_definitions SET reward_json='{"items":{"1":5000,"3":10,"10":3,"15":1},"rank":1}',updated_at=UNIX_TIMESTAMP() WHERE id=1;
UPDATE quest_definitions SET reward_json='{"items":{"1":2000,"3":5,"10":2,"15":1},"rank":1}',updated_at=UNIX_TIMESTAMP() WHERE id=101;
UPDATE quest_definitions SET reward_json='{"items":{"1":3000,"90111":1},"rank":1}',updated_at=UNIX_TIMESTAMP() WHERE id=102;
UPDATE quest_definitions SET reward_json='{"items":{"1":2000,"3":5},"rank":1}',updated_at=UNIX_TIMESTAMP() WHERE id=103;
UPDATE quest_steps SET reward_json='{"items":{"1":5000,"3":10,"10":3,"15":1},"rank":1}' WHERE quest_id=1 AND step_no=(SELECT terminal.step_no FROM (SELECT MAX(step_no) AS step_no FROM quest_steps WHERE quest_id=1) terminal);
UPDATE quest_steps SET reward_json='{"items":{"1":2000,"3":5,"10":2,"15":1},"rank":1}' WHERE quest_id=101 AND step_no=2;
UPDATE quest_steps SET reward_json='{"items":{"1":3000,"90111":1},"rank":1}' WHERE quest_id=102 AND step_no=2;
UPDATE quest_steps SET reward_json='{"items":{"1":2000,"3":5},"rank":1}' WHERE quest_id=103 AND step_no=2;
