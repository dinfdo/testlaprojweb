<?php
// Run once: http://localhost/v1leg/testlaprojweb/make_test_accounts.php
// Then DELETE this file.
require_once __DIR__ . '/config/database.php';

$accounts = [
    ['admin',   'admin@example.com',   'admin123',   'admin'],
    ['student', 'student@example.com', 'student123', 'user'],
];

$pdo = get_db();

foreach ($accounts as [$username, $email, $password, $role]) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, role)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
    );
    $stmt->execute([$username, $email, $hash, $role]);
    echo "Set password for <b>$username</b> → <code>$password</code><br>";
}

echo '<br><strong style="color:red">Delete this file now: testlaprojweb/make_test_accounts.php</strong>';
