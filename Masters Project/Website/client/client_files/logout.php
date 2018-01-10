<?php

//Require
require('../../index_files/session.php');

$_SESSION['user'] = 0;
header('Location: ../../index.html');
exit();
?>