<?php
    session_start();

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../config/constants.php';

    $_SESSION = [];
    session_destroy();
    header("Location: " . BASE_URL . "views/auth/login.php?status=logged_out");
    exit;

?>