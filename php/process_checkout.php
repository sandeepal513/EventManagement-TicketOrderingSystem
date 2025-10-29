<?php
// ===== process_checkout.php =====
// This file handles checkout form submission and payment processing

session_start();
require_once './connection.php'; // your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- Sanitize and validate inputs ---
        $fullName = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $cardName = trim($_POST['cardName'] ?? '');
        $cardNumber = trim($_POST['cardNumber'] ?? '');
        $expiry = trim($_POST['expiry'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');

        // --- Validation checks ---
        if (empty($fullName) || empty($email) || empty($address) || empty($city) || empty($zip)) {
            throw new Exception('Please fill all required fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }

        if (empty($cardNumber) || strlen($cardNumber) < 13) {
            throw new Exception('Invalid card number.');
        }

        if (empty($cvv) || strlen($cvv) < 3) {
            throw new Exception('Invalid CVV.');
        }

        // --- Simulate payment processing ---
        $paymentProcessed = processPayment($cardNumber, $expiry, $cvv, 284.97);

        if (!$paymentProcessed) {
            throw new Exception('Payment failed. Please try again.');
        }

        // --- Create or get user ---
        $user_id = getCurrentUserId($email, $fullName);

        // --- Generate confirmation code ---
        $confirmationCode = generateConfirmationCode();

        // --- Create order ---
        $orderId = createOrder($user_id, $confirmationCode, 284.97, 'paid');

        // --- Add order details ---
        addOrderDetails($orderId, 1, 2, 99.98);  // Example ticket type
        addOrderDetails($orderId, 2, 1, 149.99); // Example ticket type

        // --- Add attendee(s) ---
        $attendees = array(
            array('name' => $fullName, 'email' => $email)
        );

        foreach ($attendees as $attendee) {
            $qrCode = generateQRCode($orderId, $attendee['email']);
            addAttendee($orderId, 1, $attendee['name'], $attendee['email'], $qrCode);
        }

        // --- Send email confirmation (optional) ---
        sendConfirmationEmail($email, $confirmationCode, $orderId);

        // --- Save session data for confirmation page ---
        $_SESSION['order'] = array(
            'order_id' => $orderId,
            'confirmation_code' => $confirmationCode,
            'email' => $email,
            'total' => 284.97
        );

        // ✅ --- Redirect to confirmation page ---
        header('Location: confirmation.php?order_id=' . $orderId);
        exit;

    } catch (Exception $e) {
        // If an error occurs, show it on the same page or redirect back with error
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
        exit;
    }
} else {
    echo "Invalid request method.";
    exit;
}

// ===== HELPER FUNCTIONS =====

function processPayment($cardNumber, $expiry, $cvv, $amount) {
    // Simulate payment always successful for demo
    return true;
}

function getCurrentUserId($email, $fullName) {
    global $conn;

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['user_id'];
    }

    // Create new user if not found
    $password = password_hash('temp_' . time(), PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $fullName, $email, $password);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    throw new Exception('Failed to create user.');
}

function generateConfirmationCode() {
    $prefix = 'TECH';
    $random = strtoupper(bin2hex(random_bytes(6)));
    return $prefix . substr($random, 0, 8);
}

function createOrder($user_id, $confirmationCode, $totalAmount, $paymentStatus) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status, confirmation_code, payment_method) VALUES (?, ?, ?, ?, 'card')");
    $stmt->bind_param("idss", $user_id, $totalAmount, $paymentStatus, $confirmationCode);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    throw new Exception('Failed to create order.');
}

function addOrderDetails($orderId, $ticketTypeId, $quantity, $subtotal) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO order_details (order_id, ticket_type_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $orderId, $ticketTypeId, $quantity, $subtotal);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add order details.');
    }
}

function generateQRCode($orderId, $email) {
    return 'placeholder_qr_' . $orderId;
}

function addAttendee($orderId, $eventId, $name, $email, $qrCode) {
    global $conn;

    $uniqueHash = bin2hex(random_bytes(16));
    $stmt = $conn->prepare("INSERT INTO attendees (order_id, event_id, attendee_name, email, qr_code, unique_ticket_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $orderId, $eventId, $name, $email, $qrCode, $uniqueHash);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add attendee.');
    }
}

function sendConfirmationEmail($email, $confirmationCode, $orderId) {
    $subject = "Your Event Tickets - Confirmation Code: $confirmationCode";
    $message = "
    <h2>Thank you for your purchase!</h2>
    <p>Your confirmation code is: <strong>$confirmationCode</strong></p>
    <p>Order ID: $orderId</p>
    <p>Please visit our website to download your tickets.</p>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    // Uncomment below line to send real email
    // mail($email, $subject, $message, $headers);
}
?>
