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

if(isset($_POST['edit'])){
  
    $date = $_POST['date'];
    $gin_no = $_POST['gin_no'];
    $customer_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $payment_no = $_POST['payment_no'];
    $account_name = $_POST['account_name'];
    $group_id = $_POST['group_id'];
    $id = $_POST['id'];
    
    // receivable Last Balance
    $payabl_balancestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id='$group_id' AND id < '$id' ORDER BY id DESC");
    $payabl_balancestmt->execute();
    $payabl_balancedata = $payabl_balancestmt->fetch(PDO::FETCH_ASSOC);
    $last_id = $payabl_balancedata['id'];
    $last_asc_id = $payabl_balancedata['asc_id'];
    $last_balance = $payabl_balancedata['balance'];
    // echo "<script>alert('$last_balance');</script>";
    
    $balance = $last_balance - $amount;
    
    if($balance == 0){
      // Update last_row Paid Status
      $pendingstatus_update = $pdo->prepare("UPDATE receivable SET payment_no = '$payment_no', account_name = '$account_name', paid='$amount', balance='$balance', status='paid' WHERE customer_id='$customer_id' AND id='$id'");
      $pendingstatus_update->execute();
    }else{
      // Update last_row Pending Status
      $pendingstatus_update = $pdo->prepare("UPDATE receivable SET payment_no = '$payment_no', account_name = '$account_name', paid='$amount', balance='$balance', status='pending' WHERE customer_id='$customer_id' AND id='$id'");
      $pendingstatus_update->execute();
    }

    // For Update Others row
    // Check How Many Line to update
    $other_rowstmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id='$group_id' AND id > '$id'");
    $other_rowstmt->execute();
    $other_rowdatas = $other_rowstmt->fetchAll();
    $i = 1;
    // print "<pre>";
    // print_r($other_rowdatas);
    foreach ($other_rowdatas as $other_rowdata) {
    // echo "<script>alert('Hello');</script>";

      $id = $other_rowdata['id'];
      $customer_id = $other_rowdata['customer_id'];
      $group_id = $other_rowdata['group_id'];
      $amount = $other_rowdata['amount'];
      $paid = $other_rowdata['paid'];
      $updatea_ascid = $id + $i;

      $balancestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id = '$group_id' AND id<'$id' ORDER BY id DESC");
      $balancestmt->execute();
      $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

      $newbalance = $balancedata['balance'] + $amount - $paid;

      $updateupdate = $pdo->prepare("UPDATE receivable SET balance='$newbalance', asc_id='$updatea_ascid', status='Pending' WHERE id='$id' AND customer_id='$customer_id'");
      $updateupdate->execute();
      $i++;
    }
}

// Delete Row
if(isset($_POST['delete'])){
  
    $gin_no = $_POST['gin_no'];
    $customer_id = $_POST['customer_id'];
    $group_id = $_POST['group_id'];
    $id = $_POST['id'];
    
    // Delete Current row
    $pendingstatus_update = $pdo->prepare("DELETE FROM receivable WHERE id='$id'");
    $pendingstatus_update->execute();

    // For Update Others row
    // Check How Many Line to update
    $other_rowstmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id='$group_id' AND id > '$id'");
    $other_rowstmt->execute();
    $other_rowdatas = $other_rowstmt->fetchAll();
    $i = 1;

    foreach ($other_rowdatas as $other_rowdata) {

      $id = $other_rowdata['id'];
      $customer_id = $other_rowdata['customer_id'];
      $group_id = $other_rowdata['group_id'];
      $amount = $other_rowdata['amount'];
      $paid = $other_rowdata['paid'];
      $updatea_ascid = $id + $i;

      $balancestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id = '$group_id' AND id<'$id' ORDER BY id DESC");
      $balancestmt->execute();
      $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

      $newbalance = $balancedata['balance'] + $amount - $paid;

      $updateupdate = $pdo->prepare("UPDATE receivable SET balance='$newbalance', asc_id='$updatea_ascid', status='Pending' WHERE id='$id' AND customer_id='$customer_id'");
      $updateupdate->execute();
      $i++;
    }
}

if(isset($_POST['update_remark'])) {
  
    $id = $_POST['update_id'];
    $remark = $_POST['remark'];

    $stmt = $pdo->prepare("UPDATE receivable SET remark=:remark WHERE id=:id");
    $stmt->execute([':remark'=>$remark, ':id'=>$id]);

    echo "<script>swal('Success!', 'Remark Updated Successfully', 'success');</script>";
  }
?>

<?php
    $customer_id = $_GET['customer_id'];
    $group_id = $_GET['group_id'];
    $payaplestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id='$group_id'");
    $payaplestmt->execute();
    $payapledata = $payaplestmt->fetchAll();

    // customer Name
    $customerIdstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
    $customerIdstmt->execute();
    $customerIdResult = $customerIdstmt->fetch(PDO::FETCH_ASSOC);
 ?>

<div class="col-md-12 px-4 mt-4">
  <div class="d-flex justify-content-between">
    <div>
      <h4>Received History From Customer - <?php echo $customerIdResult['customer_name']; ?></h4>
    </div>
    <div>
      <?php
      $customer_id = $_GET['customer_id'];
      ?>
      <a href="account_receivable_detail.php?customer_id=<?php echo $customer_id; ?>">
        Back
      </a>
      /
      <a href="account_receivable.php">
          Receivable
      </a>
    </div>
  </div>
  <div class="outer">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">#</th>
          <th>Date</th>
          <th>GRN No</th>
          <th>Payment No</th>
          <th>Account Name</th>
          <th>Amount</th>
          <th>Paid Amount</th>
          <th>Balance</th>
          <th>Remark</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($payapledata) {
            $id = 1;
            foreach ($payapledata as $value) {
              $customer_id = $value['customer_id'];
         ?>
        <tr data-bs-toggle="modal" data-bs-target="#myModal<?php echo $value['id']; ?>">
          <td><?php echo $id; ?></td>
          <td><?php echo $value['date'];?></td>
          <td><?php echo $value['gin_no']; ?></td>
          <td><?php echo $value['payment_no'];?></td>
          <td><?php echo $value['account_name'];?></td>
          <td><?php echo number_format($value['amount']);?></td>
          <td><?php echo number_format($value['paid']);?></td>
          <td><?php echo number_format($value['balance']);?></td>
          <td data-toggle="modal" data-target="#myModal<?php echo $value['id']; ?>" style="cursor: pointer;"><?php echo $value['remark'];?></td>
          <!-- Modal -->
          <div class="modal fade" id="myModal<?php echo $value['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="" method="post">
                  <div class="modal-header">
                    <h5 class="modal-title">Update Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="update_id" value="<?php echo $value['id']; ?>">
                    <textarea name="remark" class="form-control" placeholder="Enter remark"><?php echo $value['remark']; ?></textarea>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="update_remark" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <td class="d-flex">
            <?php
            if($value['gin_no'] == ''){
              ?>
              <!-- First link styled as button with tooltip -->
            <button 
                class="btn btn-sm btn-warning text-light"
                onclick="openDrawer(<?php echo $value['id']; ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
            </button>

            <!-- Drawer (Hidden by default) -->
            <div id="drawer<?php echo $value['id']; ?>" class="drawer shadow-lg">
              <div class="drawer-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Edit Received</h5>
                <button type="button" class="btn-close" onclick="closeDrawer()"></button>
              </div>

              <div class="drawer-body p-4">
                <form action="" method="post">
                  <input type="hidden" name="gin_no" value="<?php echo $value['gin_no'];?>">
                  <input type="hidden" name="group_id" value="<?php echo $value['group_id'];?>">
                  <input type="hidden" name="customer_id" value="<?php echo $value['customer_id'];?>">
                  <input type="hidden" name="id" value="<?php echo $value['id'];?>">

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" value="<?php echo $value['date'];?>" name="date">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Payment No</label>
                    <input type="text" class="form-control" value="<?php echo $value['payment_no'];?>" name="payment_no">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Paid Amount</label>
                    <input type="number" class="form-control" value="<?php echo $value['paid'];?>" name="amount">
                  </div>

                  <div class="mb-4">
                    <label class="form-label fw-semibold">Account Name</label>
                    <select name="account_name" class="form-select form-control">
                      <option value="AYA Bank" <?php if($value['account_name'] == 'AYA Bank'){ echo "selected"; } ?>>AYA Bank</option>
                      <option value="KBZ Bank" <?php if($value['account_name'] == 'KBZ Bank'){ echo "selected"; } ?>>KBZ Bank</option>
                      <option value="Cash" <?php if($value['account_name'] == 'Cash'){ echo "selected"; } ?>>Cash</option>
                    </select>
                  </div>

                  <div class="d-flex justify-content-center gap-2 border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="closeDrawer(<?php echo $value['id']; ?>)">
                      Cancel
                    </button>
                    <button type="submit" name="edit" class="btn btn-purple text-light px-4 shadow-sm ml-2">
                      Save Changes
                    </button>
                  </div>

                </form>
              </div>
            </div>
            <!-- Drawer Backdrop -->
              <div id="drawerBackdrop<?php echo $value['id']; ?>" class="drawer-backdrop <?php echo ($drawerToOpen == $value['id']) ? 'show' : ''; ?>" onclick="closeDrawer(<?php echo $value['id']; ?>)"></div>

            <!-- Second link styled as button with tooltip -->
             <form action="" method="post">
              <input type="hidden" name="gin_no" value="<?php echo $value['gin_no'];?>">
              <input type="hidden" name="group_id" value="<?php echo $value['group_id'];?>">
              <input type="hidden" name="customer_id" value="<?php echo $value['customer_id'];?>">
              <input type="hidden" name="id" value="<?php echo $value['id'];?>">

               <button type="submit"
                 onclick="return confirm('Are you sure you want to delete this record?');" class="btn btn-sm btn-danger text-light ml-2" name="delete">
                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                     <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                     <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                   </svg>
               </button>
             </form>
              <?php
            }
            ?>
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
</div>
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
  <?php include 'footer.html'; ?>
