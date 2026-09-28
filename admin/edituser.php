<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/auth.inc.php');
RequireAuthentication(ROLE_ADMIN);

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = $userId ? GetUserById($userId) : null;

if (!$user) {
    header("location: /admin/users");
    exit;
}

$isError = false;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ValidateCSRFToken($_POST['csrf_token'] ?? '')) {
        $isError = true;
        $msg = "Your session has expired or the request could not be verified. Please refresh the page and try again.";
    } else {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $roleId = isset($_POST['roleId']) ? (int)$_POST['roleId'] : 0;
        $addressLine1 = $_POST['addressLine1'] ?? '';
        $addressLine2 = $_POST['addressLine2'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $zip = $_POST['zip'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $membershipTypeId = ($_POST['membershipTypeId'] ?? '') !== '' ? (int)$_POST['membershipTypeId'] : null;
        $duesPaidDate = ($_POST['duesPaidDate'] ?? '') !== '' ? $_POST['duesPaidDate'] : null;
        $duesStatusId = ($_POST['duesStatusId'] ?? '') !== '' ? (int)$_POST['duesStatusId'] : null;

        if ($fullname === '' || $email === '') {
            $isError = true;
            $msg = "Full Name and Email are required.";
            $user = array_merge($user, [
                "FullName"=>$fullname, "Email"=>$email, "RoleID"=>$roleId,
                "AddressLine1"=>$addressLine1, "AddressLine2"=>$addressLine2, "City"=>$city, "State"=>$state, "Zip"=>$zip, "Phone"=>$phone,
                "MembershipTypeID"=>$membershipTypeId, "DuesPaidDate"=>$duesPaidDate, "DuesStatusID"=>$duesStatusId
            ]);
        } elseif ($phone !== '' && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $isError = true;
            $msg = "Phone must be in the format XXX-XXX-XXXX.";
            $user = array_merge($user, [
                "FullName"=>$fullname, "Email"=>$email, "RoleID"=>$roleId,
                "AddressLine1"=>$addressLine1, "AddressLine2"=>$addressLine2, "City"=>$city, "State"=>$state, "Zip"=>$zip, "Phone"=>$phone,
                "MembershipTypeID"=>$membershipTypeId, "DuesPaidDate"=>$duesPaidDate, "DuesStatusID"=>$duesStatusId
            ]);
        } else {
            try {
                UpdateUserByAdmin($userId, $fullname, $email, $roleId, $addressLine1, $addressLine2, $city, $state, $zip, $phone, $membershipTypeId, $duesPaidDate, $duesStatusId);
                header("location: /admin/users");
                exit;
            } catch (mysqli_sql_exception $e) {
                $isError = true;
                $msg = ($e->getCode() === 1062)
                    ? "That email is already in use by another user."
                    : "Error saving user.";
                $user = array_merge($user, [
                    "FullName"=>$fullname, "Email"=>$email, "RoleID"=>$roleId,
                    "AddressLine1"=>$addressLine1, "AddressLine2"=>$addressLine2, "City"=>$city, "State"=>$state, "Zip"=>$zip, "Phone"=>$phone,
                    "MembershipTypeID"=>$membershipTypeId, "DuesPaidDate"=>$duesPaidDate, "DuesStatusID"=>$duesStatusId
                ]);
            }
        }
    }
}

require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');

$roles = GetAllRoles();
$membershipTypes = ListMembershipTypes();
$duesStatuses = ListDuesStatuses();
?>
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <h1 class="launch__heading">Edit User: <?php echo htmlspecialchars($user['Username'], ENT_QUOTES); ?></h1>
                <?php if ($msg) { ?>
                    <?php if ($isError) { ?>
                        <div class="error-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } else { ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
                    <?php } ?>
                <?php } ?>
                <form method="POST" action="/admin/edituser?id=<?php echo (int)$userId; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(GenerateCSRFToken(), ENT_QUOTES); ?>">

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Username'], ENT_QUOTES); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($user['FullName'], ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['Email'], ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="roleId">Role</label>
                        <select class="form-control" id="roleId" name="roleId">
                            <?php foreach ($roles as $role) { ?>
                                <option value="<?php echo (int)$role['RoleID']; ?>" <?php echo (int)$user['RoleID'] === (int)$role['RoleID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['Rolename'], ENT_QUOTES); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="addressLine1">Address Line 1</label>
                        <input type="text" class="form-control" id="addressLine1" name="addressLine1" value="<?php echo htmlspecialchars($user['AddressLine1'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="addressLine2">Address Line 2</label>
                        <input type="text" class="form-control" id="addressLine2" name="addressLine2" value="<?php echo htmlspecialchars($user['AddressLine2'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['City'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" class="form-control" id="state" name="state" maxlength="2" value="<?php echo htmlspecialchars($user['State'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" name="zip" value="<?php echo htmlspecialchars($user['Zip'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="XXX-XXX-XXXX" pattern="\d{3}-\d{3}-\d{4}" value="<?php echo htmlspecialchars($user['Phone'] ?? '', ENT_QUOTES); ?>">
                        <small class="form-text text-muted">Format: XXX-XXX-XXXX</small>
                    </div>

                    <div class="form-group">
                        <label for="membershipTypeId">Membership</label>
                        <select class="form-control" id="membershipTypeId" name="membershipTypeId">
                            <option value="">-- None --</option>
                            <?php foreach ($membershipTypes as $mt) { ?>
                                <option value="<?php echo (int)$mt['MembershipTypeID']; ?>" <?php echo (isset($user['MembershipTypeID']) && (int)$user['MembershipTypeID'] === (int)$mt['MembershipTypeID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mt['Name'], ENT_QUOTES); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="duesPaidDate">Dues Paid Date</label>
                        <input type="date" class="form-control" id="duesPaidDate" name="duesPaidDate" value="<?php echo htmlspecialchars($user['DuesPaidDate'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="duesStatusId">Dues Status</label>
                        <select class="form-control" id="duesStatusId" name="duesStatusId">
                            <option value="">-- None --</option>
                            <?php foreach ($duesStatuses as $ds) { ?>
                                <option value="<?php echo (int)$ds['DuesStatusID']; ?>" <?php echo (isset($user['DuesStatusID']) && (int)$user['DuesStatusID'] === (int)$ds['DuesStatusID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ds['Name'], ENT_QUOTES); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    <a class="btn btn-secondary" href="/admin/users">Back to Users List</a>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>
