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
        'admin' => BASE_URL . 'app/controllers/profile_controller.php',
        'organizer' => BASE_URL . 'app/controllers/profile_controller.php',
        'user' => BASE_URL . 'app/controllers/profile_controller.php',
    };

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $user_id = $_SESSION['user_id'];
        
        // Check if file was uploaded
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = match ($_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File is too large",
                UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "Upload blocked by extension",
                default => "Unknown upload error"
            };
            
            $_SESSION['error_message'] = "Upload error: " . $error_msg;
            header("Location: $profile_page");
            exit();
        }

        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        // Get file MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
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
        
        // Use absolute path for robustness
        $upload_dir = ROOT . '/public/upload/profile_pic/';

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
        
        // Fetch current profile picture from database (using prepared statement)
        $stmt = mysqli_prepare($conn, "SELECT profile_pic FROM users WHERE user_id = ?");
        if (!$stmt) {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
            header("Location: $profile_page");
            exit();
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Delete old profile picture if it exists and is not the default
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
            
            // Update database with new profile picture filename (using prepared statement)
            $stmt = mysqli_prepare($conn, "UPDATE users SET profile_pic = ? WHERE user_id = ?");
            
            if (!$stmt) {
                $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
                // Delete uploaded file if database update failed
                unlink($upload_path);
                header("Location: $profile_page");
                exit();
            }
            
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
        
    } else {
        // If accessed directly without POST, redirect to profile
        header("Location: $profile_page");
        exit();
    }

    mysqli_close($conn);
?>