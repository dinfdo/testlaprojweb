<?php
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Me endpoint'];
echo json_encode($response);
