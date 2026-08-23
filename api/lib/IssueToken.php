<?php
require_once __DIR__ . '/../config.php';

/**
 * Issues one single-use anonymous voting token for an election.
 *
 * THE SEAM: this function accepts ONLY $electionId. It must never be
 * changed to accept voter_id, phone_number, or anything else that could
 * identify a person — that constraint is the entire point of the design.
 * Whatever Person A's code knows about the voter stops at this call.
 *
 * @param int $electionId
 * @return string token_hash — a 64-char hex bearer token. This exact
 *                value is what gets handed back to the voter's app and
 *                later submitted to /cast-vote. Nothing else is stored
 *                alongside it that could trace it back to a voter.
 */
function issueToken(int $electionId): string
{
    $pdo = getPDO();

    $tokenHash = bin2hex(random_bytes(32)); // 256-bit random bearer token

    $stmt = $pdo->prepare(
        'INSERT INTO CREDENTIAL_TOKEN (token_hash, election_id, used) VALUES (:token_hash, :election_id, 0)'
    );
    $stmt->execute([
        ':token_hash' => $tokenHash,
        ':election_id' => $electionId,
    ]);

    return $tokenHash;
}
