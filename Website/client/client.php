<?php
//Require
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");

//Database Queries
$currentUser = $_SESSION['username']; //Get Current User Name
$sqlCurrentStatus = "SELECT VN, V1, V2, TS, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select all current status related to the current user
$resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
if (!$resultCurrentStatus || mysqli_num_rows($resultCurrentStatus) == 0) {return;}

//Store CurrentStatus Query Results into $arrayCurrentStatus
$arrayCurrentStatus = array();
if (mysqli_num_rows($resultCurrentStatus) > 0) {
    while ($row = mysqli_fetch_assoc($resultCurrentStatus)) {
        $tempVOne = "";
        switch ($row['VN']) {
            case 'BatteryVoltage':
                $row['VN'] = "Battery Voltage";
                $tempVOne = round($row['V1'], 2) . "V";
                break;
            case 'SolarPanelVoltage':
                $row['VN'] = "PV Voltage";
                $tempVOne = round($row['V1'], 2) . "V";
                break;
            case 'BatteryCurrent':
                $row['VN'] = "Battery Current";
                $tempVOne = round($row['V1'], 2) . "A";
                break;
            case 'SolarPanelCurrent':
                $row['VN'] = "PV Current";
                $tempVOne = round($row['V1'], 2) . "A";
                break;
            case 'ChargeControllerCurrent':
                $row['VN'] = "Charge Controller Current";
                $tempVOne = round($row['V1'], 2) . "A";
                break;
            case 'TemperatureInner':
                $row['VN'] = "Inside Temperature";
                $tempVOne = round($row['V1'], 2) . "&deg;C";
                break;
            case 'TemperatureOuter':
                $row['VN'] = "Outside Temperature";
                $tempVOne = round($row['V1'], 2) . "&deg;C";
                break;
            case 'HumidityInner':
                $row['VN'] = "Inside Humidity";
                $tempVOne = round($row['V1'], 2) . "g/m<sup>3</sup>";
                break;
            case 'HumidityOuter':
                $row['VN'] = "Outside Humidity";
                $tempVOne = round($row['V1'], 2) . "g/m<sup>3</sup>";
                break;
            case 'GPS':
                $tempOne = (round($row['V1'], 2) > 0) ? round($row['V1'], 2) . '&deg;N' : round($row['V1'], 2) . '&deg;S';
                $tempTwo = (round($row['V2'], 2) > 0) ? round($row['V2'], 2) . '&deg;E' : round($row['V2'], 2) . '&deg;W';
                $tempVOne = $tempOne . '&comma;' . $tempTwo;
                break;
            case 'SolarPanel':
                $row['VN'] = "PV";
                break;
            default:
                $tempVOne = $row['V1'];
                break;
        }
        $arrayCurrentStatus[] = "[ {$row['VN']}, {$tempVOne}, {$row['RPID']}, {$row['TS']} ]";
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