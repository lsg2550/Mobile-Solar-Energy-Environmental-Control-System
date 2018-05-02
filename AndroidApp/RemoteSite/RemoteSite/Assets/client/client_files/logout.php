<?php
if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}
$_SESSION['user'] = 0;
header('Location: ../../index.html');
exit();
?>