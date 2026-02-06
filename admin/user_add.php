<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

// Fetch roles for select box
$roleStmt = $pdo->prepare("SELECT * FROM roles ORDER BY id ASC");
$roleStmt->execute();
$roles = $roleStmt->fetchAll();

// Handle form submission
if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? '';
    $errors = [];

    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    if (empty($password)) $errors['password'] = 'Password is required';
    if (empty($role)) $errors['role'] = 'Role is required';

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (:name,:email,:password,:role)");
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);

        if ($result) {
            echo "<script>
              swal('Success!', 'New User Added Successfully', 'success').then(() => {
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
        <h3>Create New User</h3>
        <form action="" method="post">
            <label class="mt-2">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Enter name" value="<?php echo $_POST['name'] ?? ''; ?>">
            <p style="color:red;"><?php echo $errors['name'] ?? ''; ?></p>

            <label class="mt-2">Email</label>
            <input type="email" class="form-control" name="email" placeholder="Enter email" value="<?php echo $_POST['email'] ?? ''; ?>">
            <p style="color:red;"><?php echo $errors['email'] ?? ''; ?></p>

            <label class="mt-2">Password</label>
            <input type="password" class="form-control" name="password" placeholder="Enter password">
            <p style="color:red;"><?php echo $errors['password'] ?? ''; ?></p>

            <label class="mt-2">Role</label>
            <select name="role" class="form-control">
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['id']; ?>" <?php if(!empty($_POST['role']) && $_POST['role'] == $role['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($role['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p style="color:red;"><?php echo $errors['role'] ?? ''; ?></p>

            <div class="row mt-3">
                <div class="col-6">
                    <button type="submit" class="add_btn form-control">Add</button>
                </div>
                <div class="col-6">
                    <a href="users.php"><button type="button" class="add_btn form-control">Back</button></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.html'; ?>
