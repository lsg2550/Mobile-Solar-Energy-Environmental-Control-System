<?php
    function processXML($xmlFileName) {
        //Include
        include("connect.php");

        //Init
        global $xmlDirectory;
        $TYP = "ST";
        $V1 = "IMG";
        $V2 = "";

        //Init - Get Username
        $RPID = $_GET["rpid"];
        $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = '{$RPID}'";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

        //Get Timestamp    
        date_default_timezone_set('America/Chicago');
        $dateFormat = "Y-m-d H:i:s";
        $TS = date_create_from_format($dateFormat, $xmlFileName);

        //Update DB
        $sqlInsertIntoLog = "INSERT INTO log (VID, TYP, USR, RPID, V1, V2, TS) VALUES (NULL, '{$TYP}', '{$USR}', '{$RPID}', '{$V1}', '{$V2}', '{$TS}');";
        $resultInsertIntoLog = mysqli_query($conn, $sqlInsertIntoLog);
    }

    //TODO: Filter $_GET
    $xmlDirectory = "../../DetectDir/";
    $xmlFilename = $_GET["capture"];
    $xmlFullFilePath = $xmlDirectory . $xmlFilename;

    if(is_dir($xmlFullFilePath)) {
        try {
            processXML($xmlFilename);
            echo "OK";
        } catch (Exception $e) { echo "ERROR"; }
    } else { echo "NO"; }
?>