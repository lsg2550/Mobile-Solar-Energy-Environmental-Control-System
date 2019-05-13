<?php
    /**
     * Descrption: This script is the main page a client will see once they login. This page will display the current status of all the RPi's belonging to the client
     * and should also display the current status of all the RPi's that they have access to.
     * 
     * Note: Currently the viewing of RPi's that the client has access to is hardcoded. Please read the note in index.php for more information. In short,
     * this should not be a hard fix, you will likely have to perform two SQL queries to get the RPis that the current client owns and the RPis that the 
     * client has access to. Then in the $sqlCurrentStatus you feed it the array of RPi IDs as `uid-owner` in (0, 1, 2) where 0,1,2 are an array of 
     * user ID's. This should return all RPi's belonging to those IDs.
     */

    //Require
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");

    //Database Queries
    $currentUID = (!empty($_SESSION['username_access'])) ? $_SESSION['username_access'] : $_SESSION['username']; //Get Current User Name
    $sqlCurrentStatus = "SELECT vn, v1, v2, ts, rpi.rpid FROM (status NATURAL JOIN vitals) CROSS JOIN rpi WHERE `uid-owner`='{$currentUID}';"; //Select all current status related to the current user
    $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
    if (!$resultCurrentStatus || mysqli_num_rows($resultCurrentStatus) == 0) { doNothing(); }

    //Store CurrentStatus Query Results into $arrayCurrentStatus
    $arrayCurrentStatus = array();
    if (mysqli_num_rows($resultCurrentStatus) > 0) {
        while ($row = mysqli_fetch_assoc($resultCurrentStatus)) {
            $tempVOne = "";
            switch ($row['vn']) {
                case 'BatteryVoltage':
                    $row['vn'] = "Battery Voltage";
                    $tempVOne = round($row['v1'], 2) . "V";
                    break;
                case 'SolarPanelVoltage':
                    $row['vn'] = "PV Voltage";
                    $tempVOne = round($row['v1'], 2) . "V";
                    break;
                case 'BatteryCurrent':
                    $row['vn'] = "Battery Current";
                    $tempVOne = round($row['v1'], 2) . "A";
                    break;
                case 'SolarPanelCurrent':
                    $row['vn'] = "PV Current";
                    $tempVOne = round($row['v1'], 2) . "A";
                    break;
                case 'ChargeControllerCurrent':
                    $row['vn'] = "Charge Controller Current";
                    $tempVOne = round($row['v1'], 2) . "A";
                    break;
                case 'TemperatureInner':
                    $row['vn'] = "Inside Temperature";
                    $tempVOne = round($row['v1'], 2) . "&deg;C";
                    break;
                case 'TemperatureOuter':
                    $row['vn'] = "Outside Temperature";
                    $tempVOne = round($row['v1'], 2) . "&deg;C";
                    break;
                case 'HumidityInner':
                    $row['vn'] = "Inside Humidity";
                    $tempVOne = round($row['v1'], 2) . "g/m<sup>3</sup>";
                    break;
                case 'HumidityOuter':
                    $row['vn'] = "Outside Humidity";
                    $tempVOne = round($row['v1'], 2) . "g/m<sup>3</sup>";
                    break;
                case 'GPS':
                    $tempOne = (round($row['v1'], 2) > 0) ? round($row['v1'], 2) . '&deg;N' : round($row['v1'], 2) . '&deg;S';
                    $tempTwo = (round($row['v2'], 2) > 0) ? round($row['v2'], 2) . '&deg;E' : round($row['v2'], 2) . '&deg;W';
                    $tempVOne = $tempOne . '&comma;' . $tempTwo;
                    break;
                case 'SolarPanel':
                    $row['vn'] = "PV";
                    $tempVOne = $row['v1'];
                    break;
                case 'Clarity':
                    $tempVOne = round($row['v1'], 2) . "%";
                    break;
                default:
                    $tempVOne = $row['v1'];
                    break;
            }
            $arrayCurrentStatus[] = "[ {$row['vn']}, {$tempVOne}, {$row['rpid']}, {$row['ts']} ]";
        }
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>Client's Selection Page</title>
    <link rel="stylesheet" href="client.css">
    <link rel="stylesheet" href="navigator.css">
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

    <div class="displays">
        <fieldset id="current-status-fieldset">
            <legend>Current Status</legend>
            <div id="current-status-div">
                <?php echo getData($arrayCurrentStatus, "<tr><th>Vital Name</th><th>Status</th><th>RPiID</th><th>Timestamp</th></tr>", false); ?>
            </div>
        </fieldset>
    </div>
</body>

</html>