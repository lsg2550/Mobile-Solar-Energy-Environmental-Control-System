<?php

//Require
ob_start();
require('session.php');
require('connect.php');

//TODO: Filter User & Pass
$user = $_POST['username'];
$pass = $_POST['password'];

$sqlValidate = "SELECT username, passwd FROM users WHERE username = '" . $user . "' AND passwd = '" . $pass . "';";
$sqlResult = mysqli_query($conn, $sqlValidate);

if(mysqli_num_rows($sqlResult) > 0) {
    header('Location: ../client/client.php');
    $_SESSION['user'] = 1;
    $_SESSION['username'] = $user;
    exit();
} else{
    header('Location: ../index.html');
    $_SESSION['user'] = 0;
    $_SESSION['username'] = '';
}

ob_end_start();
exit();
?>