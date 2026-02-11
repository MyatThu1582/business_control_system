<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';

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

  $supplier_display = '';
  if (!empty($temp_purchaseresult['supplier_id'])) {
    $supStmt = $pdo->prepare("SELECT supplier_name FROM supplier WHERE supplier_id = ?");
    $supStmt->execute([$temp_purchaseresult['supplier_id']]);
    $supRow = $supStmt->fetch(PDO::FETCH_ASSOC);
    $supplier_display = $temp_purchaseresult['supplier_id'] . ' - ' . ($supRow['supplier_name'] ?? '');
  }
  ?>
<style>
.supplier-typeahead { position: relative; }
.supplier-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.supplier-typeahead-dropdown.show { display: block; }
.supplier-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.supplier-typeahead-dropdown .option:hover, .supplier-typeahead-dropdown .option.active { background: #e9ecef; }
.supplier-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
.item-typeahead { position: relative; }
.item-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>
    
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
              
                <div class="col-4">
                  <label for="">Supplier</label>
                  <div class="supplier-typeahead" id="supplier_typeahead_main">
                    <input type="text" class="form-control supplier-typeahead-input" placeholder="Type supplier code or name..." value="<?php echo htmlspecialchars($supplier_display); ?>" autocomplete="off" <?php echo $isReadOnly; ?>>
                    <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($temp_purchaseresult['supplier_id'] ?? ''); ?>">
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
                        <th>Item</th>
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
                              $item_display = ($item_id ?? '') . ' - ' . ($itemIdResult['item_name'] ?? '');
                      ?>
                      <tr class="item-row" style="font-size: 15px;">
                          <td class="no-padding" style="min-width: 250px;">
                            <div class="item-typeahead">
                              <input type="text" class="custom-input item-typeahead-input" placeholder="Type item code or name..." value="<?php echo htmlspecialchars($item_display); ?>" autocomplete="off" <?php echo $isReadOnly; ?>>
                              <input type="hidden" name="item_id[]" value="<?php echo htmlspecialchars($item_id); ?>">
                              <div class="item-typeahead-dropdown"></div>
                            </div>
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
<script>
(function() {
  var searchTimeout;
  function displayText(s) { return (s.supplier_id || '') + ' - ' + (s.supplier_name || ''); }
  function searchSuppliers(q, callback) {
    if (!q || q.trim() === '') { callback([]); return; }
    fetch('get_suppliers_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { callback(d.success && d.results ? d.results : []); }).catch(function() { callback([]); });
  }
  function initSupplierTypeahead(w) {
    var input = w.querySelector('.supplier-typeahead-input');
    var hidden = w.querySelector('input[name="supplier_id"]');
    var dropdown = w.querySelector('.supplier-typeahead-dropdown');
    if (!input || !hidden || !dropdown || input.readOnly) return;
    function doSearch() {
      searchSuppliers(input.value.trim(), function(list) {
        dropdown.innerHTML = '';
        if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching supplier</div>';
        else list.forEach(function(s) {
          var div = document.createElement('div');
          div.className = 'option';
          div.textContent = displayText(s);
          div.onclick = function() { hidden.value = s.supplier_id || ''; input.value = displayText(s); dropdown.classList.remove('show'); };
          dropdown.appendChild(div);
        });
        dropdown.classList.add('show');
      });
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(doSearch, 300);
    };
    input.onfocus = function() { if (input.value.trim()) doSearch(); };
    document.addEventListener('click', function(ev) { if (!w.contains(ev.target)) dropdown.classList.remove('show'); });
  }
  function initItemTypeahead(w) {
    var input = w.querySelector('.item-typeahead-input');
    var hidden = w.querySelector('input[name="item_id[]"]');
    var dropdown = w.querySelector('.item-typeahead-dropdown');
    if (!input || !hidden || !dropdown || input.readOnly) return;
    function search(q, done) {
      fetch('get_items_search.php?q=' + encodeURIComponent((q || '').trim())).then(function(r) { return r.json(); })
        .then(function(d) { done(d.success && d.results ? d.results : []); }).catch(function() { done([]); });
    }
    function render(list) {
      dropdown.innerHTML = '';
      if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching item</div>';
      else list.forEach(function(x) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = (x.item_id || '') + ' - ' + (x.item_name || '');
        div.onclick = function() {
          var row = w.closest('.item-row');
          hidden.value = x.item_id || '';
          input.value = (x.item_id || '') + ' - ' + (x.item_name || '');
          var priceInput = row ? row.querySelector('.original_price') : null;
          if (priceInput) { priceInput.value = x.original_price != null ? x.original_price : ''; priceInput.dispatchEvent(new Event('input')); }
          dropdown.classList.remove('show');
        };
        dropdown.appendChild(div);
      });
      dropdown.classList.add('show');
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(function() { search(q, render); }, 300);
    };
    input.onfocus = function() { if (input.value.trim()) search(input.value.trim(), render); };
    document.addEventListener('click', function(ev) { if (!w.contains(ev.target)) dropdown.classList.remove('show'); });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.supplier-typeahead').forEach(initSupplierTypeahead);
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
  });
})();
</script>
<?php include 'footer.html'; ?>