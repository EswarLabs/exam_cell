<?php
require_once __DIR__ . '/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['exam_admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['exam_admin_id']);
}

function loginAdmin($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM exam_cell_admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['exam_admin_id']   = $admin['id'];
            $_SESSION['exam_admin_name'] = $admin['full_name'];
            $_SESSION['exam_admin_user'] = $admin['username'];
            $conn->close();
            return true;
        }
    }

    $conn->close();
    return false;
}

function logoutAdmin() {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
