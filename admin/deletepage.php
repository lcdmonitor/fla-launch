<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
    $pageId = (int)($_POST['page_id'] ?? 0);
    if ($pageId > 0) {
        DeletePageById($pageId);
    }
}

header("location: /admin/pages");
exit;
