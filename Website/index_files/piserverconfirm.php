<?php
    //TODO: Filter $_GET

    //Require
    require("connect.php");

    //Get Timestamp
    date_default_timezone_set('UTC');
    $currentTimestamp = date("Y-m-d H:i:s", time());

    //Get User
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID={$_GET['rpid']}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    #Update DB Log
    $sqlUpdateDBWithNO = "INSERT INTO log('TYP', 'USR', 'RPID', 'TS') VALUES ('{$_GET['result']}', '{$USR}', '{$_GET['rpid']}', '{$currentTimestamp}');";
    mysqli_query($conn, $sqlUpdateDBWithNO);
?>