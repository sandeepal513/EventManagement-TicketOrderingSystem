<?php
    session_start();

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../config/constants.php';

    include_once ROOT . '/config/connection.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }

    // Determine the profile page to redirect back to
    $role = $_SESSION['role'] ?? 'user';
    $profile_page = match ($role) {
        'admin' => BASE_URL . 'app/controllers/profile_admin_controller.php',
        'organizer' => BASE_URL . 'app/controllers/profile_admin_controller.php',
        default => BASE_URL . 'app/controllers/profile_admin_controller.php',
    };

    $user_id = $_SESSION['user_id'];

    // Check if this is a GET request with confirm parameter
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['confirm']) && $_GET['confirm'] === 'delete') {
        // This is a delete request via GET (from clicking the image link)
        // Process the deletion
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // Invalid request method, redirect back
        header("Location: $profile_page");
        exit();
    }

    // Fetch current profile picture filename from database
    $query = "SELECT profile_pic FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
        header("Location: $profile_page");
        exit();
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_profile_picture);
    $stmt->fetch();
    $stmt->close();

    // Delete the profile picture file if it exists and is not the default picture
    if (!empty($current_profile_picture)) {
        $file_path = ROOT . '/public/upload/profile_pic/' . $current_profile_picture;
        
        // Only delete if file exists and is not default
        if (file_exists($file_path) && $current_profile_picture !== 'default_profile.png') {
            if (!unlink($file_path)) {
                error_log("Failed to delete profile picture file: " . $file_path);
            }
        }
    }

    // Update database to remove profile picture reference
    $update_query = "UPDATE users SET profile_pic = NULL WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if (!$update_stmt) {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
        header("Location: $profile_page");
        exit();
    }
    
    $update_stmt->bind_param("i", $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile picture deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete profile picture from database: " . $update_stmt->error;
    }
    
    $update_stmt->close();
    $conn->close();

    // Redirect back to profile page
    header("Location: $profile_page");
    exit();
?>