<?php

if (isset($_POST['update_ticket']) && isset($_POST['ticket_type_id'])) {
    
    $ticket_id = intval($_POST['ticket_type_id']);
    
    //Validation Checks
    $errors = [];
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $total_quantity = filter_var($_POST['total_quantity'], FILTER_VALIDATE_INT);
    $available_quantity = filter_var($_POST['available_quantity'], FILTER_VALIDATE_INT);
    
    $sale_start = trim($_POST['sale_start'] ?? ''); 
    $sale_end = trim($_POST['sale_end'] ?? '');     

    if (empty($_POST['event_id'])) { $errors['event_id'] = "Select event."; }
    if ($price === false || $price <= 0) { $errors['price'] = "Invalid price."; }
    if ($total_quantity === false || $total_quantity <= 0) { $errors['total_quantity'] = "Invalid total quantity."; }
    if ($available_quantity > $total_quantity || $available_quantity < 0) { $errors['available_quantity'] = "Available quantity must be 0 to total quantity."; }

    $valid_ticket_types = ['GA', 'VIP', 'EARLYBIRD', 'COMPLIMENTARY'];
    if (empty($_POST['ticket_type']) || !in_array($_POST['ticket_type'], $valid_ticket_types)) { 
        $errors['ticket_type'] = "Invalid ticket type."; 
    }
    
    $event_id = intval($_POST['event_id']);
    $fk_check = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
    $fk_check->bind_param("i", $event_id);
    $fk_check->execute();
    if ($fk_check->get_result()->num_rows === 0) { 
        $errors['event_id'] = "Invalid event ID.";  
    }
    $fk_check->close();
    

    $start_timestamp = !empty($sale_start) ? strtotime($sale_start) : 0;
    $end_timestamp = !empty($sale_end) ? strtotime($sale_end) : 0;

 // Date Validation
if (empty($formData['sale_start'])) { 
    $errors['sale_start'] = "Sale Start date is required."; 
}

if (empty($formData['sale_end'])) { 
    $errors['sale_end'] = "Sale End date is required."; 
}

if (!empty($formData['sale_start']) && strtotime($formData['sale_start']) === false) {
    $errors['sale_start'] = "Invalid Sale Start date format.";
}

if (!empty($formData['sale_end']) && strtotime($formData['sale_end']) === false) {
    $errors['sale_end'] = "Invalid Sale End date format.";
}

$start_timestamp = strtotime($formData['sale_start']);
$end_timestamp = strtotime($formData['sale_end']);

if ($start_timestamp && $end_timestamp && $start_timestamp >= $end_timestamp) {
    $errors['sale_end'] = "Sale End date must be after Sale Start date.";
}

    

    if (empty($errors)) {
        
        // Capture data 
        $ticket_type = $_POST['ticket_type'];
        
        
        
        $sale_start_db = date('Y-m-d H:i:s', $start_timestamp);
        $sale_end_db = date('Y-m-d H:i:s', $end_timestamp);
        
    $stmt = $conn->prepare("UPDATE ticket_types SET 
    event_id = ?, ticket_type = ?, price = ?, total_quantity = ?, 
    available_quantity = ?, sale_start = ?, sale_end = ? 
    WHERE ticket_type_id = ?");
    
// Types: i s d i i s s i
$stmt->bind_param("isdiissi", 
    $event_id, 
    $ticket_type,           
    $price, 
    $total_quantity, 
    $available_quantity,    
    $sale_start_db, 
    $sale_end_db, 
    $ticket_id
);

        
        if ($stmt->execute()) {
            $redirect_message = urlencode("✅ Ticket updated successfully!");
            header("Location: ticket.php?status=updated&message=$redirect_message");
            exit;
        } else {
            // If this fails, the error message from MySQL will be sent back
            $redirect_message = urlencode("❌ Database Error (Update): " . $stmt->error);
            header("Location: ticket.php?status=error&message=$redirect_message");
            exit;
        }
        $stmt->close();
    } else {
        // Validation failure redirect 

        $formData = [
            'ticket_type_id' => $ticket_id,
            'event_id' => $_POST['event_id'] ?? '',
            'ticket_type' => $_POST['ticket_type'] ?? '',
            'price' => $_POST['price'] ?? '',
            'total_quantity' => $_POST['total_quantity'] ?? '',
            'available_quantity' => $_POST['available_quantity'] ?? '',
            'sale_start' => $sale_start, 
            'sale_end' => $sale_end 
        ];

        $encoded_errors = urlencode(base64_encode(json_encode($errors)));
        $encoded_form_data = urlencode(base64_encode(json_encode($formData)));
        $error_summary = implode(', ', array_values($errors));  // For alert message
        $redirect_message = urlencode("❌ Validation failed: $error_summary");
        
        header("Location: ticket.php?id=$ticket_id&status=validation_failed&message=$redirect_message&validation_errors=$encoded_errors&form_data=$encoded_form_data");
        exit;
    }
}
?>