<?php
include('./includes/functions.inc.php');

$username = null;
$password = null;

if (isset($_POST['username'])) {
    $username = $_POST['username'];
}

if (isset($_POST['password'])) {
    $password = $_POST['password'];
}

try {
    $loginResult=ValidateLogin($username, $password);
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    http_response_code(500);
    exit;
}

if (!$loginResult) { /*login failed*/
    echo "invalid username/password";
    http_response_code(403);
    exit;
} else { /*login success*/
    session_start();
    $_SESSION["UserID"] = $loginResult["UserID"];
    $_SESSION["FullName"] = $loginResult["FullName"];
    $_SESSION["RoleID"] = $loginResult["RoleID"];
    $_SESSION["Username"] = $loginResult["Username"];
}
