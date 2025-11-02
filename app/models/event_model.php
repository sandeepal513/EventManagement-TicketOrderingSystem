<?php

    function getEventList($conn) {
        
        if (!$conn || $conn->connect_error) {
            error_log("Database connection error in getEventList");
            return null;
        }

        $stmt = $conn->prepare("SELECT * FROM event");

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed in getEventList: " . $conn->error);
            return null;
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getEventList: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $eventlist = $result->fetch_assoc();
        
        $stmt->close();

        return $eventlist;


    }

    function getEventDetails($conn, $event_id) {
        
        if (!$conn || $conn->connect_error) {
            error_log("Database connection error in getEventDetails");
            return null;
        }

        $stmt = $conn->prepare("SELECT * FROM event WHERE event_id = ?");
        
        if (!$stmt) {
            error_log("Prepare failed in getEventDetails: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $event_id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getEventDetails: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $eventdetails = $result->fetch_assoc();
        
        $stmt->close();

        return $eventdetails;

    }

    function getCategoryDetails($conn, $category_id) {
        
        if (!$conn || $conn->connect_error) {
            error_log("Database connection error in getCategory");
            return null;
        }

        $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
        
        if (!$stmt) {
            error_log("Prepare failed in getCategory: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $category_id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getCategory: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        $stmt->close();

        return $category;
    }


?>