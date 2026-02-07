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
$errors = [];

if (isset($_POST['edit_btn'])) {

    $date        = trim($_POST['date']);
    $gin_no  = trim($_POST['gin_no']);
    $remark      = trim($_POST['remark']);
    $return_type = trim($_POST['return_type']);
    $grn_no      = trim($_POST['grn_no']);

    $item_ids = $_POST['item_id'];
    $qtys     = $_POST['qty'];
    $amounts  = $_POST['amount'];

    if (empty($date)) {
        $errors['date'] = 'Date is required';
    }

    if (empty($gin_no)) {
        $errors['gin_no'] = 'Sale VR No is required';
    }

    $valid_rows = [];
    for ($i = 0; $i < count($item_ids); $i++) {
        if (!empty($item_ids[$i]) && !empty($qtys[$i]) && !empty($amounts[$i])) {
            $valid_rows[] = [
                'item_id' => $item_ids[$i],
                'qty'     => $qtys[$i],
                'amount'  => $amounts[$i],
            ];
        }
    }

    if (empty($valid_rows)) {
        $errors['items'] = 'Please fill at least one item row completely';
    }

    if (empty($errors)) {

        $delete = $pdo->prepare("DELETE FROM sale_return WHERE gin_no=?");
        // $delete->execute([$gin_no]);

        $stmt = $pdo->prepare("
            INSERT INTO sale_return 
            (date, gin_no, item_id, qty, amount, remark, status, return_type, grn_no)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");

        foreach ($valid_rows as $row) {
            // $stmt->execute([$date, $gin_no, $row['item_id'], $row['qty'], $row['amount'], $remark, $return_type, $grn_no]);
        }

        echo "<script>
            sessionStorage.setItem('saleReturnEdited','true');
            window.location.href='sale_return.php';
        </script>";
    }
}

$grn_no     = $_GET['grn_no'];
$gin_no = $_GET['gin_no'];

$sale_returnstmt = $pdo->prepare("SELECT * FROM sale_return WHERE gin_no=? ORDER BY id DESC");
$sale_returnstmt->execute([$gin_no]);
$sale_returnresult = $sale_returnstmt->fetchAll();
?>

<style>
.item-typeahead { position: relative; }
.item-typeahead-dropdown { position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; }
.item-typeahead-dropdown.show { display: block; }
.item-typeahead-dropdown .option { padding: 8px 12px; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; }
.item-typeahead-dropdown .option:hover, .item-typeahead-dropdown .option.active { background: #e9ecef; }
.item-typeahead-dropdown .no-result { padding: 10px 12px; color: #6c757d; font-size: 14px; }
</style>

<div class="col-md-12 px-3 pt-1">
  <div class="collapse show">
    <form action="" method="post">
      <div class="card">
        <div class="card-header py-2 pb-0 pt-3">
          <h5 class="d-flex align-items-center justify-content-between">
            Edit Sale Return - <?php echo $_GET['gin_no']; ?>
            <div class="d-flex"></div>
          </h5>
        </div>

        <div class="card-body" style="background-color: rgba(0,0,0,0.01);">
          <div class="row">
            <div class="col-6 d-flex">
              <div class="col">
                <label>Return Date</label>
                <input type="date" class="form-control"
                       name="date"
                       value="<?php echo $sale_returnresult[0]['date']; ?>">
              </div>

              <div class="col">
                <label>GRN_No</label>
                <input type="text" class="form-control" readonly
                       name="grn_no"
                       value="<?php echo $grn_no; ?>">
              </div>

              <div class="col">
                <label>gin_no</label>
                <input type="text" class="form-control"
                       name="gin_no"
                       value="<?php echo $gin_no; ?>">
              </div>
            </div>

            <div class="col-2">
              <label>Return Type</label>
              <select name="return_type" class="form-control">
                <option value="">Select Return Type</option>
                <option value="damaged">Damaged</option>
                <option value="wrong">Wrong Item</option>
                <option value="extra">Extra Quantity</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="col-4">
              <label>Remark</label>
              <input type="text" class="form-control remark"
                     name="remark"
                     value="<?php echo $sale_returnresult[0]['remark']; ?>">
            </div>
          </div>

          <div class="pl-2 pt-3">
            <table class="table table-hover table-bordered">
              <thead class="table-sm" style="background-color: #f4f4f4;">
                <tr>
                  <th>Item</th>
                  <th class="text-right">Selling Price</th>
                  <th class="text-right">Qty</th>
                  <th colspan="2">Amount</th>
                </tr>
              </thead>

              <tbody id="item-rows">
                <?php
                  if ($sale_returnresult) {
                    foreach ($sale_returnresult as $value) {
                      $item_id = $value['item_id'];
                      $itemStmt = $pdo->prepare("SELECT * FROM item WHERE item_id=?");
                      $itemStmt->execute([$item_id]);
                      $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
                      $item_display = ($item_id ?? '') . ' - ' . ($item['item_name'] ?? '');
                ?>
                <tr class="item-row" style="font-size: 15px;">
                  <td class="no-padding" style="min-width: 250px;">
                    <div class="item-typeahead">
                      <input type="text" class="custom-input item-typeahead-input" placeholder="Type item code or name..." value="<?php echo htmlspecialchars($item_display); ?>" autocomplete="off">
                      <input type="hidden" name="item_id[]" value="<?php echo htmlspecialchars($item_id); ?>">
                      <div class="item-typeahead-dropdown"></div>
                    </div>
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $item['selling_price']; ?>" class="custom-input text-right original_price" name="selling_price[]">
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['qty']; ?>" class="custom-input text-right qty" name="qty[]">
                  </td>

                  <td class="no-padding">
                    <input type="number" value="<?php echo $value['amount']; ?>" class="custom-input text-right" name="amount[]">
                  </td>

                  <td class="no-padding text-center"
                      style="background:none!important; cursor:pointer!important; width:30px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         fill="currentColor"
                         class="bi bi-x-lg remove-row-btn"
                         viewBox="0 0 16 16">
                      <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                  </td>
                </tr>
                <?php } } ?>
              </tbody>
            </table>
          </div>

          <div>
            <button type="button"
                    id="add-row-btn"
                    disabled
                    class="btn btn-default text-info btn-sm ml-2">
              + Add a new line
            </button>
          </div>
        </div>

        <div class="card-footer"
             style="border-top:1px solid lightgrey; background-color:white;">
          <div class="d-flex justify-content-end mt-1">
            <a href="sale_return.php"
               class="btn btn-secondary btn-sm text-light">Cancel</a>
            <button type="submit"
                    name="edit_btn"
                    class="btn btn-purple btn-sm text-light ml-1">
              Update Return
            </button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<script>
(function() {
  var searchTimeout;
  function initItemTypeahead(w) {
    var input = w.querySelector('.item-typeahead-input');
    var hidden = w.querySelector('input[name="item_id[]"]');
    var dropdown = w.querySelector('.item-typeahead-dropdown');
    if (!input || !hidden || !dropdown) return;
    function search(q, done) {
      fetch('get_items_search.php?q=' + encodeURIComponent((q || '').trim())).then(function(r) { return r.json(); })
        .then(function(d) { done(d.success && d.results ? d.results : []); }).catch(function() { done([]); });
    }
    function render(list) {
      dropdown.innerHTML = '';
      if (!list.length) dropdown.innerHTML = '<div class="no-result">No matching item</div>';
      else list.forEach(function(x) {
        var div = document.createElement('div');
        div.className = 'option';
        div.textContent = (x.item_id || '') + ' - ' + (x.item_name || '');
        div.onclick = function() {
          var row = w.closest('.item-row');
          hidden.value = x.item_id || '';
          input.value = (x.item_id || '') + ' - ' + (x.item_name || '');
          var priceInput = row ? row.querySelector('.original_price') : null;
          if (priceInput) {
            priceInput.value = x.selling_price != null ? x.selling_price : '';
            priceInput.dispatchEvent(new Event('input'));
          }
          dropdown.classList.remove('show');
        };
        dropdown.appendChild(div);
      });
      dropdown.classList.add('show');
    }
    input.oninput = function() {
      clearTimeout(searchTimeout);
      var q = input.value.trim();
      if (!q) { hidden.value = ''; dropdown.classList.remove('show'); return; }
      searchTimeout = setTimeout(function() { search(q, render); }, 300);
    };
    input.onfocus = function() { if (input.value.trim()) search(input.value.trim(), render); };
    input.onkeydown = function(e) {
      if (e.key === 'Escape') { dropdown.classList.remove('show'); return; }
      var opts = dropdown.querySelectorAll('.option');
      if (!opts.length) return;
      var act = dropdown.querySelector('.option.active');
      if (e.key === 'ArrowDown') { e.preventDefault(); if (!act) opts[0].classList.add('active'); else { act.classList.remove('active'); (act.nextElementSibling || opts[0]).classList.add('active'); } }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (!act) opts[opts.length-1].classList.add('active'); else { act.classList.remove('active'); (act.previousElementSibling || opts[opts.length-1]).classList.add('active'); } }
      else if (e.key === 'Enter' && act) { e.preventDefault(); act.click(); }
    };
    document.addEventListener('click', function(ev) { if (!w.contains(ev.target)) dropdown.classList.remove('show'); });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-typeahead').forEach(initItemTypeahead);
  });
})();
</script>
<script>
document.addEventListener('input', function(e) {
  if (e.target.classList.contains('qty') || e.target.classList.contains('original_price')) {
    var row = e.target.closest('.item-row');
    if (!row) return;
    var priceInput = row.querySelector('.original_price');
    var qtyInput = row.querySelector('.qty');
    var amountInput = row.querySelector('input[name="amount[]"]');
    if (!priceInput || !qtyInput || !amountInput) return;
    var price = parseFloat(priceInput.value) || 0;
    var qty = parseFloat(qtyInput.value) || 0;
    amountInput.value = (price * qty).toFixed(2);
  }
});
</script>
<?php include 'footer.html'; ?>
