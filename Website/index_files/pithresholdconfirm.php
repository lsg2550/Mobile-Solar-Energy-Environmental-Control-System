<?php
    /**
     * Name: pithresholdconfirm.php
     * Description: This script is called by the Raspberry Pi, where the RPi requests for a new/updated JSON file
     * with new/updated thresholds for the ESSI sensors.
     */

    try {
        // Require
        require("connect.php");

        // Initialize variables
        $RASPBERRY_PI_THRESHOLD_DIRECTORY = "../../rpixmlsthresholds/";
        $RASPBERRY_PI_ID = $_GET["rpid"]; //TODO: Filter rpid

        // Generate threshold dictionary
        $thresholdDictionary = new stdClass();

        // Query database for the RPi's thresholds
        $sqlSelectRPiThresholds = "SELECT vn, vl, vu FROM vitals WHERE rpid='{$RASPBERRY_PI_ID}';";
        $resultSelectRPiThresholds = mysqli_query($conn, $sqlSelectRPiThresholds);

        // From results, get the thresholds of every vital and store them into the threshold dictionary
        while($vital = mysqli_fetch_assoc($resultSelectRPiThresholds)) {
            switch($vital["vn"]) {
                case "BatteryVoltage":
                    $thresholdDictionary->voltagelower = $vital["vl"];
                    $thresholdDictionary->voltageupper = $vital["vu"];
                    break;
                case "BatteryCurrent":
                    $thresholdDictionary->currentlower = $vital["vl"];
                    $thresholdDictionary->currentupper = $vital["vu"];
                    break;
                case "SolarPanelVoltage":
                    $thresholdDictionary->spvoltagelower = $vital["vl"];
                    $thresholdDictionary->spvoltageupper = $vital["vu"];
                    break;
                case "SolarPanelCurrent":
                    $thresholdDictionary->spcurrentlower = $vital["vl"];
                    $thresholdDictionary->spcurrentupper = $vital["vu"];
                    break;
                case "ChargeControllerCurrent":
                    $thresholdDictionary->cccurrentlower = $vital["vl"];
                    $thresholdDictionary->cccurrentupper = $vital["vu"];
                    break;
                case "TemperatureInner":
                    $thresholdDictionary->temperatureinnerlower = $vital["vl"];
                    $thresholdDictionary->temperatureinnerupper = $vital["vu"];
                    break;
                case "TemperatureOuter":
                    $thresholdDictionary->temperatureouterlower = $vital["vl"];
                    $thresholdDictionary->temperatureouterupper = $vital["vu"];
                    break;                
                case "HumidityInner":
                    $thresholdDictionary->humidityinnerlower = $vital["vl"];
                    $thresholdDictionary->humidityinnerupper = $vital["vu"];
                    break;          
                case "HumidityOuter":
                    $thresholdDictionary->humidityouterlower = $vital["vl"];
                    $thresholdDictionary->humidityouterupper = $vital["vu"];
                    break;
                default:
                    break;
            }
        }

        // Create a file for the thresholds and write the dictionary to it
        $thresholdFile = fopen("{$RASPBERRY_PI_THRESHOLD_DIRECTORY}{$RASPBERRY_PI_ID}.json", "w");
        fwrite($thresholdFile, json_encode($thresholdDictionary));
        fclose($thresholdFile);

        // Return an OK
        echo "OK";
    } catch (Exception $e) {
        //echo $e;

        // Return a NO
        echo "NO";
    }
?>