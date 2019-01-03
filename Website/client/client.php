<?php
    //Require
    require("../index_files/sessionstart.php");
    require("../index_files/sessioncheck.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>Remote Site - Client Page</title>
<link rel="stylesheet" href="client_files/client.css">
</head>
<body>
<h1 class="title">Remote Site - Mobile Solar Energy & Environmental Control System</h1>
	<div class="formdiv">
		<form action="client_files/listlogs.php" method="post" target="status">
		  <input type="submit" value="List Logs">
		</form>
		<form action="image_files/image.php" method="post">
		  <input type="submit" value="View Images">
		</form>
		<form action="../index_files/logout.php" method="post">
		  <input type="submit" value="Log Out">
		</form>
	</div>
<div id="status-div">
  <iframe id="status" name="status"></iframe>
</div>
</body>
</html>