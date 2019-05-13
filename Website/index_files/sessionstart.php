<?php
    /**
     * Descrption: This script checks if a session exists, if it doesn't it will create one so that the webpage will know whether or not
     * the user is logged in, or if they have access to other RPis, etc.
     * 
     * Note: ob_start and ob_end_flush are required by 000webhost(or browsers? in general) when redirecting traffic to different
     * pages. Although this script isn't redirecting anyone this still caused an error that needed the ob start and flush. Perhaps
     * it may not be required anymore, but I'll leave that to whoever picks this up.
     */

    //Buffer Start
    ob_start();

    if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
        session_start();
    }

    //Buffer End
    ob_end_flush();
?>