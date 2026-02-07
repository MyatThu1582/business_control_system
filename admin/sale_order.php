<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

if(isset($_POST['cancel'])){
  $order_no = $_POST['order_no'];

  $stmt = $pdo->prepare("UPDATE sale_order SET status='cancel' WHERE order_no = '$order_no'");
  $stmt->execute();
}

// Add Purchase Order
  if (isset($_POST['add_btn'])) {
      // --- Validate static fields ---
      if (empty($_POST['order_date'])) {
          $dateError = 'Date is required';
      }
      if (empty($_POST['order_no'])) {
          $vr_noError = 'Vr_No is required';
      }
      if (empty($_POST['customer_id'])) {
          $customer_idError = 'Customer is required';
      }

      // --- Validate dynamic arrays ---
      $item_ids = $_POST['item_id'] ?? [];
      $qtys = $_POST['qty'] ?? [];
      $prices = $_POST['selling_price'] ?? [];

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
      if (empty($dateError) && empty($vr_noError) && empty($customer_idError) && !$hasItemError) {
          $order_date = $_POST['order_date'];
          $order_no = "PO-" . $_POST['order_no'];
          $customer_id = $_POST['customer_id'];

          // Insert main purchase order
          $addstmt = $pdo->prepare("
              INSERT INTO sale_order (order_date, order_no, customer_id, status) 
              VALUES (:order_date, :order_no, :customer_id, 'Pending')
          ");
          $addResult = $addstmt->execute([
              ':order_date' => $order_date,
              ':order_no' => $order_no,
              ':customer_id' => $customer_id
          ]);

          if ($addResult) {
              $stmt = $pdo->prepare("SELECT * FROM sale_order ORDER BY id DESC LIMIT 1");
                        $stmt->execute();
                        $sale_orderdata = $stmt->fetch(PDO::FETCH_ASSOC);
              $sale_orderid = $sale_orderdata['id'];

              // Insert each item row
              $itemStmt = $pdo->prepare("
                  INSERT INTO sale_order_items (item_id, qty, price, amount, order_no, sale_orderid)
                  VALUES (:item_id, :qty, :price, :amount, :order_no, :sale_orderid)
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
                      ':sale_orderid' => $sale_orderid
                  ]);
              }

              echo "<script>swal('Success!', 'Sale Order Added Successfully', 'success');</script>";
          }
      }
  }

  $drawerToOpen = null;

  if (isset($_POST['update_btn'])) {
        $update_id = $_POST['update_id'];
        
        // Check empty fields
        if (empty($_POST['order_date']) || empty($_POST['order_no']) || empty($_POST['customer_id'])) {
            // Set drawer to open
            $drawerToOpen = $update_id;

            // Set drawer-specific errors
            $dateErrorDrawer = empty($_POST['order_date']) ? 'Date is required' : '';
            $vr_noErrorDrawer = empty($_POST['order_no']) ? 'Vr_No is required' : '';
            $customer_idErrorDrawer = empty($_POST['customer_id']) ? 'Customer is required' : '';
        } else {
          // All fields filled, proceed to update
          $update_id = $_POST['update_id']; // hidden field from the drawer form
          $order_date = $_POST['order_date'];
          $order_no = "PO-" . $_POST['order_no'];
          $customer_id = $_POST['customer_id'];

          $updatestmt = $pdo->prepare("UPDATE sale_order SET order_date=:order_date, order_no=:order_no, customer_id=:customer_id WHERE id=:id");
          $updateResult = $updatestmt->execute(
              array(
                  ':order_date' => $order_date,
                  ':order_no' => $order_no,
                  ':customer_id' => $customer_id,
                  ':id' => $update_id
              )
          );

          $update_orderitemstmt = $pdo->prepare("UPDATE sale_order_items SET order_no=:order_no WHERE sale_orderid=:id");
          $update_orderitem = $update_orderitemstmt->execute(
              array(
                  ':order_no' => $order_no,
                  ':id' => $update_id
              )
          );

          if ($updateResult) {
              echo "<script>
                      swal('Success!', 'Sale Order Updated Successfully', 'success');
                    </script>";
          }
      }
  }

  $sale_orderstmt = $pdo->prepare("SELECT * FROM sale_order WHERE status='pending' ORDER BY id DESC");
  $sale_orderstmt->execute();
  $sale_orderdata = $sale_orderstmt->fetchAll();

 ?>
<style>
.customer-typeahead { position: relative; }
.customer-typeahead-dropdown {
  position: absolute; left: 0; right: 0; top: 100%; z-index: 1000;
  max-height: 220px; overflow-y: auto;
  background: #fff; border: 1px solid #ced4da; border-top: none;
  border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  display: none;
}
.customer-typeahead-dropdown.show { display: block; }
.customer-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.customer-typeahead-dropdown .option:hover, .customer-typeahead-dropdown .option.active { background: #e9ecef; }
.customer-typeahead-dropdown .option:last-child { border-bottom: none; }
.customer-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>
<script>
function fetchItemNameFromId(input) {
    const row = input.closest('.item-row'); // find parent row
    const itemId = input.value.trim();

    const itemNameInput = row.querySelector('.item_name');
    const priceInput = row.querySelector('.selling_price');
    const stockSpan = row.querySelector('.stock_balance');

    if (itemId !== "") {
        fetch("get_item_by_id.php?item_id=" + encodeURIComponent(itemId))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                itemNameInput.value = data.item_name;
                stockSpan.innerText = "Balance Qty is " + data.stock_balance + " pcs";
                priceInput.value = data.selling_price;
            } else {
                itemNameInput.value = "";
                priceInput.value = "";
                stockSpan.innerText = "";
            }
        })
        .catch(err => console.error("Error fetching item name:", err));
    } else {
        itemNameInput.value = "";
        priceInput.value = "";
        stockSpan.innerText = "";
    }
}

function fetchItemIdFromName(input) {
    const row = input.closest('.item-row');
    const itemName = input.value.trim();

    const itemIdInput = row.querySelector('.item_id');
    const priceInput = row.querySelector('.selling_price');
    const stockSpan = row.querySelector('.stock_balance');

    if (itemName !== "") {
        fetch("get_item_by_name.php?item_name=" + encodeURIComponent(itemName))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                itemIdInput.value = data.item_id;
                stockSpan.innerText = "Balance Qty is " + data.stock_balance + " pcs";
                priceInput.value = data.selling_price;
            } else {
                itemIdInput.value = "";
                priceInput.value = "";
                stockSpan.innerText = "";
            }
        })
        .catch(err => console.error("Error fetching item id:", err));
    } else {
        itemIdInput.value = "";
        priceInput.value = "";
        stockSpan.innerText = "";
    }
}
</script>

  <div class="col-md-12 mt-4 px-3 pt-1">
        <h4 class="mb-3 d-flex align-items-center justify-content-between">
          Sale Order Listings
          <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#newSaleForm" aria-expanded="true" aria-controls="newSaleForm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/>
            </svg>
          </button>
        </h4>
      <div class="collapse show" id="newSaleForm">
            <div class="card">
      <div class="card-body">
        <form class="" action="" method="post" style="margin-top:-20px;">
          <div class="row">
            <div class="col-3">
              <label for="" class="mt-4">Order Date</label>
              <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" name="order_date">
              <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
            </div>
            <div class="col-3">
              <label for="" class="mt-4">Order No</label>
              <div class="d-flex">
                <input type="text" class="form-control w-25 mr-2" value="<?php echo "SO"; ?>" readonly>
                <input type="text" class="form-control" name="order_no" value="<?php echo rand(1,999999) ?>">
              </div>
              <p style="color:red;"><?php echo empty($vr_noError) ? '' : '*'.$vr_noError;?></p>
            </div>
            <div class="col-6">
              <label for="customer_display_main" class="mt-4">Customer</label>
              <div class="customer-typeahead" id="customer_typeahead_main">
                <input type="text" class="form-control customer-typeahead-input" id="customer_display_main" placeholder="Type customer code or name..." autocomplete="off">
                <input type="hidden" name="customer_id" id="customer_id_main">
                <div class="customer-typeahead-dropdown" id="customer_dropdown_main"></div>
              </div>
              <p style="color:red;"><?php echo empty($customer_idError) ? '' : '*'.$customer_idError;?></p>
            </div>
          </div>
          <!-- Second Row -->
          <div id="item-rows">
            <div class="row item-row">
              <div class="col">
                <label>Item Code</label>
                <input type="text" class="form-control item_id" placeholder="Item Code" name="item_id[]" oninput="fetchItemNameFromId(this)">
                <span class="stock_balance" style="color:green; font-size: 15px;"></span>
              </div>

              <div class="col">
                <label>Item Name</label>
                <input type="text" class="form-control item_name" placeholder="Item Name" name="item_name[]" oninput="fetchItemIdFromName(this)">
              </div>

              <div class="col">
                <label>Price</label>
                <input type="number" class="form-control selling_price" placeholder="Price" name="selling_price[]">
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
                Add Sale Order
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
          <th>Customer Name</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($sale_orderdata) {
            $idd = 1;
            foreach ($sale_orderdata as $value) {
              $id = $value['id'];
              $customer_id = $value['customer_id'];
              $order_no = $value['order_no'];

              // customer Name
              $customerIdstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
              $customerIdstmt->execute();
              $customerIdResult = $customerIdstmt->fetch(PDO::FETCH_ASSOC);

              $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM sale_order_items WHERE order_no='$order_no'");
              $total_amountstmt->execute();
              $total_amount = $total_amountstmt->fetch(PDO::FETCH_ASSOC);

         ?>
        <tr>
          <td><?php echo $idd; ?></td>
          <td><?php echo date('d M Y', strtotime($value['order_date'])); ?></td>
          <td><?php echo $value['order_no']; ?></td>
          <td><?php echo $customerIdResult['customer_name']; ?></td>
          <td><?php echo number_format($total_amount['total_amount']); ?></td>
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
                <h5 class="mb-0 fw-bold text-dark">Edit Sale Order</h5>
                <button type="button" class="btn-close" onclick="closeDrawer(<?php echo $value['id']; ?>)"></button>
              </div>

              <div class="drawer-body p-4">
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
                      <label class="form-label">Customer</label>
                      <div class="customer-typeahead customer-typeahead-drawer" data-drawer-id="<?php echo $value['id']; ?>">
                        <input type="text" class="form-control customer-typeahead-input" id="customer_display_<?php echo $value['id']; ?>" placeholder="Type customer code or name..." value="<?php echo htmlspecialchars($value['customer_id'] . ' - ' . ($customerIdResult['customer_name'] ?? '')); ?>" autocomplete="off">
                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($value['customer_id']); ?>">
                        <div class="customer-typeahead-dropdown" id="customer_dropdown_<?php echo $value['id']; ?>"></div>
                      </div>
                      <span style="color:red;"><?php echo empty($customer_idErrorDrawer) ? '' : '*'.$customer_idErrorDrawer; ?></span>
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
            
            <a href="delete.php?table_name=sale_order&id=<?php echo $id; ?>&order_no=<?php echo $value['order_no'] ?>" class="btn btn-sm delete-order">
              <i class="fas fa-trash"></i>
            </a>

            <a href="sale_order_detail.php?order_no=<?php echo $value['order_no']; ?>" class="btn btn-sm btn-primary ml-2">View Detail</a>

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
    clone.querySelectorAll('input').forEach(input => input.value = "");
    clone.querySelectorAll('.stock_balance').forEach(span => span.innerText = "");

    // Add remove button if not exists
    if (!clone.querySelector('.remove-row-btn')) {
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-danger remove-row-btn';
      removeBtn.textContent = '- Remove';
      // Create a wrapper div for alignment
      const colDiv = document.createElement('div');
      colDiv.className = 'col-1 mt-4 mr-4';
      colDiv.appendChild(removeBtn);

      clone.appendChild(colDiv);
    }

    container.appendChild(clone);
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
<!-- Customer AJAX typeahead: search on server when typing (no pre-loaded list) -->
<script>
(function() {
  var searchTimeout;
  function displayText(c) { return (c.customer_id || '') + ' - ' + (c.customer_name || ''); }

  function searchCustomers(q, callback) {
    if (!q || q.trim() === '') { callback([]); return; }
    fetch('get_customers_search.php?q=' + encodeURIComponent(q.trim()))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        callback(data.success && data.results ? data.results : []);
      })
      .catch(function() { callback([]); });
  }

  function renderDropdown(dropdownEl, list, onSelect) {
    dropdownEl.innerHTML = '';
    if (list.length === 0) {
      dropdownEl.innerHTML = '<div class="no-result">No matching customer</div>';
    } else {
      list.forEach(function(c) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = displayText(c);
        div.addEventListener('click', function() {
          onSelect(c.customer_id, displayText(c));
        });
        dropdownEl.appendChild(div);
      });
    }
    dropdownEl.classList.add('show');
  }

  function initCustomerTypeahead(wrapper) {
    var input = wrapper.querySelector('.customer-typeahead-input');
    var hidden = wrapper.querySelector('input[name="customer_id"]');
    var dropdown = wrapper.querySelector('.customer-typeahead-dropdown');
    var drawerId = wrapper.getAttribute('data-drawer-id');
    if (!input || !hidden || !dropdown) return;

    function doSearch() {
      var q = input.value.trim();
      searchCustomers(q, function(list) {
        renderDropdown(dropdown, list, function(id, text) {
          hidden.value = id || '';
          input.value = text;
          dropdown.classList.remove('show');
        });
      });
    }

    input.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) {
        hidden.value = '';
        dropdown.classList.remove('show');
        return;
      }
      searchTimeout = setTimeout(doSearch, 300);
    });

    input.addEventListener('focus', function() {
      var q = input.value.trim();
      if (q) doSearch();
    });

    input.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (opts.length === 0) return;
      var active = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!active) { opts[0].classList.add('active'); return; }
        active.classList.remove('active');
        var next = active.nextElementSibling;
        if (next) next.classList.add('active'); else opts[0].classList.add('active');
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!active) { opts[opts.length - 1].classList.add('active'); return; }
        active.classList.remove('active');
        var prev = active.previousElementSibling;
        if (prev) prev.classList.add('active'); else opts[opts.length - 1].classList.add('active');
      } else if (e.key === 'Enter' && active) {
        e.preventDefault();
        active.click();
      }
    });

    document.addEventListener('click', function(e) {
      if (!wrapper.contains(e.target)) dropdown.classList.remove('show');
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.customer-typeahead').forEach(initCustomerTypeahead);
  });
})();
</script>
