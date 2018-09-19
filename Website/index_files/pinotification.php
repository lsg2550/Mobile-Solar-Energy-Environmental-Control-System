<?php
    //Require
    require("connect.php");

    //Init - Get Username
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    switch ($_GET["noti"]) {
        case 'voltage':
            $emailMessage = "It has been more than 7 days since RPi '{$result['rpiID']}' has made contact with the server!";
            $emailMessage = wordwrap($emailMessage, 70);
            echo $emailMessage; 
            mail($currentRPiOwnerEmail, "Raspberry Pi Last Contact", $emailMessage);    
            break;
        default:
            # code...
            break;
    }
?>