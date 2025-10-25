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


// SQL query
//if ($category) {
   // $sql = "SELECT * FROM events WHERE category = '" . $conn->real_escape_string($category) . "'";
//} else {
   // $sql = "SELECT * FROM events";
//}

$sql = "SELECT * FROM events";
$conditions = [];

if ($category) {
    $conditions[] = "category = '" . $conn->real_escape_string($category) . "'";
}
if ($date) {
    $conditions[] = "date = '" . $conn->real_escape_string($date) . "'";
}
if ($location) {
    $conditions[] = "location LIKE '%" . $conn->real_escape_string($location) . "%'";
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
        echo '<div class="card">';
        echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        echo '<p><strong>Date:</strong> ' . htmlspecialchars($row['date']) . '</p>';
        echo '<p><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</p>';
        echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
        echo '<p><strong>Price:</strong> ' . htmlspecialchars($row['price']) . '</p>';
        echo '</div>';
    }
} else {
    echo "<p>No events found for the selected category.</p>";
}

$conn->close();
?>
