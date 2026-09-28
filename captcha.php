<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/functions.inc.php');
require($_SERVER['DOCUMENT_ROOT'] . '/includes/captcha.inc.php');
StartSecureSession();
RenderCaptchaImage(GenerateCaptchaAnswer());
