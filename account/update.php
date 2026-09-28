<?php
require($_SERVER['DOCUMENT_ROOT'] .'/includes/auth.inc.php');
RequireAuthentication();

$isError = false;
$msg = null;
$user = GetUserById($_SESSION['UserID']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $addressLine1 = $_POST['addressLine1'] ?? '';
        $addressLine2 = $_POST['addressLine2'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $zip = $_POST['zip'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if ($phone !== '' && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $isError = true;
            $msg = "Phone must be in the format XXX-XXX-XXXX.";
        } else {
            UpdateUserProfile($_SESSION['UserID'], $addressLine1, $addressLine2, $city, $state, $zip, $phone);
            $user = GetUserById($_SESSION['UserID']);
            $msg = "Your profile has been updated.";
        }
    }
}

require($_SERVER['DOCUMENT_ROOT'] .'/includes/header.php');
?>
<!--launch Section-->
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Update your account:</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form class="needs-validation" method="POST" action="/account/update" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <div class="form-group">
                        <label for="addressLine1">Address Line 1</label>
                        <input type="text" class="form-control" id="addressLine1" name="addressLine1" value="<?php echo htmlspecialchars($user['AddressLine1'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="addressLine2">Address Line 2</label>
                        <input type="text" class="form-control" id="addressLine2" name="addressLine2" value="<?php echo htmlspecialchars($user['AddressLine2'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['City'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" class="form-control" id="state" name="state" maxlength="2" value="<?php echo htmlspecialchars($user['State'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" name="zip" value="<?php echo htmlspecialchars($user['Zip'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="XXX-XXX-XXXX" pattern="\d{3}-\d{3}-\d{4}" value="<?php echo htmlspecialchars($user['Phone'] ?? '', ENT_QUOTES); ?>">
                        <small class="form-text text-muted">Format: XXX-XXX-XXXX</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] .'/includes/footer.php'); ?>
