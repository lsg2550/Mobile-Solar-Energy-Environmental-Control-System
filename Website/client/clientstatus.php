<?php
    //Require
    require("../index_files/sessionstart.php");
    require("../index_files/sessioncheck.php");
    require("../index_files/connect.php");
    require("../index_files/operations.php");

    //Database Queries
    $currentUser = $_SESSION['username']; //Get Current User Name
    $sqlCurrentStatus = "SELECT VN, V1, V2, TS, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select all current status related to the current user
    $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
    if(!$resultCurrentStatus || mysqli_num_rows($resultCurrentStatus) == 0){ return; }

    //Store CurrentStatus Query Results into $arrayCurrentStatus
    $arrayCurrentStatus = array(); 
    if(mysqli_num_rows($resultCurrentStatus) > 0) {
        while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
            $tempRow = "";
            $tempVV = "";
            switch ($row['VN']) {
                case 'BatteryVoltage':
                case 'SolarPanelVoltage':
                    $tempVV = round($row['V1'], 2) . "V";
                    break;
                case 'BatteryCurrent':
                case 'SolarPanelCurrent':
                case 'ChargeControllerCurrent':
                    $tempVV = round($row['V1'], 2) . "A";
                    break;
                case 'TemperatureInner':
                case 'TemperatureOuter':
                    $tempVV = round($row['V1'], 2) . "&deg;C";
                    break;
                case 'HumidityInner':
                case 'HumidityOuter':
                    $tempVV = round($row['V1'], 2) . "g/m<sup>3</sup>";
                    break;
                case 'GPS':
                    $tempOne = (round($row['V1'], 2) > 0) ? round($row['V1'], 2) . '&deg;N' : round($row['V1'], 2) . '&deg;S';
                    $tempTwo = (round($row['V2'], 2) > 0) ? round($row['V2'], 2) . '&deg;E' : round($row['V2'], 2) . '&deg;W';
                    $tempVV = $tempOne . '&comma;' . $tempTwo; 
                    break;
                default: //Will always be Exhaust
                    $tempVV = $row['V1'];
                    break;
            }
            $arrayCurrentStatus[] = "[ {$row['VN']}, {$tempVV}, {$row['RPID']}, {$row['TS']} ]";
        }
    }
    
    $initalRaspberryPi = true; //Initialize
    $currentRaspberryPi = "-1"; //Initialize

    //getData - Generate and format HTML tables to display CurrentStatus and Log results, respectively
    function getData($stringToReplace, $tableHeader, $printAll) { 
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
        $displayHTML = ""; //Initialize Display HTML
        global $initalRaspberryPi;
        global $currentRaspberryPi;
        
        if ($stringSplit[2] !== $currentRaspberryPi) { //$stringSplit[2] will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
            $currentRaspberryPi = $stringSplit[2];

            if($initalRaspberryPi === false){ $displayHTML .= "</table>"; } //Closes the table from the previous RaspberryPi
            else { $initalRaspberryPi = false; } //Initial table will change this to false after it creates the first table header

            $displayHTML .= "<table>{$tableHeader}";
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

    //Reset Globals
    function resetGlobals() {
        global $initalRaspberryPi;
        global $currentRaspberryPi;

        $initalRaspberryPi = true; //Reset currentRaspberryPi 'Counter'
        $currentRaspberryPi = "-1"; //Reset currentRaspberryPi 'Counter'
    }

    foreach ($arrayCurrentStatus as $rowIndex => $rowArray) {
        echo getData($rowArray, "<tr><th>Vital Name</th><th>Status</th><th>Timestamp</th></tr>", false); 
    }
    echo "</table>";
?>