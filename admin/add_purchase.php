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
              $filtered_items[] = [
                  'item_id' => $item_id,
                  'qty' => $qty,
                  'price' => $price,
                  'discount' => $discounts[$index] ?? 0,
                  'foc' => $focs[$index] ?? 0,
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
                      ':percentage' => $discount,
                      ':percentage_amount' => $percentage_amount,
                      ':stock_foc' => $foc,
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
    
<script>
  function fetchSupplierNameFromId() {
      let supplierId = document.getElementById("supplier_id").value.trim();

      if (supplierId !== "") {
          fetch("get_supplier_by_id.php?supplier_id=" + encodeURIComponent(supplierId))
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  document.getElementById("supplier_name").value = data.supplier_name;
              } else {
                  document.getElementById("supplier_name").value = "";
              }
          })
          .catch(err => console.error("Error fetching supplier name:", err));
      } else {
          document.getElementById("supplier_name").value = "";
      }
  }

  function fetchSupplierIdFromName() {
      let supplierName = document.getElementById("supplier_name").value.trim();

      if (supplierName !== "") {
          fetch("get_supplier_by_name.php?supplier_name=" + encodeURIComponent(supplierName))
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  document.getElementById("supplier_id").value = data.supplier_id;
              } else {
                  document.getElementById("supplier_id").value = "";
              }
          })
          .catch(err => console.error("Error fetching supplier id:", err));
      } else {
          document.getElementById("supplier_id").value = "";
      }
  }

  function fetchItemNameFromId(input) {
    const row = input.closest('.item-row');
    const itemId = input.value.trim();
    const itemNameInput = row.querySelector('.item_name');
    const priceInput = row.querySelector('.original_price');
    const stockSpan = row.querySelector('.stock_balance');

    if(itemId!=="") {
      fetch("get_item_by_id.php?item_id="+encodeURIComponent(itemId))
        .then(res => res.json())
        .then(data => {
          if(data.success){
            itemNameInput.value = data.item_name;
            priceInput.value = data.original_price;
            stockSpan.innerText = data.stock_balance;
          } else {
            itemNameInput.value = "";
            priceInput.value = "";
            stockSpan.innerText = "";
          }
        });
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
    const priceInput = row.querySelector('.original_price');
    const stockSpan = row.querySelector('.stock_balance');

    if(itemName!=="") {
      fetch("get_item_by_name.php?item_name="+encodeURIComponent(itemName))
        .then(res => res.json())
        .then(data => {
          if(data.success){
            itemIdInput.value = data.item_id;
            priceInput.value = data.original_price;
            stockSpan.innerText = data.stock_balance;
          } else {
            itemIdInput.value = "";
            priceInput.value = "";
            stockSpan.innerText = "";
          }
        });
    } else {
      itemIdInput.value = "";
      priceInput.value = "";
      stockSpan.innerText = "";
    }
  }

</script>
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
              
                <div class="col-2">
                  <label for="">Supplier_Id</label>
                  <input type="text" id="supplier_id" oninput="fetchSupplierNameFromId()" class="form-control" placeholder="Supplier_Id" name="supplier_id" >
                  <p style="color:red;"><?php echo empty($supplier_idError) ? '' : '*'.$supplier_idError;?></p>
                </div>
                <div class="col-2">
                  <label for="">Supplier_Name</label>
                  <input type="text" id="supplier_name" class="form-control" placeholder="Supplier_Name" name="supplier_name" oninput="fetchSupplierIdFromName()">
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
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th class="text-right">Discount %</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Foc</th>
                        <th colspan="2">Amount</th>
                    </tr>
                    </thead>
                    <tbody id="item-rows">
                      <tr class="item-row" style="font-size: 15px;">
                          <td class="no-padding"> 
                            <input type="text" 
                                  value="" 
                                  class="custom-input item_id" 
                                  name="item_id[]" 
                                  oninput="fetchItemNameFromId(this)">
                          </td>

                          <td class="no-padding">
                            <input type="text" 
                                  value="" 
                                  class="custom-input item_name" 
                                  name="item_name[]" 
                                  oninput="fetchItemIdFromName(this)">
                          </td>

                          <td class="no-padding">
                            <input type="number" 
                                  value="" 
                                  class="custom-input text-right original_price" 
                                  name="original_price[]">
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

          // Clear all input values in the cloned row
          clone.querySelectorAll('input').forEach(input => input.value = '');
          clone.querySelectorAll('.stock_balance').forEach(span => span.innerText = '');

          // Add remove button if it doesn't exist
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
// to disable input when select po_no
document.addEventListener('DOMContentLoaded', function () {
  const poSelect = document.getElementById('po_no');
  const itemRowsContainer = document.getElementById('item-rows');
  const addRowBtn = document.getElementById('add-row-btn');
  const supplierIdInput = document.getElementById('supplier_id');
  const supplierNameInput = document.getElementById('supplier_name');

  function toggleInputs() {
    const disable = poSelect.value !== ""; // Disable if PO selected

    // Disable item row inputs
    itemRowsContainer.querySelectorAll('input').forEach(input => {
      input.disabled = disable;
      if (disable) input.value = "";
    });

    // Disable add row button
    addRowBtn.disabled = disable;

    // Disable supplier inputs
    supplierIdInput.disabled = disable;
    supplierNameInput.disabled = disable;

    if (disable) {
      supplierIdInput.value = "";
      supplierNameInput.value = "";
    }
  }

  poSelect.addEventListener('change', toggleInputs);
  toggleInputs(); // run on page load
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