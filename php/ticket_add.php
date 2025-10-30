<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 

// connection
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . ($conn->connect_error ?? 'Connection not established'));
}

// Initialization 
$errors = [];
$messages = [];
$edit_mode = false;
$ticket_id = $_GET['id'] ?? null;
$form_title = " Define New Ticket Type";
$submit_button_text = "<i class='fas fa-plus-circle'></i> Add Ticket Type";

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

// Check URL for Messages and Validation Data 
if (isset($_GET['status'])) {
    $message_text = htmlspecialchars($_GET['message'] ?? 'An operation occurred.');
    $message_type = 'info';

    if ($_GET['status'] == 'success') {
        $message_type = "success";
    } elseif ($_GET['status'] == 'updated') {
        $message_type = "info";
    } elseif ($_GET['status'] == 'error' || $_GET['status'] == 'validation_failed') {
        $message_type = "danger";
        
        // Load errors and old form data for sticky form
        if (isset($_GET['form_data'])) {
            $submitted_data = json_decode(base64_decode($_GET['form_data']), true);
            if (!empty($submitted_data)) {
                 $formData = array_merge($formData, $submitted_data);
                 $formData['sale_start'] = format_datetime_for_input($formData['sale_start']);
                 $formData['sale_end'] = format_datetime_for_input($formData['sale_end']);
            }
        }
        if (isset($_GET['validation_errors'])) {
            $errors = json_decode(base64_decode($_GET['validation_errors']), true);
        }
    }
    $messages[] = ["type" => $message_type, "message" => $message_text];
}

// Load Data for Edit
if ($ticket_id && is_numeric($ticket_id) && !isset($_GET['form_data'])) { 
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
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit;
    }
}

// --- INCLUDE UPDATE LOGIC ---
// This file will handle the POST for 'update_ticket'
include 'ticket_update.php';

include 'ticket_delete.php';

// --- Handle ADD (Insert) Logic 
if (isset($_POST['add_ticket'])) { 
    // Capture POST data and populate $formData
    foreach ($formData as $key => $value) {
        $formData[$key] = $_POST[$key] ?? '';
    }
    
    // Validation
    $price = filter_var($formData['price'], FILTER_VALIDATE_FLOAT);
    $total_quantity = filter_var($formData['total_quantity'], FILTER_VALIDATE_INT);
    $available_quantity = filter_var($formData['available_quantity'], FILTER_VALIDATE_INT);

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
        $ticket_status_value = 'Pending'; 
        
        // ADD/INSERT 
$stmt = $conn->prepare("INSERT INTO ticket_types (event_id, ticket_type, price, total_quantity, available_quantity, sale_start, sale_end) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdiiss", $event_id, $ticket_type, $price, $total_quantity, $available_quantity, $sale_start_db, $sale_end_db);


        if ($stmt->execute()) {
            $redirect_message = urlencode("🎟️ New ticket type added successfully!");
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=$redirect_message");
            exit;
        } else {
            $redirect_message = urlencode("❌ Database Error (Insert): " . $stmt->error);
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=$redirect_message");
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
$eventQuery = "SELECT event_id, event_name FROM events WHERE status = 'approved' ORDER BY event_name ASC";
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