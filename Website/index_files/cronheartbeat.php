<?php
    //Require
    require("connect.php");

    //Date Difference Format
    $diffFormat = "%a";

    //Get Timestamp
    date_default_timezone_set('America/Chicago');
    $currentTimestamp = new DateTime(date("Y-m-d H:i:s", time()));

    //Get List of RPis
    $sqlGetListOfRPis = "SELECT rpid from rpi ORDER BY rpid ASC;";
    $resultGetListOfRPis = mysqli_query($conn, $sqlGetListOfRPis);

    //SQL get most recent timestamp
    while($result = mysqli_fetch_assoc($resultGetListOfRPis)) {
        //Get the most recent timestamp of the current RPi
        $sqlGetMostRecentTimestamp = "SELECT ts FROM log WHERE rpid='{$result['rpid']}' AND typ='ST' ORDER BY TS DESC LIMIT 1;";
        $resultGetMostRecentTimestamp = mysqli_query($conn, $sqlGetMostRecentTimestamp);
        $lastUpdateByRPi = mysqli_fetch_assoc($resultGetMostRecentTimestamp)['ts'];
        if($lastUpdateByRPi == "") { continue; } //If there are no updates by the current RPi, move on to the next one (if any)
        echo "Last Update by RPi '{$result['rpid']}' was at '{$lastUpdateByRPi}'...<br>";

        //Calculate difference between last update and now.
        $lastUpdateByRPi_DateObject = new DateTime($lastUpdateByRPi);
        $dateDifference = $lastUpdateByRPi_DateObject->diff($currentTimestamp);
        $dateDifferenceFormatted = $dateDifference->format($diffFormat);

        //Get RPi Owners Email
        $sqlGetRPiOwnerEmail = "SELECT email FROM users CROSS JOIN rpi WHERE rpid='{$result['rpid']}' AND `rpi.uid-owner`=users.uid;";
        $resultGetRPiOwnerEmail = mysqli_query($conn, $sqlGetRPiOwnerEmail);
        $currentRPiOwnerEmail = mysqli_fetch_assoc($resultGetRPiOwnerEmail)['email'];

        //If it has been more than a week since the RPi has contacted the server, send an email to the user!
        if($dateDifference->days > 7) {
            $emailMessage = "It has been more than 7 days since RPi '{$result['rpid']}' has made contact with the server!";
            $emailMessage = wordwrap($emailMessage, 70);
            echo $emailMessage; 
            mail($currentRPiOwnerEmail, "Raspberry Pi Last Contact", $emailMessage);    
        } else { 
            $emailMessage = "It has been less than 7 days since RPi '{$result['rpid']}' has made contact with the server!"; 
            $emailMessage = wordwrap($emailMessage, 70);
            echo $emailMessage; 
            mail($currentRPiOwnerEmail, "Raspberry Pi Last Contact", $emailMessage);    
        }
    }
?>