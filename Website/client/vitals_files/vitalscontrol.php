<?php
    //Require
    ob_start();
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");

    //TODO: Filter $_POST 
    //This php code will update the 'vitals' table for the respective raspberry pi and its user
    $currentUser = $_SESSION['username']; //Get Current User Name
    $amtOfRPis = count(array_unique($_POST['rpid'])); //Get the amount of RPis to edit
    $amtOfVitals = count($_POST['vitalname']); //Get the amount of Vitals to edit - will be the same count for $vitalupper and $vitallower
    
    //Using array_map with trim because the result of the splicing/$_POST causes whitespaces
    $listOfRPis_unordered = array_unique($_POST['rpid']);
    $listOfRPis = array_map('trim', array_splice($listOfRPis_unordered, 0, $amtOfRPis));
    //$listOfSelectedOptions = array_map('trim', $_POST['selectedOption']);
    $listOfSelectedOptions = $_POST['selectedOption'];
    $listOfVitalNames = array_map('trim', $_POST['vitalname']);

    //Get Timestamp
    date_default_timezone_set('UTC');
    $currentTimestamp = date("Y-m-d H:i:s", time());

    //Update Database
    $counter_j = 0;
    for($counter_i = 0; $counter_i < $amtOfRPis; $counter_i++) {
        $tempRPi = $listOfRPis[$counter_i];
        $tempOffset = 0;

        if($counter_j % 2 === 0) { $tempOffset = $counter_i + 2; } //In order to determine the current index in the array, we need to establish an offset for the while loop so that it won't access an index that it shouldn't (above or below the set of variables for the current raspberry pi)
        while($counter_j != ($counter_i + $tempOffset)) {
            $tempVV = $listOfSelectedOptions[$counter_j];
            $tempVN = $listOfVitalNames[$counter_j];

            //Get VID
            $sqlGetVID = "SELECT VID FROM vitals WHERE VN='{$tempVN}' AND RPID='{$tempRPi}' AND USR='{$currentUser}';";
            $resultSqlGetVID = mysqli_query($conn, $sqlGetVID);
            $tempVID = mysqli_fetch_assoc($resultSqlGetVID)['VID'];

            //Update Database
            $sqlUpdate = "UPDATE status SET VV='{$tempVV}', TS='{$currentTimestamp}' WHERE VID='{$tempVID}' AND RPID='{$tempRPi}' AND USR='{$currentUser}';";
            $sqlLog = "INSERT INTO `log`(`VID`, `TYP`, `USR`, `RPID`, `V1`, `V2`, `TS`) VALUES ('{$tempVID}', 'UP', '{$currentUser}', '{$tempRPi}', '{$tempVV}', '', '{$currentTimestamp}');";
            $resultSqlUpdate = mysqli_query($conn, $sqlUpdate);
            $resultSqlLog = mysqli_query($conn, $sqlLog);

            //Increment counter_j
            $counter_j += 1; 
        }
    }

    header("Refresh:0; url=../vitals.php");
    ob_end_flush();
?>