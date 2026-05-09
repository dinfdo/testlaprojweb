<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

$pdo    = get_db();
$params = [];
$where  = [];

if (isset($_GET['device_id']) && ctype_digit($_GET['device_id'])) {
    $where[]  = 'c.device_id = $' . (count($params) + 1);
    $params[] = (int)$_GET['device_id'];
}
if (isset($_GET['difficulty']) && ctype_digit($_GET['difficulty'])) {
    $where[]  = 'c.difficulty_level = $' . (count($params) + 1);
    $params[] = (int)$_GET['difficulty'];
}

$sql = '
    SELECT c.id, c.name, c.description, c.purpose, c.difficulty_level, c.image_path,
           d.id AS device_id, d.name AS device_name
    FROM components c
    JOIN devices d ON d.id = c.device_id
' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
    ORDER BY c.difficulty_level, c.name
';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response(['success' => true, 'components' => $stmt->fetchAll()]);
