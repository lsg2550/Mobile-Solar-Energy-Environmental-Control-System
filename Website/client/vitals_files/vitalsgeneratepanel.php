<?php
    /**
     * Description: This script generates the html to display the control panel on the vitals page.
     * 
     * Note: Just like client.php what the user has access to is hardcoded. You will need to change this to be more dynamic.
     * Also as noted in client.php and vitals.php the access table has read/write permissions. After you set them in the $_SESSION cookies you may
     * want to make those changes accordingly here.
     */

    //Require
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");

    //Database Queries
    $currentUID = (!empty($_SESSION['username_access'])) ? $_SESSION['username_access'] : $_SESSION['username']; //Get Current User Name
    $sqlVitalsCurrentThresholds = "SELECT vn, vl, vu, rpi.rpid FROM vitals CROSS JOIN rpi WHERE `uid-owner`='{$currentUID}' ORDER BY vn;"; //Select vital settings (lower & upper limits) to display and allow user to change those vital settings

    //Execute Queries
    $resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);
    if (!$resultCurrentThresholds || mysqli_num_rows($resultCurrentThresholds) == 0) { return; }

    //Store Current Tresholds Query Results into $arrayCurrentThreshold
    $arrayCurrentThreshold = array(); 
    if(mysqli_num_rows($resultCurrentThresholds) > 0) {
        while($row = mysqli_fetch_assoc($resultCurrentThresholds)) {
            $tempVitalName = $row['vn'];
            
            switch ($tempVitalName) {
                case "BatteryVoltage":
                    $tempVitalName = "Battery Voltage (V)";
                    break;
                case "BatteryCurrent":
                    $tempVitalName = "Battery Current (A)";
                    break;
                case "SolarPanelVoltage":
                    $tempVitalName = "PV Voltage (V)";
                    break;
                case "SolarPanelCurrent":
                    $tempVitalName = "PV Current (A)";
                    break;
                case "ChargeControllerCurrent":
                    $tempVitalName = "Charge Controller Current (A)";
                    break;
                case "TemperatureInner":
                    $tempVitalName = "Inside Temperature (C)";
                    break;
                case "TemperatureOuter":
                    $tempVitalName = "Outside Temperature (C)";
                    break;
                case "HumidityInner":
                    $tempVitalName = "Inside Humidity (g/m3)";
                    break;
                case "HumidityOuter":
                    $tempVitalName = "Outside Humidity (g/m3)";
                    break;
                default:
                    $tempVitalName = "SKIP";
                    break;
            }

            if($tempVitalName !== "SKIP") {
                $tempRow = "[ {$tempVitalName}, {$row['vl']}, {$row['vu']}, {$row['rpid']}, {$row['vn']} ]"; //Last index is used to store the original name for form processing purposes (see generateVitalTHresholdControlPanel)
                $arrayCurrentThreshold[] = $tempRow;
            }
        }
    }

    //generateVitalsThresholdControlPanel - Generates an HTML threshold panel where the user will define thresholds for the vitals to follow (e.g default battery VL is 12.6v, the user can change this to 12.0v)
    function generateVitalThresholdControlPanel($vitalThresholdDataArr) {
        $initalRaspberryPi = true;
        $currentRaspberryPi = splitDataIntoArray($vitalThresholdDataArr[0]);
        $vitalThresholdControlPanel = "<fieldset class='rpi-fieldset'>"; //Initialize Vital Threshold Control Panel HTML

        foreach ($vitalThresholdDataArr as $vitalThresholdData) {
            $vitalThresholdDataFormatted = splitDataIntoArray($vitalThresholdData);
            $tempRaspberryPi = $vitalThresholdDataFormatted[3];

            if ($tempRaspberryPi !== $currentRaspberryPi) { //end($vitalThresholdDataFormatted) will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
                $currentRaspberryPi = $tempRaspberryPi;
                
                if($initalRaspberryPi === false){ $vitalThresholdControlPanel .= "</table></fieldset><fieldset class='rpi-fieldset'>"; } //Closes the table and fieldset from the previous RaspberryPi, then starts a new fieldset
                else { $initalRaspberryPi = false; } //Initial table will change this to false after it creates the first table header

                $vitalThresholdControlPanel .= "<legend>Raspberry Pi - {$currentRaspberryPi}</legend><table class='rpi-table'><tr><th>Vital Name</th><th>Vital Lower</th><th>Vital Upper</th></tr>";
            }

            //Generate Panel
            $vitalThresholdControlPanel .= "<tr>" 
            . "<td>{$vitalThresholdDataFormatted[0]}</td>"
            . "<td><input type='text' name='vitallower[]' value='{$vitalThresholdDataFormatted[1]}' required></td>"
            . "<td><input type='text' name='vitalupper[]' value='{$vitalThresholdDataFormatted[2]}' required></td>"
            . "<input type='hidden' name='vitalname[]' value='{$vitalThresholdDataFormatted[4]}'>"
            . "<input type='hidden' name='rpid[]' value='{$currentRaspberryPi}'>"
            . "</tr>"; //[0] Vital Name; [1] Vital Lower Threshold; [2] Vital Upper Threshold  $GLOBALS['currentRaspberryPi'] Raspberry Pi ID
        }
        
        //Return HTML for the current row OR current row including the closure of the previous rowGLOBALS
        $vitalThresholdControlPanel .= "</table></fieldset>";
        return $vitalThresholdControlPanel;
    }

    // This is checked if jQuery is what called this script
    if(isset($_POST["isJQ"]) && $_POST["isJQ"]) {  echo generateVitalThresholdControlPanel($arrayCurrentThreshold); }
?>