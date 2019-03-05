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
$rpi = $_POST["rpi_select"]; //The RaspberryPi the user selected
$chartOrCSV = !isset($_POST["formaction"]) ? "chart" : $_POST["formaction"]; //if formaction is not set, we let it be "chart" by default (assuming this is just the page wanting charts on load, otherwise let chartorcsv be whatever the user requested

if (array_filter($vitals) == empty($vitals)) { outputError("Please select atleast one vital to display!"); } //If the user selected no vitals then tell them to select atleast one vital

//Get the correct vital name
$arrayVitals = array();
foreach ($vitals as $var) {
    switch ($var) {
        case 'battery':
            $arrayVitals["BatteryVoltage"] = "BatteryVoltage";
            $arrayVitals["BatteryCurrent"] = "BatteryCurrent";
            break;
        case 'solar':
            $arrayVitals["SolarPanelVoltage"] = "SolarPanelVoltage";
            $arrayVitals["SolarPanelCurrent"] = "SolarPanelCurrent";
            break;
        case 'temperature':
            $arrayVitals["TemperatureInner"] = "TemperatureInner";
            $arrayVitals["TemperatureOuter"] = "TemperatureOuter";
            break;
        case 'humidity':
            $arrayVitals["HumidityInner"] = "HumidityInner";
            $arrayVitals["HumidityOuter"] = "HumidityOuter";
            break;
        case 'clarity':
            $arrayVitals["Clarity"] = "Clarity";
            break;
        case 'exhaust':
            $arrayVitals["Exhaust"] = "Exhaust";
            break;
        default:
            break;
    }
}
// debug($arrayVitals); //Debug

//Database Queries
$arrayLogVitals = array(); //This array will contain the vital names as keys that point to an array of the vital name's values
$arrayLogTS = array(); //This array will contain all the timestamps according to the dateStart/End, timeStart/End, and timeInterval

foreach ($arrayVitals as $vitalname) {
    $sqlLog = "SELECT V.VN, V1, DATE(TS) as DStamp, TIME(TS) as TStamp FROM log AS l NATURAL JOIN vitals AS V
        WHERE l.USR='{$currentUser}'
        AND l.RPID='{$rpi}'
        AND TYP='ST'
        AND V.VN='{$vitalname}'
        AND TS BETWEEN '{$dateStart}' AND '{$dateEnd}'
        ORDER BY TS ASC;";
    $resultLog = mysqli_query($conn, $sqlLog);

    if (!$resultLog || mysqli_num_rows($resultLog) == 0) { continue; } //If resultlog returned an error or no rows, continue to the next vital

    while ($row = mysqli_fetch_assoc($resultLog)) {
        $vitalTS = new DateTime($row['DStamp'] . $row['TStamp'], new DateTimeZone("America/Chicago"));
        $timeFromVitalTS = date("H:i:s", strtotime($vitalTS->format('Y-m-d H:i:s'))); //Convert $vitalTS (datetime object) to a date object extracting time - for comparison against $timestart and $timeend
        if ($timeFromVitalTS >= $timeStart && $timeFromVitalTS <= $timeEnd) { 
            $arrayLogVitals[$row['VN']][] = $row['V1'];
            $arrayLogTS[$row['VN']][] = $vitalTS->format('Y-m-d H:i:s');
        }
    }
}

//Debug
// debug($arrayLogVitals);
// debug($arrayLogTS);

if (empty($arrayLogTS) && empty($arrayLogVital) && empty($arrayLogVitalName)) { outputError("Sorry! No data was found for the corresponding date and time!"); } //If nothing was found from the DB, then tell the user that nothing was found

//Output
if ($chartOrCSV == "chart") {  
    outputCharts($arrayLogVitals, $arrayLogTS);
} else if ($chartOrCSV == "csv") { 
    outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS);
}

function outputCharts($arrayLogVitals, $arrayLogTS) {
    //POST
    $interpolate = isset($_POST["interpolate_data"]) ? TRUE : FALSE;
    $timeInterval = $_POST["time_interval"]; //Determines step-size in chart creation

    //Pre-process Data
    $arrayLogTSUnix = convertDateTimeToTimeStamp($arrayLogTS);
    // debug($arrayLogTSUnix); //Debug

    //Clean-up vital names
    foreach ($arrayLogVitals as $vitalName => $vitalValueArr) {
        switch($vitalName){
            case 'BatteryVoltage':
                $arrayLogVitals["Battery Voltage"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'BatteryCurrent':
                $arrayLogVitals["Battery Current"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'SolarPanelVoltage':
                $arrayLogVitals["PV Voltage"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'SolarPanelCurrent':
                $arrayLogVitals["PV Current"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'TemperatureInner':
                $arrayLogVitals["Inside Temperature"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'TemperatureOuter':
                $arrayLogVitals["Outside Temperature"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'HumidityInner':
                $arrayLogVitals["Inside Humidity"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'HumidityOuter':
                $arrayLogVitals["Outside Humidity"] = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                break;
            case 'Exhaust':
                $temporaryExhaust = $arrayLogVitals[$vitalName];
                unset($arrayLogVitals[$vitalName]);
                $arrayLogVitals["Exhaust"] = $temporaryExhaust;
                break;
            default:
                break;
        }
    }
    // debug($arrayLogVitals); //Debug

    //Fix data
    $optimalTemperatureRatio = array("InnerSensor" => 0, "OuterSensor" => 0);
    foreach ($arrayLogVitals as $vitalName => &$vitalValueArr) {
        if ($interpolate) {
            if ($vitalName == "Inside Temperature" || $vitalName == "Inside Humidity") {
                foreach ($vitalValueArr as $vitalValueIdx => &$vitalValue) {
                    if (strtolower($vitalValue) == "null" || !isset($vitalValue)) {
                        $vitalValue = (isset($vitalValueArr[$vitalValueIdx - 1])) ? $vitalValueArr[$vitalValueIdx - 1] : $vitalValueArr[$vitalValueIdx + 1];
                        $optimalTemperatureRatio["InnerSensor"]++;
                    }
                }
            }

            if ($vitalName == "Outside Temperature" || $vitalName == "Outside Humidity") {
                foreach ($vitalValueArr as $vitalValueIdx => &$vitalValue) {
                    if (strtolower($vitalValue) == "null" || !isset($vitalValue)) {
                        $vitalValue = (isset($vitalValueArr[$vitalValueIdx - 1])) ? $vitalValueArr[$vitalValueIdx - 1] : $vitalValueArr[$vitalValueIdx + 1];
                        $optimalTemperatureRatio["OuterSensor"]++;
                    }
                }
            }
        }

        if ($vitalName == "Exhaust") {
            foreach ($vitalValueArr as &$vitalValue) {
                $vitalValue = (strtolower($vitalValue) == "off") ? 0 : 1; //Off = 0; On = 1;
            }
        }
    }
    // debug($arrayLogVitals); //Debug

    //Calculate Inside and Outside Sensor Successful Read Ratio
    $innerKeyCount = count($arrayLogVitals["Inside Temperature"]) + count($arrayLogVitals["Inside Humidity"]);
    $outerKeyCount = count($arrayLogVitals["Outside Temperature"]) + count($arrayLogVitals["Outside Humidity"]);
    $optimalTemperatureRatio = [ "InnerSensor" => (($innerKeyCount - $optimalTemperatureRatio["InnerSensor"]) / $innerKeyCount),
                                 "OuterSensor" => (($outerKeyCount - $optimalTemperatureRatio["OuterSensor"]) / $outerKeyCount) ];
    // debug($optimalTemperatureRatio); //Debug

    echo "<canvas class='charts-canvas' id='primary-chart'></canvas>";
    echo "<script>createchart('primary-chart', 'line'," . json_encode(array_values($arrayLogTSUnix)[0]) . "," . json_encode(array_keys($arrayLogVitals)) . "," . json_encode(array_values($arrayLogVitals)) . "," . count(array_keys($arrayLogVitals)) . "," . $timeInterval . ")</script>";
    outputSensorSuccessRatio($optimalTemperatureRatio);
}

function outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS) {
    //global
    global $currentUser;

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

function convertDateTimeToTimeStamp($arrOrSingleTimeStamp) {
    $convertedDateTimeArray = array();

    foreach ($arrOrSingleTimeStamp as $vitalName => $vitalValueArr) {
        foreach ($vitalValueArr as $vitalValue) {
            $convertedDateTimeArray[$vitalName][] = date_timestamp_get(new DateTime($vitalValue, new DateTimeZone("America/Chicago"))) * 1000;
        }
    }

    return $convertedDateTimeArray;
}
?>