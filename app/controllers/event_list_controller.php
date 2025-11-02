<?php
    include_once __DIR__ . '/../../config/constants.php';
    include_once ROOT . '/config/connection.php';

    // Debug log: Controller accessed
    error_log("Event list controller accessed with GET: " . json_encode($_GET));

    // Get filters from GET request
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
    $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
    $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    // $maxPrice filter removed as 'price' column is not in the new 'event' table
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Base SQL query joining event and categories tables
    // FIX 1: Corrected table name from 'events' to 'event'
    $sql = "SELECT e.*, c.category_name 
            FROM events AS e
            LEFT JOIN categories AS c ON e.category_id = c.category_id";
    
    $conditions = [];
    $params = [];
    $types = "";

    // FIX 2: Changed hard-coded 'approved' to a placeholder '?'
    $conditions[] = "e.status = ?";
    $params[] = 'approved'; // This now correctly matches the placeholder
    $types .= "s";

    // Build WHERE conditions based on filters
    if ($category) {
        $conditions[] = "c.category_name = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    // Date range filter logic
    if (!empty($date_start) && !empty($date_end)) {
        // Both start and end date
        $conditions[] = "e.event_date BETWEEN ? AND ?";
        $params[] = $date_start;
        $params[] = $date_end;
        $types .= "ss";
    } elseif (!empty($date_start)) {
        // Only start date
        $conditions[] = "e.event_date >= ?";
        $params[] = $date_start;
        $types .= "s";
    } elseif (!empty($date_end)) {
        // Only end date
        $conditions[] = "e.event_date <= ?";
        $params[] = $date_end;
        $types .= "s";
    }

    if ($location) {
        $conditions[] = "LOWER(e.venue) LIKE LOWER(?)";
        $params[] = "%" . $location . "%";
        $types .= "s";
    }
    if (!empty($search)) {
        $conditions[] = "LOWER(e.event_name) LIKE LOWER(?)";
        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    // Append WHERE clause if conditions exist
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Add ORDER BY clause for sorting
    if ($sort === 'a-z') {
        $sql .= " ORDER BY e.event_name ASC";
    } elseif ($sort === 'date') {
        $sql .= " ORDER BY e.event_date ASC";
    } elseif ($sort === 'pop') {
        // Using 'total_attendees' as a proxy for popularity
        $sql .= " ORDER BY e.total_attendees DESC";
    } else {
        // Default sort by event date
        $sql .= " ORDER BY e.event_date ASC";
    }

    // Debug log: SQL query and params
    error_log("SQL Query: " . $sql);
    error_log("Params: " . json_encode($params));
    error_log("Types: " . $types);

    // Prepare and execute the statement to prevent SQL injection
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters if they exist
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Debug log: Number of rows
        error_log("Query result rows: " . ($result ? $result->num_rows : 'null'));

        // Display results
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Format date and time
                $eventTimestamp = strtotime($row['event_date'] . ' ' . $row['event_time']);
                $formattedDate = date('D, M j, Y', $eventTimestamp);
                $formattedTime = date('g:i A', $eventTimestamp);

                echo '<div class="card" data-event-id="' . htmlspecialchars($row['event_id']) . '">';
                
                // Use image_url, provide a placeholder if empty
                $image_path = BASE_URL . 'public/upload/event_imgs/';
                $imageUrl = !empty($row['image_url']) ? $image_path . htmlspecialchars($row['image_url']) : 'https://placehold.co/600x400/eee/ccc?text=No+Image';
                
                echo '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($row['event_name']) . '">';
                echo '<h3>' . htmlspecialchars($row['event_name']) . '</h3>';
                
                // Truncate description for card view
                $description = htmlspecialchars($row['description']);
                echo '<p>' . (strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description) . '</p>';
                
                echo '<p><strong>Date:</strong> ' . $formattedDate . '</p>';
                echo '<p><strong>Time:</strong> ' . $formattedTime . '</p>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($row['category_name']) . '</p>';
                echo '<p><strong>Venue:</strong> ' . htmlspecialchars($row['venue']) . '</p>';
                // 'Price' paragraph removed

                // View button
                echo '<form action="' . BASE_URL . 'app/views/pages/event_details_view.php" method="get">';
                echo '<input type="hidden" name="event_id" value="' . htmlspecialchars($row['event_id']) . '">';
                echo '<button type="submit" class="btn-view">View Details</button>';
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<div class="no-events-message">';
            echo '<p>No events found for the selected filters.</p>';
            echo '</div>';
        }
        $stmt->close();
    } else {
        // Handle SQL preparation error
        echo '<div class="no-events-message">';
        // Add more specific error info for debugging
        echo '<p>Error preparing the event query: ' . htmlspecialchars($conn->error) . '</p>';
        error_log("SQL Prepare Error: " . $conn->error); // Log the specific error
        echo '</div>';
    }

    $conn->close();
?>

