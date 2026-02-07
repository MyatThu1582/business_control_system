<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../Config/config.php';
require '../Config/common.php';

$table = $_GET['table'] ?? '';
$search = $_GET['search'] ?? '';

// Allow only certain tables
$allowedTables = ['item', 'supplier', 'customer', 'categories'];
if (!in_array($table, $allowedTables)) {
    die("Invalid table selected!");
}

// Define which column to search for each table
$searchColumns = [
    'item' => 'item_name',
    'supplier' => 'supplier_name',
    'customer' => 'category_name',
    'categories' => 'categories_name'
];

// Build query dynamically based on table
$searchColumn = $searchColumns[$table] ?? '';

if ($search && $searchColumn) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $searchColumn LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM $table ORDER BY id DESC");
    $stmt->execute();
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename={$table}_export_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output column headers
if (count($data) > 0) {
    echo implode("\t", array_keys($data[0])) . "\n";
}

// Output rows
foreach ($data as $row) {
    echo implode("\t", array_values($row)) . "\n";
}

exit;
