<?php
session_start();
require '../config/config.php';
require '../config/common.php';


  ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  
  <body>
    <?php include 'header.php';?>

    <form class="" action="" method="post">
      <div class="d-flex">





      </div>



      <label for="" class="mt-4"><b>Item_Name</b></label>
      <input type="text" class="form-control" placeholder="Item_Name" name="item_name">

      <label for="" class="mt-4"><b>Price</b></label>
      <input type="number" class="form-control" placeholder="Price" name="price">

      <label for="" class="mt-4"><b>Qty</b></label>
      <input type="number" class="form-control" placeholder="Qty" name="qty">

      <div class="d-flex">

        <a href="temp.php" style="width:450px;"><button type="button" name="button" class="add_btn form-control mt-3">Back</button></a>
      </div>
    </form>


    <?php include 'footer.html'; ?>
  </body>
</html>
