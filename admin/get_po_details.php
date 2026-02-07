<?php
header("Content-Type: application/json");
require '../Config/config.php';

if (!isset($_GET['order_no']) || trim($_GET['order_no']) === '') {
    echo json_encode(['success' => false, 'message' => 'order_no required']);
    exit;
}

$order_no = trim($_GET['order_no']);

$postmt = $pdo->prepare("SELECT * FROM purchase_order WHERE order_no = :order_no");
$postmt->execute([':order_no' => $order_no]);
$po = $postmt->fetch(PDO::FETCH_ASSOC);

if (!$po) {
    echo json_encode(['success' => false, 'message' => 'PO not found']);
    exit;
}

$supplier_id = $po['supplier_id'];
$supplierstmt = $pdo->prepare("SELECT supplier_id, supplier_name FROM supplier WHERE supplier_id = :id");
$supplierstmt->execute([':id' => $supplier_id]);
$supplier = $supplierstmt->fetch(PDO::FETCH_ASSOC);

$itemsstmt = $pdo->prepare("
    SELECT poi.item_id, poi.qty, poi.price, poi.amount, i.item_name
    FROM purchase_order_items poi
    LEFT JOIN item i ON i.item_id = poi.item_id
    WHERE poi.order_no = :order_no
    ORDER BY poi.id
");
$itemsstmt->execute([':order_no' => $order_no]);
$items = $itemsstmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'order_no' => $order_no,
    'supplier_id' => $supplier_id,
    'supplier_name' => $supplier ? $supplier['supplier_name'] : '',
    'items' => $items,
]);
