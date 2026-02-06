<?php
header("Content-Type: application/json");
require '../Config/config.php'; // uses $pdo

if(isset($_GET['customer_id'])){
    $customer_id = $_GET['customer_id'];

    $stmt = $pdo->prepare("SELECT customer_name FROM customer WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result){
        echo json_encode(["success"=>true,"customer_name"=>$result['customer_name']]);
    } else {
        echo json_encode(["success"=>false]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"No customer_id sent"]);
}
?>
