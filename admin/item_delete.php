<?php
require '../Config/config.php';
require '../Config/common.php';
$stmt = $pdo->prepare("DELETE FROM item WHERE id=".$_GET['id']);
$stmt->execute();
header('Location: item.php');
 ?>
