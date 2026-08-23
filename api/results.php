<?php
// GET /results?election_id=1
header('Content-Type: application/json');
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

echo json_encode([
    'election_id' => (int) $electionId,
    'total_ballots' => array_sum($tally),
    'tally' => $tally,
]);
