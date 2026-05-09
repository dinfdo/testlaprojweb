<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
start_session();

$body     = request_body();
$username = trim($body['username'] ?? '');
$password = $body['password']      ?? '';

if (!$username || !$password) {
    json_response(['success' => false, 'message' => 'Username and password are required']);
}

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = $1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['success' => false, 'message' => 'Invalid credentials']);
}

session_regenerate_id(true);
$_SESSION['user_id']  = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

json_response([
    'success' => true,
    'user'    => ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $user['role']],
]);
