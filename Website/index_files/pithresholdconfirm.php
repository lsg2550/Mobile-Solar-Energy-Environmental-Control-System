<?php
function generateThresholdAndVitalsFile() {
    //Include
    include("connect.php");

    //Init
    $fileDirectory = "../../rpis/";
    
    //Init - Get User
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID={$_GET["rpid"]}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    //Get RPIDs belonging to $currentUser
    $RPID = [];
    $sqlGetRPID = "SELECT rpiID FROM rpi WHERE owner='{$USR}';";
    $resultGetRPID = mysqli_query($conn, $sqlGetRPID);
    while($rpi = mysqli_fetch_assoc($resultGetRPID)) { $RPID[] = $rpi["rpiID"]; } //Gets raspberry pi IDs and stores them into an array

    //Generate Threshold JSON
    foreach($RPID as $rpi) {      
        //Start JSON Build  
        $thresholdXML = new stdClass();

        //SQL Queries
        $sqlGetRPIThresholdsThresh = "SELECT VN, VL, VU FROM vitals WHERE RPID='{$rpi}';";
        $sqlGetRPIThresholdsToggle = "SELECT VN, VV FROM status NATURAL JOIN vitals WHERE status.RPID='{$rpi}';";
        $resultGetRPIThresholdsThresh = mysqli_query($conn, $sqlGetRPIThresholdsThresh);
        $resultGetRPIThresholdsToggle = mysqli_query($conn, $sqlGetRPIThresholdsToggle);

        //Get Vitals - Variables with thresholds
        while($vital = mysqli_fetch_assoc($resultGetRPIThresholdsThresh)) {
            switch($vital["VN"]) {
                case "Battery":
                    $thresholdXML->voltagelower = $vital["VL"];
                    $thresholdXML->voltageupper = $vital["VU"];
                    break;
                case "Temperature":
                    $thresholdXML->temperaturelower = $vital["VL"];
                    $thresholdXML->temperatureupper = $vital["VU"];
                    break;
                case "Photo":
                    $thresholdXML->photolower = $vital["VL"];
                    $thresholdXML->photoupper = $vital["VU"];
                    break;
                default:
                    break;
            }
        }

        //Get Vitals - Variables with On/Off
        while($vital = mysqli_fetch_assoc($resultGetRPIThresholdsToggle)) {
            switch($vital["VN"]) {
                case "Solar Panel":
                    $thresholdXML->solartoggle = $vital["VV"];
                    break;
                case "Exhaust":
                    $thresholdXML->exhausttoggle = $vital["VV"];
                    break;
                default:
                    break;
            }
        }

        $rpiFile = fopen("{$fileDirectory}{$rpi}.json", "w");
        fwrite($rpiFile, json_encode($thresholdXML));
        fclose($rpiFile);
        //End JSON Build
    }

    echo "OK";
}

try { generateThresholdAndVitalsFile(); }
catch (Exception $e) {
    echo "NO";

    //Update database log of the error
    //Include
    //include("connect.php");

    //Get Timestamp
    //date_default_timezone_set('UTC');
    //$currentTimestamp = date("Y-m-d H:i:s", time());

    //Get User
    //$sqlGetUser = "SELECT owner FROM rpi WHERE rpiID={$_GET['rpid']}";
    //$resultsGetUser = mysqli_query($conn, $sqlGetUser);
    //$USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    #Update DB Log - That there was an issue with the RPi
    //$sqlUpdateDBWithNO = "INSERT INTO log('TYP', 'USR', 'RPID', 'TS') VALUES ('NO', '{$USR}', '{$_GET['rpid']}', '{$currentTimestamp}');";
    //mysqli_query($conn, $sqlUpdateDBWithNO);
}
?>