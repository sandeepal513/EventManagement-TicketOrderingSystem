<?php
// ===== generate_pdf.php =====
// Generate PDF tickets for orders
// IMPORTANT: This file must be called FIRST before any HTML output

// Start output buffering to catch any accidental output
ob_start();

// Only set headers if not already sent
if (!headers_sent()) {
    // Include database configuration
    require_once './connection.php';
    
    // Get order_id from GET parameter
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    
    if ($order_id <= 0) {
        ob_end_clean();
        header('HTTP/1.0 400 Bad Request');
        die('Invalid order ID');
    }
    
    try {
        // Fetch order details
        $stmt = $conn->prepare("
            SELECT 
                o.order_id,
                o.confirmation_code,
                o.customer_name,
                o.customer_email,
                o.total_amount,
                o.order_date,
                e.event_name,
                e.event_date,
                e.event_time,
                e.venue
            FROM orders o
            LEFT JOIN order_details od ON o.order_id = od.order_id
            LEFT JOIN ticket_types tt ON od.ticket_type_id = tt.ticket_type_id
            LEFT JOIN events e ON tt.event_id = e.event_id
            WHERE o.order_id = ?
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            ob_end_clean();
            header('HTTP/1.0 404 Not Found');
            die('Order not found');
        }
        
        $order = $result->fetch_assoc();
        $stmt->close();
        
        // Fetch attendees with QR codes
        $stmt = $conn->prepare("
            SELECT 
                a.attendee_id,
                a.attendee_name,
                a.email,
                a.qr_code,
                a.ticket_number,
                a.unique_ticket_hash
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
        
        // Fetch order details (tickets)
        $stmt = $conn->prepare("
            SELECT 
                od.quantity,
                od.unit_price,
                od.subtotal,
                tt.ticket_name
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
        
        // Clear any output that may have been generated
        ob_end_clean();
        
        // Check if TCPDF is installed
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            // Fallback: Generate HTML PDF if TCPDF not available
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="tickets_' . $order_id . '.html"');
            generateHtmlPdf($order, $attendees, $tickets);
            exit;
        }
        
        // Load TCPDF
        require_once __DIR__ . '/../vendor/autoload.php';
        
        // Create PDF object
        // $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT_CM, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // generate_pdf.php:119 - FIXED LINE using A4, Portrait, and Millimeters
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Event Ticketing System');
        $pdf->SetAuthor('Event Organizer');
        $pdf->SetTitle('Event Tickets - ' . $order['confirmation_code']);
        $pdf->SetSubject('Ticket Confirmation');
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Add page
        $pdf->AddPage();
        
        // Header
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(147, 51, 234); // Purple
        $pdf->Cell(0, 15, 'EVENT TICKETS', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 8, 'Confirmation: ' . $order['confirmation_code'], 0, 1, 'C');
        
        $pdf->Ln(5);
        
        // Event Details Section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, 'Event Details', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(50, 50, 50);
        
        // Event information table
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(40, 7, 'Event:', 0, 0, 'L', true);
        $pdf->Cell(0, 7, $order['event_name'] ?? 'N/A', 0, 1, 'L', true);
        
        $pdf->Cell(40, 7, 'Date:', 0, 0, 'L', false);
        $pdf->Cell(0, 7, ($order['event_date'] ?? 'N/A'), 0, 1, 'L', false);
        
        $pdf->Cell(40, 7, 'Time:', 0, 0, 'L', true);
        $pdf->Cell(0, 7, ($order['event_time'] ?? 'N/A'), 0, 1, 'L', true);
        
        $pdf->Cell(40, 7, 'Venue:', 0, 0, 'L', false);
        $pdf->Cell(0, 7, ($order['venue'] ?? 'N/A'), 0, 1, 'L', false);
        
        $pdf->Ln(5);
        
        // Order Information
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Order Information', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(40, 7, 'Name:', 0, 0, 'L', true);
        $pdf->Cell(0, 7, $order['customer_name'], 0, 1, 'L', true);
        
        $pdf->Cell(40, 7, 'Email:', 0, 0, 'L', false);
        $pdf->Cell(0, 7, $order['customer_email'], 0, 1, 'L', false);
        
        $pdf->Cell(40, 7, 'Order Date:', 0, 0, 'L', true);
        $date = new DateTime($order['order_date']);
        $pdf->Cell(0, 7, $date->format('M d, Y H:i A'), 0, 1, 'L', true);
        
        $pdf->Cell(40, 7, 'Total Amount:', 0, 0, 'L', false);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, '$' . number_format($order['total_amount'], 2), 0, 1, 'L', false);
        
        $pdf->Ln(5);
        
        // Attendees Section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, 'Your Tickets (' . count($attendees) . ')', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        
        // For each attendee, create a ticket section
        foreach ($attendees as $index => $attendee) {
            if ($index > 0) {
                $pdf->AddPage();
            }
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(0, 10, 'Ticket ' . ($index + 1) . ' - ' . $attendee['attendee_name'], 0, 1, 'L', true);
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetFillColor(245, 245, 245);
            
            $pdf->Cell(40, 7, 'Name:', 0, 0, 'L', true);
            $pdf->Cell(0, 7, $attendee['attendee_name'], 0, 1, 'L', true);
            
            $pdf->Cell(40, 7, 'Email:', 0, 0, 'L', false);
            $pdf->Cell(0, 7, $attendee['email'] ?? 'N/A', 0, 1, 'L', false);
            
            if (!empty($attendee['ticket_number'])) {
                $pdf->Cell(40, 7, 'Ticket #:', 0, 0, 'L', true);
                $pdf->Cell(0, 7, $attendee['ticket_number'], 0, 1, 'L', true);
            }
            
            $pdf->Cell(40, 7, 'Hash:', 0, 0, 'L', false);
            $pdf->Cell(0, 7, substr($attendee['unique_ticket_hash'], 0, 20) . '...', 0, 1, 'L', false);
            
            // QR Code (if available)
            if (!empty($attendee['qr_code']) && file_exists(__DIR__ . '/../' . $attendee['qr_code'])) {
                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 7, 'Your QR Code:', 0, 1);
                
                try {
                    $imagePath = __DIR__ . '/../' . $attendee['qr_code'];
                    $pdf->Image($imagePath, 50, $pdf->GetY(), 100, 100, 'PNG');
                    $pdf->SetY($pdf->GetY() + 100);
                } catch (Exception $e) {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->Cell(0, 7, 'QR Code: ' . htmlspecialchars($attendee['unique_ticket_hash']), 0, 1);
                }
            }
            
            $pdf->Ln(10);
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 5, 'This ticket is non-transferable. Please present this PDF or show QR code at entrance.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'For support, contact: support@eventtickets.com', 0, 1, 'C');
        
        // Close and output PDF document
        $pdf->Output('tickets_' . $order_id . '.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        ob_end_clean();
        error_log('PDF Generation Error: ' . $e->getMessage());
        header('HTTP/1.0 500 Internal Server Error');
        die('Error generating PDF: ' . htmlspecialchars($e->getMessage()));
    }
    
} else {
    // Headers already sent
    ob_end_clean();
    die('Error: Headers already sent. Cannot generate PDF.');
}

// ===== FALLBACK HTML PDF GENERATOR =====
function generateHtmlPdf($order, $attendees, $tickets) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Event Tickets</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .page { page-break-after: always; padding: 40px; max-width: 800px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 30px; color: #9333ea; }
            .header h1 { font-size: 32px; margin-bottom: 5px; }
            .header p { color: #666; font-size: 12px; }
            .section { margin-bottom: 30px; }
            .section h2 { font-size: 16px; color: #000; margin-bottom: 15px; border-bottom: 2px solid #9333ea; padding-bottom: 8px; }
            .info-row { display: flex; margin-bottom: 8px; }
            .info-row span { flex: 1; }
            .info-row strong { flex: 1; font-weight: bold; }
            .ticket { border: 2px solid #9333ea; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #f9f9f9; }
            .ticket h3 { color: #9333ea; margin-bottom: 10px; }
            .footer { text-align: center; font-size: 10px; color: #999; margin-top: 40px; border-top: 1px solid #ccc; padding-top: 15px; }
            @media print {
                .page { page-break-after: always; }
                body { margin: 0; padding: 0; }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="header">
                <h1>EVENT TICKETS</h1>
                <p>Confirmation: <?php echo htmlspecialchars($order['confirmation_code']); ?></p>
            </div>
            
            <div class="section">
                <h2>Event Details</h2>
                <div class="info-row">
                    <span><strong>Event:</strong></span>
                    <span><?php echo htmlspecialchars($order['event_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span><strong>Date:</strong></span>
                    <span><?php echo htmlspecialchars($order['event_date'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span><strong>Time:</strong></span>
                    <span><?php echo htmlspecialchars($order['event_time'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span><strong>Venue:</strong></span>
                    <span><?php echo htmlspecialchars($order['venue'] ?? 'N/A'); ?></span>
                </div>
            </div>
            
            <div class="section">
                <h2>Order Information</h2>
                <div class="info-row">
                    <span><strong>Name:</strong></span>
                    <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-row">
                    <span><strong>Email:</strong></span>
                    <span><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="info-row">
                    <span><strong>Total Amount:</strong></span>
                    <span><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></span>
                </div>
            </div>
            
            <div class="section">
                <h2>Your Tickets (<?php echo count($attendees); ?>)</h2>
                <?php foreach ($attendees as $index => $attendee): ?>
                    <div class="ticket">
                        <h3>Ticket <?php echo ($index + 1); ?> - <?php echo htmlspecialchars($attendee['attendee_name']); ?></h3>
                        <div class="info-row">
                            <span><strong>Email:</strong></span>
                            <span><?php echo htmlspecialchars($attendee['email'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Ticket Hash:</strong></span>
                            <span><?php echo htmlspecialchars($attendee['unique_ticket_hash']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="footer">
                <p>This ticket is non-transferable. Please present this PDF or show QR code at entrance.</p>
                <p>For support, contact: support@eventtickets.com</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>