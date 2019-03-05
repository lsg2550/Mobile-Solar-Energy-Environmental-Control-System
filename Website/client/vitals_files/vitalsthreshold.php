<?php
    //Require
    ob_start();
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");

    //TODO: Filter $_POST 
    //This php code will update the 'vitals' table for the respective raspberry pi and its user
    $currentUser = $_SESSION['username']; //Get Current User Name
    $amtOfRPis = count(array_unique($_POST['rpid'])); //Get the amount of RPis to edit
    $amtOfVitals = count($_POST['vitalname']); //Get the amount of Vitals to edit - will be the same count for $vitalupper and $vitallower
    
    //Using array_map with trim because the result of the splicing/$_POST causes whitespaces
    $listOfRPis_unordered = array_unique($_POST['rpid']);
    $listOfRPis = array_map('trim', array_splice($listOfRPis_unordered, 0, $amtOfRPis));
    $listOfLowerVitals = array_map('trim', $_POST['vitallower']);
    $listOfUpperVitals = array_map('trim', $_POST['vitalupper']);
    $listOfVitalNames = array_map('trim', $_POST['vitalname']);

    //Get Timestamp
    date_default_timezone_set('America/Chicago');
    $currentTimestamp = date("Y-m-d H:i:s", time());

    for($counter_i = 0; $counter_i < $amtOfRPis; $counter_i++) {
        $tempRPi = $listOfRPis[$counter_i];
        
        for($counter_j = 0; $counter_j < $amtOfVitals; $counter_j++) {
            $tempVL = $listOfLowerVitals[$counter_j];
            $tempVU = $listOfUpperVitals[$counter_j];
            $tempVN = $listOfVitalNames[$counter_j];

            //Get VID
            $sqlGetVID = "SELECT VID FROM vitals WHERE VN='{$tempVN}' AND RPID='{$tempRPi}' AND USR='{$currentUser}';";
            $resultSqlGetVID = mysqli_query($conn, $sqlGetVID);
            $tempVID = mysqli_fetch_assoc($resultSqlGetVID)['VID'];

            //Update Database
            $sqlUpdate = "UPDATE vitals SET VL='{$tempVL}', VU='{$tempVU}' WHERE VN='{$tempVN}' AND RPID='{$tempRPi}' AND USR='{$currentUser}';";
            $sqlLog = "INSERT INTO `log`(`VID`, `TYP`, `USR`, `RPID`, `V1`, `V2`, `TS`) VALUES ('{$tempVID}', 'UP', '{$currentUser}', '{$tempRPi}', '{$tempVL}', '{$tempVU}', '{$currentTimestamp}');";
            $resultSqlUpdate = mysqli_query($conn, $sqlUpdate);
            $resultSqlLog = mysqli_query($conn, $sqlLog);
        }
    }

    header("Refresh:0; url=vitals.php");
    ob_end_flush();
?>