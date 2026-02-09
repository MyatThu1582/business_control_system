<?php
  session_start();
  if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
  require '../config/config.php';
  require '../config/common.php';
?>
<?php include 'header.php'; ?>
<?php
    $supplier_id = $_GET['supplier_id'];

    if (empty($_POST['search'])) {
      $payaplestmt = $pdo->prepare("
        SELECT p.*
        FROM payable p
        JOIN (
          SELECT group_id, MAX(id) AS max_id
          FROM payable
          WHERE supplier_id = :supplier_id
          GROUP BY group_id
        ) x ON x.max_id = p.id
        ORDER BY p.id DESC
      ");
      $payaplestmt->execute([':supplier_id' => $supplier_id]);
      $payapledata = $payaplestmt->fetchAll();
    }else{
      $search = $_POST['search'];
      $payaplestmt = $pdo->prepare("
        SELECT p.*
        FROM payable p
        JOIN (
          SELECT group_id, MAX(id) AS max_id
          FROM payable
          WHERE supplier_id = :supplier_id
            AND grn_no LIKE :search
          GROUP BY group_id
        ) x ON x.max_id = p.id
        ORDER BY p.id DESC
      ");
      $payaplestmt->execute([
        ':supplier_id' => $supplier_id,
        ':search' => '%'.$search.'%'
      ]);
      $payapledata = $payaplestmt->fetchAll();
    }

    // Supplier Name
    $supplierIdstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
    $supplierIdstmt->execute();
    $supplierIdResult = $supplierIdstmt->fetch(PDO::FETCH_ASSOC);
 
    // Add Payment
    if(isset($_POST['save'])){
      $date = $_POST['date'];
      $grn_no = $_POST['grn_no'];
      $group_id = $_POST['group_id'];
      $supplier_id = $_POST['supplier_id'];
      $amount = $_POST['amount'];
      $payment_no = $_POST['payment_no'];
      $account_name = $_POST['account_name'];
      
      // Payable Last Balance
      $payabl_balancestmt = $pdo->prepare("SELECT * FROM payable WHERE supplier_id='$supplier_id' AND group_id='$group_id' ORDER BY id DESC");
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
        $payablstmt = $pdo->prepare("
          INSERT INTO payable
            (date, grn_no, supplier_id, amount, paid, balance, purchase_id, asc_id, group_id, status, payment_no, account_name, remark)
          VALUES
            (:date, :grn_no, :supplier_id, 0, :paid, :balance, 0, :asc_id, :group_id, 'paid', :payment_no, :account_name, 'Payment')
        ");
      }else{
        // Update last_row Pending Status
        $pendingstatus_update = $pdo->prepare("UPDATE payable SET status='pending' WHERE supplier_id='$supplier_id' AND id='$last_id'");
        $pendingstatus_update->execute();
        $payablstmt = $pdo->prepare("
          INSERT INTO payable
            (date, grn_no, supplier_id, amount, paid, balance, purchase_id, asc_id, group_id, status, payment_no, account_name, remark)
          VALUES
            (:date, :grn_no, :supplier_id, 0, :paid, :balance, 0, :asc_id, :group_id, 'pending', :payment_no, :account_name, 'Payment')
        ");
      }

      // Add Paid Amount And Asc_id
      $asc_id = $last_asc_id + 1;
      $payabldata = $payablstmt->execute(
        array(
          ':date'=>$date,
          ':grn_no'=>$grn_no,
          ':payment_no'=>$payment_no,
          ':supplier_id'=>$supplier_id,
          ':paid'=>$amount,
          ':asc_id' => $asc_id,
          ':group_id' => $group_id,
          ':account_name' => $account_name,
          ':balance'=>$balance
        )
      );
        echo "<script>window.location.href='account_payable_detail.php?supplier_id=$supplier_id';</script>";
    }
 ?>

<div class="col-md-12 px-4 mt-4">
  <div class="d-flex justify-content-between">
    <div>
      <h4><?php echo $supplierIdResult['supplier_name']; ?>'s Detail Payment</h4>
    </div>
    <div class="d-flex">
      <div class="ml-1 mr-3">
        <form class="" action="" method="post">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search GRN No" name="search">
            <button type="submit" class="input-group-text" id="basic-addon2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
              </svg>
            </button>
          </div>
        </form>
      </div>
      <div class="mt-1">
        <a href="index.php">
          Home
        </a>
        /
        <a href="account_payable.php">
            Payable
        </a>
      </div>
    </div>
  </div>
  <div class="outer">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Date</th>
          <th>GRN No</th>
          <th>Payable Amount</th>
          <th>Paid Amount</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($payapledata) {
            $id = 1;
            foreach ($payapledata as $value) {
              $supplier_id = $value['supplier_id'];

              $grn_no = $value['grn_no'];
              $group_id = $value['group_id'];

              // Total Amount
              $amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM payable WHERE supplier_id = '$supplier_id' AND grn_no = '$grn_no'");
              $amountstmt->execute();
              $total_amountdata = $amountstmt->fetch(PDO::FETCH_ASSOC);
              $total_amount = $total_amountdata['total_amount'];
              
              // Paid Amount
              $paidamountstmt = $pdo->prepare("SELECT SUM(paid) AS total_paid_amount FROM payable WHERE supplier_id = '$supplier_id' AND group_id = '$group_id'");
              $paidamountstmt->execute();
              $paidamountdata = $paidamountstmt->fetch(PDO::FETCH_ASSOC);
              $paidamount = $paidamountdata['total_paid_amount'];
              // echo "<script>alert($paidamounta);</script>";

              // Balance
              $balance = $total_amount - $paidamount;

         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo date('d M Y', strtotime($value['date'])); ?></td>
          <td><?php if(str_contains($value['grn_no'], "PR")){ echo $value['grn_no']; ?><span class="badge badge-primary ms-2">Purchase Return</span><?php }else{ echo $value['grn_no']; } ?></td>
          <td><?php echo number_format($total_amount);?></td>
          <td><?php echo number_format($paidamount);?></td>
          <td><?php echo number_format($balance);?></td>
          <td><span class="badge <?php if($balance == 0){ echo "badge-success"; }else{ echo "badge-primary"; } ?>"><?php if($balance != 0 ){ echo "Pending"; }else{ echo "Paid"; } ?></span></td>
          <td>
              <?php 
                if($balance != 0){
                  ?>
                  <button data-toggle="modal" data-target="#myModal<?php echo $value['id']; ?>"
                    class="btn btn-sm btn-primary text-light"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Add Payment">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash" viewBox="0 0 16 16">
                        <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                        <path d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z"/>
                      </svg>
                  </button>
                  <?php
                } 
              ?>

            <a href="account_payable_detail_per_voucher.php?supplier_id=<?php echo $value['supplier_id'];?>&group_id=<?php echo $value['group_id'] ?>"
              class="btn btn-sm btn-purple text-light"
              data-bs-toggle="tooltip" data-bs-placement="top" title="View Payment History">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                  <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                  <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                  <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                </svg>
            </a>
          </td>
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
                  <input type="hidden" name="group_id" value="<?php echo $value['group_id'];?>">
                  <input type="hidden" name="supplier_id" value="<?php echo $value['supplier_id'];?>">
                    <div class="row mb-2">
                      <div class="col">
                          <label for="">Date</label>
                          <input type="date" class="border border-dark form-control" name="date">
                      </div>
                      <div class="col">
                        <label for="">Payment No</label>
                        <input type="text" class="border border-dark form-control" name="payment_no">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col">
                        <label for="">Amount</label>
                        <input type="number" class="form-control border border-dark" name="amount">
                      </div>
                      <div class="col">
                        <label for="">Account Name</label>
                        <select name="account_name" id="" class="border border-dark form-control">
                          <option value="AYA Bank">AYA Bank</option>
                          <option value="KBZ Bank">KBZ Bank</option>
                          <option value="Cash">Cash</option>
                        </select>
                      </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" data-dismiss="modal" class="btn btn-sm btn-danger">Cancel</button>
                  <button type="submit" class="btn btn-sm btn-purple text-light" name="save">Add Payment</button>
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
<script>
  document.addEventListener("DOMContentLoaded", function(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  });
</script>
  <?php include 'footer.html'; ?>
