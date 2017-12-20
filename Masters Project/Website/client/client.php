<?php
if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    exit();
}

/* Init Misc */
error_reporting(E_ALL);
set_time_limit(0); //Allow the script to hang
ob_implicit_flush(); //See what we're getting as it comes in

/* Init Connections */
$ipaddress = "127.0.0.1";
$port = 8080;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

/* Check if socket was created */
if ($socket === false) {
    echo "socket_create() failed: reason " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "Socket Created!\n";
}

/* Try to connect to server */
echo "Attempting to connect to '$ipaddress' on port '$pOpen/ort'";
$result = socket_connect($socket, $ipaddress, $port);
if ($result === false) {
    echo "Socket failed to connect: " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "Socket successfully connected.";
}

/* End Connection */
echo "Closing socket...";
socket_write($socket, "quit");
socket_close($socket);
echo "OK.\n\n";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Remote Site - Client Page</title>
    </head>
    <body>
        <div>
            <h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1>
        </div>
        <div>
            <fieldset><legend>Car Status - {If possible, insert Log (Time,Date) from Code? Otherwise add it as a row}</legend>
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
            <fieldset><legend>Car Status - 05:00PM - July 24, 2017</legend>
                <table>
                    <tr>
                        <th>Temperature</th>
                        <th>20C/68F</th>
                    </tr>
                    <tr>
                        <th>Battery Voltage</th>
                        <th>12.6V</th>
                    </tr>
                    <tr>
                        <th>Exhaust</th>
                        <th>ON</th>
                    </tr>
                    <tr>
                        <th>Solar Panel</th>
                        <th>Not Charging</th>
                    </tr>
                </table>
            </fieldset>
        </div>
        <div>
            <form action="client_files/logout.php" method="post">
                <input type="submit" value="Log Out">
            </form>
        </div>
    </body>
</html>