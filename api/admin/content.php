<?php
require_once __DIR__ . '/../helpers.php';

require_admin();
$pdo = get_db();

$method = $_SERVER['REQUEST_METHOD'];

function invalid_resource(): void {
    json_response(['success' => false, 'message' => 'Invalid or missing resource'], 422);
}

if ($method === 'GET') {
    $resource = $_GET['resource'] ?? '';
    if ($resource === 'components') {
        $stmt = $pdo->query('SELECT c.id, c.name, c.description, c.purpose AS category, c.difficulty_level, c.image_path AS image_url, d.id AS device_id, d.name AS device_name FROM components c JOIN devices d ON d.id = c.device_id ORDER BY d.name, c.difficulty_level, c.name');
        json_response(['success' => true, 'components' => $stmt->fetchAll()]);
    }

    if ($resource === 'levels') {
        $stmt = $pdo->query('SELECT l.id, l.level_number, l.title, l.description, l.min_score_required, d.id AS device_id, d.name AS device_name FROM levels l JOIN devices d ON d.id = l.device_id ORDER BY l.device_id, l.level_number');
        json_response(['success' => true, 'levels' => $stmt->fetchAll()]);
    }

    if ($resource === 'devices') {
        $stmt = $pdo->query('SELECT id, name, description FROM devices ORDER BY name');
        json_response(['success' => true, 'devices' => $stmt->fetchAll()]);
    }

    invalid_resource();
}

$body = request_body();
$resource = $body['resource'] ?? '';

if ($method === 'POST') {
    if ($resource === 'component') {
        $deviceId = isset($body['device_id']) ? (int)$body['device_id'] : 0;
        $name = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $purpose = trim($body['purpose'] ?? $body['category'] ?? '');
        $difficulty = isset($body['difficulty_level']) ? (int)$body['difficulty_level'] : 0;
        $imagePath = trim($body['image_url'] ?? '') ?: null;

        if (!$deviceId || !$name || !$description || !$purpose || $difficulty < 1 || $difficulty > 3) {
            json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
        }

        $stmt = $pdo->prepare('INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id');
        $stmt->execute([$deviceId, $name, $description, $purpose, $difficulty, $imagePath]);
        json_response(['success' => true, 'id' => (int)$stmt->fetchColumn()]);
    }

    if ($resource === 'level') {
        $deviceId = isset($body['device_id']) ? (int)$body['device_id'] : 0;
        $levelNumber = isset($body['level_number']) ? (int)$body['level_number'] : 0;
        $title = trim($body['title'] ?? '');
        $minScore = isset($body['min_score_required']) ? (int)$body['min_score_required'] : 0;

        if (!$deviceId || $levelNumber < 1 || !$title || $minScore < 0) {
            json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
        }

        $stmt = $pdo->prepare('INSERT INTO levels (device_id, level_number, title, description, min_score_required) VALUES ($1, $2, $3, $4, $5) RETURNING id');
        $stmt->execute([$deviceId, $levelNumber, $title, null, $minScore]);
        json_response(['success' => true, 'id' => (int)$stmt->fetchColumn()]);
    }

    invalid_resource();
}

if ($method === 'DELETE') {
    if ($resource === 'component') {
        $id = isset($body['id']) ? (int)$body['id'] : 0;
        if (!$id) {
            json_response(['success' => false, 'message' => 'Missing id'], 400);
        }
        $stmt = $pdo->prepare('DELETE FROM components WHERE id = $1');
        $stmt->execute([$id]);
        json_response(['success' => true]);
    }

    if ($resource === 'level') {
        $id = isset($body['id']) ? (int)$body['id'] : 0;
        if (!$id) {
            json_response(['success' => false, 'message' => 'Missing id'], 400);
        }
        $stmt = $pdo->prepare('DELETE FROM levels WHERE id = $1');
        $stmt->execute([$id]);
        json_response(['success' => true]);
    }

    invalid_resource();
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
