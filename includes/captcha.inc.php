<?php

function GenerateCaptchaAnswer()
{
    $answer = (string)random_int(10000, 99999);
    $_SESSION['captcha_answer'] = $answer;
    return $answer;
}

function RenderCaptchaImage($text)
{
    $width = 150;
    $height = 50;
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 235, 235, 235));

    for ($i = 0; $i < 8; $i++) {
        $lineColor = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
        imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
    }

    $len = strlen($text);
    $spacing = intdiv($width, $len + 1);
    for ($i = 0; $i < $len; $i++) {
        $charColor = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
        imagestring($image, 5, $spacing * ($i + 1) - 6, rand(10, 20), $text[$i], $charColor);
    }

    for ($i = 0; $i < 100; $i++) {
        $dotColor = imagecolorallocate($image, rand(150, 220), rand(150, 220), rand(150, 220));
        imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
    }

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    imagepng($image);
    imagedestroy($image);
}

function ValidateCaptchaAnswer($submitted)
{
    $expected = $_SESSION['captcha_answer'] ?? null;
    unset($_SESSION['captcha_answer']);
    return $expected !== null && hash_equals((string)$expected, (string)$submitted);
}
