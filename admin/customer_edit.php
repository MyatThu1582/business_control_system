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
        if (empty($_POST['customer_id']) || empty($_POST['customer_name']) || empty($_POST['customer_phone']) || empty($_POST['customer_address'])) {
          if (empty($_POST['customer_id'])) {
            $customeridError = 'Customer_Id is required';
          }
          if (empty($_POST['customer_name'])) {
            $customernameError = 'Customer_name is required';
          }
          if (empty($_POST['customer_phone'])) {
            $customerphoneError = 'Customer_phone is required';
          }
          if (empty($_POST['customer_address'])) {
            $customeraddressError = 'Customer_address is required';
          }
        }else {
          $customer_id = $_POST['customer_id'];
          $customer_name = $_POST['customer_name'];
          $customer_phone = $_POST['customer_phone'];
          $customer_address = $_POST['customer_address'];
          $id = $_GET['id'];

          $stmt = $pdo->prepare("UPDATE customer SET customer_id=:customer_id,customer_name=:customer_name,customer_phone=:customer_phone,customer_address=:customer_address WHERE id='$id'");
          $result = $stmt->execute(
            array(':customer_id'=>$customer_id, ':customer_name'=>$customer_name, ':customer_phone'=>$customer_phone, ':customer_address'=>$customer_address)
          );
          if ($result) {
            echo "<script>
              swal('Succcess!', 'Customer Updated Successfully', 'success').then((value) => {
              window.location.href='customer.php'; });
          </script>";
          }
        }
      }

      $stmt = $pdo->prepare("SELECT * FROM customer WHERE id=".$_GET['id']);
      $stmt->execute();
      $result = $stmt->fetchAll();
     ?>

     <div class="w-50 mx-auto mt-5">
       <div class="card crd">
         <div class="card-body">
           <h2>Update Page</h2>
           <form class="" action="" method="post">
             <label for="" class="mt-4"><b>Customer_Id</b></label>
             <input type="text" class="form-control" placeholder="Categories_Code" name="customer_id" value="<?php echo $result[0]['customer_id'];?>">
             <p style="color:red;"><?php echo empty($customeridError) ? '' : '*'.$customeridError;?></p>

             <label for="" class="mt-4"><b>Customer_Name</b></label>
             <input type="text" class="form-control" placeholder="Categories_Name" name="customer_name" value="<?php echo $result[0]['customer_name'];?>">
             <p style="color:red;"><?php echo empty($customernameError) ? '' : '*'.$customernameError;?></p>


             <label for="" class="mt-4"><b>Customer_Phone</b></label>
             <input type="number" class="form-control" placeholder="Customer_Phone" name="customer_phone" value="<?php echo $result[0]['customer_phone'];?>">
             <p style="color:red;"><?php echo empty($customerphoneError) ? '' : '*'.$customerphoneError;?></p>

             <label for="" class="mt-4"><b>Customer_Address</b></label>
             <input type="text" class="form-control" placeholder="Customer_Address" name="customer_address" value="<?php echo $result[0]['customer_address'];?>">
             <p style="color:red;"><?php echo empty($customeraddressError) ? '' : '*'.$customeraddressError;?></p>

             <div class="d-flex">
               <button type="submit" name="button" class="add_btn form-control mt-3">Update</button>
               <a href="customer.php" style="width:450px;"><button type="button" name="button" class="add_btn form-control mt-3">Back</button></a>
             </div>
           </form>
         </div>
       </div>
     </div>

    <?php include 'footer.html'; ?>

  </body>
</html>
