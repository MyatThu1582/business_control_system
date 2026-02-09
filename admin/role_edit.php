<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
include 'header.php';

// Fetch role
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id=".$_GET['id']);
$stmt->execute();
$role = $stmt->fetch();

if ($_POST) {
    if (empty($_POST['role_name']) || empty($_POST['description'])) {
        if (empty($_POST['role_name'])) $roleNameError = 'Role Name is required';
        if (empty($_POST['description'])) $descriptionError = 'Description is required';
    } else {
        $role_name = $_POST['role_name'];
        $description = $_POST['description'];
        $id = $_GET['id'];

        $stmt = $pdo->prepare("UPDATE roles SET name=:role_name, description=:description WHERE id=:id");
        $result = $stmt->execute([
            ':role_name'=>$role_name,
            ':description'=>$description,
            ':id'=>$id
        ]);

        if ($result) {
            echo "<script>
                swal('Success!', 'Role Updated Successfully', 'success').then(() => {
                    window.location.href='roles.php';
                });
            </script>";
        }
    }
}
?>

<div class="w-50 mx-auto mt-5">
  <div class="card">
    <div class="card-body">
      <h2>Update Role</h2>
      <form action="" method="post">
        <label class="mt-4"><b>Role Name</b></label>
        <input type="text" class="form-control" name="role_name" value="<?php echo htmlspecialchars($role['name']); ?>">
        <p style="color:red;"><?php echo empty($roleNameError) ? '' : '*'.$roleNameError; ?></p>

        <label class="mt-4"><b>Description</b></label>
        <textarea class="form-control" name="description"><?php echo htmlspecialchars($role['description']); ?></textarea>
        <p style="color:red;"><?php echo empty($descriptionError) ? '' : '*'.$descriptionError; ?></p>

        <div class="row mt-3">
            <div class="col-6">
                <button type="submit" class="add_btn form-control">Update</button>
            </div>
            <div class="col-6">
                <a href="roles.php" class="add_btn form-control text-center text-decoration-none">Back</a>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.html'; ?>
