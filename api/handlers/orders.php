<?php
function handleOrdersResource($pdo, $method, $id, $input) {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
            $stmt->execute([$id]);
            respond($stmt->fetch() ?: []);
        }

        // Optional filter by customerId
        if (isset($_GET['customerId'])) {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE customerId = ? ORDER BY orderDate DESC');
            $stmt->execute([$_GET['customerId']]);
            respond($stmt->fetchAll());
        }

        // Optional filter by sellerId (return orders for a specific seller)
        if (isset($_GET['sellerId']) && !isset($_GET['count'])) {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE sellerId = ? ORDER BY orderDate DESC');
            $stmt->execute([$_GET['sellerId']]);
            respond($stmt->fetchAll());
        }

        // Count incoming orders for a provider/seller: ?sellerId=...&count=1
        if (isset($_GET['sellerId']) && isset($_GET['count'])) {
            if (isset($_GET['status'])) {
                $stmt = $pdo->prepare('SELECT COUNT(*) as c FROM orders WHERE sellerId = ? AND status = ?');
                $stmt->execute([$_GET['sellerId'], $_GET['status']]);
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) as c FROM orders WHERE sellerId = ?');
                $stmt->execute([$_GET['sellerId']]);
            }
            $row = $stmt->fetch();
            respond(['count' => (int)($row['c'] ?? 0)]);
        }

        // Default: return all orders
        respond($pdo->query('SELECT * FROM orders ORDER BY orderDate DESC')->fetchAll());
    }

    if ($method === 'POST') {
        // Create new order
        $idVal = $input['id'] ?? bin2hex(random_bytes(8));
        $sql = 'INSERT INTO orders (id, serviceId, serviceTitle, sellerId, sellerName, customerId, customerName, price, quantity, notes, status, orderDate, deadline, completedDate, paymentMethod, isPaid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idVal,
            $input['serviceId'] ?? null,
            $input['serviceTitle'] ?? null,
            $input['sellerId'] ?? null,
            $input['sellerName'] ?? null,
            $input['customerId'] ?? null,
            $input['customerName'] ?? null,
            $input['price'] ?? 0,
            $input['quantity'] ?? 1,
            $input['notes'] ?? null,
            $input['status'] ?? 0,
            $input['orderDate'] ?? date('c'),
            $input['deadline'] ?? null,
            $input['completedDate'] ?? null,
            $input['paymentMethod'] ?? null,
            isset($input['isPaid']) ? (int)$input['isPaid'] : 0,
        ]);
        respond(['id' => $idVal], 201);
    }

    if ($method === 'PUT') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $parts=[];$params=[];
        foreach (['serviceId','serviceTitle','sellerId','sellerName','customerId','customerName','price','quantity','notes','status','orderDate','deadline','completedDate','paymentMethod','isPaid'] as $f) {
            if (isset($input[$f])) { $parts[] = "`$f` = ?"; $params[] = $input[$f]; }
        }
        if (empty($parts)) respond(['ok'=>true]);
        $params[] = $id;
        $pdo->prepare('UPDATE orders SET '.implode(',', $parts).' WHERE id = ?')->execute($params);

        // If the order status was updated to 'completed' (index 4), increment the
        // corresponding service's `sold` count so statistics reflect completed sales.
        if (isset($input['status']) && intval($input['status']) === 4) {
            // Fetch the order to get serviceId and quantity (in case they weren't in input)
            $stmt = $pdo->prepare('SELECT serviceId, quantity FROM orders WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['serviceId'])) {
                $serviceId = $row['serviceId'];
                $quantity = isset($input['quantity']) ? intval($input['quantity']) : intval($row['quantity'] ?? 1);
                try {
                    $pdo->prepare('UPDATE services SET sold = COALESCE(sold,0) + ? WHERE id = ?')->execute([$quantity, $serviceId]);
                } catch (Exception $e) {
                    // ignore errors updating services.sold
                }
            }
        }

        // Add an order_progress entry when status is updated so the customer
        // can see the change (and for basic history). Map status to a
        // human-friendly description and insert a progress row.
        if (isset($input['status'])) {
            try {
                $statusInt = intval($input['status']);
                $desc = 'Status diubah: ' . $statusInt;
                switch ($statusInt) {
                    case 0: $desc = 'Pending'; break;
                    case 1: $desc = 'Menunggu Konfirmasi'; break;
                    case 2: $desc = 'Sedang Dikerjakan'; break;
                    case 3: $desc = 'Siap Review'; break;
                    case 4: $desc = 'Sudah Selesai'; break;
                    case 5: $desc = 'Dibatalkan'; break;
                }
                // if sellerName provided in input, include it
                $who = isset($input['sellerName']) ? $input['sellerName'] : (isset($input['customerName']) ? $input['customerName'] : null);
                if ($who) $desc .= ' oleh ' . $who;

                $progressId = bin2hex(random_bytes(8));
                $orderId = $id;
                $percentage = 0;
                // approximate a percentage for UI (optional)
                if ($statusInt === 2) $percentage = 50;
                if ($statusInt === 3) $percentage = 75;
                if ($statusInt === 4) $percentage = 100;

                $pdo->prepare('INSERT INTO order_progress (id, orderId, percentage, description, timestamp) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$progressId, $orderId, $percentage, $desc, date('c')]);
            } catch (Exception $e) {
                // ignore any failure to insert progress
            }
        }

        respond(['ok'=>true]);
    }

    if ($method === 'DELETE') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $pdo->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
        respond(['ok'=>true]);
    }
}

?>
