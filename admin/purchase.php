<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';

?>
<?php include 'header.php';?>
<?php
  $stmt = $pdo->prepare("
    SELECT grn_no
    FROM temp_purchase
    GROUP BY grn_no
    ORDER BY MAX(id) DESC
  ");
  $stmt->execute();
  $result = $stmt->fetchAll();
?>
    <div class="col-md-12 mt-2 px-3 pt-1">
      <div class="card">
        <div class="card-header py-2 pb-0 pt-3">
          <h5 class="d-flex align-items-center justify-content-between">
            Purchase Bills
            <div class="d-flex">
                <div>
                  <a href="add_purchase.php"
                    class="btn btn-sm btn-primary fw-semibold shadow-sm" 
                    type="button" 
                    style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                    + Add New Purchase
                  </a>
                </div>
              </div>
          </h5>
        </div>
        <div class="card-body">
          <div class="">
            <table class="table table-hover">
              <thead class="custom-thead">
                <tr>
                  <th style="width: 10px">No</th>
                  <th>Date</th>
                  <th>Supplier</th>
                  <th>GRN No</th>
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
                      $grn_no = $value['grn_no'];

                      // purchase data
                      $stmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE grn_no=:grn_no ORDER BY id DESC");
                      $stmt->execute([':grn_no' => $grn_no]);
                      $data = $stmt->fetch(PDO::FETCH_ASSOC);

                      // fetch supplier
                      $supplier_id = $data['supplier_id'];
                      $supplierIdstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id=:supplier_id");
                      $supplierIdstmt->execute([':supplier_id' => $supplier_id]);
                      $supplierIdResult = $supplierIdstmt->fetch(PDO::FETCH_ASSOC);

                      // total amt
                      $total_amtstmt = $pdo->prepare("SELECT SUM(amount) AS total_amt FROM temp_purchase_items WHERE grn_no=:grn_no");
                      $total_amtstmt->execute([':grn_no' => $grn_no]);
                      $total_amtdata = $total_amtstmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <tr style="font-size: 15px; cursor:pointer;" 
                  onclick="window.location='purchase_detail.php?grn_no=<?php echo $data['grn_no']; ?>&temp_purchaseid=<?php echo $data['id']; ?>&status=<?php echo $data['status']; ?>'">
                  <td><?php echo $id; ?></td>
                  <td><?php echo date('d M Y', strtotime($data['date'])); ?></td>
                  <td><?php echo $supplierIdResult['supplier_name'];?></td>
                  <td>
                    <?php echo $data['grn_no'];?>
                  </td>
                  <td><?php echo number_format($total_amtdata['total_amt']); ?></td>
                  <td>
                    <div class="badge <?php if($data['status'] != 'draft'){ echo "badge-primary"; }else{ echo "badge-secondary"; } ?>"><?php echo $data['status'];?></div>
                  </td>
                  <td><?php echo $data['type'];?></td>
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
    </div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      if (sessionStorage.getItem('purchaseUpdated') === 'true') {
          sessionStorage.removeItem('purchaseUpdated'); // clear flag
          swal('Updated!', 'Purchase Updated Successfully', 'success');
      }

      if (sessionStorage.getItem('purchaseApproved') === 'true') {
          sessionStorage.removeItem('purchaseApproved'); // clear flag
          swal('Approved!', 'Purchase Approved Successfully', 'success');
      }
  });
</script>
<?php include 'footer.html'; ?>