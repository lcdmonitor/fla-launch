<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$search = $_GET['search'] ?? '';
$users = ListUsers($search);

$roles = GetAllRoles();
$membershipTypes = ListMembershipTypes();
$duesStatuses = ListDuesStatuses();

function LabelById($items, $idKey, $labelKey, $id)
{
    foreach ($items as $item) {
        if ((int)$item[$idKey] === (int)$id) {
            return $item[$labelKey];
        }
    }
    return '';
}
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Manage Users</h1>
                <form method="GET" action="/admin/users" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by username, name, or email" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Membership</th>
                                <th>Dues Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Username'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['FullName'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email'], ENT_QUOTES); ?></td>
                                    <td><?php echo htmlspecialchars(LabelById($roles, 'RoleID', 'Rolename', $row['RoleID']), ENT_QUOTES); ?></td>
                                    <td><?php echo $row['MembershipTypeID'] !== null ? htmlspecialchars(LabelById($membershipTypes, 'MembershipTypeID', 'Name', $row['MembershipTypeID']), ENT_QUOTES) : ''; ?></td>
                                    <td><?php echo $row['DuesStatusID'] !== null ? htmlspecialchars(LabelById($duesStatuses, 'DuesStatusID', 'Name', $row['DuesStatusID']), ENT_QUOTES) : ''; ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="/admin/edituser?id=<?php echo (int)$row['UserID']; ?>">Edit</a>
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
