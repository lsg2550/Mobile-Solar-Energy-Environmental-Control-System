<?php
//Require
require('../index_files/sessionstart.php');
require('../index_files/sessioncheck.php');
require('../index_files/connect.php');

/* Database Queries */
$currentUser = $_SESSION['username']; //Get Current User Name
$sqlVitalsCurrentStatus = 'SELECT VN, VV, RPID FROM status NATURAL JOIN vitals WHERE USR="' . $currentUser . '";'; //Select current status of 
$sqlVitalsCurrentThresholds = 'SELECT VN, VL, VU, RPID FROM vitals WHERE USR="' . $currentUser . '";'; //Select vital settings (lower & upper limits) to display and allow user to change those vital settings

//Execute Queries
$resultCurrentStatus = mysqli_query($conn, $sqlVitalsCurrentStatus);
$resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);
?>

<html>
    <head>
        <title>Remote Site - Vitals Control Page</title>
        <link rel="stylesheet" type="text/css" href="vitals_files/vitals.css">
    </head>   
    <body>
        <div>
            <form action="vitals_files/vitalscontrol.php" method="post">
                <fieldset>
                    <legend>Vitals' Control Panel:</legend>
                    <!--TODO: PHP TO LOOP AND CREATE TABLES AND FORM-->
                </fieldset>
            </form>
        </div>

        <div>
            <form action="vitals_files/vitalsthreshold.php" method="post">
                <fieldset>
                    <legend>Vitals' Threshold Panel:</legend>
                    <!--TODO: PHP TO LOOP AND CREATE TABLES AND FORM-->
                </fieldset>
            </form>
        </div>
    </body>
</html>