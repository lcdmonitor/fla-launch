<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
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
            <h1>Admin Dashboard</h1>
            <UL style="padding: 3px; spacing: 3px; margin: 3px;">
                <li style="padding: 3px; spacing: 3px; margin: 3px;"><a href="/admin/pages" class="btn btn-primary">Manage Pages</a></li>
                <li style="padding: 3px; spacing: 3px; margin: 3px;"><a href="/admin/users" class="btn btn-primary">Manage Users</a></li>
                <li style="padding: 3px; spacing: 3px; margin: 3px;"><a href="/admin/gallery" class="btn btn-primary">Manage Gallery</a></li>
                <li style="padding: 3px; spacing: 3px; margin: 3px;"><a href="/admin/newsalert" class="btn btn-primary">Manage News Alert</a></li>
                <li style="padding: 3px; spacing: 3px; margin: 3px;"><a href="/admin/stats" class="btn btn-primary">View Stats</a></li>
            </UL>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
