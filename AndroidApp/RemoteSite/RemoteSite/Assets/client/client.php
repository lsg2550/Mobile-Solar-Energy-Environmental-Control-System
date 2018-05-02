<?php
if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}

if ($_SESSION['user'] !== 1) {
    header('Location: ../index.html');
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Remote Site - Client Page</title>
        <link rel="stylesheet" type="text/css" href="client_files/client.css">
    </head>
    <body>
        <div><h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1></div>
        <div>
            <form action="client_files/listlogs.php" method="post" target="status">
                <input type="submit" value="List Logs">
            </form>
            <form action="client_files/logout.php" method="post">
                <input type="submit" value="Log Out">
            </form>
        </div>
        <div>
            <iframe name="status" style="min-height:98vh;" width="100%"></iframe>
        </div>
    </body>
</html>
