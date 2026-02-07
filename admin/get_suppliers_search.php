<?php
header("Content-Type: application/json");
require '../Config/config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$search = '%' . $q . '%';
$stmt = $pdo->prepare("
    SELECT supplier_id, supplier_name 
    FROM supplier 
    WHERE supplier_id LIKE :q OR supplier_name LIKE :q2 
    ORDER BY supplier_name 
    LIMIT 50
");
$stmt->execute([':q' => $search, ':q2' => $search]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'results' => $results]);
