<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require '../config/config.php';
require '../config/common.php';

// Legacy file: redirect to the current editor page (`sale_detail.php`).
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: sale.php");
  exit;
}

$stmt = $pdo->prepare("SELECT id, gin_no, status FROM temp_sale WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  header("Location: sale.php");
  exit;
}

$gin_no = $row['gin_no'];
$status = $row['status'];
header("Location: sale_detail.php?gin_no=" . urlencode($gin_no) . "&temp_saleid=" . urlencode((string)$id) . "&status=" . urlencode($status));
exit;
