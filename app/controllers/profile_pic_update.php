<?php

session_start();
include_once __DIR__ . '/../../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Determine the profile page to redirect back to
$role = $_SESSION['role'] ?? 'user';
$profile_page = match ($role) {
    'admin' => '../../controllers/profile_admin_controller.php',
    'organizer' => '../../controllers/profile_organizer_controller.php',
    default => '../../controllers/profile_user_controller.php',
};

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $profile_page");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = "No file uploaded or upload error occurred. Error code: " . ($_FILES['profile_picture']['error'] ?? 'N/A');
    header("Location: $profile_page");
    exit();
}

$file = $_FILES['profile_picture'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    $_SESSION['error_message'] = "Invalid file type ($file_type). Only JPG, PNG, GIF, and WebP are allowed.";
    header("Location: $profile_page");
    exit();
}

// Validate file size (5MB maximum)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $max_size) {
    $_SESSION['error_message'] = "File size exceeds 5MB limit.";
    header("Location: $profile_page");
    exit();
}

// Set upload directory - go up from controllers to root, then to public
$upload_dir = __DIR__ . '/../../public/upload/profile_pic/';

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $_SESSION['error_message'] = "Failed to create upload directory.";
        header("Location: $profile_page");
        exit();
    }
}

// Generate unique filename
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// Fetch current profile picture from database
$stmt = mysqli_prepare($conn, "SELECT profile_pic FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Delete old profile picture if it exists
if (!empty($user['profile_pic'])) {
    $old_pic_name = $user['profile_pic'];
    $old_pic_path = $upload_dir . $old_pic_name;
    // Ensure we don't delete the default image
    if (file_exists($old_pic_path) && $old_pic_name !== 'default_profile.png') {
        unlink($old_pic_path);
    }
}

// Move uploaded file to destination
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    
    // Update database with new profile picture filename
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_pic = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Profile picture updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update database: " . mysqli_error($conn);
        // Delete uploaded file if database update failed
        unlink($upload_path);
    }
    
    mysqli_stmt_close($stmt);
    
} else {
    $_SESSION['error_message'] = "Failed to move uploaded file. Check directory permissions (0755).";
}

// Redirect back to profile page
header("Location: $profile_page");
exit();

mysqli_close($conn);

?>