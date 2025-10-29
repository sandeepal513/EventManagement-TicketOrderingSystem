<?php

    session_start();
    include_once './../config/connection.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./../pages/login.php");
        exit();
    }

    // Determine the profile page to redirect back to
    if ($_SESSION['role'] == 'admin') {
        $profile_page = './../pages/profile_admin.php';
    } elseif ($_SESSION['role'] == 'organizer') {
        $profile_page = './../pages/profile_organizer.php';
    } else {
        $profile_page = './../pages/profile_user.php';
    }


    $user_id = $_SESSION['user_id'];

    // Fetch current profile picture filename from database
    $query = "SELECT profile_pic FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_profile_picture);
    $stmt->fetch();
    $stmt->close();

    // Delete the profile picture file if it exists and is not the default picture
    if (!empty($current_profile_picture)) {
        $file_path = __DIR__ . '/../res/profile_pic/' . $current_profile_picture;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Update database to remove profile picture reference
    $update_query = "UPDATE users SET profile_pic = NULL WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $user_id);
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile picture deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete profile picture from database.";
    }
    $update_stmt->close();

    // Redirect back to profile page
    header("Location: $profile_page");
    exit();

?>