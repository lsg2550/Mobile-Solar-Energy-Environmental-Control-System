<?php
//Require
require('../index_files/sessionstart.php');
require('../index_files/sessioncheck.php');
require('../index_files/connect.php');
require('../operations/operations.php');

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

        $vitalStatusControlPanel .= '<table><caption>Raspberry Pi - ' . $GLOBALS['currentRaspberryPi'] . '</caption><tr><th>Vital Name</th><th>Status</th></tr>';
    }

    //Generate Panel
    $vitalStatusControlPanel .= '<tr>';
    for ($i = 0; $i < count($vitalControlDataFormatted) - 1; $i++) {
        $vitalStatusControlPanel .= '<th>' . $vitalControlDataFormatted[$i] . '</th>';
    }
    $vitalStatusControlPanel .= '</tr>';

    //Return HTML for the current row OR current row including the closure of the previous row
    return $vitalStatusControlPanel;
}

//generateVitalsThresholdControlPanel - Generates an HTML threshold panel where the user will define thresholds for the vitals to follow (e.g default battery VL is 12.6v, the user can change this to 12.0v)
function generateVitalThresholdControlPanel($vitalThresholdData) {
    $vitalThresholdDataFormatted = splitDataIntoArray($vitalThresholdData);
    $vitalThresholdControlPanel = ''; //Initialize Vital Threshold Control Panel HTML

    if (end($vitalThresholdDataFormatted) !== $GLOBALS['currentRaspberryPi']) { //end($vitalThresholdDataFormatted) will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
        $GLOBALS['currentRaspberryPi'] = end($vitalThresholdDataFormatted);
        
        if($GLOBALS['initalRaspberryPi'] === false){ $vitalThresholdControlPanel .= '</table>'; } //Closes the table from the previous RaspberryPi
        else { $GLOBALS['initalRaspberryPi'] == false; } //Initial table will change this to false after it creates the first table header

        $vitalThresholdControlPanel .= '<table><caption>Raspberry Pi - ' . $GLOBALS['currentRaspberryPi'] . '</caption><tr><th>Vital Name</th><th>Vital Lower</th><th>Vital Upper</th></tr>';
    }

    //Generate Panel
    $vitalThresholdControlPanel .= '<tr>';
    for ($i = 0; $i < count($vitalThresholdDataFormatted) - 1; $i++) {
        $vitalThresholdControlPanel .= '<th>' . $vitalThresholdDataFormatted[$i] . '</th>';
    }
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
        <div>
            <form action="vitals_files/vitalscontrol.php" method="post">
                <fieldset><legend>Vital Status Control Panel:</legend>
                    <?php 
                        foreach ($arrayCurrentStatus as $aCS) { echo generateVitalStatusControlPanel($aCS); } //Generate Status Control Panel
                        echo '</table>';
                        $GLOBALS['currentRaspberryPi'] = '-1'; //Reset currentRaspberryPi 'Counter'
                        $GLOBALS['initalRaspberryPi'] = true; //Reset currentRaspberryPi 'Counter'
                    ?>
                </fieldset>
            </form>
        </div>
		
        <div>
            <form action="vitals_files/vitalsthreshold.php" method="post">
                <fieldset><legend>Vital Threshold Control Panel:</legend>
                    <?php
                        foreach ($arrayCurrentThreshold as $aCT) { echo generateVitalThresholdControlPanel($aCT); } //Generate Treshold Control Panel
                        echo '</table>';
                        $GLOBALS['currentRaspberryPi'] = '-1'; //Reset currentRaspberryPi 'Counter'
                        $GLOBALS['initalRaspberryPi'] = true; //Reset currentRaspberryPi 'Counter'
                    ?>
                </fieldset>
            </form>
        </div>
    </body>
</html>