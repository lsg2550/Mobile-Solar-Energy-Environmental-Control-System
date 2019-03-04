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
debug($arrayVitals); //Debug

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

    $tempLogVital = array(); //This array will contain all the vital values during the given timestamps as before
    $tempLogTS = array(); //This array will contain all the timestamps according to the dateStart/End, timeStart/End, and timeInterval
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
debug($arrayLogVitals);
debug($arrayLogTS);

if (empty($arrayLogTS) && empty($arrayLogVital) && empty($arrayLogVitalName)) { outputError("Sorry! No data was found for the corresponding date and time!"); } //If nothing was found from the DB, then tell the user that nothing was found

//Output
if ($chartOrCSV == "chart") {  
    outputCharts($arrayLogVitals, $arrayLogTS);
} else if ($chartOrCSV == "csv") { 
    outputCSV($arrayLogVitalName, $arrayLogVital, $arrayLogTS);
}

function outputCharts($arrayLogVitals, $arrayLogTS) {
    //POST
    $interpolate = isset($_POST["interpolate_data"]) ? $_POST["interpolate_data"] : 'no';
    $timeInterval = $_POST["time_interval"]; //Determines step-size in chart creation

    //Pre-process Data
    $arrayLogTSUnix = convertDateTimeToTimeStamp($arrayLogTS);
    debug($arrayLogTSUnix); //Debug

    //Clean-up vital names
    foreach ($arrayLogVitals as $vitalName => $vitalValueArr) {
        switch($vitalName){
            case 'BatteryVoltage':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Battery Voltage"];
                break;
            case 'BatteryCurrent':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Battery Current"];
                break;
            case 'SolarPanelVoltage':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["PV Voltage"];
                break;
            case 'SolarPanelCurrent':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["PV Current"];
                break;
            case 'TemperatureInner':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Inside Temperature"];
                break;
            case 'TemperatureOuter':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Outside Temperature"];
                break;
            case 'HumidityInner':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Inside Humidity"];
                break;
            case 'HumidityOuter':
                $arrayLogVitals[$vitalName] = $arrayLogVitals["Outside Humidity"];
                break;
            default:
                break;
        }
    }
    debug($arrayLogVitals); //Debug
    return;


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

function debug($message){
    print_r("<br/><br/>");
    print_r($message);
    print_r("<br/><br/>");
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

    
function replace_key_function($array, $key1, $key2)
{
    $keys = array_keys($array);
    $index = array_search($key1, $keys);

    if ($index !== false) {
        $keys[$index] = $key2;
        $array = array_combine($keys, $array);
    }

    return $array;
}
    */
?>