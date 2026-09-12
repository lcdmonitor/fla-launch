<?php
function GetIsUserLoggedIn()
{
    if (isset($_SESSION["UserID"])) {
        return true;
    } else {
        return false;
    }
}

function StartSecureSession()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', '1800'); // MAMP's php.ini ships 1440s, less than our 30min app timeout
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    $idleTimeoutSeconds = 1800; // 30 minutes

    if (isset($_SESSION['LastActivity']) && (time() - $_SESSION['LastActivity']) > $idleTimeoutSeconds) {
        session_unset();
        session_destroy();
        return;
    }

    $_SESSION['LastActivity'] = time();
}

function GenerateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function ValidateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function RecordLoginAttempt($username, $ipAddress, $successful)
{
    $mysqli = GetDBConnection();

    $sql = "INSERT INTO LoginAttempt (Username, IPAddress, Successful) VALUES (?, ?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed");
    }

    $successfulInt = $successful ? 1 : 0;

    mysqli_stmt_bind_param($stmt, 'ssi', $username, $ipAddress, $successfulInt);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function IsLoginLocked($username, $ipAddress)
{
    $mysqli = GetDBConnection();

    $sql = "SELECT COUNT(*) AS FailedCount FROM LoginAttempt
            WHERE Successful = 0
              AND AttemptTime > (NOW() - INTERVAL 15 MINUTE)
              AND (Username = ? OR IPAddress = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed");
    }

    mysqli_stmt_bind_param($stmt, 'ss', $username, $ipAddress);
    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return ((int)$row['FailedCount']) >= 5;
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

    if (!IsGoodPassword($password)) {
        die("Error: password does not meet minimum requirements (12+ characters)");
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
    } else {
        // Run password_verify against a fixed, non-secret dummy hash so response time
        // doesn't reveal whether the username exists (mitigates timing-based enumeration).
        static $dummyHash = '$2y$10$ZxQR3VYmKG12R48V8zFhzORPh42IFAP50uEDTRLlJ3nP9wle/bADG';
        password_verify($password, $dummyHash);
    }

    mysqli_stmt_close($stmt);
    
    return $result;
}

function IsGoodPassword($password)
{
    return strlen($password) >= 12;
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
        $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
        //$mail->Username   = $EMAIL_USER;                     //SMTP username
        //$mail->Password   = $EMAIL_PASSWORD;                               //SMTP password
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->SMTPAutoTLS = false; //TODO fix hacks and make secure
        $mail->SMTPSecure  = false;
        $mail->Port       = 25;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
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

