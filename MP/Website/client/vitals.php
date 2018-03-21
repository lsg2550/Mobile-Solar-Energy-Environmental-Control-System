<?php
//Require
require('../index_files/session.php');

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    exit();
}
?>

<html>
    <head>
        <title>Remote Site - Vitals Control Page</title>
        <link rel="stylesheet" type="text/css" href="vitals_files/vitals.css">
    </head>   
    <body>
        <div>
            <form action="vitals_files/vitalscontrol.php" method="post">
                <fieldset>

                    <legend>Vitals' Control Panel:</legend>
                    Username:
                    <input type="text" name="username"><br>
                    Password:
                    <input type="password" name="password"><br>
                    <input type="submit" value="Submit">

                </fieldset>
            </form>
        </div>
        <div>
            <form action="vitals_files/vitalsthreshold.php" method="post">
                <fieldset>

                    <legend>Vitals' Threshold Panel:</legend>
                    Username:
                    <input type="text" name="username"><br>
                    Password:
                    <input type="password" name="password"><br>
                    <input type="submit" value="Submit">

                </fieldset>
            </form>
        </div>
    </body>
</html>