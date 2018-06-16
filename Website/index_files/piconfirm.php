<?php

//TODO: Filter $_GET
$xmlDirectory = "../../xmls/";
$xmlFilename = $_GET["xmlfile"];
$xmlFullFilePath = $xmlDirectory . $xmlFilename;

if(file_exists($xmlFullFilePath)) {
    echo "OK";
} else {
    echo "NO";
}

?>