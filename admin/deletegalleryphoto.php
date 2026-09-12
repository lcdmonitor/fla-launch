<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$redirectTarget = "/admin/gallery";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
    $photoId = (int)($_POST['photo_id'] ?? 0);
    if ($photoId > 0) {
        $photo = GetGalleryPhotoById($photoId);
        if ($photo) {
            $redirectTarget = "/admin/gallery-photos?category=" . (int)$photo['CategoryID'];
            try {
                DeleteGalleryPhotoById($photoId);
                @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/' . $photo['FileName']);
                @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/thumbs/' . $photo['ThumbFileName']);
            } catch (mysqli_sql_exception $e) {
                // DB delete failed - leave files alone
            }
        }
    }
}

header("location: " . $redirectTarget);
exit;
