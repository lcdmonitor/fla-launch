<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/functions.inc.php');

function RequireAuthentication(){
    StartSecureSession();
    if (!isset($_SESSION["UserID"])) {
        header("location: /index.php");
        exit;
    }
}

RequireAuthentication();
?>
