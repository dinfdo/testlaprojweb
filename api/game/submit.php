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

// Verify level + get min_score_required
$stmt = $pdo->prepare('SELECT id, min_score_required FROM levels WHERE id = $1');
$stmt->execute([$levelId]);
$level = $stmt->fetch();
if (!$level) {
    json_response(['success' => false, 'message' => 'Level not found'], 404);
}

// Grade answers
$correctCount = 0;
$results      = [];

foreach ($answers as $ans) {
    $qid = isset($ans['question_id']) ? (int)$ans['question_id'] : 0;
    $aid = isset($ans['answer_id'])   ? (int)$ans['answer_id']   : 0;

    if (!$qid || !$aid) continue;

    // Confirm the question belongs to this level
    $stmt = $pdo->prepare('
        SELECT q.id, q.component_id, a.is_correct
        FROM questions q
        JOIN answers a ON a.question_id = q.id AND a.id = $1
        WHERE q.id = $2 AND q.level_id = $3
    ');
    $stmt->execute([$aid, $qid, $levelId]);
    $row = $stmt->fetch();

    if (!$row) continue;

    $isCorrect = (bool)$row['is_correct'];
    if ($isCorrect) {
        $correctCount++;

        // Mark component as learned
        if ($row['component_id']) {
            $stmt2 = $pdo->prepare('
                INSERT INTO learned_components (user_id, component_id)
                VALUES ($1, $2)
                ON CONFLICT (user_id, component_id) DO NOTHING
            ');
            $stmt2->execute([$session['id'], (int)$row['component_id']]);
        }
    }

    $results[] = ['question_id' => $qid, 'answer_id' => $aid, 'correct' => $isCorrect];
}

$total = count($results);
$score = $total > 0 ? (int)round(($correctCount / $total) * 100) : 0;

// Save quiz session
$stmt = $pdo->prepare('
    INSERT INTO quiz_sessions (user_id, level_id, score, total_questions, correct_answers)
    VALUES ($1, $2, $3, $4, $5)
');
$stmt->execute([$session['id'], $levelId, $score, $total, $correctCount]);

// Upsert user_progress
$passed = $score >= (int)$level['min_score_required'];
$stmt   = $pdo->prepare('
    INSERT INTO user_progress (user_id, level_id, best_score, completed, updated_at)
    VALUES ($1, $2, $3, $4, NOW())
    ON CONFLICT (user_id, level_id) DO UPDATE
        SET best_score = GREATEST(user_progress.best_score, EXCLUDED.best_score),
            completed  = user_progress.completed OR EXCLUDED.completed,
            updated_at = NOW()
');
$stmt->execute([$session['id'], $levelId, $score, $passed]);

json_response([
    'success'         => true,
    'score'           => $score,
    'correct_answers' => $correctCount,
    'total_questions' => $total,
    'passed'          => $passed,
    'results'         => $results,
]);
