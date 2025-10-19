<?php
    $db_server = "localhost";
    $db_name = "ticketing_system";
    $db_username = "root";
    $db_password = "1234";
    $conn = "";

    $conn = new mysqli($db_server, $db_username, $db_password, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
?>