<?php
header("Content-Type: application/json");
require '../Config/config.php';

if (isset($_GET['supplier_name'])) {
    $supplier_name = $_GET['supplier_name'];

    $stmt = $pdo->prepare("SELECT supplier_id FROM supplier WHERE supplier_name = :name");
    $stmt->execute([':name' => $supplier_name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(["success" => true, "supplier_id" => $row['supplier_id']]);
    } else {
        echo json_encode(["success" => false]);
    }
}
