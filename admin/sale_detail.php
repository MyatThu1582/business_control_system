<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../config/config.php';
require '../config/common.php';
include 'header.php';

/* ================= EDIT SALE ================= */

if (isset($_POST['edit_btn'])) {

    if (empty($_POST['date'])) $dateError = 'Date is required';
    if (empty($_POST['gin_no'])) $gin_noError = 'Invoice No is required';

    if (empty($_POST['so_no'])) {
        if (empty($_POST['customer_id'])) {
            $customer_idError = 'Customer is required';
        }
    }

    $item_ids  = $_POST['item_id'] ?? [];
    $qtys      = $_POST['qty'] ?? [];
    $prices    = $_POST['selling_price'] ?? [];
    $discounts = $_POST['discount'] ?? [];
    $focs      = $_POST['foc'] ?? [];

    $hasItemError = false;
    foreach ($item_ids as $i => $item_id) {
        if (empty($item_id) || empty($qtys[$i]) || empty($prices[$i])) {
            $hasItemError = true;
            break;
        }
    }

    if (empty($dateError) && empty($gin_noError) && empty($customer_idError) && !$hasItemError) {

        $date    = $_POST['date'];
        $gin_no  = $_POST['gin_no'];
        $type    = $_POST['type'];
        $so_no   = $_POST['so_no'] ?? '';
        $sale_id = $_POST['sale_id'];

        if (empty($so_no)) {
            $customer_id = $_POST['customer_id'];
        }

        $pdo->prepare("
            UPDATE temp_sale SET
                date = :date,
                gin_no = :gin_no,
                customer_id = :customer_id,
                so_no = :so_no,
                type = :type,
                status = 'draft'
            WHERE id = :id
        ")->execute([
            ':date'=>$date,
            ':gin_no'=>$gin_no,
            ':customer_id'=>$customer_id,
            ':so_no'=>$so_no,
            ':type'=>$type,
            ':id'=>$sale_id
        ]);

        $pdo->prepare("DELETE FROM temp_sale_items WHERE temp_sale_id=:id")
            ->execute([':id'=>$sale_id]);

        $stmt = $pdo->prepare("
            INSERT INTO temp_sale_items
            (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, gin_no, temp_sale_id)
            VALUES
            (:item_id,:price,:qty,:type,:percentage,:percentage_amount,:stock_foc,:amount,:gin_no,:temp_sale_id)
        ");

        foreach ($item_ids as $i => $item_id) {
            $qty = $qtys[$i];
            $price = $prices[$i];
            $discount = $discounts[$i] ?? 0;
            $foc = $focs[$i] ?? 0;

            $amount = $price * $qty;
            $percentage_amount = ($discount > 0) ? ($amount / 100) * $discount : 0;
            $amount -= $percentage_amount;

            $stmt->execute([
                ':item_id'=>$item_id,
                ':price'=>$price,
                ':qty'=>$qty,
                ':type'=>$type,
                ':percentage'=>$discount,
                ':percentage_amount'=>$percentage_amount,
                ':stock_foc'=>$foc,
                ':amount'=>$amount,
                ':gin_no'=>$gin_no,
                ':temp_sale_id'=>$sale_id
            ]);
        }

        echo "<script>sessionStorage.setItem('saleUpdated','true');</script>";
    }
}


$gin_no = $_GET['gin_no'];

$temp_saleresult = $pdo->query("SELECT * FROM temp_sale WHERE gin_no='$gin_no' ORDER BY id DESC")->fetch(PDO::FETCH_ASSOC);

$temp_sale_itemresult = $pdo->query("SELECT * FROM temp_sale_items WHERE gin_no='$gin_no' ORDER BY id DESC")->fetchAll();

$isReadOnly = ($_GET['status'] === 'approved') ? 'readonly' : '';

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
.customer-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
.item-typeahead { position: relative; }
.item-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>

<div class="col-md-12 px-3 pt-1">
  <div class="collapse show">
    <form action="" method="post">
      <input type="hidden" name="sale_id" value="<?php echo $_GET['temp_saleid']; ?>">
      <div class="card">
        <div class="card-header py-2 pb-0 pt-3">
          <h5 class="d-flex align-items-center justify-content-between">
            Edit Sale - <?php echo $_GET['gin_no']; ?>
            <div class="d-flex">
              <div class="dropdown">
                <button 
                  class="btn btn-sm btn-primary dropdown-toggle fw-semibold shadow-sm" 
                  type="button" 
                  id="saleOptionsDropdown" 
                  data-bs-toggle="dropdown" 
                  aria-expanded="false"
                  style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                  Sale Options
                </button>

                <ul class="dropdown-menu border-0 shadow p-0" aria-labelledby="saleOptionsDropdown" 
                    style="border-radius: 3px; overflow: hidden; min-width: 140px;">
                  
                  <?php 
                  if($_GET['status'] == 'draft'){
                    ?>
                    <li>
                      <?php
                        $stmt = $pdo->prepare("SELECT * FROM temp_sale WHERE gin_no='$gin_no' ORDER BY id DESC");
                        $stmt->execute();
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                      ?>
                      <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" href="approve_sale.php?gin_no=<?php echo $data['gin_no']; ?>&temp_saleid=<?php echo $data['id']; ?>&status=<?php echo $data['status']; ?>&action=approve"
                        style="transition: background 0.2s;">
                        <i class="bi bi-arrow-counterclockwise text-primary"></i>
                        <span style="font-size: 13px;">
                          Approve Sale
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
                              window.location.href = 'add_sale_return.php?gin_no=<?php echo $_GET['gin_no']; ?>&temp_saleid=<?php echo $_GET['temp_saleid']; ?>';
                          }
                          return false;
                      "
                      style="transition: background 0.2s;">
                      
                      <i class="bi bi-arrow-counterclockwise text-primary"></i>
                      <span style="font-size: 13px;">Sale Return</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                      href="print_sale.php?gin_no=<?php echo $_GET['gin_no']; ?>">
                      <i class="bi bi-printer text-primary"></i>
                      <span style="font-size: 13px;">Print</span>
                    </a>
                  </li>

                  <hr style="margin: 0px;">
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 delete-temp-sale" href="delete.php?table_name=temp_sale&id=<?php echo $_GET['temp_saleid']; ?>&gin_no=<?php echo $_GET['gin_no']; ?>" 
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
          <?php $isReadOnly = (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'readonly' : ''; ?>

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
                  } else {
                    // Empty template row for add-row when no items
                ?>
                <tr class="item-row" style="font-size: 15px;">
                  <td class="no-padding" style="min-width: 250px;">
                    <div class="item-typeahead">
                      <input type="text" class="custom-input item-typeahead-input" placeholder="Type item code or name..." autocomplete="off">
                      <input type="hidden" name="item_id[]">
                      <div class="item-typeahead-dropdown"></div>
                    </div>
                  </td>
                  <td class="no-padding"><input type="number" class="custom-input text-right selling_price" name="selling_price[]"></td>
                  <td class="no-padding"><input type="number" class="custom-input text-right discount" name="discount[]"></td>
                  <td class="no-padding"><input type="number" class="custom-input text-right qty" name="qty[]"></td>
                  <td class="no-padding"><input type="number" class="custom-input text-right foc" name="foc[]"></td>
                  <td class="no-padding"><input type="number" class="custom-input text-right" name="amount[]"></td>
                  <td class="no-padding text-center" style="background:none !important; cursor:pointer !important; width: 30px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg remove-row-btn" viewBox="0 0 16 16">
                      <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                  </td>
                </tr>
                <?php } ?>
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
          <div class="d-flex justify-content-between mt-1">
            <div>
              <a href="sale.php" class="btn btn-secondary btn-sm text-light">Cancel</a>    
              <?php 
                if($_GET['status'] != 'approved'){
                  ?>
                    <button type="submit" name="edit_btn" class="btn btn-purple btn-sm text-light ml-1">Save Sale</button>
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
  // Auto calculate amount on qty or price change
  document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('selling_price')) {
      const row = e.target.closest('.item-row');
      const priceInput = row.querySelector('.selling_price');
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
        }

        container.appendChild(clone);
        if (window.initItemTypeahead) {
            var tw = clone.querySelector('.item-typeahead');
            if (tw) window.initItemTypeahead(tw);
        }
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
  // SweetAlert for deleting temporary sale
  document.querySelectorAll('.delete-temp-sale').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this Sale!",
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
  // Show success message after sale update
  document.addEventListener('DOMContentLoaded', function() {
      if (sessionStorage.getItem('saleUpdated') === 'true') {
          sessionStorage.removeItem('saleUpdated'); // clear flag
          swal('Updated!', 'Sale Updated Successfully', 'success');
      }
  });
</script>
<script>
(function() {
  var searchTimeout;
  function displayText(c) { return (c.customer_id || '') + ' - ' + (c.customer_name || ''); }
  function searchCustomers(q, callback) {
    if (!q || q.trim() === '') { callback([]); return; }
    fetch('get_customers_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { callback(d.success && d.results ? d.results : []); }).catch(function() { callback([]); });
  }
  function renderDropdown(dd, list, onSelect) {
    dd.innerHTML = '';
    if (!list.length) dd.innerHTML = '<div class="no-result">No matching customer</div>';
    else list.forEach(function(c) {
      var div = document.createElement('div');
      div.className = 'option';
      div.textContent = displayText(c);
      div.onclick = function() { onSelect(c.customer_id, displayText(c)); };
      dd.appendChild(div);
    });
    dd.classList.add('show');
  }
  function initCustomerTypeahead(w) {
    var input = w.querySelector('.customer-typeahead-input');
    var hidden = w.querySelector('input[name="customer_id"]');
    var dropdown = w.querySelector('.customer-typeahead-dropdown');
    if (!input || !hidden || !dropdown || input.readOnly) return;
    function doSearch() {
      searchCustomers(input.value.trim(), function(list) {
        renderDropdown(dropdown, list, function(id, text) {
          hidden.value = id || ''; input.value = text; dropdown.classList.remove('show');
        });
      });
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(doSearch, 300);
    };
    input.onfocus = function() { if (input.value.trim()) doSearch(); };
    input.onkeydown = function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (!opts.length) return;
      var act = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!act) opts[0].classList.add('active'); else { act.classList.remove('active'); (act.nextElementSibling || opts[0]).classList.add('active'); } }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!act) opts[opts.length-1].classList.add('active'); else { act.classList.remove('active'); (act.previousElementSibling || opts[opts.length-1]).classList.add('active'); } }
      else if (e.key === 'Enter' && act) { e.preventDefault(); act.click(); }
    };
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
          var stockSpan = row ? row.querySelector('.stock_balance') : null;
          if (priceInput) priceInput.value = x.selling_price != null ? x.selling_price : '';
          if (stockSpan) {
            stockSpan.innerText = '';
            fetch('get_item_by_id.php?item_id=' + encodeURIComponent(x.item_id)).then(function(r) { return r.json(); })
              .then(function(d) {
                if (d.success && stockSpan) {
                  stockSpan.innerText = 'Balance Qty is ' + (d.stock_balance || 0) + ' pcs';
                }
              }).catch(function() {});
          }
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
    input.onkeydown = function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (!opts.length) return;
      var act = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!act) opts[0].classList.add('active'); else { act.classList.remove('active'); (act.nextElementSibling || opts[0]).classList.add('active'); } }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!act) opts[opts.length-1].classList.add('active'); else { act.classList.remove('active'); (act.previousElementSibling || opts[opts.length-1]).classList.add('active'); } }
      else if (e.key === 'Enter' && act) { e.preventDefault(); act.click(); }
    };
    document.addEventListener('click', function(ev) { if (!w.contains(ev.target)) dropdown.classList.remove('show'); });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.customer-typeahead').forEach(initCustomerTypeahead);
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
    window.initItemTypeahead = initItemTypeahead;
  });
})();
</script>
<?php include 'footer.html'; ?>
