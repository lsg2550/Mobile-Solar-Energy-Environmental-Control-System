<?php

//Require
ob_start();

$_SESSION['user'] = 0;
header('Location: ../../index.html');
ob_end_flush();
exit();

?>