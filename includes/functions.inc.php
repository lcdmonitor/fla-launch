<?php
include_once('./includes/config.inc.php');


function printSiteName()
{
    global $SITE_NAME;
    echo($SITE_NAME);
}

function GetDBConnection()
{
    global $DB_HOST;
    global $DB_NAME;
    global $DB_PORT;
    global $DB_USER;
    global $DB_PASS;
    return $mysqli =  mysqli_connect($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME,$DB_PORT);
}

function ListUsers()
{
    $mysqli = GetDBConnection();

    $query = "Select UserID, FullName, PasswordHash from web.User";

    $result = mysqli_query($mysqli, $query);

    while($row=mysqli_fetch_assoc($result))
    {
        printf("%s&nbsp;%s&nbsp<br>", $row["UserID"], $row["FullName"]);
    }
}
