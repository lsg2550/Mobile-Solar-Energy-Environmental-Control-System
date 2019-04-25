<?php
    //Require
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");

    //This php code will update the 'vitals' table for the respective raspberry pi and its user
    $currentUID = $_SESSION['username']; //Get Current User Name
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
            $sqlGetVID = "SELECT vid FROM vitals WHERE vn='{$tempVN}' AND rpid='{$tempRPi}';";
            $resultSqlGetVID = mysqli_query($conn, $sqlGetVID);
            $tempVID = mysqli_fetch_assoc($resultSqlGetVID)['vid'];

            //Update Database
            $sqlUpdate = "UPDATE vitals SET vl='{$tempVL}', vu='{$tempVU}' WHERE vn='{$tempVN}' AND rpid='{$tempRPi}';";
            $sqlLog = "INSERT INTO log(vid, typ, uid, rpid, v1, v2, ts) VALUES ('{$tempVID}', 'UP', '{$currentUID}', '{$tempRPi}', '{$tempVL}', '{$tempVU}', '{$currentTimestamp}');";
            $resultSqlUpdate = mysqli_query($conn, $sqlUpdate);
            $resultSqlLog = mysqli_query($conn, $sqlLog);
        }
    }
    
    //Output
    echo "ok";
?>