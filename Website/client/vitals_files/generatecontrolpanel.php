<?php
    //Initialize
    $initalRaspberryPi = true;
    $currentRaspberryPi = "-1"; 

    //generateVitalsThresholdControlPanel - Generates an HTML threshold panel where the user will define thresholds for the vitals to follow (e.g default battery VL is 12.6v, the user can change this to 12.0v)
    function generateVitalThresholdControlPanel($vitalThresholdData) {
        $vitalThresholdDataFormatted = splitDataIntoArray($vitalThresholdData);
        $vitalThresholdControlPanel = ""; //Initialize Vital Threshold Control Panel HTML
        global $currentRaspberryPi;
        global $initalRaspberryPi;

        //If the vital name is Solar Panel, Exhaust (vitals that can't be overwritten outside from the status panel) then return nothing
        if(trim($vitalThresholdDataFormatted[0]) === "SolarPanel" || trim($vitalThresholdDataFormatted[0]) === "Exhaust" || trim($vitalThresholdDataFormatted[0]) === "Photo" || trim($vitalThresholdDataFormatted[0]) === "GPS") { return ""; } 
        if (end($vitalThresholdDataFormatted) !== $currentRaspberryPi) { //end($vitalThresholdDataFormatted) will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
            $currentRaspberryPi = end($vitalThresholdDataFormatted);
            
            if($initalRaspberryPi === false){ $vitalThresholdControlPanel .= "</table></fieldset><fieldset>"; } //Closes the table and fieldset from the previous RaspberryPi, then starts a new fieldset
            else { $initalRaspberryPi = false; } //Initial table will change this to false after it creates the first table header

            $vitalThresholdControlPanel .= "<legend>Raspberry Pi - {$currentRaspberryPi}</legend><table><tr><th>Vital Name</th><th>Vital Lower</th><th>Vital Upper</th></tr>";
        }

        //Generate Panel
        $vitalThresholdControlPanel .= "<tr>" . generateThresholdOptions($vitalThresholdDataFormatted[0], $vitalThresholdDataFormatted[1], $vitalThresholdDataFormatted[2], $currentRaspberryPi) . "</tr>"; //[0] Vital Name; [1] Vital Lower Threshold; [2] Vital Upper Threshold  $GLOBALS['currentRaspberryPi'] Raspberry Pi ID

        //Return HTML for the current row OR current row including the closure of the previous rowGLOBALS
        return $vitalThresholdControlPanel;
    }

    //This will only be called for the vitals that are not controllable (overwritable), but whose thresholds are - e.g Battery, Temperature, Photo
    function generateThresholdOptions($vitalname, $vitallower, $vitalupper, $rpid) {
        //Initialize
        $selectHTML = "";
        
        //Populate threshold row START
        $selectHTML .= "<td>{$vitalname}</td>";
        $selectHTML .= "<td><input type='text' name='vitallower[]' value='{$vitallower}'></td>"; //Second Column - VLower Column
        $selectHTML .= "<td><input type='text' name='vitalupper[]' value='{$vitalupper}'></td>"; //Third Column - VUpper Column
        $selectHTML .= "<input type='hidden' name='vitalname[]' value='{$vitalname}'>";
        $selectHTML .= "<input type='hidden' name='rpid[]' value='{$rpid}'>";
        //Populate threshold row END
        
        return $selectHTML;
    }

    //Reset Globals
    function resetGlobals() {
        global $initalRaspberryPi;
        global $currentRaspberryPi;

        $initalRaspberryPi = true; //Reset currentRaspberryPi 'Counter'
        $currentRaspberryPi = "-1"; //Reset currentRaspberryPi 'Counter'
    }
?>