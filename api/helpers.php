<?php
require_once __DIR__ . '/../config/database.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_auth(): array {
    start_session();
    if (empty($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    return [
        'id'       => (int)$_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role'],
    ];
}

function require_admin(): array {
    $user = require_auth();
    if ($user['role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Forbidden'], 403);
    }
    return $user;
}

function only_method(string ...$methods): void {
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

// Reads JSON body from php://input; falls back to $_POST
function request_body(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($ct, 'application/json')) {
        $decoded = json_decode(file_get_contents('php://input'), true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}
