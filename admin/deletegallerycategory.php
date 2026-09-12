<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    if ($categoryId > 0) {
        $photos = ListPhotosByCategory($categoryId);
        try {
            DeleteGalleryCategoryById($categoryId);
            foreach ($photos as $p) {
                @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/' . $p['FileName']);
                @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/thumbs/' . $p['ThumbFileName']);
            }
        } catch (mysqli_sql_exception $e) {
            // DB delete failed - leave files alone
        }
    }
}

header("location: /admin/gallery");
exit;
