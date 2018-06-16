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

function processXML($xmlFileName = null) {
    //Include
    include("connect.php");

    //Init
    $xmldir = "../../xmls/";
    $processedxmldir = "../../processedxmls/";
    $listOfXMLFiles = [];
    $USR = $_SESSION['username']; //Get Current User Name
    $TYP = "ST";

    if($xmlFileName == null) {
        //No Specific File Name Init
        $directoryFiles = scandir($xmldir);

        //Store XML Files into $listOfXMLFiles
        foreach($directoryFiles as $directoryFile) {
            if(is_file($xmldir . $directoryFile)) { 
                $listOfXMLFiles[] = $directoryFile; 
            }
        }
    } else {
        //Because we already confirmed in piconfirm.php that the file is a file and exists, we can safely place it into $listOfXMLFiles
        //$xmlFilePath = $xmldir . $xmlFileName;
        $listOfXMLFiles[] = $xmlFileName;
    }

    //Load xml files from $listOfXMLFiles & process them - grab data and update database
    sort($listOfXMLFiles);
    foreach($listOfXMLFiles as $xmlFile) {
        $xml = simplexml_load_file($xmldir . $xmlFile);
        $RPID = $xml->rpid;
        $TS = $xml->log;
        //print_r($xml);

        foreach($xml as $key => $value) {
            switch($key) {
                case "log":
                case "rpid":
                case "solarpanelvalue":
                    break;
                default:
                    //VitalName
                    $VN = "";
                    if($key === "solarpanel") { $VN = "solar panel"; } 
                    else { $VN = $key; }

                    //Get VID
                    $sqlGetVID = "SELECT VID FROM vitals WHERE VN='{$VN}' AND RPID='{$RPID}' AND USR='{$USR}';";
                    $resultGetVID = mysqli_query($conn, $sqlGetVID);
                    $VID = mysqli_fetch_assoc($resultGetVID)['VID'];

                    //Vital Values
                    $V1 = $VV = $value;
                    $V2 = "";

                    //Update DB
                    $sqlInsertIntoLog = "INSERT INTO log (VID, TYP, USR, RPID, V1, V2, TS) VALUES ('{$VID}', '{$TYP}', '{$USR}', '{$RPID}', '{$V1}', '{$V2}', '{$TS}');";
                    $sqlUpdateCurrentStatus = "UPDATE status SET VV='{$VV}', TS='{$TS}' WHERE VID='{$VID}' AND USR='{$USR}' AND RPID='{$RPID}';";
                    $resultInsertIntoLog = mysqli_query($conn, $sqlInsertIntoLog);
                    $resultUpdateCurrentStatus = mysqli_query($conn, $sqlUpdateCurrentStatus);
                    //echo $sqlInsertIntoLog . "<br>" . $sqlUpdateCurrentStatus . "<br>";
            }
        }

        //Move out of the waiting xml folder
        rename($xmldir . $xmlFile, $processedxmldir . $xmlFile);
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
}
?>