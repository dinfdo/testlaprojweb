<?php
require_once __DIR__ . '/../helpers.php';

require_admin();
$pdo = get_db();

// GET – list all components
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('
        SELECT c.id, c.name, c.description, c.purpose, c.difficulty_level, c.image_path,
               d.id AS device_id, d.name AS device_name
        FROM components c
        JOIN devices d ON d.id = c.device_id
        ORDER BY d.name, c.difficulty_level, c.name
    ');
    json_response(['success' => true, 'components' => $stmt->fetchAll()]);
}

// POST – create component
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body      = request_body();
    $deviceId  = isset($body['device_id'])       ? (int)$body['device_id']       : 0;
    $name      = trim($body['name']              ?? '');
    $desc      = trim($body['description']       ?? '');
    $purpose   = trim($body['purpose']           ?? '');
    $diff      = isset($body['difficulty_level']) ? (int)$body['difficulty_level'] : 1;
    $imgPath   = trim($body['image_path']        ?? '') ?: null;

    if (!$deviceId || !$name || !$desc || !$purpose || $diff < 1 || $diff > 3) {
        json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $stmt = $pdo->prepare('
        INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path)
        VALUES ($1, $2, $3, $4, $5, $6)
        RETURNING id
    ');
    $stmt->execute([$deviceId, $name, $desc, $purpose, $diff, $imgPath]);
    json_response(['success' => true, 'id' => (int)$stmt->fetchColumn()]);
}

// PUT – update component
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body      = request_body();
    $id        = isset($body['id'])              ? (int)$body['id']               : 0;
    $name      = trim($body['name']              ?? '');
    $desc      = trim($body['description']       ?? '');
    $purpose   = trim($body['purpose']           ?? '');
    $diff      = isset($body['difficulty_level']) ? (int)$body['difficulty_level'] : 1;
    $imgPath   = trim($body['image_path']        ?? '') ?: null;

    if (!$id || !$name || !$desc || !$purpose || $diff < 1 || $diff > 3) {
        json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $stmt = $pdo->prepare('
        UPDATE components
        SET name = $1, description = $2, purpose = $3, difficulty_level = $4, image_path = $5
        WHERE id = $6
    ');
    $stmt->execute([$name, $desc, $purpose, $diff, $imgPath, $id]);
    json_response(['success' => true]);
}

// DELETE – remove component
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        json_response(['success' => false, 'message' => 'Missing id'], 400);
    }
    $stmt = $pdo->prepare('DELETE FROM components WHERE id = $1');
    $stmt->execute([$id]);
    json_response(['success' => true]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
