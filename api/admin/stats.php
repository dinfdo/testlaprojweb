<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');
require_admin();

$pdo = get_db();

$users      = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$components = (int)$pdo->query('SELECT COUNT(*) FROM components')->fetchColumn();
$sessions   = (int)$pdo->query('SELECT COUNT(*) FROM quiz_sessions')->fetchColumn();
$completed  = (int)$pdo->query('SELECT COUNT(*) FROM user_progress WHERE completed = 1')->fetchColumn();

$avgScore = $pdo->query('SELECT ROUND(AVG(score), 1) FROM quiz_sessions')->fetchColumn();

json_response([
    'success'          => true,
    'total_users'      => $users,
    'total_components' => $components,
    'total_sessions'   => $sessions,
    'levels_completed' => $completed,
    'avg_score'        => $avgScore !== null ? (float)$avgScore : null,
]);
