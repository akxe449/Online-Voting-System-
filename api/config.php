<?php
// ============================================================
// TEMPORARY STUB — Person A owns the real version of this file
// (their Day 1 task: .env + PDO connection). This exists only
// so Person B's endpoints are independently testable today.
// Replace this whole file when A's branch merges — don't hand-
// merge the two, just take A's.
// ============================================================

function getPDO(): PDO
{
    static $pdo;
    if ($pdo === null) {
        $host = 'localhost';
        $db   = 'voting_system';
        $user = 'root';
        $pass = ''; 

        $pdo = new PDO(
            "mysql:host={$host};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}
