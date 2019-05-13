<?php
    /**
     * androidloginconfirm.php
     * Desc: This php script is for the android app to validate & confirm login credentials.
     */

    //Require
    require("connect.php");

    //TODO: Filter User & Pass; Take User and Pass from POST
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //Database Queries
    $sqlValidate = "SELECT username, passwd FROM users WHERE username='{$user}' AND passwd='{$pass}';"; //passwd is already sha1 hashed on the app, so we don't need to do it here; only add passwd=sha1('$pass') if you are sending a hash here in plaintext
    $resultsValidate = mysqli_query($conn, $sqlValidate); //Execute Query

    if (!$resultsValidate || mysqli_num_rows($resultsValidate) == 0) { 
        echo "NO";
    } elseif (mysqli_num_rows($resultsValidate) == 1) {
        echo "OK";
    }
?>