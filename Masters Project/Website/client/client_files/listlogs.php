<?php

function getData($stringToReplace) {
    //Remove extra characters and place data into array
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);

    //Get Data and Return HTML Table
    $stringHTML = '<tr>';
    for ($i = 0; $i < count($stringSplit) - 1; $i++) {
        $stringHTML .= '<th>' . $stringSplit [$i] . '</th>';
    }
    $stringHTML .= '</tr>';
    return $stringHTML;
}

function getTimeStamp($stringToReplace) {
    //Remove extra characters and place data into array
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);
    return end($stringSplit);
}

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
$ipaddress = '127.0.0.1';
$port = 8080;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

/* Try to connect to server */
$result = socket_connect($socket, $ipaddress, $port);

/* Get Data */
$request = 'request' . chr(10); //chr(10) == '\n'
socket_write($socket, $request, strlen($request));

//Buffers
$bufferCurrent = '';
$bufferStatus = $bufferLog = array();
$bufferStatusBool = $bufferLogBool = false;
while (($bufferCurrent = socket_read($socket, 1024)) !== false) {

    //echo 'Checking if Current or Log: ' . $bufferCurrent . chr(10);
    if (strcmp($bufferCurrent, "CURRENT") === 0) { //Server is sending CurrentStatus data
        $bufferCurrent = socket_read($socket, 1024);

        while (strcmp($bufferCurrent, "CURRENTEND") !== 0) {
            $bufferStatus[] = $bufferCurrent;
            $bufferCurrent = socket_read($socket, 1024);
        }

        $bufferStatusBool = true;
    } elseif (strcmp($bufferCurrent, "LOG") === 0) {
        $bufferCurrent = socket_read($socket, 1024);

        while (strcmp($bufferCurrent, "LOGEND") !== 0) {
            $bufferLog[] = $bufferCurrent;
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
    <fieldset><legend>Current Status - <?php echo getTimeStamp($bufferStatus[0]); ?></legend>
        <?php
        echo '<table>';

        foreach ($bufferStatus as $bS) {
            echo getData($bS);
        }

        echo '</table>';
        ?>
    </fieldset>
    <fieldset><legend>Log</legend>
        <?php
        echo '<table>';

        foreach ($bufferLog as $bL) {
            echo getData($bL);
        }

        echo '</table>';
        ?>
    </fieldset>
</html>