<?php

    session_start();

    include_once __DIR__ . '/../../config/constants.php';

    include_once ROOT . '/config/connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $full_name = $conn->real_escape_string($_POST['full_name']);
        $email     = $conn->real_escape_string($_POST['email']);
        $password  = $conn->real_escape_string($_POST['password']);
        $phone     = $conn->real_escape_string($_POST['phone']);
        $role      = $conn->real_escape_string($_POST['role']);

        if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: " . BASE_URL . "app/views/auth/register_view.php");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header("Location: " . BASE_URL . "app/views/auth/register_view.php");
            exit();
        }

        // Check if email already exists
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Email already exists
            $_SESSION['error'] = "An account with this email already exists.";
            $stmt_check->close();
            header("Location: " . BASE_URL . "app/views/auth/register_view.php");
            exit();
        }
        $stmt_check->close();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO users (full_name, email, password, phone, role) 
                    VALUES (?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $role);

        if ($stmt_insert->execute()) {
            // --- SUCCESS ---
            $_SESSION['message'] = "Registration successful! You can now log in.";
            header("Location: " . BASE_URL . "app/views/auth/login_view.php");// Redirect to login page
            exit();
        } else {
            // --- FAILED TO INSERT ---
            $_SESSION['error'] = "Registration failed. Please try again. Error: " . $stmt_insert->error;
            header("Location: " . BASE_URL . "app/views/auth/register_view.php");
            exit();
        }

        // Close statement and connection
        $stmt_insert->close();
        $conn->close();


    }else {
        // Not a POST request, redirect to registration page
        header("Location: " . BASE_URL . "app/views/auth/register_view.php");
        exit();
    }

?>