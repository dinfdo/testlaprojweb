<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

if (empty($_GET['level_id']) || !ctype_digit($_GET['level_id'])) {
    json_response(['success' => false, 'message' => 'Missing or invalid level_id'], 400);
}

$levelId = (int)$_GET['level_id'];
$pdo     = get_db();

// Verify level exists
$stmt = $pdo->prepare('SELECT id, title, description, min_score_required FROM levels WHERE id = $1');
$stmt->execute([$levelId]);
$level = $stmt->fetch();
if (!$level) {
    json_response(['success' => false, 'message' => 'Level not found'], 404);
}

// Fetch questions
$stmt = $pdo->prepare('
    SELECT id, question_text, question_type, difficulty
    FROM questions
    WHERE level_id = $1
    ORDER BY id
');
$stmt->execute([$levelId]);
$questions = $stmt->fetchAll();

// Attach shuffled answers (without is_correct)
$answerStmt = $pdo->prepare('
    SELECT id, answer_text
    FROM answers
    WHERE question_id = $1
');
foreach ($questions as &$q) {
    $answerStmt->execute([(int)$q['id']]);
    $answers = $answerStmt->fetchAll();
    shuffle($answers);
    $q['answers'] = $answers;
}
unset($q);

json_response(['success' => true, 'level' => $level, 'questions' => $questions]);
