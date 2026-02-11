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
// Get distinct GIN numbers
$stmt = $pdo->prepare("
  SELECT gin_no
  FROM temp_sale
  GROUP BY gin_no
  ORDER BY MAX(id) DESC
");
$stmt->execute();
$result = $stmt->fetchAll();
?>

<div class="col-md-12 mt-2 px-3 pt-1">
  <div class="card">
    <div class="card-header py-2 pb-0 pt-3">
      <h5 class="d-flex align-items-center justify-content-between">
        Sale Bills
        <div class="d-flex">
          <a href="add_sale.php"
            class="btn btn-sm btn-primary fw-semibold shadow-sm"
            style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
            + Add New Sale
          </a>
        </div>
      </h5>
    </div>

    <div class="card-body">
      <table class="table table-hover">
        <thead class="custom-thead">
          <tr>
            <th style="width:10px">No</th>
            <th>Date</th>
            <th>Customer</th>
            <th>GIN No</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Type</th>
          </tr>
        </thead>

        <tbody>
          <?php
          if ($result) {
            $id = 1;
            foreach ($result as $value) {
              $gin_no = $value['gin_no'];

              // latest sale record for this GIN
              $stmt = $pdo->prepare("SELECT * FROM temp_sale WHERE gin_no=:gin_no ORDER BY id DESC");
              $stmt->execute([':gin_no' => $gin_no]);
              $data = $stmt->fetch(PDO::FETCH_ASSOC);

              // customer
              $customer_id = $data['customer_id'];
              $customerStmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id=:customer_id");
              $customerStmt->execute([':customer_id' => $customer_id]);
              $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

              // total amt
              $total_amtstmt = $pdo->prepare("SELECT SUM(amount) AS total_amt FROM temp_sale_items WHERE gin_no=:gin_no");
              $total_amtstmt->execute([':gin_no' => $gin_no]);
              $total_amtdata = $total_amtstmt->fetch(PDO::FETCH_ASSOC);
          ?>
          <tr style="cursor:pointer; font-size:15px;"
              onclick="window.location='sale_detail.php?gin_no=<?php echo $gin_no; ?>&temp_saleid=<?php echo $data['id']; ?>&status=<?php echo $data['status']; ?>'">
            <td><?php echo $id; ?></td>
            <td><?php echo date('d M Y', strtotime($data['date'])); ?></td>
            <td><?php echo $customer['customer_name'] ?? '-'; ?></td>
            <td><?php echo $gin_no; ?></td>
            <td><?php echo number_format($total_amtdata['total_amt']); ?></td>
            <td>
              <div class="badge <?php if($data['status'] != 'draft'){ echo "badge-primary"; }else{ echo "badge-secondary"; } ?>"><?php echo $data['status'];?></div>
            </td>
            <td><?php echo $data['type']; ?></td>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (sessionStorage.getItem('saleUpdated') === 'true') {
    sessionStorage.removeItem('saleUpdated');
    swal('Updated!', 'Sale Updated Successfully', 'success');
  }

  if (sessionStorage.getItem('saleApproved') === 'true') {
    sessionStorage.removeItem('saleApproved');
    swal('Approved!', 'Sale Approved Successfully', 'success');
  }
});
</script>

<?php include 'footer.html'; ?>
