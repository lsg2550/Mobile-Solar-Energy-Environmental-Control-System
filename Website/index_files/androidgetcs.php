<?php
    //Android JSON Location
    $androidXMLLocation = "../../androidxmls/";
    $currentUser = $_GET['username'];

    //Open JSON File and print it out - I am aware that this isn't the right or best way to handle this, but for now it works. 
    if($jsonFile = fopen("{$androidXMLLocation}{$currentUser}cs.json", "r")) {
        while(!feof($jsonFile)) { echo fgets($jsonFile); }
        fclose($jsonFile);
    }
?>