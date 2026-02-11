<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
include 'header.php';
  
  $supplierstmt = $pdo->prepare("SELECT DISTINCT supplier_id FROM payable");
  $supplierstmt->execute();
  $supplierdata = $supplierstmt->fetchAll();
 ?>
<div class="col-md-12 px-3 mt-4">
  <div class="d-flex">
    <h4 class="col-10 me-5">Payable Listings</h4>
  </div>
  <div class="outer">
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Supplier Name</th>
          <th>Payable Amount</th>
          <th>Paid Amount</th>
          <th>Balance</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($supplierdata) {
            $id = 1;
            foreach ($supplierdata as $value) {
              $supplier_id = $value['supplier_id'];

              // Supplier Name
              $supplierIdstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
              $supplierIdstmt->execute();
              $supplierIdResult = $supplierIdstmt->fetch(PDO::FETCH_ASSOC);

              // Total Receivable Amount
              $total_amtstmt = $pdo->prepare("SELECT SUM(amount) AS total_amt FROM payable WHERE supplier_id='$supplier_id'");
              $total_amtstmt->execute();
              $total_amtdata = $total_amtstmt->fetch(PDO::FETCH_ASSOC);
              
              // Total Paid Amount
              $total_paidstmt = $pdo->prepare("SELECT SUM(paid) AS total_paid FROM payable WHERE supplier_id='$supplier_id'");
              $total_paidstmt->execute();
              $total_paiddata = $total_paidstmt->fetch(PDO::FETCH_ASSOC);

              $balance = $total_amtdata['total_amt'] - $total_paiddata['total_paid'];
         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo $supplierIdResult['supplier_name'];?></td>
          <td><?php echo number_format($total_amtdata['total_amt']);?></td>
          <td><?php echo number_format($total_paiddata['total_paid']);?></td>
          <td><?php echo number_format($balance);?></td>
          <td>
            <!-- First link styled as button with tooltip -->
            <a href="account_payable_detail.php?supplier_id=<?php echo $value['supplier_id'];?>"
              class="btn btn-sm btn-primary text-light"
              data-bs-toggle="tooltip" data-bs-placement="top" title="View Payable Details">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                  <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                  <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                </svg>
            </a>

            <!-- Second link styled as button with tooltip -->
            <a href="account_payable_detail.php?supplier_id=<?php echo $value['supplier_id'];?>"
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
