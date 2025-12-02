<?php
// handlers/users.php
// Functions to handle users resource

function handleUsersResource($pdo, $method, $id, $input) {
    // POST may be used for special actions (backwards-compatible)
    if ($method === 'POST') {
        if (isset($_GET['action']) && $_GET['action'] === 'become_provider') {
            _handleBecomeProvider($pdo, $input);
            return;
        }
        respond(['error' => 'Unsupported POST on users'], 400);
    }
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id, nrp, nama, email, phone, profile_image, role, is_verified_provider, provider_since, provider_description, created_at FROM users WHERE id = ?');
            $stmt->execute([$id]);
            respond($stmt->fetch() ?: []);
        }
        $stmt = $pdo->query('SELECT id, nrp, nama, email, phone, profile_image, role, is_verified_provider, provider_since, provider_description, created_at FROM users ORDER BY id DESC');
        respond($stmt->fetchAll());
    }

    if ($method === 'PUT') {
        if (!$id) respond(['error' => 'Missing id'], 400);
        $parts = [];$params = [];
        foreach (['nrp','nama','email','phone','profile_image','role','is_verified_provider','provider_since','provider_description'] as $f) {
            if (isset($input[$f])) { $parts[] = "`$f` = ?"; $params[] = $input[$f]; }
        }
        if (empty($parts)) respond(['ok'=>true]);
        $params[] = $id;
        $pdo->prepare('UPDATE users SET '.implode(',', $parts).' WHERE id = ?')->execute($params);
        respond(['ok' => true]);
    }

    if ($method === 'DELETE') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        respond(['ok'=>true]);
    }
}

// Support backwards-compatible "become_provider" action via router
// POST /api.php?resource=users&action=become_provider
function _handleBecomeProvider($pdo, $input) {
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
    $desc = isset($input['description']) ? trim($input['description']) : null;
    if (!$userId || !$desc) {
        respond(['success' => false, 'message' => 'Missing user_id or description'], 400);
    }
    try {
        $stmt = $pdo->prepare('UPDATE users SET role = ?, provider_description = ?, provider_since = ? WHERE id = ?');
        $now = date('Y-m-d H:i:s');
        $stmt->execute(['provider', $desc, $now, $userId]);
        respond(['success' => true, 'message' => 'Permohonan menjadi penyedia berhasil']);
    } catch (Exception $e) {
        respond(['success' => false, 'message' => 'DB error: ' . $e->getMessage()], 500);
    }
}


?>
