<?php

if (isset($_POST['update_ticket']) && isset($_POST['ticket_type_id'])) {
    
    $ticket_id = intval($_POST['ticket_type_id']);
    
    //Validation Checks
    $errors = [];
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $total_quantity = filter_var($_POST['total_quantity'], FILTER_VALIDATE_INT);
    
    // Available quantity is not submitted in edit, so we don't validate it here.
    // It will be updated if the total_quantity is changed, or handled by sales logic.
    
    $sale_start = trim($_POST['sale_start'] ?? ''); 
    $sale_end = trim($_POST['sale_end'] ?? '');     

    if (empty($_POST['event_id'])) { $errors['event_id'] = "Select event."; }
    if ($price === false || $price <= 0) { $errors['price'] = "Invalid price."; }
    if ($total_quantity === false || $total_quantity <= 0) { $errors['total_quantity'] = "Invalid total quantity."; }
    
    $valid_ticket_types = ['GA', 'VIP', 'EARLYBIRD', 'COMPLIMENTARY'];
    if (empty($_POST['ticket_type']) || !in_array($_POST['ticket_type'], $valid_ticket_types)) { 
        $errors['ticket_type'] = "Invalid ticket type."; 
    }
    
    $event_id = intval($_POST['event_id']);
    // BUG FIX: Corrected table name from 'events' to 'event'
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
    if (empty($sale_start)) { 
        $errors['sale_start'] = "Sale Start date is required."; 
    }

    if (empty($sale_end)) { 
        $errors['sale_end'] = "Sale End date is required."; 
    }

    if (!$start_timestamp) {
        $errors['sale_start'] = "Invalid Sale Start date format.";
    }

    if (!$end_timestamp) {
        $errors['sale_end'] = "Invalid Sale End date format.";
    }

    if ($start_timestamp && $end_timestamp && $start_timestamp >= $end_timestamp) {
        $errors['sale_end'] = "Sale End date must be after Sale Start date.";
    }

    

    if (empty($errors)) {
        
        // Capture data 
        $ticket_type = $_POST['ticket_type'];
        
        $sale_start_db = date('Y-m-d H:i:s', $start_timestamp);
        $sale_end_db = date('Y-m-d H:i:s', $end_timestamp);
        
        // Note: We are not updating available_quantity here.
        // That should be handled by sales logic or a separate process.
        // We only update total_quantity.
        $stmt = $conn->prepare("UPDATE ticket_types SET 
            event_id = ?, ticket_type = ?, price = ?, total_quantity = ?, 
            sale_start = ?, sale_end = ? 
            WHERE ticket_type_id = ?");
        
        // Types: i s d i s s i
        $stmt->bind_param("isdissi", 
            $event_id, 
            $ticket_type,           
            $price, 
            $total_quantity, 
            $sale_start_db, 
            $sale_end_db, 
            $ticket_id
        );

        // === MODIFICATION: Use SESSION for messages and redirect ===
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Ticket updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to clean page
            exit;
        } else {
            $_SESSION['message'] = "❌ Database Error (Update): " . $stmt->error;
            $_SESSION['message_type'] = "danger";
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$ticket_id"); // Redirect back to edit
            exit;
        }
        $stmt->close();
    } else {
        // === MODIFICATION: Use SESSION for validation errors and redirect ===
        $_SESSION['message'] = "❌ Validation failed. Please correct the errors.";
        $_SESSION['message_type'] = "danger";
        $_SESSION['errors'] = $errors;
        
        // Repopulate form data for sticky form
        $_SESSION['form_data'] = [
            'ticket_type_id' => $ticket_id,
            'event_id' => $_POST['event_id'] ?? '',
            'ticket_type' => $_POST['ticket_type'] ?? '',
            'price' => $_POST['price'] ?? '',
            'total_quantity' => $_POST['total_quantity'] ?? '',
            'sale_start' => $sale_start, 
            'sale_end' => $sale_end 
        ];
        
        // Redirect back to the edit page
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$ticket_id");
        exit;
    }
}
?>
