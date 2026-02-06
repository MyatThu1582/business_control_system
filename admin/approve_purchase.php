<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';

  ?>
 <?php include 'header.php';?>

<?php

  //  Save Purchase
  if (isset($_POST['save_btn'])) {
    $grn_no = $_POST['grn_no'];

    $temp_purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE status='draft' AND grn_no='$grn_no'");
    $temp_purchasestmt->execute();
    $temp_purchase = $temp_purchasestmt->fetch(PDO::FETCH_ASSOC);

    $id = $temp_purchase['id'];
    $date = $temp_purchase['date'];
    $grn_no = $temp_purchase['grn_no'];
    $po_no = $temp_purchase['po_no'];
    $supplier_id = $temp_purchase['supplier_id'];
    $type = $temp_purchase['type'];

    $temp_purchase_itemstmt = $pdo->prepare("SELECT * FROM temp_purchase_items WHERE grn_no='$grn_no'");
    $temp_purchase_itemstmt->execute();
    $temp_purchase_item = $temp_purchase_itemstmt->fetchAll();

    foreach ($temp_purchase_item as $value) {
      $item_id = $value['item_id'];
      $amount = $value['amount'];
      $qty = $value['qty'];
      $foc = $value['stock_foc'];
      
      // Add Credit Purchase
      if ($type == "credit") {
        $parstmt = $pdo->prepare("
          INSERT INTO credit_purchase (date, grn_no, supplier_id, item_id, qty, po_no, amount)
          VALUES (:date, :grn_no, :supplier_id, :item_id, :qty, :po_no, :amount)
        ");
        $parResult = $parstmt->execute(
          array(
            ':date'=>$date,
            ':grn_no'=>$grn_no,
            ':supplier_id'=>$supplier_id,
            ':item_id'=>$item_id,
            ':qty'=>$qty,
            ':po_no'=>$po_no,
            ':amount'=>$amount
          )
        );

      }else {
      // Add Cash Purchase
        $cashstmt = $pdo->prepare("
          INSERT INTO cash_purchase (date, grn_no, supplier_id, item_id, qty, po_no, amount)
          VALUES (:date, :grn_no, :supplier_id, :item_id, :qty, :po_no, :amount)
        ");
        $cashResult = $cashstmt->execute(
          array(
            ':date'=>$date,
            ':grn_no'=>$grn_no,
            ':supplier_id'=>$supplier_id,
            ':item_id'=>$item_id,
            ':qty'=>$qty,
            ':po_no'=>$po_no,
            ':amount'=>$amount
          )
        );
      }

      // Add Stock
      
      // Stock Balance
      $stock_balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
      $stock_balancestmt->execute();
      $stock_balancedata = $stock_balancestmt->fetch(PDO::FETCH_ASSOC);
  
      if (!empty($stock_balancedata)) {
        $oldbalance = $stock_balancedata['balance'];
      }else{
        $oldbalance = 0;
      }
      $stockbalance = $oldbalance + $qty + $foc;
  
      // Foc Check
      if($foc != 0){
        $in_qty = $qty + $foc;
      }else{
        $in_qty = $qty;
      }
  
      $stockstmt = $pdo->prepare("
        INSERT INTO stock (date, item_id, to_from, in_qty, out_qty, foc_qty, balance, grn_no, gin_no)
        VALUES (:date, :item_id, 'purchase', :in_qty, 0, :foc_qty, :balance, :grn_no, NULL)
      ");
      $stockdata = $stockstmt->execute(
        array(':date'=>$date, ':grn_no'=>$grn_no, ':item_id'=>$item_id, ':in_qty'=>$in_qty, ':foc_qty'=>$foc, ':balance'=>$stockbalance)
      );

    }

    
    // Add Payable
    if($type == "credit"){
      // Purchase Id
      $purchase_idstmt = $pdo->prepare("SELECT * FROM credit_purchase WHERE grn_no = '$grn_no' ORDER BY id DESC");
      $purchase_idstmt->execute();
      $purchase_data = $purchase_idstmt->fetch(PDO::FETCH_ASSOC);
      
      $purchase_id = $purchase_data['id'];
      $supplier_id = $purchase_data['supplier_id'];
      
      // total amount
      $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_purchase WHERE grn_no = '$grn_no'");
      $total_amountstmt->execute();
      $total_amountresult = $total_amountstmt->fetch(PDO::FETCH_ASSOC);

      $amount = $total_amountresult['total_amount'];

      // Payable Balance
      $payabl_balancestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' ORDER BY id DESC");
      $payabl_balancestmt->execute();
      $payabl_balancedata = $payabl_balancestmt->fetch(PDO::FETCH_ASSOC);

      $balance = $amount;
      
      $payablstmt = $pdo->prepare("
        INSERT INTO payable
          (date, grn_no, supplier_id, amount, paid, balance, purchase_id, asc_id, group_id, status, payment_no, account_name, remark)
        VALUES
          (:date, :grn_no, :supplier_id, :amount, 0, :balance, :purchase_id, 0, :group_id, 'Pending', '', '', '')
      ");
      $payabldata = $payablstmt->execute(
        array(
          ':date'=>$date,
          ':grn_no'=>$grn_no,
          ':supplier_id'=>$supplier_id,
          ':amount'=>$amount,
          ':purchase_id'=>$purchase_id,
          ':group_id'=>$grn_no,
          ':balance'=>$balance
        )
      );
    }

    // update temp purchase status
    $updatestmt = $pdo->prepare("UPDATE temp_purchase SET status='approved' WHERE id='$id'");
    $updatestmt->execute();

    echo "<script>sessionStorage.setItem('purchaseApproved', 'true');
                window.location.href = 'purchase.php';
            </script>";
    
  }

  $grn_no = $_GET['grn_no'];

  $temp_purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE grn_no='$grn_no' ORDER BY id DESC");
  $temp_purchasestmt->execute();
  $temp_purchaseresult = $temp_purchasestmt->fetch(PDO::FETCH_ASSOC);

  $temp_purchase_itemstmt = $pdo->prepare("SELECT * FROM temp_purchase_items WHERE grn_no='$grn_no' ORDER BY id DESC");
  $temp_purchase_itemstmt->execute();
  $temp_purchase_itemresult = $temp_purchase_itemstmt->fetchAll();
  ?>
    
    <div class="col-md-12 px-3 pt-1">
      <div class="collapse show">
        <form class="" action="" method="post">
          <input type="hidden" name="purchase_id" value="<?php echo $_GET['temp_purchaseid']; ?>">
          <div class="card">
            <div class="card-header py-3 bg-lightgreen">
              <h5 class="d-flex align-items-center justify-content-between">
                Approve Purchase - <?php echo $_GET['grn_no']; ?>
              </h5>
            </div>
            <div class="card-body" style="background-color: rgba(0,0,0,0.01);">

              <!-- Readonly for Inputs -->
              <?php 
              $isReadOnly = (
                  isset($_GET['status'], $_GET['action']) 
                  && $_GET['status'] === 'draft' 
                  && $_GET['action'] === 'approve'
              ) ? 'readonly' : ''; 
              ?>
              <div class="row">
                <div class="col-6 d-flex">
                  <div class="col">
                    <label for="">Date</label>
                    <input type="date" class="form-control" value="<?php echo $temp_purchaseresult['date']; ?>" placeholder="Date" name="date" <?php echo $isReadOnly; ?>>
                    <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
                  </div>
                  <div class="col">
                    <label for="">GRN_No</label>
                    <input type="text" class="form-control" placeholder="GRN No" name="grn_no" value="<?php echo $temp_purchaseresult['grn_no']; ?>" <?php echo $isReadOnly; ?>>
                    <p style="color:red;"><?php echo empty($grn_noError) ? '' : '*'.$grn_noError;?></p>
                  </div>
                  <div class="col">
                  <label for="">PO No</label>
                  <select name="po_no" id="po_no" class="form-control" value="<?php echo $temp_purchaseresult['po_no']; ?>" <?php echo $isReadOnly; ?>>
                    <option value="">Select PO_No</option>
                    <?php
                    $po_nostmt = $pdo->prepare("SELECT * FROM purchase_order WHERE status LIKE '%ending%' ORDER BY id DESC");
                    $po_nostmt->execute();
                    $po_nodatas = $po_nostmt->fetchAll();
                    foreach ($po_nodatas as $po_nodata) {
                      ?>
                      <option value="<?php echo $po_nodata['order_no']; ?>"><?php echo $po_nodata['order_no']; ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </div>
                </div>
              
                <div class="col-2">
                  <label for="">Supplier_Id</label>
                  <input type="text" id="supplier_id" oninput="fetchSupplierNameFromId()" class="form-control" placeholder="Supplier_Id" name="supplier_id" value="<?php echo $temp_purchaseresult['supplier_id']; ?>" <?php echo $isReadOnly; ?>>
                  <p style="color:red;"><?php echo empty($supplier_idError) ? '' : '*'.$supplier_idError;?></p>
                </div>
                <div class="col-2">
                  <label for="">Supplier_Name</label>
                  <input type="text" id="supplier_name" class="form-control" placeholder="Supplier_Name" name="supplier_name" oninput="fetchSupplierIdFromName()" <?php echo $isReadOnly; ?>>
                </div>
                <div class="col-2">
                  <label for="">Payment</label>
                  <select name="type" class="form-control" <?php echo $isReadOnly; ?>>
                      <option value="cash" <?php if($temp_purchaseresult['type'] == 'cash'){ echo "selected"; } ?>>Cash</option>
                      <option value="credit" <?php if($temp_purchaseresult['type'] == 'credit'){ echo "selected"; } ?>>Credit</option>
                    </select>
                </div>
              </div>
              <div class="pl-2 pt-3">
                  <table class="table table-hover table-bordered">
                    <thead class="table-sm" style="background-color: #f4f4f4;">
                    <tr>
                        <th>Item id</th>
                        <th>Item Name</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Discount %</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Foc</th>
                        <th colspan="2">Amount</th>
                    </tr>
                    </thead>
                    <tbody id="item-rows">
                      <?php
                        if ($temp_purchase_itemresult) {
                          $id = 1;
                          foreach ($temp_purchase_itemresult as $value) {
                              $grn_no = $value['grn_no'];
                              $item_id = $value['item_id'];
                              $itemIdstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                              $itemIdstmt->execute();
                              $itemIdResult = $itemIdstmt->fetch(PDO::FETCH_ASSOC);
                      ?>
                      <tr class="item-row" style="font-size: 15px;">
                          <td class="no-padding"> 
                            <input type="text" 
                                  value="<?php echo $item_id; ?>" 
                                  class="custom-input item_id" 
                                  name="item_id[]" 
                                  oninput="fetchItemNameFromId(this)" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="text" 
                                  value="<?php echo $itemIdResult['item_name']; ?>" 
                                  class="custom-input item_name" 
                                  name="item_name[]" 
                                  oninput="fetchItemIdFromName(this)" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="<?php echo $value['price']; ?>" 
                                  class="custom-input text-right original_price" 
                                  name="original_price[]" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="<?php echo $value['percentage']; ?>" 
                                  class="custom-input text-right discount" 
                                  name="discount[]" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="<?php echo $value['qty']; ?>" 
                                  class="custom-input text-right qty" 
                                  name="qty[]" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="<?php echo $value['stock_foc']; ?>" 
                                  class="custom-input text-right foc" 
                                  name="foc[]" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="<?php echo $value['amount']; ?>" 
                                  class="custom-input text-right" 
                                  name="amount[]" <?php echo $isReadOnly; ?>>
                          </td>

                          <td class="no-padding text-center" style="background:none !important; cursor:pointer !important; width: 30px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                class="bi bi-x-lg remove-row-btn" viewBox="0 0 16 16">
                              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                            </svg>
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

                <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
                  <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-1">
                        <div class="">
                          <?php 
                          $grn_no = $_GET['grn_no'];
                          $temp_purchaseid = $_GET['temp_purchaseid'];
                          $status = $_GET['status'];
                          ?>
                          <a href="purchase_detail.php?grn_no=<?php echo $grn_no; ?>&temp_purchaseid=<?php echo $temp_purchaseid; ?>&status=<?php echo $status; ?>" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                          <?php 
                            if($_GET['status'] != 'approved'){
                              ?>
                                <button type="submit" name="save_btn" class="btn btn-purple btn-sm text-light ml-1">Approve Purchase</button>
                              <?php
                            }
                          ?>
                        </div>
                    </div>
                </div>
          </div>
        </form>
      </div>
    </div>
<?php include 'footer.html'; ?>