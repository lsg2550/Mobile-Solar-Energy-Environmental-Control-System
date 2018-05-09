<?php
//Require
require("../index_files/sessionstart.php");
require("../index_files/sessioncheck.php");
require("../index_files/connect.php");
require("../index_files/operations.php");
require("vitals_files/generatecontrolpanel.php");

//Database Queries
$currentUser = $_SESSION['username']; //Get Current User Name
$sqlVitalsCurrentStatus = "SELECT VN, VV, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select current status of 
$sqlVitalsCurrentThresholds = "SELECT VN, VL, VU, RPID FROM vitals WHERE USR='{$currentUser}';"; //Select vital settings (lower & upper limits) to display and allow user to change those vital settings

//Execute Queries
$resultCurrentStatus = mysqli_query($conn, $sqlVitalsCurrentStatus);
$resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);

//Store CurrentStatus Query Results into $arrayCurrentStatus
$arrayCurrentStatus = array(); 
if(mysqli_num_rows($resultCurrentStatus) > 0) {
    while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
        $tempRow = "[ {$row['VN']}, {$row['VV']}, {$row['RPID']} ]";
        $arrayCurrentStatus[] = $tempRow;
    }
}

//Store Current Tresholds Query Results into $arrayCurrentThreshold
$arrayCurrentThreshold = array(); 
if(mysqli_num_rows($resultCurrentThresholds) > 0) {
    while($row = mysqli_fetch_assoc($resultCurrentThresholds)) {
        $tempRow = "[ {$row['VN']}, {$row['VL']}, {$row['VU']}, {$row['RPID']} ]";
        $arrayCurrentThreshold[] = $tempRow;
    }
}
?>

<html>
    <head>
        <title>Remote Site - Vital Control Page</title>
        <link rel="stylesheet" type="text/css" href="vitals_files/vitals.css">
    </head>   
    <body>
        <div><h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1></div>

        <div> <!-- Send User Back to Client Page -->
            <form action="client.php" method="post">
                <input type="submit" value="Back to Client Page">
            </form>
        </div>

        <div> <!-- Vital Status/Threshold Control Panels -->
            <div> 
                <form action="vitals_files/vitalscontrol.php" method="post">
                    <fieldset><legend>Vital Status Control Panel:</legend>
                        <?php 
                            echo "<fieldset>";
                            foreach ($arrayCurrentStatus as $aCS) { echo generateVitalStatusControlPanel($aCS); } //Generate Status Control Panel
                            echo "</table></fieldset>";
                            resetGlobals();
                        ?>
                        <input type="submit" value="Commit Any Changes">
                    </fieldset>
                </form>
            </div>
            <div>
                <form action="vitals_files/vitalsthreshold.php" method="post">
                    <fieldset><legend>Vital Threshold Control Panel:</legend>
                        <?php
                            echo "<fieldset>";
                            foreach ($arrayCurrentThreshold as $aCT) { echo generateVitalThresholdControlPanel($aCT); } //Generate Threshold Control Panel
                            echo "</table></fieldset>";
                            resetGlobals();
                        ?>
                        <input type="submit" value="Commit Any Changes">
                    </fieldset>
                </form>
            </div>
        </div>
    </body>
</html>