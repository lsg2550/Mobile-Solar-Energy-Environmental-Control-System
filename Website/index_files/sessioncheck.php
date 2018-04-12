<?php

ob_start();

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    ob_end_flush();
    exit();
}

ob_end_flush();

?>