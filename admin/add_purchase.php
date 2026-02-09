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

  // Add Purchase
  if (isset($_POST['add_btn'])) {

      // --- Validate static fields ---
      if (empty($_POST['date'])) {
          $dateError = 'Date is required';
      }
      if (empty($_POST['grn_no'])) {
          $grn_noError = 'GRN No is required';
      }
      if (empty($_POST['po_no'])) {
          if (empty($_POST['supplier_id'])) {
              $supplier_idError = 'Supplier is required';
          }
      }

      // --- Get dynamic arrays ---
      $item_ids = $_POST['item_id'] ?? [];
      $qtys = $_POST['qty'] ?? [];
      $prices = $_POST['original_price'] ?? [];
      $discounts = $_POST['discount'] ?? [];
      $focs = $_POST['foc'] ?? [];

      // --- Filter only rows that actually have data ---
      $filtered_items = [];
      foreach ($item_ids as $index => $item_id) {
          $qty = trim($qtys[$index] ?? '');
          $price = trim($prices[$index] ?? '');

          // keep rows with valid data only
          if (!empty($item_id) && !empty($qty) && !empty($price)) {
              $d = $discounts[$index] ?? 0;
              $f = $focs[$index] ?? 0;
              $filtered_items[] = [
                  'item_id' => $item_id,
                  'qty' => $qty,
                  'price' => $price,
                  'discount' => ($d !== '' && $d !== null) ? (float) $d : 0,
                  'foc' => ($f !== '' && $f !== null) ? (int) $f : 0,
              ];
          }
      }

      // --- Validate filtered items ---
      $hasItemError = false;
      if (count($filtered_items) === 0 && empty($_POST['po_no'])) {
          $item_idError = "Please fill at least one item.";
          $hasItemError = true;
      }

      // --- If no errors, process data ---
      if (empty($dateError) && empty($grn_noError) && empty($supplier_idError) && !$hasItemError) {

          $date = $_POST['date'];
          $grn_no = $_POST['grn_no'];
          $type = $_POST['type'];
          $po_no = $_POST['po_no'] ?? '';

          if (empty($po_no)) {
              $supplier_id = $_POST['supplier_id'];
          }

          // If PO number is provided
          if (!empty($po_no)) {

              $purchase_orderstmt = $pdo->prepare("SELECT * FROM purchase_order WHERE order_no=:po_no");
              $purchase_orderstmt->execute([':po_no' => $po_no]);
              $purchase_order = $purchase_orderstmt->fetch(PDO::FETCH_ASSOC);
              $supplier_id = $purchase_order['supplier_id'];

              // Add Temp Purchase
              $addstmt = $pdo->prepare("
                  INSERT INTO temp_purchase
                  (date, grn_no, supplier_id, po_no, type, status)
                  VALUES
                  (:date, :grn_no, :supplier_id, :po_no, :type, 'draft')
              ");
              $addstmt->execute([
                  ':date' => $date,
                  ':grn_no' => $grn_no,
                  ':supplier_id' => $supplier_id,
                  ':type' => $type,
                  ':po_no' => $po_no,
              ]);

              // Get temp purchase id 
              $temp_purchase_idstmt = $pdo->prepare("SELECT * FROM temp_purchase ORDER BY id DESC LIMIT 1");
              $temp_purchase_idstmt->execute();
              $temp_purchase_id = $temp_purchase_idstmt->fetch(PDO::FETCH_ASSOC)['id'];

              $purchase_orderdatastmt = $pdo->prepare("SELECT * FROM purchase_order_items WHERE order_no=:po_no");
              $purchase_orderdatastmt->execute([':po_no' => $po_no]);
              $purchase_orderdata = $purchase_orderdatastmt->fetchAll();

              foreach ($purchase_orderdata as $purchase_order_item) {
                  $item_id = $purchase_order_item['item_id'];
                  $qty = $purchase_order_item['qty'];
                  $price = $purchase_order_item['price'];
                  $amount = $purchase_order_item['amount'];

                  $addstmt = $pdo->prepare("
                      INSERT INTO temp_purchase_items
                      (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, grn_no, temp_purchase_id)
                      VALUES
                      (:item_id, :price, :qty, :type, 0, 0, 0, :amount, :grn_no, :temp_purchase_id)
                  ");

                  $addstmt->execute([
                    ':item_id' => $item_id,
                    ':price' => $price,
                    ':qty' => $qty,
                    ':type' => $type,
                    ':amount' => $amount,
                    ':grn_no' => $grn_no,
                    ':temp_purchase_id' => $temp_purchase_id
                  ]);
              }

              if ($addstmt) {
                  // Update PO status
                  $stmt = $pdo->prepare("UPDATE purchase_order SET status='Delivered' WHERE order_no=:po_no");
                  $stmt->execute([':po_no' => $po_no]);
              }

          } else {
              // --- Without PO number ---
              $po_no = '';
              $addstmt = $pdo->prepare("
                  INSERT INTO temp_purchase
                  (date, grn_no, supplier_id, po_no, type, status)
                  VALUES
                  (:date, :grn_no, :supplier_id, :po_no, :type, 'draft')
              ");
              $addstmt->execute([
                  ':date' => $date,
                  ':grn_no' => $grn_no,
                  ':supplier_id' => $supplier_id,
                  ':type' => $type,
                  ':po_no' => $po_no,
              ]);

              // Get latest temp_purchase_id
              $temp_purchase_idstmt = $pdo->prepare("SELECT * FROM temp_purchase ORDER BY id DESC LIMIT 1");
              $temp_purchase_idstmt->execute();
              $temp_purchase_id = $temp_purchase_idstmt->fetch(PDO::FETCH_ASSOC)['id'];

              $add_itemstmt = $pdo->prepare("
                  INSERT INTO temp_purchase_items
                  (item_id, price, qty, type, percentage, percentage_amount, stock_foc, amount, grn_no, temp_purchase_id)
                  VALUES
                  (:item_id, :price, :qty, :type, :percentage, :percentage_amount, :stock_foc, :amount, :grn_no, :temp_purchase_id)
              ");

              foreach ($filtered_items as $item) {
                  $item_id = $item['item_id'];
                  $qty = $item['qty'];
                  $price = $item['price'];
                  $foc = $item['foc'];
                  $discount = $item['discount'];

                  $amount = $price * $qty;
                  $percentage_amount = 0;

                  if (!empty($discount) && $discount > 0) {
                      $percentage_amount = ($amount / 100) * $discount;
                      $amount -= $percentage_amount;
                  }

                  $add_itemstmt->execute([
                      ':item_id' => $item_id,
                      ':price' => $price,
                      ':qty' => $qty,
                      ':type' => $type,
                      ':percentage' => (float) $discount,
                      ':percentage_amount' => $percentage_amount,
                      ':stock_foc' => (int) $foc,
                      ':amount' => $amount,
                      ':grn_no' => $grn_no,
                      ':temp_purchase_id' => $temp_purchase_id
                  ]);
              }
          }

          echo "<script>
              swal('Success!', 'Purchase Added Successfully', 'success');
          </script>";
      }
  }

  ?>
<style>
.supplier-typeahead, .item-typeahead { position: relative; }
.supplier-typeahead-dropdown, .item-typeahead-dropdown {
  position: absolute; left: 0; right: 0; top: 100%; z-index: 1000;
  max-height: 220px; overflow-y: auto;
  background: #fff; border: 1px solid #ced4da; border-top: none;
  border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  display: none;
}
.supplier-typeahead-dropdown.show, .item-typeahead-dropdown.show { display: block; }
.supplier-typeahead-dropdown .option, .item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.supplier-typeahead-dropdown .option:hover, .supplier-typeahead-dropdown .option.active,
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.supplier-typeahead-dropdown .option:last-child, .item-typeahead-dropdown .option:last-child { border-bottom: none; }
.supplier-typeahead-dropdown .no-result, .item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>
    <div class="col-md-12 mt-2 px-3 pt-1">
      <div class="collapse show" id="">  
        <form class="" action="" method="post">
          <div class="card">
            <div class="card-header py-2 pb-0 pt-3">
              <h5 class="d-flex align-items-center justify-content-between">
                Add New Purchase
                <div class="d-flex">
                    <div>
                      <a href="purchase.php" class="btn btn-sm btn-success" style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;" name="save_btn">View Purchase Bills</a>
                    </div>
                </div>
              </h5>
            </div>
            <div class="card-body" style="background-color: rgba(0,0,0,0.01);">
              <div class="row">
                <div class="col-6 d-flex">
                  <div class="col">
                    <label for="">Date</label>
                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" name="date">
                    <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
                  </div>
                  <div class="col">
                    <label for="">GRN_No</label>
                    <input type="text" class="form-control" placeholder="GRN No" name="grn_no">
                    <p style="color:red;"><?php echo empty($grn_noError) ? '' : '*'.$grn_noError;?></p>
                  </div>
                  <div class="col">
                  <label for="">PO No</label>
                  <select name="po_no" id="po_no" class="form-control">
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
                  <label for="supplier_display_main">Supplier</label>
                  <div class="supplier-typeahead" id="supplier_typeahead_main">
                    <input type="text" class="form-control supplier-typeahead-input" id="supplier_display_main" placeholder="Type supplier code or name..." autocomplete="off">
                    <input type="hidden" name="supplier_id" id="supplier_id_main">
                    <div class="supplier-typeahead-dropdown" id="supplier_dropdown_main"></div>
                  </div>
                  <p style="color:red;"><?php echo empty($supplier_idError) ? '' : '*'.$supplier_idError;?></p>
                </div>
                <div class="col-2">
                  <label for="">Payment</label>
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
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right original_price" 
                                  name="original_price[]">
                          </td>
                          <td class="text-right">
                            <span class="stock_balance"></span>
                          </td>
                          <td class="no-padding">
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right discount" 
                                  name="discount[]">
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right qty" 
                                  name="qty[]">
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right foc" 
                                  name="foc[]">
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right" 
                                  name="amount[]">
                          </td>

                          <td class="no-padding text-center" style="background:none !important; cursor:pointer !important; width: 30px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                class="bi bi-x-lg remove-row-btn" viewBox="0 0 16 16">
                              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                            </svg>
                          </td>

                      </tr>
                    </tbody>
                </table>
              </div>
              <template id="item-row-template">
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
                  <td class="no-padding">
                    <input type="number" class="custom-input text-right discount" name="discount[]">
                  </td>
                  <td class="no-padding">
                    <input type="number" class="custom-input text-right qty" name="qty[]">
                  </td>
                  <td class="no-padding">
                    <input type="number" class="custom-input text-right foc" name="foc[]">
                  </td>
                  <td class="no-padding">
                    <input type="number" class="custom-input text-right" name="amount[]">
                  </td>
                  <td class="no-padding text-center" style="background:none !important; cursor:pointer !important; width: 30px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg remove-row-btn" viewBox="0 0 16 16">
                      <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                  </td>
                </tr>
              </template>
              <div>
                  <button type="button" id="add-row-btn" class="btn btn-default text-info btn-sm ml-2">+ Add a new line</button>
              </div>
            </div>
            <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
              <!-- Buttons -->
              <div class="d-flex justify-content-end mt-2">
                <div>
                  <button type="submit" name="add_btn" class="btn btn-purple text-light btn-sm mr-2">Add Purchase</button>
                </div>
                <div>
                    <a href="purchase.php" class="btn btn-secondary btn-sm text-light">Cancel</a>
                </div>
              </div>
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

      // --- Show 5 rows initially ---
      const firstRow = container.querySelector('.item-row');
      for (let i = 1; i < 6; i++) { // already 1 row exists, so clone 4 more
          const clone = firstRow.cloneNode(true);
          clone.querySelectorAll('input').forEach(input => input.value = '');
          clone.querySelectorAll('.stock_balance').forEach(span => span.innerText = '');
          container.appendChild(clone);
      }

      // --- Add row button ---
      addBtn.addEventListener('click', function () {
          const firstRow = container.querySelector('.item-row');
          const clone = firstRow.cloneNode(true);

          clone.querySelectorAll('input').forEach(function(input) { input.value = ''; });
          clone.querySelectorAll('.stock_balance').forEach(function(span) { span.innerText = ''; });
          var itemDrop = clone.querySelector('.item-typeahead-dropdown');
          if (itemDrop) { itemDrop.innerHTML = ''; itemDrop.classList.remove('show'); }

          if (!clone.querySelector('.remove-row-btn')) {
              const removeBtn = document.createElement('button');
              removeBtn.type = 'button';
              removeBtn.className = 'btn btn-danger remove-row-btn';
              removeBtn.textContent = '- Remove';
              const colDiv = document.createElement('div');
              colDiv.className = 'col-1 mt-4 mr-4';
              colDiv.appendChild(removeBtn);
              clone.appendChild(colDiv);
          }

          container.appendChild(clone);
          if (window.initItemTypeahead) {
              var typeahead = clone.querySelector('.item-typeahead');
              if (typeahead) window.initItemTypeahead(typeahead);
          }
      });

      // --- Remove row functionality ---
      container.addEventListener('click', function(e) {
          if (e.target && e.target.classList.contains('remove-row-btn')) {
              const row = e.target.closest('.item-row');
              if (row) row.remove();
          }
      });
  });
</script>

<script>
// When PO selected: fill supplier + item table (current structure). When cleared: restore empty rows.
document.addEventListener('DOMContentLoaded', function () {
  const poSelect = document.getElementById('po_no');
  const itemRowsContainer = document.getElementById('item-rows');
  const addRowBtn = document.getElementById('add-row-btn');
  const supplierTypeahead = document.getElementById('supplier_typeahead_main');
  const rowTemplate = document.getElementById('item-row-template');

  function toggleInputs(disable) {
    addRowBtn.disabled = disable;
    if (supplierTypeahead) {
      var supplierInput = supplierTypeahead.querySelector('.supplier-typeahead-input');
      var supplierHidden = supplierTypeahead.querySelector('input[name="supplier_id"]');
      if (supplierInput) supplierInput.disabled = disable;
      if (supplierHidden) supplierHidden.disabled = disable;
    }
    itemRowsContainer.querySelectorAll('.item-typeahead-input, input[name="item_id[]"], .original_price, .discount, .qty, .foc, input[name="amount[]"]').forEach(function(el) {
      el.disabled = disable;
    });
    if (!disable) {
      itemRowsContainer.querySelectorAll('.original_price, .discount, .qty, .foc, input[name="amount[]"]').forEach(function(input) {
        input.disabled = false;
      });
    }
  }

  function restoreEmptyRows() {
    itemRowsContainer.innerHTML = '';
    if (!rowTemplate || !rowTemplate.content) return;
    for (var i = 0; i < 5; i++) {
      var clone = rowTemplate.content.cloneNode(true);
      itemRowsContainer.appendChild(clone);
    }
    itemRowsContainer.querySelectorAll('.item-typeahead').forEach(function(wrapper) {
      if (window.initItemTypeahead) window.initItemTypeahead(wrapper);
    });
  }

  function fillPoInCurrentStructure(orderNo) {
    if (!orderNo) {
      var supplierInput = supplierTypeahead && supplierTypeahead.querySelector('.supplier-typeahead-input');
      var supplierHidden = supplierTypeahead && supplierTypeahead.querySelector('input[name="supplier_id"]');
      if (supplierInput) supplierInput.value = '';
      if (supplierHidden) supplierHidden.value = '';
      restoreEmptyRows();
      return;
    }
    fetch('get_po_details.php?order_no=' + encodeURIComponent(orderNo))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data.success) return;
        var supplierInput = supplierTypeahead && supplierTypeahead.querySelector('.supplier-typeahead-input');
        var supplierHidden = supplierTypeahead && supplierTypeahead.querySelector('input[name="supplier_id"]');
        if (supplierInput) supplierInput.value = (data.supplier_id || '') + ' - ' + (data.supplier_name || '');
        if (supplierHidden) supplierHidden.value = data.supplier_id || '';

        itemRowsContainer.innerHTML = '';
        var items = data.items || [];
        items.forEach(function(it) {
          var tr = document.createElement('tr');
          tr.className = 'item-row';
          tr.style.fontSize = '15px';
          var price = parseFloat(it.price) || 0;
          var qty = parseInt(it.qty, 10) || 0;
          var amount = parseFloat(it.amount) || 0;
          var itemText = (it.item_id || '') + ' - ' + (it.item_name || '');

          var tdItem = document.createElement('td');
          tdItem.className = 'no-padding';
          tdItem.style.minWidth = '250px';
          var inpItem = document.createElement('input');
          inpItem.type = 'text';
          inpItem.className = 'custom-input';
          inpItem.value = itemText;
          inpItem.disabled = true;
          inpItem.style.background = '#f5f5f5';
          tdItem.appendChild(inpItem);

          function numCell(val, cls) {
            var td = document.createElement('td');
            td.className = 'no-padding';
            var inp = document.createElement('input');
            inp.type = 'number';
            inp.className = 'custom-input text-right ' + (cls || '');
            inp.value = val;
            inp.disabled = true;
            inp.style.background = '#f5f5f5';
            td.appendChild(inp);
            return td;
          }
          var tdStock = document.createElement('td');
          tdStock.className = 'text-right';
          tdStock.appendChild(document.createElement('span'));

          tr.appendChild(tdItem);
          tr.appendChild(numCell(price, 'original_price'));
          tr.appendChild(tdStock);
          tr.appendChild(numCell(0, 'discount'));
          tr.appendChild(numCell(qty, 'qty'));
          tr.appendChild(numCell(0, 'foc'));
          tr.appendChild(numCell(amount, ''));
          tr.appendChild(document.createElement('td'));
          itemRowsContainer.appendChild(tr);
        });
      })
      .catch(function() {});
  }

  poSelect.addEventListener('change', function() {
    var val = poSelect.value.trim();
    if (val) {
      fillPoInCurrentStructure(val);
      toggleInputs(true);
    } else {
      fillPoInCurrentStructure(null);
      toggleInputs(false);
    }
  });
  toggleInputs(!!poSelect.value);
  if (poSelect.value) fillPoInCurrentStructure(poSelect.value);
});
</script>

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
<?php include 'footer.html'; ?>
<!-- Supplier typeahead -->
<script>
(function() {
  var searchTimeout;
  function displayText(s) { return (s.supplier_id || '') + ' - ' + (s.supplier_name || ''); }
  function searchSuppliers(q, callback) {
    if (!q || q.trim() === '') { callback([]); return; }
    fetch('get_suppliers_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { callback(d.success && d.results ? d.results : []); }).catch(function() { callback([]); });
  }
  function initSupplierTypeahead(wrapper) {
    var input = wrapper.querySelector('.supplier-typeahead-input');
    var hidden = wrapper.querySelector('input[name="supplier_id"]');
    var dropdown = wrapper.querySelector('.supplier-typeahead-dropdown');
    if (!input || !hidden || !dropdown) return;
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
    input.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(doSearch, 300);
    });
    input.addEventListener('focus', function() {
      if (input.value.trim()) doSearch();
    });
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (opts.length === 0) return;
      var active = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!active) { opts[0].classList.add('active'); return; } active.classList.remove('active'); var next = active.nextElementSibling; if (next) next.classList.add('active'); else opts[0].classList.add('active'); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!active) { opts[opts.length - 1].classList.add('active'); return; } active.classList.remove('active'); var prev = active.previousElementSibling; if (prev) prev.classList.add('active'); else opts[opts.length - 1].classList.add('active'); }
      else if (e.key === 'Enter' && active) { e.preventDefault(); active.click(); }
    });
    document.addEventListener('click', function(e) { if (!wrapper.contains(e.target)) dropdown.classList.remove('show'); });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.supplier-typeahead').forEach(initSupplierTypeahead);
  });
})();
</script>
<!-- Item typeahead (multiple rows) -->
<script>
(function() {
  var itemSearchTimeout;
  function searchItems(q, done) {
    if (!q || q.trim() === '') { done([]); return; }
    fetch('get_items_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { done(d.success && d.results ? d.results : []); }).catch(function() { done([]); });
  }
  function initItemTypeahead(wrapper) {
    if (!wrapper) return;
    var input = wrapper.querySelector('.item-typeahead-input');
    var hidden = wrapper.querySelector('input[name="item_id[]"]');
    var dropdown = wrapper.querySelector('.item-typeahead-dropdown');
    var row = wrapper.closest('.item-row');
    var priceInput = row ? row.querySelector('.original_price') : null;
    var stockSpan = row ? row.querySelector('.stock_balance') : null;
    if (!input || !hidden || !dropdown) return;
    function render(list) {
      dropdown.innerHTML = '';
      if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching item</div>';
      else list.forEach(function(x) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = (x.item_id || '') + ' - ' + (x.item_name || '');
        div.onclick = function() {
          hidden.value = x.item_id || '';
          input.value = (x.item_id || '') + ' - ' + (x.item_name || '');
          if (priceInput) {
            priceInput.value = x.original_price != null ? x.original_price : '';
            priceInput.dispatchEvent(new Event('input'));
          }
          dropdown.classList.remove('show');
          if (stockSpan) {
            stockSpan.innerText = '';
            stockSpan.style.color = '';
            fetch('get_item_by_id.php?item_id=' + encodeURIComponent(x.item_id)).then(function(r) { return r.json(); }).then(function(data) {
              if (data.success && stockSpan) {
                var bal = parseInt(data.stock_balance, 10) || 0;
                if (bal > 0) {
                  stockSpan.innerText = bal;
                  stockSpan.style.color = '';
                } else {
                  stockSpan.innerText = 'Out of Stock';
                  stockSpan.style.color = 'red';
                }
              }
            }).catch(function() {});
          }
        };
        dropdown.appendChild(div);
      });
      dropdown.classList.add('show');
    }
    input.addEventListener('input', function() {
      clearTimeout(itemSearchTimeout);
      var q = input.value.trim();
      if (!q) {
        hidden.value = '';
        if (priceInput) priceInput.value = '';
        if (stockSpan) { stockSpan.innerText = ''; stockSpan.style.color = ''; }
        dropdown.classList.remove('show');
        return;
      }
      itemSearchTimeout = setTimeout(function() { searchItems(q, render); }, 300);
    });
    input.addEventListener('focus', function() {
      if (input.value.trim()) searchItems(input.value.trim(), render);
    });
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (opts.length === 0) return;
      var active = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!active) { opts[0].classList.add('active'); return; } active.classList.remove('active'); var next = active.nextElementSibling; if (next) next.classList.add('active'); else opts[0].classList.add('active'); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!active) { opts[opts.length - 1].classList.add('active'); return; } active.classList.remove('active'); var prev = active.previousElementSibling; if (prev) prev.classList.add('active'); else opts[opts.length - 1].classList.add('active'); }
      else if (e.key === 'Enter' && active) { e.preventDefault(); active.click(); }
    });
    document.addEventListener('click', function(e) { if (!wrapper.contains(e.target)) dropdown.classList.remove('show'); });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
  });
  window.initItemTypeahead = initItemTypeahead;
})();
</script>