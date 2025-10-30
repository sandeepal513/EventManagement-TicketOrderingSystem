<?php

    function getUserById($conn, $user_id) {
        // Check if connection is valid
        if (!$conn || $conn->connect_error) {
            error_log("Database connection error in getUserById");
            return null;
        }
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        
        if (!$stmt) {
            error_log("Prepare failed in getUserById: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getUserById: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $stmt->close();
        
        return $user;
    }

?>