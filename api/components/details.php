<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    json_response(['success' => false, 'message' => 'Missing or invalid id'], 400);
}

$pdo  = get_db();
$stmt = $pdo->prepare('
    SELECT c.id, c.name, c.description, c.purpose, c.difficulty_level, c.image_path,
           d.id AS device_id, d.name AS device_name
    FROM components c
    JOIN devices d ON d.id = c.device_id
    WHERE c.id = $1
');
$stmt->execute([(int)$_GET['id']]);
$component = $stmt->fetch();

if (!$component) {
    json_response(['success' => false, 'message' => 'Component not found'], 404);
}

json_response(['success' => true, 'component' => $component]);
