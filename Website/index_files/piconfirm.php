<?php
include("operations.php");

//TODO: Filter $_GET
$xmlDirectory = "../../xmls/";
$xmlFilename = $_GET["xmlfile"];
$xmlFullFilePath = $xmlDirectory . $xmlFilename;

if(is_file($xmlFullFilePath)) {
    echo "OK";
    processXML($xmlFilename);
} else {
    echo "NO";
}

?>