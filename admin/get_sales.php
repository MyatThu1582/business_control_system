<?php
session_start();
if (empty($_SESSION['user_id'])) exit;

require '../config/config.php';
require '../config/common.php';

// Get period from GET
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';

switch ($period) {
    case 'weekly':
        $stmt = $pdo->prepare("
            SELECT DATE(t.date) AS sale_day, 
                   SUM(ti.qty * ti.price) AS total_amount
            FROM temp_sale_items ti
            JOIN temp_sale t ON ti.temp_sale_id = t.id
            WHERE t.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(t.date)
            ORDER BY sale_day ASC
        ");
        break;

    case 'monthly':
        $stmt = $pdo->prepare("
            SELECT DATE(t.date) AS sale_day, 
                   SUM(ti.qty * ti.price) AS total_amount
            FROM temp_sale_items ti
            JOIN temp_sale t ON ti.temp_sale_id = t.id
            WHERE MONTH(t.date) = MONTH(CURDATE()) 
              AND YEAR(t.date) = YEAR(CURDATE())
            GROUP BY DATE(t.date)
            ORDER BY sale_day ASC
        ");
        break;

    case 'yearly':
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(t.date, '%Y-%m') AS sale_day, 
                   SUM(ti.qty * ti.price) AS total_amount
            FROM temp_sale_items ti
            JOIN temp_sale t ON ti.temp_sale_id = t.id
            WHERE YEAR(t.date) = YEAR(CURDATE())
            GROUP BY DATE_FORMAT(t.date, '%Y-%m')
            ORDER BY sale_day ASC
        ");
        break;

    default:
        echo json_encode(["labels"=>[], "data"=>[]]);
        exit;
}


$stmt->execute();
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($sales_data as $row) {
    $labels[] = $row['sale_day'];
    $data[] = $row['total_amount'];
}

echo json_encode(["labels"=>$labels, "data"=>$data]);
