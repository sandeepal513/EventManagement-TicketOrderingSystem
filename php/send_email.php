<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendConfirmationEmail($email, $confirmationCode, $orderId) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('noreply@ticketing.com', 'Event Ticketing');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = "Your Event Tickets - $confirmationCode";
        $mail->Body = "
            <h2>Thank You!</h2>
            <p>Confirmation Code: <strong>$confirmationCode</strong></p>
            <p>Order ID: $orderId</p>
            <p>Download your tickets here: tickets_$orderId.pdf</p>
        ";
        
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>