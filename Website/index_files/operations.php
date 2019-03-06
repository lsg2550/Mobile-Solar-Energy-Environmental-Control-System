<?php
//Require
require "sessionstart.php";
require "sessioncheck.php";

//Remove extra characters and place data into an array, all data carried out in this website will follow the same data format
//e.g: Initial array '[data1, data2, data3]' will become 'array[0] = data1, array[1] = data2, array[2] = data3'
function splitDataIntoArray($stringToReplace) {
    $charToReplace = array('[', ']');
    $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
    $stringSplit = preg_split("/[,]+/", $stringReplaced);
    return $stringSplit; //Returns array of data
}

function convert2DArrayto1DArray($inputArray, $forSensor = false) {
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

function transposeArray($inputArray){
    return array_map(null, ...$inputArray);
}

//ONLY USED FOR PHP WHERE DATABASE TRANSACTIONS ARE REQUIRED AND CAN'T BE DEBUGGED PROPERLY ON THE CMS
function debug($message){
    print_r("<br/><br/>");
    print_r($message);
    print_r("<br/><br/>");
}

//getTimeStamp - Gets the timestamp of the current status to display it on the HTML page
function getTimeStamp($stringToReplace) {
    $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
    return end($stringSplit); //Return timestamp for currentstatus fieldset
}

//getData - Generate and format HTML tables to display CurrentStatus and Log results, respectively
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

function doNothing() {;}
?>