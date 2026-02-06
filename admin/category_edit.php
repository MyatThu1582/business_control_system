<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

// Fetch current category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id=".$_GET['id']);
$stmt->execute();
$result = $stmt->fetchAll();

// Handle form submission
if ($_POST) {
    if (empty($_POST['categories_code']) || empty($_POST['categories_name'])) {
        if (empty($_POST['categories_code'])) {
            $categoriescodeError = 'Categories_Code is required';
        }
        if (empty($_POST['categories_name'])) {
            $categoriesnameError = 'Categories_Name is required';
        }
    } else {
        $categories_code = $_POST['categories_code'];
        $categories_name = $_POST['categories_name'];
        $id = $_GET['id'];

        $stmt = $pdo->prepare("UPDATE categories SET categories_code=:categories_code, categories_name=:categories_name WHERE id=:id");
        $resultUpdate = $stmt->execute([
            ':categories_code' => $categories_code,
            ':categories_name' => $categories_name,
            ':id' => $id
        ]);

        if ($resultUpdate) {
            echo "<script>
                swal('Success!', 'Category Updated Successfully', 'success').then(() => {
                    window.location.href='category.php';
                });
            </script>";
        }
    }
}
?>

<div class="w-50 mx-auto mt-5">
  <div class="card">
    <div class="card-body">
      <h2>Update Category</h2>
      <form action="" method="post">
        <label class="mt-4"><b>Categories Code</b></label>
        <input type="text" class="form-control" placeholder="Categories Code" name="categories_code" value="<?php echo htmlspecialchars($result[0]['categories_code']); ?>">
        <p style="color:red;"><?php echo empty($categoriescodeError) ? '' : '*'.$categoriescodeError; ?></p>

        <label class="mt-4"><b>Categories Name</b></label>
        <input type="text" class="form-control" placeholder="Categories Name" name="categories_name" value="<?php echo htmlspecialchars($result[0]['categories_name']); ?>">
        <p style="color:red;"><?php echo empty($categoriesnameError) ? '' : '*'.$categoriesnameError; ?></p>

        <div class="row mt-3">
            <div class="col-6">
                <button type="submit" class="add_btn form-control">Update</button>
            </div>
            <div class="col-6">
                <a href="category.php" class="add_btn form-control text-center text-decoration-none">Back</a>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.html'; ?>
