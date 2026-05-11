<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
$session = require_auth();

$body    = request_body();
$levelId = isset($body['level_id']) ? (int)$body['level_id'] : 0;
$answers = $body['answers'] ?? [];   // [{question_id, answer_id}, ...]

if (!$levelId || !is_array($answers) || empty($answers)) {
    json_response(['success' => false, 'message' => 'level_id and answers are required'], 400);
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT id, min_score_required FROM levels WHERE id = ?');
$stmt->execute([$levelId]);
$level = $stmt->fetch();
if (!$level) {
    json_response(['success' => false, 'message' => 'Level not found'], 404);
}

$correctCount = 0;
$results      = [];

foreach ($answers as $ans) {
    $qid = isset($ans['question_id']) ? (int)$ans['question_id'] : 0;
    $aid = isset($ans['answer_id'])   ? (int)$ans['answer_id']   : 0;

    if (!$qid || !$aid) continue;

    $stmt = $pdo->prepare('
        SELECT q.id, q.component_id, a.is_correct
        FROM questions q
        JOIN answers a ON a.question_id = q.id AND a.id = ?
        WHERE q.id = ? AND q.level_id = ?
    ');
    $stmt->execute([$aid, $qid, $levelId]);
    $row = $stmt->fetch();

    if (!$row) continue;

    $isCorrect = (bool)$row['is_correct'];
    if ($isCorrect) {
        $correctCount++;

        if ($row['component_id']) {
            $stmt2 = $pdo->prepare(
                'INSERT IGNORE INTO learned_components (user_id, component_id) VALUES (?, ?)'
            );
            $stmt2->execute([$session['id'], (int)$row['component_id']]);
        }
    }

    $results[] = ['question_id' => $qid, 'answer_id' => $aid, 'correct' => $isCorrect];
}

$total = count($results);
$score = $total > 0 ? (int)round(($correctCount / $total) * 100) : 0;

$stmt = $pdo->prepare('
    INSERT INTO quiz_sessions (user_id, level_id, score, total_questions, correct_answers)
    VALUES (?, ?, ?, ?, ?)
');
$stmt->execute([$session['id'], $levelId, $score, $total, $correctCount]);

$passed = $score >= (int)$level['min_score_required'];
$stmt   = $pdo->prepare('
    INSERT INTO user_progress (user_id, level_id, best_score, completed, updated_at)
    VALUES (?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        best_score = GREATEST(best_score, VALUES(best_score)),
        completed  = completed OR VALUES(completed),
        updated_at = NOW()
');
$stmt->execute([$session['id'], $levelId, $score, (int)$passed]);

json_response([
    'success'         => true,
    'score'           => $score,
    'correct_answers' => $correctCount,
    'total_questions' => $total,
    'passed'          => $passed,
    'results'         => $results,
]);
