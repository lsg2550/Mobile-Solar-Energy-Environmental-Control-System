<?php
    function processXML($xmlFileName) {
        // Require
        require("connect.php");

        //Init
        global $xmlDirectory;
        $processedxmldir = "../../processedxmls/";
        $TYP = "ST";

        //Init - Get Username
        $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

        //Load xml files from $listOfXMLFiles & process them - grab data and update database
        $xml = json_decode(file_get_contents($xmlDirectory . $xmlFileName));
        $RPID = $xml->{"rpid"};
        $TS = $xml->{"log"};

        foreach($xml as $key => $value) {
            switch($key) {
                case "log":
                case "rpid":
                    break;
                default:
                    //Vital Name & Vital Values
                    $VN = $key;
                    $V1 = $value;
                    $V2 = null;

                    if($VN == "gps") {
                        $value = str_replace(array('"', '[', ']', '"'), '', $value);
                        $value = explode(",", $value);
                        $V1 = $value[0]; //Latitude
                        $V2 = $value[1]; //Longitude
                    }

                    //Get VID
                    $sqlGetVID = "SELECT VID FROM vitals WHERE VN='{$VN}' AND RPID='{$RPID}' AND USR='{$USR}';";
                    $resultGetVID = mysqli_query($conn, $sqlGetVID);
                    $VID = mysqli_fetch_assoc($resultGetVID)['VID'];

                    //Update DB
                    $sqlInsertIntoLog = "INSERT INTO log (VID, TYP, USR, RPID, V1, V2, TS) VALUES ('{$VID}', '{$TYP}', '{$USR}', '{$RPID}', '{$V1}', '{$V2}', '{$TS}');";
                    $sqlUpdateCurrentStatus = "REPLACE INTO status (VID, V1, V2, TS, USR, RPID) VALUES ('{$VID}', '{$V1}', '{$V2}', '{$TS}', '{$USR}', '{$RPID}');";

                    // Execute SQL queries to update the database
                    mysqli_query($conn, $sqlInsertIntoLog);
                    mysqli_query($conn, $sqlUpdateCurrentStatus);
            }
        }

        //Move out of the waiting xml folder
        rename($xmlDirectory . $xmlFileName, $processedxmldir . $xmlFileName);
    }

    //TODO: Filter $_GET
    $xmlDirectory = "../../rpixmls/";
    $xmlFilename = $_GET["xmlfile"];
    $xmlFullFilePath = $xmlDirectory . $xmlFilename;

    if(is_file($xmlFullFilePath)) {
        try { 
            processXML($xmlFilename); 
            echo "OK";
        } catch (Exception $e) { echo "ERROR"; }
    } else { echo "NO"; }
?>