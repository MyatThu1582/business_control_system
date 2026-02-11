<?php
session_start();
require '../config/config.php';
require '../config/common.php';
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if($_GET['table_name'] == 'purchase_order'){
    $id = $_GET['id'];
    $order_no = $_GET['order_no'];

    // purchase order delete
    $stmt = $pdo->prepare("DELETE FROM purchase_order WHERE id = :id");
    $stmt->execute([':id' => $id]);
    // purchase order items delete
    $stmt = $pdo->prepare("DELETE FROM purchase_order_items WHERE order_no = :order_no");
    $stmt->execute([':order_no' => $order_no]);

    header('Location: purchase_order.php');
}

if($_GET['table_name'] == 'purchase_order_item'){
    $id = $_GET['id'];
    $order_no = $_GET['order_no'];

    $stmt = $pdo->prepare("DELETE FROM purchase_order_items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: purchase_order_detail.php?order_no='.$order_no);
}

if($_GET['table_name'] == 'temp_purchase'){
    $id = $_GET['id'];
    $grn_no = $_GET['grn_no'];
    $stmt = $pdo->prepare("DELETE FROM temp_purchase WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $stmt = $pdo->prepare("DELETE FROM temp_purchase_items WHERE grn_no = :grn_no");
    $stmt->execute([':grn_no' => $grn_no]);
    header('Location: purchase.php');
}

if($_GET['table_name'] == 'purchase_return'){
    $gin_no = $_GET['gin_no'];
    $grn_no = $_GET['grn_no'];
    // $stmt = $pdo->prepare("DELETE FROM purchase_return WHERE gin_no = :gin_no AND grn_no = :grn_no");
    // $stmt->execute([':gin_no' => $gin_no, ':grn_no' => $grn_no]);
    
    // $stockstmt = $pdo->prepare("DELETE FROM stock WHERE gin_no = :gin_no AND grn_no = :grn_no");
    // $stockstmt->execute([':gin_no' => $gin_no, ':grn_no' => $grn_no]);
    
    // $payablestmt = $pdo->prepare("DELETE FROM payable WHERE gin_no = :gin_no AND grn_no = :grn_no");
    // $payablestmt->execute([':gin_no' => $gin_no, ':grn_no' => $grn_no]);
    
    // header('Location: purchase_return.php');
}

if($_GET['table_name'] == 'temp_sale'){
    $id = $_GET['id'];
    $gin_no = $_GET['gin_no'] ?? null;
    if ($gin_no === null) {
        $ginStmt = $pdo->prepare("SELECT gin_no FROM temp_sale WHERE id = :id");
        $ginStmt->execute([':id' => $id]);
        $gin_no = $ginStmt->fetchColumn();
    }

    $stmt = $pdo->prepare("DELETE FROM temp_sale WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if (!empty($gin_no)) {
        $stmt = $pdo->prepare("DELETE FROM temp_sale_items WHERE gin_no = :gin_no");
        $stmt->execute([':gin_no' => $gin_no]);
    }
    header('Location: sale.php');
}

if($_GET['table_name'] == 'sale_order'){
    $id = $_GET['id'];
    $order_no = $_GET['order_no'] ?? null;
    if ($order_no === null) {
        $orderStmt = $pdo->prepare("SELECT order_no FROM sale_order WHERE id = :id");
        $orderStmt->execute([':id' => $id]);
        $order_no = $orderStmt->fetchColumn();
    }
    $stmt = $pdo->prepare("DELETE FROM sale_order WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if (!empty($order_no)) {
        $stmt = $pdo->prepare("DELETE FROM sale_order_items WHERE order_no = :order_no");
        $stmt->execute([':order_no' => $order_no]);
    }
    header('Location: sale_order.php');
}

if($_GET['table_name'] == 'sale_order_item'){
    $id = $_GET['id'];
    $order_no = $_GET['order_no'] ?? '';

    $stmt = $pdo->prepare("DELETE FROM sale_order_items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: sale_order_detail.php?order_no='.$order_no);
}
 ?>
