<?php
// --- Handle Delete Action ---
if (isset($_POST['delete_ticket']) && isset($_POST['ticket_type_id'])) {
    
    $ticket_id = intval($_POST['ticket_type_id']);
    
    //  Validation 
    if ($ticket_id <= 0) {
        // === MODIFICATION: Use SESSION ===
        $_SESSION['message'] = "❌ Invalid ticket ID for deletion.";
        $_SESSION['message_type'] = "danger";

    } else {
        // DELETE Query to remove ticket
        $stmt = $conn->prepare("DELETE FROM ticket_types WHERE ticket_type_id = ?");
        $stmt->bind_param("i", $ticket_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // === MODIFICATION: Use SESSION ===
                $_SESSION['message'] = "🗑️ Ticket ID $ticket_id deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                // === MODIFICATION: Use SESSION ===
                $_SESSION['message'] = "❌ Ticket ID $ticket_id not found or already deleted.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            // === MODIFICATION: Use SESSION ===
            $_SESSION['message'] = "❌ Database Error (Delete): " . htmlspecialchars($stmt->error);
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    
    // === MODIFICATION: Redirect after action ===
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
