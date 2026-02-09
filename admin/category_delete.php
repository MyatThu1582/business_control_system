<?php
include '../config/config.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindValue(':id', (int)$_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
}

header('Location: category.php');
?>
