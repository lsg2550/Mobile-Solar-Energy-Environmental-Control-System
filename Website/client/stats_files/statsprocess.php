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

//For Chart
if($chartOrCSV == "chart") { 
    $arrayLogVitalforChart = array();
    $arrayLogTSforChart = array();
}

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
            $arrayLogVital[] = $row['V1'];
            $arrayLogTS[] = $vitalTS->format('Y-m-d H:i:s'); 

            //For Chart
            if($chartOrCSV == "chart") { 
                $tempLogVital[] = $row['V1'];
                $tempLogTS[] = $vitalTS->format('Y-m-d H:i:s'); 
            }
        }
    }

    //For Chart
    if($chartOrCSV == "chart") { 
        $arrayLogVitalforChart[] = $tempLogVital;
        $arrayLogTSforChart[] = $tempLogTS;
    }
}

if (empty($arrayLogTS) && empty($arrayLogVital) && empty($arrayLogVitalName)) { outputError("Sorry! No data was found for the corresponding date and time!"); } //If nothing was found from the DB, then tell the user that nothing was found

//Output
if ($chartOrCSV == "chart") {  
    outputCharts($arrayLogVitalName, $arrayLogVital, $arrayLogTS, $arrayLogVitalforChart, $arrayLogTSforChart);
} else if ($chartOrCSV == "csv") { 
    outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS);
}

function outputCharts($arrayLogVitalName, $arrayLogVital, $arrayLogTS, $arrayLogVitalforChart, $arrayLogTSforChart) {
    //POST
    $interpolate = isset($_POST["interpolate_data"]) ? $_POST["interpolate_data"] : 'no';
    $timeInterval = $_POST["time_interval"]; //Determines step-size in chart creation

    //Pre-process Data
    $arrayLogTSUnix = convertDateTimeToTimeStamp($arrayLogTS);

    //Clean-up vital names
    $arrayLogVitalNameCleanedUp = $arrayLogVitalName;
    foreach ($arrayLogVitalNameCleanedUp as $rowIdx => $rowValue) {
        switch ($arrayLogVitalNameCleanedUp[$rowIdx]) {
            case 'BatteryVoltage':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Battery Voltage";
                break;
            case 'BatteryCurrent':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Battery Current";
                break;
            case 'SolarPanelVoltage':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "PV Voltage";
                break;
            case 'SolarPanelCurrent':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "PV Current";
                break;
            case 'TemperatureInner':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Inside Temperature";
                break;
            case 'TemperatureOuter':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Outside Temperature";
                break;
            case 'HumidityInner':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Inside Humidity";
                break;
            case 'HumidityOuter':
                $arrayLogVitalNameCleanedUp[$rowIdx] = "Outside Humidity";
                break;
            default:
                break;
        }
    }
    $arrayLogVitalNameCleanedUp = array_values(array_unique($arrayLogVitalNameCleanedUp));

    //Fix data
    $optimalTemperatureRatio = array();
    foreach ($arrayLogVitalforChart as $rowIdx => $rowArray) {
        $optimalCounter = 0;
        foreach ($rowArray as $innerRowIdx => $innerValue) {
            //For Temperature I/O and Humidity I/O
            if ($rowArray[$innerRowIdx] == "NULL") {
                if ($interpolate == "no") {
                    $rowArray[$innerRowIdx] = "null";
                } else {
                    $rowArray[$innerRowIdx] = isset($rowArray[$innerRowIdx - 1]) ? $rowArray[$innerRowIdx - 1] : null;
                }
                $optimalCounter++;
            }
            //For Exhaust - Converting On/Off to 1/0
            if ($rowArray[$innerRowIdx] == "on") {
                $rowArray[$innerRowIdx] = 1;
            } else if ($rowArray[$innerRowIdx] == "off") {
                $rowArray[$innerRowIdx] = 0;
            }
        }
        $optimalTemperatureRatio[] = ((count($rowArray) - $optimalCounter)/count($rowArray)) != 1 ? [ $arrayLogVitalNameCleanedUp[$rowIdx] => ((count($rowArray) - $optimalCounter)/count($rowArray)) ] : doNothing();
        $arrayLogVital[$rowIdx] = $rowArray;
    }
    //Optimal temperature/humidity ratio processing 
    $optimalTemperatureRatio = convert2DArrayto1DArray(array_values(array_filter($optimalTemperatureRatio)));

    
    echo "<canvas class='charts-canvas' id='primary-chart'></canvas>";
    echo "<script>createchart('primary-chart', 'line'," . json_encode(array_values(array_unique($arrayLogTSUnix))) . "," . json_encode($arrayLogVitalNameCleanedUp) . "," . json_encode($arrayLogVital) . "," . count(array_values($arrayLogVitalNameCleanedUp)) . ")</script>";
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

    foreach ($arrOrSingleTimeStamp as $rowIdx => $rowValue) {
        $convertedDateTimeArray[] = date_timestamp_get(new DateTime($rowValue, new DateTimeZone("America/Chicago"))) * 1000;
    }

    return $convertedDateTimeArray;
}

function getKeyAmount($array, $keyToLookFor){
    $keyAmount = 0;

    foreach ($array as $key => $value) {
        if($value == $keyToLookFor) { $keyAmount++; }
    }

    return $keyAmount;
}




/*
//Processes data
    $optimalTemperatureRatio = array();
    $optimalInnerCounter = 0;
    $optimalOuterCounter = 0;
    $timeoutCount = 10;

    foreach ($arrayLogVital as $rowIdx => $rowValue) {
        if($arrayLogVitalName[$rowIdx] == "TemperatureInner") { //The sensor for temperature also checks for humidity, so whenever temperature can't be recorded, humidity can't be recorded either
            if($arrayLogVital[$rowIdx] == "NULL" && $interpolate != "no") {
                $gotValue = FALSE;

                for ($i=0; $i < $timeoutCount; $i++) {  //Check 5 steps back
                    if($arrayLogVitalName[$rowIdx - $i] == "TemperatureInner" && isset($arrayLogVital[$rowIdx - $i])) {
                        $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx - $i];
                        $gotValue = true;
                        break;
                    }
                }

                if($gotValue == FALSE) {
                    for ($i=0; $i < $timeoutCount; $i++) { //Check 5 steps forward
                        if($arrayLogVitalName[$rowIdx + $i] == "TemperatureInner" && isset($arrayLogVital[$rowIdx + $i])) {
                            $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx + $i];
                            $gotValue = true;
                            break;
                        }
                    }
                }

                if($gotValue == FALSE) {
                    print_r("THIRD ATTEMPT");
                    print_r($arrayLogVital[$rowIdx]);
                    print_r("<br/>");
                    $arrayLogVital[$rowIdx] = null;
                }

                $optimalInnerCounter++;
            } elseif ($arrayLogVital[$rowIdx] == "NULL" && $interpolate == "no") {
                $arrayLogVital[$rowIdx] = null;
            }
        }

        if($arrayLogVitalName[$rowIdx] == "TemperatureOuter") { //The sensor for temperature also checks for humidity, so whenever temperature can't be recorded, humidity can't be recorded either
            if($arrayLogVital[$rowIdx] == "NULL" && $interpolate != "no") {
                $gotValue = false;

                for ($i=0; $i < $timeoutCount; $i++) {  //Check 5 steps back
                    if($arrayLogVitalName[$rowIdx - $i] == "TemperatureOuter" && isset($arrayLogVital[$rowIdx - $i])) {
                        $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx - $i];
                        $gotValue = true;
                        break;
                    }
                }

                if(!$gotValue) {
                    for ($i=0; $i < $timeoutCount; $i++) { //Check 5 steps forward
                        if($arrayLogVitalName[$rowIdx + $i] == "TemperatureOuter" && isset($arrayLogVital[$rowIdx + $i])) {
                            $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx + $i];
                            $gotValue = true;
                            break;
                        }
                    }
                }

                if(!$gotValue) {
                    $arrayLogVital[$rowIdx] = null;
                }

                $optimalOuterCounter++;
            } elseif ($arrayLogVital[$rowIdx] == "NULL" && $interpolate == "no") {
                $arrayLogVital[$rowIdx] = null;
            }
        }

        if($arrayLogVitalName[$rowIdx] == "HumidityInner") { //The sensor for temperature also checks for humidity, so whenever temperature can't be recorded, humidity can't be recorded either
            if($arrayLogVital[$rowIdx] == "NULL" && $interpolate != "no") { 
                $gotValue = false;

                for ($i=0; $i < $timeoutCount; $i++) {  //Check 5 steps back
                    if($arrayLogVitalName[$rowIdx - $i] == "HumidityInner" && isset($arrayLogVital[$rowIdx - $i])) {
                        $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx - $i];
                        $gotValue = true;
                        break;
                    }
                }

                if(!$gotValue) {
                    for ($i=0; $i < $timeoutCount; $i++) { //Check 5 steps forward
                        if($arrayLogVitalName[$rowIdx + $i] == "HumidityInner" && isset($arrayLogVital[$rowIdx + $i])) {
                            $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx + $i];
                            $gotValue = true;
                            break;
                        }
                    }
                }

                if(!$gotValue){
                    $arrayLogVital[$rowIdx] = null;
                }
            } elseif ($arrayLogVital[$rowIdx] == "NULL" && $interpolate == "no") {
                $arrayLogVital[$rowIdx] = null;
            }
        }

        if($arrayLogVitalName[$rowIdx] == "HumidityOuter") { //The sensor for temperature also checks for humidity, so whenever temperature can't be recorded, humidity can't be recorded either
            if($arrayLogVital[$rowIdx] == "NULL" && $interpolate != "no") {
                $gotValue = false;

                for ($i=0; $i < $timeoutCount; $i++) {  //Check 5 steps back
                    if($arrayLogVitalName[$rowIdx - $i] == "HumidityOuter" && isset($arrayLogVital[$rowIdx - $i])) {
                        $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx - $i];
                        $gotValue = true;
                        break;
                    }
                }

                if(!$gotValue) {
                    for ($i=0; $i < $timeoutCount; $i++) { //Check 5 steps forward
                        if($arrayLogVitalName[$rowIdx + $i] == "HumidityOuter" && isset($arrayLogVital[$rowIdx + $i])) {
                            $arrayLogVital[$rowIdx] = $arrayLogVital[$rowIdx + $i];
                            $gotValue = true;
                            break;
                        }
                    }
                }

                if(!$gotValue) {
                    $arrayLogVital[$rowIdx] = null;
                }
            } elseif ($arrayLogVital[$rowIdx] == "NULL" && $interpolate == "no") {
                $arrayLogVital[$rowIdx] = null;
            }
        }

        if($arrayLogVitalName[$rowIdx] == "Exhaust") {
            $arrayLogVital[$rowIdx] = ($arrayLogVital[$rowIdx] == "on") ? 1 : 0;
        }
    }
    $innerKeyCount = getKeyAmount($arrayLogVitalName, "TemperatureInner");
    $outerKeyCount = getKeyAmount($arrayLogVitalName, "TemperatureOuter");
    $optimalTemperatureRatio = [ "InnerSensor" => ($innerKeyCount - $optimalInnerCounter) / $innerKeyCount, "OuterSensor" => ($outerKeyCount - $optimalOuterCounter) / $outerKeyCount ];

    echo "<script>createchart('primary-chart', 'line'," . json_encode($arrayLogTSUnix) . "," . json_encode($arrayLogVitalNameCleanedUp) . "," . json_encode($arrayLogVital) . "," . count($arrayLogVitalNameCleanedUp) . "," . $timeInterval . ")</script>";
*/
?>