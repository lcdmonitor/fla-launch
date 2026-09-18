<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'); ?>
<?php

$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $email = $_POST['email'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($email !== '' && !IsPasswordResetLocked($email, $ipAddress)) {
            $user = GetUserByEmail($email);
            if ($user) {
                $token = CreatePasswordResetToken($user['UserID']);
                $resetLink = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
                    . '://' . $_SERVER['HTTP_HOST'] . '/resetpassword?token=' . $token;
                SendEmail(
                    $user['Email'],
                    $user['Username'],
                    'Password Reset Request',
                    "Click the link below to reset your password. This link expires in 1 hour.<br><a href=\"$resetLink\">$resetLink</a>",
                    "Reset your password: $resetLink"
                );
            }
        }

        if ($email !== '') {
            RecordPasswordResetAttempt($email, $ipAddress);
        }

        $msg = "If an account exists for that email address, a password reset link has been sent.";
    }
}
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Forgot Password</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } else { ?>
                    <p>Enter the email address associated with your account and we'll send you a link to reset your password.</p>
                <?php } ?>
                <form class="needs-validation" method="POST" action="/forgotpassword" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
