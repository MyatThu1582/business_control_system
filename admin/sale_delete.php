<?php
include 'Config/config.php';
$stmt = $pdo->prepare("DELETE FROM temp_sale WHERE id=".$_GET['id']);
$stmt->execute();
header('Location: sale.php');
 ?>
