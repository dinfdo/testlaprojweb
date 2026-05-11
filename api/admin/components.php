<?php
require_once __DIR__ . '/../helpers.php';

require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('
        SELECT c.id, c.name, c.description, c.purpose, c.difficulty_level, c.image_path, c.slot_position,
               d.id AS device_id, d.name AS device_name
        FROM components c
        JOIN devices d ON d.id = c.device_id
        ORDER BY d.name, c.difficulty_level, c.name
    ');
    json_response(['success' => true, 'components' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body         = request_body();
    $deviceId     = isset($body['device_id'])        ? (int)$body['device_id']        : 0;
    $name         = trim($body['name']               ?? '');
    $desc         = trim($body['description']        ?? '');
    $purpose      = trim($body['purpose']            ?? '');
    $diff         = isset($body['difficulty_level'])  ? (int)$body['difficulty_level']  : 1;
    $imgPath      = trim($body['image_path']         ?? '') ?: null;
    $slotPosition = trim($body['slot_position']      ?? '') ?: null;

    if (!$deviceId || !$name || !$desc || !$purpose || $diff < 1 || $diff > 3) {
        json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $stmt = $pdo->prepare('
        INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path, slot_position)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$deviceId, $name, $desc, $purpose, $diff, $imgPath, $slotPosition]);
    json_response(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body         = request_body();
    $id           = isset($body['id'])               ? (int)$body['id']               : 0;
    $name         = trim($body['name']               ?? '');
    $desc         = trim($body['description']        ?? '');
    $purpose      = trim($body['purpose']            ?? '');
    $diff         = isset($body['difficulty_level'])  ? (int)$body['difficulty_level']  : 1;
    $imgPath      = trim($body['image_path']         ?? '') ?: null;
    $slotPosition = trim($body['slot_position']      ?? '') ?: null;

    if (!$id || !$name || !$desc || !$purpose || $diff < 1 || $diff > 3) {
        json_response(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $stmt = $pdo->prepare('
        UPDATE components
        SET name = ?, description = ?, purpose = ?, difficulty_level = ?, image_path = ?, slot_position = ?
        WHERE id = ?
    ');
    $stmt->execute([$name, $desc, $purpose, $diff, $imgPath, $slotPosition, $id]);
    json_response(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        json_response(['success' => false, 'message' => 'Missing id'], 400);
    }
    $stmt = $pdo->prepare('DELETE FROM components WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['success' => true]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
