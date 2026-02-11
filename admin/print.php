<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';

// Company info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM company WHERE user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// Dummy items for invoice
$items = [
    ['name' => 'Item A', 'qty' => 2, 'price' => 5000],
    ['name' => 'Item B', 'qty' => 1, 'price' => 7500],
    ['name' => 'Item C', 'qty' => 3, 'price' => 1200],
];

$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item['qty'] * $item['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $company['name']; ?> Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .logo { max-height: 80px; }
        .company-details { text-align: right; }
        hr { border: 0; border-top: 2px solid #000; margin: 10px 0 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
        .print-btn { margin-bottom: 20px; padding: 10px 15px; background: #1c1c1c; color: #fff; text-decoration: none; border-radius: 5px; display: inline-block; }
        .print-btn:hover { background: #444; }
    </style>
</head>
<body>

<div class="invoice-box">

    <div class="header">
        <div class="logo-area">
            <?php if(!empty($company['logo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($company['logo']); ?>" alt="Logo" class="logo">
            <?php endif; ?>
        </div>
        <div class="company-details">
            <h2><?php echo htmlspecialchars($company['name'] ?? ''); ?></h2>
            <p>
                <?php echo htmlspecialchars($company['street_name'] ?? ''); ?>,
                <?php echo htmlspecialchars($company['building_no'] ?? ''); ?>,
                <?php echo htmlspecialchars($company['city'] ?? ''); ?>,
                <?php echo htmlspecialchars($company['country'] ?? ''); ?>
            </p>
            <p>Phone: <?php echo htmlspecialchars($company['phone'] ?? ''); ?> | Email: <?php echo htmlspecialchars($company['email'] ?? ''); ?></p>
            <p>Bank Account: <?php echo htmlspecialchars($company['bank_account'] ?? ''); ?></p>
        </div>
    </div>

    <hr>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; foreach ($items as $item): ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['qty']; ?></td>
                <td><?php echo number_format($item['price']); ?></td>
                <td><?php echo number_format($item['qty'] * $item['price']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">Grand Total</td>
                <td><?php echo number_format($totalAmount); ?></td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 40px;">Notes: Thank you for your business!</p>
</div>

</body>
</html>
