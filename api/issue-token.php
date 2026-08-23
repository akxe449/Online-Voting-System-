<?php
// ============================================================
// TEMPORARY endpoint, Day 1 only. In the real flow, issueToken()
// is called internally from Person A's otp-verify handler — it's
// never a public HTTP endpoint. This file exists purely so A can
// hit it from Postman today to confirm the seam works, before
// A's own code calls the function directly. Delete this file (or
// lock it behind an internal-only check) before Day 3.
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/lib/IssueToken.php';

$input = json_decode(file_get_contents('php://input'), true);
$electionId = $input['election_id'] ?? null;

if (!$electionId) {
    http_response_code(400);
    echo json_encode(['error' => 'election_id is required']);
    exit;
}

try {
    $token = issueToken((int) $electionId);
    echo json_encode(['token_hash' => $token]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
