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

  if (isset($_POST['edit_btn'])) {

    // --- Validate static fields ---
      if (empty($_POST['date'])) {
          $dateError = 'Date is required';
      }
      if (empty($_POST['grn_no'])) {
          $grn_noError = 'GRN No is required';
      }
      if(empty($_POST['po_no'])){
          if (empty($_POST['supplier_id'])) {
              $supplier_idError = 'Supplier is required';
          }
      }

      // --- Validate dynamic arrays ---
      $item_ids = $_POST['item_id'] ?? [];
      $qtys = $_POST['qty'] ?? [];
      $prices = $_POST['original_price'] ?? [];
      $discounts = $_POST['discount'] ?? [];
      $focs = $_POST['foc'] ?? [];

      $hasItemError = false;
      foreach ($item_ids as $index => $item_id) {
          $qty = trim($qtys[$index] ?? '');
          $price = trim($prices[$index] ?? '');

          if (empty($item_id)) {
              $item_idError = "Item ID is required in row " . ($index + 1);
              $hasItemError = true;
              break;
          }
          if (empty($qty)) {
              $qtyError = "Qty is required in row " . ($index + 1);
              $hasItemError = true;
              break;
          }
          if (empty($price)) {
              $priceError = "Price is required in row " . ($index + 1);
              $hasItemError = true;
              break;
          }
      }

      // --- If no errors, process the data ---
      if (empty($dateError) && empty($grn_noError) && empty($supplier_idError) && !$hasItemError) {

          $date = $_POST['date'];
          $grn_no = $_POST['grn_no'];
          $type = $_POST['type'];
          $po_no = $_POST['po_no'] ?? '';
          $purchase_id = $_POST['purchase_id']; // Hidden input for editing

          if(empty($po_no)){
              $supplier_id = $_POST['supplier_id'];
          }

          // --- Update main purchase table ---
          $updatestmt = $pdo->prepare("
              UPDATE temp_purchase 
              SET date = :date,
                  grn_no = :grn_no,
                  supplier_id = :supplier_id,
                  po_no = :po_no,
                  type = :type,
                  status = 'draft'
              WHERE id = :purchase_id
          ");

          $updatestmt->execute([
              ':date' => $date,
              ':grn_no' => $grn_no,
              ':supplier_id' => $supplier_id,
              ':po_no' => $po_no,
              ':type' => $type,
              ':purchase_id' => $purchase_id
          ]);

          // --- Delete existing items and reinsert (simplest + safe method) ---
          $delstmt = $pdo->prepare("DELETE FROM temp_purchase_items WHERE temp_purchase_id = :purchase_id");
          $delstmt->execute([':purchase_id' => $purchase_id]);

          $add_itemstmt = $pdo->prepare("
              INSERT INTO temp_purchase_items
              (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, grn_no, temp_purchase_id)
              VALUES
              (:item_id, :price, :qty, :type, :percentage, :percentage_amount, :stock_foc, :amount, :grn_no, :temp_purchase_id)
          ");

          foreach ($item_ids as $index => $item_id) {
              $qty = $qtys[$index];
              $price = $prices[$index];
              $foc = $focs[$index] ?? 0;
              $discount = $discounts[$index] ?? 0;

              $amount = $price * $qty;

              if (!empty($discount) && $discount > 0) {
                  $percentage_amount = ($amount / 100) * $discount;
                  $amount = $amount - $percentage_amount;
              } else {
                  $discount = 0;
                  $percentage_amount = 0;
              }

              $add_itemstmt->execute([
                  ':item_id' => $item_id,
                  ':price' => $price,
                  ':qty' => $qty,
                  ':type' => $type,
                  ':percentage' => $discount,
                  ':percentage_amount' => $percentage_amount,
                  ':stock_foc' => $foc,
                  ':amount' => $amount,
                  ':grn_no' => $grn_no,
                  ':temp_purchase_id' => $purchase_id
              ]);
          }

          echo "<script>sessionStorage.setItem('purchaseUpdated', 'true');
              </script>";

      }
  }

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

  $supplier_id = $temp_purchaseresult['supplier_id'] ?? '';
  $supplierNamestmt = $pdo->prepare("SELECT supplier_name FROM supplier WHERE supplier_id = :id");
  $supplierNamestmt->execute([':id' => $supplier_id]);
  $supplierNameRow = $supplierNamestmt->fetch(PDO::FETCH_ASSOC);
  $supplier_name = $supplierNameRow ? $supplierNameRow['supplier_name'] : '';

  $temp_purchase_itemstmt = $pdo->prepare("SELECT * FROM temp_purchase_items WHERE grn_no='$grn_no' ORDER BY id DESC");
  $temp_purchase_itemstmt->execute();
  $temp_purchase_itemresult = $temp_purchase_itemstmt->fetchAll();
  ?>
<style>
.supplier-typeahead, .item-typeahead { position: relative; }
.supplier-typeahead-dropdown, .item-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.supplier-typeahead-dropdown.show, .item-typeahead-dropdown.show { display: block; }
.supplier-typeahead-dropdown .option, .item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.supplier-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option:hover, .supplier-typeahead-dropdown .option.active, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.supplier-typeahead-dropdown .no-result, .item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>
    
<script>
</script>
    <div class="col-md-12 px-3 pt-1">
      <div class="collapse show">
        <form class="" action="" method="post">
          <input type="hidden" name="purchase_id" value="<?php echo $_GET['temp_purchaseid']; ?>">
          <div class="card">
            <div class="card-header py-2 pb-0 pt-3 bg-">
              <h5 class="d-flex align-items-center justify-content-between">
                Edit Purchase - <?php echo $_GET['grn_no']; ?>
                <div class="d-flex">
                  <div class="dropdown">
                    <button 
                      class="btn btn-sm btn-primary dropdown-toggle fw-semibold shadow-sm" 
                      type="button" 
                      id="purchaseOptionsDropdown" 
                      data-bs-toggle="dropdown" 
                      aria-expanded="false"
                      style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                      Purchase Options
                    </button>

                    <ul class="dropdown-menu border-0 shadow p-0" aria-labelledby="purchaseOptionsDropdown" 
                        style="border-radius: 3px; overflow: hidden; min-width: 140px;">
                      
                      <?php 
                      if($_GET['status'] == 'draft'){
                        ?>
                        <li>
                          <?php
                            $stmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE grn_no='$grn_no' ORDER BY id DESC");
                            $stmt->execute();
                            $data = $stmt->fetch(PDO::FETCH_ASSOC);
                          ?>
                          <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" href="approve_purchase.php?grn_no=<?php echo $data['grn_no']; ?>&temp_purchaseid=<?php echo $data['id']; ?>&status=<?php echo $data['status']; ?>&action=approve"
                            style="transition: background 0.2s;">
                            <i class="bi bi-arrow-counterclockwise text-primary"></i>
                            <span style="font-size: 13px;">
                              Approve Purchase
                            </span>
                          </a>
                        </li>
                        <hr style="margin: 0px;">
                        <?php
                      }
                      ?>
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                          href=""
                          onclick="
                              let status = '<?php echo $_GET['status']; ?>';
                              if (status === 'draft') {
                                  swal('', 'You may only return Done pickings.', 'warning');
                              } else {
                                  window.location.href = 'add_purchase_return.php?grn_no=<?php echo $_GET['grn_no']; ?>&temp_purchaseid=<?php echo $_GET['temp_purchaseid']; ?>';
                              }
                              return false;
                          "
                          style="transition: background 0.2s;">
                          
                          <i class="bi bi-arrow-counterclockwise text-primary"></i>
                          <span style="font-size: 13px;">Purchase Return</span>
                        </a>
                      </li>

                      <hr style="margin: 0px;">
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 delete-temp-purchase" href="delete.php?table_name=temp_purchase&id=<?php echo $_GET['temp_purchaseid']; ?>&grn_no=<?php echo $_GET['grn_no']; ?>" 
                          style="transition: background 0.2s;">
                          <i class="bi bi-trash3 text-danger"></i>
                          <span class="text-danger" style="font-size: 13px;">Delete</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </h5>
            </div>
            <div class="card-body" style="background-color: rgba(0,0,0,0.01);">

              <!-- Readonly for Inputs -->
              <?php $isReadOnly = (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'readonly' : ''; ?>
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
              
                <div class="col-4">
                  <label for="">Supplier</label>
                  <div class="supplier-typeahead">
                    <input type="text" class="form-control supplier-typeahead-input" placeholder="Type supplier code or name..." value="<?php echo htmlspecialchars($supplier_id . ' - ' . $supplier_name); ?>" <?php echo $isReadOnly; ?> autocomplete="off">
                    <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier_id); ?>">
                    <div class="supplier-typeahead-dropdown"></div>
                  </div>
                  <p style="color:red;"><?php echo empty($supplier_idError) ? '' : '*'.$supplier_idError;?></p>
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
                        <th style="min-width: 200px;">Item</th>
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
                          <td class="no-padding" style="min-width: 200px;">
                            <div class="item-typeahead">
                              <input type="text" class="custom-input item-typeahead-input" placeholder="Type item code or name..." value="<?php echo htmlspecialchars($item_id . ' - ' . ($itemIdResult['item_name'] ?? '')); ?>" <?php echo $isReadOnly; ?> autocomplete="off">
                              <input type="hidden" name="item_id[]" value="<?php echo htmlspecialchars($item_id); ?>">
                              <input type="hidden" name="item_name[]" value="<?php echo htmlspecialchars($itemIdResult['item_name'] ?? ''); ?>">
                              <div class="item-typeahead-dropdown"></div>
                            </div>
                            <span class="stock_balance" style="color:green; font-size: 13px;"></span>
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
              <div>
                <?php
                  if($_GET['status'] != 'approved'){
                    ?>
                    <button type="button" id="add-row-btn" class="btn btn-default text-info btn-sm ml-2">+ Add a new line</button>
                    <?php
                  }
                ?>
              </div>
            </div>

                <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
                  <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-1">
                      <?php 
                        if($_GET['status'] != 'approved'){
                      ?>
                      <?php
                        }
                      ?>
                        <div>
                          <a href="purchase.php" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                          <?php 
                            if($_GET['status'] != 'approved'){
                              ?>
                                <button type="submit" name="edit_btn" class="btn btn-purple btn-sm text-light ml-1">Save Purchase</button>
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
<script>
  document.addEventListener('input', function(e) {
    // only run when user changes qty or price
    if (e.target.classList.contains('qty') || e.target.classList.contains('original_price')) {
      const row = e.target.closest('.item-row');
      const priceInput = row.querySelector('.original_price');
      const qtyInput = row.querySelector('.qty');
      const amountInput = row.querySelector('input[name="amount[]"]');

      const price = parseFloat(priceInput.value) || 0;
      const qty = parseFloat(qtyInput.value) || 0;

      const amount = price * qty;
      amountInput.value = amount.toFixed(2);
    }
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('item-rows');
    const addBtn = document.getElementById('add-row-btn');

    addBtn.addEventListener('click', function () {
        const firstRow = container.querySelector('.item-row');
        if (!firstRow) return;

        const clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
        clone.querySelectorAll('.stock_balance').forEach(function(s) { s.innerText = ''; });
        var dd = clone.querySelector('.item-typeahead-dropdown');
        if (dd) { dd.innerHTML = ''; dd.classList.remove('show'); }
        
        if ("<?php echo $isReadOnly; ?>" === "readonly") {
            clone.querySelectorAll('input').forEach(function(inp) { inp.setAttribute('readonly', true); });
            clone.querySelectorAll('.remove-row-btn').forEach(function(btn) { btn.style.pointerEvents = 'none'; });
        } else if (window.initItemTypeahead) {
            var tw = clone.querySelector('.item-typeahead');
            if (tw) window.initItemTypeahead(tw);
        }

        container.appendChild(clone);
    });

    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-row-btn')) {
            const row = e.target.closest('.item-row');
            if (row) row.remove();
        }
    });
});
</script>


<script>
  document.querySelectorAll('.delete-temp-purchase').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this Purchase!",
              icon: "warning",
              buttons: ["Cancel", "Yes, delete it!"],
              dangerMode: true,
          })
          .then((willDelete) => {
              if (willDelete) {
                  window.location.href = href;
              }
          });
      });
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      if (sessionStorage.getItem('purchaseUpdated') === 'true') {
          sessionStorage.removeItem('purchaseUpdated');
          swal('Updated!', 'Purchase Updated Successfully', 'success');
      }
  });
</script>
<?php include 'footer.html'; ?>
<script>
(function() {
  var searchTimeout;
  function initAjaxTypeahead(wrapper, apiUrl, displayFn, hiddenName, onSelect) {
    var input = wrapper.querySelector('.supplier-typeahead-input, .customer-typeahead-input, .item-typeahead-input');
    if (!input) input = wrapper.querySelector('input[type="text"]');
    var hidden = wrapper.querySelector('input[name="' + hiddenName + '"]');
    var dropdown = wrapper.querySelector('.supplier-typeahead-dropdown, .customer-typeahead-dropdown, .item-typeahead-dropdown');
    if (!input || !dropdown || input.readOnly) return;
    if (hiddenName === 'supplier_id' && !hidden) hidden = wrapper.querySelector('input[name="supplier_id"]');
    if (hiddenName === 'item_id[]' && !hidden) hidden = wrapper.querySelector('input[name="item_id[]"]');
    if (!hidden) return;

    function search(q, done) {
      fetch(apiUrl + encodeURIComponent((q || '').trim())).then(function(r) { return r.json(); })
        .then(function(d) { done(d.success && d.results ? d.results : []); }).catch(function() { done([]); });
    }
    function render(list) {
      dropdown.innerHTML = '';
      if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching result</div>';
      else list.forEach(function(x) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = displayFn(x);
        div.onclick = function() { onSelect(wrapper, x, hidden, input, dropdown); };
        dropdown.appendChild(div);
      });
      dropdown.classList.add('show');
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { if (hidden) hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(function() { search(q, render); }, 300);
    };
    input.onfocus = function() { if (input.value.trim()) search(input.value.trim(), render); };
    input.onkeydown = function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (!opts.length) return;
      var act = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!act) opts[0].classList.add('active'); else { act.classList.remove('active'); (act.nextElementSibling || opts[0]).classList.add('active'); } }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!act) opts[opts.length-1].classList.add('active'); else { act.classList.remove('active'); (act.previousElementSibling || opts[opts.length-1]).classList.add('active'); } }
      else if (e.key === 'Enter' && act) { e.preventDefault(); act.click(); }
    };
    document.addEventListener('click', function(e) { if (!wrapper.contains(e.target)) dropdown.classList.remove('show'); });
  }

  function supplierOnSelect(w, x, hidden, input, dropdown) {
    hidden.value = x.supplier_id || '';
    input.value = (x.supplier_id || '') + ' - ' + (x.supplier_name || '');
    dropdown.classList.remove('show');
  }
  function itemOnSelect(w, x, hidden, input, dropdown) {
    var row = w.closest('.item-row');
    hidden.value = x.item_id || '';
    input.value = (x.item_id || '') + ' - ' + (x.item_name || '');
    var priceInput = row ? row.querySelector('.original_price') : null;
    var stockSpan = row ? row.querySelector('.stock_balance') : null;
    if (priceInput) priceInput.value = x.original_price != null ? x.original_price : '';
    if (stockSpan) {
      stockSpan.innerText = '';
      fetch('get_item_by_id.php?item_id=' + encodeURIComponent(x.item_id)).then(function(r) { return r.json(); })
        .then(function(d) { if (d.success && stockSpan) stockSpan.innerText = 'Balance Qty is ' + (d.stock_balance || 0) + ' pcs'; }).catch(function() {});
    }
    dropdown.classList.remove('show');
    if (priceInput) priceInput.dispatchEvent(new Event('input'));
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.supplier-typeahead').forEach(function(w) {
      if (!w.querySelector('.supplier-typeahead-input') || w.querySelector('.supplier-typeahead-input').readOnly) return;
      initAjaxTypeahead(w, 'get_suppliers_search.php?q=', function(x) { return (x.supplier_id || '') + ' - ' + (x.supplier_name || ''); }, 'supplier_id', supplierOnSelect);
    });
    document.querySelectorAll('.item-typeahead').forEach(function(w) {
      if (!w.querySelector('.item-typeahead-input') || w.querySelector('.item-typeahead-input').readOnly) return;
      initAjaxTypeahead(w, 'get_items_search.php?q=', function(x) { return (x.item_id || '') + ' - ' + (x.item_name || ''); }, 'item_id[]', itemOnSelect);
    });
    window.initItemTypeahead = function(w) {
      if (!w.querySelector('.item-typeahead-input') || w.querySelector('.item-typeahead-input').readOnly) return;
      initAjaxTypeahead(w, 'get_items_search.php?q=', function(x) { return (x.item_id || '') + ' - ' + (x.item_name || ''); }, 'item_id[]', itemOnSelect);
    };
  });
})();
</script>