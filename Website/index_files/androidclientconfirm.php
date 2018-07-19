<?php
    function generateAndroidJSONS() {
        //Require
        require("connect.php");

        //Android JSON Location
        $androidXMLLocation = "../../androidxmls/";

        //Database Queries
        $currentUser = $_POST["username"]; //Current User
        $sqlCurrentStatus = "SELECT VN, VV, TS, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select all current status related to the current user
        $sqlLog = "SELECT VID, TYP, RPID, V1, V2, TS FROM log WHERE USR='{$currentUser}' ORDER BY RPID, TS;"; //Select all logs related to the current user

        //Execute Queries
        $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
        $resultLog = mysqli_query($conn, $sqlLog);

        //Store CurrentStatus Query Results into $jsonCurrentStatus
        $jsonCurrentStatus = array();
        if(mysqli_num_rows($resultCurrentStatus) > 0) {
            while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
                $tempRow = array( 'VN' => $row['VN'], 'VV' => $row['VV'], 'RPID' => $row['RPID'], 'TS' => $row['TS'] );
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