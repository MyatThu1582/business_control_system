<?php
header("Content-Type: application/json");
require '../Config/config.php';

if (isset($_GET['supplier_id'])) {
    $supplier_id = $_GET['supplier_id'];

    $stmt = $pdo->prepare("SELECT supplier_name FROM supplier WHERE supplier_id = :id");
    $stmt->execute([':id' => $supplier_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(["success" => true, "supplier_name" => $row['supplier_name']]);
    } else {
        echo json_encode(["success" => false]);
    }
}
