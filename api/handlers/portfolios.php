<?php
// handlers/portfolios.php
function handlePortfoliosResource($pdo, $method, $id, $input) {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM portfolios WHERE id = ?');
            $stmt->execute([$id]);
            respond($stmt->fetch() ?: []);
        }
        // filter by serviceId or sellerId
        if (isset($_GET['serviceId'])) {
            $stmt = $pdo->prepare('SELECT * FROM portfolios WHERE serviceId = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['serviceId']]);
            respond($stmt->fetchAll());
        }
        if (isset($_GET['sellerId'])) {
            $stmt = $pdo->prepare('SELECT * FROM portfolios WHERE sellerId = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['sellerId']]);
            respond($stmt->fetchAll());
        }
        respond($pdo->query('SELECT * FROM portfolios ORDER BY created_at DESC')->fetchAll());
    }

    if ($method === 'POST') {
        $idVal = $input['id'] ?? bin2hex(random_bytes(8));
        $sql = 'INSERT INTO portfolios (id, serviceId, sellerId, title, description, imageUrl) VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idVal,
            $input['serviceId'] ?? null,
            $input['sellerId'] ?? null,
            $input['title'] ?? null,
            $input['description'] ?? null,
            $input['imageUrl'] ?? null,
        ]);
        respond(['id' => $idVal], 201);
    }

    if ($method === 'PUT') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $parts=[];$params=[];
        foreach (['serviceId','sellerId','title','description','imageUrl'] as $f) {
            if (isset($input[$f])) { $parts[] = "`$f` = ?"; $params[] = $input[$f]; }
        }
        if (empty($parts)) respond(['ok'=>true]);
        $params[] = $id;
        $pdo->prepare('UPDATE portfolios SET '.implode(',', $parts).' WHERE id = ?')->execute($params);
        respond(['ok'=>true]);
    }

    if ($method === 'DELETE') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $pdo->prepare('DELETE FROM portfolios WHERE id = ?')->execute([$id]);
        respond(['ok'=>true]);
    }
}

?>