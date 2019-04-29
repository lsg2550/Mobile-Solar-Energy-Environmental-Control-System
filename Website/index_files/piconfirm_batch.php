<?php
    /**
     * USE THIS ONLY IF 000webhost DELETES THE LOG TABLE FROM THE DATABASE
     * ALL JSON FILES SHOULD BE STORED ON THE FILE SERVER (CMS) SO THAT THE LOG TABLE
     * CAN BE RECOVERED!!
     */

    function processXML($xmlFileName) {
        // Require
        require("connect.php");

        //Init
        global $xmlDirectory;
        $processedxmldir = "../../processedxmls/";
        $TYP = "ST";

        //Init - Get Username
        $sqlGetUser = "SELECT `uid-owner` FROM rpi WHERE rpid={$_GET["rpid"]}";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $UID = mysqli_fetch_assoc($resultsGetUser)['uid-owner'];

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
                    $sqlGetVID = "SELECT vid FROM vitals WHERE vn='{$VN}' AND rpid='{$RPID}';";
                    $resultGetVID = mysqli_query($conn, $sqlGetVID);
                    $VID = mysqli_fetch_assoc($resultGetVID)['vid'];

                    //Update DB
                    $sqlInsertIntoLog = "INSERT INTO logs (vid, typ, uid, rpid, v1, v2, ts) VALUES ('{$VID}', '{$TYP}', '{$UID}', '{$RPID}', '{$V1}', '{$V2}', '{$TS}');";
                    
                    // Execute SQL queries to update the database
                    mysqli_query($conn, $sqlInsertIntoLog);
            }
        }

        //Move out of the waiting xml folder
        rename($xmlDirectory . $xmlFileName, $processedxmldir . $xmlFileName);
    }

    //TODO: Filter $_GET
    $xmlDirectory = "../../rpixmls/";
    $xmlDirectoryFileNames = array_diff(scandir($xmlDirectory), array('.', '..'));
    foreach ($xmlDirectoryFileNames as $index => $xmlFilename) {
        $xmlFullFilePath = $xmlDirectory . $xmlFilename;

        if(is_file($xmlFullFilePath)) {
            try { 
                processXML($xmlFilename); 
                echo "OK";
            } catch (Exception $e) { echo "ERROR"; }
        } else { echo "NO"; }
    }
?>