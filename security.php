<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Global Login Check
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// 2. Prevent Session Hijacking (Store IP or Browser Hash)
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=session_expired");
        exit();
    }
}

// 3. Role-Based Access Control (RBAC)
function checkRole($allowed_roles) {
    $current_role = $_SESSION['role'] ?? '';
    if (!in_array($current_role, $allowed_roles)) {
        // If a Patient tries to access a Staff page, send them back
        header("Location: index.php?error=access_denied");
        exit();
    }
}
?>