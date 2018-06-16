<?php

//Require
require("sessionstart.php");
require("sessioncheck.php");

//Remove extra characters and place data into an array, all data carried out in this website will follow the same data format
//e.g: Initial array '[data1, data2, data3]' will become 'array[0] = data1, array[1] = data2, array[2] = data3'
function splitDataIntoArray($stringToReplace) { 
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);

    //Returns array of data
    return $stringSplit;
}

function generateThresholdFile() {
    //Include
    include("connect.php");

    //Init
    $thresholddir = "../../rpis/";
    $USR = $_SESSION['username']; //Get Current User Name

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
?>