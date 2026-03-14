<?php
// Database Configuration (Production Setup)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'examcell');

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/notifications/');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', '/exam_cell/uploads/notifications/');
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'nbkrist_examcell');
}

function getDBConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed. Please contact administrator.");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>