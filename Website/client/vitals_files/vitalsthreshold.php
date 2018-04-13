<?php
    echo 'LOWER:';
    foreach($_POST['vitallower'] as $vital) {
        echo $vital . '<br>';
    }

    echo 'UPPER:';
    foreach($_POST['vitalupper'] as $vital) {
        echo $vital . '<br>';
    }

    echo 'RPID:';
    foreach($_POST['rpid'] as $vital) {
        echo $vital . '<br>';
    }
?>