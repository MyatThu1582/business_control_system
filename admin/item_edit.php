<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Item</title>
</head>

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

<body>
<?php include 'header.php'; ?>

<?php
$id = $_GET['id'];

/* FETCH ITEM */
$stmt = $pdo->prepare("SELECT * FROM item WHERE id=:id");
$stmt->execute([':id'=>$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

/* UPDATE */
if ($_POST) {

  if (empty($_POST['item_id']) || empty($_POST['item_name']) || empty($_POST['categories_id'])  
      || empty($_POST['original_price']) || empty($_POST['selling_price']) || empty($_POST['reorder_level'])) {

    if (empty($_POST['item_id'])) $itemidError = 'Item_Id is required';
    if (empty($_POST['item_name'])) $itemnameError = 'Item_Name is required';
    if (empty($_POST['categories_id'])) $categoriesidError = 'Categories_Id is required';
    if (empty($_POST['original_price'])) $original_priceError = 'Original_Price is required';
    if (empty($_POST['selling_price'])) $selling_priceError = 'Selling_Price is required';
    if (empty($_POST['reorder_level'])) $reorder_levelError = 'Reorder_Level is required';

  } else {

    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $categories_id = $_POST['categories_id'];
    $original_price = $_POST['original_price'];
    $selling_price = $_POST['selling_price'];
    $reorder_level = $_POST['reorder_level'];

    /* IMAGE */
    $image_name = $_POST['old_image'];

    if (!empty($_FILES['item_image']['name'])) {
      $folder = "images/";
      if (!is_dir($folder)) mkdir($folder, 0777, true);

      $image_name = time() . "_" . $_FILES['item_image']['name'];
      move_uploaded_file($_FILES['item_image']['tmp_name'], $folder.$image_name);
    }

    $stmt = $pdo->prepare("
      UPDATE item SET 
        item_id=:item_id,
        item_name=:item_name,
        categories_id=:categories_id,
        original_price=:original_price,
        selling_price=:selling_price,
        reorder_level=:reorder_level,
        item_image=:item_image
      WHERE id=:id
    ");

    $resultUpdate = $stmt->execute([
      ':item_id'=>$item_id,
      ':item_name'=>$item_name,
      ':categories_id'=>$categories_id,
      ':original_price'=>$original_price,
      ':selling_price'=>$selling_price,
      ':reorder_level'=>$reorder_level,
      ':item_image'=>$image_name,
      ':id'=>$id
    ]);

    if ($resultUpdate) {
      echo "<script>
        swal('Success!', 'Item Updated Successfully', 'success').then(() => {
        window.location.href='item.php'; });
      </script>";
    }
  }
}

/* FETCH CATEGORIES */
$catStmt = $pdo->prepare("SELECT * FROM categories");
$catStmt->execute();
$catResult = $catStmt->fetchAll();
?>

<div>
  <div class="card w-50" style="margin:auto; margin-top:10px;">
    <div class="card-body">
      <h3>Update Item</h3>

      <form action="" method="post" enctype="multipart/form-data">

        <label class="mt-2">Item ID</label>
        <input type="text" class="form-control" name="item_id" value="<?= $result['item_id'] ?>">
        <p style="color:red;"><?php echo empty($itemidError)?'':'*'.$itemidError;?></p>

        <label class="mt-2">Item Name</label>
        <input type="text" class="form-control" name="item_name" value="<?= $result['item_name'] ?>">
        <p style="color:red;"><?php echo empty($itemnameError)?'':'*'.$itemnameError;?></p>

        <label class="mt-2">Category</label>
        <select name="categories_id" class="form-control">
          <option value="">SELECT CATEGORY</option>
          <?php foreach($catResult as $value): ?>
            <option value="<?= $value['categories_code']; ?>"
              <?php if($value['categories_code']==$result['categories_id']) echo "selected"; ?>>
              <?= $value['categories_name']; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p style="color:red;"><?php echo empty($categoriesidError)?'':'*'.$categoriesidError;?></p>

        <div class="row">
          <div class="col-6">
            <label class="mt-2">Original Price</label>
            <input type="number" class="form-control" name="original_price" value="<?= $result['original_price'] ?>">
            <p style="color:red;"><?php echo empty($original_priceError)?'':'*'.$original_priceError;?></p>
          </div>
          <div class="col-6">
            <label class="mt-2">Selling Price</label>
            <input type="number" class="form-control" name="selling_price" value="<?= $result['selling_price'] ?>">
            <p style="color:red;"><?php echo empty($selling_priceError)?'':'*'.$selling_priceError;?></p>
          </div>
        </div>

        <label class="mt-2">Reorder Level</label>
        <input type="number" class="form-control" name="reorder_level" value="<?= $result['reorder_level'] ?>">
        <p style="color:red;"><?php echo empty($reorder_levelError)?'':'*'.$reorder_levelError;?></p>

        <label class="mt-3">Item Image</label>
        <input type="file" class="form-control" name="item_image">
        <input type="hidden" name="old_image">
        <?php if(!empty($result['item_image'])): ?>
          <img src="images/<?= $result['item_image']; ?>" width="80" class="mt-2">
        <?php endif; ?>

        <div class="row mt-3">
          <div class="col-6">
            <button type="submit" class="add_btn form-control mt-3">Update</button>
          </div>
          <div class="col-6">
            <a href="item.php"><button type="button" class="add_btn form-control mt-3">Back</button></a>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include 'footer.html'; ?>
</body>
</html>
