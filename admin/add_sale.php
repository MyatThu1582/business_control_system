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
    // $item_id = "ITM-005";

    // // get latest stock balance
    // $stockstmt = $pdo->prepare("
    //     SELECT balance 
    //     FROM stock 
    //     WHERE item_id = :id 
    //     ORDER BY id DESC 
    //     LIMIT 1
    // ");
    // $stockstmt->execute([':id' => $item_id]);
    // $stockrow = $stockstmt->fetch(PDO::FETCH_ASSOC);

    // $stock_balance = $stockrow ? (int)$stockrow['balance'] : 0;

    // // get pending sale order qty
    // $salestmt = $pdo->prepare("
    //     SELECT SUM(soi.qty) AS pending_qty
    //     FROM sale_order_items soi
    //     JOIN sale_order so ON so.order_no = soi.order_no
    //     WHERE soi.item_id = :id
    //     AND so.status NOT IN ('done')
    // ");
    // $salestmt->execute([':id' => $item_id]);
    // $salerow = $salestmt->fetch(PDO::FETCH_ASSOC);

    // $pending_qty = $salerow && $salerow['pending_qty']
    //     ? (int)$salerow['pending_qty']
    //     : 0;

    // // real usable stock
    // $real_balance = $stock_balance - $pending_qty;
    // if ($real_balance < 0) {
    //     $real_balance = 0;
    // }

    // print_r($real_balance);


// Add Sale
if (isset($_POST['add_btn'])) {

    // --- Validate static fields ---
    if (empty($_POST['date'])) {
        $dateError = 'Date is required';
    }
    if (empty($_POST['gin_no'])) {
        $gin_noError = 'GIN No is required';
    }
    if (empty($_POST['so_no'])) {
        if (empty($_POST['customer_id'])) {
            $customer_idError = 'Customer is required';
        }
    }

    // --- Get dynamic arrays ---
    $item_ids  = $_POST['item_id'] ?? [];
    $qtys      = $_POST['qty'] ?? [];
    $prices    = $_POST['original_price'] ?? [];
    $discounts = $_POST['discount'] ?? [];
    $focs = $_POST['foc'] ?? [];


    // --- Filter valid rows ---
    $filtered_items = [];
    foreach ($item_ids as $index => $item_id) {
        $qty   = trim($qtys[$index] ?? '');
        $price = trim($prices[$index] ?? '');

        if (!empty($item_id) && !empty($qty) && !empty($price)) {
            $d = $discounts[$index] ?? 0;
            $f = $focs[$index] ?? 0;
            $filtered_items[] = [
                'item_id'  => $item_id,
                'qty'      => $qty,
                'price'    => $price,
                'discount' => ($d !== '' && $d !== null) ? (float) $d : 0,
                'foc' => ($f !== '' && $f !== null) ? (int) $f : 0,
            ];
        }
    }

    // --- Validate item existence ---
    $hasItemError = false;
    if (count($filtered_items) === 0 && empty($_POST['so_no'])) {
        $item_idError = "Please fill at least one item.";
        $hasItemError = true;
    }

    // --- Process if valid ---
    if (empty($dateError) && empty($gin_noError) && empty($customer_idError) && !$hasItemError) {

        $date  = $_POST['date'];
        $gin_no = $_POST['gin_no'];
        $type  = $_POST['type'];
        $so_no = $_POST['so_no'] ?? '';

        if (empty($so_no)) {
            $customer_id = $_POST['customer_id'];
        }

        // ===== WITH SALE ORDER =====
        if (!empty($so_no)) {

            $sale_orderstmt = $pdo->prepare("SELECT * FROM sale_order WHERE order_no=:so_no");
            $sale_orderstmt->execute([':so_no' => $so_no]);
            $sale_order = $sale_orderstmt->fetch(PDO::FETCH_ASSOC);
            $customer_id = $sale_order['customer_id'];

            $addstmt = $pdo->prepare("
                INSERT INTO temp_sale
                (date, gin_no, customer_id, so_no, type, status)
                VALUES
                (:date, :gin_no, :customer_id, :so_no, :type, 'draft')
            ");
            $addstmt->execute([
                ':date' => $date,
                ':gin_no' => $gin_no,
                ':customer_id' => $customer_id,
                ':so_no' => $so_no,
                ':type' => $type,
            ]);

            $temp_sale_id = $pdo->lastInsertId();

            $sale_itemsstmt = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_no=:so_no");
            $sale_itemsstmt->execute([':so_no' => $so_no]);
            $sale_items = $sale_itemsstmt->fetchAll();

            foreach ($sale_items as $row) {

                $additem = $pdo->prepare("
                    INSERT INTO temp_sale_items
                    (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, gin_no, temp_sale_id)
                    VALUES
                    (:item_id, :price, :qty, :type, 0, 0, 0, :amount, :gin_no, :temp_sale_id)
                ");

                $additem->execute([
                    ':item_id' => $row['item_id'],
                    ':price' => $row['price'],
                    ':qty' => $row['qty'],
                    ':type' => $type,
                    ':amount' => $row['amount'],
                    ':gin_no' => $gin_no,
                    ':temp_sale_id' => $temp_sale_id
                ]);
            }

            if ($addstmt) {
                $pdo->prepare("UPDATE sale_order SET status='Delivered' WHERE order_no=:so_no")
                    ->execute([':so_no' => $so_no]);
            }

        } 
        // ===== WITHOUT SALE ORDER =====
        else {

            $addstmt = $pdo->prepare("
                INSERT INTO temp_sale
                (date, gin_no, customer_id, so_no, type, status)
                VALUES
                (:date, :gin_no, :customer_id, '', :type, 'draft')
            ");
            $addstmt->execute([
                ':date' => $date,
                ':gin_no' => $gin_no,
                ':customer_id' => $customer_id,
                ':type' => $type,
            ]);

            $temp_sale_id = $pdo->lastInsertId();

            $add_itemstmt = $pdo->prepare("
                INSERT INTO temp_sale_items
                (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, gin_no, temp_sale_id)
                VALUES
                (:item_id, :price, :qty, :type, :percentage, :percentage_amount, :stock_foc, :amount, :gin_no, :temp_sale_id)
            ");

            foreach ($filtered_items as $item) {

                $amount = $item['price'] * $item['qty'];
                $percentage_amount = 0;
                $foc = $item['foc'];

                if (!empty($item['discount'])) {
                    $percentage_amount = ($amount / 100) * $item['discount'];
                    $amount -= $percentage_amount;
                }

                $add_itemstmt->execute([
                    ':item_id' => $item['item_id'],
                    ':price' => $item['price'],
                    ':qty' => $item['qty'],
                    ':type' => $type,
                    ':percentage' => (float) $item['discount'],
                    ':percentage_amount' => $percentage_amount,
                    ':stock_foc' => (int) $foc,
                    ':amount' => $amount,
                    ':gin_no' => $gin_no,
                    ':temp_sale_id' => $temp_sale_id
                ]);
            }
        }

        echo "<script>swal('Success!', 'Sale Added Successfully', 'success');</script>";
    }
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
.item-typeahead-dropdown { position: absolute; left: 0; top: 100%; z-index: 1000; min-width: 420px; max-height: 280px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 12px; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead-dropdown .option img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink: 0; }
.item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>

<div class="col-md-12 mt-2 px-3 pt-1">
  <div class="collapse show" id="">  
    <form action="" method="post">
      <div class="card">
        <div class="card-header py-2 pb-0 pt-3">
          <h5 class="d-flex align-items-center justify-content-between">
            Add New Sale
            <div class="d-flex">
              <a href="sale.php" class="btn btn-sm btn-success" style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                View Sale Bills
              </a>
            </div>
          </h5>
        </div>

        <div class="card-body" style="background-color: rgba(0,0,0,0.01);">
          <div class="row">
            <div class="col-6 d-flex">
              <div class="col">
                <label>Date</label>
                <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" name="date" placeholder="Date">
              </div>
              <div class="col">
                <label>GIN No</label>
                <input type="text" class="form-control" name="gin_no" placeholder="GIN No">
              </div>
              <div class="col">
                <label>SO No</label>
                <select name="so_no" id="so_no" class="form-control">
                  <option value="">Select SO No</option>
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
                <label>Customer</label>
                <div class="customer-typeahead" id="customer_typeahead_main">
                  <input type="text" class="form-control customer-typeahead-input" id="customer_display_main" placeholder="Type customer code or name..." autocomplete="off">
                  <input type="hidden" name="customer_id" id="customer_id_main">
                  <div class="customer-typeahead-dropdown" id="customer_dropdown_main"></div>
                </div>
                <p style="color:red;"><?php echo empty($customer_idError) ? '' : '*'.$customer_idError;?></p>
            </div>

            <div class="col-2">
              <label>Payment</label>
              <select name="type" class="form-control">
                <option value="cash">Cash</option>
                <option value="credit">Credit</option>
              </select>
            </div>
          </div>

          <div class="pl-2 pt-3">
            <table class="table table-hover table-bordered">
              <thead class="table-sm" style="background-color: #f4f4f4;">
                <tr>
                  <th>Item</th>
                  <th>Price</th>
                  <th style="width: 120px;">In Stock</th>
                  <th class="text-right">Discount %</th>
                  <th class="text-right">Qty</th>
                  <th class="text-right">Foc</th>
                  <th colspan="2">Amount</th>
                </tr>
              </thead>
              <tbody id="item-rows">
                <tr class="item-row" style="font-size: 15px;">
                  <td class="no-padding" style="min-width: 250px;">
                    <div class="item-typeahead">
                      <input type="text" class="custom-input item-typeahead-input" placeholder="Type item code or name..." autocomplete="off">
                      <input type="hidden" name="item_id[]">
                      <div class="item-typeahead-dropdown"></div>
                    </div>
                  </td>
                  <td class="no-padding">
                    <input type="number" class="custom-input text-right original_price" name="original_price[]">
                  </td>
                  <td class="text-right"><span class="stock_balance"></span></td>
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
              </tbody>
            </table>
          </div>

          <div>
            <button type="button" id="add-row-btn" class="btn btn-default text-info btn-sm ml-2">+ Add a new line</button>
          </div>
        </div>

        <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
          <div class="d-flex justify-content-end mt-2">
            <button type="submit" name="add_btn" class="btn btn-purple text-light btn-sm mr-2">Add Sale</button>
            <a href="index.php" class="btn btn-secondary btn-sm text-light">Cancel</a>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('item-rows');
  const addBtn = document.getElementById('add-row-btn');
  const firstRow = container.querySelector('.item-row');

  // Show 5 rows initially
  for (let i = 1; i < 6; i++) {
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
    clone.querySelectorAll('.stock_balance').forEach(function(s) { s.innerText = ''; });
    var dd = clone.querySelector('.item-typeahead-dropdown');
    if (dd) { dd.innerHTML = ''; dd.classList.remove('show'); }
    container.appendChild(clone);
    if (window.initItemTypeahead) {
      var tw = clone.querySelector('.item-typeahead');
      if (tw) window.initItemTypeahead(tw);
    }
  }

  // Add row button
  addBtn.addEventListener('click', function () {
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
    clone.querySelectorAll('.stock_balance').forEach(function(s) { s.innerText = ''; });
    var dd = clone.querySelector('.item-typeahead-dropdown');
    if (dd) { dd.innerHTML = ''; dd.classList.remove('show'); }
    container.appendChild(clone);
    if (window.initItemTypeahead) {
      var tw = clone.querySelector('.item-typeahead');
      if (tw) window.initItemTypeahead(tw);
    }
  });

  // Remove row
  container.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-row-btn')) {
      const row = e.target.closest('.item-row');
      if (row) row.remove();
    }
  });
});

// Disable inputs if SO selected
document.addEventListener('DOMContentLoaded', function () {
  const soSelect = document.getElementById('so_no');
  const itemRowsContainer = document.getElementById('item-rows');
  const addRowBtn = document.getElementById('add-row-btn');
  const customerTypeahead = document.getElementById('customer_typeahead_main');
  const customerInput = customerTypeahead ? customerTypeahead.querySelector('.customer-typeahead-input') : null;
  const customerHidden = customerTypeahead ? customerTypeahead.querySelector('input[name="customer_id"]') : null;

  function toggleInputs() {
    const disable = soSelect.value !== "";
    itemRowsContainer.querySelectorAll('input').forEach(function(inp) { inp.disabled = disable; if (disable) inp.value = ''; });
    addRowBtn.disabled = disable;
    if (customerInput) { customerInput.disabled = disable; if (disable) customerInput.value = ''; }
    if (customerHidden) { customerHidden.disabled = disable; if (disable) customerHidden.value = ''; }
  }

  soSelect.addEventListener('change', toggleInputs);
  toggleInputs();
});

// Auto calculate amount
document.addEventListener('input', function(e) {
  if (e.target.classList.contains('qty') || e.target.classList.contains('original_price')) {
    const row = e.target.closest('.item-row');
    const priceInput = row.querySelector('.original_price');
    const qtyInput = row.querySelector('.qty');
    const amountInput = row.querySelector('input[name="amount[]"]');
    const price = parseFloat(priceInput.value) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    amountInput.value = (price * qty).toFixed(2);
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
    if (!input || !hidden || !dropdown) return;
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
  // Item typeahead (sale: selling_price)
  function initItemTypeahead(w) {
    var input = w.querySelector('.item-typeahead-input');
    var hidden = w.querySelector('input[name="item_id[]"]');
    var dropdown = w.querySelector('.item-typeahead-dropdown');
    if (!input || !hidden || !dropdown) return;
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
        if (x.item_image) {
          var img = document.createElement('img');
          img.src = 'images/' + x.item_image;
          img.alt = x.item_name || '';
          img.onerror = function() { this.style.display = 'none'; };
          div.appendChild(img);
        }
        var label = document.createElement('span');
        label.textContent = (x.item_id || '') + ' - ' + (x.item_name || '');
        div.appendChild(label);
        div.onclick = function() {
          var row = w.closest('.item-row');
          hidden.value = x.item_id || '';
          input.value = (x.item_id || '') + ' - ' + (x.item_name || '');
          var priceInput = row ? row.querySelector('.original_price') : null;
          var stockSpan = row ? row.querySelector('.stock_balance') : null;
          if (priceInput) priceInput.value = x.selling_price != null ? x.selling_price : '';
          if (stockSpan) {
            stockSpan.innerText = '';
            fetch('get_item_by_id.php?item_id=' + encodeURIComponent(x.item_id)).then(function(r) { return r.json(); })
              .then(function(d) {
                if (d.success && stockSpan) {
                  var bal = parseInt(d.stock_balance, 10);
                  stockSpan.innerText = bal > 0 ? '' + bal : 'Out of Stock';
                  stockSpan.style.color = bal > 0 ? '' : 'red';
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