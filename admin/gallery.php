<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
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
                <a class="btn btn-primary mb-3" href="/admin/editgallerycategory">New Category</a>
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
                        <tbody>
                            <?php foreach ($categories as $row) { ?>
                                <tr>
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

                <form id="deleteGalleryCategoryForm" method="POST" action="/admin/deletegallerycategory" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="category_id" id="deleteGalleryCategoryId" value="">
                </form>

                <a class="btn btn-secondary" href="/admin">Back to Admin Dashboard</a>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
