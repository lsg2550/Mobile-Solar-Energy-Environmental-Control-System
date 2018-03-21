<?php

ob_start();

//Require
require('../../index_files/connect.php');

//Functions
function getData($stringToReplace, $printAll) {
    //Remove extra characters and place data into array
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);

    //Get Data and Return HTML Table
    $print = -1;
    if ($printAll === true) {
        $print = 0;
    }

    $stringHTML = '<tr>';
    for ($i = 0; $i < count($stringSplit) + $print; $i++) {
        $stringHTML .= '<th>' . $stringSplit [$i] . '</th>';
    }
    $stringHTML .= '</tr>';

    return $stringHTML;
}

function getTimeStamp($stringToReplace) {
    //Remove extra characters and place data into array
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);
    return end($stringSplit);
}

//Session
if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}

if ($_SESSION['user'] !== 1) {
    header('Location: ../../index.html');
    ob_end_flush();
    exit();
}

//Code
$currentUser = $_POST['currentuser'];
//$sqlCurrentStatus = 'SELECT VN, VV, TS, RPID FROM status NATURAL JOIN vitals WHERE USR="'. $currentUser .'"'; //Select all current status
//$sqlLog = 'SELECT VID, TYP, RPID, V1, V2, TS FROM log WHERE USR="'. $currentUser .'"'; //Select all log
$sqlCurrentStatus = 'SELECT VN, VV, RPID, TS FROM status NATURAL JOIN vitals'; //Select all current status
$sqlLog = 'SELECT VID, TYP, RPID, V1, V2, TS FROM log'; //Select all log

$resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
$resultLog = mysqli_query($conn, $sqlLog);

$arrayCurrentStatus = array();
$arrayLog = array();

if(mysqli_num_rows($resultCurrentStatus) > 0){
    while($row = mysqli_fetch_assoc($resultCurrentStatus)){
        $tempRow = '[' . $row['VN'] . ',' . $row['VV'] . ',' . $row['RPID'] . ',' . $row['TS'] . ']';
        $arrayCurrentStatus[] = $tempRow;
    }
}

if(mysqli_num_rows($resultLog) > 0){
    while($row = mysqli_fetch_assoc($resultLog)){
        if($row['V2'] == NULL || $row['V2'] == ''){
            $row['V2'] = "N/A";
        }

        $tempRow = '[' . $row['VID'] . ',' . $row['TYP']. ',' . $row['RPID'] . ',' . $row['V1'] . ',' . $row['V2'] . ',' . $row['TS'] . ']';
        $arrayLog[] = $tempRow;
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="listlogs.css">
    </head>
    <fieldset><legend>Current Status - <?php echo getTimeStamp($arrayCurrentStatus[0]); ?></legend>
        <?php
        echo '<table>';

        echo '<tr>';
        echo '<th>Vital Name</th>' . '<th>Status</th>' . '<th>RPiID</th>';
        echo '</tr>';
        foreach ($arrayCurrentStatus as $aCS) {
            echo getData($aCS, false);
        }

        echo '</table>';
        ?>
    </fieldset>
    <fieldset><legend>Log</legend>
        <?php
        echo '<table>';

        echo '<tr>';
        echo '<th>VID</th>' . '<th>TYP</th>' . '<th>RPiID</th>' . '<th>Vital 1</th>' . '<th>Vital 2</th>' . '<th>Timestamp</th>';
        echo '</tr>';
        foreach ($arrayLog as $aL) {
            echo getData($aL, true);
        }

        echo '</table>';
        ?>
    </fieldset>
</html>