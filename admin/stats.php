<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
if (!in_array($days, [1, 7, 30, 90, 0], true)) {
    $days = 30;
}

$hitsByDay = GetPageHitCountByDay($days);
$hitsByUser = GetPageHitCountByUser($days);
$hitsByUserAgent = GetPageHitCountByUserAgent($days);
$hitsByReferrer = GetPageHitCountByReferrer($days);
$recentHits = GetRecentPageHits(200);

$dayFilters = [1 => "Last 24 Hours", 7 => "Last 7 Days", 30 => "Last 30 Days", 90 => "Last 90 Days", 0 => "All Time"];
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Site Stats</h1>

                <div class="mb-3">
                    <?php foreach ($dayFilters as $value => $label) { ?>
                        <a class="btn btn-sm <?php echo $days === $value ? 'btn-primary' : 'btn-outline-primary'; ?>"
                           href="/admin/stats?days=<?php echo $value; ?>"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></a>
                    <?php } ?>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <h2 class="h5">Hits by Day</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Date</th><th>Hits</th></tr></thead>
                                <tbody>
                                    <?php foreach ($hitsByDay as $row) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['HitDate'], ENT_QUOTES); ?></td>
                                            <td><?php echo (int)$row['HitCount']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="h5">Hits by User</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead><tr><th>User</th><th>Hits</th></tr></thead>
                                <tbody>
                                    <?php foreach ($hitsByUser as $row) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['UserLabel'], ENT_QUOTES); ?></td>
                                            <td><?php echo (int)$row['HitCount']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="h5">Hits by User Agent</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead><tr><th>User Agent</th><th>Hits</th></tr></thead>
                                <tbody>
                                    <?php foreach ($hitsByUserAgent as $row) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['UserAgentLabel'], ENT_QUOTES); ?></td>
                                            <td><?php echo (int)$row['HitCount']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="h5">Hits by Referrer</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Referrer</th><th>Hits</th></tr></thead>
                                <tbody>
                                    <?php foreach ($hitsByReferrer as $row) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['ReferrerLabel'], ENT_QUOTES); ?></td>
                                            <td><?php echo (int)$row['HitCount']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <h2 class="h5 mt-4">Recent Activity</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>URL</th>
                                <th>Referrer</th>
                                <th>IP</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentHits as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['HitTime'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['Username'] ?? 'Guest', ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['RequestUrl'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['Referrer'] ?? '', ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['IPAddress'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['UserAgent'] ?? '', ENT_QUOTES); ?></td>
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
