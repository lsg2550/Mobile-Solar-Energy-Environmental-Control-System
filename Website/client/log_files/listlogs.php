<?php
//Require
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");

//Database Queries
$currentUID = (!empty($_SESSION['username_access'])) ? $_SESSION['username_access'] : $_SESSION['username']; //Current User
$sqlLog = "SELECT V.vn, typ, L.rpid, v1, v2, ts FROM log AS L NATURAL JOIN vitals AS V WHERE L.uid='{$currentUID}' ORDER BY L.rpid, L.ts DESC;"; //Select all logs related to the current user

//Execute Queries
$resultLog = mysqli_query($conn, $sqlLog);
if(!$resultLog || mysqli_num_rows($resultLog) == 0){ return; }

//Store Log Query Results into $arrayLog
$arrayLog = array();
if (mysqli_num_rows($resultLog) > 0) {
    while ($row = mysqli_fetch_assoc($resultLog)) {
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
            case 'Clarity':
                $tempVOne = round($row['v1'], 2) . "%";
                break;
            default:
                $tempVOne = $row['v1'];
                break;
        }
        $arrayLog[] = "[ {$row['vn']}, {$row['typ']}, {$row['rpid']}, {$tempVOne}, {$row['ts']} ]";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Logs</title>
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