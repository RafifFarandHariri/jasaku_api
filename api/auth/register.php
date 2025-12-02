<?php
// Backwards-compatible endpoint for POST /api/auth/register.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../handlers/auth.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); echo json_encode(['ok'=>true]); exit; }

$input = getJsonInput();
if ($input === null) $input = $_POST;

try {
    registerUser($pdo, $input);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>