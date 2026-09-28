<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$contentAreas = [
    'fla' => 'Home / FLA Overview',
    'launch' => 'Launches',
    'education' => 'Education',
];
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Site Content</h1>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contentAreas as $key => $label) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($label, ENT_QUOTES); ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="/admin/editsitecontent?key=<?php echo htmlspecialchars($key, ENT_QUOTES); ?>">Edit</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <a class="btn btn-secondary" href="/admin">Back to Admin Dashboard</a>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
