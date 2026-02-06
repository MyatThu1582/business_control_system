<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
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
            $filtered_items[] = [
                'item_id'  => $item_id,
                'qty'      => $qty,
                'price'    => $price,
                'discount' => $discounts[$index] ?? 0,
                'foc' => $focs[$index] ?? 0,
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
                    ':percentage' => $item['discount'],
                    ':percentage_amount' => $percentage_amount,
                    ':stock_foc' => $foc,
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
<script>
  // Fetch Customer Name from ID
  function fetchCustomerNameFromId() {
      let customerId = document.getElementById("customer_id").value.trim();

      if (customerId !== "") {
          fetch("get_customer_by_id.php?customer_id=" + encodeURIComponent(customerId))
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  document.getElementById("customer_name").value = data.customer_name;
              } else {
                  document.getElementById("customer_name").value = "";
              }
          })
          .catch(err => console.error("Error fetching customer name:", err));
      } else {
          document.getElementById("customer_name").value = "";
      }
  }

  // Fetch Customer ID from Name
  function fetchCustomerIdFromName() {
      let customerName = document.getElementById("customer_name").value.trim();

      if (customerName !== "") {
          fetch("get_customer_by_name.php?customer_name=" + encodeURIComponent(customerName))
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  document.getElementById("customer_id").value = data.customer_id;
              } else {
                  document.getElementById("customer_id").value = "";
              }
          })
          .catch(err => console.error("Error fetching customer ID:", err));
      } else {
          document.getElementById("customer_id").value = "";
      }
  }

  // Fetch Item Name from ID
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
            priceInput.value = data.selling_price;

            if (parseInt(data.stock_balance) > 0) {
              stockSpan.innerText = data.stock_balance;
              stockSpan.style.color = ""; // keep default color
            } else {
              stockSpan.innerText = "Out of Stock";
              stockSpan.style.color = "red"; // make it red
            }


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

  // Fetch Item ID from Name
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
            priceInput.value = data.selling_price;

            if (parseInt(data.stock_balance) > 0) {
              stockSpan.innerText = data.stock_balance;
              stockSpan.style.color = ""; // keep default color
            } else {
              stockSpan.innerText = "Out of Stock";
              stockSpan.style.color = "red"; // make it red
            }

            
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

            <div class="col-2">
                <label>Customer ID</label>
                <input type="text" id="customer_id" class="form-control" name="customer_id" placeholder="Customer ID"
                        oninput="fetchCustomerNameFromId()">
                </div>
                <div class="col-2">
                <label>Customer Name</label>
                <input type="text" id="customer_name" class="form-control" name="customer_name" placeholder="Customer Name"
                        oninput="fetchCustomerIdFromName()">
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
                  <th>Item Code</th>
                  <th>Item Name</th>
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
                  <td class="no-padding">
                    <input type="text" class="custom-input item_id" name="item_id[]" 
                            oninput="fetchItemNameFromId(this)">
                    </td>
                    <td class="no-padding">
                    <input type="text" class="custom-input item_name" name="item_name[]" 
                            oninput="fetchItemIdFromName(this)">
                    </td>
                    <td class="no-padding">
                    <input type="number" class="custom-input text-right original_price" name="original_price[]">
                  </td>
                  <td class="text-right">
                    <span class="stock_balance"></span>
                  </td>
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

  // Show 5 rows initially
  const firstRow = container.querySelector('.item-row');
  for (let i = 1; i < 6; i++) {
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => input.value = '');
    container.appendChild(clone);
  }

  // Add row button
  addBtn.addEventListener('click', function () {
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => input.value = '');
    container.appendChild(clone);
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
  const customerIdInput = document.getElementById('customer_id');
  const customerNameInput = document.getElementById('customer_name');

  function toggleInputs() {
    const disable = soSelect.value !== "";
    itemRowsContainer.querySelectorAll('input').forEach(input => {
      input.disabled = disable;
      if (disable) input.value = '';
    });
    addRowBtn.disabled = disable;
    customerIdInput.disabled = disable;
    customerNameInput.disabled = disable;
    if (disable) {
      customerIdInput.value = '';
      customerNameInput.value = '';
    }
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

<?php include 'footer.html'; ?> 