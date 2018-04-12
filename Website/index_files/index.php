<?php

//Require
require('sessionstart.php');
require('connect.php');
ob_start();

//TODO: Filter User & Pass
//Take User and Pass from POST
$user = $_POST['username'];
$pass = $_POST['password'];

/* Database Queries */
$sqlValidate = "SELECT username, passwd FROM users WHERE username = '" . $user . "' AND passwd = '" . $pass . "';"; //Select username and password from the user given username and password
$sqlResult = mysqli_query($conn, $sqlValidate); //Execute Query

//If user is correct, sign them in, otherwise send them back to the log in page
if(mysqli_num_rows($sqlResult) > 0) {
    header('Location: ../client/client.php');
    $_SESSION['user'] = 1;
    $_SESSION['username'] = $user;
    ob_end_start();
    exit();
} else{
    header('Location: ../index.html');
    $_SESSION['user'] = 0;
    $_SESSION['username'] = '';
    ob_end_start();
    exit();
}

?>