<?php
session_start();
if (empty($_SESSION['user_id'])) exit;

require '../Config/config.php';
require '../Config/common.php';

// No period filter for stock, we always show latest balance per item
$stmt = $pdo->prepare("
    SELECT i.item_name, s.item_id, s.balance, i.reorder_level
    FROM stock s
    JOIN item i ON s.item_id = i.item_id
    WHERE s.id IN (
        SELECT MAX(id) FROM stock GROUP BY item_id
    )
    ORDER BY s.balance ASC
");
$stmt->execute();
$stock_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
$colors = [];

foreach ($stock_data as $row) {
    $labels[] = $row['item_name'];
    $data[] = (int)$row['balance'];
    $colors[] = ($row['balance'] <= $row['reorder_level'])
        ? 'rgba(255, 99, 132, 0.7)'  // Low stock (red)
        : 'rgba(75, 192, 192, 0.7)'; // Normal stock (teal)
}

echo json_encode([
    "labels" => $labels,
    "data"   => $data,
    "colors" => $colors
]);
