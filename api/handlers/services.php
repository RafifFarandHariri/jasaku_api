<?php
// handlers/services.php
function handleServicesResource($pdo, $method, $id, $input) {
    // ensure packages table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS services_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serviceId INT NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        price INT DEFAULT 0,
        delivery_days INT DEFAULT 0,
        revisions INT DEFAULT 0,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
            $stmt->execute([$id]);
            $svc = $stmt->fetch() ?: [];
            if (!empty($svc)) {
                $ps = $pdo->prepare('SELECT * FROM services_packages WHERE serviceId = ? ORDER BY id ASC');
                $ps->execute([$svc['id']]);
                $svc['packages'] = $ps->fetchAll();
            }
            respond($svc);
        }
        $rows = $pdo->query('SELECT * FROM services ORDER BY id DESC')->fetchAll();
        // attach packages for each service (optional)
        foreach ($rows as &$r) {
            $ps = $pdo->prepare('SELECT * FROM services_packages WHERE serviceId = ? ORDER BY id ASC');
            $ps->execute([$r['id']]);
            $r['packages'] = $ps->fetchAll();
        }
        respond($rows);
    }
    if ($method === 'POST') {
        $sql = 'INSERT INTO services (title, seller, price, description, sold, rating, reviews, is_verified, has_fast_response, category, serviceType) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([
            $input['title'] ?? '',
            $input['seller'] ?? '',
            $input['price'] ?? 0,
            $input['description'] ?? null,
            $input['sold'] ?? 0,
            $input['rating'] ?? 0,
            $input['reviews'] ?? 0,
            isset($input['is_verified']) ? (int)$input['is_verified'] : 1,
            isset($input['has_fast_response']) ? (int)$input['has_fast_response'] : 1,
            $input['category'] ?? null,
            $input['serviceType'] ?? null,
        ]);
        $serviceId = $pdo->lastInsertId();

        // If packages provided, validate and insert into services_packages
        if (!empty($input['packages']) && is_array($input['packages'])) {
            // validate packages array
            foreach ($input['packages'] as $idx => $pkg) {
                if (!is_array($pkg)) {
                    respond(['success' => false, 'message' => "Invalid package at index $idx"], 400);
                }
                $title = isset($pkg['title']) ? trim($pkg['title']) : '';
                $price = isset($pkg['price']) ? (int)$pkg['price'] : 0;
                $delivery = isset($pkg['delivery_days']) ? (int)$pkg['delivery_days'] : 0;
                $revisions = isset($pkg['revisions']) ? (int)$pkg['revisions'] : 0;
                if ($title === '') {
                    respond(['success' => false, 'message' => "Package title is required at index $idx"], 400);
                }
                if ($price < 0) {
                    respond(['success' => false, 'message' => "Package price must be >= 0 at index $idx"], 400);
                }
                if ($delivery < 0) {
                    respond(['success' => false, 'message' => "Package delivery_days must be >= 0 at index $idx"], 400);
                }
                if ($revisions < 0) {
                    respond(['success' => false, 'message' => "Package revisions must be >= 0 at index $idx"], 400);
                }
            }

            $ins = $pdo->prepare('INSERT INTO services_packages (serviceId, title, price, delivery_days, revisions, description) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($input['packages'] as $pkg) {
                $title = isset($pkg['title']) ? $pkg['title'] : null;
                $price = isset($pkg['price']) ? (int)$pkg['price'] : 0;
                $delivery = isset($pkg['delivery_days']) ? (int)$pkg['delivery_days'] : 0;
                $revisions = isset($pkg['revisions']) ? (int)$pkg['revisions'] : 0;
                $desc = isset($pkg['description']) ? $pkg['description'] : null;
                $ins->execute([$serviceId, $title, $price, $delivery, $revisions, $desc]);
            }
        }

        respond(['id' => $serviceId], 201);
    }
    if ($method === 'PUT') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $parts=[];$params=[];
        foreach (['title','seller','price','description','sold','rating','reviews','is_verified','has_fast_response','category','serviceType'] as $f) {
            if (isset($input[$f])) { $parts[] = "`$f` = ?"; $params[] = $input[$f]; }
        }
        if (empty($parts)) respond(['ok'=>true]);
        $params[] = $id;
        $pdo->prepare('UPDATE services SET '.implode(',', $parts).' WHERE id = ?')->execute($params);
        respond(['ok'=>true]);
    }
    if ($method === 'DELETE') { if (!$id) respond(['error'=>'Missing id'],400); $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]); respond(['ok'=>true]); }
}

?>
