<?php
  session_start();
  if (empty($_SESSION['user_id'])) {
      header("Location: login.php");
      exit;
  }
  require '../Config/config.php';
  require '../Config/common.php';
  include 'header.php';

  $purchase_returnstmt = $pdo->prepare("SELECT * FROM purchase_return ORDER BY id DESC");
  $purchase_returnstmt->execute();
  $purchase_returndata = $purchase_returnstmt->fetchAll();
?>

<div class="col-md-12 mt-2 px-3 pt-1">
  <div class="card">
    <div class="card-header py-2 pb-0 pt-3">
        <h5 class="d-flex align-items-center justify-content-between">
          Purchase Return Listings
          <div class="d-flex">
              <div>
                <a href="purchase.php"
                  class="btn btn-sm btn-primary fw-semibold shadow-sm" 
                  type="button" 
                  style="background: linear-gradient(135deg, #007bff, #00b4d8); border: none;">
                  + New Purchase Return
                </a>
              </div>
            </div>
        </h5>
      </div>
      <div class="card-body">
        <table class="table table-hover">
          <thead class="custom-thead">
            <tr>
              <th style="width: 10px">No</th>
              <th>Return Date</th>
              <th>GIN No</th>
              <th>Purchase Voucher No</th>
              <th>Remark</th>
              <th>Amount</th>
              <th>Status</th>
              <th style="width: 50px;">View</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if ($purchase_returndata) {
                $id = 1;
                foreach ($purchase_returndata as $value) {
                  $grn_no = $value['grn_no'];
                  
                  // total amt
                  $total_amtstmt = $pdo->prepare("SELECT SUM(amount) AS total_amt FROM purchase_return WHERE grn_no='$grn_no' ORDER BY id DESC");
                  $total_amtstmt->execute();
                  $total_amtdata = $total_amtstmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <tr>
              <td><?php echo $id; ?></td>
              <td><?php echo date('d M Y', strtotime($value['date'])); ?></td>
              <td><?php echo $value['gin_no']; ?></td>
              <td><?php echo $value['grn_no']; ?></td>
              <td><?php echo $value['remark']; ?></td>
              <td><?php echo number_format($value['amount']); ?></td>
              <td>
                <div class="badge <?php if($value['status'] != 'draft'){ echo "badge-primary"; }else{ echo "badge-secondary"; } ?>"><?php echo $value['status'];?></div>
              </td>
              <td class="text-center">
                  <a href="purchase_return_detail.php?grn_no=<?php echo $value['grn_no']; ?>&gin_no=<?php echo $value['gin_no']; ?>" class="link mr-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
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
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      if (sessionStorage.getItem('purchaseReturnEdited') === 'true') {
          sessionStorage.removeItem('purchaseReturnEdited'); // clear flag
          swal('Updated!', 'Purchase Return Updated Successfully', 'success');
      }
  });
</script>
<script>
  // for multiple row
  document.addEventListener('DOMContentLoaded', () => {
    const itemRowsContainer = document.getElementById('item-rows');
    const addRowBtn = document.querySelector('.add-row-btn');

    // Add new row
    addRowBtn.addEventListener('click', () => {
      const firstRow = itemRowsContainer.querySelector('.item-row');
      const newRow = firstRow.cloneNode(true);

      // Clear inputs
      newRow.querySelectorAll('input').forEach(input => input.value = '');

      // Add remove button to new row
      const btnWrapper = document.createElement('div');
      btnWrapper.classList.add('col-1', 'mt-4', 'mr-4');
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-danger remove-row-btn';
      removeBtn.textContent = '- Remove';
      btnWrapper.appendChild(removeBtn);

      newRow.appendChild(btnWrapper);
      itemRowsContainer.appendChild(newRow);
    });

    // Remove row
    itemRowsContainer.addEventListener('click', (e) => {
      if(e.target && e.target.classList.contains('remove-row-btn')) {
        const row = e.target.closest('.item-row');
        row.remove();
      }
    });
  });
</script>
<script>
  // for edit drawer
  function openDrawer(id) {
    document.getElementById("drawer" + id).classList.add("open");
    document.getElementById("drawerBackdrop" + id).classList.add("show");
  }

  function closeDrawer(id) {
    document.getElementById("drawer" + id).classList.remove("open");
    document.getElementById("drawerBackdrop" + id).classList.remove("show");
  }
</script>
<script>
  // for delete 
  document.querySelectorAll('.delete-return').forEach(button => {
      button.addEventListener('click', function(e) {
          e.preventDefault(); // prevent default link
          const href = this.getAttribute('href');

          swal({
              title: "Are you sure?",
              text: "You will not be able to recover this return!",
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