<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../config/config.php';
require '../config/common.php';

$gin_no = $_GET['gin_no'] ?? '';

$sale = $pdo->query("SELECT * FROM temp_sale WHERE gin_no='$gin_no' ORDER BY id DESC")->fetch(PDO::FETCH_ASSOC);
$items = $pdo->query("SELECT * FROM temp_sale_items WHERE gin_no='$gin_no' ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Sale - <?php echo $gin_no; ?></title>
<style>
body { font-family: monospace; margin: 0; padding: 0; }
#print-area {
    width: 80mm; /* thermal printer width */
    margin: auto;
    padding: 5mm;
}

h2, h4, p { margin: 2mm 0; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th, td { border-bottom: 1px dashed #000; padding: 2px 0; text-align: left; }
th { font-weight: bold; }
tfoot td { font-weight: bold; border-top: 1px solid #000; }

@media print {
    body { margin: 0; }
    #print-area { width: 80mm; }
}
</style>
</head>
<body>
  <?php 
  $customer_id = $sale['customer_id'];
  $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
  $stmt->execute();
  $customer = $stmt->fetch(PDO::FETCH_ASSOC);
  ?>
<div id="print-area">
  <h2>Sale Invoice</h2>
  <p>GIN No: <?php echo $sale['gin_no']; ?></p>
  <p>Date: <?php echo $sale['date']; ?></p>
  <p>Customer: <?php echo $customer['customer_name']; ?></p>
  <p>Payment: <?php echo ucfirst($sale['type']); ?></p>

  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Amt</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $total = 0;
      foreach ($items as $item): 
        $itemData = $pdo->query("SELECT * FROM item WHERE item_id='".$item['item_id']."'")->fetch(PDO::FETCH_ASSOC);
        $total += $item['amount'];
      ?>
      <tr>
        <td><?php echo $itemData['item_name']; ?></td>
        <td><?php echo $item['qty']; ?></td>
        <td><?php echo number_format($item['price'],2); ?></td>
        <td><?php echo number_format($item['amount'],2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" style="text-align:right;">Total:</td>
        <td><?php echo number_format($total,2); ?></td>
      </tr>
    </tfoot>
  </table>

  <p>-----------------------------</p>
  <p>Thank you!</p>
</div>

<script>
window.onload = function() {
    window.print();
};
</script>
</body>
</html>