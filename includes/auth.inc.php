<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/functions.inc.php');

function RequireAuthentication($requiredRoleId = null){
    StartSecureSession();
    if (!isset($_SESSION["UserID"])) {
        header("location: /index.php");
        exit;
    }
    if ($requiredRoleId !== null && $_SESSION["RoleID"] != $requiredRoleId) {
        header("location: /index.php");
        exit;
    }
}
?>
