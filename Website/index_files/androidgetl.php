<?php
    /**
     * Description: This script is accessed by the android app via http request but acts as if its a json request. This loads the log json file created
     * by 'androidclientconfirm.php' and prints it out as text in the format of a json file to the html. The android app then uses a library for processing json files
     * to process the text output by this script to retrieve the data and display it on the app.
     */

    //Android JSON Location
    $androidXMLLocation = "../../androidxmls/";
    $currentUser = $_GET['username'];
    
    //Open JSON File and print it out - I am aware that this isn't the right or best way to handle this, but for now it works. 
    if($jsonFile = fopen("{$androidXMLLocation}{$currentUser}l.json", "r")) {
        while(!feof($jsonFile)) { echo fgets($jsonFile); }
        fclose($jsonFile);
    }
?>