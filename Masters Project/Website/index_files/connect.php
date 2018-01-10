<?php

/* Init Misc */
error_reporting(E_ALL);
set_time_limit(0); //Allow the script to hang
ob_implicit_flush(); //See what we're getting as it comes in

/* Init Connections */
$ipaddress = '127.0.0.1';
$port = 8080;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

/* Try to connect to server */
$result = socket_connect($socket, $ipaddress, $port);
?>

