<?php
// migrate_passwords.php
// Small helper to migrate plain `password` column into `password_hash` or
// to reset a single user's password. Run from CLI or via browser (careful).

require_once __DIR__ . '/../api/db.php';

function println($s) { echo $s . PHP_EOL; }

$argv_email = isset($argv[1]) ? $argv[1] : null;
$argv_pass = isset($argv[2]) ? $argv[2] : null;

// If run with two args: php migrate_passwords.php email@example.com newpassword
if ($argv_email && $argv_pass) {
    // Set/reset a single user's password
    $email = $argv_email;
    $new = $argv_pass;
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
    $stmt->execute([$hash, $email]);
    println("Updated password_hash for user: $email (rows: " . $stmt->rowCount() . ")");
    exit;
}

// Otherwise try to migrate `password` -> `password_hash` when a plain column exists
try {
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'")->fetch();
    if ($col) {
        println("Found plain 'password' column. Migrating values into 'password_hash'...");
        $stmt = $pdo->query("SELECT id, password FROM users WHERE (password IS NOT NULL AND password <> '') AND (password_hash IS NULL OR password_hash = '')");
        $rows = $stmt->fetchAll();
        $count = 0;
        $uStmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        foreach ($rows as $r) {
            $h = password_hash($r['password'], PASSWORD_DEFAULT);
            $uStmt->execute([$h, $r['id']]);
            $count += $uStmt->rowCount();
        }
        println("Migration complete. Updated $count rows.");
    } else {
        println("No plain 'password' column found. If users cannot login, you may need to set/reset passwords for specific accounts.");
        println("CLI: php migrate_passwords.php user@example.com newPassword");
    }
} catch (Exception $e) {
    println('Error: ' . $e->getMessage());
}

// Helpful: show a quick check snippet for a given email when accessed via browser
if (!empty($_GET['check_email'])) {
    $email = $_GET['check_email'];
    $stmt = $pdo->prepare('SELECT id, email, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    header('Content-Type: text/plain; charset=utf-8');
    if (!$row) {
        echo "User not found: $email";
    } else {
        echo "id: {$row['id']}\nemail: {$row['email']}\npassword_hash: ";
        echo ($row['password_hash'] ? $row['password_hash'] : '[empty]');
    }
}

?>
