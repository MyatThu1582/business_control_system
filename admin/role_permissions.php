<?php
session_start();
require '../Config/config.php'; // your common file
require 'permission.php';    // contains hasPermission() helper

// Only users with permission.manage can access
if (!hasPermission('permissions.manage')) {
    http_response_code(403);
    die('Access Denied');
}

// Database connection (replace with your db connection)
require '../Config/common.php';

// Get roles for dropdown
$rolesStmt = $pdo->prepare("SELECT id, name FROM roles ORDER BY name");
$rolesStmt->execute();
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all permissions
$permStmt = $pdo->prepare("SELECT id, name, permission_key FROM permissions ORDER BY name");
$permStmt->execute();
$permissions = $permStmt->fetchAll(PDO::FETCH_ASSOC);

// Selected role
$selectedRoleId = $_GET['role_id'] ?? $roles[0]['id'];

// Get assigned permissions for selected role
$assignedStmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$assignedStmt->execute([$selectedRoleId]);
$assignedPermissions = $assignedStmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_id'])) {
    $roleId = $_POST['role_id'];
    $newPermissions = $_POST['permissions'] ?? [];

    // Begin transaction
    $pdo->beginTransaction();
    try {
        // Delete old permissions
        $delStmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $delStmt->execute([$roleId]);

        // Insert new permissions
        $insStmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($newPermissions as $permId) {
            $insStmt->execute([$roleId, $permId]);
        }

        $pdo->commit();
        $success = "Permissions updated successfully!";
        // Refresh assigned permissions
        $assignedPermissions = $newPermissions;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update permissions: " . $e->getMessage();
    }
}

$current_page = basename($_SERVER['PHP_SELF']); // for sidebar active

include 'header.php'; // your header file
?>

<div class="col-md-12 px-3 py-3">
    <div class="">
        <h1 class="card-title">Permission Listings</h1>
    </div><br><br>
    <div class="content">
        <div class="container-fluid">

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="GET" class="mb-3">
                <label for="role">Select Role:</label>
                <select name="role_id" id="role" onchange="this.form.submit()" class="form-control" style="width:200px;">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $role['id'] == $selectedRoleId ? 'selected' : '' ?>><?= $role['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php
            // Define permission groups
            $permissionGroups = [
                'Roles & Users' => ['roles.manage', 'users.manage', 'permissions.manage'],

                'Configurations' => ['category.view', 'item.view', 'supplier.view', 'customer.view'],

                'Purchase' => ['purchase.order.view', 'purchase.create', 'purchase.view', 'purchase.return'],

                'Sale' => ['sale.order.view', 'sale.create', 'sale.return'],

                'Accounting' => [
                    'account.payable.view',
                    'account.payable.detail',
                    'account.payable.voucher',
                    'account.receivable.view',
                    'account.receivable.detail'
                ],

                'Stock' => ['stock.manage'],

                'Reporting' => ['report.view'],

                'Company' => ['company.manage'],

                // 💾 NEW MODULE
                'Backup & Restore' => [
                    'backup.manage',
                    'restore.manage'
                ],
            ];

            ?>
            <form method="POST">
                <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">


                <?php foreach ($permissionGroups as $groupName => $permsInGroup): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-lightgreen">
                            <strong><?= $groupName ?></strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($permissions as $perm): ?>
                                    <?php if (in_array($perm['permission_key'], $permsInGroup)): ?>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    id="perm<?= $perm['id'] ?>"
                                                    name="permissions[]"
                                                    value="<?= $perm['id'] ?>"
                                                    <?= in_array($perm['id'], $assignedPermissions) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm<?= $perm['id'] ?>">
                                                    <?= $perm['name'] ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-success mt-3">Save Permissions</button>
            </form>

        </div>
    </div>
</div>

<?php include 'footer.html'; ?>
</body>

</html>