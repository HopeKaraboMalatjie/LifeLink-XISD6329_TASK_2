<?php
// Shared helpers for api/*.php endpoints

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Standard ApiResponse<T> shape: {success, message, data, error}
function api_ok($data = null, ?string $message = null): void {
    respond(['success' => true, 'message' => $message, 'data' => $data, 'error' => null]);
}

function api_error(string $error, int $status = 400): void {
    respond(['success' => false, 'message' => $error, 'data' => null, 'error' => $error], $status);
}

function param(string $key, $default = null) {
    if (isset($_GET[$key])) return $_GET[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}
