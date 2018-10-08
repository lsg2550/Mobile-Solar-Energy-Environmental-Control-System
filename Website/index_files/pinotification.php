<?php
    //Require
    require("connect.php");

    //Init - Get Username
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    //Get User Email
    $sqlGetEmail = "SELECT email FROM users WHERE username = '{$USR}'";
    $resultsGetEmail = mysqli_query($conn, $sqlGetEmail);
    $EMAIL = mysqli_fetch_assoc($resultsGetEmail)['email'];

    switch ($_GET["noti"]) {
        case 'voltage':
            $emailMessage = "'{$_GET["rpid"]}' has triggered the voltage threshold with ''V!";
            $emailMessage = wordwrap($emailMessage, 70);
            echo $emailMessage; 
            mail($EMAIL, "Low Voltage", $emailMessage);    
            break;
        default:
            break;
    }
?>