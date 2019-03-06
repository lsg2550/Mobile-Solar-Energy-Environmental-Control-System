<?php
//Require
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");

//Database Queries
$currentUser = $_SESSION["username"]; //Current User
$sqlLog = "SELECT V.VN, TYP, l.RPID, V1, V2, TS FROM log AS l NATURAL JOIN vitals AS V WHERE l.USR='{$currentUser}' ORDER BY l.RPID, l.TS DESC;"; //Select all logs related to the current user

//Execute Queries
$resultLog = mysqli_query($conn, $sqlLog);
if(!$resultLog || mysqli_num_rows($resultLog) == 0){ return; }

//Store Log Query Results into $arrayLog
$arrayLog = array();
if (mysqli_num_rows($resultLog) > 0) {
    while ($row = mysqli_fetch_assoc($resultLog)) {
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
            default:
                $tempVOne = $row['V1'];
                break;
        }
        $arrayLog[] = "[ {$row['VN']}, {$row['TYP']}, {$row['RPID']}, {$tempVOne}, {$row['TS']} ]";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Logs Page</title>
    <link rel="stylesheet" type="text/css" href="listlogs.css">
    <link rel="stylesheet" type="text/css" href="../navigator.css">
</head>

<body>
    <h1 class="title">Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    <div class="formdiv">
        <form action="../client.php" method="post">
            <input type="submit" value="Client Page">
        </form>
        <form action="../../index_files/logout.php" method="post">
            <input type="submit" value="Log Out">
        </form>
    </div>

    <div class="displays">
        <fieldset id="log-fieldset">
            <legend>Log</legend>
            <div id="log-table-div">
                <?php echo getData($arrayLog, "<tr><th>Vital Name</th><th>TYP</th><th>RPiID</th><th>Vital Value</th><th>Timestamp</th></tr>", true); ?>
            </div>
        </fieldset>
    </div>
</body>

</html>