<?php

ob_start();

if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || !session_id()) {
    session_start();
}

ob_end_flush();

?>