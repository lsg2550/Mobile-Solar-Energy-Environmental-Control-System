<?php

//Buffer Start
ob_start();

//Require
require("sessionstart.php");
require("sessioncheck.php");

//Log User Out
$_SESSION['user'] = 0;
$_SESSION['username'] = "";
header("Location: ../index.html");

//Buffer End
ob_end_flush();
exit();

?>