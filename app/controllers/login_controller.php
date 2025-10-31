<?php

session_start();

include_once __DIR__ . '/../../config/constants.php';

include_once ROOT . '/config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password_attempt = $_POST['password'];

    if (empty($email) || empty($password_attempt)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: " . BASE_URL . "app/views/auth/login_view.php");
        exit();
    }

    $sql = "SELECT user_id, full_name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, fetch their data
        $user = $result->fetch_assoc();

        // 5. Verify the password
        // Use password_verify() to compare the submitted password with the stored hash
        if (password_verify($password_attempt, $user['password'])) {
            
            // 6. Store user data in the session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            $_SESSION['message'] = "Login Successfull";
            header("Location: " . BASE_URL . "app/views/auth/login_view.php"); 
            exit();

        } else {
            // --- FAILED! Invalid password ---
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: " . BASE_URL . "index.php"); 
            exit();
        }

    } else {
        // --- FAILED! No user found with that email ---
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: " . BASE_URL . "index.php"); 
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}else {
    header("Location: " . BASE_URL . "index.php"); 
    exit();
}

?>