<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
start_session();

$body       = request_body();
$identifier = trim($body['identifier'] ?? $body['username'] ?? $body['email'] ?? '');
$password   = $body['password']      ?? '';

if (!$identifier || !$password) {
    json_response(['success' => false, 'message' => 'Username/email and password are required']);
}

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = $1 OR email = $1');
$stmt->execute([$identifier]);
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
