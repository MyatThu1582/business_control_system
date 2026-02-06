<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

if ($_POST) {
    if (empty($_POST['role_name']) || empty($_POST['description'])) {
        if (empty($_POST['role_name'])) $roleNameError = 'Role Name is required';
        if (empty($_POST['description'])) $descriptionError = 'Description is required';
    } else {
        $role_name = $_POST['role_name'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (:role_name, :description)");
        $result = $stmt->execute([
            ':role_name' => $role_name,
            ':description' => $description
        ]);

        if ($result) {
            echo "<script>
                swal('Success!', 'Role added successfully', 'success').then(() => {
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
      <h2>Create New Role</h2>
      <form action="" method="post">
        <label class="mt-4"><b>Role Name</b></label>
        <input type="text" class="form-control" name="role_name">
        <p style="color:red;"><?php echo empty($roleNameError) ? '' : '*'.$roleNameError; ?></p>

        <label class="mt-4"><b>Description</b></label>
        <textarea class="form-control" name="description"></textarea>
        <p style="color:red;"><?php echo empty($descriptionError) ? '' : '*'.$descriptionError; ?></p>

        <div class="row mt-3">
            <div class="col-6">
                <button type="submit" class="add_btn form-control">Add</button>
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
