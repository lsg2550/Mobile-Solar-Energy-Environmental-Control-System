<?php
    /**
     * USE THIS ONLY IF 000webhost DELETES THE LOG TABLE FROM THE DATABASE
     * ALL CLARITY IMAGES SHOULD BE STORED ON THE FILE SERVER (CMS) SO THAT THE LOG TABLE
     * CAN BE RECOVERED!!
     */

    
    // Require
    require("connect.php");
    require("operations.php");

    function logImages($fileName, $isClarity=False, $isMotion=False) {

        // global
        global $RASPBERRY_PI_ID;
        global $conn;

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
            $CLARITY_DIRECTORY_FILESNAMES = array_diff(scandir($CLARITY_DIRECTORY), array('.', '..'));

            foreach ($CLARITY_DIRECTORY_FILESNAMES as $index => $CLARITY_FILE_NAME) {
                $CLARITY_FILE_FULL_PATH = $CLARITY_DIRECTORY . $CLARITY_FILE_NAME;
                if (is_file($CLARITY_FILE_FULL_PATH)) { logImages($CLARITY_FILE_NAME, $isClarityUpdate, $isMotionUpdate); }
                else { echo "NO"; } 
            }
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