-- ============================================================
-- PERSON B — Anonymous voting layer
-- No foreign key anywhere in this file points to VOTER,
-- VOTING_INTENT, or any identity table. That omission IS the
-- anonymization boundary — check this stays true on every PR.
-- ============================================================

CREATE TABLE IF NOT EXISTS CREDENTIAL_TOKEN (
    token_hash   CHAR(64) NOT NULL,
    election_id  INT UNSIGNED NOT NULL,
    issued_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    used         TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (token_hash),
    FOREIGN KEY (election_id) REFERENCES ELECTION(election_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS BALLOT (
    ballot_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash         CHAR(64) NOT NULL,
    encrypted_choice   TEXT NOT NULL,
    cast_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmation_code  VARCHAR(16) NOT NULL,
    UNIQUE KEY uq_ballot_token_hash (token_hash),        -- belt-and-braces: DB itself
    UNIQUE KEY uq_ballot_confirmation_code (confirmation_code), -- refuses a 2nd ballot per token
    FOREIGN KEY (token_hash) REFERENCES CREDENTIAL_TOKEN(token_hash)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS AUDIT_LOG (
    log_id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    confirmation_code  VARCHAR(16) NOT NULL,
    row_hash           CHAR(64) NOT NULL,
    prev_hash          CHAR(64) NOT NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_audit_confirmation_code (confirmation_code),
    FOREIGN KEY (confirmation_code) REFERENCES BALLOT(confirmation_code)
) ENGINE=InnoDB;
