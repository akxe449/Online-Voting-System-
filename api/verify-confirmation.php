<?php
// GET /verify-confirmation?code=ABC123
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$code = $_GET['code'] ?? null;
if (!$code) {
    http_response_code(400);
    echo json_encode(['error' => 'code is required']);
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT log_id, created_at FROM AUDIT_LOG WHERE confirmation_code = :code');
$stmt->execute([':code' => $code]);
$row = $stmt->fetch();

echo json_encode([
    'recorded' => $row !== false,
    'logged_at' => $row['created_at'] ?? null,
]);
