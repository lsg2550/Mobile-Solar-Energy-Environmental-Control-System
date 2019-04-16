<?php
    /**
     * Name: pinotification.php
     * Description: This script is called by the Raspberry Pi, when it runs its notification function.
     * In the notification function, if any of the ESSI sensors trigger a threshold flag, this script will
     * be called. Thus creating an email and sending it to the user.
     * 
     * Note: Improvements to work on, to avoid spamming the owner/user about these threshold breaches, allow this
     * script to take in multiple arguments and have the raspberry pi send in multiple arguments. Also, work on
     * getting both the owner/user and their email in one sequel statement using a join.
     */

    // Require
    require("connect.php");

    // Initialize Variables
    $RASPBERRY_PI_ID = $_GET['rpid'];
    $ESSI_TRIGGER = $_GET['noti'];
    $ESSI_TRIGGER_VALUE = $_GET['valu'];

    // Get owner (user) from database
    $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$RASPBERRY_PI_ID}";
    $resultsGetUser = mysqli_query($conn, $sqlGetUser);
    $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

    // Get owner's (user) email from the database
    $sqlGetEmail = "SELECT email FROM users WHERE username = '{$USR}'";
    $resultsGetEmail = mysqli_query($conn, $sqlGetEmail);
    $EMAIL = mysqli_fetch_assoc($resultsGetEmail)['email'];

    // Determine email message
    $emailMessage = "";
    switch ($ESSI_TRIGGER) {
        case "bvoltage":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the battery voltage threshold with '{$ESSI_TRIGGER_VALUE}'V!";
            break;
        case "bcurrent":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the battery current threshold with '{$ESSI_TRIGGER_VALUE}'mA!";
            break;
        case "spvoltage":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the pv voltage threshold with '{$ESSI_TRIGGER_VALUE}'V!";
            break;
        case "spcurrent":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the pv current threshold with '{$ESSI_TRIGGER_VALUE}'mA!";
            break;
        case "cccurrent":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the charge controller current threshold with '{$ESSI_TRIGGER_VALUE}'mA!";
            break;
        case "temperatureI":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the inside temperature threshold with '{$ESSI_TRIGGER_VALUE}'F!";
            break;        
        case "temperatureO":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the outside temperature threshold with '{$ESSI_TRIGGER_VALUE}'F!";
            break;
        case "humidityI":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the inside humidity threshold with '{$ESSI_TRIGGER_VALUE}'F!";
            break;        
        case "humidityO":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has triggered the outside humidity threshold with '{$ESSI_TRIGGER_VALUE}'F!";
            break;
        case "motion":
            $emailMessage = "'{$RASPBERRY_PI_ID}' has reported movement near your vehicle. Please check 'https://remote-ecs.000webhostapp.com/client/image_files/image.php' to view the images.";
            break;
        default:
            exit();
    }

    // Send Email to User
    //echo $emailMessage;
    $emailMessage = wordwrap($emailMessage, 70);
    mail($EMAIL, "Raspberry Pi {$RASPBERRY_PI_ID} Notification", $emailMessage);
?>