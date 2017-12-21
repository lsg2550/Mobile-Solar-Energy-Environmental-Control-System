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

/* Try to connect to server */
$result = socket_connect($socket, $ipaddress, $port);

/* Get Data */
$request = 'request' . chr(10); //chr(10) == '\n'
socket_write($socket, $request, strlen($request));

//Buffers
$bufferCurrent = $bufferStatus = $bufferLog = '';
$bufferStatusBool = $bufferLogBool = false;
while (($bufferCurrent = socket_read($socket, 1024)) !== false) {

    echo 'Checking if Current or Log: ' . $bufferCurrent . chr(10);
    if (strcmp($bufferCurrent, "CURRENT") === 0) { //Server is sending CurrentStatus data
        $bufferCurrent = socket_read($socket, 1024);

        while (strcmp($bufferCurrent, "CURRENTEND") !== 0) {
            $bufferStatus .= $bufferCurrent;
            $bufferCurrent = socket_read($socket, 1024);
        }

        $bufferStatusBool = true;
    } elseif (strcmp($bufferCurrent, "LOG") === 0) {
        $bufferCurrent = socket_read($socket, 1024);

        while (strcmp($bufferCurrent, "LOGEND") !== 0) {
            $bufferLog .= $bufferCurrent;
            $bufferCurrent = socket_read($socket, 1024);
        }

        $bufferLogBool = true;
    }

    if ($bufferStatusBool === true && $bufferLogBool === true) {
        break;
    }

    sleep(5);
}

/* End Connection */
socket_write($socket, 'QUIT');
socket_close($socket);
?>

<!DOCTYPE html>
<html>
    <fieldset><legend>Current Status</legend>
        <table>
            <tr>
                <th>Temperature</th>
                <th><?php echo $bufferStatus; ?></th>
            </tr>
            <tr>
                <th>Battery Voltage</th>
                <th><?php echo $bufferLog; ?></th>
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