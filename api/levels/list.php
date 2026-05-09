<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

$pdo    = get_db();
$params = [];
$where  = [];

if (isset($_GET['device_id']) && ctype_digit($_GET['device_id'])) {
    $where[]  = 'l.device_id = $1';
    $params[] = (int)$_GET['device_id'];
}

$sql = '
    SELECT l.id, l.level_number, l.title, l.description, l.min_score_required,
           d.id AS device_id, d.name AS device_name
    FROM levels l
    JOIN devices d ON d.id = l.device_id
' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
    ORDER BY l.device_id, l.level_number
';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response(['success' => true, 'levels' => $stmt->fetchAll()]);
