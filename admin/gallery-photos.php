<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
require($_SERVER['DOCUMENT_ROOT'] . '/includes/gallery-upload.inc.php');

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$category = $categoryId ? GetGalleryCategoryById($categoryId) : null;

if (!$category) {
    header("location: /admin/gallery");
    exit;
}

$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $isError = true;
    $msg = "Upload too large - try fewer photos per batch.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'reorder') {
            $orderedIds = explode(',', $_POST['ordered_ids'] ?? '');
            $index = 0;
            try {
                foreach ($orderedIds as $idValue) {
                    $photoId = (int)$idValue;
                    if ($photoId > 0) {
                        UpdatePhotoSortOrder($photoId, $index);
                        $index++;
                    }
                }
                header("location: /admin/gallery-photos?category=" . $categoryId);
                exit;
            } catch (mysqli_sql_exception $e) {
                $isError = true;
                $msg = "Error saving photo order.";
            }
        } elseif ($action === 'upload') {
            $errors = array();
            $successCount = 0;
            $count = isset($_FILES['photos']['name']) ? count($_FILES['photos']['name']) : 0;

            for ($i = 0; $i < $count; $i++) {
                $error = $_FILES['photos']['error'][$i];
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($error !== UPLOAD_ERR_OK) {
                    $errors[] = "File " . ($i + 1) . " failed to upload.";
                    continue;
                }
                try {
                    $stored = ValidateAndStoreUploadedPhoto($_FILES['photos']['tmp_name'][$i], (int)$_FILES['photos']['size'][$i]);
                    try {
                        CreateGalleryPhoto($categoryId, $stored['fileName'], $stored['thumbFileName']);
                        $successCount++;
                    } catch (mysqli_sql_exception $e) {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/' . $stored['fileName']);
                        @unlink($_SERVER['DOCUMENT_ROOT'] . '/img/gallery/thumbs/' . $stored['thumbFileName']);
                        $errors[] = "Could not save a photo record.";
                    }
                } catch (Exception $e) {
                    $errors[] = htmlspecialchars($_FILES['photos']['name'][$i], ENT_QUOTES) . ": " . $e->getMessage();
                }
            }

            if (count($errors) > 0) {
                $isError = true;
                $msg = $successCount . " photo(s) uploaded. " . count($errors) . " failed: " . implode(" ", $errors);
            } else {
                $msg = $successCount . " photo(s) uploaded successfully.";
            }
        }
    }
}

$photos = ListPhotosByCategory($categoryId);

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
                <h1 class="launch__heading">Manage Photos: <?php echo htmlspecialchars($category['Name'], ENT_QUOTES); ?></h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo $msg; ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo $msg; ?></div>
                    <?php } ?>
                <?php } ?>

                <h2 class="h4">Upload Photos</h2>
                <form method="POST" action="/admin/gallery-photos?category=<?php echo $categoryId; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="action" value="upload">
                    <div class="form-group">
                        <label for="photos">Photos</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                        <small class="form-text text-muted">JPEG, PNG, GIF, or WEBP. 10MB max per photo, up to 20 photos per upload.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>

                <hr>

                <h2 class="h4">Reorder Photos</h2>
                <?php if (count($photos) === 0) { ?>
                    <p>No photos in this category yet.</p>
                <?php } else { ?>
                    <form id="reorderForm" method="POST" action="/admin/gallery-photos?category=<?php echo $categoryId; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                        <input type="hidden" name="action" value="reorder">
                        <input type="hidden" name="ordered_ids" id="orderedIds" value="">
                        <div id="photoSortList" class="d-flex flex-wrap gap-3 mb-3">
                            <?php foreach ($photos as $photo) { ?>
                                <div class="sortable-item card p-2" data-photo-id="<?php echo (int)$photo['PhotoID']; ?>" style="width: 180px; cursor: move;">
                                    <img src="/img/gallery/thumbs/<?php echo htmlspecialchars($photo['ThumbFileName'], ENT_QUOTES); ?>" class="card-img-top" alt="">
                                    <button type="button" class="btn btn-sm btn-danger mt-2"
                                            onclick="confirmDeleteGalleryPhoto(<?php echo (int)$photo['PhotoID']; ?>)">Delete</button>
                                </div>
                            <?php } ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Order</button>
                    </form>
                <?php } ?>

                <form id="deleteGalleryPhotoForm" method="POST" action="/admin/deletegalleryphoto" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="photo_id" id="deleteGalleryPhotoId" value="">
                </form>

                <a class="btn btn-secondary" href="/admin/gallery">Back to Categories</a>
            </div>
        </div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.7/Sortable.min.js"></script>
<script>
    var photoSortList = document.getElementById('photoSortList');
    if (photoSortList) {
        Sortable.create(photoSortList, { animation: 150 });

        document.getElementById('reorderForm').addEventListener('submit', function () {
            var ids = Array.prototype.map.call(
                photoSortList.querySelectorAll('[data-photo-id]'),
                function (el) { return el.getAttribute('data-photo-id'); }
            );
            document.getElementById('orderedIds').value = ids.join(',');
        });
    }
</script>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
