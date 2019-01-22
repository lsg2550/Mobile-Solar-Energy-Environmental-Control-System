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
    $emailMessage = "";

    switch ($_GET["noti"]) {
        case "bvoltage":
            $emailMessage = "'{$_GET["rpid"]}' has triggered the battery voltage threshold with '{$_GET["valu"]}'V!";
            break;
        case "bcurrent":
            $emailMessage = "'{$_GET["rpid"]}' has triggered the battery current threshold with '{$_GET["valu"]}'mA!";
            break;
        case "spvoltage":
            $emailMessage = "'{$_GET["rpid"]}' has triggered the solar panel voltage threshold with '{$_GET["valu"]}'V!";
            break;
        case "spcurrent":
            $emailMessage = "'{$_GET["rpid"]}' has triggered the solar panel voltage threshold with '{$_GET["valu"]}'mA!";
            break;
        default:
            exit();
    }

    //Send Emailv
    //echo $emailMessage;
    $emailMessage = wordwrap($emailMessage, 70);
    mail($EMAIL, "Raspberry Pi {$_GET["rpid"]} Notification", $emailMessage);
?>