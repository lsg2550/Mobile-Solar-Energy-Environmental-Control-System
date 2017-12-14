<?php
	error_reporting(E_ALL);
	
	echo "Connect to Java Socket Server";
	
	//Get Server Port & IP
	$server_port = "5550";
	$server_address = "localhost";
	
	//Create Socket
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if($socket === false) {
		echo "Socket failed to create: " . socket_strerror(socket_last_error()) . "<br>";
	} else {
		echo "Socket successfully created.";
	}
	
	echo "Attempting to connect to '$server_address' on port '$server_port'";
	$result = socket_connect($socket, $server_address, $service_port);
	if($result === false) {
		echo "Socket failed to connect: " . socket_strerror(socket_last_error($socket)) . "<br>";
	} else {
		echo "Socket successfully connected.";
	}
	
	echo "Closing socket...";
	socket_close($socket);
	echo "OK.<br><br>";
?>