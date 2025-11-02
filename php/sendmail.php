<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['send'])) {
    
    // --- 1. Sanitize and Retrieve Form Data ---
    $fullname = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone'])); 
    $address = htmlspecialchars(trim($_POST['address']));
    $message = htmlspecialchars(trim($_POST['message']));

    // --- 2. Validation ---
    $errors = [];

    // Full Name validation (Only letters and spaces)
    if (empty($fullname)) {
        $errors['name'] = 'Full name is required.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $fullname)) {
        $errors['name'] = 'Full name should only contain letters and spaces.';
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Phone validation (if provided, must be 10 digits)
    if (empty($email)) {
        $errors['phone'] = 'Phone number is required.';
    }
    elseif (!empty($phone) && !preg_match('/^[0-9]{10}$/', str_replace(['-', ' ', '(', ')'], '', $phone))) {
        $errors['phone'] = 'Phone number must be 10 digits (if provided).';
    }

    // Address validation (Address should not be empty)
    if (empty($address)) {
        $errors['address'] = 'Address is required.';
    }

    // Message validation (Message should not be empty and at least 10 characters)
    if (empty($message)) {
        $errors['message'] = 'Message is required.';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message should be at least 10 characters long.';
    }

    // If there are validation errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Store form data to pre-fill on form reload
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit(0);
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                            //Send using SMTP                 
        $mail->SMTPAuth   = true; //Enable SMTP authentication
        $mail->Host       = 'smtp.gmail.com';    //Set the SMTP server to send through              
        $mail->Username   = 'methsarasenarathne71@gmail.com';                     //SMTP username
        $mail->Password   = 'odtbgedyjwvdwzup';                               //SMTP app password (generate from Gmail)

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('methsarasenarathne71@gmail.com', 'Event Management System');
        $mail->addAddress('methsarasenarathne71@gmail.com', 'Admin');     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Contact form submission from Event Management System';
        $mail->Body    = '
        <html>
        <body>
            <h2>New Contact Form Submission</h2>
            <p><strong>Full Name:</strong> ' . htmlspecialchars($fullname) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
            <p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>
            <p><strong>Address:</strong> ' . htmlspecialchars($address) . '</p>
            <p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>
        </body>
        </html>';

        if($mail->send()){
            $_SESSION['status'] = "Thank you for contacting us. We will get back to you shortly.";
            header("Location: {$_SERVER['HTTP_REFERER']}");
            exit(0);
        } else {
            $_SESSION['status'] = "Message could not be sent. Please try again later.";
            header("Location: {$_SERVER['HTTP_REFERER']}");
            exit(0);
        }
    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit(0);
    }

} else {
    header("Location: contact.php");
    exit(0);
}