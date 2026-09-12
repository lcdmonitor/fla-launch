<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'); ?>
<style>
    /* Pin the footer to the bottom of the viewport on this page when content is short */
    body { display: flex; flex-direction: column; min-height: 100vh; background: transparent; }
    footer.footer { margin-top: auto; }
</style>
<!--gallery Section-->
<?php
$categories = ListVisibleGalleryCategories();
$categoriesWithPhotos = array();
foreach ($categories as $category) {
    $photos = ListPhotosByCategory($category['CategoryID']);
    if (count($photos) > 0) {
        $category['Photos'] = $photos;
        $categoriesWithPhotos[] = $category;
    }
}
?>
<video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted"
    style="position: fixed; top: 0; left: 0; min-width: 100%; min-height: 100%; width: auto; height: auto; object-fit: cover; z-index: -1;">
    <source src="/img/background.mp4" type="video/mp4">
</video>
<section class="container-custom py-5">
    <div class="row row-cols-1 row-cols-md-3 g-4 pt-4">
        <?php foreach ($categoriesWithPhotos as $category) { ?>
            <?php $cover = $category['Photos'][0]; ?>
            <div class="col">
                <div class="card h-100" style="cursor: pointer;" data-bs-toggle="modal"
                    data-bs-target="#galleryModal<?php echo (int) $category['CategoryID']; ?>">
                    <img src="/img/gallery/thumbs/<?php echo htmlspecialchars($cover['ThumbFileName'], ENT_QUOTES); ?>"
                        class="card-img-top" style="width: 50%; height: auto; margin: 0 auto; display: block; padding-top: 1.5rem;" alt="">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($category['Name'], ENT_QUOTES); ?>
                        </h5>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<?php foreach ($categoriesWithPhotos as $category) { ?>
    <div class="modal fade" id="galleryModal<?php echo (int) $category['CategoryID']; ?>" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo htmlspecialchars($category['Name'], ENT_QUOTES); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="galleryCarousel<?php echo (int) $category['CategoryID']; ?>" class="carousel slide"
                        data-bs-ride="false">
                        <div class="carousel-inner">
                            <?php foreach ($category['Photos'] as $index => $photo) { ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="/img/gallery/<?php echo htmlspecialchars($photo['FileName'], ENT_QUOTES); ?>"
                                        class="d-block w-100" loading="lazy" alt="">
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (count($category['Photos']) > 1) { ?>
                            <button class="carousel-control-prev" type="button"
                                data-bs-target="#galleryCarousel<?php echo (int) $category['CategoryID']; ?>"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                data-bs-target="#galleryCarousel<?php echo (int) $category['CategoryID']; ?>"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
