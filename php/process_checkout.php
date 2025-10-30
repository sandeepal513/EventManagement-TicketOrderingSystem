<?php
// ===== process_checkout.php =====
// Handles checkout with NEW ticket_types schema
// NEW SCHEMA: ticket_type (GA/VIP), ticket_status, etc.

session_start();
require_once './connection.php';

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
        $cartDataJson = $_POST['cartData'] ?? '[]';
        
        // Parse cart data from JavaScript
        $selectedTickets = json_decode($cartDataJson, true);

        // --- Validation checks ---
        if (empty($fullName) || empty($email) || empty($address) || empty($city) || empty($zip)) {
            throw new Exception('Please fill all required fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }

        if (empty($selectedTickets) || !is_array($selectedTickets)) {
            throw new Exception('Cart is empty. Please select tickets.');
        }

        if (empty($cardNumber) || strlen($cardNumber) < 13) {
            throw new Exception('Invalid card number.');
        }

        if (empty($cvv) || strlen($cvv) < 3) {
            throw new Exception('Invalid CVV.');
        }

        // --- Start transaction for database consistency ---
        $conn->begin_transaction();

        // --- Simulate payment processing ---
        $paymentProcessed = processPayment($cardNumber, $expiry, $cvv, calculateTotal($selectedTickets));

        if (!$paymentProcessed) {
            throw new Exception('Payment failed. Please try again.');
        }

        // --- Create or get user ---
        $user_id = getCurrentUserId($email, $fullName, $phone);

        // --- Generate confirmation code ---
        $confirmationCode = generateConfirmationCode();

        // --- Calculate totals ---
        $subtotal = 0;
        foreach ($selectedTickets as $ticket) {
            $subtotal += $ticket['quantity'] * $ticket['price'];
        }
        $tax = $subtotal * 0.10;
        $processingFee = 10.00;
        $totalAmount = $subtotal + $tax + $processingFee;

        // --- Create order ---
        $orderId = createOrder($user_id, $confirmationCode, $subtotal, $tax, $processingFee, $totalAmount, 'paid', $fullName, $email, $phone, $address, $city, $zip);

        // --- Add order details AND UPDATE TICKET QUANTITIES ---
        $event_id = 1; // Default event - adjust if needed from session or ticket data
        
        foreach ($selectedTickets as $ticket) {
            $ticketTypeId = $ticket['id'];
            $quantity = $ticket['quantity'];
            $ticketPrice = $ticket['price'];
            $itemSubtotal = $quantity * $ticketPrice;

            // Insert order detail
            addOrderDetails($orderId, $ticketTypeId, $quantity, $itemSubtotal);

            // *** CRITICAL: UPDATE TICKET AVAILABILITY IN DATABASE ***
            decreaseTicketAvailability($ticketTypeId, $quantity);
        }

        // --- Create attendee records (one per ticket purchased) ---
        foreach ($selectedTickets as $ticket) {
            $quantity = $ticket['quantity'];
            // Get ticket type info for display
            $ticketType = $ticket['type']; // GA or VIP
            
            for ($i = 0; $i < $quantity; $i++) {
                $attendeeName = $fullName;
                if ($quantity > 1) {
                    $attendeeName .= " - " . $ticketType . " Ticket " . ($i + 1);
                }
                $qrCode = generateQRCode($orderId, $i);
                addAttendee($orderId, $event_id, $attendeeName, $email, $qrCode);
            }
        }

        // --- Send email confirmation (optional) ---
        sendConfirmationEmail($email, $confirmationCode, $orderId, $fullName);

        // --- Commit transaction ---
        $conn->commit();

        // --- Clear cart from session ---
        unset($_SESSION['cart']);
        
        // --- Save session data for confirmation page ---
        $_SESSION['order'] = array(
            'order_id' => $orderId,
            'confirmation_code' => $confirmationCode,
            'email' => $email,
            'total' => $totalAmount
        );

        // ✅ --- Redirect to confirmation page ---
        header('Location: confirmation.php?order_id=' . $orderId);
        exit;

    } catch (Exception $e) {
        // Rollback on any error
        if ($conn) {
            $conn->rollback();
        }
        
        // Log error for debugging
        error_log("Checkout Error: " . $e->getMessage());
        
        // Show error to user
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
} else {
    echo "Invalid request method.";
    exit;
}

// ===== HELPER FUNCTIONS =====

function processPayment($cardNumber, $expiry, $cvv, $amount) {
    // Simulate payment always successful for demo
    // In production, integrate with payment gateway (Stripe, PayPal, etc.)
    return true;
}

function getCurrentUserId($email, $fullName, $phone = '') {
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
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
    $stmt->bind_param("ssss", $fullName, $email, $password, $phone);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    throw new Exception('Failed to create user.');
}

function generateConfirmationCode() {
    $prefix = 'TKT';
    $timestamp = time();
    $random = strtoupper(substr(md5($timestamp), 0, 6));
    return $prefix . $timestamp . $random;
}

function createOrder($user_id, $confirmationCode, $subtotal, $tax, $processingFee, $totalAmount, $paymentStatus, $customerName, $customerEmail, $customerPhone, $billingAddress, $billingCity, $billingZip) {
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO orders (user_id, confirmation_code, subtotal, tax, processing_fee, total_amount, payment_status, payment_method, customer_name, customer_email, customer_phone, billing_address, billing_city, billing_zip) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 'card', ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->bind_param(
        "isdddssssss", 
        $user_id, $confirmationCode, $subtotal, $tax, $processingFee, $totalAmount, $paymentStatus, $customerName, $customerEmail, $customerPhone, $billingAddress, $billingCity, $billingZip
    );

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    throw new Exception('Failed to create order: ' . $stmt->error);
}

function addOrderDetails($orderId, $ticketTypeId, $quantity, $subtotal) {
    global $conn;

    // Get ticket price from ticket_types table - UPDATED QUERY FOR NEW SCHEMA
    $stmt = $conn->prepare("SELECT price FROM ticket_types WHERE ticket_type_id = ?");
    $stmt->bind_param("i", $ticketTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception('Ticket type not found.');
    }
    
    $row = $result->fetch_assoc();
    $unitPrice = $row['price'];

    $stmt = $conn->prepare("INSERT INTO order_details (order_id, ticket_type_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidi", $orderId, $ticketTypeId, $quantity, $unitPrice, $subtotal);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add order details: ' . $stmt->error);
    }
}

function decreaseTicketAvailability($ticketTypeId, $quantity) {
    global $conn;

    // First, check if enough tickets are available - UPDATED QUERY FOR NEW SCHEMA
    $stmt = $conn->prepare("SELECT available_quantity FROM ticket_types WHERE ticket_type_id = ?");
    $stmt->bind_param("i", $ticketTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception('Ticket type not found.');
    }
    
    $row = $result->fetch_assoc();
    $availableQty = $row['available_quantity'];
    
    if ($availableQty < $quantity) {
        throw new Exception('Not enough tickets available for this type.');
    }

    // *** UPDATE TICKET AVAILABILITY ***
    $stmt = $conn->prepare("UPDATE ticket_types SET available_quantity = available_quantity - ? WHERE ticket_type_id = ?");
    $stmt->bind_param("ii", $quantity, $ticketTypeId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update ticket availability: ' . $stmt->error);
    }
}

function generateQRCode($orderId, $ticketNumber) {
    // Generate a unique ticket hash
    $uniqueHash = bin2hex(random_bytes(16));
    return 'QR-' . $orderId . '-' . $ticketNumber . '-' . $uniqueHash;
}

function addAttendee($orderId, $eventId, $name, $email, $qrCode) {
    global $conn;

    $uniqueHash = bin2hex(random_bytes(16));
    $stmt = $conn->prepare("INSERT INTO attendees (order_id, event_id, attendee_name, email, qr_code, unique_ticket_hash, check_in_status) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("iissss", $orderId, $eventId, $name, $email, $qrCode, $uniqueHash);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add attendee: ' . $stmt->error);
    }
}

function sendConfirmationEmail($email, $confirmationCode, $orderId, $customerName) {
    $subject = "Your Event Tickets - Confirmation Code: $confirmationCode";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .code { background: #f0f0f0; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Your Purchase! 🎉</h1>
            </div>
            <div class='content'>
                <p>Hi " . htmlspecialchars($customerName) . ",</p>
                <p>Your order has been confirmed! Your tickets are ready.</p>
                <p><strong>Order ID:</strong> #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . "</p>
                <p><strong>Confirmation Code:</strong></p>
                <div class='code'>" . htmlspecialchars($confirmationCode) . "</div>
                <p>Please visit our website to download your tickets or view them here.</p>
                <p>If you have any questions, please contact our support team.</p>
                <p>Best regards,<br>Event Ticketing Team</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    // Uncomment the line below to send real email
    // mail($email, $subject, $message, $headers);
}

function calculateTotal($selectedTickets) {
    $subtotal = 0;
    foreach ($selectedTickets as $ticket) {
        $subtotal += $ticket['quantity'] * $ticket['price'];
    }
    $tax = $subtotal * 0.10;
    $processingFee = 10.00;
    return $subtotal + $tax + $processingFee;
}
?>