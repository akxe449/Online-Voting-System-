-- ============================================================
-- CANDIDATE — added for Day 2 (candidate-list screen needs data
-- to display). Belongs conceptually with the joint ELECTION
-- table since it's public, non-identity, non-vote data.
-- ============================================================

CREATE TABLE IF NOT EXISTS CANDIDATE (
    candidate_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    election_id   INT UNSIGNED NOT NULL,
    name          VARCHAR(100) NOT NULL,
    FOREIGN KEY (election_id) REFERENCES ELECTION(election_id)
) ENGINE=InnoDB;
