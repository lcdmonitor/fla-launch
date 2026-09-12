<?php
include('./includes/functions.inc.php');
StartSecureSession();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!ValidateCSRFToken($csrfToken)) {
    echo "invalid request";
    http_response_code(403);
    exit;
}

try {
    if (IsLoginLocked($username, $ipAddress)) {
        echo "too many failed attempts, please try again later";
        http_response_code(429);
        exit;
    }
    $loginResult = ValidateLogin($username, $password);
    RecordLoginAttempt($username, $ipAddress, (bool)$loginResult);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    http_response_code(500);
    exit;
}

if (!$loginResult) { /*login failed*/
    echo "invalid username/password";
    http_response_code(403);
    exit;
} else { /*login success*/
    session_regenerate_id(true);
    $_SESSION["UserID"] = $loginResult["UserID"];
    $_SESSION["FullName"] = $loginResult["FullName"];
    $_SESSION["RoleID"] = $loginResult["RoleID"];
    $_SESSION["Username"] = $loginResult["Username"];
}
