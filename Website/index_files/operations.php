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

function doNothing() {;}
?>