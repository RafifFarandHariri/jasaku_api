<?php
// Backwards-compatible endpoint for POST /api/auth/login.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../handlers/auth.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); echo json_encode(['ok'=>true]); exit; }

$input = getJsonInput();
if ($input === null) $input = $_POST;

// Call the internal login function defined in handlers/auth.php
try {
    // handlers/auth.php expects $_GET['action']=='login' when handleAuth used,
    // but loginUser can be called directly.
    loginUser($pdo, $input);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>