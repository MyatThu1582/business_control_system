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
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<style>
.add_btn {
  background-color: #1c1c1c;
  color: white;
  transition: 0.5s;
  border-radius: 10px;
  padding: 7px;
}
.add_btn:hover {
  border: 2px solid #1c1c1c;
  background: none;
  color: #1c1c1c;
  transition: 0.5s;
  border-radius: 10px;
  box-shadow: 2px 8px 16px gray;
}
</style>

<body>
<?php include 'header.php'; ?>

<?php
if ($_POST) {

  if (
    empty($_POST['item_id']) ||
    empty($_POST['item_name']) ||
    empty($_POST['categories_id']) ||
    empty($_POST['original_price']) ||
    empty($_POST['selling_price']) ||
    empty($_POST['reorder_level']) ||
    empty($_POST['expiry_date']) ||
    empty($_POST['location'])
  ) {

    if (empty($_POST['item_id'])) $itemidError = 'Item_Id is required';
    if (empty($_POST['item_name'])) $itemnameError = 'Item_Name is required';
    if (empty($_POST['categories_id'])) $categoriesidError = 'Categories_Id is required';
    if (empty($_POST['original_price'])) $original_priceError = 'Original_Price is required';
    if (empty($_POST['selling_price'])) $selling_priceError = 'Selling_Price is required';
    if (empty($_POST['reorder_level'])) $reorder_levelError = 'Reorder Level is required';
    if (empty($_POST['expiry_date'])) $expiry_dateError = 'Expiry Date is required';
    if (empty($_POST['location'])) $locationError = 'Location is required';

  } else {

    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $categories_id = $_POST['categories_id'];
    $original_price = $_POST['original_price'];
    $selling_price = $_POST['selling_price'];
    $reorder_level = $_POST['reorder_level'];
    $expiry_date = $_POST['expiry_date'];
    $location = $_POST['location'];

    /* IMAGE UPLOAD */
    $image_name = null;

    if (!empty($_FILES['item_image']['name'])) {

      $folder = "images/";
      if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
      }

      $image_name = time() . "_" . $_FILES['item_image']['name'];
      $tmp_name = $_FILES['item_image']['tmp_name'];

      move_uploaded_file($tmp_name, $folder . $image_name);
    }

    $stmt = $pdo->prepare("
      INSERT INTO item 
      (item_id, item_name, categories_id, original_price, selling_price, reorder_level, expiry_date, location, item_image)
      VALUES 
      (:item_id, :item_name, :categories_id, :original_price, :selling_price, :reorder_level, :expiry_date, :location, :item_image)
    ");

    $result = $stmt->execute([
      ':item_id' => $item_id,
      ':item_name' => $item_name,
      ':categories_id' => $categories_id,
      ':original_price' => $original_price,
      ':selling_price' => $selling_price,
      ':reorder_level' => $reorder_level,
      ':expiry_date' => $expiry_date,
      ':location' => $location,
      ':item_image' => $image_name
    ]);

    if ($result) {
      echo "<script>
        swal('Success!', 'New Item Added Successfully', 'success').then(() => {
        window.location.href='item.php'; });
      </script>";
    }
  }
}
?>

<div>
  <div class="card w-50" style="margin:auto; margin-top:10px;">
    <div class="card-body">
      <h3>Create New Item</h3>

      <form action="" method="post" enctype="multipart/form-data">

      <!-- Item Code + Item Name -->
      <div class="row">
        <div class="col-6">
          <label class="mt-2">Item Code</label>
          <input type="text" class="form-control" name="item_id">
          <p style="color:red;"><?php echo empty($itemidError)?'':'*'.$itemidError;?></p>
        </div>
        <div class="col-6">
          <label class="mt-2">Item Name</label>
          <input type="text" class="form-control" name="item_name">
          <p style="color:red;"><?php echo empty($itemnameError)?'':'*'.$itemnameError;?></p>
        </div>
      </div>

      <!-- Category -->
      <?php
      $catStmt = $pdo->prepare("SELECT * FROM categories");
      $catStmt->execute();
      $catResult = $catStmt->fetchAll();
      ?>
      <label class="mt-2">Category</label>
      <select name="categories_id" class="form-control">
        <option value="">SELECT CATEGORY</option>
        <?php foreach ($catResult as $value) { ?>
          <option value="<?php echo $value['categories_code']; ?>">
            <?php echo $value['categories_name']; ?>
          </option>
        <?php } ?>
      </select>
      <p style="color:red;"><?php echo empty($categoriesidError)?'':'*'.$categoriesidError;?></p>

      <!-- Original + Selling Price -->
      <div class="row">
        <div class="col-6">
          <label class="mt-2">Original Price</label>
          <input type="number" class="form-control" name="original_price">
          <p style="color:red;"><?php echo empty($original_priceError)?'':'*'.$original_priceError;?></p>
        </div>
        <div class="col-6">
          <label class="mt-2">Selling Price</label>
          <input type="number" class="form-control" name="selling_price">
          <p style="color:red;"><?php echo empty($selling_priceError)?'':'*'.$selling_priceError;?></p>
        </div>
      </div>

      <!-- Reorder Level + Expiry Date -->
      <div class="row">
        <div class="col-6">
          <label class="mt-2">Reorder Level</label>
          <input type="number" class="form-control" name="reorder_level">
          <p style="color:red;"><?php echo empty($reorder_levelError)?'':'*'.$reorder_levelError;?></p>
        </div>
        <div class="col-6">
          <label class="mt-2">Expiry Date</label>
          <input type="date" class="form-control" name="expiry_date">
          <p style="color:red;"><?php echo empty($expiry_dateError)?'':'*'.$expiry_dateError;?></p>
        </div>
      </div>

      <!-- Location + Image -->
      <div class="row">
        <div class="col-6">
          <label class="mt-2">Location</label>
          <input type="text" class="form-control" name="location" placeholder="Rack / Store / Room">
          <p style="color:red;"><?php echo empty($locationError)?'':'*'.$locationError;?></p>
        </div>
        <div class="col-6">
          <label class="mt-2">Item Image</label>
          <input type="file" name="item_image" class="form-control">
        </div>
      </div>

      <!-- Buttons -->
      <div class="row mt-3">
        <div class="col-6">
          <button type="submit" class="add_btn form-control">Add</button>
        </div>
        <div class="col-6">
          <a href="item.php">
            <button type="button" class="add_btn form-control">Back</button>
          </a>
        </div>
      </div>

    </form>

    </div>
  </div>
</div>

<?php include 'footer.html'; ?>
</body>
</html>
