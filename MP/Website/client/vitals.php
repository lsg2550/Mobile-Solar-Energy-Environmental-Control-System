<?php
//Require
require('../index_files/connect.php');
//require('../index_files/session.php');

//Grab Data from Database
$currentUser = $_SESSION['username'];
$sqlVitalsCurrentStatus = 'SELECT VN, VV, RPID FROM status NATURAL JOIN vitals WHERE USR="' . $currentUser . '";';
$sqlVitalsCurrentThresholds = 'SELECT VN, VL, VU, RPID FROM vitals WHERE USR="' . $currentUser . '";';

$resultCurrentStatus = mysqli_query($conn, $sqlVitalsCurrentStatus);
$resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    exit();
}
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