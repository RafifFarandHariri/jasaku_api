<?php
// Simple DB test helper for local development. Not for production.
require_once __DIR__ . '/db.php';

try {
    // list databases and current connection info
    $stmt = $pdo->query('SHOW DATABASES');
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode([
        'ok' => true,
        'message' => 'Connected to database server',
        'db_host' => $DB_HOST ?? 'unknown',
        'db_name_configured' => $DB_NAME ?? 'unknown',
        'available_databases' => $dbs
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'message' => $e->getMessage()]);
}

?>