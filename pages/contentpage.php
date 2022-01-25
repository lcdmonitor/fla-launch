<?php 
    require($_SERVER['DOCUMENT_ROOT'] .'/includes/header.php'); 
?>
<!--launch Section-->
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Content Page:</h1>
                <!-- TODO Update CSS Classes, these refer to launch page -->
                <?php print var_dump($_GET);?>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] .'/includes/footer.php'); ?>
