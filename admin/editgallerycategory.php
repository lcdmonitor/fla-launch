<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$category = $categoryId ? GetGalleryCategoryById($categoryId) : null;
$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $name = $_POST['name'] ?? '';
        $sortOrder = isset($_POST['sortOrder']) ? (int)$_POST['sortOrder'] : 0;
        $hidden = isset($_POST['hidden']) ? 1 : 0;
        $postedCategoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        if ($name === '') {
            $isError = true;
            $msg = "Name is required.";
            $category = ["Name"=>$name, "SortOrder"=>$sortOrder, "Hidden"=>$hidden];
            $categoryId = $postedCategoryId ?: null;
        } else {
            try {
                if ($postedCategoryId > 0) {
                    UpdateGalleryCategoryById($postedCategoryId, $name, $sortOrder, $hidden);
                } else {
                    CreateGalleryCategory($name, $sortOrder, $hidden);
                }
                header("location: /admin/gallery");
                exit;
            } catch (mysqli_sql_exception $e) {
                $isError = true;
                $msg = "Error saving category.";
                $category = ["Name"=>$name, "SortOrder"=>$sortOrder, "Hidden"=>$hidden];
                $categoryId = $postedCategoryId ?: null;
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
                <h1 class="launch__heading"><?php echo $categoryId ? 'Edit Category' : 'New Category'; ?></h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form method="POST" action="/admin/editgallerycategory">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="category_id" value="<?php echo $categoryId ?? ''; ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($category['Name'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sortOrder">Sort Order</label>
                        <input type="number" class="form-control" id="sortOrder" name="sortOrder" value="<?php echo (int)($category['SortOrder'] ?? 0); ?>">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="hidden" name="hidden" <?php echo !empty($category['Hidden']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="hidden">Hidden</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a class="btn btn-secondary" href="/admin/gallery">Back to Categories</a>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
