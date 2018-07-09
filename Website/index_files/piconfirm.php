<?php
function processXML($xmlFileName) {
    //Include
    include("connect.php");

    //Init
    $xmldir = "../../xmls/";
    $processedxmldir = "../../processedxmls/";
    $TYP = "ST";

    //Init - Get Username
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    //Load xml files from $listOfXMLFiles & process them - grab data and update database
    $xml = json_decode(file_get_contents($xmldir . $xmlFileName));
    $RPID = $xml->{"rpid"};
    $TS = $xml->{"log"};

    foreach($xml as $key => $value) {
        switch($key) {
            case "log":
            case "rpid":
            case "solarpanelvalue":
                break;
            default:
                //VitalName
                $VN = $key;

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
    rename($xmldir . $xmlFileName, $processedxmldir . $xmlFileName);
    echo "OK";
}

//TODO: Filter $_GET
$xmlDirectory = "../../xmls/";
$xmlFilename = $_GET["xmlfile"];
$xmlFullFilePath = $xmlDirectory . $xmlFilename;

if(is_file($xmlFullFilePath)) {
    try { processXML($xmlFilename); } 
    catch (Exception $e) { echo "ERROR";}
} else {
    echo "NO";
}

?>