<?php
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