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
    SELECT customer_id, customer_name 
    FROM customer 
    WHERE customer_id LIKE :q OR customer_name LIKE :q2 
    ORDER BY customer_name 
    LIMIT 50
");
$stmt->execute([':q' => $search, ':q2' => $search]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'results' => $results]);
