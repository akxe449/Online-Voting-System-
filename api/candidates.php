<?php
// GET /candidates.php?election_id=1
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // dev-only, see note in project README
require_once __DIR__ . '/config.php';

$electionId = $_GET['election_id'] ?? null;
if (!$electionId) {
    http_response_code(400);
    echo json_encode(['error' => 'election_id is required']);
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT candidate_id, name FROM CANDIDATE WHERE election_id = :election_id');
$stmt->execute([':election_id' => $electionId]);

echo json_encode($stmt->fetchAll());
