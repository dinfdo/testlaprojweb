<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');
$session = require_auth();

$pdo  = get_db();

// Per-level progress
$stmt = $pdo->prepare('
    SELECT l.id AS level_id, l.level_number, l.title, l.min_score_required,
           d.name AS device_name,
           COALESCE(up.best_score, 0)  AS best_score,
           COALESCE(up.completed, FALSE) AS completed,
           up.updated_at
    FROM levels l
    JOIN devices d ON d.id = l.device_id
    LEFT JOIN user_progress up ON up.level_id = l.id AND up.user_id = $1
    ORDER BY l.device_id, l.level_number
');
$stmt->execute([$session['id']]);
$levels = $stmt->fetchAll();

// Learned components count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM learned_components WHERE user_id = $1');
$stmt->execute([$session['id']]);
$learnedCount = (int)$stmt->fetchColumn();

// Recent quiz sessions
$stmt = $pdo->prepare('
    SELECT qs.score, qs.total_questions, qs.correct_answers, qs.created_at,
           l.title AS level_title
    FROM quiz_sessions qs
    JOIN levels l ON l.id = qs.level_id
    WHERE qs.user_id = $1
    ORDER BY qs.created_at DESC
    LIMIT 10
');
$stmt->execute([$session['id']]);
$recentSessions = $stmt->fetchAll();

json_response([
    'success'         => true,
    'levels'          => $levels,
    'learned_components' => $learnedCount,
    'recent_sessions' => $recentSessions,
]);
