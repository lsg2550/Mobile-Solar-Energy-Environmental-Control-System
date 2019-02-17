<?php
    $vital = $_POST["vital_select"];
    $dateStart = $_POST["date_start"];
    $dateEnd = $_POST["date_end"];
    $timeStart = $_POST["time_start"];
    $timeEnd = $_POST["time_end"];
    $timeInterval = $_POST["time_interval"];
    $rpi = $_POST["rpi_select"];

    
    echo $vital . $dateStart . $dateEnd . $timeStart . $timeEnd . $timeInterval . $rpi;
?>