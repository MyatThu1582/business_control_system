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

$errors = [];

  if (isset($_POST['edit_btn'])) {
      $date = trim($_POST['date']);
      $gin_no = trim($_POST['gin_no']);
      $remark = trim($_POST['remark']);
      $return_type = trim($_POST['return_type']);
      $grn_no = trim($_POST['grn_no']);

      $item_ids = $_POST['item_id'];
      $qtys = $_POST['qty'];
      $amounts = $_POST['amount'];

      // --- validation ---
      if (empty($date)) {
          $errors['date'] = 'Date is required';
      }
      if (empty($gin_no)) {
          $errors['gin_no'] = 'GIN No is required';
      }

      // dynamic input check (only for non-empty rows)
      $valid_rows = [];
      for ($i = 0; $i < count($item_ids); $i++) {
          if (!empty($item_ids[$i]) && !empty($qtys[$i]) && !empty($amounts[$i])) {
              $valid_rows[] = [
                  'item_id' => $item_ids[$i],
                  'qty' => $qtys[$i],
                  'amount' => $amounts[$i],
              ];
          }
      }

      if (empty($valid_rows)) {
          $errors['items'] = 'Please fill at least one item row completely';
      }

      // --- if no error, update ---
      if (empty($errors)) {
          // delete old items of that gin_no
          $delete = $pdo->prepare("DELETE FROM purchase_return WHERE gin_no=?");
          $delete->execute([$gin_no]);

          // insert updated items
          $stmt = $pdo->prepare("INSERT INTO purchase_return (date, gin_no, item_id, qty, amount, remark, status, return_type, grn_no) VALUES (?, ?, ?, ?, ?, ?, 'done', ?, ?)");
          foreach ($valid_rows as $row) {
              $stmt->execute([$date, $gin_no, $row['item_id'], $row['qty'], $row['amount'], $remark, $return_type, $grn_no]);
          }

          echo "<script>sessionStorage.setItem('purchaseReturnEdited', 'true');
                    window.location.href = 'purchase_return.php';
                </script>";
      }
  }

  $grn_no = $_GET['grn_no'];
  $gin_no = $_GET['gin_no'];

  $purchase_returnstmt = $pdo->prepare("SELECT * FROM purchase_return WHERE gin_no='$gin_no' ORDER BY id DESC");
  $purchase_returnstmt->execute();
  $purchase_returnresult = $purchase_returnstmt->fetchAll();
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
          <div class="card">
            <div class="card-header py-2 pb-0 pt-3">
              <h5 class="d-flex align-items-center justify-content-between">
                Edit Purchase Return - <?php echo $_GET['gin_no']; ?>
                <div class="d-flex">
                  <div class="dropdown">
                    <button 
                      class="btn btn-sm btn-primary dropdown-toggle fw-semibold shadow-sm" 
                      type="button" 
                      id="purchaseOptionsDropdown" 
                      data-bs-toggle="dropdown" 
                      aria-expanded="false"
                      style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                      Return Options
                    </button>

                    <ul class="dropdown-menu border-0 shadow p-0" aria-labelledby="purchaseOptionsDropdown" 
                        style="border-radius: 3px; overflow: hidden; min-width: 140px;">
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 delete-purchase-return" href="delete.php?table_name=purchase_return&grn_no=<?php echo $_GET['grn_no']; ?>&gin_no=<?php echo $_GET['gin_no']; ?>" 
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
                    <input type="date" class="form-control" placeholder="Date" name="date" value="<?php echo $purchase_returnresult[0]['date']; ?>">
                    <p style="color:red;"><?php echo empty($dateError) ? '' : '*'.$dateError;?></p>
                  </div>
                  <div class="col">
                    <label for="">GRN_No</label>
                    <input type="text" class="form-control" placeholder="GRN No" readonly name="grn_no" value="<?php echo $grn_no; ?>">
                    <p style="color:red;"><?php echo empty($grn_noError) ? '' : '*'.$grn_noError;?></p>
                  </div>
                  <div class="col">
                    <label for="">GIN_No</label>
                    <input type="text" class="form-control" placeholder="GIN No" name="gin_no" value="<?php echo $gin_no; ?>">
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
                  <input type="text" class="form-control remark" placeholder="remark" name="remark"  value="<?php echo $purchase_returnresult[0]['remark']; ?>">
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
                        if ($purchase_returnresult) {
                          $id = 1;
                          foreach ($purchase_returnresult as $value) {
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
                                  value="<?php echo $itemIdResult['original_price']; ?>" 
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
                  <button type="button" id="add-row-btn" disabled class="btn btn-default text-info btn-sm ml-2">+ Add a new line</button>
              </div>
            </div>

            <div class="card-footer" style="border-top: 1px solid lightgrey; background-color: white;">
              <!-- Buttons -->
                <div class="d-flex justify-content-end mt-1">
                    <div>
                      <a href="purchase_return.php" class="btn btn-secondary btn-sm text-light">Cancel</a>    
                      <button type="submit" name="edit_btn" class="btn btn-purple btn-sm text-light ml-1">Update Return</button>
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
  document.querySelectorAll('.delete-purchase-return').forEach(button => {
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