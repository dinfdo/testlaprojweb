<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

$limit = isset($_GET['limit']) && ctype_digit($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = min($limit, 100);

$pdo  = get_db();
$stmt = $pdo->prepare('
    SELECT u.id, u.username,
           COALESCE(SUM(up.best_score), 0)                              AS total_score,
           COUNT(up.level_id)                                           AS levels_attempted,
           SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END)           AS levels_completed
    FROM users u
    LEFT JOIN user_progress up ON up.user_id = u.id
    WHERE u.role = \'user\'
    GROUP BY u.id, u.username
    ORDER BY total_score DESC, levels_completed DESC
    LIMIT ?
');
$stmt->execute([$limit]);

$rows = $stmt->fetchAll();
foreach ($rows as $i => &$row) {
    $row['rank']             = $i + 1;
    $row['total_score']      = (int)$row['total_score'];
    $row['levels_attempted'] = (int)$row['levels_attempted'];
    $row['levels_completed'] = (int)$row['levels_completed'];
}
unset($row);

json_response(['success' => true, 'leaderboard' => $rows]);
