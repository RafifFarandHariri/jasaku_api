<?php
// handlers/reviews.php
function handleReviewsResource($pdo, $method, $id, $input) {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
            $stmt->execute([$id]);
            respond($stmt->fetch() ?: []);
        }
        if (isset($_GET['serviceId'])) {
            $stmt = $pdo->prepare('SELECT * FROM reviews WHERE serviceId = ? ORDER BY created_at DESC');
            $stmt->execute([$_GET['serviceId']]);
            respond($stmt->fetchAll());
        }
        $rows = $pdo->query('SELECT * FROM reviews ORDER BY created_at DESC')->fetchAll();
        respond($rows);
    }

    if ($method === 'POST') {
        // Expected: serviceId, userId, userName, rating (numeric), comment (optional)
        $idVal = $input['id'] ?? bin2hex(random_bytes(8));
        $sql = 'INSERT INTO reviews (id, serviceId, userId, userName, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idVal,
            $input['serviceId'] ?? null,
            $input['userId'] ?? null,
            $input['userName'] ?? null,
            $input['rating'] ?? 0,
            $input['comment'] ?? null,
            $input['created_at'] ?? date('c'),
        ]);

        // Recalculate aggregate on services table
        if (!empty($input['serviceId'])) {
            $sId = $input['serviceId'];
            $avgStmt = $pdo->prepare('SELECT AVG(rating) AS avgRating, COUNT(*) AS cnt FROM reviews WHERE serviceId = ?');
            $avgStmt->execute([$sId]);
            $row = $avgStmt->fetch();
            $avg = $row ? (float)$row['avgRating'] : 0.0;
            $cnt = $row ? (int)$row['cnt'] : 0;
            $update = $pdo->prepare('UPDATE services SET rating = ?, reviews = ? WHERE id = ?');
            $update->execute([$avg, $cnt, $sId]);
        }

        respond(['id' => $idVal], 201);
    }

    if ($method === 'DELETE') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
        respond(['ok'=>true]);
    }
}

?>
