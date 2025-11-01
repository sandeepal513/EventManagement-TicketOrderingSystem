<?php
    session_start();

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../config/constants.php';

    include_once ROOT . '/config/connection.php';
    include_once ROOT . '/app/models/user_model.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "views/auth/login_view.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Get user data
    $user = getUserById($conn, $user_id);

    if (!$user) {
        // User not found, redirect to login
        session_destroy();
        header("Location: " . BASE_URL . "views/auth/login_view.php");
        exit();
    }

    // Determine profile picture source (relative to the view file location)
    if (!empty($user['profile_pic'])) {
        // Path relative to view files in views/admin/, views/user/, etc.
        $profile_pic_src = BASE_URL . 'public/upload/profile_pic/' . htmlspecialchars($user['profile_pic']);
    } else {
        // Default profile picture
        $profile_pic_src = BASE_URL . 'public/assets/images/sys_img/default_profile.png';
    }

    // Get notification preferences with proper defaults
    $email_alerts = isset($user['email_alerts']) ? (int)$user['email_alerts'] : 0;
    $sms_alerts = isset($user['sms_alerts']) ? (int)$user['sms_alerts'] : 0;

    // Include the appropriate view based on user role
    if ($user['role'] == 'admin') {
        include_once ROOT . '/app/views/admin/profile_admin_view.php';
    } elseif ($user['role'] == 'user') {
        include_once ROOT . '/app/views/user/profile_user_view.php';
    } elseif ($user['role'] == 'organizer') {
        include_once ROOT . '/app/views/organizer/profile_organizer_view.php';
    } else {
        // Invalid role, log out
        session_destroy();
        header("Location: " . BASE_URL . "views/auth/login_view.php");
        exit();
    }
?>