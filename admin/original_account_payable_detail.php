<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Legacy page: redirect to the current payable detail view.
$supplier_id = $_GET['supplier_id'] ?? '';
header("Location: account_payable_detail.php?supplier_id=" . urlencode($supplier_id));
exit;
?>
</style>


<?php
    $supplier_id = $_GET['supplier_id'];
    $payaplestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' ORDER BY asc_id");
    $payaplestmt->execute();
    $payapledata = $payaplestmt->fetchAll();

    // Supplier Name
    $supplierIdstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
    $supplierIdstmt->execute();
    $supplierIdResult = $supplierIdstmt->fetch(PDO::FETCH_ASSOC);
 
    // Add Payment
    if(isset($_POST['save'])){
      $date = $_POST['date'];
      $grn_no = $_POST['grn_no'];
      $supplier_id = $_POST['supplier_id'];
      $amount = $_POST['amount'];

      // Payable Last Balance
      $payabl_balancestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' AND grn_no='$grn_no'");
      $payabl_balancestmt->execute();
      $payabl_balancedata = $payabl_balancestmt->fetch(PDO::FETCH_ASSOC);
      $last_id = $payabl_balancedata['id'];
      $last_asc_id = $payabl_balancedata['asc_id'];
      $last_balance = $payabl_balancedata['balance'];
  
      // Update Row above last_row Paid Status
      $paidstatus_update = $pdo->prepare("UPDATE payable SET status='paid' WHERE supplier_id='$supplier_id' AND asc_id<'$last_asc_id'");
      $paidstatus_update->execute();

      $balance = $last_balance - $amount;
      
      if($balance == 0){
        // Update last_row Paid Status
        $pendingstatus_update = $pdo->prepare("UPDATE payable SET status='paid' WHERE supplier_id='$supplier_id' AND id='$last_id'");
        $pendingstatus_update->execute();        
        $payablstmt = $pdo->prepare("INSERT INTO payable (date,grn_no,supplier_id,paid,balance,asc_id,group_id,status) VALUES (:date,:paymentgrn_no,:supplier_id,:paid,:balance,:asc_id,:group_id,'paid')");
      }else{
        // Update last_row Pending Status
        $pendingstatus_update = $pdo->prepare("UPDATE payable SET status='pending' WHERE supplier_id='$supplier_id' AND id='$last_id'");
        $pendingstatus_update->execute();
        $payablstmt = $pdo->prepare("INSERT INTO payable (date,grn_no,supplier_id,paid,balance,asc_id,group_id,status) VALUES (:date,:paymentgrn_no,:supplier_id,:paid,:balance,:asc_id,:group_id,'pending')");
      }

      // Add Paid Amount And Asc_id
      $paymentgrn_no =  52 . rand(0,999999);
      $asc_id = $last_asc_id + 1;
      $payabldata = $payablstmt->execute(
        array(':date'=>$date, ':paymentgrn_no'=>$paymentgrn_no, ':supplier_id'=>$supplier_id, ':paid'=>$amount, ':asc_id' => $asc_id, ':group_id' => $grn_no, ':balance'=>$balance)
      );

      // Current Id
      $current_idstmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' ORDER BY id DESC");
      $current_idstmt->execute();
      $current_iddata = $current_idstmt->fetch(PDO::FETCH_ASSOC);
      $current_id = $current_iddata['id'];
      $current_ascid = $current_iddata['asc_id'];
      $current_balance = $current_iddata['balance'];

      // For Update Others row
      // Check How Many Line to update
      $other_rowstmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' AND id!='$current_id' AND asc_id!='$last_asc_id' AND asc_id>$last_asc_id");
      $other_rowstmt->execute();
      $other_rowdatas = $other_rowstmt->fetchAll();
      $i = 1;
      // print "<pre>";
      // print_r($other_rowdatas);
      foreach ($other_rowdatas as $other_rowdata) {
      // echo "<script>alert('Hello');</script>";

        $id = $other_rowdata['id'];
        $supplier_id = $other_rowdata['supplier_id'];
        $amount = $other_rowdata['amount'];
        $paid = $other_rowdata['paid'];
        $updatea_ascid = $current_ascid + $i;

        if($i == 1){
            $newbalance = $current_balance + $amount - $paid;
        }else{
            $balancestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' AND id<'$id' ORDER BY id DESC");
            $balancestmt->execute();
            $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

            $newbalance = $balancedata['balance'] + $amount - $paid;
        }

        $updateupdate = $pdo->prepare("UPDATE payable SET balance='$newbalance', asc_id='$updatea_ascid', status='Pending' WHERE id='$id' AND supplier_id='$supplier_id'");
        $updateupdate->execute();
        $i++;
      }
        echo "<script>window.location.href='account_payable_detail.php?supplier_id=$supplier_id';</script>";
    }
 ?>

<div class="col-md-12 px-4 mt-4">
  <div class="d-flex justify-content-between">
    <div>
      <h4>Supplier - <?php echo $supplierIdResult['supplier_name']; ?>'s Detail</h4>
    </div>
    <div>
      <a href="index.php">
        Home
      </a>
      /
      <a href="account_payable.php">
          Payable
      </a>
    </div>
  </div>
  <div class="" style="margin-top:-10px;">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Date</th>
          <th>GRN No</th>
          <th>Amount</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($payapledata) {
            $id = 1;
            foreach ($payapledata as $value) {
              $supplier_id = $value['supplier_id'];
         ?>
        <tr data-bs-toggle="modal" data-bs-target="#myModal<?php echo $value['id']; ?>">
          <td><?php echo $id; ?></td>
          <td><?php echo $value['date'];?></td>
          <td><?php if(str_contains($value['grn_no'], "PR")){ echo $value['grn_no']; ?><span class="badge badge-primary ms-2">Purchase Return</span><?php }else{ echo $value['grn_no']; } ?></td>
          <td><?php echo $value['amount'];?></td>
          <td><?php echo $value['paid'];?></td>
          <td><?php echo $value['balance'];?></td>
          <td><span class="badge <?php if($value['status'] == 'paid'){ echo "badge-success"; }else{ echo "badge-primary"; } ?>"><?php echo $value['status'];?></span></td>
        </tr>
        <!-- modal -->
        <div id="myModal<?php echo $value['id']; ?>" class="modal fade" role="dialog">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Add Paid Amount</h4>
              </div>
              <div class="modal-body">
                <form action="" method="post">
                  <input type="hidden" name="grn_no" value="<?php echo $value['grn_no'];?>">
                  <input type="hidden" name="supplier_id" value="<?php echo $value['supplier_id'];?>">
                    <div class="row mb-2">
                      <div class="col">
                        <label for="">Date</label>
                        <input type="date" class="border border-dark form-control" name="date">
                    </div>
                    <div class="col">
                      <label for="">Amount</label>
                      <input type="number" class="form-control border border-dark" name="amount">
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="save">Save</button>
                  <button type="button" data-bs-dismiss="modal">Close</button>
                </div>
              </form>
            </div>

          </div>
        </div>
        <?php
          $id++;
            }
          }
         ?>
      </tbody>
    </table>
  </div>
</div>
  <?php include 'footer.html'; ?>
