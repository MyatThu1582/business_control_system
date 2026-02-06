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

if (isset($_POST['save_btn'])) {
    // Collect main data
    $sale_id      = $_POST['sale_id'] ?? '';
    $date         = trim($_POST['date'] ?? '');
    $gin_no   = trim($_POST['gin_no'] ?? '');
    $grn_no    = trim($_POST['grn_no'] ?? '');
    $return_type  = $_POST['return_type'] ?? '';
    $remark       = $_POST['remark'] ?? '';

    // Dynamic rows
    $item_ids = $_POST['item_id'] ?? [];
    $qtys     = $_POST['qty'] ?? [];
    $prices   = $_POST['original_price'] ?? [];

    // Error holders
    $errors = [];

    // ✅ Validate required static fields
    if (empty($date))       $errors['date'] = 'Return date is required';
    if (empty($grn_no))  $errors['grn_no'] = 'Return number is required';

    // ✅ Collect valid dynamic rows only
    $validRows = [];
    foreach ($item_ids as $i => $item_id) {
        $qty   = $qtys[$i] ?? '';
        $price = $prices[$i] ?? 0;

        if (empty($item_id) && empty($qty)) continue;

        if (empty($item_id)) $errors["item_id_$i"] = "Item ID is required for row ".($i+1);
        if (empty($qty))     $errors["qty_$i"] = "Quantity is required for row ".($i+1);

        if (!empty($item_id) && !empty($qty)) {
            $validRows[] = [
                'item_id' => $item_id,
                'qty'     => $qty,
                'price'   => $price,
            ];
        }
    }

    if (empty($errors)) {
        foreach ($validRows as $row) {
            $item_id = $row['item_id'];
            $qty     = $row['qty'];
            $price   = $row['price'];
            $amount  = $price * $qty;

            $stmt = $pdo->prepare("
                INSERT INTO sale_return
                (date, grn_no, item_id, qty, amount, remark, status, return_type, gin_no)
                VALUES 
                (:date, :grn_no, :item_id, :qty, :amount, :remark, 'pending', :return_type, :gin_no)
            ");
            $stmt->execute([
                ':date'        => $date,
                ':grn_no'   => $grn_no,
                ':item_id'     => $item_id,
                ':qty'         => $qty,
                ':amount'      => $amount,
                ':remark'      => $remark,
                ':return_type' => $return_type,
                ':gin_no'  => $gin_no
            ]);
        }

        // Fetch all pending returns
        $sale_returnstmt = $pdo->prepare("SELECT * FROM sale_return WHERE gin_no='$gin_no' AND status='pending'");
        $sale_returnstmt->execute();
        $sale_returndatas = $sale_returnstmt->fetchAll();

        foreach($sale_returndatas as $sale_returndata){
            $item_id = $sale_returndata['item_id'];
            $qty     = $sale_returndata['qty'];
            $date    = $sale_returndata['date'];
            $amount  = $sale_returndata['amount'];

            // Stock Balance (return adds back stock)
            $stock_balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
            $stock_balancestmt->execute();
            $stock_balancedata = $stock_balancestmt->fetch(PDO::FETCH_ASSOC);

            $oldbalance = !empty($stock_balancedata) ? $stock_balancedata['balance'] : 0;
            $stockbalance = $oldbalance + $qty;

            $stockstmt = $pdo->prepare("
                INSERT INTO stock
                (date, item_id, to_from, in_qty, out_qty, foc_qty, balance, grn_no, gin_no)
                VALUES
                (:date, :item_id, 'sale_return', :in_qty, 0, 0, :balance, :grn_no, :gin_no)
            ");
            $stockstmt->execute([
                ':date'       => $date,
                ':item_id'    => $item_id,
                ':gin_no' => $gin_no,
                ':in_qty'     => $qty,
                ':balance'    => $stockbalance,
                ':grn_no'  => $grn_no
            ]);
        }

        // Reduce Receivable if credit sale
        $credit_checkstmt = $pdo->prepare("SELECT * FROM credit_sale WHERE gin_no='$gin_no' ORDER BY id DESC");
        $credit_checkstmt->execute();
        $credit_checkdata = $credit_checkstmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($credit_checkdata)){
            $customer_id = $credit_checkdata['customer_id'];

            $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM sale_return WHERE gin_no='$gin_no'");
            $total_amountstmt->execute();
            $total_amountresult = $total_amountstmt->fetch(PDO::FETCH_ASSOC);

            $amount = $total_amountresult['total_amount'];

            $receivable_balancestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND gin_no='$gin_no' ORDER BY id DESC");
            $receivable_balancestmt->execute();
            $receivable_balancedata = $receivable_balancestmt->fetch(PDO::FETCH_ASSOC);

            $last_balance = $receivable_balancedata['balance'] ?? 0;
            $last_asc_id = $receivable_balancedata['asc_id'] ?? 0;
            $asc_id = $last_asc_id + 1;
            $balance = $last_balance - $amount;

            $status = ($balance == 0) ? 'paid' : 'pending';
            $receivablstmt = $pdo->prepare("
                INSERT INTO receivable
                  (date, gin_no, customer_id, amount, paid, balance, sale_id, asc_id, group_id, status, payment_no, account_name, remark)
                VALUES
                  (:date, :gin_no, :customer_id, 0, :paid, :balance, 0, :asc_id, :group_id, :status, '', '', 'Sale Return')
            ");
            $receivablstmt->execute([
                ':date'       => $date,
                ':gin_no' => $gin_no,
                ':customer_id'=> $customer_id,
                ':paid'       => $amount,
                ':balance'    => $balance,
                ':asc_id'     => $asc_id,
                ':group_id'   => $gin_no,
                ':status'     => $status
            ]);
        }

        // Update sale_return status
        $updatestmt = $pdo->prepare("UPDATE sale_return SET status='done' WHERE gin_no='$gin_no'");
        $updatestmt->execute();

        echo "<script>
                swal('Success!', 'All selected Sale Returns marked as done!', 'success');
              </script>";

    } else {
        // Assign individual errors
        $dateError      = $errors['date'] ?? '';
        $grn_noError = $errors['grn_no'] ?? '';
        $rowsError      = '';
        foreach ($errors as $key => $err) {
            if (strpos($key, 'item_id_') !== false || strpos($key, 'qty_') !== false) {
                $rowsError .= $err . '<br>';
            }
        }
    }
}

// Fetch temp sale data
$gin_no = $_GET['gin_no'] ?? '';

$temp_salestmt = $pdo->prepare("SELECT * FROM temp_sale WHERE gin_no='$gin_no' ORDER BY id DESC");
$temp_salestmt->execute();
$temp_saleresult = $temp_salestmt->fetch(PDO::FETCH_ASSOC);

$temp_sale_itemstmt = $pdo->prepare("SELECT * FROM temp_sale_items WHERE gin_no='$gin_no' ORDER BY id DESC");
$temp_sale_itemstmt->execute();
$temp_sale_itemresult = $temp_sale_itemstmt->fetchAll();

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
            stockSpan.innerText = "Balance Qty is "+data.stock_balance+" pcs";
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
            stockSpan.innerText = "Balance Qty is "+data.stock_balance+" pcs";
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

    <div class="col-md-12 px-3 pt-1">
  <div class="collapse show">
    <form action="" method="post">
      <input type="hidden" name="sale_id" value="<?php echo $_GET['temp_saleid']; ?>">
      <div class="card">
        <div class="card-header py-2 pb-0 pt-3">
          <h5 class="d-flex align-items-center justify-content-between">
            Add Sale Return
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
                  
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" href="sale_return.php"
                      style="transition: background 0.2s;">
                      <i class="bi bi-arrow-counterclockwise text-primary"></i>
                      <span style="font-size: 13px;">Sale Return</span>
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
          <div class="row">
            <div class="col-6 d-flex">
              <div class="col">
                <label for="">Return Date</label>
                <input type="date" class="form-control" 
                       value="<?php echo date('Y-m-d'); ?>" 
                       name="date">
                <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
              </div>
              <div class="col">
                <label for="">Invoice No</label>
                <input type="text" class="form-control" placeholder="Invoice No" readonly name="gin_no" value="<?php echo $temp_saleresult['gin_no']; ?>">
                <p style="color:red;"><?php echo empty($gin_noError) ? '' : '*'.$gin_noError;?></p>
              </div>
              <div class="col">
                <label for="">Return No</label>
                <?php
                  $sale_returnstmt = $pdo->prepare("SELECT * FROM sale_return ORDER BY id DESC");
                  $sale_returnstmt->execute();
                  $sale_returndata = $sale_returnstmt->fetch(PDO::FETCH_ASSOC);
                  $grn_no = !empty($sale_returndata) ? $sale_returndata['id'] + 1 : "1";
                ?>
                <input type="text" class="form-control" value="SR00<?php echo $grn_no; ?>" readonly placeholder="Return No" name="grn_no">
                <p style="color:red;"><?php echo empty($grn_noError) ? '' : '*'.$grn_noError;?></p>
              </div>
            </div>

            <div class="col-2">
              <label>Return Type</label>
              <select name="return_type" class="form-control">
                <option value="">Select Return Type</option>
                <option value="damaged">Damaged</option>
                <option value="wrong">Wrong Item</option>
                <option value="extra">Extra Quantity</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-4">
              <label>Remark</label>
              <input type="text" class="form-control remark" placeholder="remark" name="remark">
            </div>
          </div>

          <div class="pl-2 pt-3">
            <table class="table table-hover table-bordered">
              <thead class="table-sm" style="background-color: #f4f4f4;">
                <tr>
                    <th>Item id</th>
                    <th>Item Name</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Qty</th>
                    <th colspan="2">Amount</th>
                </tr>
              </thead>
              <tbody id="item-rows">
                <?php
                  if ($temp_sale_itemresult) {
                    $id = 1;
                    foreach ($temp_sale_itemresult as $value) {
                        $gin_no = $value['gin_no'];
                        $item_id = $value['item_id'];
                        $itemIdstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                        $itemIdstmt->execute();
                        $itemIdResult = $itemIdstmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <tr class="item-row" style="font-size: 15px;">
                    <td class="no-padding"> 
                      <input type="text" 
                            value="<?php echo $item_id; ?>" 
                            class="custom-input item_id" 
                            name="item_id[]" 
                            oninput="fetchItemNameFromId(this)">
                    </td>

                    <td class="no-padding">
                      <input type="text" 
                            value="<?php echo $itemIdResult['item_name']; ?>" 
                            class="custom-input item_name" 
                            name="item_name[]" 
                            oninput="fetchItemIdFromName(this)">
                    </td>

                    <td class="no-padding">
                      <input type="number" 
                            value="<?php echo $value['price']; ?>" 
                            class="custom-input text-right original_price" 
                            name="original_price[]">
                    </td>

                    <td class="no-padding">
                      <input type="number" 
                            value="<?php echo $value['qty']; ?>" 
                            class="custom-input text-right qty" 
                            name="qty[]">
                    </td>

                    <td class="no-padding">
                      <input type="number" 
                            value="<?php echo $value['amount']; ?>" 
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
                <?php
                    $id++;
                    }
                  }
                ?>
              </tbody>
            </table>
          </div>
          <div>
              <button type="button" id="add-row-btn" class="btn btn-default text-info btn-sm ml-2">+ Add a new line</button>
          </div>
        </div>

        <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
          <!-- Buttons -->
            <div class="d-flex justify-content-end mt-1">
                <div>
                  <a href="sale.php" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                  <button type="submit" name="save_btn" class="btn btn-purple btn-sm text-light ml-1">Save Sale Return</button>
                </div>
            </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  // Calculate amount on price or qty change
  document.addEventListener('input', function(e) {
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
  // Add/Remove rows dynamically
  document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('item-rows');
    const addBtn = document.getElementById('add-row-btn');

    addBtn.addEventListener('click', function () {
      const firstRow = container.querySelector('.item-row');
      if (!firstRow) return;

      const clone = firstRow.cloneNode(true);

      // Clear input values in cloned row
      clone.querySelectorAll('input').forEach(input => input.value = '');
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
  // Delete temp sale confirmation
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

<?php include 'footer.html'; ?>