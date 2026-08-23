-- ============================================================
-- JOINT TABLES — written together, first 1.5 hrs of Day 1
-- Owned by neither A nor B specifically; both read from these.
-- ============================================================

CREATE TABLE IF NOT EXISTS ELECTION_TYPE (
    election_type_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    rules_config      JSON NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ELECTION (
    election_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    election_type_id  INT UNSIGNED NOT NULL,
    title             VARCHAR(200) NOT NULL,
    start_time        DATETIME NOT NULL,
    end_time          DATETIME NOT NULL,
    FOREIGN KEY (election_type_id) REFERENCES ELECTION_TYPE(election_type_id)
) ENGINE=InnoDB;
