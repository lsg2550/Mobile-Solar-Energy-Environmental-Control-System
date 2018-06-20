<?php
function generateThresholdFile() {
    //Include
    include("connect.php");

    //Init
    $thresholddir = "../../rpis/";
    
    //Init - Get User
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
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
        $thresholdXML = new SimpleXMLElement("<thresholds/>");
        $BATT = $thresholdXML->addChild("Battery");
        $TEMP = $thresholdXML->addChild("Temperature");
        $CAM = $thresholdXML->addChild("Photo");

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

        $rpiFile = fopen("{$thresholddir}{$rpi}.xml", "w");
        fwrite($rpiFile, $thresholdXML->asXML());
        fclose($rpiFile);
    }
}

generateThresholdFile();
?>