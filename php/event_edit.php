<?php
if (isset($_POST['update_event']) && $edit_id) {
   
    $category_id = filter_var(trim($_POST['category_id'] ?? ''), FILTER_SANITIZE_NUMBER_INT);
    $organizer_id = filter_var(trim($_POST['organizer_id'] ?? ''), FILTER_SANITIZE_NUMBER_INT);
    $event_name = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = preg_replace('/\s+/u', '', trim($_POST['event_time'] ?? ''));
    $map_link = trim($_POST['map_link'] ?? '');
    
    $errors = []; 
    $update_image = false;
    $image_url = '';

    // Basic Presence & Format Checks 
    if (empty($category_id)) {
        $errors['category_id'] = "Category is required.";
    } elseif (!is_numeric($category_id) || $category_id <= 0) {
        $errors['category_id'] = "Category ID must be a valid positive number.";
    }

    if (empty($organizer_id)) {
        $errors['organizer_id'] = "Organizer ID is required.";
    } elseif (!is_numeric($organizer_id) || $organizer_id <= 0) {
        $errors['organizer_id'] = "Organizer ID must be a valid positive number.";
    }

    if (empty($event_name)) {
        $errors['event_name'] = "Event name is required.";
    } elseif (strlen($event_name) < 3 || strlen($event_name) > 50) {
        $errors['event_name'] = "Event name must be between 3 and 100 characters.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $event_name)) {  // Allow letters, numbers, and spaces
        $errors['event_name'] = "Event name can only contain letters, numbers, and spaces.";
    }

    if (empty($venue)) {
        $errors['venue'] = "Venue is required.";
    } elseif (strlen($venue) < 3 || strlen($venue) > 100) {
        $errors['venue'] = "Event name must be between 3 and 100 characters.";
    }

    if (empty($description)) {
        $errors['description'] = "Description is required.";
    } elseif (strlen($description) < 3 || strlen($description) > 150) {
        $errors['description'] = "Description must be between 3 and 150 characters.";
    }

    if (empty($event_date)) {
    $errors['event_date'] = "Event date is required.";
} elseif (!DateTime::createFromFormat('Y-m-d', $event_date)) {
    $errors['event_date'] = "Event date must be in YYYY-MM-DD format.";
} elseif (strtotime($event_date) < time()) {
    $errors['event_date'] = "Event date must be in the future.";
}
   

if (empty($event_time)) {
    $errors['event_time'] = "Event time is required.";
} else {
    // Attempt to create a DateTime object from the input time in HH:MM format
    $dt = DateTime::createFromFormat('H:i', $event_time);
    
    if (!$dt || $dt->format('H:i') !== $event_time) {
        $errors['event_time'] = "Event time must be a valid time in HH:MM format (24-hour).";
    }
}

    // VALIDATION FOR GOOGLE MAP LINK
  if (empty($map_link)) {
    $errors['map_link'] = "Google Map Link is required.";
} else {
    // Remove extra spaces 
    $map_link = trim($map_link);

    $map_link = filter_var($map_link, FILTER_SANITIZE_URL);

    // Automatically add "http://" 
    if (!preg_match("~^(?:f|ht)tps?://~i", $map_link)) {
        $map_link = "http://" . $map_link;
    }
    
   
    if (!filter_var($map_link, FILTER_VALIDATE_URL)) {
        $errors['map_link'] = "Google Map Link is not a valid URL (even after auto-correction).";
    }
    else{
        // Allow Google Map URLs (like goo.gl or maps.app.goo.gl)
        if (preg_match("/^(https?:\/\/)(maps\.app\.goo\.gl|goo\.gl)/i", $map_link)) {
            
        }
        // check to prevent numeric only input 
        elseif (is_numeric($map_link) || preg_match('/^\d+$/', $map_link)) {
            $errors['map_link'] = "Google Map Link cannot be just a number (e.g., '2222').";
        }
    }
    
}



    //  VALIDATION AND HANDLING FOR EVENT IMAGE 
    if (!empty($_FILES["image"]["name"])) {
        $file = $_FILES['image'];
        $target_dir = "uploads/";
        $max_size = 5 * 1024 * 1024; // 5MB
        $allowed_types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        
        $filename = basename($file["name"]);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check File Size
        if ($file["size"] > $max_size) {
            $errors['image'] = "Image file is too large. Max size is 5MB.";
        }
        
        // Check File Type
        if (!in_array($file_ext, array_keys($allowed_types)) || $file['type'] != $allowed_types[$file_ext]) {
            $errors['image'] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
        }

        // Secure Filename and Path
        if (empty($errors)) {
      
            $new_filename = uniqid('event_', true) . '.' . $file_ext;
            $image_url = $target_dir . $new_filename;

            // Attempt Upload
            if (!move_uploaded_file($file["tmp_name"], $image_url)) {
                $errors['image'] = "There was an error uploading your file. Please try again.";
                $update_image = false; 
            } else {
                $update_image = true;
            }
        }
    }

    // If no errors, update the event in the database
    if (empty($errors)) {
       
        $sql = "UPDATE events SET category_id=?, event_name=?, description=?, venue=?, event_date=?, event_time=?, location_map_link=?, status='pending'";
        $params = [$category_id, $event_name, $description, $venue, $event_date, $event_time, $map_link];
        $types = "issssss"; 

        if ($update_image) {
            $sql .= ", image_url=?";
            $params[] = $image_url;
            $types .= "s";
        }

        $sql .= " WHERE event_id=?";
        $params[] = $edit_id;
        $types .= "i";

        // Use prepared statement for security
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ Event updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: event.php");
                exit;
            } else {
                $_SESSION['message'] = "❌ Database Error: " . $stmt->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "❌ SQL Prepare Error: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    } else {
        // Store the errors in a session variable to display them back to the user
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; 
       
    }
}
?>
