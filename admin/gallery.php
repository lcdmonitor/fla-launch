<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $orderedIds = explode(',', $_POST['ordered_ids'] ?? '');
        $index = 0;
        try {
            foreach ($orderedIds as $idValue) {
                $categoryId = (int)$idValue;
                if ($categoryId > 0) {
                    UpdateGalleryCategorySortOrder($categoryId, $index);
                    $index++;
                }
            }
            header("location: /admin/gallery");
            exit;
        } catch (mysqli_sql_exception $e) {
            $isError = true;
            $msg = "Error saving category order.";
        }
    }
}

require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$categories = ListGalleryCategories();
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Gallery Categories</h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <a class="btn btn-primary mb-3" href="/admin/editgallerycategory">New Category</a>
                <p class="text-muted">Drag rows to reorder, then click Save Order.</p>
                <form id="reorderCategoriesForm" method="POST" action="/admin/gallery">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="action" value="reorder">
                    <input type="hidden" name="ordered_ids" id="orderedIds" value="">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Sort Order</th>
                                    <th>Hidden</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categorySortList">
                                <?php foreach ($categories as $row) { ?>
                                    <tr data-category-id="<?php echo (int)$row['CategoryID']; ?>" style="cursor: move;">
                                        <td><?php echo htmlspecialchars($row['Name'], ENT_QUOTES); ?></td>
                                        <td><?php echo (int)$row['SortOrder']; ?></td>
                                        <td><?php echo $row['Hidden'] ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="/admin/editgallerycategory?id=<?php echo (int)$row['CategoryID']; ?>">Edit</a>
                                            <a class="btn btn-sm btn-secondary" href="/admin/gallery-photos?category=<?php echo (int)$row['CategoryID']; ?>">Manage Photos</a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDeleteGalleryCategory(<?php echo (int)$row['CategoryID']; ?>, '<?php echo htmlspecialchars(addslashes($row['Name']), ENT_QUOTES); ?>')">Delete</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Order</button>
                </form>

                <form id="deleteGalleryCategoryForm" method="POST" action="/admin/deletegallerycategory" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="category_id" id="deleteGalleryCategoryId" value="">
                </form>

                <a class="btn btn-secondary" href="/admin">Back to Admin Dashboard</a>
            </div>
        </div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.7/Sortable.min.js"></script>
<script>
    var categorySortList = document.getElementById('categorySortList');
    if (categorySortList) {
        Sortable.create(categorySortList, { animation: 150 });

        document.getElementById('reorderCategoriesForm').addEventListener('submit', function () {
            var ids = Array.prototype.map.call(
                categorySortList.querySelectorAll('[data-category-id]'),
                function (el) { return el.getAttribute('data-category-id'); }
            );
            document.getElementById('orderedIds').value = ids.join(',');
        });
    }
</script>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
