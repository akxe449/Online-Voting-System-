-- ============================================================
-- Dummy seed data so Person B can test independently before
-- Person A's registration flow exists. Person A will seed real
-- ELECTION_TYPE/ELECTION rows later — these are placeholders.
-- ============================================================

INSERT INTO ELECTION_TYPE (election_type_id, name, rules_config)
VALUES (1, 'Student Council', JSON_OBJECT())
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO ELECTION (election_id, election_type_id, title, start_time, end_time)
VALUES (1, 1, 'Demo Election 2026', NOW(), NOW() + INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Three dummy tokens: two unused, one pre-used (to test the "already voted" path)
INSERT INTO CREDENTIAL_TOKEN (token_hash, election_id, used) VALUES
    (SHA2('demo-token-unused-1', 256), 1, 0),
    (SHA2('demo-token-unused-2', 256), 1, 0),
    (SHA2('demo-token-already-used', 256), 1, 1)
ON DUPLICATE KEY UPDATE used = VALUES(used);
