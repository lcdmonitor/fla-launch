<?php
const ROLE_ADMIN = 1;
const ROLE_MEMBER = 2;

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

function ListPages()
{
    $mysqli = GetDBConnection();

    $query = "SELECT PageID, PageKey, Summary, MemberOnly FROM Page";

    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    $pages = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $pages[] = $row;
    }

    return $pages;
}

function GetPageById($pageId)
{

    if (!isset($pageId)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT Title, PageKey, Summary, Content, MemberOnly FROM Page WHERE (PageID = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $pageId);

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

function CreatePage($title, $pageKey, $summary, $content, $memberOnly)
{
    $mysqli = GetDBConnection();

    $sql = "INSERT INTO Page (Title, PageKey, Summary, Content, MemberOnly) VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    $memberOnlyInt = $memberOnly ? 1 : 0;

    mysqli_stmt_bind_param($stmt, 'ssssi', $title, $pageKey, $summary, $content, $memberOnlyInt);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function UpdatePageById($pageId, $title, $pageKey, $summary, $content, $memberOnly)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE Page SET Title = ?, PageKey = ?, Summary = ?, Content = ?, MemberOnly = ? WHERE PageID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    $memberOnlyInt = $memberOnly ? 1 : 0;

    mysqli_stmt_bind_param($stmt, 'ssssii', $title, $pageKey, $summary, $content, $memberOnlyInt, $pageId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function DeletePageById($pageId)
{
    $mysqli = GetDBConnection();

    $sql = "DELETE FROM Page WHERE PageID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $pageId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function ListGalleryCategories()
{
    $mysqli = GetDBConnection();

    $query = "SELECT CategoryID, Name, SortOrder, Hidden FROM GalleryCategory ORDER BY SortOrder";

    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    $categories = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    return $categories;
}

function ListVisibleGalleryCategories()
{
    $mysqli = GetDBConnection();

    $query = "SELECT CategoryID, Name, SortOrder, Hidden FROM GalleryCategory WHERE Hidden = 0 ORDER BY SortOrder";

    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    $categories = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    return $categories;
}

function GetGalleryCategoryById($categoryId)
{

    if (!isset($categoryId)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT CategoryID, Name, SortOrder, Hidden FROM GalleryCategory WHERE (CategoryID = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $categoryId);

    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    $result = false;

    if ($row = mysqli_fetch_assoc($resultData)) {
        $result = array(
                    "CategoryID"=>$row["CategoryID"],
                    "Name"=>$row["Name"],
                    "SortOrder"=>$row["SortOrder"],
                    "Hidden"=>$row["Hidden"]
                );
    }

    mysqli_stmt_close($stmt);

    return $result;
}

function CreateGalleryCategory($name, $sortOrder, $hidden)
{
    $mysqli = GetDBConnection();

    $sql = "INSERT INTO GalleryCategory (Name, SortOrder, Hidden) VALUES (?, ?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    $hiddenInt = $hidden ? 1 : 0;

    mysqli_stmt_bind_param($stmt, 'sii', $name, $sortOrder, $hiddenInt);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function UpdateGalleryCategoryById($categoryId, $name, $sortOrder, $hidden)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE GalleryCategory SET Name = ?, SortOrder = ?, Hidden = ? WHERE CategoryID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    $hiddenInt = $hidden ? 1 : 0;

    mysqli_stmt_bind_param($stmt, 'siii', $name, $sortOrder, $hiddenInt, $categoryId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function DeleteGalleryCategoryById($categoryId)
{
    $mysqli = GetDBConnection();

    $sql = "DELETE FROM GalleryCategory WHERE CategoryID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function ListPhotosByCategory($categoryId)
{
    $mysqli = GetDBConnection();

    $sql = "SELECT PhotoID, CategoryID, FileName, ThumbFileName, SortOrder FROM GalleryPhoto WHERE CategoryID = ? ORDER BY SortOrder";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    $photos = array();

    while ($row = mysqli_fetch_assoc($resultData)) {
        $photos[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $photos;
}

function GetGalleryPhotoById($photoId)
{

    if (!isset($photoId)) {
        die("Error missing parameter value");
    }

    $mysqli = GetDBConnection();

    $sql = "SELECT PhotoID, CategoryID, FileName, ThumbFileName, SortOrder FROM GalleryPhoto WHERE (PhotoID = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $photoId);

    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    $result = false;

    if ($row = mysqli_fetch_assoc($resultData)) {
        $result = array(
                    "PhotoID"=>$row["PhotoID"],
                    "CategoryID"=>$row["CategoryID"],
                    "FileName"=>$row["FileName"],
                    "ThumbFileName"=>$row["ThumbFileName"],
                    "SortOrder"=>$row["SortOrder"]
                );
    }

    mysqli_stmt_close($stmt);

    return $result;
}

function CreateGalleryPhoto($categoryId, $fileName, $thumbFileName)
{
    $mysqli = GetDBConnection();

    $sortSql = "SELECT COALESCE(MAX(SortOrder), -1) + 1 AS NextSortOrder FROM GalleryPhoto WHERE CategoryID = ?";
    $sortStmt = mysqli_stmt_init($mysqli);
    if (!mysqli_stmt_prepare($sortStmt, $sortSql)) {
        die("Error: Statement Failed to Prepare");
    }
    mysqli_stmt_bind_param($sortStmt, 'i', $categoryId);
    mysqli_stmt_execute($sortStmt);
    $sortResult = mysqli_fetch_assoc(mysqli_stmt_get_result($sortStmt));
    mysqli_stmt_close($sortStmt);
    $nextSortOrder = (int)$sortResult['NextSortOrder'];

    $sql = "INSERT INTO GalleryPhoto (CategoryID, FileName, ThumbFileName, SortOrder) VALUES (?, ?, ?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'issi', $categoryId, $fileName, $thumbFileName, $nextSortOrder);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function UpdateGalleryCategorySortOrder($categoryId, $sortOrder)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE GalleryCategory SET SortOrder = ? WHERE CategoryID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'ii', $sortOrder, $categoryId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function UpdatePhotoSortOrder($photoId, $sortOrder)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE GalleryPhoto SET SortOrder = ? WHERE PhotoID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'ii', $sortOrder, $photoId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function DeleteGalleryPhotoById($photoId)
{
    $mysqli = GetDBConnection();

    $sql = "DELETE FROM GalleryPhoto WHERE PhotoID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $photoId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function GetNewsAlertContent()
{
    $mysqli = GetDBConnection();

    $query = "SELECT Content FROM NewsAlert WHERE NewsAlertID = 1";

    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    $row = mysqli_fetch_assoc($result);

    return $row ? $row['Content'] : false;
}

function UpdateNewsAlertContent($content)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE NewsAlert SET Content = ? WHERE NewsAlertID = 1";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 's', $content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function RecordPageHit()
{
    $mysqli = GetDBConnection();

    $userId = isset($_SESSION['UserID']) ? (int)$_SESSION['UserID'] : null;
    $username = $_SESSION['Username'] ?? null;
    $requestUrl = $_SERVER['REQUEST_URI'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $sql = "INSERT INTO PageHit (UserID, Username, RequestUrl, Referrer, IPAddress, UserAgent) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'isssss', $userId, $username, $requestUrl, $referrer, $ipAddress, $userAgent);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function GetPageHitCountByDay($days)
{
    $mysqli = GetDBConnection();

    if ($days > 0) {
        $sql = "SELECT DATE(HitTime) AS HitDate, COUNT(*) AS HitCount FROM PageHit WHERE HitTime >= (NOW() - INTERVAL ? DAY) GROUP BY HitDate ORDER BY HitDate DESC";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
        mysqli_stmt_bind_param($stmt, 'i', $days);
    } else {
        $sql = "SELECT DATE(HitTime) AS HitDate, COUNT(*) AS HitCount FROM PageHit GROUP BY HitDate ORDER BY HitDate DESC";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
    }

    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $rows = array();
    while ($row = mysqli_fetch_assoc($resultData)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

function GetPageHitCountByUser($days)
{
    $mysqli = GetDBConnection();

    if ($days > 0) {
        $sql = "SELECT COALESCE(Username, 'Guest') AS UserLabel, COUNT(*) AS HitCount FROM PageHit WHERE HitTime >= (NOW() - INTERVAL ? DAY) GROUP BY UserLabel ORDER BY HitCount DESC";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
        mysqli_stmt_bind_param($stmt, 'i', $days);
    } else {
        $sql = "SELECT COALESCE(Username, 'Guest') AS UserLabel, COUNT(*) AS HitCount FROM PageHit GROUP BY UserLabel ORDER BY HitCount DESC";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
    }

    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $rows = array();
    while ($row = mysqli_fetch_assoc($resultData)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

function GetPageHitCountByUserAgent($days)
{
    $mysqli = GetDBConnection();

    if ($days > 0) {
        $sql = "SELECT COALESCE(UserAgent, 'Unknown') AS UserAgentLabel, COUNT(*) AS HitCount FROM PageHit WHERE HitTime >= (NOW() - INTERVAL ? DAY) GROUP BY UserAgentLabel ORDER BY HitCount DESC LIMIT 20";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
        mysqli_stmt_bind_param($stmt, 'i', $days);
    } else {
        $sql = "SELECT COALESCE(UserAgent, 'Unknown') AS UserAgentLabel, COUNT(*) AS HitCount FROM PageHit GROUP BY UserAgentLabel ORDER BY HitCount DESC LIMIT 20";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
    }

    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $rows = array();
    while ($row = mysqli_fetch_assoc($resultData)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

function GetPageHitCountByReferrer($days)
{
    $mysqli = GetDBConnection();

    if ($days > 0) {
        $sql = "SELECT COALESCE(Referrer, 'Direct / None') AS ReferrerLabel, COUNT(*) AS HitCount FROM PageHit WHERE HitTime >= (NOW() - INTERVAL ? DAY) GROUP BY ReferrerLabel ORDER BY HitCount DESC LIMIT 20";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
        mysqli_stmt_bind_param($stmt, 'i', $days);
    } else {
        $sql = "SELECT COALESCE(Referrer, 'Direct / None') AS ReferrerLabel, COUNT(*) AS HitCount FROM PageHit GROUP BY ReferrerLabel ORDER BY HitCount DESC LIMIT 20";
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("Error: Statement Failed to Prepare");
        }
    }

    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $rows = array();
    while ($row = mysqli_fetch_assoc($resultData)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

function GetRecentPageHits($limit)
{
    $mysqli = GetDBConnection();

    $sql = "SELECT HitTime, UserID, Username, RequestUrl, Referrer, IPAddress, UserAgent FROM PageHit ORDER BY HitTime DESC LIMIT ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $rows = array();
    while ($row = mysqli_fetch_assoc($resultData)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

function GetUserByEmail($email)
{
    $mysqli = GetDBConnection();

    $sql = "SELECT UserID, Username, Email FROM User WHERE (Email = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $result = false;

    if ($row = mysqli_fetch_assoc($resultData)) {
        $result = array(
                    "UserID"=>$row["UserID"],
                    "Username"=>$row["Username"],
                    "Email"=>$row["Email"]
                );
    }

    mysqli_stmt_close($stmt);

    return $result;
}

function CreatePasswordResetToken($userId)
{
    $mysqli = GetDBConnection();

    $invalidateSql = "UPDATE PasswordReset SET UsedDate = NOW() WHERE UserID = ? AND UsedDate IS NULL";
    $invalidateStmt = mysqli_stmt_init($mysqli);
    if (!mysqli_stmt_prepare($invalidateStmt, $invalidateSql)) {
        die("Error: Statement Failed to Prepare");
    }
    mysqli_stmt_bind_param($invalidateStmt, 'i', $userId);
    mysqli_stmt_execute($invalidateStmt);
    mysqli_stmt_close($invalidateStmt);

    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $sql = "INSERT INTO PasswordReset (UserID, TokenHash, ExpiresDate) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'is', $userId, $tokenHash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $rawToken;
}

function ValidatePasswordResetToken($token)
{
    $mysqli = GetDBConnection();

    $tokenHash = hash('sha256', $token);

    $sql = "SELECT pr.ResetID, u.Username FROM PasswordReset pr
            JOIN User u ON u.UserID = pr.UserID
            WHERE pr.TokenHash = ? AND pr.UsedDate IS NULL AND pr.ExpiresDate > NOW()";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 's', $tokenHash);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    $result = false;

    if ($row = mysqli_fetch_assoc($resultData)) {
        $result = array(
                    "ResetID"=>$row["ResetID"],
                    "Username"=>$row["Username"]
                );
    }

    mysqli_stmt_close($stmt);

    return $result;
}

function MarkPasswordResetTokenUsed($resetId)
{
    $mysqli = GetDBConnection();

    $sql = "UPDATE PasswordReset SET UsedDate = NOW() WHERE ResetID = ?";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'i', $resetId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function RecordPasswordResetAttempt($email, $ipAddress)
{
    $mysqli = GetDBConnection();

    $sql = "INSERT INTO PasswordResetAttempt (Email, IPAddress) VALUES (?, ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'ss', $email, $ipAddress);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function IsPasswordResetLocked($email, $ipAddress)
{
    $mysqli = GetDBConnection();

    $sql = "SELECT COUNT(*) AS AttemptCount FROM PasswordResetAttempt
            WHERE AttemptTime > (NOW() - INTERVAL 15 MINUTE)
              AND (Email = ? OR IPAddress = ?)";

    $stmt = mysqli_stmt_init($mysqli);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("Error: Statement Failed to Prepare");
    }

    mysqli_stmt_bind_param($stmt, 'ss', $email, $ipAddress);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return ((int)$row['AttemptCount']) >= 3;
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
        $mail->SMTPAuth   = true;                                    //Enable SMTP authentication
        $mail->Username   = $EMAIL_USER;                     //SMTP username
        $mail->Password   = $EMAIL_PASSWORD;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable STARTTLS encryption
        $mail->SMTPAutoTLS = true;
        $mail->Port       = 587;                                    //TCP port to connect to; 587 for STARTTLS
        $mail->Timeout    = 10;                                     //Fail fast rather than hanging until the web server's own request timeout kills the process uncatchably
    
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
        return true;
    } catch (Exception $e) {
        error_log("SendEmail failed: " . $mail->ErrorInfo);
        return false;
    }
}

