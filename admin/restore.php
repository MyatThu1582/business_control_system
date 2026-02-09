<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
require '../config/config.php';
require '../config/common.php';

?>
<?php include 'header.php'; ?>


<?php include 'footer.html'; ?>