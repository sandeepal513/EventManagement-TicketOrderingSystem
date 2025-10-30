<?php

        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'ticketing_system';

        if (!$conn = mysqli_connect($host, $user, $password, $database)) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
?>