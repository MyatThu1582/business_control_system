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
      $purchase_id = $_POST['purchase_id'] ?? '';
      $date        = trim($_POST['date'] ?? '');
      $grn_no      = trim($_POST['grn_no'] ?? '');
      $gin_no      = trim($_POST['gin_no'] ?? '');
      $return_type = $_POST['return_type'] ?? '';
      $remark      = $_POST['remark'] ?? '';

      // echo $grn_no;
      // echo $gin_no;
      // exit();
      // Dynamic rows
      $item_ids = $_POST['item_id'] ?? [];
      $qtys     = $_POST['qty'] ?? [];
      $prices   = $_POST['original_price'] ?? [];

      // Error holders
      $errors = [];

      // ✅ Validate required static fields
      if (empty($date))   $errors['date'] = 'Return date is required';
      if (empty($gin_no)) $errors['gin_no'] = 'GIN number is required';

      // ✅ Collect valid dynamic rows only (non-empty ones)
      $validRows = [];
      foreach ($item_ids as $i => $item_id) {
          $qty = $qtys[$i] ?? '';
          $price = $prices[$i] ?? 0;

          // skip empty rows completely
          if (empty($item_id) && empty($qty)) continue;

          // validate only filled rows
          if (empty($item_id)) $errors["item_id_$i"] = "Item ID is required for row ".($i+1);
          if (empty($qty))     $errors["qty_$i"] = "Quantity is required for row ".($i+1);

          // store if valid
          if (!empty($item_id) && !empty($qty)) {
              $validRows[] = [
                  'item_id' => $item_id,
                  'qty'     => $qty,
                  'price'   => $price,
              ];
          }
      }

      // ✅ proceed only if no errors
      if (empty($errors)) {
          foreach ($validRows as $row) {
              $item_id = $row['item_id'];
              $qty     = $row['qty'];
              $price   = $row['price'];
              $amount  = $price * $qty;

              $stmt = $pdo->prepare("
                  INSERT INTO purchase_return 
                  (date, gin_no, item_id, qty, amount, remark, status, return_type, grn_no)
                  VALUES 
                  (:date, :gin_no, :item_id, :qty, :amount, :remark, 'pending', :return_type, :grn_no)
              ");
              $stmt->execute([
                  ':date'        => $date,
                  ':gin_no'      => $gin_no,
                  ':item_id'     => $item_id,
                  ':qty'         => $qty,
                  ':amount'      => $amount,
                  ':remark'      => $remark,
                  ':return_type' => $return_type,
                  ':grn_no'      => $grn_no
              ]);
          }

          // fetch all pending data from purchase return
          $purchase_returnstmt = $pdo->prepare("SELECT * FROM purchase_return WHERE grn_no='$grn_no' AND status='pending'");
          $purchase_returnstmt->execute();
          $purchase_returndatas = $purchase_returnstmt->fetchAll();
          foreach($purchase_returndatas as $purchase_returndata){
            $item_id = $purchase_returndata['item_id'];
            $qty = $purchase_returndata['qty'];
            $date = $purchase_returndata['date'];
            $amount = $purchase_returndata['amount'];

            // Stock Balance
            $stock_balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
            $stock_balancestmt->execute();
            $stock_balancedata = $stock_balancestmt->fetch(PDO::FETCH_ASSOC);
        
            if (!empty($stock_balancedata)) {
              if($stock_balancedata['balance'] < $qty){
                echo "<script>
                        swal('Error', 'Stock is not enough', 'error');
                    </script>";
              }else{
                $stmt = $pdo->prepare("UPDATE purchase_return SET status='received' WHERE grn_no = '$grn_no'");
                $stmt->execute();
              }
              $stockbalance = $stock_balancedata['balance'] - $qty;
            }else{
              $stockbalance = 0 - $qty;
            }
        
            $stockstmt = $pdo->prepare("
              INSERT INTO stock (date, item_id, to_from, in_qty, out_qty, foc_qty, balance, grn_no, gin_no)
              VALUES (:date, :item_id, 'purchase_return', 0, :out_qty, 0, :balance, :grn_no, :gin_no)
            ");
            $stockdata = $stockstmt->execute(
              array(':date'=>$date, ':grn_no'=>$grn_no, ':item_id'=>$item_id, ':out_qty'=>$qty, ':balance'=>$stockbalance, ':gin_no'=>$gin_no)
            );
          }

            // Cash reduce

            // fetch supplier_id
            $cash_checkstmt = $pdo->prepare("SELECT * FROM credit_purchase WHERE grn_no='$grn_no' ORDER BY id DESC");
            $cash_checkstmt->execute();
            $cash_checksdata = $cash_checkstmt->fetch(PDO::FETCH_ASSOC);
            if(!empty($cash_checksdata)){
              $supplier_id = $cash_checksdata['supplier_id'];

              // total amount
              $total_amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM purchase_return WHERE grn_no = '$grn_no'");
              $total_amountstmt->execute();
              $total_amountresult = $total_amountstmt->fetch(PDO::FETCH_ASSOC);

              $amount = $total_amountresult['total_amount'];

              // Payable Last Balance
              $payabl_balancestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' AND grn_no='$grn_no' ORDER BY id DESC");
              $payabl_balancestmt->execute();
              $payabl_balancedata = $payabl_balancestmt->fetch(PDO::FETCH_ASSOC);
              $last_id = $payabl_balancedata['id'];
              $last_asc_id = $payabl_balancedata['asc_id'];
              $last_balance = $payabl_balancedata['balance'];
        
              $balance = $last_balance - $amount;
        
              // Return Voucher Generate
              $asc_id = $last_asc_id + 1;
              // Insert Payable
              $status = ($balance == 0) ? 'paid' : 'pending';
              $payablstmt = $pdo->prepare("
                INSERT INTO payable
                  (date, grn_no, supplier_id, amount, paid, balance, purchase_id, asc_id, group_id, status, payment_no, account_name, remark)
                VALUES
                  (:date, :grn_no, :supplier_id, 0, :paid, :balance, 0, :asc_id, :group_id, :status, '', '', 'Purchase Return')
              ");
              $payabldata = $payablstmt->execute(
                array(
                  ':date'=>$date,
                  ':grn_no'=>$grn_no,
                  ':supplier_id'=>$supplier_id,
                  ':paid'=>$amount,
                  ':asc_id' => $asc_id,
                  ':group_id' => $grn_no,
                  ':status' => $status,
                  ':balance'=>$balance
                )
              );
            }
          
            // update temp purchase status
            $stmt = $pdo->prepare("SELECT * FROM purchase_return WHERE status='received'");
            $stmt->execute();
            $result = $stmt->fetchAll();

            foreach ($result as $value) {
              $id = $value['id'];
              $updatestmt = $pdo->prepare("UPDATE purchase_return SET status='done' WHERE id='$id'");
              $updatestmt->execute();
            }

            echo "<script>
              swal('Success!', 'All selected Purchase Returns marked as done!', 'success');
            </script>";

      } else {
          // assign individual errors for display in form
          $dateError  = $errors['date'] ?? '';
          $gin_noError = $errors['gin_no'] ?? '';
          $rowsError  = '';
          foreach ($errors as $key => $err) {
              if (strpos($key, 'item_id_') !== false || strpos($key, 'qty_') !== false) {
                  $rowsError .= $err . '<br>';
              }
          }
      }
  }

  $grn_no = $_GET['grn_no'];

  $temp_purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE grn_no='$grn_no' ORDER BY id DESC");
  $temp_purchasestmt->execute();
  $temp_purchaseresult = $temp_purchasestmt->fetch(PDO::FETCH_ASSOC);

  $temp_purchase_itemstmt = $pdo->prepare("SELECT * FROM temp_purchase_items WHERE grn_no='$grn_no' ORDER BY id DESC");
  $temp_purchase_itemstmt->execute();
  $temp_purchase_itemresult = $temp_purchase_itemstmt->fetchAll();
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
        <form class="" action="" method="post">
          <input type="hidden" name="purchase_id" value="<?php echo $_GET['temp_purchaseid']; ?>">
          <div class="card">
            <div class="card-header py-3 bg-lightgreen">
              <h5 class="d-flex align-items-center justify-content-between">
                Add Purchase Return
                <div class="d-flex">
                  <!-- <div class="dropdown">
                    <button 
                      class="btn btn-sm btn-primary dropdown-toggle fw-semibold shadow-sm" 
                      type="button" 
                      id="purchaseOptionsDropdown" 
                      data-bs-toggle="dropdown" 
                      aria-expanded="false"
                      style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                      Purchase Options
                    </button>

                    <ul class="dropdown-menu border-0 shadow p-0" aria-labelledby="purchaseOptionsDropdown" 
                        style="border-radius: 3px; overflow: hidden; min-width: 140px;">
                      
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" href="purchase_return.php"
                          style="transition: background 0.2s;">
                          <i class="bi bi-arrow-counterclockwise text-primary"></i>
                          <span style="font-size: 13px;">Purchase Return</span>
                        </a>
                      </li>
                      <hr style="margin: 0px;">
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 delete-temp-purchase" href="delete.php?table_name=temp_purchase&id=<?php echo $_GET['temp_purchaseid']; ?>&grn_no=<?php echo $_GET['grn_no']; ?>" 
                          style="transition: background 0.2s;">
                          <i class="bi bi-trash3 text-danger"></i>
                          <span class="text-danger" style="font-size: 13px;">Delete</span>
                        </a>
                      </li>
                    </ul>
                  </div> -->
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
                    <label for="">GRN_No</label>
                    <input type="text" class="form-control" placeholder="GRN No" readonly name="grn_no" value="<?php echo $temp_purchaseresult['grn_no']; ?>">
                    <p style="color:red;"><?php echo empty($grn_noError) ? '' : '*'.$grn_noError;?></p>
                  </div>
                  <div class="col">
                    <label for="">GIN_No</label>
                    <?php
                      $purchase_returnstmt = $pdo->prepare("SELECT * FROM purchase_return ORDER BY id DESC");
                      $purchase_returnstmt->execute();
                      $purchase_returndata = $purchase_returnstmt->fetch(PDO::FETCH_ASSOC);
                      if(!empty($purchase_returndata)){
                        $return_no = $purchase_returndata['id'] + 1;
                      }else{
                        $return_no = "1";
                      }
                    ?>
                    <input type="text" class="form-control" value="RE00<?php echo $return_no; ?>" readonly placeholder="GIN No" name="gin_no">
                    <p style="color:red;"><?php echo empty($gin_noError) ? '' : '*'.$gin_noError;?></p>
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
                        if ($temp_purchase_itemresult) {
                          $id = 1;
                          foreach ($temp_purchase_itemresult as $value) {
                              $grn_no = $value['grn_no'];
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
                      <a href="purchase.php" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                      <button type="submit" name="save_btn" class="btn btn-purple btn-sm text-light ml-1">Save Purchase</button>
                    </div>
                </div>
            </div>
          </div>
        </form>
      </div>
    </div>
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

<script>
  // Add New Tr
  document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('item-rows');
    const addBtn = document.getElementById('add-row-btn');

    addBtn.addEventListener('click', function () {
        const firstRow = container.querySelector('.item-row');
        if (!firstRow) return;

        const clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input').forEach(input => input.value = '');
        clone.querySelectorAll('.stock_balance').forEach(span => span.innerText = '');
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
  document.querySelectorAll('.delete-temp-purchase').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this Purchase!",
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