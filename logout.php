<?php
session_start(); // Start the session

// Check if user || admin is logged in
if (isset($_SESSION['user_id'])) {
    // Unset user-specific session variables
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    $redirect_page = 'login.php';
} elseif (isset($_SESSION['admin_id'])) {
    // Unset admin-specific session variables
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
    $redirect_page = 'admin_login.php';
} else {
    $redirect_page = 'index.php'; // No one was logged in
}

// Destroy session completely
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect based on user type
header("Location: $redirect_page");
exit();
?>
