<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Legacy page: redirect to the current receivable detail view.
$customer_id = $_GET['customer_id'] ?? '';
header("Location: account_receivable_detail.php?customer_id=" . urlencode($customer_id));
exit;
?>

<div class="col-md-12 px-3 mt-4">
  <div class="d-flex justify-content-between px-2">
    <div>
      <h4>Customer - <?php echo $customer['customer_name']; ?>'s Detail</h4>
    </div>
    <div>
      <a href="index.php">
        Home
      </a>
      /
      <a href="account_receivable.php">
          Receivable
      </a>
    </div>
  </div>
  <div class="outer" style="margin-top:-10px;">
    <table class="table table-bordered mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 10px">No</th>
          <th>Date</th>
          <th>GIN_No</th>
          <th>Amount</th>
          <th>Received Amount</th>
          <th>Balance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if ($receivabledata) {
            $id = 1;
            foreach ($receivabledata as $value) {
              $customer_id = $value['customer_id'];

              $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
              $customerstmt->execute();
              $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);
         ?>
        <tr data-bs-toggle="modal" data-bs-target="#myModal<?php echo $value['id']; ?>">
          <td><?php echo $id; ?></td>
          <td><?php echo $value['date'];?></td>
          <td><?php echo $value['gin_no'];?></td>
          <td><?php echo number_format($value['amount']);?></td>
          <td><?php echo number_format($value['paid']);?></td>
          <td><?php echo number_format($value['balance']);?></td>
          <td><span class="badge <?php if($value['status'] == 'paid'){ echo "badge-success"; }elseif($value['status'] == 'pending'){ echo "badge-primary"; } ?>"><?php echo $value['status'];?></span></td>
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
                      <input type="hidden" name="gin_no" value="<?php echo $value['gin_no'];?>">
                      <input type="hidden" name="customer_id" value="<?php echo $value['customer_id'];?>">
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
