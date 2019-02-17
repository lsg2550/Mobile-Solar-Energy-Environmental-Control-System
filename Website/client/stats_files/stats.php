<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");

    function generateRPISelect() {
        require_once("../../index_files/connect.php");

        //Database Queries
        $currentUser = $_SESSION['username']; //Get Current User Name
        $sqlRPis = "SELECT rpiID FROM rpi WHERE owner='{$currentUser}';"; // Select all RPis belonging to the current user

        //Execute Queries
        $resultRPis = mysqli_query($conn, $sqlRPis);

        //Store RPis into an array of RPis
        $arrayRPis = array(); 
        if(mysqli_num_rows($resultRPis) > 0) {
            while($row = mysqli_fetch_assoc($resultRPis)) {
                $arrayRPis[] = $row['rpiID'];
            }
        }

        # Create the HTML dropdown menu
        echo "<select id='rpi-select' name='rpi_select' required>";
        for ($i=0; $i < count($arrayRPis); $i++) { 
            echo "<option value='{$arrayRPis[$i]}'>{$arrayRPis[$i]}</option>";
        }
        echo "</select>";
    }
?>

<html>
<head>
    <title>Statistics Page</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js"></script>
    <script src="createchart.js"></script>
    <link rel="stylesheet" type="text/css" href="stats.css">
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

    <div class="formdiv">
        <form id="data-preview-select" method="post">
            <!-- Select Vital -->
            <label for="vital-select">Select Vital:</label>
            <select id="vital-select" name="vital_select" required>
                <option value="battery">Battery</option>  <!-- Voltage/Current -->
                <option value="solar">Solar</option> <!-- Voltage/Current -->
                <option value="temperature">Temperature</option> <!-- Inner/Outer -->
                <option value="humidity">Humidity</option> <!-- Inner/Outer -->
                <option value="exhaust">Exhaust</option> <!-- Single -->
            </select>

            <!-- Select Date -->
            <label for="date-start-select">Select Start Date:</label>
            <input type="date" class="date-range" id="date-start-select" name="date_start" required>
            <label for="date-end-select">Select End Date:</label>
            <input type="date" class="date-range" id="date-end-select" name="date_end" required>

            <!-- Select Time -->
            <label for="time-start-select">Select Start Time:</label>
            <input type="time" class="time-range" id="time-start-select" name="time_start" required>
            <label for="time-end-select">Select End Time:</label>
            <input type="time" class="time-range" id="time-end-select" name="time_end" required>

            <!-- Select Time Interval -->
            <label for="time-interval">Select Time Interval:</label>
            <select id="time-interval" name="time_interval" required>
                <option value="15">15 Minutes</option>
                <option value="30">30 Minutes</option>
                <option value="45">45 Minutes</option>
            </select>

            <!-- Select RPi -->
            <label for="rpi-select">Select RPi:</label>
            <?php generateRPISelect(); ?>

            <!-- Submit -->
            <input type="submit">
        </form>
    </div>

    <div class="charts">
        <canvas class="charts-canvas" id="primary-chart"></canvas>
        <canvas class="charts-canvas" id="secondary-chart"></canvas>
        <script>createchart("primary-chart", "line", "")</script>
        <script>createchart("secondary-chart", "line", "")</script>
    </div>
</body>
</html>