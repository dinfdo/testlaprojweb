<?php
require_once __DIR__ . '/../helpers.php';

require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('
        SELECT u.id, u.username, u.email, u.role, u.created_at,
               COUNT(DISTINCT qs.id)   AS quiz_count,
               COALESCE(SUM(up.best_score), 0) AS total_score
        FROM users u
        LEFT JOIN quiz_sessions qs ON qs.user_id = u.id
        LEFT JOIN user_progress  up ON up.user_id = u.id
        GROUP BY u.id, u.username, u.email, u.role, u.created_at
        ORDER BY u.created_at DESC
    ');
    json_response(['success' => true, 'users' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = request_body();
    $id   = isset($body['id']) ? (int)$body['id'] : 0;
    $role = $body['role'] ?? '';

    if (!$id || !in_array($role, ['user', 'admin'], true)) {
        json_response(['success' => false, 'message' => 'Invalid id or role'], 400);
    }

    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$role, $id]);
    json_response(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        json_response(['success' => false, 'message' => 'Missing id'], 400);
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['success' => true]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
