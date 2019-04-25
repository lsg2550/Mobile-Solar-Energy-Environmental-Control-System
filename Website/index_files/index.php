<?php
    //Buffer Start
    ob_start();

    //Require
    require("sessionstart.php");
    require("connect.php");

    //TODO: Filter User & Pass; Take User and Pass from POST
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //Database Queries
    $sqlValidate = "SELECT uid FROM users WHERE username='{$user}' AND passwd=SHA1('{$pass}');"; //Select username and password from the user given username and password
    $resultsValidate = mysqli_query($conn, $sqlValidate); //Execute Query
    if (!$resultsValidate || mysqli_num_rows($resultsValidate) == 0) { 
        // Log login attempt - update db 
        header("Location: /index.html"); 
    }

    //If user is correct, sign them in, otherwise send them back to the log in page
    if(mysqli_num_rows($resultsValidate) == 1) {
        // Log login attempt - update db

        // Check if this user has access elsewhere
        $sqlHaveAccess = "SELECT `uid-owner` FROM access WHERE `uid-accessor`='{$uid}';";
        $resultsHaveAccess = mysqli_query($conn, $sqlHaveAccess);

        $_SESSION['username'] = $uid;
        $_SESSION['username_access'] = mysqli_fetch_assoc($resultsHaveAccess)["uid-owner"]; // Change this, don't hardcode, there may be a chance that a specific user will have access to multiple RPi!
        $_SESSION['user'] = 1;
        header("Location: /client/client.php");
    }

    //Buffer End & Exit
    ob_end_start();
?>