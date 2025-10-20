<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

require './database_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // 1. Find the user_id from the email
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        // 2. Generate token and expiration time
        $token = bin2hex(random_bytes(32));
        
        // Use your 'expires_at' (TIMESTAMP) column
        // Set it to 1 hour from now
        $expires_at = date('Y-m-d H:i:s', time() + 3600); 

        // 3. Delete any old tokens for this user
        $sql_delete = "DELETE FROM password_resets WHERE user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();

        // 4. Store the new token in 'password_resets'
        // Note the column names: user_id, reset_token, expires_at
        $sql_insert = "INSERT INTO password_resets (user_id, reset_token, expires_at) 
                       VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        // 'iss' = integer, string, string
        $stmt_insert->bind_param("iss", $user_id, $token, $expires_at);

        if ($stmt_insert->execute()) {
            // 5. Send the email
            // !! UPDATE THIS to your domain/path
            $reset_link = "http://localhost/EventManagement-TicketOrderingSystem/pages/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // --- SMTP Server settings (REPLACE WITH YOURS) ---
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'SAPthathsara@gmail.com';
                $mail->Password   = 'amen kvas tryy cyfg';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587; 

                $mail->setFrom('no-reply@yourproject.com', 'Event Management System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Click the link to reset your password: <a href='$reset_link'>$reset_link</a>";

                $mail->send();
                $_SESSION['message'] = "If an account exists, a reset link has been sent.";
                header("Location: forgot_password.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                header("Location: forgot_password.php");
                exit();
            }
        }
    } else {
        // Generic message even if user not found
        $_SESSION['message'] = "If an account exists, a reset link has been sent.";
        header("Location: forgot_password.php");
        exit();
    }
}

?>