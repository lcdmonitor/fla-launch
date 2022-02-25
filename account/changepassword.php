<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$isError = true;
$msg = null;
$isPost = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isPost=true;
        $currentPassword=$_POST["currentPassword"];
        $newPassword=$_POST["newPassword"];
        $newPasswordConfirm=$_POST["newPasswordConfirm"];

        if(!isset($currentPassword)|| !isset($newPassword)|| !isset($newPasswordConfirm))
        {
            die("required data not provided, either current or new passwords");
        }

        if(!ValidateLogin($_SESSION["Username"], $currentPassword))
        {
            $msg = "Current Password is incorrect";
        }
        else
        {
            //TODO VALIDATE AND set password AND add confirm validation to html/js
            $msg = "Password Changed Successfully";
            $isError = false;
        }
}

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
                <h1 class="launch__heading">Change your Password:</h1>
                <?php if($isError && $isPost) {?>
                    <div class="error-message"><?php echo $msg; ?></div><?php } else {?> 
                    <div class="success-message"><?php echo $msg; ?></div>
                <?php } ?>
                <!--TODO Update CSS Classes, these refer to launch page -->
                <form class="needs-validation" method="POST" action="changepassword">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" aria-describedby="currentPasswordHelp" required>
                        <small id="currentPasswordHelp" class="form-text text-muted">Enter your current password</small>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPasswordConfirm">Confirm Password</label>
                        <input type="password" class="form-control" id="newPasswordConfirm" name="newPasswordConfirm" aria-describedby="newPasswordConfirmHelp" required>
                        <small id="newPasswordConfirmHelp" class="form-text text-muted">Enter your new password again</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>