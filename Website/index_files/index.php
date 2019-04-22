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
    $sqlValidate = "SELECT username, passwd FROM users WHERE username='{$user}' AND passwd=SHA1('{$pass}');"; //Select username and password from the user given username and password
    $resultsValidate = mysqli_query($conn, $sqlValidate); //Execute Query
    if (!$resultsValidate || mysqli_num_rows($resultsValidate) == 0) { 
        // Log login attempt - update db 
        header("Location: /index.html"); 
    }

    //If user is correct, sign them in, otherwise send them back to the log in page
    if(mysqli_num_rows($resultsValidate) == 1) {
        // Log login attempt - update db

        // Check if this user has access elsewhere
        $sqlHaveAccess = "SELECT USR_ORIG FROM access WHERE USR='{$user}';";
        $resultsHaveAccess = mysqli_query($conn, $sqlHaveAccess);

        $_SESSION['username_access'] = mysqli_fetch_assoc($resultsHaveAccess)["USR_ORIG"];
        $_SESSION['user'] = 1;
        $_SESSION['username'] = $user;
        header("Location: /client/client.php");
    }

    //Buffer End & Exit
    ob_end_start();
?>