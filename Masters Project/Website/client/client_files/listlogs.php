<?php
//if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
//    session_start();
//}
//
//if ($_SESSION['user'] !== 1) {
//    header('Location: ../index.html');
//    exit();
//}

/* Init Misc */
error_reporting(E_ALL);
set_time_limit(0); //Allow the script to hang
ob_implicit_flush(); //See what we're getting as it comes in

/* Init Connections */
$ipaddress = '127.0.0.1';
$port = 8080;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);


/* Check if socket was created */
if ($socket === false) {
    echo "socket_create() failed: reason " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "Socket Created!\n";
}

/* Try to connect to server */
$result = socket_connect($socket, $ipaddress, $port);
if ($result === false) {
    echo "Socket failed to connect: " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "Socket successfully connected.\n";
}

/* Get Data */
echo "Writing to server...\n";
socket_write($socket, 'request');
$buf = '';
echo "Reading from server...\n";
socket_recv($socket, $buf, 1024, MSG_WAITALL);

echo $buf . "\n";

/* End Connection */
echo "Closing socket...";
socket_write($socket, 'quit');
socket_close($socket);
?>

<!DOCTYPE html>
<html>
    <fieldset><legend>Current Status</legend>
        <table>
            <tr>
                <th>Temperature</th>
                <th>{celsius}/{fahrenheit}</th>
            </tr>
            <tr>
                <th>Battery Voltage</th>
                <th>{Voltage}</th>
            </tr>
            <tr>
                <th>Exhaust</th>
                <th>{ON/OFF}</th>
            </tr>
            <tr>
                <th>Solar Panel</th>
                <th>{Charging/NotCharging}</th>
            </tr>
        </table>
    </fieldset>
    <fieldset><legend>Log</legend>
        <!-- TODO: Loop Through and Display All Logs-->
    </fieldset>
</html>