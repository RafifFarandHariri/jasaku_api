<?php
// api/user/become_provider.php
// Backwards-compatible endpoint used by older clients.
// Expects JSON body: { "user_id": 123, "description": "..." }
require_once __DIR__ . '/../db.php';

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
$desc = isset($data['description']) ? trim($data['description']) : null;
if (!$userId || !$desc) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing user_id or description']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE users SET role = ?, provider_description = ?, provider_since = ? WHERE id = ?');
    $now = date('Y-m-d H:i:s');
    $stmt->execute(['provider', $desc, $now, $userId]);
    echo json_encode(['success' => true, 'message' => 'Permohonan menjadi penyedia berhasil']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

?>