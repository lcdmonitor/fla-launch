<?php

function ValidateAndStoreUploadedPhoto($tmpName, $sizeBytes)
{
    if ($sizeBytes > 10 * 1024 * 1024) {
        throw new Exception("File is too large (10MB max).");
    }

    $info = getimagesize($tmpName);

    if ($info === false) {
        throw new Exception("Not a valid image file.");
    }

    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    if (!in_array($info[2], $allowedTypes, true)) {
        throw new Exception("Unsupported image type (JPEG, PNG, GIF, and WEBP only).");
    }

    if ($info[0] * $info[1] > 50000000) {
        throw new Exception("Image resolution is too large.");
    }

    $ext = image_type_to_extension($info[2], false);

    $galleryDir = $_SERVER['DOCUMENT_ROOT'] . '/img/gallery';
    $thumbDir = $galleryDir . '/thumbs';

    if (!is_dir($galleryDir)) {
        mkdir($galleryDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    $randomName = bin2hex(random_bytes(16));
    $fileName = $randomName . '.' . $ext;
    $thumbFileName = $randomName . '.' . $ext;

    $sourcePath = $galleryDir . '/' . $fileName;
    $thumbPath = $thumbDir . '/' . $thumbFileName;

    if (!move_uploaded_file($tmpName, $sourcePath)) {
        throw new Exception("Could not save uploaded file.");
    }

    GenerateGalleryThumbnail($sourcePath, $thumbPath, $info[2]);

    return array("fileName" => $fileName, "thumbFileName" => $thumbFileName);
}

function GenerateGalleryThumbnail($sourcePath, $thumbPath, $imageType)
{
    $priorMemoryLimit = ini_get('memory_limit');
    ini_set('memory_limit', '512M');

    $src = match ($imageType) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
        IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
        IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
        IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
        default => false,
    };

    if ($src === false) {
        ini_set('memory_limit', $priorMemoryLimit);
        throw new Exception("Could not process image.");
    }

    if ($imageType === IMAGETYPE_JPEG) {
        $exif = @exif_read_data($sourcePath);
        $angles = [3 => 180, 6 => -90, 8 => 90];
        if ($exif && !empty($exif['Orientation']) && isset($angles[$exif['Orientation']])) {
            $rotated = imagerotate($src, $angles[$exif['Orientation']], 0);
            if ($rotated !== false) {
                imagedestroy($src);
                $src = $rotated;
            }
        }
    }

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $thumbW = 400;

    if ($srcW <= $thumbW) {
        copy($sourcePath, $thumbPath);
        imagedestroy($src);
        ini_set('memory_limit', $priorMemoryLimit);
        return;
    }

    $thumbH = (int)round($srcH * ($thumbW / $srcW));

    $dst = imagecreatetruecolor($thumbW, $thumbH);

    if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    } elseif ($imageType === IMAGETYPE_GIF) {
        $transparentIndex = imagecolortransparent($src);
        if ($transparentIndex >= 0) {
            $rgb = imagecolorsforindex($src, $transparentIndex);
            $t = imagecolorallocate($dst, $rgb['red'], $rgb['green'], $rgb['blue']);
            imagefill($dst, 0, 0, $t);
            imagecolortransparent($dst, $t);
        }
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumbW, $thumbH, $srcW, $srcH);

    match ($imageType) {
        IMAGETYPE_JPEG => imagejpeg($dst, $thumbPath, 85),
        IMAGETYPE_PNG => imagepng($dst, $thumbPath),
        IMAGETYPE_GIF => imagegif($dst, $thumbPath),
        IMAGETYPE_WEBP => imagewebp($dst, $thumbPath, 85),
    };

    imagedestroy($dst);
    imagedestroy($src);
    ini_set('memory_limit', $priorMemoryLimit);
}
