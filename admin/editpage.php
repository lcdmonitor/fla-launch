<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$pageId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page = $pageId ? GetPageById($pageId) : null;
$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $title = $_POST['title'] ?? '';
        $pageKey = $_POST['pageKey'] ?? '';
        $summary = $_POST['summary'] ?? '';
        $content = $_POST['content'] ?? '';
        $memberOnly = isset($_POST['memberOnly']) ? 1 : 0;
        $postedPageId = isset($_POST['page_id']) ? (int)$_POST['page_id'] : 0;

        if ($title === '' || $pageKey === '' || $summary === '') {
            $isError = true;
            $msg = "Title, Page Key, and Summary are required.";
            $page = ["Title"=>$title, "PageKey"=>$pageKey, "Summary"=>$summary, "Content"=>$content, "MemberOnly"=>$memberOnly];
            $pageId = $postedPageId ?: null;
        } else {
            try {
                if ($postedPageId > 0) {
                    UpdatePageById($postedPageId, $title, $pageKey, $summary, $content, $memberOnly);
                } else {
                    CreatePage($title, $pageKey, $summary, $content, $memberOnly);
                }
                header("location: /admin/pages");
                exit;
            } catch (mysqli_sql_exception $e) {
                $isError = true;
                $msg = ($e->getCode() === 1062)
                    ? "That Page Key is already in use by another page."
                    : "Error saving page.";
                $page = ["Title"=>$title, "PageKey"=>$pageKey, "Summary"=>$summary, "Content"=>$content, "MemberOnly"=>$memberOnly];
                $pageId = $postedPageId ?: null;
            }
        }
    }
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
                <h1 class="launch__heading"><?php echo $pageId ? 'Edit Page' : 'New Page'; ?></h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form id="editPageForm" method="POST" action="/admin/editpage">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="page_id" value="<?php echo $pageId ?? ''; ?>">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($page['Title'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="pageKey">Page Key</label>
                        <input type="text" class="form-control" id="pageKey" name="pageKey" required value="<?php echo htmlspecialchars($page['PageKey'] ?? '', ENT_QUOTES); ?>">
                        <small class="form-text text-muted">Used in the page's URL: /pages/&lt;page key&gt;</small>
                    </div>
                    <div class="form-group">
                        <label for="summary">Summary</label>
                        <input type="text" class="form-control" id="summary" name="summary" required value="<?php echo htmlspecialchars($page['Summary'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="memberOnly" name="memberOnly" <?php echo !empty($page['MemberOnly']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="memberOnly">Members Only</label>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <small class="form-text text-muted d-block">When inserting an image, leave the width/height fields blank.</small>
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/themes/default.min.css">
                        <textarea id="content" name="content" class="form-control" rows="12"><?php echo htmlspecialchars($page['Content'] ?? '', ENT_QUOTES); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a class="btn btn-secondary" href="/admin/pages">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/sceditor.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/formats/bbcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/icons/monocons.min.js"></script>
<script>
    var contentTextarea = document.getElementById('content');
    sceditor.create(contentTextarea, {
        format: 'bbcode',
        icons: 'monocons',
        style: 'https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/themes/content/default.min.css',
        toolbar: 'bold,italic,underline|color|link,image'
    });
    document.getElementById('editPageForm').addEventListener('submit', function () {
        sceditor.instance(contentTextarea).updateOriginal();
    });
</script>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
