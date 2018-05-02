<?php

//Buffer Start
ob_start();

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    ob_end_flush();
    exit();
} 

//Buffer End
ob_end_flush();

?>