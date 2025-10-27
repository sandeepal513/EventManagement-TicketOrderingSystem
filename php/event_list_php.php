<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "event"; // Change if your DB name is different

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$maxPrice = isset($_GET['maxPrice']) ? $_GET['maxPrice'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM event_list";
$conditions = [];

if ($category) {
    $conditions[] = "category = '" . $conn->real_escape_string($category) . "'";
}
if ($date) {
    $conditions[] = "DATE(date) = '" . $conn->real_escape_string($date) . "'";
}

if ($location) {
    $conditions[] = "LOWER(location) LIKE LOWER('%" . $conn->real_escape_string($location) . "%')";
}

// Price filter (fixed min 500)
if ($maxPrice) {
    $conditions[] = "price BETWEEN 500 AND '" . $conn->real_escape_string($maxPrice) . "'";
}

// Search by name
if (!empty($search)) {
    $conditions[] = "LOWER(name) LIKE '%" . strtolower($conn->real_escape_string($search)) . "%'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

if ($sort === 'a-z') {
    $sql .= " ORDER BY name ASC";
} elseif ($sort === 'date') {
    $sql .= " ORDER BY date ASC";
} elseif ($sort === 'pop') {
    $sql .= " ORDER BY popularity DESC"; // Make sure you have a 'popularity' column
}

$result = $conn->query($sql);

// Display results
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="card" data-event-id="' . htmlspecialchars($row['event_id']) . '">';
        echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        echo '<p><strong>Date:</strong> ' . htmlspecialchars($row['date']) . '</p>';
        echo '<p><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</p>';
        echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
        echo '<p><strong>Price:</strong> ' . htmlspecialchars($row['price']) . '</p>';

        //view button
         echo '<form action="event_details.php" method="get">';
         echo '<input type="hidden" name="event_id" value="' . htmlspecialchars($row['event_id']) . '">';
         echo '<button type="submit" class="btn-view">View</button>';
         echo '</form>';
        echo '</div>';
    }
} else {
    echo "<p>No events found for the selected filters.</p>";
}


$conn->close();
?>
