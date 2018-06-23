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
    while($rpi = mysqli_fetch_assoc($resultGetRPID)) {
        $RPID[] = $rpi["rpiID"];
    }

    //Generate Threshold XML
    foreach($RPID as $rpi) {
        //Create XML Document
        $thresholdXML = new SimpleXMLElement("<thresholds/>");
        
        //Get Thresholds
        //Variables with Lower and Upper Thresholds
        $BATT = $thresholdXML->addChild("Battery");
        $TEMP = $thresholdXML->addChild("Temperature");
        $CAM = $thresholdXML->addChild("Photo");

        //Get Threshold Values
        $sqlGetRPIThresholds = "SELECT VN, VL, VU FROM vitals WHERE RPID='{$rpi}';";
        $resultGetRPIThresholds = mysqli_query($conn, $sqlGetRPIThresholds);
        while($vital = mysqli_fetch_assoc($resultGetRPIThresholds)) {
            switch($vital["VN"]) {
                case "Battery":
                    $BATT->addChild("voltagelower", $vital["VL"]);
                    $BATT->addChild("voltageupper", $vital["VU"]);
                    break;
                case "Temperature":
                    $TEMP->addChild("temperaturelower", $vital["VL"]);
                    $TEMP->addChild("temperatureupper", $vital["VU"]);
                    break;
                case "Photo":
                    $CAM->addChild("photolower", $vital["VL"]);
                    $CAM->addChild("photoupper", $vital["VU"]);
                    break;
                default:
                    break;
            }
        }

        //Get Vitals 
        //Variables with On/Off
        $SOLARP = $thresholdXML->addChild("Solar Panel");
        $EXHAUST = $thresholdXML->addChild("Exhaust");
        $sqlGetRPIThresholds = "SELECT VN, VV FROM status NATURAL JOIN vitals WHERE status.RPID='{$rpi}';";
        $resultGetRPIThresholds = mysqli_query($conn, $sqlGetRPIThresholds);
        while($vital = mysqli_fetch_assoc($resultGetRPIThresholds)) {
            switch($vital["VN"]) {
                case "Solar Panel":
                    $SOLARP->addChild("toggle", $vital["VV"]);
                    break;
                case "Exhaust":
                    $EXHAUST->addChild("toggle", $vital["VV"]);
                    break;
                default:
                    break;
            }
        }

        $rpiFile = fopen("{$fileDirectory}{$rpi}.xml", "w");
        fwrite($rpiFile, $thresholdXML->asXML());
        fclose($rpiFile);
    }

    echo "OK";
}

generateThresholdAndVitalsFile();
?>