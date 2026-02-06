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
        if (empty($_POST['supplier_id']) || empty($_POST['supplier_name']) || empty($_POST['supplier_phone']) || empty($_POST['supplier_address'])) {
          if (empty($_POST['supplier_id'])) {
            $supplieridError = 'Supplier_Id is required';
          }
          if (empty($_POST['supplier_name'])) {
            $suppliernameError = 'Supplier_Name is required';
          }
          if (empty($_POST['supplier_phone'])) {
            $supplierphoneError = 'Supplier_Phone is required';
          }
          if (empty($_POST['supplier_address'])) {
            $supplieraddressError = 'Supplier_Address is required';
          }
        }else {
          $supplier_id = $_POST['supplier_id'];
          $supplier_name = $_POST['supplier_name'];
          $supplier_phone = $_POST['supplier_phone'];
          $supplier_address = $_POST['supplier_address'];
          $id = $_GET['id'];

          $stmt = $pdo->prepare("UPDATE supplier SET supplier_id=:supplier_id,supplier_name=:supplier_name,supplier_phone=:supplier_phone,supplier_address=:supplier_address WHERE id='$id'");
          $result = $stmt->execute(
            array(':supplier_id'=>$supplier_id, ':supplier_name'=>$supplier_name, ':supplier_phone'=>$supplier_phone, ':supplier_address'=>$supplier_address)
          );
          if ($result) {
            echo "<script>
              swal('Succcess!', 'Supplier Updated Successfully', 'success').then((value) => {
              window.location.href='supplier.php'; });
          </script>";
          }
        }
      }

      $stmt = $pdo->prepare("SELECT * FROM supplier WHERE id=".$_GET['id']);
      $stmt->execute();
      $result = $stmt->fetchAll();
     ?>

     <div class="w-50 mx-auto mt-5">
       <div class="card crd">
         <div class="card-body">
           <h2>Update Page</h2>
           <form class="" action="" method="post">
             <label for="" class="mt-4"><b>Supplier_Id</b></label>
             <input type="text" class="form-control" placeholder="Supplier_Id" name="supplier_id" value="<?php echo $result[0]['supplier_id']; ?>">
             <p style="color:red;"><?php echo empty($supplieridError) ? '' : '*'.$supplieridError;?></p>

             <label for="" class="mt-4"><b>Supplier_Name</b></label>
             <input type="text" class="form-control" placeholder="Supplier_Name" name="supplier_name" value="<?php echo $result[0]['supplier_name']; ?>">
             <p style="color:red;"><?php echo empty($suppliernameError) ? '' : '*'.$suppliernameError;?></p>

             <label for="" class="mt-4"><b>Supplier_Phone</b></label>
             <input type="number" class="form-control" placeholder="Supplier_Phone" name="supplier_phone" value="<?php echo $result[0]['supplier_phone'];?>">
             <p style="color:red;"><?php echo empty($supplierphoneError) ? '' : '*'.$supplierphoneError;?></p>

             <label for="" class="mt-4"><b>Supplier_Address</b></label>
             <input type="text" class="form-control" placeholder="Supplier_Address" name="supplier_address" value="<?php echo $result[0]['supplier_address'];?>">
             <p style="color:red;"><?php echo empty($supplieraddressError) ? '' : '*'.$supplieraddressError;?></p>

             <div class="d-flex">
               <button type="submit" name="button" class="add_btn form-control mt-3">Update</button>
               <a href="supplier.php" style="width:450px;"><button type="button" name="button" class="add_btn form-control mt-3">Back</button></a>
             </div>
           </form>
         </div>
       </div>
     </div>

    <?php include 'footer.html'; ?>

  </body>
</html>
