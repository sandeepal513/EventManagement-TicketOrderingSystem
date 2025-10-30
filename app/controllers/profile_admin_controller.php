<?php

session_start();

include_once __DIR__ . '/../../config/connection.php';
include_once __DIR__ . '/../models/user_model.php';

// REMOVE THIS LINE IN PRODUCTION - for testing only
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin'; // Add role to session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user = getUserById($conn, $user_id);

if (!$user) {
    // User not found, redirect to login
    session_destroy();
    header("Location: ../../views/auth/login.php");
    exit();
}

// Store role in session if not already set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = $user['role'];
}

// Determine profile picture source (relative to the view file location)
if (!empty($user['profile_pic'])) {
    // Path relative to view files in views/admin/
    $profile_pic_src = "../../public/upload/profile_pic/" . htmlspecialchars($user['profile_pic']);
} else {
    // Default profile picture
    $profile_pic_src = "../../public/assets/images/sys_img/default_profile.png";
}

// Get notification preferences
$email_alerts = isset($user['email_alerts']) ? (int)$user['email_alerts'] : 0;
$sms_alerts = isset($user['sms_alerts']) ? (int)$user['sms_alerts'] : 0;

// Include the appropriate view based on user role
if ($user['role'] == 'admin') {
    include_once __DIR__ . '/../views/admin/profile_admin_view.php';
} elseif ($user['role'] == 'user') {
    include_once __DIR__ . '/../views/user/profile_user_view.php';
} elseif ($user['role'] == 'organizer') {
    include_once __DIR__ . '/../views/organizer/profile_organizer_view.php';
} else {
    // Invalid role, log out
    session_destroy();
    header("Location: ../../views/auth/login.php");
    exit();
}

?>