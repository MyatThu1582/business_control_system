<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

  // Add Purchase Order
  if (isset($_POST['add_btn'])) {
      // --- Validate static fields ---
      if (empty($_POST['order_date'])) {
          $dateError = 'Date is required';
      }
      if (empty($_POST['order_no'])) {
          $vr_noError = 'Vr_No is required';
      }
      if (empty($_POST['supplier_id'])) {
          $supplier_idError = 'Supplier is required';
      }

      // --- Validate dynamic arrays ---
      $item_ids = $_POST['item_id'] ?? [];
      $qtys = $_POST['qty'] ?? [];
      $prices = $_POST['original_price'] ?? [];

      $hasItemError = false;

      foreach ($item_ids as $index => $item_id) {
          $qty = trim($qtys[$index] ?? '');
          $price = trim($prices[$index] ?? '');

          if (empty($item_id)) {
              $item_idError = "Item ID is required in row " . ($index + 1);
              $hasItemError = true;
              break; // stop at first error
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
      if (empty($dateError) && empty($vr_noError) && empty($supplier_idError) && !$hasItemError) {
          $order_date = $_POST['order_date'];
          $order_no = "PO-" . $_POST['order_no'];
          $supplier_id = $_POST['supplier_id'];

          // Insert main purchase order
          $addstmt = $pdo->prepare("
              INSERT INTO purchase_order (order_date, order_no, supplier_id, status) 
              VALUES (:order_date, :order_no, :supplier_id, 'Pending')
          ");
          $addResult = $addstmt->execute([
              ':order_date' => $order_date,
              ':order_no' => $order_no,
              ':supplier_id' => $supplier_id
          ]);

          if ($addResult) {
              $stmt = $pdo->prepare("SELECT * FROM purchase_order ORDER BY id DESC LIMIT 1");
                        $stmt->execute();
                        $purchase_orderdata = $stmt->fetch(PDO::FETCH_ASSOC);
              $purchase_orderid = $purchase_orderdata['id'];

              // Insert each item row
              $itemStmt = $pdo->prepare("
                  INSERT INTO purchase_order_items (item_id, qty, price, amount, order_no, purchase_orderid)
                  VALUES (:item_id, :qty, :price, :amount, :order_no, :purchase_orderid)
              ");

              foreach ($item_ids as $index => $item_id) {
                  $qty = $qtys[$index];
                  $price = $prices[$index];
                  $amount = $price * $qty;

                  $itemStmt->execute([
                      ':item_id' => $item_id,
                      ':qty' => $qty,
                      ':price' => $price,
                      ':amount' => $amount,
                      ':order_no' => $order_no,
                      ':purchase_orderid' => $purchase_orderid
                  ]);
              }

              echo "<script>swal('Success!', 'Purchase Order Added Successfully', 'success');</script>";
          }
      }
  }


    $drawerToOpen = null;

    if (isset($_POST['update_btn'])) {
          $update_id = $_POST['update_id'];
          
          // Check empty fields
          if (empty($_POST['order_date']) || empty($_POST['order_no']) || empty($_POST['supplier_id'])) {
              // Set drawer to open
              $drawerToOpen = $update_id;

              // Set drawer-specific errors
              $dateErrorDrawer = empty($_POST['order_date']) ? 'Date is required' : '';
              $vr_noErrorDrawer = empty($_POST['order_no']) ? 'Vr_No is required' : '';
              $supplier_idErrorDrawer = empty($_POST['supplier_id']) ? 'Supplier is required' : '';
          } else {
            // All fields filled, proceed to update
            $update_id = $_POST['update_id']; // hidden field from the drawer form
            $order_date = $_POST['order_date'];
            $order_no = "PO-" . $_POST['order_no'];
            $supplier_id = $_POST['supplier_id'];

            $updatestmt = $pdo->prepare("UPDATE purchase_order SET order_date=:order_date, order_no=:order_no, supplier_id=:supplier_id WHERE id=:id");
            $updateResult = $updatestmt->execute(
                array(
                    ':order_date' => $order_date,
                    ':order_no' => $order_no,
                    ':supplier_id' => $supplier_id,
                    ':id' => $update_id
                )
            );

            $update_orderitemstmt = $pdo->prepare("UPDATE purchase_order_items SET order_no=:order_no WHERE purchase_orderid=:id");
            $update_orderitem = $update_orderitemstmt->execute(
                array(
                    ':order_no' => $order_no,
                    ':id' => $update_id
                )
            );

            if ($updateResult) {
                echo "<script>
                        swal('Success!', 'Purchase Order Updated Successfully', 'success');
                      </script>";
            }
        }
    }

    $purchase_orderstmt = $pdo->prepare("SELECT * FROM purchase_order ORDER BY id DESC");
    $purchase_orderstmt->execute();
    $purchase_orderdata = $purchase_orderstmt->fetchAll();
 ?>
<style>
.supplier-typeahead { position: relative; }
.supplier-typeahead-dropdown {
  position: absolute; left: 0; right: 0; top: 100%; z-index: 1000;
  max-height: 220px; overflow-y: auto;
  background: #fff; border: 1px solid #ced4da; border-top: none;
  border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  display: none;
}
.supplier-typeahead-dropdown.show { display: block; }
.supplier-typeahead-dropdown .option {
  padding: 8px 12px; cursor: pointer; font-size: 14px;
  border-bottom: 1px solid #eee;
}
.supplier-typeahead-dropdown .option:hover,
.supplier-typeahead-dropdown .option.active { background: #e9ecef; }
.supplier-typeahead-dropdown .option:last-child { border-bottom: none; }
.supplier-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
/* Item typeahead (same look, scoped to row) */
.item-typeahead { position: relative; }
.item-typeahead-dropdown {
  position: absolute; left: 0; right: 0; top: 100%; z-index: 1000;
  max-height: 220px; overflow-y: auto;
  background: #fff; border: 1px solid #ced4da; border-top: none;
  border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  display: none;
}
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead-dropdown .option:last-child { border-bottom: none; }
.item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>


  <div class="col-md-12 mt-4 px-3 pt-1">
    <h4 class="mb-3 d-flex align-items-center justify-content-between">
        Purchase Order Listings
        <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#newOrderForm" aria-expanded="true" aria-controls="newSaleForm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/>
          </svg>
        </button>
      </h4>
    <div class="collapse show" id="newOrderForm">  
      <div class="card">
        <div class="card-body">
          <form class="" action="" method="post" style="margin-top:-20px;">
            <div class="row">
              <div class="col-3">
                <label for="" class="mt-4">Order Date</label>
                <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" name="order_date">
                <span style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></span>
              </div>
              <div class="col-3">
                <label for="" class="mt-4">Order No</label>
                <div class="d-flex">
                  <input type="text" class="form-control w-25 mr-2" value="<?php echo "PO";?>" readonly>
                  <input type="number" class="form-control" name="order_no" placeholder="Fill your PO NO">
                </div>
                <span style="color:red;"><?php echo empty($vr_noError) ? '' : '*'.$vr_noError;?></span>
              </div>
              <div class="col-6">
                <label for="supplier_display_main" class="mt-4">Supplier</label>
                <div class="supplier-typeahead" id="supplier_typeahead_main">
                  <input type="text" class="form-control supplier-typeahead-input" id="supplier_display_main" placeholder="Type supplier code or name..." autocomplete="off">
                  <input type="hidden" name="supplier_id" id="supplier_id_main">
                  <div class="supplier-typeahead-dropdown" id="supplier_dropdown_main"></div>
                </div>
                <span style="color:red;"><?php echo empty($supplier_idError) ? '' : '*'.$supplier_idError;?></span>
              </div>
            </div>
            <!-- Second Row (Item Rows Container) -->
            <div id="item-rows">
              <div class="row mt-3 mb-3 item-row">
                <div class="col">
                  <label>Item</label>
                  <div class="item-typeahead">
                    <input type="text" class="form-control item-typeahead-input" placeholder="Type item code or name..." autocomplete="off">
                    <input type="hidden" name="item_id[]">
                    <div class="item-typeahead-dropdown"></div>
                  </div>
                  <span class="stock_balance" style="color:green; font-size: 15px;"></span>
                </div>
                <div class="col">
                  <label>Price</label>
                  <input type="number" class="form-control original_price" placeholder="Price" name="original_price[]">
                </div>
                <div class="col">
                  <label>Qty</label>
                  <input type="number" class="form-control qty" placeholder="Qty" name="qty[]">
                </div>
              </div>
            </div>
            
            <div class="d-flex mt-2 justify-content-end">
              <!-- Single Plus Button -->
              <div class="mt-2 mr-2">
                <button type="button" id="add-row-btn" class="btn btn-info">+ Add Item</button>
              </div>
              
              <!-- Submit Button (Outside of Rows) -->
              <div class="mt-2">
                <button type="submit" name="add_btn" class="btn btn-purple text-light">
                  Add Purchase Order
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div> 
  <div>
    <table class="table table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Order Date</th>
          <th>Order No</th>
          <th>Supplier Name</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($purchase_orderdata) {
            $idd = 1;
            foreach ($purchase_orderdata as $value) {
              $id = $value['id'];
              $supplier_id = $value['supplier_id'];
              $order_no = $value['order_no'];

              // Supplier Name
              $supplierIdstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
              $supplierIdstmt->execute();
              $supplierIdResult = $supplierIdstmt->fetch(PDO::FETCH_ASSOC);

              $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM purchase_order_items WHERE order_no='$order_no'");
              $total_amountstmt->execute();
              $total_amount = $total_amountstmt->fetch(PDO::FETCH_ASSOC);

         ?>
        <tr>
          <td><?php echo $idd; ?></td>
          <td><?php echo date('d M Y', strtotime($value['order_date'])); ?></td>
          <td><?php echo $value['order_no']; ?></td>
          <td><?php echo $supplierIdResult['supplier_name']; ?></td>
          <td class="text-right"><?php echo number_format($total_amount['total_amount']); ?></td>
          <td>
            <div class="badge badge-primary">Pending</div>
          </td>
          <td>
            <button 
                class="btn btn-sm"
                onclick="openDrawer(<?php echo $value['id']; ?>)">
              <i class="fas fa-edit"></i>
            </button>

            <!-- Drawer (Hidden by default) -->
            <div id="drawer<?php echo $value['id']; ?>" class="drawer shadow-lg <?php echo ($drawerToOpen == $value['id']) ? 'open' : ''; ?>">
              <div class="drawer-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Edit Purchase Order</h5>
                <button type="button" class="btn-close" onclick="closeDrawer(<?php echo $value['id']; ?>)"></button>
              </div>

              <div class="drawer-body p-4 h-100">
                <form action="" method="post">
                  <input type="hidden" name="update_id" value="<?php echo $value['id']; ?>">

                  <!-- First Row -->
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Order Date</label>
                      <input type="date" class="form-control" name="order_date" value="<?php echo $value['order_date']; ?>">
                      <span style="color:red;"><?php echo empty($dateErrorDrawer) ? '' : '*'.$dateErrorDrawer; ?></span>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Order No</label>
                      <div class="d-flex gap-2">
                        <input type="text" class="form-control w-50" value="PO" readonly>
                        <input type="number" class="form-control" name="order_no" value="<?php echo str_replace('PO-','',$value['order_no']); ?>">
                      </div>
                      <span style="color:red;"><?php echo empty($vr_noErrorDrawer) ? '' : '*'.$vr_noErrorDrawer; ?></span>
                    </div>
                  </div>

                  <!-- Second Row -->
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Supplier</label>
                      <div class="supplier-typeahead supplier-typeahead-drawer" data-drawer-id="<?php echo $value['id']; ?>">
                        <input type="text" class="form-control supplier-typeahead-input" id="supplier_display_<?php echo $value['id']; ?>" placeholder="Type supplier code or name..." value="<?php echo htmlspecialchars($value['supplier_id'] . ' - ' . ($supplierIdResult['supplier_name'] ?? '')); ?>" autocomplete="off">
                        <input type="hidden" name="supplier_id" id="supplier_id_drawer_<?php echo $value['id']; ?>" value="<?php echo htmlspecialchars($value['supplier_id']); ?>">
                        <div class="supplier-typeahead-dropdown" id="supplier_dropdown_<?php echo $value['id']; ?>"></div>
                      </div>
                      <span style="color:red;"><?php echo empty($supplier_idErrorDrawer) ? '' : '*'.$supplier_idErrorDrawer; ?></span>
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <div class="d-flex justify-content-center gap-2 border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="closeDrawer(<?php echo $value['id']; ?>)">
                      Cancel
                    </button>
                    <button type="submit" name="update_btn" class="btn btn-purple text-light ml-2 px-4">
                      Update
                    </button>
                  </div>

                </form>
              </div>
            </div>
            <div id="drawerBackdrop<?php echo $value['id']; ?>" class="drawer-backdrop <?php echo ($drawerToOpen == $value['id']) ? 'show' : ''; ?>" onclick="closeDrawer(<?php echo $value['id']; ?>)"></div>
            
            <a href="delete.php?table_name=purchase_order&id=<?php echo $id; ?>&order_no=<?php echo $value['order_no'] ?>" class="btn btn-sm delete-order">
              <i class="fas fa-trash"></i>
            </a>

            <a href="purchase_order_detail.php?order_no=<?php echo $value['order_no']; ?>" class="btn btn-sm btn-primary ml-2">View Detail</a>

          </td>
        </tr>
        <?php
          $idd++;
            }
          }
         ?>
      </tbody>
    </table>
  </div>
</div>
<!-- For multiple item add -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('item-rows');
    const addBtn = document.getElementById('add-row-btn');

    addBtn.addEventListener('click', function () {
      const firstRow = container.querySelector('.item-row');
      const clone = firstRow.cloneNode(true);

      // clear inputs and stock balance
      clone.querySelectorAll('input').forEach(function(input) { input.value = ""; });
      clone.querySelectorAll('.stock_balance').forEach(function(span) { span.innerText = ""; });
      var itemDrop = clone.querySelector('.item-typeahead-dropdown');
      if (itemDrop) { itemDrop.innerHTML = ""; itemDrop.classList.remove('show'); }

      // Add remove button if not exists
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
      // init item typeahead on the new row
      if (window.initItemTypeahead) {
        var typeahead = clone.querySelector('.item-typeahead');
        if (typeahead) window.initItemTypeahead(typeahead);
      }
    });

    // Remove row listener
    container.addEventListener('click', function(e){
      if(e.target && e.target.classList.contains('remove-row-btn')){
        const row = e.target.closest('.item-row');
        if(row){
          row.remove();
        }
      }
    });
  });
</script>


<!-- for Edit Drawer -->
<script>
function openDrawer(id) {
  document.getElementById("drawer" + id).classList.add("open");
  document.getElementById("drawerBackdrop" + id).classList.add("show");
}

function closeDrawer(id) {
  document.getElementById("drawer" + id).classList.remove("open");
  document.getElementById("drawerBackdrop" + id).classList.remove("show");
}
</script>

<!-- For Delete Order -->
<script>
  document.querySelectorAll('.delete-order').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this order!",
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
<?php include 'footer.html'; ?>
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

  function searchItems(q, done) {
    if (!q || q.trim() === '') { done([]); return; }
    fetch('get_items_search.php?q=' + encodeURIComponent(q.trim())).then(function(r) { return r.json(); })
      .then(function(d) { done(d.success && d.results ? d.results : []); }).catch(function() { done([]); });
  }
  function initItemTypeahead(w) {
    var input = w.querySelector('.item-typeahead-input');
    var hidden = w.querySelector('input[name="item_id[]"]');
    var dropdown = w.querySelector('.item-typeahead-dropdown');
    var row = w.closest('.item-row');
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
          if (priceInput) priceInput.value = x.original_price != null ? x.original_price : '';
          dropdown.classList.remove('show');
          if (stockSpan) {
            stockSpan.innerText = '';
            fetch('get_item_by_id.php?item_id=' + encodeURIComponent(x.item_id)).then(function(r) { return r.json(); })
              .then(function(d) { if (d.success && stockSpan) stockSpan.innerText = 'Balance Qty is ' + (d.stock_balance || 0) + ' pcs'; }).catch(function() {});
          }
        };
        dropdown.appendChild(div);
      });
      dropdown.classList.add('show');
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; if (priceInput) priceInput.value = ''; if (stockSpan) stockSpan.innerText = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(function() { searchItems(q, render); }, 300);
    };
    input.onfocus = function() { if (input.value.trim()) searchItems(input.value.trim(), render); };
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
    document.querySelectorAll('.supplier-typeahead').forEach(initSupplierTypeahead);
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
    window.initItemTypeahead = initItemTypeahead;
  });
})();
</script>
