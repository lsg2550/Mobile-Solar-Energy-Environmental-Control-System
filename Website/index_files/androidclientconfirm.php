<?php
    /**
     * Description: The android app will call this script via http request to request the CMS to create the JSON files needed for the
     * android app to process. This script will return a 'OK' or 'NO'. In which the android app will act accordingly to the status code.
     */

    function generateAndroidJSONS() {
        //Require
        require("connect.php");

        //Android JSON Location
        $androidXMLLocation = "../../androidxmls/";

        //Database Queries
        $currentUser = $_POST["username"]; //Current User
        $currentPass = $_POST["password"]; // Current Password

        $sqlCurrentUID = "SELECT uid FROM users WHERE username='{$currentUser}' AND passwd='{$currentPass}';";
        $resultCurrentUID = mysqli_query($conn, $sqlCurrentUID);
        $currentUID = mysqli_fetch_assoc($resultCurrentUID)['uid'];
        
        $sqlCurrentStatus = "SELECT VN, V1, V2, TS, RPID FROM (status NATURAL JOIN vitals) NATURAL JOIN rpi WHERE `uid-owner`='{$currentUID}';"; //Select all current status related to the current user
        $sqlLog = "SELECT VID, TYP, RPID, V1, V2, TS FROM logs WHERE uid='{$currentUID}' ORDER BY RPID, TS;"; //Select all logs related to the current user

        //Execute Queries
        $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
        $resultLog = mysqli_query($conn, $sqlLog);

        //Store CurrentStatus Query Results into $jsonCurrentStatus
        $jsonCurrentStatus = array();
        if(mysqli_num_rows($resultCurrentStatus) > 0) {
            while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
                $tempRow = array( 'VN' => $row['VN'], 'V1' => $row['V1'], 'V2' => $row['V2'], 'RPID' => $row['RPID'], 'TS' => $row['TS'] );
                $jsonCurrentStatus[] = $tempRow;
            }
        }

        //Store Log Query Results into $jsonLog
        $jsonLog = array();
        if(mysqli_num_rows($resultLog) > 0) { 
            while($row = mysqli_fetch_assoc($resultLog)) {
                if($row['V2'] == NULL || $row['V2'] == "") { $row['V2'] = "N/A"; } //If V2 is blank, replace it with 'N/A'
                $tempRow = array( 'VID' => $row['VID'], 'TYP' => $row['TYP'], 'RPID' => $row['RPID'], 'V1' => $row['V1'], 'V2' => $row['V2'], 'TS' => $row['TS']);
                $jsonLog[] = $tempRow;
            }
        }

        //Populate and Write JSONs
        //var_dump($jsonCurrentStatus);
        $jsonCurrentStatusFile = fopen("{$androidXMLLocation}{$currentUser}cs.json", "w"); //e.g luis_cs.json
        fwrite($jsonCurrentStatusFile, json_encode($jsonCurrentStatus));
        fclose($jsonCurrentStatusFile);

        //var_dump($jsonLog);
        $jsonLogFile = fopen("{$androidXMLLocation}{$currentUser}l.json", "w"); //e.g luis_l.json
        fwrite($jsonLogFile, json_encode($jsonLog));
        fclose($jsonLogFile);
    }
    
    try {
        generateAndroidJSONS();
        echo "OK";
    } catch (Exception $e) {
        echo "NO";
    }
?>