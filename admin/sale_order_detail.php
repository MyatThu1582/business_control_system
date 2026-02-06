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
        if (empty($_POST['item_id']) || empty($_POST['qty']) || empty($_POST['selling_price'])) {
            // Set drawer to open
            $drawerToOpen = $update_id;

            $item_idErrorDrawer = empty($_POST['item_id']) ? 'Item_Id is required' : '';
            $qtyErrorDrawer = empty($_POST['qty']) ? 'Qty is required' : '';
            $priceErrorDrawer = empty($_POST['selling_price']) ? 'Price is required' : '';
        } else {
          // All fields filled, proceed to update
          $update_id = $_POST['update_id']; // hidden field from the drawer form
          $item_id = $_POST['item_id'];
          $qty = $_POST['qty'];
          $price = $_POST['selling_price'];

          $amount = $price * $qty;

          $updatestmt = $pdo->prepare("UPDATE sale_order_items SET item_id=:item_id, qty=:qty, amount=:amount WHERE id=:id");
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
                      swal('Success!', 'Sale Order Item Updated Successfully', 'success');
                    </script>";
          }
      }
  }

  $order_no = $_GET['order_no'];
  $Sale_order_itemstmt = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_no='$order_no' ORDER BY id DESC");
  $Sale_order_itemstmt->execute();
  $Sale_order_itemdatas = $Sale_order_itemstmt->fetchAll();
?>
<script>
  function fetchItemNameFromIdDrawer(id) {
    let itemId = document.getElementById("item_id"+id).value.trim();
    if(itemId!=="") {
      fetch("get_item_by_id.php?item_id="+encodeURIComponent(itemId))
      .then(res=>res.json())
      .then(data=>{
        if(data.success){
          document.getElementById("item_name"+id).value = data.item_name;
          document.getElementById("selling_price"+id).value = data.selling_price;
          document.getElementById("stock_balance"+id).innerText = "Balance Qty is "+data.stock_balance+" pcs";
        } else {
          document.getElementById("item_name"+id).value = "";
          document.getElementById("selling_price"+id).value = "";
          document.getElementById("stock_balance"+id).innerText = ""; 
        }
      });
    }
  }

  function fetchItemIdFromNameDrawer(id) {
    let itemName = document.getElementById("item_name"+id).value.trim();
    if(itemName!=="") {
      fetch("get_item_by_name.php?item_name="+encodeURIComponent(itemName))
      .then(res=>res.json())
      .then(data=>{
        if(data.success){
          document.getElementById("item_id"+id).value = data.item_id;
          document.getElementById("selling_price"+id).value = data.selling_price;
          document.getElementById("stock_balance"+id).innerText = "Balance Qty is "+data.stock_balance+" pcs";
        } else {
          document.getElementById("item_id"+id).value = "";
          document.getElementById("selling_price"+id).value = "";
          document.getElementById("stock_balance"+id).innerText = "";
        }
      });
    }
  }
</script>
  <div class="col-md-12 mt-4 px-3 pt-1">
    <div class="d-flex justify-content-between">
      <h4 class="mb-3">Sale Order Item Details</h4>
      <div>
        <a href="index.php">
          Home
        </a>
        /
        <a href="sale_order.php">
          Sale Order
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
            if ($Sale_order_itemdatas) {
              $idd = 1;
              foreach ($Sale_order_itemdatas as $value) {
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
                  <h5 class="mb-0 fw-bold text-dark">Edit Sale Order</h5>
                  <button type="button" class="btn-close" onclick="closeDrawer(<?php echo $value['id']; ?>)"></button>
                </div>

                <div class="drawer-body p-4">
                  <form action="" method="post">
                    <input type="hidden" name="update_id" value="<?php echo $value['id']; ?>">

                    <!-- Third Row -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Item Id</label>
                        <input type="text" id="item_id<?php echo $value['id']; ?>" class="form-control" name="item_id" value="<?php echo $value['item_id']; ?>" 
                          oninput="fetchItemNameFromIdDrawer(<?php echo $value['id']; ?>)">
                        <span style="color:red;"><?php echo empty($item_idErrorDrawer) ? '' : '*'.$item_idErrorDrawer; ?></span>
                        <small id="stock_balance<?php echo $value['id']; ?>" class="text-success"></small>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Item Name</label>
                        <input type="text" id="item_name<?php echo $value['id']; ?>" class="form-control" 
                          oninput="fetchItemIdFromNameDrawer(<?php echo $value['id']; ?>)">
                      </div>
                    </div>

                    <!-- Fourth Row -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="selling_price" id="selling_price<?php echo $value['id']; ?>" value="<?php echo $value['price']; ?>">
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
              
              <a href="delete.php?table_name=sale_order_item&id=<?php echo $value['id'];?>&order_no=<?php echo $order_no; ?>" class="btn btn-sm delete-order-item">
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
