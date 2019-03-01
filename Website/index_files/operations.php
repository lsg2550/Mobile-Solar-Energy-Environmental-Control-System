<?php
    //Require
    require("sessionstart.php");
    require("sessioncheck.php");

    //Remove extra characters and place data into an array, all data carried out in this website will follow the same data format
    //e.g: Initial array '[data1, data2, data3]' will become 'array[0] = data1, array[1] = data2, array[2] = data3'
    function splitDataIntoArray($stringToReplace) { 
        $charToReplace = array('[', ']');
        $stringReplaced = str_replace($charToReplace, '', $stringToReplace);
        $stringSplit = preg_split("/[,]+/", $stringReplaced);

        //Returns array of data
        return $stringSplit;
    }

    function convert2DArrayto1DArray($inputArray) {
        $outputArray = array();

        foreach (array_values($inputArray) as $key => $value) {
            foreach ($value as $innerkey => $innervalue) {
                $outputArray[$innerkey] = $innervalue;
            }
        }

        return $outputArray;
    }
    
    function doNothing() {;}
?>