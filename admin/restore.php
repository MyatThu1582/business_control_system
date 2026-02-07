<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
require '../Config/config.php';
require '../Config/common.php';

?>
<?php include 'header.php'; ?>


<?php include 'footer.html'; ?>