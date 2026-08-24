<?php
// ============================================================
// verify.php — run this from the command line:  php verify.php
//
// Two checks, in order:
//   1. Hash-chain integrity: walks AUDIT_LOG from the first row,
//      recomputes each row's hash from prev_hash + confirmation_code +
//      the ballot's CURRENT encrypted_choice (pulled live from BALLOT),
//      and compares it to what's stored. A mismatch means either that
//      AUDIT_LOG row or the BALLOT row it documents was edited after
//      the fact -- this is your tamper-evidence proof for the demo.
//   2. Sanity check: count(BALLOT) should exactly equal
//      count(CREDENTIAL_TOKEN WHERE used = true). If cast-vote.php's
//      transaction is working correctly, these can never drift apart.
// ============================================================

require_once __DIR__ . '/config.php';

$pdo = getPDO();

echo "=== 1. Audit log hash-chain check ===\n\n";

$stmt = $pdo->query(
    'SELECT a.log_id, a.confirmation_code, a.row_hash, a.prev_hash, b.encrypted_choice
     FROM AUDIT_LOG a
     JOIN BALLOT b ON b.confirmation_code = a.confirmation_code
     ORDER BY a.log_id ASC'
);
$rows = $stmt->fetchAll();

if (count($rows) === 0) {
    echo "No audit log rows yet -- cast a vote first.\n";
} else {
    $expectedPrevHash = str_repeat('0', 64); // genesis
    $brokenCount = 0;

    foreach ($rows as $row) {
        $problems = [];

        if ($row['prev_hash'] !== $expectedPrevHash) {
            $problems[] = 'prev_hash does not match the previous row\'s row_hash (chain link broken -- a row may have been inserted, deleted, or reordered)';
        }

        $recomputed = hash('sha256', $row['prev_hash'] . $row['confirmation_code'] . $row['encrypted_choice']);
        if ($recomputed !== $row['row_hash']) {
            $problems[] = 'row_hash does not match recomputed hash (this row or its BALLOT row was edited after being logged)';
        }

        if (empty($problems)) {
            echo "  [OK]   log_id={$row['log_id']}  code={$row['confirmation_code']}\n";
        } else {
            $brokenCount++;
            echo "  [FAIL] log_id={$row['log_id']}  code={$row['confirmation_code']}\n";
            foreach ($problems as $p) {
                echo "         -> {$p}\n";
            }
        }

        $expectedPrevHash = $row['row_hash'];
    }

    echo "\n";
    if ($brokenCount === 0) {
        echo "Result: chain intact, " . count($rows) . " row(s) verified, no tampering detected.\n";
    } else {
        echo "Result: TAMPERING DETECTED in {$brokenCount} of " . count($rows) . " row(s).\n";
    }
}

echo "\n=== 2. Ballot / token sanity check ===\n\n";

$ballotCount = (int) $pdo->query('SELECT COUNT(*) FROM BALLOT')->fetchColumn();
$usedTokenCount = (int) $pdo->query('SELECT COUNT(*) FROM CREDENTIAL_TOKEN WHERE used = 1')->fetchColumn();

echo "  BALLOT rows:                  {$ballotCount}\n";
echo "  CREDENTIAL_TOKEN (used=true):  {$usedTokenCount}\n\n";

if ($ballotCount === $usedTokenCount) {
    echo "Result: match -- every used token produced exactly one ballot, and vice versa.\n";
} else {
    echo "Result: MISMATCH -- something is wrong with the cast-vote transaction logic.\n";
}
