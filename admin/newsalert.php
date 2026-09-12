<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$isError = false;
$msg = null;
$content = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $content = $_POST['content'] ?? '';
        try {
            UpdateNewsAlertContent($content);
            $msg = "Saved successfully.";
        } catch (mysqli_sql_exception $e) {
            $isError = true;
            $msg = "Error saving content.";
        }
    }
}

if ($content === null) {
    $content = GetNewsAlertContent();
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
                <h1 class="launch__heading">News Alert</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form id="newsAlertForm" method="POST" action="/admin/newsalert">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <div class="form-group">
                        <label for="content">Content</label>
                        <small class="form-text text-muted d-block">When inserting an image, leave the width/height fields blank.</small>
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sceditor/3.2.1/themes/default.min.css">
                        <textarea id="content" name="content" class="form-control" rows="12"><?php echo htmlspecialchars($content ?: '', ENT_QUOTES); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a class="btn btn-secondary" href="/admin">Back to Admin Dashboard</a>
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
    document.getElementById('newsAlertForm').addEventListener('submit', function () {
        sceditor.instance(contentTextarea).updateOriginal();
    });
</script>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
