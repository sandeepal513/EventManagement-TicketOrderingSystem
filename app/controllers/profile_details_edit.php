<?php

session_start();

// Database Connection
include_once __DIR__ . '/../../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Define the redirection page based on the user's role
$role = $_SESSION['role'] ?? 'user';
$profile_page = match ($role) {
    'admin' => '../../controllers/profile_admin_controller.php',
    'organizer' => '../../controllers/profile_organizer_controller.php',
    default => '../../controllers/profile_user_controller.php',
};

// Exit if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $profile_page);
    exit();
}

// Check for database connection failure
if (!$conn) {
    $_SESSION['error_message'] = "Database connection failed. Cannot save changes.";
    header("Location: " . $profile_page);
    exit();
}

// Retrieve and Sanitize Form Data
$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$current_pwd = $_POST['current_pwd'] ?? '';
$new_pwd = $_POST['new_pwd'] ?? '';
$confirm_pwd = $_POST['confirm_pwd'] ?? '';

// Essential Input Validation
if (empty($full_name) || empty($email)) {
    $_SESSION['error_message'] = "Full Name and Email fields are required.";
    header("Location: " . $profile_page);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Invalid email format.";
    header("Location: " . $profile_page);
    exit();
}

// Check for Email Uniqueness
$sql_check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
$stmt_email = mysqli_prepare($conn, $sql_check_email);
mysqli_stmt_bind_param($stmt_email, "si", $email, $user_id);
mysqli_stmt_execute($stmt_email);
$result_email = mysqli_stmt_get_result($stmt_email);

if (mysqli_num_rows($result_email) > 0) {
    $_SESSION['error_message'] = "This email address is already in use by another account.";
    mysqli_stmt_close($stmt_email);
    header("Location: " . $profile_page);
    exit();
}
mysqli_stmt_close($stmt_email);

// Check if the user is trying to change the password
$password_update_attempt = !empty($current_pwd) || !empty($new_pwd) || !empty($confirm_pwd);
$update_success = false;

// A. Update details ONLY (No password fields filled)
if (!$password_update_attempt) {
    $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $phone, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $update_success = true;
        $_SESSION['success_message'] = "Profile details updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating profile details: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);

// B. Update details AND password
} elseif (!empty($current_pwd) && !empty($new_pwd) && !empty($confirm_pwd)) {
    
    // Fetch current hashed password
    $sql_check = "SELECT password FROM users WHERE user_id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $user_data = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    // Verify current password
    if (!$user_data || empty($user_data['password']) || !password_verify($current_pwd, $user_data['password'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
    } 
    // Check if new passwords match
    elseif ($new_pwd !== $confirm_pwd) {
        $_SESSION['error_message'] = "New password and confirmation do not match.";
    } 
    // Proceed with update
    else {
        $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $phone, $hashed_pwd, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $update_success = true;
            $_SESSION['success_message'] = "Profile and password updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating profile and password: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }

// C. Handle partial password fields filled
} else {
    $_SESSION['error_message'] = "Please fill in all three password fields (Current, New, Confirm) to change your password.";
}

// Final redirection
header("Location: " . $profile_page);
exit();

?>