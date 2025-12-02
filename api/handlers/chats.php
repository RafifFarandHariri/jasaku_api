<?php
// handlers/chats.php
function handleChatsResource($pdo, $method, $id, $input) {
    if ($method === 'GET') {
        if ($id) { $stmt = $pdo->prepare('SELECT * FROM chats WHERE id = ?'); $stmt->execute([$id]); respond($stmt->fetch() ?: []); }

        // return all messages for a conversation
        if (isset($_GET['conversationId'])) {
            $stmt = $pdo->prepare('SELECT * FROM chats WHERE conversationId = ? ORDER BY timestamp ASC');
            $stmt->execute([$_GET['conversationId']]); respond($stmt->fetchAll());
        }

        // return summarized conversations for a given user id. Frontend should
        // generate a deterministic conversationId containing both user ids
        // (for example: "u12_u34") so we can match with LIKE.
        if (isset($_GET['conversationsFor'])) {
            $user = $_GET['conversationsFor'];
            $like = "%" . $user . "%";
            $sql = "SELECT t.conversationId, t.text as lastMessage, t.timestamp as lastTimestamp, t.senderName
                      FROM chats t
                      JOIN (
                        SELECT conversationId, MAX(timestamp) as maxt
                        FROM chats
                        WHERE conversationId IS NOT NULL AND conversationId LIKE ?
                        GROUP BY conversationId
                      ) m ON t.conversationId = m.conversationId AND t.timestamp = m.maxt
                      ORDER BY t.timestamp DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$like]);
            $rows = $stmt->fetchAll();
            // return also conversationId for UI
            respond($rows);
        }

        // Provide a helper to compute/find a deterministic conversation id
        // between two participants. Call as:
        //   GET api.php?resource=chats&conversationBetween=<a>,<b>
        // The server will canonicalize the two tokens and return the id.
        if (isset($_GET['conversationBetween'])) {
            $pair = $_GET['conversationBetween'];
            $parts = array_filter(array_map('trim', explode(',', $pair)));
            if (count($parts) >= 2) {
                // Attempt to resolve each token to a numeric user id when possible.
                $resolved = [];
                foreach (array_slice($parts, 0, 2) as $p) {
                    $p = trim($p);
                    // if numeric, keep as-is
                    if (preg_match('/^\d+$/', $p)) {
                        $resolved[] = $p;
                        continue;
                    }
                    // try to find user by exact email or exact nama
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR nama = ? LIMIT 1');
                    $stmt->execute([$p, $p]);
                    $row = $stmt->fetch();
                    if ($row && isset($row['id'])) {
                        $resolved[] = $row['id'];
                        continue;
                    }
                    // try a LIKE match on name
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE nama LIKE ? LIMIT 1');
                    $stmt->execute(["%$p%"]); $row = $stmt->fetch();
                    if ($row && isset($row['id'])) { $resolved[] = $row['id']; continue; }

                    // fallback: sanitize token and use it
                    $san = preg_replace('/[^A-Za-z0-9_]/', '', $p);
                    if ($san === '') $san = bin2hex(random_bytes(4));
                    $resolved[] = $san;
                }

                // canonicalize ordering
                sort($resolved, SORT_STRING);
                $conversationId = 'conv_' . $resolved[0] . '_' . $resolved[1];
                respond(['conversationId' => $conversationId]);
            } else {
                respond(['error' => 'conversationBetween must contain two tokens separated by comma'], 400);
            }
        }

        respond($pdo->query('SELECT * FROM chats ORDER BY timestamp DESC')->fetchAll());
    }
    if ($method === 'POST') {
        $idVal = $input['id'] ?? bin2hex(random_bytes(8));
        $sql = 'INSERT INTO chats (id, conversationId, text, isMe, timestamp, type, senderName, serviceId, proposedPrice, offerId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([
            $idVal,
            $input['conversationId'] ?? null,
            $input['text'] ?? null,
            isset($input['isMe']) ? (int)$input['isMe'] : 0,
            $input['timestamp'] ?? date('c'),
            $input['type'] ?? 0,
            $input['senderName'] ?? null,
            $input['serviceId'] ?? null,
            $input['proposedPrice'] ?? null,
            $input['offerId'] ?? null,
        ]);
        respond(['id' => $idVal], 201);
    }
    if ($method === 'PUT') {
        if (!$id) respond(['error'=>'Missing id'],400);
        $parts=[];$params=[];
        foreach (['conversationId','text','isMe','timestamp','type','senderName','serviceId','proposedPrice','offerId'] as $f) {
            if (isset($input[$f])) { $parts[] = "`$f` = ?"; $params[] = $input[$f]; }
        }
        if (empty($parts)) respond(['ok'=>true]);
        $params[] = $id; $pdo->prepare('UPDATE chats SET '.implode(',', $parts).' WHERE id = ?')->execute($params); respond(['ok'=>true]);
    }
    if ($method === 'DELETE') { if (!$id) respond(['error'=>'Missing id'],400); $pdo->prepare('DELETE FROM chats WHERE id = ?')->execute([$id]); respond(['ok'=>true]); }
}

?>
