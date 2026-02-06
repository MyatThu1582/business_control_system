<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

?>

<!-- Dashboard Styles -->
<style>
  body {
    background: #f5f7fa;
    font-family: 'Segoe UI', sans-serif;
  }

  /* Fade-in + Slide-up Animation */
  @keyframes fadeSlideUp {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
  }

  .animated-card {
    opacity: 0;
    animation: fadeSlideUp 0.8s ease forwards;
  }

  .animated-delay-1 { animation-delay: 0.1s; }
  .animated-delay-2 { animation-delay: 0.2s; }
  .animated-delay-3 { animation-delay: 0.3s; }
  .animated-delay-4 { animation-delay: 0.4s; }

  /* Smart card design */
  .card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12);
  }

  .card .card-body {
    padding: 1.5rem 1.5rem;
  }

  /* Metric headers */
  .card h4, .card h6 {
    font-weight: 600;
  }

  /* Clock */
  #current-time {
    display: flex;
    align-items: center;
    font-size: 1rem;
    color: #6c757d;
    gap: 0.4rem;
    font-weight: 500;
  }
  #current-time .clock-icon {
    font-size: 2rem;
    animation: pulseGlow 2.5s infinite ease-in-out;
  }
  #current-time .time-text {
    animation: bounceFade 1s infinite;
  }
  @keyframes pulseGlow {
    0%, 100% { opacity: 1; text-shadow: 0 0 6px rgba(0,123,255,0.7); }
    50% { opacity: 0.7; text-shadow: 0 0 16px rgba(0,123,255,1); }
  }
  @keyframes bounceFade {
    0%, 100% { transform: translateY(0); opacity: 1; }
    50% { transform: translateY(-3px); opacity: 0.75; }
  }

  /* Gradient cards for metrics */
  .gradient-primary { background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; }
  .gradient-success { background: linear-gradient(135deg, #1cc88a, #17a673); color: #fff; }
  .gradient-warning { background: linear-gradient(135deg, #f6c23e, #dda20a); color: #fff; }
  .gradient-danger  { background: linear-gradient(135deg, #e74a3b, #be2617); color: #fff; }

  /* Quick link cards */
  .quick-link-card {
    transition: all 0.3s ease;
    cursor: pointer;
  }
  .quick-link-card:hover {
    transform: scale(1.01);
    box-shadow: 0 12px 28px rgba(0,0,0,0.12);
  }
  .quick-action-card {
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background: #fff;
}

.quick-action-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.12);
}

.quick-action-card .action-icon {
  font-size: 2rem;
}

.quick-action-card h6 {
  font-weight: 600;
}

.quick-action-card .btn {
  font-weight: 600;
  padding: 0.5rem 0;
}
</style>

<div class="container mt-4">
  <div class="d-flex mb-4 justify-content-between align-items-center px-3">
    <h3 class="d-flex align-items-center">📊 Dashboard</h3>
    <div id="current-time">
      <span class="time-text">00:00:00</span>
    </div>
  </div>

  <script>
  function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute:'2-digit', second:'2-digit' });
    document.querySelector('#current-time .time-text').textContent = timeStr;
  }
  updateTime();
  setInterval(updateTime, 1000);
  </script>

<!-- Metrics Row -->
<?php
  // $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_sale WHERE supplier_id='$supplier_id'");
  // $total_cashsalestmt->execute();
  // $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

  // $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_sale WHERE supplier_id='$supplier_id'");
  // $total_creditsalestmt->execute();
  // $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);

  // Total Customer
  $stmt = $pdo->prepare("SELECT COUNT(*) AS total_customer FROM customer");
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $total_customer = $result['total_customer'];
?>
<div class="row px-3">
  <div class="col-md-3 animated-card animated-delay-1">
    <div class="card gradient-primary">
      <div class="card-body">
        <div class="small mb-2">Total Sales</div>
        <h4>₹1,20,000</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-2">
    <div class="card gradient-success">
      <div class="card-body">
        <div class="small mb-2">Customers</div>
        <h4><?php echo $total_customer; ?></h4>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-3">
    <div class="card gradient-warning">
      <div class="card-body">
        <div class="small mb-2">Pending Orders</div>
        <h4>18</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-4">
    <div class="card gradient-danger">
      <div class="card-body">
        <div class="small mb-2">Items in Stock</div>
        <h4>145</h4>
      </div>
    </div>
  </div>
</div>

<!-- Insights Row -->
<div class="row g-4 px-3">
  <div class="col-md-3 animated-card animated-delay-1">
    <div class="card shadow-sm h-100 bg-white">
      <div class="card-body">
        <div class="small text-muted mb-1">Top Product</div>
        <div class="fw-semibold">A4 Paper</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-2">
    <div class="card shadow-sm h-100 bg-white">
      <div class="card-body">
        <div class="small text-muted mb-1">Best Customer</div>
        <div class="fw-semibold">John D.</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-3">
    <div class="card shadow-sm h-100 bg-white">
      <div class="card-body">
        <div class="small text-muted mb-1">Monthly Growth</div>
        <div class="fw-semibold text-success">+12.5%</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 animated-card animated-delay-4">
    <div class="card shadow-sm h-100 bg-white">
      <div class="card-body">
        <div class="small text-muted mb-1">Total Profit</div>
        <div class="fw-semibold text-primary">₹35,000</div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Links -->
<div class="row g-4 mt-3 px-3">
  <div class="col-md-3 animated-card animated-delay-1">
    <div class="card quick-action-card h-100 text-center p-3">
      <div class="action-icon mb-2">📥</div>
      <h6 class="fw-semibold mb-1">Add New Entry</h6>
      <div class="small text-muted mb-3">Purchases / Cu stomers</div>
      <a href="add_purchase.php">Add Transaction</a>
    </div>
  </div>

  <div class="col-md-3 animated-card animated-delay-2">
    <div class="card quick-action-card h-100 text-center p-3">
      <div class="action-icon mb-2">📄</div>
      <h6 class="fw-semibold mb-1">View Reports</h6>
      <div class="small text-muted mb-3">Performance & Stats</div>
      <a href="choose_report.php">Open Reports</a>
    </div>
  </div>

  <div class="col-md-3 animated-card animated-delay-3">
    <div class="card quick-action-card h-100 text-center p-3">
      <div class="action-icon mb-2">🛒</div>
      <h6 class="fw-semibold mb-1">Manage Items</h6>
      <div class="small text-muted mb-3">Add, edit, or remove items</div>
      <a href="products.php">Manage Items</a>
    </div>
  </div>

  <div class="col-md-3 animated-card animated-delay-4">
    <div class="card quick-action-card h-100 text-center p-3">
      <div class="action-icon mb-2">⚙️</div>
      <h6 class="fw-semibold mb-1">Settings</h6>
      <div class="small text-muted mb-3">Customize your system</div>
      <a href="company.php">Open Setting</a>
    </div>
  </div>
</div>

</div>

<?php include 'footer.html'; ?>
