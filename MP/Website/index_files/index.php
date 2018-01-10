<?php

//Require
require('session.php');
require('connect.php');

//TODO: Filter User & Pass
$user = $_POST['username'];
$pass = $_POST['password'];

/* Send SignIn Verification Request */
$buffer = 'signin' . chr(10); //chr(10) == '\n'
socket_write($socket, $buffer, strlen($buffer));
$buffer = $user . chr(10); //chr(10) == '\n'
socket_write($socket, $buffer, strlen($buffer));
$buffer = $pass . chr(10); //chr(10) == '\n'
socket_write($socket, $buffer, strlen($buffer));

echo 'Data Sent...' . chr(10);
/* Receive Data */
$bufferCurrent = socket_read($socket, 1024);
echo 'Data Received: ' . $bufferCurrent . chr(10);

if (strcmp($bufferCurrent, "ACCEPT") === 0) { //Credentials Match
    $_SESSION['user'] = 1;
    header('Location: ../client/client.php');
} else {
    $_SESSION['user'] = 0;
    header('Location: ../index.html');
}

exit();
?>