<?php
// --- Handle Delete Action ---
if (isset($_POST['delete_ticket']) && isset($_POST['ticket_type_id'])) {
    
    $ticket_id = intval($_POST['ticket_type_id']);
    
    //  Validation 
    if ($ticket_id <= 0) {
        // Invalid ticket ID
        $messages[] = ["type" => "danger", "message" => "❌ Invalid ticket ID for deletion."];
    } else {
        // DELETE Query to remove ticket
        $stmt = $conn->prepare("DELETE FROM ticket_types WHERE ticket_type_id = ?");
        $stmt->bind_param("i", $ticket_id);

        
        if ($stmt->execute()) {
            // Check if any row was deleted
            if ($stmt->affected_rows > 0) {
                $messages[] = ["type" => "success", "message" => "🗑️ Ticket ID $ticket_id deleted successfully!"];
            } else {
               
                $messages[] = ["type" => "danger", "message" => "❌ Ticket ID $ticket_id not found or already deleted."];
            }
        } else {
            // If deletion fails, show the error
            $messages[] = ["type" => "danger", "message" => "❌ Database Error (Delete): " . htmlspecialchars($stmt->error)];
        }
        
     
        $stmt->close();
    }
}
?>
 