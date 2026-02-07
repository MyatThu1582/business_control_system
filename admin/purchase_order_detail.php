<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

$drawerToOpen = null;

  if (isset($_POST['update_btn'])) {
        $update_id = $_POST['update_id'];
        
        // Check empty fields
        if (empty($_POST['item_id']) || empty($_POST['qty']) || empty($_POST['original_price'])) {
            // Set drawer to open
            $drawerToOpen = $update_id;

            $item_idErrorDrawer = empty($_POST['item_id']) ? 'Item_Id is required' : '';
            $qtyErrorDrawer = empty($_POST['qty']) ? 'Qty is required' : '';
            $priceErrorDrawer = empty($_POST['original_price']) ? 'Price is required' : '';
        } else {
          // All fields filled, proceed to update
          $update_id = $_POST['update_id']; // hidden field from the drawer form
          $item_id = $_POST['item_id'];
          $qty = $_POST['qty'];
          $price = $_POST['original_price'];

          $amount = $price * $qty;

          $updatestmt = $pdo->prepare("UPDATE purchase_order_items SET item_id=:item_id, qty=:qty, amount=:amount WHERE id=:id");
          $updateResult = $updatestmt->execute(
              array(
                  ':item_id' => $item_id,
                  ':qty' => $qty,
                  ':amount' => $amount,
                  ':id' => $update_id
              )
          );

          if ($updateResult) {
              echo "<script>
                      swal('Success!', 'Purchase Order Item Updated Successfully', 'success');
                    </script>";
          }
      }
  }

  $order_no = $_GET['order_no'];
  $purchase_order_itemstmt = $pdo->prepare("SELECT * FROM purchase_order_items WHERE order_no='$order_no' ORDER BY id DESC");
  $purchase_order_itemstmt->execute();
  $purchase_order_itemdatas = $purchase_order_itemstmt->fetchAll();

  // Load all items for typeahead (edit drawer)
  $itemListStmt = $pdo->query("SELECT item_id, item_name, original_price FROM item ORDER BY item_name");
  $itemsList = $itemListStmt ? $itemListStmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<style>
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
<script>
var itemsList = <?php echo json_encode($itemsList); ?>;
</script>
  <div class="col-md-12 mt-4 px-3 pt-1">
    <div class="d-flex justify-content-between">
      <h4 class="mb-3">Purchase Order Item Details</h4>
      <div>
        <a href="index.php">
          Home
        </a>
        /
        <a href="purchase_order.php">
          Purchase Order
        </a>
      </div>
    </div>
    <div>
      <table class="table table-hover">
        <thead class="custom-thead">
          <tr>
            <th style="width: 10px">No</th>
            <th>Item Name</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total Amount</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if ($purchase_order_itemdatas) {
              $idd = 1;
              foreach ($purchase_order_itemdatas as $value) {
                $id = $value['id'];
                $item_id = $value['item_id'];

              //   Item Name
                $itemIdstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                $itemIdstmt->execute();
                $itemIdResult = $itemIdstmt->fetch(PDO::FETCH_ASSOC);
          ?>
          <tr>
            <td><?php echo $idd; ?></td>
            <td><?php echo $itemIdResult['item_name']; ?></td>
            <td><?php echo number_format($value['price']); ?></td>
            <td><?php echo $value['qty']; ?></td>
            <td><?php echo number_format($value['amount']); ?></td>
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

                    <!-- Third Row -->
                    <div class="row mb-3">
                      <div class="col-md-12">
                        <label class="form-label">Item</label>
                        <div class="item-typeahead" data-drawer-id="<?php echo $value['id']; ?>">
                          <input type="text" class="form-control item-typeahead-input" placeholder="Type item code or name..." value="<?php echo htmlspecialchars($value['item_id'] . ' - ' . ($itemIdResult['item_name'] ?? '')); ?>" autocomplete="off">
                          <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($value['item_id']); ?>">
                          <div class="item-typeahead-dropdown"></div>
                        </div>
                        <span style="color:red;"><?php echo empty($item_idErrorDrawer) ? '' : '*'.$item_idErrorDrawer; ?></span>
                        <small id="stock_balance<?php echo $value['id']; ?>" class="text-success d-block"></small>
                      </div>
                    </div>

                    <!-- Fourth Row -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="original_price" id="original_price<?php echo $value['id']; ?>" value="<?php echo $value['price']; ?>">
                        <span style="color:red;"><?php echo empty($priceErrorDrawer) ? '' : '*'.$priceErrorDrawer; ?></span>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" name="qty" id="qty<?php echo $value['id']; ?>" value="<?php echo $value['qty']; ?>">
                        <span style="color:red;"><?php echo empty($qtyErrorDrawer) ? '' : '*'.$qtyErrorDrawer; ?></span>
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
              
              <a href="delete.php?table_name=purchase_order_item&id=<?php echo $value['id'];?>&order_no=<?php echo $order_no; ?>" class="btn btn-sm delete-order-item">
                                          <i class="fas fa-trash"></i>
              </a>
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
  document.querySelectorAll('.delete-order-item').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this order item!",
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
<!-- Item typeahead for detail drawer -->
<script>
(function() {
  if (typeof itemsList === 'undefined') itemsList = [];
  function itemDisplayText(it) { return (it.item_id || '') + ' - ' + (it.item_name || ''); }
  function filterItems(q) {
    q = (q || '').trim().toLowerCase();
    if (!q) return itemsList.slice(0, 50);
    return itemsList.filter(function(it) {
      var text = itemDisplayText(it).toLowerCase();
      return text === q ||
             (it.item_id && it.item_id.toString().toLowerCase().indexOf(q) !== -1) ||
             (it.item_name && it.item_name.toLowerCase().indexOf(q) !== -1);
    }).slice(0, 50);
  }
  function renderItemDropdown(dropdownEl, list, onSelect) {
    dropdownEl.innerHTML = '';
    if (list.length === 0) {
      dropdownEl.innerHTML = '<div class="no-result">No matching item</div>';
    } else {
      list.forEach(function(it) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = itemDisplayText(it);
        div.addEventListener('click', function() { onSelect(it); });
        dropdownEl.appendChild(div);
      });
    }
    dropdownEl.classList.add('show');
  }
  function initItemTypeahead(wrapper) {
    if (!wrapper) return;
    var drawerId = wrapper.getAttribute('data-drawer-id');
    var input = wrapper.querySelector('.item-typeahead-input');
    var hiddenId = wrapper.querySelector('input[name="item_id"]');
    var dropdown = wrapper.querySelector('.item-typeahead-dropdown');
    var priceInput = drawerId ? document.getElementById('original_price' + drawerId) : null;
    var stockSpan = drawerId ? document.getElementById('stock_balance' + drawerId) : null;
    if (!input || !hiddenId || !dropdown) return;
    function onSelectItem(it) {
      hiddenId.value = it.item_id || '';
      input.value = itemDisplayText(it);
      if (priceInput) priceInput.value = it.original_price != null ? it.original_price : '';
      dropdown.classList.remove('show');
      if (stockSpan) {
        stockSpan.innerText = '';
        fetch('get_item_by_id.php?item_id=' + encodeURIComponent(it.item_id))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.success && stockSpan) stockSpan.innerText = 'Balance Qty is ' + (data.stock_balance || 0) + ' pcs';
          })
          .catch(function() {});
      }
    }
    input.addEventListener('input', function() {
      var q = input.value.trim();
      if (!q) {
        hiddenId.value = '';
        if (priceInput) priceInput.value = '';
        if (stockSpan) stockSpan.innerText = '';
        dropdown.classList.remove('show');
        return;
      }
      renderItemDropdown(dropdown, filterItems(q), onSelectItem);
    });
    input.addEventListener('focus', function() {
      renderItemDropdown(dropdown, filterItems(input.value.trim()), onSelectItem);
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
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
  });
})();
</script>
