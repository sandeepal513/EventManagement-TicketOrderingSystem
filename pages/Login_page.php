<?php
session_start();

include_once './../php/connection.php';

if (!$conn) {
    header('Location: ./html.php');
    exit();
} else {
    echo "Database connection failed!";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="./../css_files/login_css.css">
</head>
<body>

    <div class="login-container">
        <?php 
        // This is a placeholder for actual session management or error handling in the full PHP file
        if (isset($_GET['error'])) {
            echo '<div class="message error">Invalid email or password. Please try again.</div>';
        }
        ?>

        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your credentials to access your account.</p>

        <form action="handle_login.php" method="POST">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="options">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" class="login-button">Log In</button>
        </form>

        <p class="register-link">
            Don't have an account? <a href="#">Sign Up</a>
        </p>
    </div>

</body>
</html>