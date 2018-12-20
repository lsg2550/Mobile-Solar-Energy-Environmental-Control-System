<?php
    function generateThresholdAndVitalsFile() {
        //Include
        include("connect.php");

        //Init
        $fileDirectory = "../../rpixmlsthresholds/";
        
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
            $thresholdXML = new stdClass();

            //SQL Queries
            $sqlGetRPIThresholdsThresh = "SELECT VN, VL, VU FROM vitals WHERE RPID='{$rpi}';";
            $sqlGetRPIThresholdsToggle = "SELECT VN, VV FROM status NATURAL JOIN vitals WHERE status.RPID='{$rpi}';";
            $resultGetRPIThresholdsThresh = mysqli_query($conn, $sqlGetRPIThresholdsThresh);
            $resultGetRPIThresholdsToggle = mysqli_query($conn, $sqlGetRPIThresholdsToggle);

            //Get Vitals - Variables with thresholds
            while($vital = mysqli_fetch_assoc($resultGetRPIThresholdsThresh)) {
                switch($vital["VN"]) {
                    case "BatteryVoltage":
                        $thresholdXML->voltagelower = $vital["VL"];
                        $thresholdXML->voltageupper = $vital["VU"];
                        break;
                    case "BatteryCurrent":
                        $thresholdXML->currentlower = $vital["VL"];
                        $thresholdXML->currentupper = $vital["VU"];
                        break;
                    case "SolarPanelVoltage":
                        $thresholdXML->spvoltagelower = $vital["VL"];
                        $thresholdXML->spvoltageupper = $vital["VU"];
                        break;
                    case "SolarPanelCurrent":
                        $thresholdXML->spcurrentlower = $vital["VL"];
                        $thresholdXML->spcurrentupper = $vital["VU"];
                        break;
                    case "ChargeControllerVoltage":
                        $thresholdXML->ccvoltagelower = $vital["VL"];
                        $thresholdXML->ccvoltageupper = $vital["VU"];
                        break;
                    case "ChargeControllerCurrent":
                        $thresholdXML->cccurrentlower = $vital["VL"];
                        $thresholdXML->cccurrentupper = $vital["VU"];
                        break;
                    case "TemperatureInner":
                        $thresholdXML->temperatureinnerlower = $vital["VL"];
                        $thresholdXML->temperatureinnerupper = $vital["VU"];
                        break;
                    case "TemperatureOuter":
                        $thresholdXML->temperatureouterlower = $vital["VL"];
                        $thresholdXML->temperatureouterupper = $vital["VU"];
                        break;
                    case "Photo":
                        $thresholdXML->photofps = $vital["VL"];
                        break;
                    default:
                        break;
                }
            }

            $rpiFile = fopen("{$fileDirectory}{$rpi}.json", "w");
            fwrite($rpiFile, json_encode($thresholdXML));
            fclose($rpiFile);
        }
    }

    try { 
        generateThresholdAndVitalsFile();
        echo "OK";
    } catch (Exception $e) {
        echo $e;
        echo "NO";
    }
?>