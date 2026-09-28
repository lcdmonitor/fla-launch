<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/functions.inc.php');
require($_SERVER['DOCUMENT_ROOT'] . '/includes/captcha.inc.php');
StartSecureSession();

$isError = false;
$msg = null;
$firstName = '';
$lastName = '';
$username = '';
$email = '';
$addressLine1 = '';
$addressLine2 = '';
$city = '';
$state = '';
$zip = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $addressLine1 = $_POST['addressLine1'] ?? '';
    $addressLine2 = $_POST['addressLine2'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip'] ?? '';

    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } elseif (!ValidateCaptchaAnswer($_POST['captcha'] ?? '')) {
        $isError = true;
        $msg = "The CAPTCHA answer was incorrect. Please try again.";
    } else {
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['passwordConfirm'] ?? '';

        if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $addressLine1 === '') {
            $isError = true;
            $msg = "First Name, Last Name, Username, Email, and Address Line 1 are required.";
        } elseif ($password !== $passwordConfirm) {
            $isError = true;
            $msg = "Password and confirmation do not match.";
        } elseif (!IsGoodPassword($password)) {
            $isError = true;
            $msg = "Password must be 12 or more characters in length.";
        } else {
            $fullname = trim("$firstName $lastName");
            try {
                CreateUser($username, $fullname, $email, $password, ROLE_MEMBER);
                $newUser = GetUserByEmail($email);
                UpdateUserProfile($newUser['UserID'], $addressLine1, $addressLine2, $city, $state, $zip, '');

                session_regenerate_id(true);
                $_SESSION['UserID'] = $newUser['UserID'];
                $_SESSION['FullName'] = $fullname;
                $_SESSION['RoleID'] = ROLE_MEMBER;
                $_SESSION['Username'] = $username;

                header("location: /");
                exit;
            } catch (mysqli_sql_exception $e) {
                $isError = true;
                $msg = ($e->getCode() === 1062)
                    ? "That username or email is already registered."
                    : "Error creating account.";
            }
        }
    }
}

require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Register</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form class="needs-validation" method="POST" action="/register" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">

                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required value="<?php echo htmlspecialchars($firstName, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required value="<?php echo htmlspecialchars($lastName, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">12 or more characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="passwordConfirm">Confirm Password</label>
                        <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm" required>
                    </div>

                    <div class="form-group">
                        <label for="addressLine1">Address Line 1</label>
                        <input type="text" class="form-control" id="addressLine1" name="addressLine1" required value="<?php echo htmlspecialchars($addressLine1, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="addressLine2">Address Line 2</label>
                        <input type="text" class="form-control" id="addressLine2" name="addressLine2" value="<?php echo htmlspecialchars($addressLine2, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" class="form-control" id="state" name="state" maxlength="2" value="<?php echo htmlspecialchars($state, ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" name="zip" value="<?php echo htmlspecialchars($zip, ENT_QUOTES); ?>">
                    </div>

                    <div class="form-group">
                        <label for="captcha">Enter the numbers shown below</label><br>
                        <img src="/captcha.php" alt="CAPTCHA" id="captchaImage">
                        <a href="#" onclick="refreshCaptcha('captchaImage'); return false;">Refresh</a>
                        <input type="text" class="form-control" id="captcha" name="captcha" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
