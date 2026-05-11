<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
require_admin();

$type   = $_POST['type']   ?? '';
$format = $_POST['format'] ?? '';

if (!in_array($type, ['components'], true)) {
    json_response(['success' => false, 'message' => 'Unsupported import type (only "components")'], 400);
}
if (!in_array($format, ['csv', 'json'], true)) {
    json_response(['success' => false, 'message' => 'format must be csv or json'], 400);
}
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
}

$tmpPath = $_FILES['file']['tmp_name'];
$rows    = [];

if ($format === 'csv') {
    $fh = fopen($tmpPath, 'r');
    if (!$fh) {
        json_response(['success' => false, 'message' => 'Cannot read uploaded file'], 500);
    }
    $headers = fgetcsv($fh);
    if (!$headers) {
        json_response(['success' => false, 'message' => 'Empty CSV file'], 400);
    }
    while (($line = fgetcsv($fh)) !== false) {
        $rows[] = array_combine($headers, $line);
    }
    fclose($fh);
} else {
    $decoded = json_decode(file_get_contents($tmpPath), true);
    if (!is_array($decoded)) {
        json_response(['success' => false, 'message' => 'Invalid JSON'], 400);
    }
    $rows = $decoded;
}

$pdo      = get_db();
$inserted = 0;
$skipped  = 0;

$deviceCache = [];
$deviceStmt  = $pdo->query('SELECT id, name FROM devices');
foreach ($deviceStmt->fetchAll() as $d) {
    $deviceCache[strtolower($d['name'])] = (int)$d['id'];
}

$insertStmt = $pdo->prepare('
    INSERT IGNORE INTO components (device_id, name, description, purpose, difficulty_level)
    VALUES (?, ?, ?, ?, ?)
');

foreach ($rows as $row) {
    $deviceKey = strtolower(trim($row['device'] ?? ''));
    $deviceId  = $deviceCache[$deviceKey] ?? null;
    $name      = trim($row['name']              ?? '');
    $desc      = trim($row['description']       ?? '');
    $purpose   = trim($row['purpose']           ?? '');
    $diff      = isset($row['difficulty_level']) ? (int)$row['difficulty_level'] : 1;

    if (!$deviceId || !$name || !$desc || !$purpose || $diff < 1 || $diff > 3) {
        $skipped++;
        continue;
    }

    $insertStmt->execute([$deviceId, $name, $desc, $purpose, $diff]);
    $inserted++;
}

json_response(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped]);
