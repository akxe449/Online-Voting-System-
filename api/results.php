<?php
// GET /results?election_id=1
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // dev-only
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Crypto.php';

$electionId = $_GET['election_id'] ?? null;
if (!$electionId) {
    http_response_code(400);
    echo json_encode(['error' => 'election_id is required']);
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare(
    'SELECT b.encrypted_choice
     FROM BALLOT b
     JOIN CREDENTIAL_TOKEN ct ON ct.token_hash = b.token_hash
     WHERE ct.election_id = :election_id'
);
$stmt->execute([':election_id' => $electionId]);

$tally = [];
foreach ($stmt->fetchAll() as $row) {
    $choice = decryptChoice($row['encrypted_choice']);
    if ($choice === false) {
        continue; // corrupted/tampered row -- shows up as a gap between this count and AUDIT_LOG
    }
    $tally[$choice] = ($tally[$choice] ?? 0) + 1;
}

// Resolve candidate_id -> name for a friendlier response (cheap since the
// candidate list per election is always small)
$namesStmt = $pdo->prepare('SELECT candidate_id, name FROM CANDIDATE WHERE election_id = :election_id');
$namesStmt->execute([':election_id' => $electionId]);
$names = [];
foreach ($namesStmt->fetchAll() as $row) {
    $names[(string) $row['candidate_id']] = $row['name'];
}

$namedTally = [];
foreach ($tally as $candidateId => $count) {
    $label = $names[$candidateId] ?? "Unknown candidate ({$candidateId})";
    $namedTally[$label] = $count;
}

echo json_encode([
    'election_id' => (int) $electionId,
    'total_ballots' => array_sum($tally),
    'tally' => $namedTally,
]);
