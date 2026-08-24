<?php
// ============================================================
// POST /cast-vote
// Body: { "token": "<token_hash>", "candidate_id": "<string>" }
//
// NOTE ON THE CONTRACT: the build plan says this accepts
// {token, encrypted_choice}. We're encrypting server-side here
// (the app sends the plain candidate_id, this endpoint encrypts
// before it ever touches disk) rather than asking the RN app to
// implement AES-GCM + key exchange in 3 days. Flagging this as a
// deliberate simplification, not a silent deviation -- swap this
// for client-side encryption later if you and Person A want the
// server to never see a plaintext choice at all.
//
// This is the load-bearing endpoint: one transaction guarantees
// exactly-one-ballot-per-token even under concurrent requests.
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // dev-only
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/AuditLog.php';
require_once __DIR__ . '/lib/Crypto.php';

$input = json_decode(file_get_contents('php://input'), true);
$tokenHash = $input['token'] ?? null;
$candidateId = $input['candidate_id'] ?? null;

if (!$tokenHash || !$candidateId) {
    http_response_code(400);
    echo json_encode(['error' => 'token and candidate_id are required']);
    exit;
}

$pdo = getPDO();

try {
    $pdo->beginTransaction();

    // Lock the token row. Any concurrent request for the SAME token now
    // blocks here until this transaction commits or rolls back -- that
    // block is what makes "exactly one vote per token" true under load,
    // not just true in the happy-path test.
    $stmt = $pdo->prepare('SELECT used FROM CREDENTIAL_TOKEN WHERE token_hash = :token FOR UPDATE');
    $stmt->execute([':token' => $tokenHash]);
    $row = $stmt->fetch();

    if ($row === false) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Unknown token']);
        exit;
    }

    if ((bool) $row['used']) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'This token has already been used to cast a vote']);
        exit;
    }

    $pdo->prepare('UPDATE CREDENTIAL_TOKEN SET used = 1 WHERE token_hash = :token')
        ->execute([':token' => $tokenHash]);

    $encryptedChoice = encryptChoice((string) $candidateId);
    $confirmationCode = strtoupper(bin2hex(random_bytes(5))); // 10 hex chars, voter-facing

    $pdo->prepare(
        'INSERT INTO BALLOT (token_hash, encrypted_choice, confirmation_code) VALUES (:token, :choice, :code)'
    )->execute([
        ':token' => $tokenHash,
        ':choice' => $encryptedChoice,
        ':code' => $confirmationCode,
    ]);

    appendAuditLog($pdo, $confirmationCode, $encryptedChoice);

    $pdo->commit();
    echo json_encode(['confirmation_code' => $confirmationCode]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Internal error', 'detail' => $e->getMessage()]);
}
