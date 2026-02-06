<?php
include 'Config/config.php';
$stmt = $pdo->prepare("DELETE FROM temp_purchase WHERE id=".$_GET['id']);
$stmt->execute();
header('Location: purchase.php');
 ?>
