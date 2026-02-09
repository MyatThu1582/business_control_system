<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
?>
<?php 
  $action = $_GET['action'] ?? 'show';

  if ($action === 'print') {
        echo "<script>window.onload = function(){ window.print(); }</script>";
    } elseif ($action === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=report.xls");
        echo $_SESSION['last_report_html']; // send table as Excel
        exit;
        // output table only
    } elseif ($action === 'pdf') {
  
      $dompdf = new Dompdf();
  
      // Get last report HTML
      $html = $_SESSION['last_report_html'] ?? '<p>No data available to export.</p>';
  
      // Optional: Add styling for PDF
      $html = '<style>
                  body { font-family: Arial, sans-serif; font-size: 12px; }
                  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                  th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                  th { background-color: #f2f2f2; }
               </style>' . $html;
  
      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'landscape'); // or 'portrait' if you like
      $dompdf->render();
  
      // Stream PDF to browser
      $dompdf->stream("report.pdf", ["Attachment" => true]);
      exit;
  }
  

    function renderReportTable($callback) {
      ob_start();
      $callback(); // execute callback function that renders table
      $html = ob_get_clean();
      $_SESSION['last_report_html'] = $html;
      echo $html;
  }
  
?>
<style>
  body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 1000px; margin: auto; padding:30px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .logo { max-height: 130px; }
        .company-details { text-align: right; }
        hr { border: 0; border-top: 2px solid #000; margin: 10px 0 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
        .print-btn { margin-bottom: 20px; padding: 10px 15px; background: #1c1c1c; color: #fff; text-decoration: none; border-radius: 5px; display: inline-block; }
        .print-btn:hover { background: #444; }
        .d-flex{ display: flex; }.justify-content-between{ justify-content: space-between; }
</style>
<?php
// Company info
// $user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM company");
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="invoice-box">

  <div class="header">
      <div class="logo-area">
          <?php if(!empty($company['logo'])): ?>
              <img src="uploads/<?php echo htmlspecialchars($company['logo']); ?>" alt="Logo" class="logo">
          <?php endif; ?>
      </div>
      <div class="company-details">
          <h2><?php echo htmlspecialchars($company['name'] ?? ''); ?></h2>
          <p>
              <?php echo htmlspecialchars($company['street_name'] ?? ''); ?>
              <?php echo htmlspecialchars($company['building_no'] ?? ''); ?>
              <?php echo htmlspecialchars($company['city'] ?? ''); ?>
              <?php echo htmlspecialchars($company['country'] ?? ''); ?>
          </p>
          <p>Phone: <?php echo htmlspecialchars($company['phone'] ?? ''); ?> | Email: <?php echo htmlspecialchars($company['email'] ?? ''); ?></p>
          <p>Bank Account: <?php echo htmlspecialchars($company['bank_account'] ?? ''); ?></p>
      </div>
  </div>

  <hr>
<?php
if($_GET['report_name'] === 'stock_inventory_summary'){
  renderReportTable(function() use ($pdo) {
?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
          Stock Inventory Summary Report
        </h4>
      <div>
        <h4>
        <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
        </h4>
      </div>
    </div>
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width: 50px">No</th>
          <th>Item Name</th>
          <th>Total In</th>
          <th>Total Out</th>
          <!-- Stock FOC -->
          <?php 
            if(!empty($_GET['stock_foc'])){
              if($_GET['stock_foc'] == 'purchase_foc'){
                ?>
                  <th><?php echo "Purchase FOC"; ?></th>
                <?php
                  }elseif($_GET['stock_foc'] == 'sale_foc'){
                ?>
                  <th><?php echo "Sale FOC"; ?></th>
                <?php
                  }elseif($_GET['stock_foc'] == 'all'){
                ?>
                <th>Purchase Foc</th>
                <th>Sale Foc</th>
              <?php
              }
            }
          ?>
          <!-- Return Stock -->
          <?php 
            if(!empty($_GET['return_stock'])){
              if($_GET['return_stock'] == 'purchase_return'){
                ?>
                  <th><?php echo "Purchase Return"; ?></th>
                <?php
                  }elseif($_GET['return_stock'] == 'sale_return'){
                ?>
                  <th><?php echo "Sale Return"; ?></th>
                <?php
                  }elseif($_GET['return_stock'] == 'all'){
                ?>
                <th>Purchase Return</th>
                <th>Sale Return</th>
              <?php
              }
            }
          ?>
          <!-- Damage Stock  -->
          <?php
          if(!empty($_GET['damage_stock'])){
          ?>
            <th>Damage</th>
          <?php
          }
          ?>
          <th>Balance</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // One Item's stock summary
        if(!empty($_GET['item_id'])) {
          $item_id = $_GET['item_id'];

          // Date Between
          if($_GET['start_date'] && $_GET['end_date']){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            // Total In
            $total_instmt = $pdo->prepare("SELECT SUM(in_qty) AS total_in FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_instmt->execute();
            $total_indata = $total_instmt->fetch(PDO::FETCH_ASSOC);
            
            // Total Out 
            $total_outstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_out FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_outstmt->execute();
            $total_outdata = $total_outstmt->fetch(PDO::FETCH_ASSOC);

            // Balance 
            $balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $balancestmt->execute();
            $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

            // Total Purchase FOC
            $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_purchase_focstmt->execute();
            $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);

            // Total Sale FOC
            $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_sale_focstmt->execute();
            $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);

            // Total Purchase Return
            $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_purchase_returnstmt->execute();
            $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);

            // Total Sale Return
            $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_sale_returnstmt->execute();
            $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);

            // Damage Stock
            $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage' AND date BETWEEN '$start_date' AND '$end_date'");
            $damagestmt->execute();
            $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

          }else{

            // Balance 
            $balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
            $balancestmt->execute();
            $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

            // Total Purchase FOC
            $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase'");
            $total_purchase_focstmt->execute();
            $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);

            // Total Sale FOC
            $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale'");
            $total_sale_focstmt->execute();
            $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);

            // Total Purchase Return
            $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return'");
            $total_purchase_returnstmt->execute();
            $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);

            // Total Sale Return
            $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return'");
            $total_sale_returnstmt->execute();
            $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);

            // Damage Stock
            $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage'");
            $damagestmt->execute();
            $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

            // Total In
            $total_instmt = $pdo->prepare("SELECT SUM(in_qty) AS total_in FROM stock WHERE item_id='$item_id'");
            $total_instmt->execute();
            $total_indata = $total_instmt->fetch(PDO::FETCH_ASSOC);
            
            // Total Out 
            $total_outstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_out FROM stock WHERE item_id='$item_id'");
            $total_outstmt->execute();
            $total_outdata = $total_outstmt->fetch(PDO::FETCH_ASSOC);
          
          }

            // Adjust Total In Qty
            if(!empty(($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'purchase_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'sale_return')){ // if show purchase foc and sale return
              $total_in = ($total_indata['total_in'] - $total_purchase_focdata['total_purchase_foc']) - $total_sale_returndata['total_sale_return'];
            }elseif(!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'purchase_foc'){ // if show purchase foc and foc all
              $total_in = $total_indata['total_in'] - $total_purchase_focdata['total_purchase_foc'];
            }elseif(!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'sale_return'){ // if show sale return and return all
              $total_in = $total_indata['total_in'] - $total_sale_returndata['total_sale_return'];
            }else{
              $total_in = $total_indata['total_in'];
            }

            // Adjust Total Out Qty
            if((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show sale foc and purchase return and damage
              $total_out = (($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $total_purchase_returndata['total_purchase_return']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show sale foc and damage
              $total_out = ($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show return and damage
              $total_out = ($total_outdata['total_out'] - $total_purchase_returndata['total_purchase_return']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return')){ // if show FOC and return
              $total_out = ($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $total_purchase_returndata['total_purchase_return'];
            }elseif(!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc'){ // if show sale foc and foc all
              $total_out = $total_outdata['total_out'] - $total_sale_focdata['total_sale_foc'];
            }elseif(!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return'){ // if show purchase return and return all
              $total_out = $total_outdata['total_out'] - $total_purchase_returndata['total_purchase_return'];
            }elseif(!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all'){ // if show damage stock
              $total_out = $total_outdata['total_out'] - $damagedata['total_damage'];
            }else{
              $total_out = $total_outdata['total_out'];
            }

          // Item Name
          $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
          $itemstmt->execute();
          $itemResult= $itemstmt->fetch(PDO::FETCH_ASSOC);
          ?>
          <tr style="<?php if($total_in == 0 && $total_out == 0){ echo "display:none;"; } ?>">
            <td class="pl-3"><?php echo "1"; ?></td>
            <td class="pl-3"><?php echo $itemResult['item_name'];?></td>
            <td class="pl-3"><?php if($total_in != 0){ echo $total_in; }else{ echo "-"; }; ?></td>
            <td class="pl-3"><?php if($total_out != 0){ echo $total_out; }else{ echo "-"; }; ?></td>
            <!-- Stock FOC -->
            <?php 
              if(!empty($_GET['stock_foc'])){
                if($_GET['stock_foc'] == 'purchase_foc'){
                  ?>
                    <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-"; }; ?></td>
                  <?php
                    }elseif($_GET['stock_foc'] == 'sale_foc'){
                  ?>
                    <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-"; }; ?></td>
                  <?php
                    }elseif($_GET['stock_foc'] == 'all'){
                  ?>
                  <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-"; }; ?></td>
                  <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-"; }; ?></td>
                <?php
                }
              }
            ?>
            <!-- Stock return -->
            <?php 
              if(!empty($_GET['return_stock'])){
                if($_GET['return_stock'] == 'purchase_return'){
                  ?>
                    <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-"; } ?></td>
                  <?php
                    }elseif($_GET['return_stock'] == 'sale_return'){
                  ?>
                    <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-"; } ?></td>
                  <?php
                    }elseif($_GET['return_stock'] == 'all'){
                  ?>
                  <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-"; } ?></td>
                  <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-"; } ?></td>
                <?php
                }
              }
            ?>
          <!-- Damage Stock -->
          <?php
            if(!empty($_GET['damage_stock'])){
              ?>
              <td class="pl-3"><?php if($damagedata['total_damage'] != 0){ echo $damagedata['total_damage']; }else{ echo "-"; } ?></td>
              <?php
            }
          ?>
          <!-- Balance Stock -->
            <td class="pl-3"><?php if($balancedata['balance'] != 0){ echo $balancedata['balance']; }else{ echo "-"; } ?></td>
          </tr>
          <?php
        }else{
          // All Item Stock Summary
          $stockstmt = $pdo->prepare("SELECT DISTINCT item_id FROM stock ORDER BY id DESC");
          $stockstmt->execute();
          $stockdata = $stockstmt->fetchAll();
          $id = 1;
          foreach($stockdata as $data){
            $item_id = $data['item_id'];
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              $start_date = $_GET['start_date'];
              $end_date = $_GET['end_date'];

              // Total In
              $total_instmt = $pdo->prepare("SELECT SUM(in_qty) AS total_in FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_instmt->execute();
              $total_indata = $total_instmt->fetch(PDO::FETCH_ASSOC);
              
              // Total Out 
              $total_outstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_out FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_outstmt->execute();
              $total_outdata = $total_outstmt->fetch(PDO::FETCH_ASSOC);

              // Balance 
              $balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' AND date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
              $balancestmt->execute();
              $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

              // Total Purchase FOC
              $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_purchase_focstmt->execute();
              $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);

              // Total Sale FOC
              $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_sale_focstmt->execute();
              $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);

              // Total Purchase Return
              $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_purchase_returnstmt->execute();
              $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);

              // Total Sale Return
              $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return' AND date BETWEEN '$start_date' AND '$end_date'");
              $total_sale_returnstmt->execute();
              $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);

              // Damage Stock
              $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage' AND date BETWEEN '$start_date' AND '$end_date'");
              $damagestmt->execute();
              $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

            }else{

              // Balance 
              $balancestmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
              $balancestmt->execute();
              $balancedata = $balancestmt->fetch(PDO::FETCH_ASSOC);

              // Total Purchase FOC
              $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase'");
              $total_purchase_focstmt->execute();
              $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);

              // Total Sale FOC
              $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale'");
              $total_sale_focstmt->execute();
              $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);

              // Total Purchase Return
              $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return'");
              $total_purchase_returnstmt->execute();
              $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);

              // Total Sale Return
              $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return'");
              $total_sale_returnstmt->execute();
              $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);

              // Damage Stock
              $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage'");
              $damagestmt->execute();
              $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

              // Total In
              $total_instmt = $pdo->prepare("SELECT SUM(in_qty) AS total_in FROM stock WHERE item_id='$item_id'");
              $total_instmt->execute();
              $total_indata = $total_instmt->fetch(PDO::FETCH_ASSOC);
              
              // Total Out 
              $total_outstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_out FROM stock WHERE item_id='$item_id'");
              $total_outstmt->execute();
              $total_outdata = $total_outstmt->fetch(PDO::FETCH_ASSOC);

            }

            // Adjust Total In Qty
            if(!empty(($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'purchase_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'sale_return')){ // if show purchase foc and sale return
              $total_in = ($total_indata['total_in'] - $total_purchase_focdata['total_purchase_foc']) - $total_sale_returndata['total_sale_return'];
            }elseif(!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'purchase_foc'){ // if show purchase foc and foc all
              $total_in = $total_indata['total_in'] - $total_purchase_focdata['total_purchase_foc'];
            }elseif(!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'sale_return'){ // if show sale return and return all
              $total_in = $total_indata['total_in'] - $total_sale_returndata['total_sale_return'];
            }else{
              $total_in = $total_indata['total_in'];
            }

            // Adjust Total Out Qty
            if((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show sale foc and purchase return and damage
              $total_out = (($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $total_purchase_returndata['total_purchase_return']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show sale foc and damage
              $total_out = ($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return') AND (!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all')){ // if show return and damage
              $total_out = ($total_outdata['total_out'] - $total_purchase_returndata['total_purchase_return']) - $damagedata['total_damage'];
            }elseif((!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc') AND (!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return')){ // if show FOC and return
              $total_out = ($total_outdata['total_out'] - $total_sale_focdata['total_sale_foc']) - $total_purchase_returndata['total_purchase_return'];
            }elseif(!empty($_GET['stock_foc']) && $_GET['stock_foc'] == 'all' OR $_GET['stock_foc'] == 'sale_foc'){ // if show sale foc and foc all
              $total_out = $total_outdata['total_out'] - $total_sale_focdata['total_sale_foc'];
            }elseif(!empty($_GET['return_stock']) && $_GET['return_stock'] == 'all' OR $_GET['return_stock'] == 'purchase_return'){ // if show purchase return and return all
              $total_out = $total_outdata['total_out'] - $total_purchase_returndata['total_purchase_return'];
            }elseif(!empty($_GET['damage_stock']) && $_GET['damage_stock'] == 'all'){ // if show damage stock
              $total_out = $total_outdata['total_out'] - $damagedata['total_damage'];
            }else{
              $total_out = $total_outdata['total_out'];
            }

            // Item Name
            $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
            $itemstmt->execute();
            $itemResult= $itemstmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <tr style="<?php if($total_in == 0 && $total_out == 0){ echo "display:none;"; } ?>">
              <td><?php echo $id; ?></td>
              <td><?php echo $itemResult['item_name'];?></td>
              <td><?php if($total_in != 0){ echo $total_in; }else{ echo "-"; }?></td>
              <td><?php if($total_out != 0){ echo $total_out; }else{ echo "-"; }?></td>
              <!-- Stock FOC -->
            <?php 
              if(!empty($_GET['stock_foc'])){
                if($_GET['stock_foc'] == 'purchase_foc'){
                  ?>
                    <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                  <?php
                    }elseif($_GET['stock_foc'] == 'sale_foc'){
                  ?>
                    <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                  <?php
                    }elseif($_GET['stock_foc'] == 'all'){
                  ?>
                  <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                  <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                <?php
                }
              }
            ?>
            <!-- Stock return -->
            <?php 
              if(!empty($_GET['return_stock'])){
                if($_GET['return_stock'] == 'purchase_return'){
                  ?>
                    <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                  <?php
                    }elseif($_GET['return_stock'] == 'sale_return'){
                  ?>
                    <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                  <?php
                    }elseif($_GET['return_stock'] == 'all'){
                  ?>
                  <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                  <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                <?php
                }
              }
            ?>
            <!-- Damage Stock -->
            <?php
              if(!empty($_GET['damage_stock'])){
                ?>
                <td class="pl-3"><?php if($damagedata['total_damage'] != 0){ echo $damagedata['total_damage']; }else{ echo "-";} ?></td>
                <?php
              }
            ?>
            <!-- Balance Stock -->
              <td class="pl-3"><?php if($balancedata['balance'] != 0){ echo $balancedata['balance']; }else{ echo "-";}?></td>
            </tr>
          <?php
          $id++;
          }
        }
          ?>
          </tbody>
    </table>
   </div> 
<?php
// end
});
}elseif($_GET['report_name'] === 'balance_by_category'){
  renderReportTable(function() use ($pdo) {
  ?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
        Balance By Category Report
      </h4>
      <div>
        <h4> 
        <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
          <?php
          if(!empty($_GET['category_id'])) {
              $category_id = $_GET['category_id'];
              // Category Name
              $catstmt = $pdo->prepare("SELECT * FROM categories WHERE categories_code='$category_id'");
              $catstmt->execute();
              $catResult= $catstmt->fetch(PDO::FETCH_ASSOC);
              echo " Category Name : ". $catResult['categories_name'];
          }
          ?>
        </h4>
      </div>
    </div> 
    <div class="report-outer">
      <?php
        // One Category's Balance
      if(!empty($_GET['category_id'])) {
        ?>
        <table class="table mt-4 table-hover">
          <thead class="custom-thead">
            <tr>
              <th style="width: 100px">No</th>
              <th>Item Name</th>
              <!-- Stock FOC -->
              <?php 
                if(!empty($_GET['stock_foc'])){
                  if($_GET['stock_foc'] == 'purchase_foc'){
                    ?>
                      <th><?php echo "Purchase FOC"; ?></th>
                    <?php
                      }elseif($_GET['stock_foc'] == 'sale_foc'){
                    ?>
                      <th><?php echo "Sale FOC"; ?></th>
                    <?php
                      }elseif($_GET['stock_foc'] == 'all'){
                    ?>
                    <th>Purchase Foc</th>
                    <th>Sale Foc</th>
                  <?php
                  }
                }
              ?>
              <!-- Return Stock -->
              <?php 
                if(!empty($_GET['return_stock'])){
                  if($_GET['return_stock'] == 'purchase_return'){
                    ?>
                      <th><?php echo "Purchase Return"; ?></th>
                    <?php
                      }elseif($_GET['return_stock'] == 'sale_return'){
                    ?>
                      <th><?php echo "Sale Return"; ?></th>
                    <?php
                      }elseif($_GET['return_stock'] == 'all'){
                    ?>
                    <th>Purchase Return</th>
                    <th>Sale Return</th>
                  <?php
                  }
                }
              ?>
              <!-- Damage Stock  -->
              <?php
              if(!empty($_GET['damage_stock'])){
              ?>
                <th>Damage</th>
              <?php
              }
              ?>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
              <?php
              $category_id = $_GET['category_id'];
              
              $cat_itemstmt = $pdo->prepare("SELECT * FROM item WHERE categories_id='$category_id' ORDER BY id DESC");
              $cat_itemstmt->execute();
              $cat_itemdata = $cat_itemstmt->fetchAll();
              $id = 0;
              foreach($cat_itemdata as $item){
                $item_id = $item['item_id'];
            
                  // stock check & balance
                  $stockstmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' ORDER BY id DESC");
                  $stockstmt->execute();
                  $stockdata = $stockstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Purchase FOC
                  $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase'");
                  $total_purchase_focstmt->execute();
                  $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Sale FOC
                  $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale'");
                  $total_sale_focstmt->execute();
                  $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Purchase Return
                  $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return'");
                  $total_purchase_returnstmt->execute();
                  $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Sale Return
                  $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return'");
                  $total_sale_returnstmt->execute();
                  $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Damage Stock
                  $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage'");
                  $damagestmt->execute();
                  $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

                // }

                if(!empty($stockdata)){
                  $id++;

                  $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                  $itemstmt->execute();
                  $itemResult= $itemstmt->fetch(PDO::FETCH_ASSOC);
                  
                  ?>
                  <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $itemResult['item_name'] ?></td>
                    <!-- Stock FOC -->
                      <?php 
                        if(!empty($_GET['stock_foc'])){
                          if($_GET['stock_foc'] == 'purchase_foc'){
                            ?>
                              <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['stock_foc'] == 'sale_foc'){
                            ?>
                              <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['stock_foc'] == 'all'){
                            ?>
                            <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                            <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                          <?php
                          }
                        }
                      ?>
                      <!-- Stock return -->
                      <?php 
                        if(!empty($_GET['return_stock'])){
                          if($_GET['return_stock'] == 'purchase_return'){
                            ?>
                              <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['return_stock'] == 'sale_return'){
                            ?>
                              <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['return_stock'] == 'all'){
                            ?>
                            <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                            <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                          <?php
                          }
                        }
                      ?>
                      <!-- Damage Stock -->
                      <?php
                        if(!empty($_GET['damage_stock'])){
                          ?>
                          <td class="pl-3"><?php if($damagedata['total_damage'] != 0){ echo $damagedata['total_damage']; }else{ echo "-";} ?></td>
                          <?php
                        }
                      ?>
                    <td><?php echo $stockdata['balance'] ?></td>
                  </tr>
                  <?php
                }
                ?>
                <?php
              }
            ?>
        </tbody>
      </table>
    <?php
      // All categories's balance
      }else{
        $categorystmt = $pdo->prepare("SELECT * FROM categories ORDER BY id DESC");
        $categorystmt->execute();
        $categorydatas = $categorystmt->fetchAll();
        foreach($categorydatas as $categorydata){
          $category_id = $categorydata['categories_code'];
          $category_name = $categorydata['categories_name'];
        ?>
        <h5 class="mt-3"><?php echo $category_name; ?></h5>
        <table class="table table-hover">
          <thead class="custom-thead">
            <tr>
              <th style="width: 100px">No</th>
              <th>Item Name</th>
              <!-- Stock FOC -->
              <?php 
                if(!empty($_GET['stock_foc'])){
                  if($_GET['stock_foc'] == 'purchase_foc'){
                    ?>
                      <th><?php echo "Purchase FOC"; ?></th>
                    <?php
                      }elseif($_GET['stock_foc'] == 'sale_foc'){
                    ?>
                      <th><?php echo "Sale FOC"; ?></th>
                    <?php
                      }elseif($_GET['stock_foc'] == 'all'){
                    ?>
                    <th>Purchase Foc</th>
                    <th>Sale Foc</th>
                  <?php
                  }
                }
              ?>
              <!-- Return Stock -->
              <?php 
                if(!empty($_GET['return_stock'])){
                  if($_GET['return_stock'] == 'purchase_return'){
                    ?>
                      <th><?php echo "Purchase Return"; ?></th>
                    <?php
                      }elseif($_GET['return_stock'] == 'sale_return'){
                    ?>
                      <th><?php echo "Sale Return"; ?></th>
                    <?php
                      }elseif($_GET['return_stock'] == 'all'){
                    ?>
                    <th>Purchase Return</th>
                    <th>Sale Return</th>
                  <?php
                  }
                }
              ?>
              <!-- Damage Stock  -->
              <?php
              if(!empty($_GET['damage_stock'])){
              ?>
                <th>Damage</th>
              <?php
              }
              ?>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
              <?php
              
              $cat_itemstmt = $pdo->prepare("SELECT * FROM item WHERE categories_id='$category_id' ORDER BY id DESC");
              $cat_itemstmt->execute();
              $cat_itemdata = $cat_itemstmt->fetchAll();
              $id = 0;
              foreach($cat_itemdata as $item){
                $item_id = $item['item_id'];
            
                  // stock check & balance
                  $stockstmt = $pdo->prepare("SELECT * FROM stock WHERE item_id='$item_id' AND balance > 0 ORDER BY id DESC");
                  $stockstmt->execute();
                  $stockdata = $stockstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Purchase FOC
                  $total_purchase_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_purchase_foc FROM stock WHERE item_id='$item_id' AND to_from = 'purchase'");
                  $total_purchase_focstmt->execute();
                  $total_purchase_focdata = $total_purchase_focstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Sale FOC
                  $total_sale_focstmt = $pdo->prepare("SELECT SUM(foc_qty) AS total_sale_foc FROM stock WHERE item_id='$item_id' AND to_from = 'sale'");
                  $total_sale_focstmt->execute();
                  $total_sale_focdata = $total_sale_focstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Purchase Return
                  $total_purchase_returnstmt = $pdo->prepare("SELECT SUM(out_qty) AS total_purchase_return FROM stock WHERE item_id='$item_id' AND to_from = 'purchase_return'");
                  $total_purchase_returnstmt->execute();
                  $total_purchase_returndata = $total_purchase_returnstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Total Sale Return
                  $total_sale_returnstmt = $pdo->prepare("SELECT SUM(in_qty) AS total_sale_return FROM stock WHERE item_id='$item_id' AND to_from = 'sale_return'");
                  $total_sale_returnstmt->execute();
                  $total_sale_returndata = $total_sale_returnstmt->fetch(PDO::FETCH_ASSOC);
    
                  // Damage Stock
                  $damagestmt = $pdo->prepare("SELECT SUM(out_qty) AS total_damage FROM stock WHERE item_id='$item_id' AND to_from='damage'");
                  $damagestmt->execute();
                  $damagedata = $damagestmt->fetch(PDO::FETCH_ASSOC);

                // }

                if(!empty($stockdata)){
                  $id++;

                  $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
                  $itemstmt->execute();
                  $itemResult= $itemstmt->fetch(PDO::FETCH_ASSOC);
                  
                  ?>
                  <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $itemResult['item_name'] ?></td>
                    <!-- Stock FOC -->
                      <?php 
                        if(!empty($_GET['stock_foc'])){
                          if($_GET['stock_foc'] == 'purchase_foc'){
                            ?>
                              <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['stock_foc'] == 'sale_foc'){
                            ?>
                              <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['stock_foc'] == 'all'){
                            ?>
                            <td class="pl-3"><?php if($total_purchase_focdata['total_purchase_foc'] != 0){ echo $total_purchase_focdata['total_purchase_foc']; }else{ echo "-";} ?></td>
                            <td class="pl-3"><?php if($total_sale_focdata['total_sale_foc'] != 0){ echo $total_sale_focdata['total_sale_foc']; }else{ echo "-";} ?></td>
                          <?php
                          }
                        }
                      ?>
                      <!-- Stock return -->
                      <?php 
                        if(!empty($_GET['return_stock'])){
                          if($_GET['return_stock'] == 'purchase_return'){
                            ?>
                              <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['return_stock'] == 'sale_return'){
                            ?>
                              <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                            <?php
                              }elseif($_GET['return_stock'] == 'all'){
                            ?>
                            <td class="pl-3"><?php if($total_purchase_returndata['total_purchase_return'] != 0){ echo $total_purchase_returndata['total_purchase_return']; }else{ echo "-";} ?></td>
                            <td class="pl-3"><?php if($total_sale_returndata['total_sale_return'] != 0){ echo $total_sale_returndata['total_sale_return']; }else{ echo "-";} ?></td>
                          <?php
                          }
                        }
                      ?>
                      <!-- Damage Stock -->
                      <?php
                        if(!empty($_GET['damage_stock'])){
                          ?>
                          <td class="pl-3"><?php if($damagedata['total_damage'] != 0){ echo $damagedata['total_damage']; }else{ echo "-";} ?></td>
                          <?php
                        }
                      ?>
                    <td><?php echo $stockdata['balance'] ?></td>
                  </tr>
                  <?php
                }
                ?>
                <?php
              }
            ?>
        </tbody>
      </table>
        <?php
        }
      }
    ?>
  </div>
  </div>
  <?php
  });
}elseif($_GET['report_name'] === 'sales_summary'){
  renderReportTable(function() use ($pdo) {
  ?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
        Sales Summary Report
      </h4>
      <div>
        <h4>
          <?php
            if(!empty($_GET['customer_id'])){
              $customer_id = $_GET['customer_id'];

              $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
              $customerstmt->execute();
              $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);
              echo "Customer : " . $customer['customer_name'];
            }
          ?>
          <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
        </h4>
      </div>
    </div>
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th>No</th>
          <th>Date</th>
          <th>GIN NO</th>
          <?php
            if(empty($_GET['customer_id'])){
          ?>
            <th>Customer Name</th>
          <?php
            }
          ?>
          <th>Item Name</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Discount Amt</th>
          <th>FOC</th>
          <th>Total Amt</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if(!empty($_GET['customer_id'])){
          $customer_id = $_GET['customer_id'];

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $salestmt = $pdo->prepare("SELECT * FROM temp_sale WHERE customer_id='$customer_id' AND date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $salestmt->execute();
            $saledatas = $salestmt->fetchAll();
          }else{
            $salestmt = $pdo->prepare("SELECT * FROM temp_sale WHERE customer_id='$customer_id' ORDER BY id DESC");
            $salestmt->execute();
            $saledatas = $salestmt->fetchAll();
          }

        }else{

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $salestmt = $pdo->prepare("SELECT * FROM temp_sale WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $salestmt->execute();
            $saledatas = $salestmt->fetchAll();
          }else{
            $salestmt = $pdo->prepare("SELECT * FROM temp_sale ORDER BY id DESC");
            $salestmt->execute();
            $saledatas = $salestmt->fetchAll();
          }
        }

          if ($saledatas) {
            $id = 1;
            foreach ($saledatas as $saledata) {
              $temp_sale_id = $saledata['id'];

              // Temp Sale Items
              $temp_sale_itemstmt = $pdo->prepare("SELECT * FROM temp_sale_items WHERE temp_sale_id='$temp_sale_id' ORDER BY id DESC");
              $temp_sale_itemstmt->execute();
              $temp_sale_itemdata = $temp_sale_itemstmt->fetch(PDO::FETCH_ASSOC);

              $item_id = $temp_sale_itemdata['item_id'];
              $customer_id = $saledata['customer_id'];

              // Item Name
              $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
              $itemstmt->execute();
              $item = $itemstmt->fetch(PDO::FETCH_ASSOC);

              // Customer Name
              $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
              $customerstmt->execute();
              $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);
         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo date('d M Y', strtotime($saledata['date']));?></td>
          <td><?php echo $saledata['gin_no'];?></td>
          <?php
            if(empty($_GET['customer_id'])){
          ?>
            <td><?php echo $customer['customer_name'];?></td>
          <?php
            }
          ?>
          <td><?php echo $item['item_name'];?></td>
          <td><?php echo $temp_sale_itemdata['price'];?></td>
          <td><?php echo $temp_sale_itemdata['qty'];?></td>
          <td><?php echo $temp_sale_itemdata['percentage_amount'];?></td>
          <td><?php echo $temp_sale_itemdata['stock_foc'];?></td>
          <td><?php echo $temp_sale_itemdata['amount'];?></td>
          <td><?php echo $saledata['type'];?></td>
        </tr>
        <?php
          $id++;
            }
          }
         ?>
      </tbody>
    </table>
  </div>
  <?php
  });
}elseif($_GET['report_name'] === 'total_sales'){
  renderReportTable(function() use ($pdo) {
?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
        Total Sales Report
      </h4>
      <div>
        <h4> 
        <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
        </h4>
      </div>
    </div>
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th>No</th>
          <th>Customer Name</th>
          <th>Total Cash Sale</th>
          <th>Total Credit Sale</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if(!empty($_GET['customer_id'])){
          $customer_id = $_GET['customer_id'];

          // Customer Name
          $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
          $customerstmt->execute();
          $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);
          
          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_sale WHERE customer_id='$customer_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_sale WHERE customer_id='$customer_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
          }else{
            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_sale WHERE customer_id='$customer_id'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_sale WHERE customer_id='$customer_id'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
          }
          ?>
            <tr>
              <td class="pl-3"><?php echo "1"; ?></td>
              <td class="pl-3"><?php echo $customer['customer_name'];?></td>
              <td class="pl-3"><?php if($total_cashsale['total_amount'] != 0){ echo number_format($total_cashsale['total_amount']); }else{ echo "-"; } ?></td>
              <td class="pl-3"><?php if($total_creditsale['total_amount'] != 0){ echo number_format($total_creditsale['total_amount']); }else{ echo "-"; } ?></td>
            </tr>
          <?php
        }else{

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $customercountstmt = $pdo->prepare("SELECT DISTINCT(customer_id) FROM temp_sale WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $customercountstmt->execute();
            $customercount = $customercountstmt->fetchAll();
          }else{
            $customercountstmt = $pdo->prepare("SELECT DISTINCT(customer_id) FROM temp_sale ORDER BY id DESC");
            $customercountstmt->execute();
            $customercount = $customercountstmt->fetchAll();
          }

          $id = 1;
          foreach($customercount as $customer){
            $customer_id = $customer['customer_id'];

            // Customer Name
            $customerstmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id='$customer_id'");
            $customerstmt->execute();
            $customer = $customerstmt->fetch(PDO::FETCH_ASSOC);

            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_sale WHERE customer_id='$customer_id'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_sale WHERE customer_id='$customer_id'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <tr>
              <td class="pl-3"><?php echo $id; ?></td>
              <td class="pl-3"><?php echo $customer['customer_name'];?></td>
              <td class="pl-3"><?php if($total_cashsale['total_amount'] != 0){ echo number_format($total_cashsale['total_amount']); }else{ echo "-"; } ?></td>
              <td class="pl-3"><?php if($total_creditsale['total_amount'] != 0){ echo number_format($total_creditsale['total_amount']); }else{ echo "-"; } ?></td>
            </tr>
            <?php
            $id++;
          }
        }
         ?>
      </tbody>
    </table>
  </div>
<?php
  });
}elseif($_GET['report_name'] === 'purchase_summary'){
  renderReportTable(function() use ($pdo) {
  ?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
        Purchase Summary Report
      </h4>
      <div>
        <h4>
          <?php
            if(!empty($_GET['supplier_id'])){
              $supplier_id = $_GET['supplier_id'];

              $supplierstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
              $supplierstmt->execute();
              $supplier = $supplierstmt->fetch(PDO::FETCH_ASSOC);
              echo "Supplier : " . $supplier['supplier_name'];
            }
          ?>
          <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
        </h4>
      </div>
    </div>
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th>No</th>
          <th>Date</th>
          <th>GRN NO</th>
          <?php
            if(empty($_GET['supplier_id'])){
          ?>
            <th>Supplier Name</th>
          <?php
            }
          ?>
          <th>Item Name</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Discount Amt</th>
          <th>FOC</th>
          <th>Total Amt</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if(!empty($_GET['supplier_id'])){
          $supplier_id = $_GET['supplier_id'];

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE supplier_id='$supplier_id' AND date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $purchasestmt->execute();
            $purchasedatas = $purchasestmt->fetchAll();
          }else{
            $purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE supplier_id='$supplier_id' ORDER BY id DESC");
            $purchasestmt->execute();
            $purchasedatas = $purchasestmt->fetchAll();
          }

        }else{

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $purchasestmt->execute();
            $purchasedatas = $purchasestmt->fetchAll();
          }else{
            $purchasestmt = $pdo->prepare("SELECT * FROM temp_purchase ORDER BY id DESC");
            $purchasestmt->execute();
            $purchasedatas = $purchasestmt->fetchAll();
          }
        }

          if ($purchasedatas) {
            $id = 1;
            foreach ($purchasedatas as $purchasedata) {
              $id_temp_purchase = $purchasedata['id'];

              // Temp Purchase Items
              $temp_purchase_itemstmt = $pdo->prepare("SELECT * FROM temp_purchase_items WHERE temp_purchase_id='$id_temp_purchase' ORDER BY id DESC");
              $temp_purchase_itemstmt->execute();
              $temp_purchase_itemdata = $temp_purchase_itemstmt->fetch(PDO::FETCH_ASSOC);

              $item_id = $temp_purchase_itemdata['item_id'];
              $supplier_id = $purchasedata['supplier_id'];

              // Item Name
              $itemstmt = $pdo->prepare("SELECT * FROM item WHERE item_id='$item_id'");
              $itemstmt->execute();
              $item = $itemstmt->fetch(PDO::FETCH_ASSOC);

              // Customer Name
              $supplierstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
              $supplierstmt->execute();
              $supplier = $supplierstmt->fetch(PDO::FETCH_ASSOC);
         ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo date('d M Y', strtotime($purchasedata['date']));?></td>
          <td><?php echo $purchasedata['grn_no'];?></td>
          <?php
            if(empty($_GET['supplier_id'])){
          ?>
            <td><?php echo $supplier['supplier_name'];?></td>
          <?php
            }
          ?>
          <td><?php echo $item['item_name'];?></td>
          <td><?php echo $temp_purchase_itemdata['price'];?></td>
          <td><?php echo $temp_purchase_itemdata['qty'];?></td>
          <td><?php echo $temp_purchase_itemdata['percentage_amount'];?></td>
          <td><?php echo $temp_purchase_itemdata['stock_foc'];?></td>
          <td><?php echo $temp_purchase_itemdata['amount'];?></td>
          <td><?php echo $purchasedata['type'];?></td>
        </tr>
        <?php
          $id++;
            }
          }
         ?>
      </tbody>
    </table>
  </div>
  <?php
  });
}elseif($_GET['report_name'] === 'total_purchase'){
  renderReportTable(function() use ($pdo) {
  ?>
  <div class="col-md-12 px-4 mt-4">
    <div class="d-flex justify-content-between">
      <h4>
        Total Purchase Report
      </h4>
      <div>
        <h4> 
        <?php
            if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
              if($_GET['start_date'] === $_GET['end_date']){
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
              }else{
                echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
              }
            }
          ?>
        </h4>
      </div>
    </div>
    <table class="table mt-4 table-hover">
      <thead class="custom-thead">
        <tr>
          <th>No</th>
          <th>Supplier Name</th>
          <th>Total Cash Purchase</th>
          <th>Total Credit Purchase</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if(!empty($_GET['supplier_id'])){
          $supplier_id = $_GET['supplier_id'];

          // supplier Name
          $supplierstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
          $supplierstmt->execute();
          $supplier = $supplierstmt->fetch(PDO::FETCH_ASSOC);
          
          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_purchase WHERE supplier_id='$supplier_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_purchase WHERE supplier_id='$supplier_id' AND date BETWEEN '$start_date' AND '$end_date'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
          }else{
            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_purchase WHERE supplier_id='$supplier_id'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_purchase WHERE supplier_id='$supplier_id'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
          }
          ?>
            <tr>
              <td class="pl-3"><?php echo "1"; ?></td>
              <td class="pl-3"><?php echo $supplier['supplier_name'];?></td>
              <td class="pl-3"><?php if($total_cashsale['total_amount'] != 0){ echo number_format($total_cashsale['total_amount']); }else{ echo "-"; } ?></td>
              <td class="pl-3"><?php if($total_creditsale['total_amount'] != 0){ echo number_format($total_creditsale['total_amount']); }else{ echo "-"; } ?></td>
            </tr>
          <?php
        }else{

          if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $suppliercountstmt = $pdo->prepare("SELECT DISTINCT(supplier_id) FROM temp_purchase WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC");
            $suppliercountstmt->execute();
            $suppliercount = $suppliercountstmt->fetchAll();
          }else{
            $suppliercountstmt = $pdo->prepare("SELECT DISTINCT(supplier_id) FROM temp_purchase ORDER BY id DESC");
            $suppliercountstmt->execute();
            $suppliercount = $suppliercountstmt->fetchAll();
          }

          $id = 1;
          foreach($suppliercount as $supplier){
            $supplier_id = $supplier['supplier_id'];

            // supplier Name
            $supplierstmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id='$supplier_id'");
            $supplierstmt->execute();
            $supplier = $supplierstmt->fetch(PDO::FETCH_ASSOC);

            $total_cashsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM cash_purchase WHERE supplier_id='$supplier_id'");
            $total_cashsalestmt->execute();
            $total_cashsale = $total_cashsalestmt->fetch(PDO::FETCH_ASSOC);

            $total_creditsalestmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM credit_purchase WHERE supplier_id='$supplier_id'");
            $total_creditsalestmt->execute();
            $total_creditsale = $total_creditsalestmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <tr>
              <td class="pl-3"><?php echo $id; ?></td>
              <td class="pl-3"><?php echo $supplier['supplier_name'];?></td>
              <td class="pl-3"><?php if($total_cashsale['total_amount'] != 0){ echo number_format($total_cashsale['total_amount']); }else{ echo "-"; } ?></td>
              <td class="pl-3"><?php if($total_creditsale['total_amount'] != 0){ echo number_format($total_creditsale['total_amount']); }else{ echo "-"; } ?></td>
            </tr>
            <?php
            $id++;
          }
        }
         ?>
      </tbody>
    </table>
  </div>
<?php
  });
}elseif($_GET['report_name'] === 'top_customer'){
  renderReportTable(function() use ($pdo) {
  ?>
    <div class="col-md-12 px-4 mt-4">
      <div class="d-flex justify-content-between">
        <h4>
          Royal Customer Report
        </h4>
        <div>
          <h4> 
          <?php
              if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
                if($_GET['start_date'] === $_GET['end_date']){
                  echo "Date : " . date('d-m-y', strtotime($_GET['start_date']));
                }else{
                  echo "Date : " . date('d-m-y', strtotime($_GET['start_date'])) ." To ". date('d-m-y', strtotime($_GET['end_date']));
                }
              }
            ?>
          </h4>
        </div>
      </div>
      <table class="table mt-4 table-hover">
        <thead class="custom-thead">
            <tr>
              <th>Id</th>
              <th>Customer Name</th>
              <th>Total Sale Amount</th>
              <th>Total Sale Time</th>
            </tr>
        </thead>
        <tbody>
          <?php
           $topCustomerstmt = $pdo->prepare("
                SELECT 
                    t.customer_id,
                    c.customer_name,
                    SUM(ti.qty * ti.price) AS total_spent
                FROM temp_sale_items ti
                JOIN temp_sale t ON ti.temp_sale_id = t.id
                JOIN customer c ON t.customer_id = c.customer_id
                GROUP BY t.customer_id, c.customer_name
                ORDER BY total_spent DESC
                LIMIT 1
            ");
          $topCustomerstmt->execute();
          $topCustomer = $topCustomerstmt->fetch(PDO::FETCH_ASSOC);

          $customer_id = $topCustomer['customer_id'];


          // Total Sale Amount
          // Total Cash Sale Amount
          $totalCashSaleAmountstmt = $pdo->prepare("SELECT SUM(amount) AS total_sale_amount FROM cash_sale WHERE customer_id='$customer_id'");
          $totalCashSaleAmountstmt->execute();
          $totalCashSaleAmount = $totalCashSaleAmountstmt->fetch(PDO::FETCH_ASSOC);

          // Total Cash Sale Amount
          $totalCreditSaleAmountstmt = $pdo->prepare("SELECT SUM(amount) AS total_sale_amount FROM credit_sale WHERE customer_id='$customer_id'");
          $totalCreditSaleAmountstmt->execute();
          $totalCreditSaleAmount = $totalCreditSaleAmountstmt->fetch(PDO::FETCH_ASSOC);

          $total_sale_amount = $totalCashSaleAmount['total_sale_amount'] + $totalCreditSaleAmount['total_sale_amount'];

          // Total Purchase Times
          $totalPurchaseTimesstmt = $pdo->prepare("SELECT COUNT(*) AS total_purchase_times FROM temp_sale WHERE customer_id='$customer_id'");
          $totalPurchaseTimesstmt->execute();
          $totalPurchaseTimes = $totalPurchaseTimesstmt->fetch(PDO::FETCH_ASSOC);


          ?>
          <tr>
            <td class="pl-3"><?php echo "1"; ?></td>
            <td class="pl-3"><?php echo $topCustomer['customer_name']; ?></td>
            <td class="pl-3"><?php echo number_format($total_sale_amount); ?></td>
            <td class="pl-3"><?php echo number_format($totalPurchaseTimes['total_purchase_times']); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  <?php
  });
}
?>
</div>
<?php //include 'footer.html'; ?>
