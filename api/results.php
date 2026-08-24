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

// Start from the full candidate list for this election, not just the ones
// that received votes -- otherwise a candidate with 0 votes silently
// vanishes from the response instead of showing 0.
$candidatesStmt = $pdo->prepare('SELECT candidate_id, name FROM CANDIDATE WHERE election_id = :election_id');
$candidatesStmt->execute([':election_id' => $electionId]);
$candidates = $candidatesStmt->fetchAll();

$counts = [];
foreach ($candidates as $c) {
    $counts[(string) $c['candidate_id']] = 0;
}

$stmt = $pdo->prepare(
    'SELECT b.encrypted_choice
     FROM BALLOT b
     JOIN CREDENTIAL_TOKEN ct ON ct.token_hash = b.token_hash
     WHERE ct.election_id = :election_id'
);
$stmt->execute([':election_id' => $electionId]);

$unrecognized = 0;
foreach ($stmt->fetchAll() as $row) {
    $choice = decryptChoice($row['encrypted_choice']);
    if ($choice === false || !array_key_exists($choice, $counts)) {
        $unrecognized++; // corrupted/tampered row, or a candidate_id not in CANDIDATE
        continue;
    }
    $counts[$choice]++;
}

$totalBallots = array_sum($counts);

$tally = [];
foreach ($candidates as $c) {
    $count = $counts[(string) $c['candidate_id']];
    $tally[] = [
        'candidate_id' => (int) $c['candidate_id'],
        'name' => $c['name'],
        'votes' => $count,
        'percentage' => $totalBallots > 0 ? round(($count / $totalBallots) * 100, 1) : 0,
    ];
}

// Winner(s) first, ties broken by candidate_id for stable ordering
usort($tally, fn($a, $b) => $b['votes'] <=> $a['votes'] ?: $a['candidate_id'] <=> $b['candidate_id']);

echo json_encode([
    'election_id' => (int) $electionId,
    'total_ballots' => $totalBallots,
    'unrecognized_ballots' => $unrecognized, // should always be 0 in an untampered election
    'tally' => $tally,
]);
