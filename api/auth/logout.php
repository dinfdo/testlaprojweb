<?php
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Logout endpoint'];
echo json_encode($response);
