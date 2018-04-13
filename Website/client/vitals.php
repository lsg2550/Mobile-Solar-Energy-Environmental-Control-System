<?php
//Require
require('../index_files/sessionstart.php');
require('../index_files/sessioncheck.php');
require('../index_files/connect.php');
require('../operations/operations.php');
require('vitals_files/selectoptions.php');

/* Functions */
$currentRaspberryPi = '-1'; 
$initalRaspberryPi = true;

//generateVitalsControlPanel - Generates an HTML vitals control panel where the user will overwrite current vitals status (e.g if exhaust is on, the user can force it off)
function generateVitalStatusControlPanel($vitalControlData) {
    $vitalControlDataFormatted = splitDataIntoArray($vitalControlData); //Format Data into a processable format
    $vitalStatusControlPanel = ''; //Initialize Vital Status Control Panel HTML

    //If the vital name is Temperature, Battery, or Photo (vitals that can't be overwritten outside from the thresholds panel) then return nothing
    if($vitalControlDataFormatted[0] === 'Temperature' || $vitalControlDataFormatted[0] === 'Battery' || $vitalControlDataFormatted[0] === 'Photo') { return ''; }
    if (end($vitalControlDataFormatted) !== $GLOBALS['currentRaspberryPi']) { //end($vitalControlDataFormatted) will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
        $GLOBALS['currentRaspberryPi'] = end($vitalControlDataFormatted);

        if($GLOBALS['initalRaspberryPi'] === false){ $vitalStatusControlPanel .= '</table>'; } //Closes the table from the previous RaspberryPi
        else { $GLOBALS['initalRaspberryPi'] == false; } //Initial table will change this to false after it creates the first table header

        $vitalStatusControlPanel .= '<legend>Raspberry Pi - ' . $GLOBALS['currentRaspberryPi'] . '</legend><table><tr><th>Vital Name</th><th>Status</th></tr>';
    }

    //Generate Panel
    $vitalStatusControlPanel .= '<tr>';
    $vitalStatusControlPanel .= generateSelectOptions($vitalControlDataFormatted[0], $vitalControlDataFormatted[1], $GLOBALS['currentRaspberryPi']); //[0] Vital Name; [1] Vital Value; $GLOBALS['currentRaspberryPi'] Raspberry Pi ID
    $vitalStatusControlPanel .= '</tr>';

    //Return HTML for the current row OR current row including the closure of the previous row
    return $vitalStatusControlPanel;
}

//generateVitalsThresholdControlPanel - Generates an HTML threshold panel where the user will define thresholds for the vitals to follow (e.g default battery VL is 12.6v, the user can change this to 12.0v)
function generateVitalThresholdControlPanel($vitalThresholdData) {
    $vitalThresholdDataFormatted = splitDataIntoArray($vitalThresholdData);
    $vitalThresholdControlPanel = ''; //Initialize Vital Threshold Control Panel HTML

    //If the vital name is Solar Panel, Exhaust (vitals that can't be overwritten outside from the status panel) then return nothing
    if($vitalThresholdDataFormatted[0] === 'Solar Panel' || $vitalThresholdDataFormatted[0] === 'Exhaust') { return ''; } 
    if (end($vitalThresholdDataFormatted) !== $GLOBALS['currentRaspberryPi']) { //end($vitalThresholdDataFormatted) will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
        $GLOBALS['currentRaspberryPi'] = end($vitalThresholdDataFormatted);
        
        if($GLOBALS['initalRaspberryPi'] === false){ $vitalThresholdControlPanel .= '</table>'; } //Closes the table from the previous RaspberryPi
        else { $GLOBALS['initalRaspberryPi'] == false; } //Initial table will change this to false after it creates the first table header

        $vitalThresholdControlPanel .= '<legend>Raspberry Pi - ' . $GLOBALS['currentRaspberryPi'] . '</legend><table><tr><th>Vital Name</th><th>Vital Lower</th><th>Vital Upper</th></tr>';
    }

    //Generate Panel
    $vitalThresholdControlPanel .= '<tr>';
    $vitalThresholdControlPanel .= generateThresholdOptions($vitalThresholdDataFormatted[0], $vitalThresholdDataFormatted[1], $vitalThresholdDataFormatted[2], $GLOBALS['currentRaspberryPi']); //[0] Vital Name; [1] Vital Lower Threshold; [2] Vital Upper Threshold  $GLOBALS['currentRaspberryPi'] Raspberry Pi ID
    $vitalThresholdControlPanel .= '</tr>';

    //Return HTML for the current row OR current row including the closure of the previous row
    return $vitalThresholdControlPanel;
}

/* Database Queries */
$currentUser = $_SESSION['username']; //Get Current User Name
$sqlVitalsCurrentStatus = 'SELECT VN, VV, RPID FROM status NATURAL JOIN vitals WHERE USR="' . $currentUser . '";'; //Select current status of 
$sqlVitalsCurrentThresholds = 'SELECT VN, VL, VU, RPID FROM vitals WHERE USR="' . $currentUser . '";'; //Select vital settings (lower & upper limits) to display and allow user to change those vital settings

//Execute Queries
$resultCurrentStatus = mysqli_query($conn, $sqlVitalsCurrentStatus);
$resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);

//Store CurrentStatus Query Results into $arrayCurrentStatus
$arrayCurrentStatus = array(); 
if(mysqli_num_rows($resultCurrentStatus) > 0) {
    while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
        $tempRow = '[' . $row['VN'] . ',' . $row['VV'] . ',' . $row['RPID'] . ']';
        $arrayCurrentStatus[] = $tempRow;
    }
}

//Store Current Tresholds Query Results into $arrayCurrentThreshold
$arrayCurrentThreshold = array(); 
if(mysqli_num_rows($resultCurrentThresholds) > 0) {
    while($row = mysqli_fetch_assoc($resultCurrentThresholds)) {
        $tempRow = '[' . $row['VN'] . ',' . $row['VL']. ',' . $row['VU'] . ',' . $row['RPID'] . ']';
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

        <!-- Send User Back to Client Page -->
        <div>
            <form action="client.php" method="post">
                <input type="submit" value="Back to Client Page">
            </form>
        </div>

        <!-- Vital Status/Threshold Control Panels -->
        <div>
            <form action="vitals_files/vitalscontrol.php" method="post">
                <fieldset><legend>Vital Status Control Panel:</legend>
                    <?php 
                        echo '<fieldset>';
                        foreach ($arrayCurrentStatus as $aCS) { echo generateVitalStatusControlPanel($aCS); } //Generate Status Control Panel
                        echo '</table></fieldset>';
                        $GLOBALS['currentRaspberryPi'] = '-1'; //Reset currentRaspberryPi 'Counter'
                        $GLOBALS['initalRaspberryPi'] = true; //Reset currentRaspberryPi 'Counter'
                    ?>
                    <input type="submit" value="Commit Any Changes">
                </fieldset>
            </form>
        </div>
        <div>
            <form action="vitals_files/vitalsthreshold.php" method="post">
                <fieldset><legend>Vital Threshold Control Panel:</legend>
                    <?php
                        echo '<fieldset>';
                        foreach ($arrayCurrentThreshold as $aCT) { echo generateVitalThresholdControlPanel($aCT); } //Generate Threshold Control Panel
                        echo '</table></fieldset>';
                        $GLOBALS['currentRaspberryPi'] = '-1'; //Reset currentRaspberryPi 'Counter'
                        $GLOBALS['initalRaspberryPi'] = true; //Reset currentRaspberryPi 'Counter'
                    ?>
                    <input type="submit" value="Commit Any Changes">
                </fieldset>
            </form>
        </div>
    </body>
</html>