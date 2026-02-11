<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
include 'header.php';

$id = $_GET['id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch roles for select box
$roleStmt = $pdo->prepare("SELECT * FROM roles ORDER BY id ASC");
$roleStmt->execute();
$roles = $roleStmt->fetchAll();

// Handle form submission
if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role_id = $_POST['role_id'] ?? '';

    $errors = [];

    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    if (empty($role_id)) $errors['role'] = 'Role is required';

    $passwordQuery = '';
    $params = [
        ':name' => $name,
        ':email' => $email,
        ':role' => $role_id,
        ':id' => $id
    ];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $passwordQuery = ", password=:password";
        $params[':password'] = $hashedPassword;
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare("UPDATE users SET name=:name, email=:email, role=:role $passwordQuery WHERE id=:id");
        $result = $updateStmt->execute($params);

        if ($result) {
            echo "<script>
              swal('Success!', 'User Updated Successfully', 'success').then((value) => {
                window.location.href='users.php';
              });
            </script>";
        }
    }
}
?>

<style>
.add_btn{
    background-color:#1c1c1c;
    color:white;
    transition:0.5s;
    border-radius:10px;
    padding:7px;
}
.add_btn:hover{
    border:2px solid #1c1c1c;
    background:none;
    color:#1c1c1c;
    transition:0.5s;
    border-radius:10px;
    box-shadow:2px 8px 16px gray;
}
</style>

<div class="card w-50 mx-auto mt-4">
    <div class="card-body">
        <h3>Edit User</h3>
        <form action="" method="post">
            <label class="mt-2">Name</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
            <p style="color:red;"><?php echo $errors['name'] ?? ''; ?></p>

            <label class="mt-2">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            <p style="color:red;"><?php echo $errors['email'] ?? ''; ?></p>

            <label class="mt-2">Password <small>(leave blank to keep current password)</small></label>
            <input type="password" class="form-control" name="password" placeholder="New password">

            <label class="mt-2">Role</label>
            <select name="role_id" class="form-control">
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['id']; ?>" <?php echo ($user['role'] == $role['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p style="color:red;"><?php echo $errors['role'] ?? ''; ?></p>

            <div class="row mt-3">
                <div class="col-6">
                    <button type="submit" class="add_btn form-control">Update</button>
                </div>
                <div class="col-6">
                    <a href="users.php"><button type="button" class="add_btn form-control">Back</button></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.html'; ?>
