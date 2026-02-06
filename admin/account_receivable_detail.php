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
    $customer_id = $_GET['customer_id'];

    if (empty($_POST['search'])) {
      $receivablestmt = $pdo->prepare("
        SELECT r.*
        FROM receivable r
        JOIN (
          SELECT group_id, MAX(id) AS max_id
          FROM receivable
          WHERE customer_id = :customer_id
          GROUP BY group_id
        ) x ON x.max_id = r.id
        ORDER BY r.id DESC
      ");
      $receivablestmt->execute([':customer_id' => $customer_id]);
      $receivabledata = $receivablestmt->fetchAll();
    }else{
      $search = $_POST['search'];
      $receivablestmt = $pdo->prepare("
        SELECT r.*
        FROM receivable r
        JOIN (
          SELECT group_id, MAX(id) AS max_id
          FROM receivable
          WHERE customer_id = :customer_id
            AND gin_no LIKE :search
          GROUP BY group_id
        ) x ON x.max_id = r.id
        ORDER BY r.id DESC
      ");
      $receivablestmt->execute([
        ':customer_id' => $customer_id,
        ':search' => '%'.$search.'%'
      ]);
      $receivabledata = $receivablestmt->fetchAll();
    }

    // Customer Name
    $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
    $customerstmt->execute();
    $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);

    // Add Payment
    if(isset($_POST['save'])){
      $date = $_POST['date'];
      $gin_no = $_POST['gin_no'];
      $group_id = $_POST['group_id'];
      $customer_id = $_POST['customer_id'];
      $amount = $_POST['amount'];
      $payment_no = $_POST['payment_no'];
      $account_name = $_POST['account_name'];

      // Receivable Last Balance
      $receivable_balancestmt = $pdo->prepare("SELECT * FROM receivable WHERE customer_id='$customer_id' AND group_id='$group_id' ORDER BY id DESC");
      $receivable_balancestmt->execute();
      $receivable_balancedata = $receivable_balancestmt->fetch(PDO::FETCH_ASSOC);
      $last_id = $receivable_balancedata['id'];
      $last_asc_id = $receivable_balancedata['asc_id'];
      $last_balance = $receivable_balancedata['balance'];
      
      // Update Row above last_row Paid Status
      $paidstatus_update = $pdo->prepare("UPDATE receivable SET status='paid' WHERE customer_id='$customer_id' AND asc_id<'$last_asc_id'");
      $paidstatus_update->execute();
      
      $balance = $last_balance - $amount;
      
      if($balance == 0){
        // Update last_row Paid Status
        $pendingstatus_update = $pdo->prepare("UPDATE receivable SET status='paid' WHERE customer_id='$customer_id' AND id='$last_id'");
        $pendingstatus_update->execute();        
        $receivable_payment_stmt = $pdo->prepare("
          INSERT INTO receivable
            (date, gin_no, customer_id, amount, paid, balance, sale_id, asc_id, group_id, status, payment_no, account_name, remark)
          VALUES
            (:date, :gin_no, :customer_id, 0, :paid, :balance, 0, :asc_id, :group_id, 'paid', :payment_no, :account_name, 'Payment')
        ");
      }else{
        // Update last_row Pending Status
        $pendingstatus_update = $pdo->prepare("UPDATE receivable SET status='pending' WHERE customer_id='$customer_id' AND id='$last_id'");
        $pendingstatus_update->execute();
        $receivable_payment_stmt = $pdo->prepare("
          INSERT INTO receivable
            (date, gin_no, customer_id, amount, paid, balance, sale_id, asc_id, group_id, status, payment_no, account_name, remark)
          VALUES
            (:date, :gin_no, :customer_id, 0, :paid, :balance, 0, :asc_id, :group_id, 'pending', :payment_no, :account_name, 'Payment')
        ");
      }

      // Add Paid Amount And Asc_id
      $asc_id = $last_asc_id + 1;
      $receivable_payment_data = $receivable_payment_stmt->execute(
        array(
          ':date'=>$date,
          ':gin_no'=>$gin_no,
          ':payment_no'=>$payment_no,
          ':account_name'=>$account_name,
          ':customer_id'=>$customer_id,
          ':paid'=>$amount,
          ':asc_id' => $asc_id,
          ':group_id' => $group_id,
          ':balance'=>$balance
        )
      );
      echo "<script>window.location.href='account_receivable_detail.php?customer_id=$customer_id';</script>";
    }
 ?>

<div class="col-md-12 px-3 mt-4">
  <div class="d-flex justify-content-between px-2">
    <div>
      <h4>Customer - <?php echo $customer['customer_name']; ?>'s Detail</h4>
    </div>
    <div class="d-flex">
      <div class="ml-1 mr-3">
        <form class="" action="" method="post">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search GIN No" name="search">
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
        <a href="account_receivable.php">
            Receivable
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
          <th>GIN_No</th>
          <th>Amount</th>
          <th>Received Amount</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($receivabledata) {
            $id = 1;
            foreach ($receivabledata as $value) {
              $customer_id = $value['customer_id'];

              $gin_no = $value['gin_no'];
              $group_id = $value['group_id'];

              $amountstmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM receivable WHERE customer_id = '$customer_id' AND gin_no = '$gin_no'");
              $amountstmt->execute();
              $total_amountdata = $amountstmt->fetch(PDO::FETCH_ASSOC);
              $total_amount = $total_amountdata['total_amount'];
              
              // Paid Amount
              $paidamountstmt = $pdo->prepare("SELECT SUM(paid) AS total_paid_amount FROM receivable WHERE customer_id = '$customer_id' AND group_id = '$group_id'");
              $paidamountstmt->execute();
              $paidamountdata = $paidamountstmt->fetch(PDO::FETCH_ASSOC);
              $paidamount = $paidamountdata['total_paid_amount'];
              // echo "<script>alert($paidamounta);</script>";

              // Balance
              $balance = $total_amount - $paidamount;
         ?>
        <tr data-bs-toggle="modal" data-bs-target="#myModal<?php echo $value['id']; ?>">
          <td><?php echo $id; ?></td>
          <td><?php echo date('d-m-Y', strtotime($value['date']));?></td>
          <td><?php echo $value['gin_no'];?></td>
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
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Add Received">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash" viewBox="0 0 16 16">
                        <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                        <path d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z"/>
                      </svg>
                  </button>
                  <?php
                } 
              ?>

            <a href="account_receivable_detail_per_voucher.php?customer_id=<?php echo $value['customer_id'];?>&group_id=<?php echo $value['group_id'] ?>"
              class="btn btn-sm btn-purple text-light"
              data-bs-toggle="tooltip" data-bs-placement="top" title="View Received History">
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
                    <h4 class="modal-title">Add Received Amount</h4>
                  </div>
                  <div class="modal-body">
                    <form action="" method="post">
                      <input type="hidden" name="gin_no" value="<?php echo $value['gin_no'];?>">
                      <input type="hidden" name="group_id" value="<?php echo $value['group_id'];?>">
                      <input type="hidden" name="customer_id" value="<?php echo $value['customer_id'];?>">
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
