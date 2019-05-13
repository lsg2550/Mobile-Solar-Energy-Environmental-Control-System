<?php
/**
 * Description: This script contains different functions that multiple scripts use on the CMS, they are separated by broad use to the most specific use. The comments on the functions 
 * states which scripts or functions use them.
 */

/**********************************************************************************
 * Code that can be used by most if not all functions * 
 *********************************************************************************/

// Transposes an array
function transposeArray($inputArray){
    return array_map(null, ...$inputArray);
}

/**********************************************************************************
 * Specific code for certain scripts.
 * These functions are used by multiple scripts 
 * and are placed here to avoid repeated code.
 *********************************************************************************/

// getData - Generate and format HTML tables to display CurrentStatus and Log results, respectively
function getData($stringsToReplace, $tableHeader, $printAll) {
    //Initialize
    $currentRaspberryPi = "-1"; 
    $initalRaspberryPi = true; 
    $displayHTML = ""; //Display HTML

    foreach ($stringsToReplace as $stringToReplace) {
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
    
        if ($stringSplit[2] !== $currentRaspberryPi) { //$stringSplit[2] will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
            $currentRaspberryPi = $stringSplit[2];
    
            if ($initalRaspberryPi == false) {$displayHTML .= "</table>";} //Closes the table from the previous RaspberryPi
            else { $initalRaspberryPi = false;} //Initial table will change this to false after it creates the first table header
    
            $displayHTML .= ($printAll) ? "<table id='log-table'>{$tableHeader}" : "<table id='current-status-table'>{$tableHeader}" ;
        }
    
        //Generate HTML currentstatus/log tables
        $displayHTML .= "<tr>";
        for ($i = 0; $i < count($stringSplit); $i++) {
            // if ($i === 2 && !$printAll) {continue;}
            $displayHTML .= "<td>{$stringSplit[$i]}</td>";
        }
        $displayHTML .= "</tr>";
    }

    //Return table(s)
    $displayHTML .= "</table>";
    return $displayHTML;
}

// getTimeStamp - Gets the timestamp of the current status to display it on the HTML page
function getTimeStamp($stringToReplace) {
    $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
    return end($stringSplit); //Return timestamp for currentstatus fieldset
}

// Converts a 2D array to 1D array, can be used in general.
// $forSensor is meant for shaping the array to fit chart.js arrays 
function convert2DArrayto1DArray($inputArray, $forSensor=false) {
    $outputArray = array();

    if ($forSensor) {
        $rows = count($inputArray);
        $columns = count($inputArray[0]);
        $size = $rows * $columns;

        for ($i = 0; $i < count($inputArray); $i++) {
            for ($j = 0; $j < count($inputArray[$i]); $j++) {
                $outputArray[$size * $i + $j] = $inputArray[$i][$j];
            }
        }
    } else {
        foreach (array_values($inputArray) as $key => $value) {
            foreach ($value as $innerkey => $innervalue) {
                $outputArray[$innerkey] = $innervalue;
            }
        }
    }

    return $outputArray;
}

// Remove extra characters and place data into an array, all data carried out in this website will follow the same data format
// e.g: Initial array '[data1, data2, data3]' will become 'array[0] = data1, array[1] = data2, array[2] = data3'
function splitDataIntoArray($DATA_ARRAY_AS_STRING) {
    $SQUARE_BRACKET_CHARACTER_ARRAY = array('[', ']');
    $BRACKETLESS_DATA_ARRAY = str_replace($SQUARE_BRACKET_CHARACTER_ARRAY, '', $DATA_ARRAY_AS_STRING); // Turns '[data1, data2]' string into 'data1, data2' string
    $DATA_ARRAY = preg_split("/[,]+/", $BRACKETLESS_DATA_ARRAY); // Turns 'data1, data2' string into 'array[0] = data1, array[1] = data2' array
    return $DATA_ARRAY; //Returns data array
}

// Not to be confused with getTimeStamp() which is for building HTML of certain pages
// This function will be used to extract the timestamp from the filenames used by clarity and motion detection
function getTimeStampFromFileName($fileName) {
    $tempArray = array();
    preg_match("/\[([0-9\-\s]+)\]/", $fileName, $tempArray);
    //echo print_r($tempArray);
    return $tempArray[1]; // Matches for groups within square brackets [ timestamp ]
}

function getClarityValFromFileName($fileName) {
    $tempArray = array();
    preg_match("/\[([0-9\.]+)\]/", $fileName, $tempArray);
    //echo print_r($tempArray);
    return $tempArray[1]; // Matches for groups within square brackets [ timestamp ]
}

/**********************************************************************************
 * Misc code I need for debugging * 
 *********************************************************************************/

// Takes a string as input and prints it out on the page with line breaks. 
// Used mostly to debug SQL results
function debug($message=""){
    print_r("<br/><br/>");
    print_r($message);
    print_r("<br/><br/>");
}

// Literally does nothing.
function doNothing() {;}
?>