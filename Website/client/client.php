<?php
    //Require
    require("../index_files/sessionstart.php");
    require("../index_files/sessioncheck.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Client's Selection Page</title>
    <link rel="stylesheet" href="client.css">
</head>

<body>
    <h1 class="title">Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    <div class="formdiv">
        <form action="vitals_files/vitals.php" method="post">
            <input type="submit" value="Control Panel">
        </form>
        <form action="stats_files/stats.php" method="post">
            <input type="submit" value="View Statistics">
        </form>
        <form action="log_files/listlogs.php" method="post">
            <input type="submit" value="View Logs">
        </form>
        <form action="image_files/image.php" method="post">
            <input type="submit" value="View Images">
        </form>
        <form action="../index_files/logout.php" method="post">
            <input type="submit" value="Log Out">
        </form>
    </div>
</body>

</html>