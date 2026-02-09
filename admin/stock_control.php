<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
  ?>

  <?php include 'header.php'; ?>
<?php

if(isset($_POST['save'])){
  $date = $_POST['date'];
  $gin_no = $_POST['gin_no'];
  $item_id = $_POST['item_id'];
  $qty = $_POST['qty'];

  // receivable Balance
  $stock_balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
  $stock_balancestmt->execute();
  $stock_balancedata = $stock_balancestmt->fetch(PDO::FETCH_ASSOC);
  $last_balance = $stock_balancedata ? (int)$stock_balancedata['balance'] : 0;
  $balance = $last_balance - (int)$qty;

  $receivablestmt = $pdo->prepare("
    INSERT INTO stock (date, item_id, to_from, in_qty, out_qty, foc_qty, balance, grn_no, gin_no)
    VALUES (:date, :item_id, 'damage', 0, :out_qty, 0, :balance, NULL, :gin_no)
  ");
  $receivabledata = $receivablestmt->execute(
    array(':date'=>$date, ':gin_no'=>$gin_no, ':item_id'=>$item_id, ':out_qty'=>$qty, ':balance'=>$balance)
  );
}

?>
<?php
    $stockstmt = $pdo->prepare("SELECT DISTINCT item_id FROM stock");
    $stockstmt->execute();
    $stockdata = $stockstmt->fetchAll();
 ?>

 <!-- <form class="" action="" method="post">
   <div class="d-flex" style="margin-left:950px; margin-top:-15px;">
     <input type="date" name="" value="" class="form-control" placeholder="Search Supplier_Name" style="width:200px;">
     <button type="submit" name="search" class="search_btn ms-3">Search</button>
  </div>
 </form> -->

<div class="col-md-12 px-3 mt-4">
  <div class="d-flex justify-content-between px-2">
  <div>
    <h4>Stock Listings</h4>
  </div>
  <div class="d-flex align-items-center">
      <!-- Color Indicators -->
      <div class="d-flex align-items-center ms-2 mr-3">
        <div class="tooltip-square mr-2" style="background-color:lightblue;">
          <span class="tooltip-text">Stock is safe</span>
        </div>
        <div class="tooltip-square mr-2" style="background-color:blue;">
          <span class="tooltip-text">At reorder level</span>
        </div>
        <div class="tooltip-square ms-2" style="background-color:red;">
          <span class="tooltip-text">Below reorder level</span>
        </div>
      </div>
      <!-- Damage Stock Button -->
      <button data-toggle="modal" data-target="#myModal" class="btn btn-warning text-muted btn-sm me-3">
        Damage Stock
        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" style="margin-top: -5px;" viewBox="0 0 16 16">
          <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
          <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
        </svg> -->
      </button>

    </div>
  </div>

  <div class="outer">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th>No</th>
          <th>Item Name</th>
          <th>Balance</th>
          <th>Reorder Level</th>
          <th style="width: 100px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($stockdata) {
            $id = 1;
            foreach ($stockdata as $value) {
              $item_id = $value['item_id'];

              // Item Name
              $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
              $itemstmt->execute();
              $item = $itemstmt->fetch(PDO::FETCH_ASSOC);
              $reorder_level = $item['reorder_level'];
              
              $balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
              $balancestmt->execute();
              $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);
              $balance = $balancedata['balance'];
         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo $item['item_name'];?></td>
          <td><?php echo $balance;?></td>
          <td><?php echo $reorder_level;?></td>
          <td class="d-flex justify-content-around">
              <a href="stock_detail.php?item_id=<?php echo $item_id; ?>"
              class="btn btn-sm btn-primary text-light"
              data-bs-toggle="tooltip" data-bs-placement="top" title="View Stock Details">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                  <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                  <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                </svg>
            </a>
            <div style="width:30px; height: 30px; border-radius: 5px; <?php if($balance < $reorder_level){ echo "background-color: red !important;"; }elseif($balance == $reorder_level){ echo "background-color: blue !important;"; }else{ echo "background-color: lightblue !important;"; } ?>"></div>
          </td>
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
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Damage Stock</h4>
      </div>
      <div class="modal-body">
        <form action="" method="post">
          <div class="row">
            <div class="col">
              <label for="">Date</label>
              <input type="date" class="border border-dark form-control" name="date">
            </div>
            <div class="col">
              <label for="">GIN NO</label>
              <input type="text" class="form-control border border-dark" name="gin_no">
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <label for="">Item Name</label>
              <select name="item_id" id="" class="form-control border border-dark">
                <?php
                  $itemstmt = $pdo->prepare("SELECT DISTINCT item_id FROM stock ORDER BY id DESC");
                  $itemstmt->execute();
                  $itemdata = $itemstmt->fetchAll();
                  foreach ($itemdata as $item) {
                    $item_id = $item['item_id'];
                    $namestmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                    $namestmt->execute();
                    $name = $namestmt->fetch(PDO::FETCH_ASSOC);
                    ?>                
                    <option value="<?php echo $item['item_id']; ?>"><?php echo $name['item_name']; ?></option>
                    <?php
                  }
                ?>
              </select>
            </div>
            <div class="col">
              <label for="">Qty</label>
              <input type="number" class="form-control border border-dark" name="qty">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save" class="btn btn-sm btn-purple text-light">Add Stock</button>
          <button type="button" data-dismiss="modal" class="btn btn-sm btn-danger">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  });
</script>

<?php include 'footer.html'; ?>
