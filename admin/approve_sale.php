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
 // Save Sale
    if (isset($_POST['save_btn'])) {
        $gin_no = $_POST['gin_no'];

        // Fetch temp sale
        $temp_salestmt = $pdo->prepare("SELECT * FROM temp_sale WHERE gin_no=:gin_no ORDER BY id DESC LIMIT 1");
        $temp_salestmt->execute([':gin_no' => $gin_no]);
        $temp_sale = $temp_salestmt->fetch(PDO::FETCH_ASSOC);

        $id = $temp_sale['id'];
        $date = $temp_sale['date'];
        $gin_no = $temp_sale['gin_no'];
        $so_no = $temp_sale['so_no'];
        $customer_id = $temp_sale['customer_id'];
        $type = $temp_sale['type'];

        // Temp sale items
        $temp_sale_itemstmt = $pdo->prepare("SELECT * FROM temp_sale_items WHERE gin_no=:gin_no");
        $temp_sale_itemstmt->execute([':gin_no' => $gin_no]);
        $temp_sale_item = $temp_sale_itemstmt->fetchAll();

        foreach ($temp_sale_item as $value) {
            $item_id = $value['item_id'];
            $amount = $value['amount'];
            $qty = $value['qty'];
            $foc = $value['stock_foc'];

            // Add Credit Sale
            if ($type == "credit") {
                $creditstmt = $pdo->prepare("INSERT INTO credit_sale (date,gin_no,customer_id,item_id,amount,qty) VALUES (:date,:gin_no,:customer_id,:item_id,:amount,:qty)");
                $creditstmt->execute([
                    ':date' => $date,
                    ':gin_no' => $gin_no,
                    ':customer_id' => $customer_id,
                    ':item_id' => $item_id,
                    ':amount' => $amount,
                    ':qty' => $qty
                ]);
            } else {
                // Add Cash Sale
                $cashstmt = $pdo->prepare("INSERT INTO cash_sale (date,gin_no,customer_id,item_id,amount,qty) VALUES (:date,:gin_no,:customer_id,:item_id,:amount,:qty)");
                $cashstmt->execute([
                    ':date' => $date,
                    ':gin_no' => $gin_no,
                    ':customer_id' => $customer_id,
                    ':item_id' => $item_id,
                    ':amount' => $amount,
                    ':qty' => $qty
                ]);
            }

            // Update Stock (subtract sold qty + foc)
            $stock_balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
            $stock_balancestmt->execute();
            $stock_balancedata = $stock_balancestmt->fetch(PDO::FETCH_ASSOC);

            $oldbalance = !empty($stock_balancedata) ? $stock_balancedata['balance'] : 0;
            $stockbalance = $oldbalance - ($qty + $foc);

            $out_qty = $qty + $foc;

            $stockstmt = $pdo->prepare("
              INSERT INTO stock (date, item_id, to_from, in_qty, out_qty, foc_qty, balance, grn_no, gin_no)
              VALUES (:date, :item_id, 'sale', 0, :out_qty, :foc_qty, :balance, NULL, :gin_no)
            ");
            $stockstmt->execute([
                ':date' => $date,
                ':gin_no' => $gin_no,
                ':item_id' => $item_id,
                ':out_qty' => $out_qty,
                ':foc_qty' => $foc,
                ':balance' => $stockbalance
            ]);
        }

        // Add Receivable if credit
        if ($type == "credit") {
            $sale_idstmt = $pdo->prepare("SELECT * FROM credit_sale WHERE gin_no='$gin_no' ORDER BY id DESC");
            $sale_idstmt->execute();
            $sale_data = $sale_idstmt->fetch(PDO::FETCH_ASSOC);

            $sale_id = $sale_data['id'];
            $customer_id = $sale_data['customer_id'];

            $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_sale WHERE gin_no='$gin_no'");
            $total_amountstmt->execute();
            $total_amountresult = $total_amountstmt->fetch(PDO::FETCH_ASSOC);
            $amount = $total_amountresult['total_amount'];

            $receivablestmt = $pdo->prepare("
              INSERT INTO receivable
                (date, gin_no, customer_id, amount, paid, balance, sale_id, asc_id, group_id, status, payment_no, account_name, remark)
              VALUES
                (:date, :gin_no, :customer_id, :amount, 0, :balance, :sale_id, 0, :group_id, 'Pending', '', '', '')
            ");
            $receivablestmt->execute([
                ':date' => $date,
                ':gin_no' => $gin_no,
                ':customer_id' => $customer_id,
                ':amount' => $amount,
                ':sale_id' => $sale_id,
                ':group_id' => $gin_no,
                ':balance' => $amount
            ]);
        }

        // Delete or mark temp sale as processed
        $updatestmt = $pdo->prepare("UPDATE temp_sale SET status='approved' WHERE id='$id'");
        $updatestmt->execute();
        
        echo "<script>
                sessionStorage.setItem('saleApproved', 'true');
                window.location.href = 'sale.php';
            </script>";
    }

  $gin_no = $_GET['gin_no'];

    $temp_saleresult = $pdo->query("SELECT * FROM temp_sale WHERE gin_no='$gin_no' ORDER BY id DESC")->fetch(PDO::FETCH_ASSOC);

    $temp_sale_itemresult = $pdo->query("SELECT * FROM temp_sale_items WHERE gin_no='$gin_no' ORDER BY id DESC")->fetchAll();

    $customer_display = '';
    if (!empty($temp_saleresult['customer_id'])) {
      $custStmt = $pdo->prepare("SELECT customer_name FROM customer WHERE customer_id = ?");
      $custStmt->execute([$temp_saleresult['customer_id']]);
      $custRow = $custStmt->fetch(PDO::FETCH_ASSOC);
      $customer_display = $temp_saleresult['customer_id'] . ' - ' . ($custRow['customer_name'] ?? '');
    }
  ?>
<style>
.customer-typeahead { position: relative; }
.customer-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.customer-typeahead-dropdown.show { display: block; }
.customer-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.customer-typeahead-dropdown .option:hover, .customer-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead { position: relative; }
.item-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
</style>
    <div class="col-md-12 px-3 pt-1">
  <div class="collapse show">
    <form action="" method="post">
      <input type="hidden" name="sale_id" value="<?php echo $_GET['temp_saleid']; ?>">
      <div class="card">
        <div class="card-header py-3 bg-lightgreen">
          <h5 class="d-flex align-items-center justify-content-between">
            Approve Sale - <?php echo $_GET['gin_no']; ?>
          </h5>
        </div>

        <div class="card-body" style="background-color: rgba(0,0,0,0.01);">
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
                <input type="date" class="form-control" value="<?php echo $temp_saleresult['date']; ?>" name="date" <?php echo $isReadOnly; ?>>
                <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
              </div>
              <div class="col">
                <label for="">GIN_No</label>
                <input type="text" class="form-control" name="gin_no" value="<?php echo $temp_saleresult['gin_no']; ?>" <?php echo $isReadOnly; ?>>
                <p style="color:red;"><?php echo empty($gin_noError) ? '' : '*'.$gin_noError;?></p>
              </div>
              <div class="col">
                <label for="">SO No</label>
                <select name="so_no" id="so_no" class="form-control" value="<?php echo $temp_saleresult['so_no']; ?>" <?php echo $isReadOnly; ?>>
                  <option value="">Select SO_No</option>
                  <?php
                  $so_nostmt = $pdo->prepare("SELECT * FROM sale_order WHERE status LIKE '%ending%' ORDER BY id DESC");
                  $so_nostmt->execute();
                  $so_nodatas = $so_nostmt->fetchAll();
                  foreach ($so_nodatas as $so_nodata) {
                    ?>
                    <option value="<?php echo $so_nodata['order_no']; ?>"><?php echo $so_nodata['order_no']; ?></option>
                    <?php
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="col-4">
              <label for="">Customer</label>
              <div class="customer-typeahead" id="customer_typeahead_main">
                <input type="text" class="form-control customer-typeahead-input" placeholder="Type customer code or name..." value="<?php echo htmlspecialchars($customer_display); ?>" autocomplete="off" <?php echo $isReadOnly; ?>>
                <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($temp_saleresult['customer_id'] ?? ''); ?>">
                <div class="customer-typeahead-dropdown"></div>
              </div>
              <p style="color:red;"><?php echo empty($customer_idError) ? '' : '*'.$customer_idError;?></p>
            </div>
            <div class="col-2">
              <label for="">Payment</label>
              <select name="type" class="form-control" <?php echo $isReadOnly; ?>>
                <option value="cash" <?php if($temp_saleresult['type'] == 'cash'){ echo "selected"; } ?>>Cash</option>
                <option value="credit" <?php if($temp_saleresult['type'] == 'credit'){ echo "selected"; } ?>>Credit</option>
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
                  if ($temp_sale_itemresult) {
                    foreach ($temp_sale_itemresult as $value) {
                        $gin_no = $value['gin_no'];
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
                    <input type="number" value="<?php echo $value['price']; ?>" class="custom-input text-right selling_price" name="selling_price[]" <?php echo $isReadOnly; ?>>
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['percentage']; ?>" class="custom-input text-right discount" name="discount[]" <?php echo $isReadOnly; ?>>
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['qty']; ?>" class="custom-input text-right qty" name="qty[]" <?php echo $isReadOnly; ?>>
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['stock_foc']; ?>" class="custom-input text-right foc" name="foc[]" <?php echo $isReadOnly; ?>>
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['amount']; ?>" class="custom-input text-right" name="amount[]" <?php echo $isReadOnly; ?>>
                  </td>

                  <td class="no-padding text-center" style="background:none !important; cursor:pointer !important; width: 30px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                        class="bi bi-x-lg remove-row-btn" viewBox="0 0 16 16">
                      <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                  </td>
                </tr>
                <?php
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
                    $gin_no = $_GET['gin_no'];
                    $temp_saleid = $_GET['temp_saleid'];
                    $status = $_GET['status'];
                    ?>
                    <a href="sale_detail.php?gin_no=<?php echo $gin_no; ?>&temp_saleid=<?php echo $temp_saleid; ?>&status=<?php echo $status; ?>" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                    <?php 
                    if($_GET['status'] != 'approved'){
                        ?>
                        <button type="submit" name="save_btn" class="btn btn-purple btn-sm text-light ml-1">Approve Sale</button>
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
  function displayText(c) { return (c.customer_id || '') + ' - ' + (c.customer_name || ''); }
  function searchCustomers(q, callback) {
    if (!q || q.trim() === '') { callback([]); return; }
    fetch('get_customers_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { callback(d.success && d.results ? d.results : []); }).catch(function() { callback([]); });
  }
  function initCustomerTypeahead(w) {
    var input = w.querySelector('.customer-typeahead-input');
    var hidden = w.querySelector('input[name="customer_id"]');
    var dropdown = w.querySelector('.customer-typeahead-dropdown');
    if (!input || !hidden || !dropdown || input.readOnly) return;
    function doSearch() {
      searchCustomers(input.value.trim(), function(list) {
        dropdown.innerHTML = '';
        if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching customer</div>';
        else list.forEach(function(c) {
          var div = document.createElement('div');
          div.className = 'option';
          div.textContent = displayText(c);
          div.onclick = function() { hidden.value = c.customer_id || ''; input.value = displayText(c); dropdown.classList.remove('show'); };
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
          var priceInput = row ? row.querySelector('.selling_price') : null;
          if (priceInput) priceInput.value = x.selling_price != null ? x.selling_price : '';
          dropdown.classList.remove('show');
          if (priceInput) priceInput.dispatchEvent(new Event('input'));
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
    document.querySelectorAll('.customer-typeahead').forEach(initCustomerTypeahead);
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
  });
})();
</script>
<?php include 'footer.html'; ?>