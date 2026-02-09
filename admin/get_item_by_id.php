<?php
header("Content-Type: application/json");
require '../config/config.php';

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    // get item info
    $stmt = $pdo->prepare("
        SELECT item_name, original_price, selling_price 
        FROM item 
        WHERE item_id = :id
    ");
    $stmt->execute([':id' => $item_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // get latest stock balance
    $stockstmt = $pdo->prepare("
        SELECT balance 
        FROM stock 
        WHERE item_id = :id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stockstmt->execute([':id' => $item_id]);
    $stockrow = $stockstmt->fetch(PDO::FETCH_ASSOC);

    $stock_balance = $stockrow ? (int)$stockrow['balance'] : 0;

    // get pending sale order qty
    $salestmt = $pdo->prepare("
        SELECT SUM(soi.qty) AS pending_qty
        FROM sale_order_items soi
        JOIN sale_order so ON so.order_no = soi.order_no
        WHERE soi.item_id = :id
        AND so.status NOT IN ('done')
    ");
    $salestmt->execute([':id' => $item_id]);
    $salerow = $salestmt->fetch(PDO::FETCH_ASSOC);

    $pending_qty = $salerow && $salerow['pending_qty']
        ? (int)$salerow['pending_qty']
        : 0;

    // real usable stock
    $real_balance = $stock_balance - $pending_qty;
    if ($real_balance < 0) {
        $real_balance = 0;
    }

    if ($row) {
        echo json_encode([
            "success" => true,
            "item_name" => $row['item_name'],
            "original_price" => $row['original_price'],
            "selling_price" => $row['selling_price'],
            "stock_balance" => $real_balance
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
