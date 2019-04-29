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

        // Get owner/user from database
        $sqlGetUser = "SELECT `uid-owner` FROM rpi WHERE rpid='{$RASPBERRY_PI_ID}'";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $UID = mysqli_fetch_assoc($resultsGetUser)['uid-owner'];

        //Get VID from database
        $VN = "";
        if ($isClarity) { $VN = "Clarity"; } elseif ($isMotion) { $VN = "Photo"; }
        $sqlGetVID = "SELECT vid FROM vitals WHERE vn='{$VN}' AND rpid='{$RASPBERRY_PI_ID}';";
        $resultGetVID = mysqli_query($conn, $sqlGetVID);
        $VID = mysqli_fetch_assoc($resultGetVID)['vid'];

        // Initialize Variables
        $TYP = "ST";
        $V1 = $V2 = "";
        if ($isClarity) { $V1 = getClarityValFromFileName($fileName); } elseif ($isMotion) { $V1 = $V2 = "IMG"; }

        // Get Timestamp
        $TS = getTimeStampFromFileName($fileName);

        //Update DB
        $sqlInsertIntoLog = "INSERT INTO logs (vid, typ, uid, rpid, v1, v2, ts) VALUES ('{$VID}', '{$TYP}', '{$UID}', '{$RASPBERRY_PI_ID}', '{$V1}', '{$V2}', '{$TS}');";
        #echo $sqlInsertIntoLog;
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

            if (is_file($CLARITY_FILE_FULL_PATH)) { logImages($CLARITY_FILE_NAME, $isClarityUpdate, $isMotionUpdate); }
            else { echo "NO"; } 
        } else if ($isMotionUpdate) {
            $DETECT_DIRECTORY = "../detectdir/";
            $DETECT_DIRECTORY_NAME = $_GET["capture"];
            $DETECT_DIRECTORY_FULL_PATH = $DETECT_DIRECTORY . $DETECT_DIRECTORY_NAME;
            
            if (is_dir($DETECT_DIRECTORY_FULL_PATH)) { logImages($DETECT_DIRECTORY_NAME, $isClarityUpdate , $isMotionUpdate); }
            else { echo "NO"; }
        } else {
            echo "NO";
        }
    } catch (Exception $e) {
        //echo $e;
        echo "ERROR"; 
    }
?>