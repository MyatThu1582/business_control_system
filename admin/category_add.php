<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';

  ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <style media="screen">
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
  .crd{
    width:500px;
  }
  </style>
  <body>
    <?php include 'header.php';?>

    <?php
      if ($_POST) {
        if (empty($_POST['categories_code']) || empty($_POST['categories_name'])) {
          if (empty($_POST['categories_code'])) {
            $categoriescodeError = 'Categories_Code is required';
          }
          if (empty($_POST['categories_name'])) {
            $categoriesnameError = 'Categories_Name is required';
          }
        }else {
          $categories_code = $_POST['categories_code'];
          $categories_name = $_POST['categories_name'];

          $stmt = $pdo->prepare("INSERT INTO categories (categories_code,categories_name) VALUES (:categories_code,:categories_name)");
          $result = $stmt->execute(
            array(':categories_code'=>$categories_code,':categories_name'=>$categories_name)
          );
          if ($result) {
            echo "<script>
              swal('Succcess!', 'New Category Added Successfully', 'success').then((value) => {
              window.location.href='category.php'; });
          </script>";
          }
        }
      }
     ?>

    <div class="w-50 mx-auto mt-5">
      <div class="card crd">
        <div class="card-body">
          <h3>Create Page</h3>
          <form class="" action="" method="post">
            <label for="" class="mt-4"><b>Categories_Code</b></label>
            <input type="text" class="form-control" placeholder="Categories_Code" name="categories_code">
            <p style="color:red;"><?php echo empty($categoriescodeError) ? '' : '*'.$categoriescodeError;?></p>

            <label for="" class="mt-4"><b>Categories_Name</b></label>
            <input type="text" class="form-control" placeholder="Categories_Name" name="categories_name">
            <p style="color:red;"><?php echo empty($categoriescodeError) ? '' : '*'.$categoriescodeError;?></p>

            <div class="d-flex">
              <button type="submit" name="button" class="add_btn form-control mt-3">Add</button>
              <a href="category.php" style="width:450px;"><button type="button" name="button" class="add_btn form-control mt-3">Back</button></a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php include 'footer.html'; ?>
  </body>
</html>
