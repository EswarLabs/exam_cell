<?php
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        die('Error: .env file not found at ' . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                define($key, $value);
            }
        }
    }
}

loadEnv();

if (!defined('DB_HOST') || !defined('DB_NAME')) {
    die('Error: Required environment variables not set.');
}

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/notifications/');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', '/exam_cell/uploads/notifications/');
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'nbkrist_examcell');
}

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed. Please contact administrator.");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
