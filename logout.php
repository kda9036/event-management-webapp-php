<?php
    session_start();
    // unset session variable
    unset($_SESSION['userlogin']); // unset session variable
    session_unset(); // delete all sesssion variables
    session_destroy(); // destroy session
    header("location:login.php");
    exit;
?>