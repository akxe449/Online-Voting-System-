<?php

/**
 * Appends one tamper-evident row to AUDIT_LOG, chained to the previous
 * row's hash. MUST be called from inside the same transaction as the
 * BALLOT insert it documents, so the two commit or roll back together.
 *
 * @param PDO $pdo An already-open transaction (do not open a new one here)
 * @param string $confirmationCode The confirmation code just generated for this ballot
 */
function appendAuditLog(PDO $pdo, string $confirmationCode): void
{
    $prev = $pdo->query('SELECT row_hash FROM AUDIT_LOG ORDER BY log_id DESC LIMIT 1')->fetchColumn();
    $prevHash = ($prev !== false) ? $prev : str_repeat('0', 64); // genesis row chains to a zero-hash

    $rowHash = hash('sha256', $prevHash . $confirmationCode . microtime());

    $stmt = $pdo->prepare(
        'INSERT INTO AUDIT_LOG (confirmation_code, row_hash, prev_hash) VALUES (:code, :row_hash, :prev_hash)'
    );
    $stmt->execute([
        ':code' => $confirmationCode,
        ':row_hash' => $rowHash,
        ':prev_hash' => $prevHash,
    ]);
}
