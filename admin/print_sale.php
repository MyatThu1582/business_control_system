<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';

$gin_no = $_GET['gin_no'] ?? '';
if (empty($gin_no)) {
    header("Location: sale.php");
    exit;
}

$sale = $pdo->prepare("SELECT * FROM temp_sale WHERE gin_no=:gin_no ORDER BY id DESC LIMIT 1");
$sale->execute([':gin_no' => $gin_no]);
$sale = $sale->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    header("Location: sale.php");
    exit;
}

$items = $pdo->prepare("SELECT * FROM temp_sale_items WHERE gin_no=:gin_no ORDER BY id ASC");
$items->execute([':gin_no' => $gin_no]);
$items = $items->fetchAll();

$cust = $pdo->prepare("SELECT * FROM customer WHERE customer_id=:cid LIMIT 1");
$cust->execute([':cid' => $sale['customer_id']]);
$customer = $cust->fetch(PDO::FETCH_ASSOC);
$customer_name = $customer['customer_name'] ?? $sale['customer_id'];

$company = $pdo->query("SELECT * FROM company LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$clinic_name = $company['name'] ?? 'ZAR LI MIN NWE CLINIC';
$phone = $company['phone'] ?? '09-795799559';

$total = 0;
foreach ($items as $row) {
    $total += (float) $row['amount'];
}
$payment_label = ucfirst($sale['type']);
$receipt_time = date('h:i a', strtotime($sale['date'])) . ' ' . date('d/m/Y', strtotime($sale['date']));
$from_approve = isset($_GET['approved']) && $_GET['approved'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Print Sale - <?php echo htmlspecialchars($gin_no); ?></title>
<style>
* { box-sizing: border-box; }
body {
    font-family: 'Courier New', Courier, monospace;
    margin: 0;
    padding: 12px;
    background: #eee;
}
.receipt-actions {
    margin-bottom: 12px;
    display: flex;
    gap: 8px;
}
.receipt-actions button {
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    border: none;
    border-radius: 4px;
}
.btn-print { background: #007bff; color: #fff; }
.btn-done { background: #28a745; color: #fff; }
#print-area {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 24px;
    background: #fff;
    font-size: 48px;
    font-weight: 700;
    line-height: 1.3;
    color: #000;
    box-sizing: border-box;
}
.receipt-header {
    text-align: center;
    margin-bottom: 12px;
}
.receipt-header .clinic-name {
    font-weight: 800;
    font-size: 56px;
    line-height: 1.2;
}
.receipt-header .phone {
    margin-top: 4px;
    font-size: 44px;
    font-weight: 700;
}
.receipt-info { margin-bottom: 10px; font-weight: 700; font-size: 48px; }
.receipt-info .label { font-weight: 800; }
.receipt-info .value { margin-left: 0; font-weight: 700; }
.receipt-table-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-weight: 800;
    font-size: 44px;
}
.receipt-table-header .left { text-align: left; }
.receipt-table-header .right { text-align: right; }
.receipt-line { border-bottom: 4px dashed #000; margin: 8px 0; }
.receipt-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 6px 0;
    font-weight: 700;
    font-size: 48px;
}
.receipt-row .desc { flex: 1; word-break: break-word; min-width: 0; }
.receipt-row .amt { text-align: right; white-space: nowrap; margin-left: 8px; font-weight: 700; }
.receipt-total, .receipt-payment {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
    font-weight: 800;
    font-size: 52px;
}
.receipt-footer {
    text-align: center;
    margin-top: 14px;
    font-size: 44px;
    font-weight: 700;
}
@media print {
    @page {
        size: A4;
        margin: 12mm;
    }
    body {
        background: #fff;
        padding: 0;
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .receipt-actions { display: none !important; }
    #print-area {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none;
        font-size: 28pt !important;
        font-weight: 700;
    }
    .receipt-header .clinic-name { font-size: 36pt !important; font-weight: 800; }
    .receipt-header .phone { font-size: 24pt !important; font-weight: 700; }
    .receipt-info { font-size: 28pt !important; font-weight: 700; }
    .receipt-table-header { font-size: 26pt !important; font-weight: 800; }
    .receipt-row { font-size: 28pt !important; font-weight: 700; }
    .receipt-total, .receipt-payment { font-size: 30pt !important; font-weight: 800; }
    .receipt-footer { font-size: 24pt !important; font-weight: 700; }
    .receipt-line { border-bottom-width: 3px !important; }
}
</style>
</head>
<body>
<div class="receipt-actions no-print">
    <button type="button" class="btn-print" onclick="window.print();">Print</button>
    <button type="button" class="btn-done" onclick="done();">Done</button>
    <span style="font-size: 12px; color: #666; align-self: center;">Prints full page (A4). Turn off “Headers and footers” in the print dialog to hide URL/title.</span>
</div>

<div id="print-area">
    <div class="receipt-header">
        <div class="clinic-name"><?php echo htmlspecialchars($clinic_name); ?></div>
        <div class="phone"><?php echo htmlspecialchars($phone); ?></div>
    </div>

    <div class="receipt-info">
        <div>Customer:</div>
        <div class="value"><?php echo htmlspecialchars($customer_name); ?></div>
    </div>
    <div class="receipt-info">
        <div>TR# (GIN No):</div>
        <div class="value"><?php echo htmlspecialchars($gin_no); ?></div>
    </div>

    <div class="receipt-table-header">
        <span class="left">Quantity / Description</span>
        <span class="right">Total</span>
    </div>
    <div class="receipt-line"></div>

    <?php foreach ($items as $item):
        $itemRow = $pdo->prepare("SELECT item_name FROM item WHERE item_id=:id LIMIT 1");
        $itemRow->execute([':id' => $item['item_id']]);
        $itemName = $itemRow->fetchColumn() ?: $item['item_id'];
        $qty = (int) $item['qty'];
        $desc = $qty > 1 ? $qty . ' x ' . $itemName : $itemName;
        $amt = (float) $item['amount'];
    ?>
    <div class="receipt-row">
        <span class="desc"><?php echo htmlspecialchars($desc); ?></span>
        <span class="amt">K<?php echo number_format($amt); ?></span>
    </div>
    <?php endforeach; ?>

    <div class="receipt-line"></div>
    <div class="receipt-total">
        <span>Total</span>
        <span>K<?php echo number_format($total); ?></span>
    </div>
    <div class="receipt-line"></div>
    <div class="receipt-payment">
        <span><?php echo $payment_label; ?></span>
        <span>K<?php echo number_format($total); ?></span>
    </div>

    <div class="receipt-footer">
        <?php echo $receipt_time; ?>
    </div>
</div>

<script>
function done() {
    <?php if ($from_approve): ?>
    sessionStorage.setItem('saleApproved', 'true');
    <?php endif; ?>
    window.location.href = 'sale.php';
}
</script>
</body>
</html>
