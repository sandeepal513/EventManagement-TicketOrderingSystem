<?php
// ===== confirmation.php =====
// Order Confirmation Page
// Displays order details after successful checkout

session_start();

// Include database configuration
require_once './connection.php';

// Check if order_id is provided
$order_id = $_GET['order_id'] ?? $_SESSION['order_id'] ?? null;

if (!$order_id) {
    header('Location: checkout.php');
    exit;
}

// ===== FETCH ORDER DETAILS =====
try {
    // Get Order Information
    $stmt = $conn->prepare("
        SELECT 
            o.order_id,
            o.confirmation_code,
            o.customer_name,
            o.customer_email,
            o.customer_phone,
            o.total_amount,
            o.subtotal,
            o.tax,
            o.processing_fee,
            o.payment_status,
            o.payment_method,
            o.order_date,
            o.billing_address,
            o.billing_city,
            o.billing_zip
        FROM orders o
        WHERE o.order_id = ?
    ");
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        die("Order not found!");
    }
    
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Get Order Details (Tickets)
    $stmt = $conn->prepare("
        SELECT 
            od.order_detail_id,
            od.quantity,
            od.unit_price,
            od.subtotal,
            tt.ticket_name,
            tt.description
        FROM order_details od
        JOIN ticket_types tt ON od.ticket_type_id = tt.ticket_type_id
        WHERE od.order_id = ?
    ");
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $ticketsResult = $stmt->get_result();
    $tickets = [];
    
    while ($row = $ticketsResult->fetch_assoc()) {
        $tickets[] = $row;
    }
    $stmt->close();
    
    // Get Attendees (with QR codes)
    $stmt = $conn->prepare("
        SELECT 
            a.attendee_id,
            a.attendee_name,
            a.email,
            a.qr_code,
            a.unique_ticket_hash,
            a.ticket_number
        FROM attendees a
        WHERE a.order_id = ?
    ");
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $attendeesResult = $stmt->get_result();
    $attendees = [];
    
    while ($row = $attendeesResult->fetch_assoc()) {
        $attendees[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching order details: " . $e->getMessage());
    die("Error loading order details. Please contact support.");
}

// Format order date
$orderDate = new DateTime($order['order_date']);
$formattedDate = $orderDate->format('M d, Y');
$formattedTime = $orderDate->format('h:i A');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Event Ticketing System</title>
    <link rel="stylesheet" href="./../css/confirmation.css">
</head>
<body>
    <div class="container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <span>Checkout</span>
            </div>
            <div class="step-line active"></div>
            <div class="step active">
                <div class="step-number">2</div>
                <span>Confirmation</span>
            </div>
        </div>

        <!-- Success Banner -->
        <div class="success-banner">
            <div class="success-icon">✓</div>
            <h1>Purchase Successful! 🎉</h1>
            <p>Your tickets have been confirmed and sent to your email</p>
        </div>

        <div class="confirmation-wrapper">
            <!-- Left Column -->
            <div class="confirmation-main">

                <!-- Confirmation Details -->
                <div class="card">
                    <h3>Confirmation Details</h3>
                    <div class="details-row">
                        <span class="label">Confirmation Code</span>
                        <div class="code-container">
                            <code id="confirmCode"><?php echo htmlspecialchars($order['confirmation_code']); ?></code>
                            <button class="copy-btn" onclick="copyToClipboard()">📋 Copy</button>
                        </div>
                    </div>
                    <div class="details-row">
                        <span class="label">Order Number</span>
                        <span>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="details-row">
                        <span class="label">Order Date & Time</span>
                        <span><?php echo $formattedDate . ' at ' . $formattedTime; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="label">Payment Status</span>
                        <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    <div class="details-row">
                        <span class="label">Total Amount</span>
                        <span class="amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>

                <!-- Order Breakdown -->
                <div class="card">
                    <h3>Order Breakdown</h3>
                    <div class="breakdown-table">
                        <div class="breakdown-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>Tax</span>
                            <span>$<?php echo number_format($order['tax'], 2); ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>Processing Fee</span>
                            <span>$<?php echo number_format($order['processing_fee'], 2); ?></span>
                        </div>
                        <div class="breakdown-row total">
                            <span>Total</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Ticket Summary -->
                <div class="card">
                    <h3>Your Tickets</h3>
                    <div class="tickets-list">
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-item">
                                <div class="ticket-info">
                                    <h4><?php echo htmlspecialchars($ticket['ticket_name']); ?></h4>
                                    <?php if (!empty($ticket['description'])): ?>
                                        <p class="ticket-description"><?php echo htmlspecialchars($ticket['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="ticket-quantity">Quantity: <?php echo $ticket['quantity']; ?></p>
                                </div>
                                <span class="ticket-price">$<?php echo number_format($ticket['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Digital Tickets / QR Codes -->
                <?php if (!empty($attendees)): ?>
                <div class="card">
                    <h3>Digital Tickets</h3>
                    <p class="subtitle">Each attendee has a unique ticket. Present it at the event entrance or scan the QR code.</p>
                    <div class="tickets-grid">
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="ticket-qr">
                                <?php if (!empty($attendee['qr_code'])): ?>
                                    <div class="qr-code-container">
                                        <img src="<?php echo htmlspecialchars($attendee['qr_code']); ?>" 
                                             alt="QR Code for <?php echo htmlspecialchars($attendee['attendee_name']); ?>"
                                             class="qr-code">
                                    </div>
                                <?php else: ?>
                                    <div class="qr-placeholder">
                                        📱 QR CODE<br>
                                        <small><?php echo htmlspecialchars($attendee['unique_ticket_hash']); ?></small>
                                    </div>
                                <?php endif; ?>
                                <p class="attendee-name"><?php echo htmlspecialchars($attendee['attendee_name']); ?></p>
                                <p class="attendee-email"><?php echo htmlspecialchars($attendee['email']); ?></p>
                                <?php if (!empty($attendee['ticket_number'])): ?>
                                    <p class="ticket-number">Ticket #<?php echo htmlspecialchars($attendee['ticket_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="card info-card">
                    <h3>ℹ️ Next Steps</h3>
                    <ul class="steps-list">
                        <li>✓ Check your email (<?php echo htmlspecialchars($order['customer_email']); ?>) for ticket details and QR codes</li>
                        <li>✓ Download or print your tickets</li>
                        <li>✓ Add event to your calendar</li>
                        <li>✓ Arrive 15 minutes early on the event date</li>
                        <li>✓ Show your QR code or ticket at the entrance for check-in</li>
                    </ul>
                </div>

            </div>

            <!-- Right Column - Sidebar -->
            <div class="confirmation-sidebar">

                <!-- Download Options -->
                <div class="card">
                    <h3>Ticket Options</h3>
                    <button class="btn-secondary btn-block" onclick="downloadPDF()">📥 Download PDF</button>
                    <button class="btn-secondary btn-block" onclick="resendEmail()">📧 Resend Email</button>
                    <button class="btn-secondary btn-block" onclick="printPage()">🖨️ Print</button>
                </div>

                <!-- Billing Information -->
                <div class="card">
                    <h3>Billing Information</h3>
                    <div class="billing-info">
                        <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
                        <p><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        <hr>
                        <p><?php echo htmlspecialchars($order['billing_address']); ?></p>
                        <p><?php echo htmlspecialchars($order['billing_city']); ?>, <?php echo htmlspecialchars($order['billing_zip']); ?></p>
                    </div>
                </div>

                <!-- Support -->
                <div class="card support-card">
                    <p class="support-text">Need help?</p>
                    <a href="contact.php" class="support-link">Contact Support →</a>
                </div>

                <!-- Order Summary Box -->
                <div class="card">
                    <h3>Order Summary</h3>
                    <div class="summary-box">
                        <p class="summary-item">
                            <span>Tickets:</span>
                            <strong><?php echo count($attendees); ?></strong>
                        </p>
                        <p class="summary-item">
                            <span>Total:</span>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </p>
                        <p class="summary-item">
                            <span>Confirmation:</span>
                            <strong><?php echo substr($order['confirmation_code'], 0, 8); ?>...</strong>
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="js/confirmation.js"></script>
    <script>
        // Get order ID for actions
        const orderId = <?php echo $order_id; ?>;
        const confirmationCode = "<?php echo $order['confirmation_code']; ?>";

        // Copy confirmation code to clipboard
        function copyToClipboard() {
            const code = document.getElementById('confirmCode').innerText;
            navigator.clipboard.writeText(code).then(() => {
                alert('Confirmation code copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy. Please try again.');
            });
        }

        // Download PDF
        function downloadPDF() {
            window.location.href = './generate_pdf.php?order_id=' + orderId;
        }

        // Resend Email
        function resendEmail() {
            if (confirm('Are you sure you want to resend the confirmation email?')) {
                fetch('php/send_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=resend&order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email sent successfully!');
                    } else {
                        alert('Failed to send email: ' + data.message);
                    }
                })
                .catch(error => alert('Error: ' + error));
            }
        }

        // Print Page
        function printPage() {
            window.print();
        }
    </script>
</body>
</html>