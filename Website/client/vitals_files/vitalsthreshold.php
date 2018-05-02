<?php
    //TODO: Filter $_POST 
    //This php code will update the 'vitals' table for the respective raspberry pi and its user
    $currentUser = $_SESSION['username']; //Get Current User Name
    $amtOfRPis = count(array_unique($_POST['rpid'])); //Get the amount of RPis to edit
    $amtOfVitals = count($_POST['vitallower']); //Get the amount of Vitals to edit - will be the same count for $vitalupper and $vitalname
    $sqlUpdate = array();

    for($amtOfRPis - 1; $amtOfRPis >= 0; $amtOfRPis--) {
        $tempRPi = $_POST['rpid'][$amtOfRPis];
        echo 'RPID:' . $tempRPi;
        for($amtOfVitals - 1; $amtOfVitals >= 0; $amtOfVitals--) {
            $tempVL = $_POST['vitallower'][$amtOfVitals];
            $tempVU = $_POST['vitalupper'][$amtOfVitals];
            echo 'LOWER:' . tempVL;
            echo 'UPPER:' . tempVU;
            $sqlUpdate[] = 'UPDATE vitals SET VL="{$tempVL}", VU="{$tempVU}" WHERE RPID="{$tempRPI}" AND USR="{$currentUser}";'; //TODO: ADD VITALNAME
        }
    }
?>