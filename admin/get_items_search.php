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
    SELECT item_id, item_name, original_price, selling_price, item_image 
    FROM item 
    WHERE item_id LIKE :q OR item_name LIKE :q2 
    ORDER BY item_name 
    LIMIT 50
");
$stmt->execute([':q' => $search, ':q2' => $search]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'results' => $results]);
