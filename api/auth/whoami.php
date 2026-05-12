<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');
$session = require_auth();

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$session['id']]);
$user = $stmt->fetch();

if (!$user) {
    json_response(['success' => false, 'message' => 'User not found'], 404);
}

json_response(['success' => true, 'user' => $user]);
