<?php
    /**
     * Description: The vitals page will allow a user to control and set the thresholds of every RPi they own.
     * 
     * Note: Currently the database access table has user read/write permissions for a user who has access to a RPi.
     * You may need to alter the code to allow users who have write permissions to also edit the thresholds of the 
     * RPi, while those who have read permissions cannot. I made this change near the end of the of my thesis submission so
     * I was unable to implement these changes. But it is all in the database so you would just need to make new SQL queries to
     * get these permissions.
     */

    //Require
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
    require("vitalsgeneratepanel.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vital Control Panel</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="vitals.js"></script>
    <link rel="stylesheet" type="text/css" href="vitals.css">
    <link rel="stylesheet" type="text/css" href="../navigator.css">
</head>

<body>
    <h1 class="title">Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    <div class="formdiv">
        <form action="../client.php" method="post">
            <input type="submit" value="Client Page">
        </form>
        <form action="../../index_files/logout.php" method="post">
            <input type="submit" value="Log Out">
        </form>
    </div>

    <div class="displays">
        <form id="control-panel-form" method="post">
            <fieldset id="control-panel-fieldset">
                <legend>Vital Threshold Control Panel</legend>
                <div id="generated-control-panel">
                <?php echo generateVitalThresholdControlPanel($arrayCurrentThreshold); //Generate Threshold Control Panel ?>
                </div>
                <input type="submit" value="Commit Any Changes">
            </fieldset>
        </form>
    </div>
</body>

</html>