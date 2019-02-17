<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");
    require("../../index_files/operations.php");
    require("generatecontrolpanel.php");

    //Database Queries
    $currentUser = $_SESSION['username']; //Get Current User Name
    $sqlVitalsCurrentThresholds = "SELECT VN, VL, VU, RPID FROM vitals WHERE USR='{$currentUser}' ORDER BY VN;"; //Select vital settings (lower & upper limits) to display and allow user to change those vital settings

    //Execute Queries
    $resultCurrentThresholds = mysqli_query($conn, $sqlVitalsCurrentThresholds);

    //Store Current Tresholds Query Results into $arrayCurrentThreshold
    $arrayCurrentThreshold = array(); 
    if(mysqli_num_rows($resultCurrentThresholds) > 0) {
        while($row = mysqli_fetch_assoc($resultCurrentThresholds)) {
            $tempRow = "[ {$row['VN']}, {$row['VL']}, {$row['VU']}, {$row['RPID']} ]";
            $arrayCurrentThreshold[] = $tempRow;
        }
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>Remote Site - Vital Control Panel</title>
    <link rel="stylesheet" type="text/css" href="vitals.css">
    <link rel="stylesheet" type="text/css" href="../client.css">
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
        <form action="vitalsthreshold.php" method="post">
            <fieldset>
                <legend>Vital Threshold Control Panel:</legend>
                <?php
                        echo "<fieldset>";
                        foreach ($arrayCurrentThreshold as $aCT) { echo generateVitalThresholdControlPanel($aCT); } //Generate Threshold Control Panel
                        echo "</table></fieldset>";
                        resetGlobals();
                    ?>
                <input type="submit" value="Commit Any Changes">
            </fieldset>
        </form>
    </div>
</body>

</html>