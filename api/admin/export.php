<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');
require_admin();

$type   = $_GET['type']   ?? 'components';   // components | users | leaderboard
$format = $_GET['format'] ?? 'json';          // json | csv

if (!in_array($type, ['components', 'users', 'leaderboard'], true)) {
    json_response(['success' => false, 'message' => 'Invalid type'], 400);
}

$pdo = get_db();

if ($type === 'components') {
    $stmt = $pdo->query('
        SELECT c.id, d.name AS device, c.name, c.description, c.purpose, c.difficulty_level, c.slot_position
        FROM components c JOIN devices d ON d.id = c.device_id
        ORDER BY d.name, c.difficulty_level, c.name
    ');
} elseif ($type === 'users') {
    $stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at');
} else {
    $stmt = $pdo->query('
        SELECT u.username,
               COALESCE(SUM(up.best_score), 0)                           AS total_score,
               SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END)         AS levels_completed
        FROM users u
        LEFT JOIN user_progress up ON up.user_id = u.id
        WHERE u.role = \'user\'
        GROUP BY u.id, u.username
        ORDER BY total_score DESC
    ');
}

$rows = $stmt->fetchAll();

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $type . '.csv"');

    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '.json"');
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
