<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
start_session();

$body     = request_body();
$username = trim($body['username'] ?? '');
$email    = trim($body['email']    ?? '');
$password = $body['password']      ?? '';

if (!$username || !$email || !$password) {
    json_response(['success' => false, 'message' => 'All fields are required']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Invalid email address']);
}
if (strlen($password) < 6) {
    json_response(['success' => false, 'message' => 'Password must be at least 6 characters']);
}

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = $1 OR email = $2');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => 'Username or email already taken']);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash) VALUES ($1, $2, $3) RETURNING id'
);
$stmt->execute([$username, $email, $hash]);
$id = (int)$stmt->fetchColumn();

$_SESSION['user_id']  = $id;
$_SESSION['username'] = $username;
$_SESSION['role']     = 'user';

json_response(['success' => true, 'user' => ['id' => $id, 'username' => $username, 'role' => 'user']]);
