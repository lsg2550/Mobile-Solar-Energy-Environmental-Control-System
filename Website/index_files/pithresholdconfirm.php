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
        $RASPBERRY_PI_THRESHOLD_DIRECTORY = "../../rpixmlthresholds/";
        $RASPBERRY_PI_ID = $_GET["rpid"]; //TODO: Filter rpid

        // Generate threshold dictionary
        $thresholdDictionary = new stdClass();

        // Query database for the RPi's thresholds
        $sqlSelectRPiThresholds = "SELECT VN, VL, VU FROM vitals WHERE RPID='{$RASPBERRY_PI_ID}';";
        $resultSelectRPiThresholds = mysqli_query($conn, $sqlSelectRPiThresholds);

        // From results, get the thresholds of every vital and store them into the threshold dictionary
        while($vital = mysqli_fetch_assoc($resultSelectRPiThresholds)) {
            switch($vital["VN"]) {
                case "BatteryVoltage":
                    $thresholdDictionary->voltagelower = $vital["VL"];
                    $thresholdDictionary->voltageupper = $vital["VU"];
                    break;
                case "BatteryCurrent":
                    $thresholdDictionary->currentlower = $vital["VL"];
                    $thresholdDictionary->currentupper = $vital["VU"];
                    break;
                case "SolarPanelVoltage":
                    $thresholdDictionary->spvoltagelower = $vital["VL"];
                    $thresholdDictionary->spvoltageupper = $vital["VU"];
                    break;
                case "SolarPanelCurrent":
                    $thresholdDictionary->spcurrentlower = $vital["VL"];
                    $thresholdDictionary->spcurrentupper = $vital["VU"];
                    break;
                case "ChargeControllerCurrent":
                    $thresholdDictionary->cccurrentlower = $vital["VL"];
                    $thresholdDictionary->cccurrentupper = $vital["VU"];
                    break;
                case "TemperatureInner":
                    $thresholdDictionary->temperatureinnerlower = $vital["VL"];
                    $thresholdDictionary->temperatureinnerupper = $vital["VU"];
                    break;
                case "TemperatureOuter":
                    $thresholdDictionary->temperatureouterlower = $vital["VL"];
                    $thresholdDictionary->temperatureouterupper = $vital["VU"];
                    break;                
                case "HumidityInner":
                    $thresholdDictionary->humidityinnerlower = $vital["VL"];
                    $thresholdDictionary->humidityinnerupper = $vital["VU"];
                    break;          
                case "HumidityOuter":
                    $thresholdDictionary->humidityouterlower = $vital["VL"];
                    $thresholdDictionary->humidityouterupper = $vital["VU"];
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