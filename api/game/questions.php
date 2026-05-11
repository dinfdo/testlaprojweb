<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

if (empty($_GET['level_id']) || !ctype_digit($_GET['level_id'])) {
    json_response(['success' => false, 'message' => 'Missing or invalid level_id'], 400);
}

$levelId = (int)$_GET['level_id'];
$pdo     = get_db();

$stmt = $pdo->prepare('SELECT id, title, description, game_type, min_score_required FROM levels WHERE id = ?');
$stmt->execute([$levelId]);
$level = $stmt->fetch();
if (!$level) {
    json_response(['success' => false, 'message' => 'Level not found'], 404);
}

$stmt = $pdo->prepare('
    SELECT q.id, q.question_text, q.question_type, q.difficulty,
           c.slot_position
    FROM questions q
    LEFT JOIN components c ON c.id = q.component_id
    WHERE q.level_id = ?
    ORDER BY q.id
');
$stmt->execute([$levelId]);
$questions = $stmt->fetchAll();

$answerStmt = $pdo->prepare('SELECT id, answer_text FROM answers WHERE question_id = ?');
foreach ($questions as &$q) {
    $answerStmt->execute([(int)$q['id']]);
    $answers = $answerStmt->fetchAll();
    shuffle($answers);
    $q['answers'] = $answers;
}
unset($q);

json_response(['success' => true, 'level' => $level, 'questions' => $questions]);
