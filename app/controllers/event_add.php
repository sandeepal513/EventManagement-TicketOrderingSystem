<?php
    include_once __DIR__ . '/../../config/constants.php';
    include_once ROOT . '/config/connection.php';

    $edit_id = null;
    $edit_row = null;
    $errors = [];

    // Check if Edit button clicked
    if (isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $res = $conn->query("SELECT * FROM events WHERE event_id = $edit_id");
        if ($res->num_rows > 0) {
            $edit_row = $res->fetch_assoc();
        }
    }


    // Handle Add Event
    if (isset($_POST['add_event']) && !$edit_id) {
        $category_id = filter_var(trim($_POST['category_id'] ?? ''), FILTER_SANITIZE_NUMBER_INT);
        $organizer_id = filter_var(trim($_SESSION['user_id'] ?? ''), FILTER_SANITIZE_NUMBER_INT); 
        $event_name = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['description'] ?? ''); 
        $venue = trim($_POST['venue'] ?? ''); 
        $event_date = trim($_POST['event_date'] ?? '');
        $event_time = trim($_POST['event_time'] ?? '');
        $map_link = trim($_POST['map_link'] ?? ''); 
        
        $errors = []; // Initialize errors array

    
        if (empty($category_id)) {
            $errors['category_id'] = "Category is required.";
        } elseif (!is_numeric($category_id) || $category_id <= 0) {
            $errors['category_id'] = "Category ID must be a valid positive number.";
        }

        // BUG FIX: Validation for session-based organizer_id
        if (empty($organizer_id)) {
            $errors['organizer_id'] = "You must be logged in to create an event.";
        } elseif (!is_numeric($organizer_id) || $organizer_id <= 0) {
            $errors['organizer_id'] = "Invalid Organizer ID in your session.";
        }

        if (empty($event_name)) {
            $errors['event_name'] = "Event name is required.";
        } elseif (strlen($event_name) < 3 || strlen($event_name) > 50) {
            $errors['event_name'] = "Event name must be between 3 and 50 characters.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $event_name)) {  // Allow letters, numbers, and spaces
            $errors['event_name'] = "Event name can only contain letters, numbers, and spaces.";
        }

        if (empty($venue)) {
            $errors['venue'] = "Venue is required.";
        } elseif (strlen($venue) < 3 || strlen($event_name) > 100) {
            $errors['venue'] = "Event name must be between 3 and 100 characters.";
        }

        if (empty($description)) {
            $errors['description'] = "Description is required.";
        } elseif (strlen($venue) < 3 || strlen($event_name) > 150) {
            $errors['description'] = "Description must be between 3 and 100 characters.";
        }

        if (empty($event_date)) {
            $errors['event_date'] = "Event date is required.";
        } elseif (!DateTime::createFromFormat('Y-m-d', $event_date)) {
            $errors['event_date'] = "Event date must be in YYYY-MM-DD format.";
        } elseif (strtotime($event_date) < strtotime(date('Y-m-d'))) { // Compare to start of today
            $errors['event_date'] = "Event date must be in the future.";
        }

        if (empty($event_time)) {
            $errors['event_time'] = "Event time is required.";
        } elseif (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $event_time)) {
            $errors['event_time'] = "Event time must be in HH:MM format (24-hour).";
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
            // Allow Google Map sharing URLs (like goo.gl or maps.app.goo.gl)
            if (preg_match("/^(https?:\/\/)(maps\.app\.goo\.gl|goo\.gl)/i", $map_link)) {
            
            }
            //check to prevent numeric-only input (e.g., "2222" will fail)
            elseif (is_numeric($map_link) || preg_match('/^\d+$/', $map_link)) {
                $errors['map_link'] = "Google Map Link cannot be just a number (e.g., '2222').";
            }
        }
    }


        if (empty($_FILES['image']['name'])) {
        
            $errors['image'] = "Event image is required for a new event."; 
    }    
        // VALIDATION AND HANDLING FOR EVENT IMAGE 
        $image_url = '';
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $relative_dir = "public/upload/event_imgs/";
            $upload_dir = ROOT . '/' . $relative_dir;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

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
                $upload_path = $upload_dir . $new_filename;

                // Attempt Upload
                if (!move_uploaded_file($file["tmp_name"], $upload_path)) {
                    $errors['image'] = "There was an error uploading your file. Please try again.";
                    $image_url = '';
                }
            }
        }


        // If no errors, insert the event into the database
        if (empty($errors)) {
            
            $sql = "INSERT INTO events (category_id, organizer_id, event_name, description, venue, event_date, event_time, location_map_link, image_url, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
        
            if ($stmt = $conn->prepare($sql)) {
            
                $stmt->bind_param("iisssssss", $category_id, $organizer_id, $event_name, $description, $venue, $event_date, $event_time, $map_link, $new_filename);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Event added successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: " . BASE_URL . "app/views/pages/event_view.php");
                    exit;
                } else {
                    $_SESSION['message'] = "Database Error: " . $stmt->error;
                    $_SESSION['message_type'] = "danger";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "SQL Prepare Error: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
        } else {
            // Store the errors in a session variable to display them back to the user
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST; 
            
        }
    }

    // Include the update logic from event_edit.php
    include ROOT . '/app/controllers/event_edit.php';



    // Initialize search filters
    $search_category = isset($_POST['search_category']) ? $_POST['search_category'] : '';
    $search_date = isset($_POST['search_date']) ? $_POST['search_date'] : '';


    $where_clauses = [];
    if ($search_category) {
        $where_clauses[] = "e.category_id = '$search_category'";
    }
    if ($search_date) {
        $where_clauses[] = "e.event_date = '$search_date'";
    }

    if (isset($_SESSION['user_id'])) {
         $where_clauses[] = "e.organizer_id = '" . intval($_SESSION['user_id']) . "'";
    } else {
        // If no user is logged in, show no events (or handle as needed)
         $where_clauses[] = "e.organizer_id = 0"; 
    }


    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Fetch events based on the selected filters
    $events_res = $conn->query("SELECT e.*, c.category_name, u.full_name AS organizer_name 
                                FROM events e
                                LEFT JOIN categories c ON e.category_id = c.category_id
                                LEFT JOIN users u ON e.organizer_id = u.user_id
                                $where_sql
                                ORDER BY e.event_date ASC");

    // Fetch all categories for the dropdown
    $categories_res = $conn->query("SELECT * FROM categories");

?>


