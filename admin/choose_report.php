<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';

// Get current report
$report_name = isset($_GET['report_name']) ? $_GET['report_name'] : null;

// Default: all disabled
$filterCategory = $filterItem = $fromDate = $toDate = $filterCustomer = $filterSupplier = $filterStockFoc = $filterDamageStock = $filterReturnStock = true;

// Enable/disable logic
if (in_array($report_name, ["stock_inventory_summary"])) {
  $filterCategory = true;
  $filterItem = false;
  $fromDate = false;
  $toDate = false;
  $filterDamageStock = false;
  $filterReturnStock = false;
  $filterStockFoc = false;

} elseif ($report_name === "balance_by_category") {
  $filterCategory = false;
  $filterItem = true;
  $fromDate = false;
  $toDate = false;
  $filterDamageStock = false;
  $filterReturnStock = false;
  $filterStockFoc = false;
  $fromDate = true;
  $toDate = true;
} elseif (in_array($report_name, ["sales_summary", "total_sales"])) {
  $fromDate = false;
  $toDate = false;
  $filterCustomer = false;
} elseif ($report_name === "top_customer") {
  $fromDate = true;
  $toDate = true;
} elseif (in_array($report_name, ["purchase_summary", "total_purchase"])) {
  $fromDate = false;
  $toDate = false;
  $filterSupplier = false;
}

?>
<?php include 'header.php'; ?>

<div class="container-fluid py-3 px-3">
  <div class="row">
    <!-- Sidebar (75%) -->
    <div class="col-lg-8 px-3">
      <h4 class="mb-4 fw-bold">📊 Select Report to View or Print</h4>

      <div class="py-4">
        <!-- Stock Reports -->
        <div class="report-group mb-4">
          <div class="row g-3 justify-content-center">
            <div class="col-md-5">
              <a href="?report_name=stock_inventory_summary" class="report-card <?php if($report_name === 'stock_inventory_summary'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">📦</div>
                    <h6 class="fw-bold">Stock Inventory Summary</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-5">
              <a href="?report_name=balance_by_category" class="report-card <?php if($report_name === 'balance_by_category'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">📂</div>
                    <h6 class="fw-bold">Balance by Category</h6>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Sales Reports -->
        <div class="report-group mb-4">
          <div class="row g-3 justify-content-center">
            <div class="col-md-4">
              <a href="?report_name=sales_summary" class="report-card <?php if($report_name === 'sales_summary'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">💰</div>
                    <h6 class="fw-bold">Sales Summary</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4">
              <a href="?report_name=total_sales" class="report-card <?php if($report_name === 'total_sales'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">🧾</div>
                    <h6 class="fw-bold">Total Sales</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4">
              <a href="?report_name=top_customer" class="report-card <?php if($report_name === 'top_customer'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">🏆</div>
                    <h6 class="fw-bold">Top Customer</h6>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Purchase Reports -->
        <div class="report-group mb-4">
          <div class="row g-3 justify-content-center">
            <div class="col-md-5">
              <a href="?report_name=purchase_summary" class="report-card <?php if($report_name === 'purchase_summary'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">📊</div>
                    <h6 class="fw-bold">Purchase Summary</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-5">
              <a href="?report_name=total_purchase" class="report-card <?php if($report_name === 'total_purchase'){ echo "active"; } ?>">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <div class="fs-1 mb-2">🛒</div>
                    <h6 class="fw-bold">Total Purchase</h6>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Panel (25%) -->
    <div class="col-md-3 filter-box pt-3">
        <h4 class="m-0 fw-bold mb-3">
          <i class="fas fa-filter" style="font-size: 20px;"></i>
          <?php 
            
            if(!empty($report_name)){
              $report_name = $_GET['report_name'];
              switch ($report_name) {
                case 'stock_inventory_summary':
                  echo "Stock Inventory Summary";
                  break;
                case 'balance_by_category':
                  echo "Balance by Category";
                  break;
                case 'sales_summary':
                  echo "Sales Summary";
                  break;
                case 'total_sales':
                  echo "Total Sales";
                  break;
                case 'top_customer':
                  echo "Top Customer";
                  break;
                case 'purchase_summary':
                  echo "Purchase Summary";
                  break;
                case 'total_purchase':
                  echo "Total Purchase";
                  break;
                default:
                  echo "Select a Report to View Filters";
              }
            }else{
              echo "Report Filters";
            }
          ?>
        </h4>

        <div class="card shadow-sm px-3 filter-group position-relative">
        <div id="reportFilters" class="mb-3">
          <form class="row g-2" action="report.php" method="GET" target="_blank">
            <input type="hidden" name="report_name" value="<?php echo htmlspecialchars($report_name); ?>">
            <div class="col-12 mb-2 d-flex">
              <div class="col-6">
                <label class="form-label">Category</label>
                <select id="filterCategory" 
                        class="form-control report-input chzn-select"
                        <?php echo $filterCategory ? "disabled" : ""; ?> 
                        name="category_id">
                  <option value="">All</option>
                  <?php 
                  $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY id DESC");
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  foreach ($result as $value) {
                    ?>
                    <option value="<?php echo $value['categories_code']; ?>">
                      <?php echo $value['categories_name']; ?>
                    </option>
                    <?php
                  }
                  ?>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Item</label>
                <select id="filterItem" class="form-control report-input" <?php echo $filterItem ? "disabled" : ""; ?> name="item_id">
                  <option value="">All</option>
                  <?php
                  $stmt = $pdo->prepare("SELECT * FROM item ORDER BY id DESC");
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  foreach ($result as $value) {
                    ?>
                    <option value="<?php echo $value['item_id']; ?>"><?php echo $value['item_name']; ?></option>
                    <?php
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-12 mb-2 d-flex">
              <div class="col-6">
                <label class="form-label">Supplier</label>
                <select id="filterSupplier" class="form-control report-input" <?php echo $filterSupplier ? "disabled" : ""; ?> name="supplier_id">
                  <option value="">All</option>
                  <?php 
                  $stmt = $pdo->prepare("SELECT * FROM supplier ORDER BY id DESC");
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  foreach ($result as $value) {
                    ?>
                    <option value="<?php echo $value['supplier_id']; ?>"><?php echo $value['supplier_name']; ?></option>
                    <?php
                  }
                  ?>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Customer</label>
                <select id="filterCustomer" class="form-control report-input" <?php echo $filterCustomer ? "disabled" : ""; ?> name="customer_id">
                  <option value="">All</option>
                  <?php
                  $stmt = $pdo->prepare("SELECT * FROM customer ORDER BY id DESC");
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  foreach ($result as $value) {
                    ?>
                    <option value="<?php echo $value['customer_id']; ?>"><?php echo $value['customer_name']; ?></option>
                    <?php
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-12 mb-2 d-flex">
              <div class="col-6">
                <label class="form-label">FOC</label>
                <select id="filterStockFoc" class="form-control report-input" name="stock_foc" <?php echo $filterStockFoc ? "disabled" : ""; ?>>
                  <option value="all">All</option>
                  <option value="purchase_foc">Purchase FOC</option>
                  <option value="sale_foc">Sale FOC</option>
                  <option value="">Do Not Show</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Damage</label>
                <select id="filterDamageStock" class="form-control report-input" name="damage_stock" <?php echo $filterDamageStock ? "disabled" : ""; ?>>
                  <option value="all">All</option>
                  <option value="">Do Not Show</option>
                </select>
              </div>
            </div>
            <div class="col-12 mb-2 px-3">
              <label class="form-label">Return</label>
              <select id="filterReturnStock" class="form-control report-input" name="return_stock" <?php echo $filterReturnStock ? "disabled" : ""; ?>>
                <option value="all">All</option>
                <option value="purchase_return">Purchase Return</option>
                <option value="sale_return">Sale Return</option>
                <option value="">Do Not Show</option>
              </select>
            </div>
            <div class="col-12 mb-2 d-flex">
              <div class="col-6">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control report-input" name="start_date" id="fromDate" <?php echo $fromDate ? "disabled" : ""; ?>>
              </div>
              <div class="col-6">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control report-input" name="end_date" id="toDate" <?php echo $toDate ? "disabled" : ""; ?>>
              </div>
            </div>

            <!-- Row 1 -->
            <div class="col-12 mb-3 d-flex gap-2 mt-3">
              <div class="col-6">
                <button type="submit" name="action" value="show" class="btn flex-fill btn-outline-dark" style="background-color: #69ad1f; color: white;">
                  <i class="fas fa-eye"></i> Show Report
                </button>
              </div>
              <div class="col-6">
                <button type="submit" name="action" value="print" class="btn flex-fill btn-outline-dark">
                  <i class="fas fa-print"></i> Print
                </button>
              </div>
            </div>

            <!-- Row 2 -->
            <div class="col-12 mb-2 d-flex gap-2">
              <div class="col-6">
                <button type="submit" name="action" value="excel" class="btn flex-fill btn-outline-dark">
                  <i class="fas fa-file-excel"></i> Excel
                </button>
              </div>
              <div class="col-6">
                <button type="submit" name="action" value="pdf" class="btn flex-fill btn-outline-dark">
                  <i class="fas fa-file-pdf"></i> PDF
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Report Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if(isset($reportData)): ?>
          <!-- Render your report table here -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Item</th>
                <th>In</th>
                <th>Out</th>
                <th>Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($reportData as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['item_name']) ?></td>
                  <td><?= $row['in_qty'] ?></td>
                  <td><?= $row['out_qty'] ?></td>
                  <td><?= $row['balance'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No report generated yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $(".chosen-select").chosen({
      width: "100%",        // makes it fit bootstrap column
      placeholder_text_single: "Select a category",
      no_results_text: "No match found!"
    });
  });
</script>

<?php include 'footer.html'; ?>
