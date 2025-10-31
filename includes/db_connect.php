<?php
$servername = "localhost";  // Default XAMPP or WAMP server
$username = "root";         // Default MySQL username
$password = "";             // Leave empty unless you set one
$dbname = "ticketing_system";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Uncomment below line to test connection
// echo "Connected successfully";
?>
