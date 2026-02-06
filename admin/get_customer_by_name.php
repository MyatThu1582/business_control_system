<?php
header("Content-Type: application/json");
require '../Config/config.php';

if(isset($_GET['customer_name'])){
    $customer_name = $_GET['customer_name'];

    $stmt = $pdo->prepare("SELECT customer_id FROM customer WHERE customer_name = ?");
    $stmt->execute([$customer_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result){
        echo json_encode(["success"=>true,"customer_id"=>$result['customer_id']]);
    } else {
        echo json_encode(["success"=>false]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"No customer_name sent"]);
}
?>
