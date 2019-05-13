<?php
    /**
     * Descrption: This checks if the user is logged in. This script is normally run from the client pages to prevent
     * a user who is not logged in from accessing the client pages. They will be redirected to the login page.
     * 
     * Note: ob_start and ob_end_flush are required by 000webhost(or browsers? in general) when redirecting traffic to different
     * pages.
     */

    //Buffer Start
    ob_start();

    if ($_SESSION['user'] !== 1) {
        header("Location: /index.html");
    }

    //Buffer End
    ob_end_flush();
?>