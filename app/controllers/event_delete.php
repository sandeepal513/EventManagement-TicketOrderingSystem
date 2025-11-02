<?php
    session_start(); 
    include_once __DIR__ . '/../../config/constants.php'; 
    include_once ROOT . '/config/connection.php';

    // Check if event ID is provided
    if (!isset($_GET['id'])) {
        $_SESSION['message'] = "Error: Event ID not provided.";
        $_SESSION['message_type'] = "danger";
        header("Location: " . BASE_URL . "app/views/admin/admin_approve_events.php");
        exit;
    }

    $event_id = intval($_GET['id']); 

    // Delete the image file
    $res = $conn->query("SELECT image_url FROM events WHERE event_id = $event_id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        
        if (!empty($row['image_url']) && file_exists($row['image_url'])) {
            
            @unlink($row['image_url']); 
        }
    }

    // Delete related ticket types

    $delete_ticket_sql = "DELETE FROM ticket_types WHERE event_id = ?";
    $stmt_ticket = $conn->prepare($delete_ticket_sql);
    $stmt_ticket->bind_param("i", $event_id);
    $ticket_deleted = $stmt_ticket->execute();
    $stmt_ticket->close();

    // Delete the event
    $sql = "DELETE FROM events WHERE event_id = ?";
    $stmt_event = $conn->prepare($sql);
    $stmt_event->bind_param("i", $event_id);

    if ($stmt_event->execute()) {

        $_SESSION['message'] = "Event and related tickets deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {

        $_SESSION['message'] = "Error deleting event: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }

    $stmt_event->close();
    $conn->close();

    // Final redirection
    header("Location: " . BASE_URL . "app/views/admin/admin_approve_events.php"); 
    exit;
?>