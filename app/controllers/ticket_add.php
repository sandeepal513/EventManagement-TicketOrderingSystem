<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include_once __DIR__ . '/../../config/constants.php';
    include ROOT . '/config/connection.php';

    // Initialization 
    $edit_mode = false;
    $ticket_id = $_GET['id'] ?? null;
    $form_title = " Define New Ticket Type";
    $submit_button_text = "<i class='fas fa-plus-circle'></i> Add Ticket Type";


    // === MODIFICATION START ===
    // Read validation errors and form data from SESSION, not GET
    $errors = $_SESSION['errors'] ?? [];
    $form_data_from_session = $_SESSION['form_data'] ?? [];
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);

    $formData = [
        'ticket_type_id' => '', 
        'event_id' => '',
        'ticket_type' => '',
        'price' => '',
        'total_quantity' => '',
        'available_quantity' => '',
        'sale_start' => '',
        'sale_end' => ''
    ];

    // Functions
    function fetchTicketData($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM ticket_types WHERE ticket_type_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    function format_datetime_for_input($datetime) {
        if (empty($datetime)) return '';
        return date('Y-m-d\TH:i', strtotime($datetime));
    }

    // === MODIFICATION START ===
    // If we have data from a failed validation, use it to make the form sticky
    if (!empty($form_data_from_session)) {
        $formData = array_merge($formData, $form_data_from_session);
        $formData['sale_start'] = format_datetime_for_input($formData['sale_start']);
        $formData['sale_end'] = format_datetime_for_input($formData['sale_end']);
    }

    // Load Data for Edit
    if ($ticket_id && is_numeric($ticket_id) && empty($form_data_from_session)) { // Only load from DB if not a validation redirect
        $db_data = fetchTicketData($conn, $ticket_id);
        
        if ($db_data) {
            $edit_mode = true;
            $form_title = " Edit Ticket Details (ID: " . htmlspecialchars($ticket_id) . ")";
            $submit_button_text = "<i class='fas fa-save'></i> Save Changes";

            $formData = array_merge($formData, $db_data);
            $formData['ticket_type_id'] = $ticket_id;
            $formData['sale_start'] = format_datetime_for_input($formData['sale_start']);
            $formData['sale_end'] = format_datetime_for_input($formData['sale_end']);
        } else {
            // Invalid ID, just reload the page clean
            header("Location: " . $_SERVER['PHP_SELF']); 
            exit;
        }
    }

    // --- INCLUDE UPDATE LOGIC ---
    // This file will handle the POST for 'update_ticket'
    include ROOT . '/app/controllers/ticket_update.php';

    include ROOT . '/app/controllers/ticket_delete.php';

    // --- Handle ADD (Insert) Logic 
    if (isset($_POST['add_ticket'])) { 
        // Capture POST data and populate $formData
        foreach ($formData as $key => $value) {
            $formData[$key] = $_POST[$key] ?? '';
        }
        
        // Validation
        $price = filter_var($formData['price'], FILTER_VALIDATE_FLOAT);
        $available_quantity = $total_quantity = filter_var($formData['total_quantity'], FILTER_VALIDATE_INT);

        if (empty($formData['event_id'])) { $errors['event_id'] = "Select an event."; }
        if (empty($formData['ticket_type'])) { $errors['ticket_type'] = "Select a type."; }
        if ($price === false || $price <= 0) { $errors['price'] = "Invalid price."; }
        if ($total_quantity === false || $total_quantity <= 0) { $errors['total_quantity'] = "Invalid total quantity."; }
        if ($available_quantity > $total_quantity) { $errors['available_quantity'] = "Available quantity cannot exceed Total quantity."; }
        
        // Calculate timestamps 
        $start_timestamp = strtotime($formData['sale_start']);
        $end_timestamp = strtotime($formData['sale_end']);

    
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
            $event_id = $formData['event_id'];
            $ticket_type = $formData['ticket_type'];
            $sale_start_db = date('Y-m-d H:i:s', $start_timestamp);
            $sale_end_db = date('Y-m-d H:i:s', $end_timestamp);
            
            // ADD/INSERT 
    $stmt = $conn->prepare("INSERT INTO ticket_types (event_id, ticket_type, price, total_quantity, available_quantity, sale_start, sale_end) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdiiss", $event_id, $ticket_type, $price, $total_quantity, $available_quantity, $sale_start_db, $sale_end_db);


            // === MODIFICATION START ===
            // Switched to SESSION messages instead of GET parameters
            if ($stmt->execute()) {
                $_SESSION['message'] = "🎟️ New ticket type added successfully!";
                $_SESSION['message_type'] = "success";
                $stmt->close();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                // === MODIFICATION START ===
                // Validation failure: Pass errors and form data back via SESSION
                $_SESSION['message'] = "❌ Submission failed. Please correct the errors.";
                $_SESSION['message_type'] = "danger";
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $formData;
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            // Validation failure: Pass errors and form data back via URL (Base64 encoded)
            $encoded_errors = urlencode(base64_encode(json_encode($errors)));
            $encoded_form_data = urlencode(base64_encode(json_encode($formData)));
            $redirect_message = urlencode("❌ Submission failed. Please correct the errors.");
            
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=validation_failed&message=$redirect_message&validation_errors=$encoded_errors&form_data=$encoded_form_data");
            exit;
        }
    }

    // --- Data Retrieval for Dropdowns and Table ---
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        die("User not logged in.");
        header("Location: " . BASE_URL . "app/views/auth/login_view.php");
        exit;
    }
    $eventQuery = "SELECT event_id, event_name FROM events WHERE (status = 'approved' OR status = 'pending') AND organizer_id = $user_id ORDER BY event_name ASC";
    $eventResult = $conn->query($eventQuery);
    if ($eventResult === false) {
        die("Event query failed: " . $conn->error);
    }

    $ticketsQuery = "SELECT t.*, e.event_name 
                    FROM ticket_types t 
                    JOIN events e ON t.event_id = e.event_id
                    ORDER BY t.ticket_type_id DESC";
    $ticketsResult = $conn->query($ticketsQuery);
    if ($ticketsResult === false) {
        die("Tickets query failed: " . $conn->error);
    }

?>