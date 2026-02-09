<?php
session_start();
require '../config/config.php';
require '../config/common.php';
  ?>

  <?php include 'header.php'; ?>

<?php
    $item_id = $_GET['item_id'];
    $stockstmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id'");
    $stockstmt->execute();
    $stockdata = $stockstmt->fetchAll();

    // Item Name
    $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
    $itemstmt->execute();
    $item = $itemstmt->fetch(PDO::FETCH_ASSOC);
 ?>

<div class="col-md-12 px-3 mt-4">
  <div class="d-flex justify-content-between px-2">
    <div>
      <h4><?php echo $item['item_name']; ?> - Detail</h4>
    </div>
    <div>
      <a href="index.php">
        Home
      </a>
      /
      <a href="stock_control.php">
        Stock Control
      </a>
    </div>
  </div>
  <div class="outer" style="margin-top:-10px;">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Date</th>
          <th>To / From</th>
          <th>GRN_No</th>
          <th>GIN_No</th>
          <th class="text-center">In</th>
          <th class="text-center">Out</th>
          <th class="text-center">Balance</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($stockdata) {
            $id = 1;
            foreach ($stockdata as $value) {
         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo date('d M Y', strtotime($value['date']));?></td>
          <td><?php echo $value['to_from'];?></td>
          <td><?php echo $value['grn_no'];?></td>
          <td><?php echo $value['gin_no'];?></td>
          <td class="text-center">
            <?php 
             if(!empty($value['in_qty'])){
                if($value['foc_qty'] != 0){
                    echo $value['in_qty'];
                    ?>
                    <span class="badge badge-success">foc +<?php echo $value['foc_qty']; ?></span>
                    <?php 
                }else{ 
                    echo $value['in_qty'];              
                }
              }else{
                  echo '-';
             }
            ?>
          </td>
          <td class="text-center">
            <?php 
              if(!empty($value['out_qty'])){
                  if($value['foc_qty'] != 0){
                      echo $value['out_qty'];
                      ?>
                      <span class="badge badge-success">foc +<?php echo $value['foc_qty']; ?></span>
                      <?php 
                  }else{ 
                      echo $value['out_qty'];              
                  }
                }else{
                    echo '-';
              }
              ?>  
          </td>
          <td class="text-center"><?php echo $value['balance'];?></td>
        </tr>
        <?php
          $id++;
            }
          }
         ?>
      </tbody>
    </table>
  </div>
</div>


  <?php include 'footer.html'; ?>
