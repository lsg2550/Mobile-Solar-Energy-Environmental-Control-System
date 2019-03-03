<?php
//Require
require "../../index_files/sessionstart.php";
require "../../index_files/sessioncheck.php";
require "../../index_files/operations.php";
require "../../index_files/connect.php";

//Session
$currentUser = $_SESSION["username"]; //Current User

//POST
$vitals = [isset($_POST["vital1"]) ? $_POST["vital1"] : '', isset($_POST["vital2"]) ? $_POST["vital2"] : '', isset($_POST["vital3"]) ? $_POST["vital3"] : '', isset($_POST["vital4"]) ? $_POST["vital4"] : '', isset($_POST["vital5"]) ? $_POST["vital5"] : '']; //Vitals the user selected to view
$dateStart = $_POST["date_start"]; //Starting date of logs/timestamps
$dateEnd = $_POST["date_end"]; //Ending date of logs/timestamps
$timeStart = date("H:i:s", strtotime($_POST["time_start"])); //Starting time of logs/timestamps
$timeEnd = date("H:i:s", strtotime($_POST["time_end"])); //Ending time of logs/timestamps
$timeInterval = $_POST["time_interval"]; //TBD
$rpi = $_POST["rpi_select"]; //The RaspberryPi the user selected
$chartOrCSV = !isset($_POST["formaction"]) ? "chart" : $_POST["formaction"]; //if formaction is not set, we let it be "chart" by default (assuming this is just the page wanting charts on load, otherwise let chartorcsv be whatever the user requested

if (array_filter($vitals) == empty($vitals)) { outputError("Please select atleast one vital to display!"); } //If the user selected no vitals then tell them to select atleast one vital

//Get the correct vital name
$arrayVitals = array();
foreach ($vitals as $var) {
    switch ($var) {
        case 'battery':
            $arrayVitals[] = ["BatteryVoltage"];
            $arrayVitals[] = ["BatteryCurrent"];
            break;
        case 'solar':
            $arrayVitals[] = ["SolarPanelVoltage"];
            $arrayVitals[] = ["SolarPanelCurrent"];
            break;
        case 'temperature':
            $arrayVitals[] = ["TemperatureInner"];
            $arrayVitals[] = ["TemperatureOuter"];
            break;
        case 'humidity':
            $arrayVitals[] = ["HumidityInner"];
            $arrayVitals[] = ["HumidityOuter"];
            break;
        case 'clarity':
            $arrayVitals[] = ["Clarity"];
            break;
        case 'exhaust':
            $arrayVitals[] = ["Exhaust"];
            break;
        default:
            break;
    }
}

//Database Queries
$arrayLogVitalName = array(); //This array will contain all the vital names
$arrayLogVital = array(); //This array will contain all the vital values during the given timestamps as before
$arrayLogTS = array(); //This array will contain all the timestamps according to the dateStart/End, timeStart/End, and timeInterval
foreach ($arrayVitals as $rowidx => $columnidx) {
    $sqlLog = "SELECT V.VN, V1, DATE(TS) as DStamp, TIME(TS) as TStamp FROM log AS l NATURAL JOIN vitals AS V
        WHERE l.USR='{$currentUser}'
        AND l.RPID='{$rpi}'
        AND TYP='ST'
        AND V.VN='{$arrayVitals[$rowidx][0]}'
        AND TS BETWEEN '{$dateStart}' AND '{$dateEnd}'
        ORDER BY TS ASC;";
    $resultLog = mysqli_query($conn, $sqlLog);
    if (!$resultLog || mysqli_num_rows($resultLog) == 0) { continue; } //If resultlog returned an error or no rows, continue to the next vital

    $tempLogVital = array(); //This array will contain all the vital values during the given timestamps as before
    $tempLogTS = array(); //This array will contain all the timestamps according to the dateStart/End, timeStart/End, and timeInterval
    while ($row = mysqli_fetch_assoc($resultLog)) {
        $vitalTS = new DateTime($row['DStamp'] . $row['TStamp'], new DateTimeZone("America/Chicago"));
        $timeFromVitalTS = date("H:i:s", strtotime($vitalTS->format('Y-m-d H:i:s'))); //Convert $vitalTS (datetime object) to a date object extracting time - for comparison against $timestart and $timeend
        if ($timeFromVitalTS >= $timeStart && $timeFromVitalTS <= $timeEnd) { 
            $arrayLogVitalName[] = $row['VN'];
            $tempLogVital[] = $row['V1'];
            $tempLogTS[] = $vitalTS->format('Y-m-d H:i:s'); 
        }
    }

    $arrayLogVital[] = $tempLogVital;
    $arrayLogTS[] = $tempLogTS;
}

if (empty($arrayLogTS) || empty($arrayLogVital) || empty($arrayLogVitalName)) { outputError("Sorry! No data was found for the corresponding date and time!"); } //If nothing was found from the DB, then tell the user that nothing was found

//Output
if ($chartOrCSV == "chart") {  
    outputCharts($arrayLogVitalName, $arrayLogVital, $arrayLogTS);
} else if ($chartOrCSV == "csv") { 
    outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS);
}

function outputCharts($arrayLogVitalName, $arrayLogVital, $arrayLogTS) {
    //POST
    $interpolate = isset($_POST["interpolate_data"]) ? $_POST["interpolate_data"] : 'no';

    //Clean-up vital names
    $arrayLogVitalNameUnique = array_unique($arrayLogVitalName);
    foreach ($arrayLogVitalNameUnique as $rowIdx => $rowValue) {
        switch ($arrayLogVitalNameUnique[$rowIdx]) {
            case 'BatteryVoltage':
                $arrayLogVitalNameUnique[$rowIdx] = "Battery Voltage";
                break;
            case 'BatteryCurrent':
                $arrayLogVitalNameUnique[$rowIdx] = "Battery Current";
                break;
            case 'SolarPanelVoltage':
                $arrayLogVitalNameUnique[$rowIdx] = "PV Voltage";
                break;
            case 'SolarPanelCurrent':
                $arrayLogVitalNameUnique[$rowIdx] = "PV Current";
                break;
            case 'TemperatureInner':
                $arrayLogVitalNameUnique[$rowIdx] = "Inside Temperature";
                break;
            case 'TemperatureOuter':
                $arrayLogVitalNameUnique[$rowIdx] = "Outside Temperature";
                break;
            case 'HumidityInner':
                $arrayLogVitalNameUnique[$rowIdx] = "Inside Humidity";
                break;
            case 'HumidityOuter':
                $arrayLogVitalNameUnique[$rowIdx] = "Outside Humidity";
                break;
            default:
                break;
        }
    }
    $arrayLogVitalNameUnique = array_values($arrayLogVitalNameUnique);

    //Processes data
    $optimalTemperatureRatio = array();
    foreach ($arrayLogVital as $rowIdx => $rowArray) {
        $optimalCounter = 0;

        foreach ($rowArray as $innerRowIdx => $innerValue) {
            //For Temperature I/O and Humidity I/O - Calculating sensor success ratio
            if ($rowArray[$innerRowIdx] == "NULL") {
                $rowArray[$innerRowIdx] = ($interpolate == "no") ? null : isset($rowArray[$innerRowIdx - 1]) ? $rowArray[$innerRowIdx - 1] : null;
                $optimalCounter++;
            }

            //For Exhaust - Converting On/Off to 1/0
            if($arrayLogVitalName[$innerRowIdx] == "Exhaust"){ 
                $rowArray[$innerRowIdx] = ($rowArray[$innerRowIdx] == "on") ? 1 : 0; 
            }
        }

        $optimalTemperatureRatio[] = ((count($rowArray) - $optimalCounter) / count($rowArray)) != 1 ? [$arrayLogVitalNameUnique[$rowIdx] => ((count($rowArray) - $optimalCounter) / count($rowArray))] : doNothing();
        $arrayLogVital[$rowIdx] = $rowArray;
    }

    //Optimal temperature/humidity ratio processing - convert optimaltemperatureratio to a 1D array for better client-side (javascript/jquery) processing - (no nested for-loops, let the server handle that)
    $optimalTemperatureRatio = convert2DArrayto1DArray(array_values(array_filter($optimalTemperatureRatio)));

    // style='width: content-box;' style='width:content-box;'
    echo "<canvas class='charts-canvas' id='primary-chart'></canvas>";
    echo "<script>createchart('primary-chart', 'line'," . json_encode($arrayLogTS[0]) . "," . json_encode(array_values($arrayLogVitalNameUnique)) . "," . json_encode($arrayLogVital) . "," . count(array_values($arrayLogVitalNameUnique)) . ")</script>";
    outputSensorSuccessRatio($optimalTemperatureRatio);
}

function outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS) {
    //global
    global $currentUser;

    //Pre-process arrays, again
    $arrayLogVitalName = array_values($arrayLogVitalName);
    $arrayLogVital = array_values(convert2DArrayto1DArray($arrayLogVital, TRUE));
    $arrayLogTS = array_values(convert2DArrayto1DArray($arrayLogTS, TRUE));

    //Create CSV
    $csvServerRoot = $_SERVER['DOCUMENT_ROOT'];
    $csvFolderName = "/clientcsv/";
    $csvFileName = substr(hash("md5", $currentUser), 0, 8) . "_logs.csv";
    $csvFile = fopen($csvServerRoot . $csvFolderName . $csvFileName, "w");
    for ($i=0; $i < count($arrayLogVitalName); $i++) { 
        $csvLine = [$arrayLogVitalName[$i], $arrayLogTS[$i], $arrayLogVital[$i]];
        fputcsv($csvFile, $csvLine);
    }
    fclose($csvFile);

    //jQuery will then grab the file using its header function
    echo $csvFolderName . $csvFileName;
}

function outputSensorSuccessRatio($optimalTemperatureRatio) {
    if (!isset($optimalTemperatureRatio) || empty(array_filter($optimalTemperatureRatio))) {
        echo "<script>updateSensorSuccessRate(" . -1 . ")</script>";
    } else {
        echo "<script>updateSensorSuccessRate(" . json_encode($optimalTemperatureRatio) . ")</script>";
    }
}

function outputError($message){
    echo "<h2 style='display: inline-block; text-align: center;'>{$message}</h2>";
    outputSensorSuccessRatio(NULL);
    exit();
}
?>