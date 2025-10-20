<?php
require './database_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get form data
    $token = $conn->real_escape_string($_POST['reset_token']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // 2. Validate passwords
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please enter and confirm your new password.";
        header("Location: reset_password.php?token=" . $token);
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset_password.php?token=" . $token);
        exit();
    }

    // 3. Find the token in the database AND check if it has expired
    // We use NOW() to compare with your 'expires_at' TIMESTAMP column
    $sql = "SELECT user_id, expires_at FROM password_resets 
            WHERE reset_token = ? AND expires_at > NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // --- Token is valid and not expired ---
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // 4. Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 5. Update the user's password in the 'users' table
        $sql_update = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt_update->execute()) {
            // 6. Delete the token so it can't be used again
            $sql_delete = "DELETE FROM password_resets WHERE reset_token = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();

            // --- SUCCESS ---
            $_SESSION['message'] = "Your password has been updated successfully. Please log in.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update password. Please try again.";
            header("Location: reset_password.php?token=" . $token);
            exit();
        }

    } else {
        // --- Token is invalid or expired ---
        $_SESSION['error'] = "This password reset link is invalid or has expired.";
        header("Location: forgot_password.php");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>