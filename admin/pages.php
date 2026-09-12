<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$pages = ListPages();
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Pages</h1>
                <a class="btn btn-primary mb-3" href="/admin/editpage">New Page</a>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Page Key</th>
                                <th>Summary</th>
                                <th>Member Only</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['PageKey'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['Summary'], ENT_QUOTES); ?></td>
                                    <td><?php echo $row['MemberOnly'] ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="/admin/editpage?id=<?php echo (int)$row['PageID']; ?>">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDeletePage(<?php echo (int)$row['PageID']; ?>, '<?php echo htmlspecialchars(addslashes($row['PageKey']), ENT_QUOTES); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <form id="deletePageForm" method="POST" action="/admin/deletepage" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">
                    <input type="hidden" name="page_id" id="deletePageId" value="">
                </form>

                <a class="btn btn-secondary" href="/admin">Back to Admin Dashboard</a>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
