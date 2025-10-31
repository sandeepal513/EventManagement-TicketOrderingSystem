<?php
    session_start();

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../config/constants.php';

    $_SESSION = [];
    session_destroy();

    // Start a new session to set the logout success message
    session_start();
    $_SESSION['success'] = "You have been logged out successfully.";

    header("Location: " . BASE_URL . "app/views/auth/login_view.php");
    exit;

?>