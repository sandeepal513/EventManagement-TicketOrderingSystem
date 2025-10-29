<?php

    session_start();


    $_SESSION = array();


    session_destroy();


    header("Location: ./../php/login.php?status=logged_out");
    exit();
?>
