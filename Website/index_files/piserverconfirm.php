<?php
    /**
     * Name: piserverconfirm.php
     * Description: This script is called by the Raspberry Pi to tell the CMS that it did or did not retrieve 
     * a requested file. The raspberry pi will send its ID and a 'result' of 'NO' or 'OK'. OK is more expected
     * as failure to retrieve a file may be due to no internet connection in which would trigger an exception
     * on the RPi side. A NO would indicate an issue on the CMS end or something happened on the way to the RPi.
     */

    // Require
    require("connect.php");

    // Initialize variables
    date_default_timezone_set('America/Chicago');
    $CURRENT_TIMESTAMP = date("Y-m-d H:i:s", time());
    $RASPBERRY_PI_ID = $_GET['rpid']; // TODO: Filter rpid
    $RASPBERRY_PI_RESULT = $_GET['result']; // TODO: Filter result

    // Get owner of the RPi, aka the user
    $sqlGetUser = "SELECT `uid-owner` FROM rpi WHERE rpid={$RASPBERRY_PI_ID}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $UID = mysqli_fetch_assoc($resultsGetUser)['uid-owner'];

    // Update database with a NO or OK if the Raspberry Pi depending on the RPi's result
    $sqlUpdateDatabaseWithResult = "INSERT INTO logs(vid, typ, uid, rpid, v1, v2, ts) VALUES ('', '{$RASPBERRY_PI_RESULT}', '{$UID}', '{$RASPBERRY_PI_ID}', '', '', '{$CURRENT_TIMESTAMP}');";
    mysqli_query($conn, $sqlUpdateDatabaseWithResult);
?>