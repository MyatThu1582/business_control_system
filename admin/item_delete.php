<?php
require '../config/config.php';
require '../config/common.php';

$id = $_GET['id'];
echo $item_id = $_GET['item_id'];
// exit();
// Delete from related tables
$stmt = $pdo->prepare("DELETE FROM stock WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM cash_purchase WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM credit_purchase WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM cash_sale WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM credit_sale WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM purchase_order_items WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM sale_order_items WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM purchase_return WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

$stmt = $pdo->prepare("DELETE FROM sale_return WHERE item_id=:item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

// PURCHASE DELETE
// 1. Get all temp_purchase_id that use this item
$stmt = $pdo->prepare("SELECT DISTINCT temp_purchase_id FROM temp_purchase_items WHERE item_id = :item_id");
$stmt->execute([
    ':item_id' => $item_id
]);
$tempPurchaseIds = $stmt->fetchAll(PDO::FETCH_COLUMN); // array of IDs

// 2. Delete temp_purchase_item for this item
$stmt = $pdo->prepare("DELETE FROM temp_purchase_items WHERE item_id = :item_id");
$stmt->execute([
    ':item_id' => $item_id
]);

// 3. Delete temp_purchase for those IDs
if(!empty($tempPurchaseIds)) {
    $ids = implode(',', $tempPurchaseIds); // convert array to comma-separated string
    $stmt = $pdo->prepare("DELETE FROM temp_purchase WHERE id IN (".$ids.")");
    $stmt->execute();
}

// SALE DELETE
// 1. Get all temp_sale_id that use this item
$stmt = $pdo->prepare("SELECT DISTINCT temp_sale_id FROM temp_sale_items WHERE item_id = :item_id");
$stmt->execute([':item_id' => $item_id]);
$tempSaleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Delete temp_sale_items for this item
$stmt = $pdo->prepare("DELETE FROM temp_sale_items WHERE item_id = :item_id");
$stmt->execute([':item_id' => $item_id]);

// 3. Delete temp_sale for those IDs
if(!empty($tempSaleIds)) {
    $ids = implode(',', $tempSaleIds);
    $stmt = $pdo->prepare("DELETE FROM temp_sale WHERE id IN (".$ids.")");
    $stmt->execute();
}

// Finally delete the item itself
$stmt = $pdo->prepare("DELETE FROM item WHERE id=".$id);
$stmt->execute();

// Redirect back
header('Location: item.php');
?>
