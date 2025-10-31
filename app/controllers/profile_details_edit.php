<?php
    session_start();

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../config/constants.php';

    // 1. Database Connection and Initialization
    include_once ROOT . '/config/connection.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }

    // Define the redirection page based on the user's role
    $role = $_SESSION['role'] ?? 'user';
    $profile_page = match ($role) {
        'admin' => BASE_URL . 'app/controllers/profile_controller.php',
        'organizer' => BASE_URL . 'app/controllers/profile_controller.php',
        'user' => BASE_URL . 'app/controllers/profile_controller.php',
    };

    // Exit if not a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . $profile_page);
        exit();
    }

    // Check for database connection failure
    if (!$conn || $conn->connect_error) {
        $_SESSION['error_message'] = "Database connection failed. Cannot save changes.";
        header("Location: " . $profile_page);
        exit();
    }

    // 2. Retrieve and Sanitize Form Data
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_pwd = $_POST['current_pwd'] ?? '';
    $new_pwd = $_POST['new_pwd'] ?? '';
    $confirm_pwd = $_POST['confirm_pwd'] ?? '';
    
    // 3. Essential Input Validation
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
    
    // Additional validation: Full name length
    if (strlen($full_name) < 2 || strlen($full_name) > 100) {
        $_SESSION['error_message'] = "Full name must be between 2 and 100 characters.";
        header("Location: " . $profile_page);
        exit();
    }
    
    // Phone validation (optional field, but validate if provided)
    if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $_SESSION['error_message'] = "Invalid phone number format.";
        header("Location: " . $profile_page);
        exit();
    }
    
    // 4. Check for Email Uniqueness (must be unique among all other users)
    $sql_check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $stmt_email = mysqli_prepare($conn, $sql_check_email);
    
    if (!$stmt_email) {
        $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
        header("Location: " . $profile_page);
        exit();
    }
    
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

    // --- Start Update Logic ---
    
    // Check if the user is trying to change the password
    $password_update_attempt = !empty($current_pwd) || !empty($new_pwd) || !empty($confirm_pwd);
    $update_success = false;

    // A. Update details ONLY (No password fields filled)
    if (!$password_update_attempt) {
        // Build SQL query for non-password update
        $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        
        if (!$stmt) {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
            header("Location: " . $profile_page);
            exit();
        }
        
        mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $phone, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $update_success = true;
            $_SESSION['success_message'] = "Profile details updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating profile details: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

    // B. Update details AND password (All password fields must be filled)
    } elseif (!empty($current_pwd) && !empty($new_pwd) && !empty($confirm_pwd)) {
        
        // Password strength validation
        if (strlen($new_pwd) < 8) {
            $_SESSION['error_message'] = "New password must be at least 8 characters long.";
            header("Location: " . $profile_page);
            exit();
        }
        
        // 1. Fetch current hashed password securely
        $sql_check = "SELECT password FROM users WHERE user_id = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        
        if (!$stmt_check) {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
            header("Location: " . $profile_page);
            exit();
        }
        
        mysqli_stmt_bind_param($stmt_check, "i", $user_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $user_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        // Check 1: User exists and current password is correct
        if (!$user_data || empty($user_data['password'])) {
            $_SESSION['error_message'] = "User data not found.";
        } elseif (!password_verify($current_pwd, $user_data['password'])) {
            $_SESSION['error_message'] = "Current password is incorrect.";
        } 
        // Check 2: New passwords match
        elseif ($new_pwd !== $confirm_pwd) {
            $_SESSION['error_message'] = "New password and confirmation do not match.";
        } 
        // If all checks pass, proceed with update
        else {
            $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
            
            // Build SQL query for full update
            $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?";
            
            $stmt = mysqli_prepare($conn, $update_sql);
            
            if (!$stmt) {
                $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
                header("Location: " . $profile_page);
                exit();
            }
            
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

    // Close database connection
    mysqli_close($conn);

    // --- FINAL REDIRECTION ---
    header("Location: " . $profile_page);
    exit();
?>