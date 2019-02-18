<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");

    //Session
    $currentUser = $_SESSION["username"]; //Current User

    //POST
    $vital = $_POST["vital_select"];
    $dateTimeStart = new DateTime($_POST["datetime_start"], new DateTimeZone("America/Chicago"));
    $dateTimeEnd = new DateTime($_POST["datetime_end"], new DateTimeZone("America/Chicago"));
    $timeInterval = $_POST["time_interval"];
    $rpi = $_POST["rpi_select"];

    //Get the correct vital name
    $vitalOne = null; //If battery is selected, vitalOne will be batteryvoltage; temperature will be inner; so on so forth following this convention
    $vitalTwo = null; //If battery is selected, vitalTwo will be batterycurrent; temperature will be outer; so on so forth following this convention
    switch ($vital) {
        case 'battery':
            $vitalOne = "BatteryVoltage";
            $vitalTwo = "BatteryCurrent";
            break;
        case 'solar':
            $vitalOne = "SolarPanelVoltage";
            $vitalTwo = "SolarPanelCurrent";
            break;
        case 'temperature':
            $vitalOne = "TemperatureInner";
            $vitalTwo = "TemperatureOuter";
            break;
        case 'humidity':
            $vitalOne = "HumidityInner";
            $vitalTwo = "HumidityOuter";
            break;
        case 'clarity':
            $vitalOne = "Clarity";
            break;
        case 'exhaust':
            $vitalOne = "Exhaust";
            break;
        default:
            echo "Error: Vital was not found!";
            return;
    }

    //Database Queries
    $sqlLog = "SELECT V.VN, V1, TS FROM log AS l NATURAL JOIN vitals AS V WHERE l.USR='{$currentUser}' AND l.RPID='{$rpi}' AND (V.VN='{$vitalOne}' OR V.VN='{$vitalTwo}') ORDER BY l.TS DESC;"; //Select all logs related to the current user & 
    $resultLog = mysqli_query($conn, $sqlLog);

    //Store Log Query Results into $arrayLogs respective to their vital names
    $arrayLogVitalOne = array(); //This array will contain all the vital values during the given timestamps as before
    $arrayLogVitalTwo = array(); //This array will contain all the vital values during the given timestamps as before
    $arrayLogTS = array(); //This array will contain all the timestamps according to the dateStart/End, timeStart/End, and timeInterval
    if(mysqli_num_rows($resultLog) > 0) { 
        while($row = mysqli_fetch_assoc($resultLog)) {
            $vitalName = $row['VN'];
            $vitalValue = $row['V1'];
            $vitalTS = new DateTime($row['TS'], new DateTimeZone("America/Chicago"));
            $tempVital = $vitalValue;
            $arrayLogTS[] = $vitalTS->format('Y-m-d H:i:s');

            if($vitalName == $vitalOne) { $arrayLogVitalOne[] = $tempVital; } 
            else { $arrayLogVitalTwo[] = $tempVital; }
        }
    }
    
    //echo $vital . "  " . $dateTimeStart->format('Y-m-d H:i:s') . "  " . $dateTimeEnd->format('Y-m-d H:i:s') . "  " . $timeInterval . "  " . $rpi;

    //Output
    echo "<canvas class='charts-canvas' id='primary-chart'></canvas>";
    echo "<canvas class='charts-canvas' id='secondary-chart'></canvas>";
    echo "<script>createchart('primary-chart', 'line', '{$vitalOne}'," .  json_encode($arrayLogTS) .",". json_encode($arrayLogVitalOne) . ")</script>";
    echo "<script>createchart('secondary-chart', 'line', '{$vitalTwo}'," .  json_encode($arrayLogTS) .",". json_encode($arrayLogVitalTwo) . ")</script>";
?>