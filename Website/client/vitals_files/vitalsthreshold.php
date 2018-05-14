<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");

    //TODO: Filter $_POST 
    //This php code will update the 'vitals' table for the respective raspberry pi and its user
    $currentUser = $_SESSION['username']; //Get Current User Name
    $amtOfRPis = count(array_unique($_POST['rpid'])); //Get the amount of RPis to edit
    $amtOfVitals = count($_POST['vitalname']); //Get the amount of Vitals to edit - will be the same count for $vitalupper and $vitallower
    
    $listOfRPis_unordered = array_unique($_POST['rpid']);
    $listOfRPis = array_splice($listOfRPis_unordered, 0, $amtOfRPis);
    $listOfLowerVitals = $_POST['vitallower'];
    $listOfUpperVitals = $_POST['vitalupper'];
    $listOfVitalNames = $_POST['vitalname'];
    $sqlUpdate = array();

    for($counter_i = 0; $counter_i < $amtOfRPis; $counter_i++) {
        $tempRPi = $listOfRPis[$counter_i];
        echo "RPID: " . $tempRPi;
        for($counter_j = 0; $counter_j < 3; $counter_j++) {
            $tempVL = $listOfLowerVitals[$counter_j];
            $tempVU = $listOfUpperVitals[$counter_j];
            $tempVN = $listOfVitalNames[$counter_j];

            $sqlUpdate[] = "UPDATE vitals SET VL='{$tempVL}', VU='{$tempVU}' WHERE VN='{$tempVN}' AND RPID='{$tempRPi}' AND USR='{$currentUser}';";
            $resultSqlUpdate = mysqli_query($conn, $sqlUpdate);
        }
    }
?>