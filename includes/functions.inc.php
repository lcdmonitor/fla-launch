<?php
include_once('./_config/config.inc.php');


function printSiteName()
{
    global $SITE_NAME;
    echo ($SITE_NAME);
}

function GetDBConnection()
{
    global $DB_HOST;
    global $DB_NAME;
    global $DB_PORT;
    global $DB_USER;
    global $DB_PASS;
    return $mysqli =  mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
}

function ListRoles()
{
    $mysqli = GetDBConnection();

    $query = "Select RoleID, Rolename from web.Role";

    $result = mysqli_query($mysqli, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        printf("%s&nbsp;%s&nbsp<br>", $row["RoleID"], $row["Rolename"]);
    }
}

function CreateUser($username, $fullname, $email, $password, $roleid)
{
    if (!isset($username) || !isset($fullname) || !isset($email) || !isset($password) || !isset($roleid)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "INSERT INTO web.User (Username, FullName , Email, PasswordHash, RoleID) VALUES ( ?, ?, ?, ?, ? )";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, 'ssssi', $username, $fullname, $email, $hash, $roleid);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$success) {
        die("Error: Error Creating User");
    }
}

function ValidateLogin($userIdOrEmail, $password)
{
    if (!isset($userIdOrEmail) || !isset($password)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT UserID, FullName, RoleID, PasswordHash FROM web.User WHERE (Username = ? OR Email = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, 'ss', $userIdOrEmail, $userIdOrEmail);
    mysqli_stmt_execute($stmt);
    
    $resultData = mysqli_stmt_get_result($stmt);
    $result = false;
    
    if ($row = mysqli_fetch_assoc($resultData)) {
        $db_password_hash = $row["PasswordHash"];
        
        if (password_verify($password, $db_password_hash)) {
            $result = array(
                        "UserID"=>$row["UserID"],
                        "FullName"=>$row["FullName"],
                        "RoleID"=>$row["RoleID"]
                    );
        }
    }
   
    mysqli_stmt_close($stmt);
    
    return $result;
}

function GetIsUserLoggedIn()
{
    if (isset($_SESSION["UserID"])) {
        return true;
    } else {
        return false;
    }
}
