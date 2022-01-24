<?php     
    function RequireAuthentication(){
        session_start();
        if (!isset($_SESSION["UserID"])) {
            header("location: /index.php");
        }
    }

    RequireAuthentication();
?>