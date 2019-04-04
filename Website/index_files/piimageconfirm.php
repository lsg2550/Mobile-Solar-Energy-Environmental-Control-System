<?php
    /**
     * Name: piimageconfirm.php
     * Description: This script is called by the Raspberry pi during the update interval.
     * This takes in two arguments, RPID and 'clarity' or 'capture'.
     * Both clarity and capture will contain the name of the respective file or directory.
     * This script will first determine if either exists and updates the database accordingly.
     * This script is called separately by the Raspberry Pi. So one instance will call this script
     * for motion detection ('capture'), the other will call this script for clarity ('clarity')
     */

    function logImages($fileName, $isClarity=False, $isMotion=False) {
        // Require
        require("connect.php");
        require("operations.php");

        // global
        global $RASPBERRY_PI_ID;

        // Initialize Variables
        $TYP = "ST";
        $V1 = $V2 = "";
        if ($isClarity) { $V1 = $V2 = "CLR"; }
        elseif ($isMotion) { $V1 = $V2 = "IMG"; }

        // Get owner/user from database
        $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = '{$RASPBERRY_PI_ID}'";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

        // Get Timestamp
        $TS = getTimeStampFromFileName($fileName);
        echo "$TS";

        //Update DB
        $sqlInsertIntoLog = "INSERT INTO log (VID, TYP, USR, RPID, V1, V2, TS) VALUES (NULL, '{$TYP}', '{$USR}', '{$RASPBERRY_PI_ID}', '{$V1}', '{$V2}', '{$TS}');";
        mysqli_query($conn, $sqlInsertIntoLog);
        
        // If the function makes it to the end with no issues than we return an OK
        echo "OK";
    }

    // Initialize Variables
    $RASPBERRY_PI_ID = $_GET['rpid'];
    $isClarityUpdate = isset($_GET['clarity']) ? true : false;
    $isMotionUpdate = isset($_GET['capture']) ? true : false;

    try {
        if ($isClarityUpdate) {
            $CLARITY_DIRECTORY = "../claritydir/";
            $CLARITY_FILE_NAME = $_GET["clarity"];
            $CLARITY_FILE_FULL_PATH = $CLARITY_DIRECTORY . $CLARITY_FILE_NAME;

            if (is_file($CLARITY_FILE_FULL_PATH)) { logImages($CLARITY_FILE_NAME, $isClarity=$isClarityUpdate); }
            else { echo "NO"; } 
        } else if ($isMotionUpdate) {
            $DETECT_DIRECTORY = "../detectdir/";
            $DETECT_DIRECTORY_NAME = $_GET["capture"];
            $DETECT_DIRECTORY_FULL_PATH = $DETECT_DIRECTORY . $DETECT_DIRECTORY_NAME;
            
            if (is_dir($DETECT_DIRECTORY_FULL_PATH)) { logImages($DETECT_DIRECTORY_NAME, $isMotion=$isMotionUpdate); }
            else { echo "NO"; }
        } else {
            echo "NO";
        }
    } catch (Exception $e) {
        //echo $e;
        echo "ERROR"; 
    }
?>