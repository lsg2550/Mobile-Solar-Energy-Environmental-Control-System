<?php

//Require
ob_start();
require('../index_files/sessionstart.php');
require('../index_files/sessioncheck.php');

$_SESSION['user'] = 0;
header('Location: ../../index.html');
ob_end_flush();
exit();

?>