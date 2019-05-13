<?php
    /**
     * Description: This script will verify/confirm the user's login credentials. Then send them to the client homepage if login is successful.
     * 
     * Note: $_SESSION['username_access'] is hardcoded, but can be changed to be dynamic as $_SESSION objects can store arrays. Please read the note
     * below the $_SESSION['username_access'] assignment for more details. Also the access table in the database has read/write permissions. You may
     * want to also set those permissions in the $_SESSION cookies within the same array you put in $_SESSION['username_access']. Essentially, creating
     * an array of arrays under $_SESSION['username_access']. Perhaps there is a cleaner way of doing this, but this is just an idea. Unfortunately, I
     * implemented these permissions in the table near the end so I was not able to implement them.
     * 
     * Note: ob_start() and ob_end_flush() are required by 000webhost (or maybe browsers in general) to send the user elsewhere. All the code 
     * executes as normal but actually processes once ob_end_Flush() is reached.
     */

    //Buffer Start
    ob_start();

    //Require
    require("sessionstart.php");
    require("connect.php");

    //TODO: Filter User & Pass; Take User and Pass from POST
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //Database Queries
    $sqlValidate = "SELECT uid FROM users WHERE username='{$user}' AND passwd=SHA1('{$pass}');"; //Select username and password from the user given username and password
    $resultsValidate = mysqli_query($conn, $sqlValidate); //Execute Query

    // Log login attempt - update db
    //$sqlLoginAttempt = "INSERT INTO log (typ) VALUES ('LA')"; //Code to update the DB should go here that there was a login attempt - typ='LA'
    // Whether the attempt is successful or not, report the login attempt 

    if (!$resultsValidate || mysqli_num_rows($resultsValidate) == 0) {
        header("Location: /index.html"); 
    }

    //If user is correct, sign them in, otherwise send them back to the log in page
    if(mysqli_num_rows($resultsValidate) == 1) {
        // Check if this user has access elsewhere
        $sqlHaveAccess = "SELECT `uid-owner` FROM access WHERE `uid-accessor`='{$uid}';";
        $resultsHaveAccess = mysqli_query($conn, $sqlHaveAccess);
        $_SESSION['username_access'] = mysqli_fetch_assoc($resultsHaveAccess)["uid-owner"]; // Change this, don't hardcode, there may be a chance that a specific user will have access to multiple RPi!
        /**
        * It is possible to have $_SESSION['username_access'] be an array of all the different RPi's they have access to. For the time being it is hardcoded. 
        * When you change this you will have to change almost all php files under the client/ directory to not only grab the RPi's that the respective user owns,
        * but also the RPi's they have access to.
        */
        $_SESSION['username'] = $uid;
        $_SESSION['user'] = 1;
        header("Location: /client/client.php");
    }

    //Buffer End & Exit
    ob_end_flush();
?>