<?php
require '../Config/config.php';
require '../Config/common.php';
$stmt = $pdo->prepare("DELETE FROM supplier WHERE id=".$_GET['id']);
$stmt->execute();
header('Location: supplier.php');
 ?>
