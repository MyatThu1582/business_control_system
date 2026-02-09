<?php
  session_start();
  if (empty($_SESSION['user_id'])) {
      header("Location: login.php");
      exit;
  }
  require '../config/config.php';
  require '../config/common.php';
  include 'header.php';
?>
<?php

    // Sale pending orders
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_pending_order FROM sale_order WHERE status='pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_pending_order = (int)$result['total_pending_order'];

    // Purchase pending orders
    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total_pending_purchase_order FROM purchase_order WHERE status='pending'");
    $stmt2->execute();
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $total_pending_purchase_order = (int)$result2['total_pending_purchase_order'];


// Top Customer
$topCustomerstmt = $pdo->prepare("
    SELECT 
        t.customer_id,
        c.customer_name,
        SUM(ti.qty * ti.price) AS total_spent
    FROM temp_sale_items ti
    JOIN temp_sale t ON ti.temp_sale_id = t.id
    JOIN customer c ON t.customer_id = c.customer_id
    GROUP BY t.customer_id, c.customer_name
    ORDER BY total_spent DESC
    LIMIT 1
");
$topCustomerstmt->execute();
$topCustomer = $topCustomerstmt->fetch(PDO::FETCH_ASSOC);

// Total Profit
$stmt = $pdo->prepare("
    SELECT SUM((s.price - i.original_price) * s.qty) AS total_profit
    FROM temp_sale_items s
    JOIN item i ON s.item_id = i.item_id
");
$stmt->execute();
$totalProfit = $stmt->fetch(PDO::FETCH_ASSOC)['total_profit'];

?>

<div class="container mt-4 px-4">
  <!-- Dashboard header -->
   <?php
    // Fetch company data for current user
    $stmt = $pdo->prepare("SELECT * FROM company LIMIT 1");
    $stmt->execute();
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
   ?>
    <div class="d-flex mb-4 justify-content-between align-items-center px-2">
      <h3 class="d-flex align-items-center">📊 <?php //if(!empty($company)){ echo $company['name']; }else{ echo "Dashboard"; } ?></h3>
      <div id="current-time">
        <span class="time-text">00:00:00</span>
      </div>
    </div>

    <div class="text-center mb-5" style="border-bottom: 1px solid grey; padding-top: 8%; padding-bottom: 15%;">
        <?php if (!empty($company['logo'])): ?>
            <img src="../uploads/<?php echo rawurlencode($company['logo']); ?>" class="logo-preview mt-4" alt="Logo">
        <?php endif; ?>
    </div>
    <div class="d-flex px-2 justify-content-between">
      <!-- Chart Card -->
      <div class="card shadow-sm mb-4 col-9 mr-3">
          <div class="d-flex align-items-center py-3 px-3" style="justify-content: space-between; border-bottom: 1px solid rgba(105,173,31,0.5);">
            <h4 class="mb-0 fw-semibold me-auto">Total Sale</h4>
            <div class="btn-group btn-group-sm" role="group" aria-label="Filter sales">
              <button type="button" class="btn btn-outline-primary" onclick="filterSales('weekly', this)">Weekly</button>
              <button type="button" class="btn btn-outline-primary" onclick="filterSales('monthly', this)">Monthly</button>
              <button type="button" class="btn btn-outline-primary" onclick="filterSales('yearly', this)">Yearly</button>
            </div>
          </div>
          <div class="card-body">
            <canvas id="salesChart" height="120"></canvas>
          </div>
      </div>

      <!-- Metrics Column -->
      <div class="col-3 metrics-column">
          <div class="metric-card">
              <div class="metric-label">Total Profit</div>
              <div class="metric-value"><?php echo number_format($totalProfit) . " MMK"; ?></div>
          </div>
          <div class="metric-card">
            <div class="metric-label">
                Pending 
                <span id="orderType">Sale</span> Orders
                <i id="toggleIcon" class="bi bi-arrow-left-right"
                    style="cursor:pointer; color:#007bff; margin-left:6px; float: right;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5m14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5"/>
                    </svg>
                </i>
            </div>

            <div class="metric-value" id="pendingValue">
                <?php echo number_format($total_pending_order); ?>
            </div>
            </div>


          <div class="metric-card">
              <div class="metric-label">Top Customer</div>
              <div class="metric-value">
                <?php if ($topCustomer) {
                       echo $topCustomer['customer_name'];
                  } else {
                       echo "No sales yet.";
                  } ?>
              </div>
          </div>
      </div>
    </div>

    <div class="d-flex justify-content-between pr-3 mt-2">
      <div class="col-3 d-flex flex-column mr-3" style="gap:20px;">
        <h4 class="pt-3" style="border-top: 1px solid rgba(105,173,31,0.5);">Financial Overview</h4>
        <!-- Total AP Amount -->
        <div class="metric-card shadow-sm p-3 bg-white rounded d-flex flex-column justify-content-between" style="min-height: 145px;">
            <div class="mt-2">
                <div class="metric-label mb-2">Total AP Amount</div>
                <div class="metric-value h4">
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT 
                            COALESCE(SUM(amount),0) - COALESCE(SUM(paid),0) AS total_ap
                        FROM payable
                    ");
                    $stmt->execute();
                    $total_ap = $stmt->fetch(PDO::FETCH_ASSOC)['total_ap'] ?? 0;

                    echo number_format($total_ap) . " MMK";
                    ?>

                </div>
            </div>
            <a href="account_payable.php" class="text-sm text-primary d-inline-block text-right">View All</a>
        </div>

        <!-- Total AR Amount -->
        <div class="metric-card shadow-sm p-3 bg-white rounded d-flex flex-column justify-content-between" style="min-height: 145px;">
            <div class="mt-2">
                <div class="metric-label mb-2">Total AR Amount</div>
                <div class="metric-value h4">
                <?php
                $stmt = $pdo->prepare("
                    SELECT 
                        COALESCE(SUM(amount),0) - COALESCE(SUM(paid),0) AS total_ar
                    FROM receivable
                ");
                $stmt->execute();
                $total_ar = $stmt->fetch(PDO::FETCH_ASSOC)['total_ar'] ?? 0;

                echo number_format($total_ar) . " MMK";
                ?>

                </div>
            </div>
            <a href="account_receivable.php" class="text-sm text-primary mt-3 d-inline-block text-right">View All</a>
        </div>
      </div>

      <!-- Right Side: Stock Overview -->
      <div class="card shadow-sm mb-4 col-9">
        <div class="d-flex align-items-center py-3 px-4" 
            style="justify-content: space-between; border-bottom: 1px solid rgba(105,173,31,0.5);">
          <h4 class="mb-0 fw-semibold">Stock Overview</h4>
          <a href="stock_control.php" class="small text-primary text-decoration-none">View All</a>
        </div>
        <div class="card-body" style="height: 320px;">
          <canvas id="stockChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Quick Links Row -->
    <div class="row mt-2 px-2">
        <!-- Sale -->
        <div class="col-md-3 mb-3">
            <a href="sale.php" class="card shadow-sm text-center p-4 text-decoration-none quick-link">
                <div class="mb-2"><i class="fas fa-shopping-cart fa-2x text-primary"></i></div>
                <div class="fw-bold">Add Sale</div>
            </a>
        </div>

        <!-- Stock List -->
        <div class="col-md-3 mb-3">
            <a href="stock_control.php" class="card shadow-sm text-center p-4 text-decoration-none quick-link">
                <div class="mb-2"><i class="fas fa-boxes fa-2x text-success"></i></div>
                <div class="fw-bold">Stock List</div>
            </a>
        </div>

        <!-- Settings -->
        <div class="col-md-3 mb-3">
            <a href="company.php" class="card shadow-sm text-center p-4 text-decoration-none quick-link">
                <div class="mb-2"><i class="fas fa-cog fa-2x text-warning"></i></div>
                <div class="fw-bold">Settings</div>
            </a>
        </div>

        <!-- Reports -->
        <div class="col-md-3 mb-3">
            <a href="choose_report.php" class="card shadow-sm text-center p-4 text-decoration-none quick-link">
                <div class="mb-2"><i class="fas fa-chart-line fa-2x text-danger"></i></div>
                <div class="fw-bold">Reports</div>
            </a>
        </div>
    </div>
  </div>

<script>
let showingSale = true;

const salePending = <?php echo $total_pending_order; ?>;
const purchasePending = <?php echo $total_pending_purchase_order; ?>;

const valueEl = document.getElementById('pendingValue');
const typeEl = document.getElementById('orderType');
const iconEl = document.getElementById('toggleIcon');

iconEl.addEventListener('click', function () {
  if (showingSale) {
    valueEl.innerText = purchasePending.toLocaleString();
    typeEl.innerText = 'Purchase';
  } else {
    valueEl.innerText = salePending.toLocaleString();
    typeEl.innerText = 'Sale';
  }
  showingSale = !showingSale;
});
</script>


<!-- Current Time Script -->
<script>
  function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute:'2-digit', second:'2-digit' });
    document.querySelector('#current-time .time-text').textContent = timeStr;
  }
  updateTime();
  setInterval(updateTime, 1000);
</script>

<script src="assets/get_sales.js"></script>
<script src="assets/stock_chart.js"></script>
<?php include 'footer.html'; ?>
