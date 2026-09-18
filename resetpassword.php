<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/functions.inc.php');
StartSecureSession();

$isError = false;
$msg = null;
$tokenValid = false;
$token = $_POST['token'] ?? ($_GET['token'] ?? '');

$resetInfo = $token !== '' ? ValidatePasswordResetToken($token) : false;

if (!$resetInfo) {
    $isError = true;
    $msg = "This password reset link is invalid or has expired. Please request a new one.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
        $tokenValid = true;
    } else {
        $newPassword = $_POST['newPassword'] ?? '';
        $newPasswordConfirm = $_POST['newPasswordConfirm'] ?? '';

        if ($newPassword !== $newPasswordConfirm) {
            $isError = true;
            $msg = "Password and confirmation do not match.";
            $tokenValid = true;
        } elseif (!IsGoodPassword($newPassword)) {
            $isError = true;
            $msg = "New password must be 12 or more characters in length.";
            $tokenValid = true;
        } else {
            ChangePassword($resetInfo['Username'], $newPassword);
            MarkPasswordResetTokenUsed($resetInfo['ResetID']);
            $msg = "Your password has been reset. You can now log in.";
        }
    }
} else {
    $tokenValid = true;
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
                <h1 class="launch__heading">Reset Password</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <?php if ($tokenValid) { ?>
                    <form class="needs-validation" method="POST" action="/resetpassword" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="newPasswordConfirm">Confirm New Password</label>
                            <input type="password" class="form-control" id="newPasswordConfirm" name="newPasswordConfirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </form>
                <?php } else { ?>
                    <a class="btn btn-primary" href="/forgotpassword">Request a New Link</a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
