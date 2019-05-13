<?php
    /**
     * Description: This script logs the user out clearing their $_SESSION cookies and sending them back to the login page.
     * 
     * Note: ob_start() and ob_end_Flush() are required by 000webhost (or maybe browsers in general) to send the user elsewhere. All the code 
     * executes as normal but actually processes once ob_end_Flush() is reached.
     */

    //Buffer Start
    ob_start();

    //Require
    require("sessionstart.php");
    require("sessioncheck.php");

    //Log User Out
    $_SESSION['user'] = 0;
    $_SESSION['username'] = "";
    header("Location: ../index.html");

    //Buffer End
    ob_end_flush();
?>