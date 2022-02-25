<?php
function GetIsUserLoggedIn()
{
    if (isset($_SESSION["UserID"])) {
        return true;
    } else {
        return false;
    }
}

include_once($_SERVER['DOCUMENT_ROOT'] .'/_config/config.inc.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require $_SERVER['DOCUMENT_ROOT'] . '/includes/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/SMTP.php';


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

    $query = "Select RoleID, Rolename from Role";

    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

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

    $sql = "INSERT INTO User (Username, FullName , Email, PasswordHash, RoleID) VALUES ( ?, ?, ?, ?, ? )";

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

function ChangePassword($username, $password)
{
    if (!isset($username) || !isset($password)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "UPDATE User SET PasswordHash = ? WHERE Username = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed". htmlspecialchars($mysqli->error));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, 'ss', $hash, $username);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$success) {
        die("Error: Error Updating Password");
    }
}

function ValidateLogin($userIdOrEmail, $password)
{

    if (!isset($userIdOrEmail) || !isset($password)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT UserID, Username, FullName, RoleID, PasswordHash FROM User WHERE (Username = ? OR Email = ?)";

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
                        "RoleID"=>$row["RoleID"],
                        "Username"=>$row["Username"]
                    );
        }
    }
   
    mysqli_stmt_close($stmt);
    
    return $result;
}

function IsGoodPassword($password)
{
    return strlen($password) > 8;
}

function GetPageContent($pageid)
{

    if (!isset($pageid)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT Title, PageKey, Summary, Content, MemberOnly FROM Page WHERE (PageKey = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 's', $pageid);

    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    $result = false;
    
    if ($row = mysqli_fetch_assoc($resultData)) {
 
        $result = array(
                    "Title"=>$row["Title"],
                    "PageKey"=>$row["PageKey"],
                    "Summary"=>$row["Summary"],
                    "Content"=>$row["Content"],
                    "MemberOnly"=>$row["MemberOnly"]
                );

    }
   
    mysqli_stmt_close($stmt);
    
    return $result;
}

function SendEmail($to, $to_name, $subject, $body, $alt_body)
{
    $mail = new PHPMailer(true);

    try {
        global $EMAIL_SERVER;
        global $EMAIL_USER;
        global $EMAIL_PASSWORD;
        global $EMAIL_FROM;
        global $EMAIL_FROM_NAME;
        global $EMAIL_DEBUG;

        //Server settings
        if ($EMAIL_DEBUG) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        }

        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $EMAIL_SERVER;                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $EMAIL_USER;                     //SMTP username
        $mail->Password   = $EMAIL_PASSWORD;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom($EMAIL_FROM, $EMAIL_FROM_NAME);
        $mail->addAddress($to, $to_name);     //Add a recipient

        //$mail->addAddress('ellen@example.com');               //Name is optional
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');
    
        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body;
    
        $mail->send();
        if ($EMAIL_DEBUG) {
            echo 'Message has been sent';
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

