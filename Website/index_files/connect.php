<?php
    /**
     * Description: This script is used to log into the database and create a $conn object for the other scripts in the CMS to use and retrieve data from
     * the database.
     * 
     * Note: $dbinfofp is hardcoded due to the format of the CMS platform. If you change out of 000webhost or use your own server, be sure to change this!
     */

    $dbinfofp = str_replace("public_html/", "", $_SERVER["DOCUMENT_ROOT"] . "/db/db.json"); //Ugly but I suppose it will do since I have no way of hard coding since the server does not belong to me, I do not know if my full path will always be the same
    $dbinfo = json_decode(file_get_contents($dbinfofp));

    $sname = $dbinfo->{"servername"};
    $database = $dbinfo->{"database"};
    $uname = $dbinfo->{"username"};
    $password = $dbinfo->{"password"};

    $conn = mysqli_connect($sname, $uname, $password, $database);
?>