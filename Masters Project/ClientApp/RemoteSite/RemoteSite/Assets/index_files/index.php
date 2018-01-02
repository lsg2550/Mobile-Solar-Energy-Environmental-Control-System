<?php
// TODO: Check Username & Password
if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}

$user = $_POST['username'];
$pass = $_POST['password'];

$_SESSION['user'] = 1;
header('Location: ../client/client.php');
exit();
?>