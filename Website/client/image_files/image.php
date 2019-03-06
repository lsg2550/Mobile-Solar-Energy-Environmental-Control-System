<?php
//Require
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessionstart.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/sessioncheck.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/connect.php");
require($_SERVER["DOCUMENT_ROOT"] . "/index_files/operations.php");
    
function getImages() {
    global $conn;

    //Get the names of all detection folders
    $detectFoldersPath = $_SERVER["DOCUMENT_ROOT"] . "/detectdir/";
    $detectFoldersNames = array_diff(scandir($detectFoldersPath), array('.', '..'));

    //Iterate through each detection folder 
    foreach ($detectFoldersNames as $detectFolderName) {
        $detectFolderNameFullPath = array_diff(scandir($detectFoldersPath . $detectFolderName), array('.', '..'));
        echo "<table id='imagelisttable'>";
        echo "<caption><b>{$detectFolderName}</b></caption>";
        echo "<tr><th>Image Name</th><th>Click to View</th></tr>";
        foreach ($detectFolderNameFullPath as $detectImageName) {
            $detectImageNameFullPath = preg_replace("/\s+/", "%20", $detectFoldersPath . $detectFolderName . "/" . $detectImageName);
            echo "<tr><td>{$detectImageName}</td><td><a href={$detectImageNameFullPath} target='imagecurrent'>Click Here!</a></td></tr>";
        }
        echo "</table>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detection Images</title>
    <link rel="stylesheet" type="text/css" href="../navigator.css">
    <link rel="stylesheet" type="text/css" href="image.css">
    <script src="adjustimageframe.js"></script>
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

    <div id="imagecontainer">
        <div id="imagelist">
            <?php getImages(); ?>
        </div>

        <div id="imageviewer">
            <iframe id="imagecurrent" name="imagecurrent" onload="resizeIframe(this)" scrolling="no" frameborder="no"></iframe>
        </div>

        <div id="imageclear"></div>
    </div>
</body>

</html>