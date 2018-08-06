<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");
    require("../../index_files/operations.php");

    //Database Queries
    $currentUser = $_SESSION["username"]; //Current User
    $sqlCurrentStatus = "SELECT VN, VV, TS, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select all current status related to the current user
    $sqlLog = "SELECT VID, TYP, RPID, V1, V2, TS FROM log WHERE USR='{$currentUser}' ORDER BY RPID, TS;"; //Select all logs related to the current user

    //Execute Queries
    $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
    $resultLog = mysqli_query($conn, $sqlLog);

    //Store CurrentStatus Query Results into $arrayCurrentStatus
    $arrayCurrentStatus = array(); 
    if(mysqli_num_rows($resultCurrentStatus) > 0) {
        while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
            $tempRow = "[ {$row['VN']}, {$row['VV']}, {$row['RPID']}, {$row['TS']} ]";
            $arrayCurrentStatus[] = $tempRow;
        }
    }

    //Store Log Query Results into $arrayLog
    $arrayLog = array();
    if(mysqli_num_rows($resultLog) > 0) { 
        while($row = mysqli_fetch_assoc($resultLog)) {
            //If V2 is blank, replace it with 'N/A'
            if($row['V2'] == NULL || $row['V2'] == "") { $row['V2'] = "N/A"; }

            $tempRow = "[ {$row['VID']}, {$row['TYP']}, {$row['RPID']}, {$row['V1']}, {$row['V2']}, {$row['TS']} ]";
            $arrayLog[] = $tempRow;
        }
    }

    //Functions
    $initalRaspberryPi = true; //Initialize
    $currentRaspberryPi = "-1"; //Initialize

    //getData - Generate and format HTML tables to display CurrentStatus and Log results, respectively
    function getData($stringToReplace, $tableHeader, $printAll) { 
        //Initialize
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
        $displayHTML = ""; //Initialize Display HTML
        global $initalRaspberryPi;
        global $currentRaspberryPi;
        
        if ($stringSplit[2] !== $currentRaspberryPi) { //$stringSplit[2] will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
            $currentRaspberryPi = $stringSplit[2];

            if($initalRaspberryPi === false){ $displayHTML .= "</table>"; } //Closes the table from the previous RaspberryPi
            else { $initalRaspberryPi = false; } //Initial table will change this to false after it creates the first table header

            $displayHTML .= "<table><caption>Raspberry Pi - {$currentRaspberryPi}</caption>{$tableHeader}";
        }

        //Generate HTML currentstatus/log tables
        $displayHTML .= "<tr>";
        for ($i = 0; $i < count($stringSplit); $i++) { 
            if($i === 2 && !$printAll) { continue; }
            $displayHTML .= "<td>{$stringSplit[$i]}</td>"; 
        }
        $displayHTML .= "</tr>";

        //Return table(s)
        return $displayHTML;
    }

    //getTimeStamp - Gets the timestamp of the current status to display it on the HTML page
    function getTimeStamp($stringToReplace) { 
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
        return end($stringSplit); //Return timestamp for currentstatus fieldset
    }

    //Reset Globals
    function resetGlobals() {
        global $initalRaspberryPi;
        global $currentRaspberryPi;

        $initalRaspberryPi = true; //Reset currentRaspberryPi 'Counter'
        $currentRaspberryPi = "-1"; //Reset currentRaspberryPi 'Counter'
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="listlogs.css">
    </head>

    <body>
        <div>
            <div>
                <fieldset><legend>Current Status</legend>
                    <?php
                        foreach ($arrayCurrentStatus as $aCS) { echo getData($aCS, "<tr><th>Vital Name</th><th>Status</th><th>Timestamp</th></tr>", false); }
                        echo "</table>";
                        resetGlobals();
                    ?>
                </fieldset>
            </div>
            <div>
                <fieldset><legend>Log</legend>
                    <?php
                        foreach ($arrayLog as $aL) { echo getData($aL, "<tr><th>VID</th><th>TYP</th><th>RPiID</th><th>Vital 1</th><th>Vital 2</th><th>Timestamp</th></tr>", true); }
                        echo "</table>";
                        resetGlobals();
                    ?>
                </fieldset>
            </div>
        </div>
    </body>
</html>