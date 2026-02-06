<!DOCTYPE html>
<?php
// session_start();
// require '../Config/common.php';
$current_page = basename($_SERVER['PHP_SELF']);
require_once 'permission.php';    // contains hasPermission() helper

// Define treeview pages
$purchase_pages = ['purchase_order.php', 'add_purchase.php', 'purchase.php', 'purchase_return.php', 'purchase_order_detail.php'];
$sale_pages = ['sale_order.php', 'sale.php', 'sale_return.php', 'add_sale.php', 'sale_detail.php'];


?>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>Zar Ni Min Nwe | Business Control System</title>

  <link href="bootstrap-4.0.0-dist/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
  <script src="chart/chart.js"></script>

  <!-- Bootstrap CDN -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

  <script src="sweetalert/sweetalert.min.js"></script>
</head>
<style>
  .logout {
    border-radius: 200px;
  }

  .dropdown:hover>.dropdown-menu {
    display: block;
  }

  .dropdown>.dropdown-toggle:active {
    pointer-events: none;
  }

  .a_href {
    text-decoration: none;
  }

  .drome {
    width: 234px;
    background-color: gray;
  }

  /* Active sidebar link */
  .nav-sidebar .nav-link.active {
    background-color: rgba(105, 173, 31, 1) !important;
    color: #fff !important;
  }

  /* Active link hover effect (optional) */
  .nav-sidebar .nav-link.active:hover {
    background-color: rgba(105, 173, 31, 1) !important;
    color: #fff !important;
  }

  /* Treeview menu active link */
  .nav-sidebar .nav-treeview .nav-link.active {
    background-color: rgba(255, 255, 255, 0.3) !important;
    color: #fff !important;
  }

  /* Optional: change icon color for active link */
  .nav-sidebar .nav-link.active i,
  .nav-sidebar .nav-treeview .nav-link.active i {
    color: #fff !important;
  }
  .card-title {
    font-size: 25px;
    font-weight: 500;
  }

  .btn-purple {
    background-color: rgba(105, 173, 31, 1) !important;
  }

  .btn-blue {
    background-color: lightblue;
  }

  .green {
    color: rgba(105, 173, 31, 1) !important;
  }

  .outer {
    overflow-y: auto;
    height: 500px;
  }

  .outer {
    width: 100%;
    -layout: fixed;
  }

  .outer th {
    text-align: left;
    top: 0;
    position: sticky;
    background-color: white;
  }

  .left-sider .main-sidebar {
    position: fixed !important;
    /* Fix to viewport */
    top: 0;
    left: 0;
    height: 108.7vh !important;
    /* Full screen height */
    overflow-y: auto !important;
    /* Scroll if menu is long */
    z-index: 1030;
    /* Stay above content */
  }

  /* Keep the user panel sticky */
  .main-sidebar .user-panel {
    position: sticky;
    top: 0;
    /* Stick to the top */
    z-index: 1050;
    /* Above other sidebar items */
    background-color: #343a40;
    /* Match sidebar background */
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .title {
    font-size: 23px;
  }

  /* table > thead{
  background-color: #d0f0c0;
} */
.table thead.custom-thead th {
background-color: #d0f0c0;
}

  .table td,
  .table tr {
    padding: 5px;
  }

  .tooltip-square {
    width: 25px;
    height: 25px;
    border-radius: 4px;
    position: relative;
    /* for tooltip positioning */
    cursor: pointer;
  }

  .tooltip-text {
    visibility: hidden;
    width: max-content;
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 4px 8px;
    border-radius: 4px;
    position: absolute;
    bottom: 125%;
    /* above the square */
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    font-size: 12px;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.3s;
  }

  /* small arrow */
  .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    /* bottom of tooltip */
    left: 50%;
    transform: translateX(-50%);
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
  }

  .tooltip-square:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
  }

  .bg-lightgreen {
    background-color: #d0f0c0;
  }

  .drawer {
    position: fixed;
    top: 0;
    right: -400px;
    /* hidden */
    width: 400px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.2);
    transition: right 0.3s ease;
    z-index: 1050;
    overflow-y: auto;
    padding: 1rem;
  }

  .drawer.open {
    right: 0;
  }

  .drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .drawer-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
  }

  .drawer-backdrop.show {
    display: block;
  }

  .drawer.show {
    right: 0;
  }

  .drawer-header h5 {
    font-size: 1.1rem;
    color: #343a40;
  }

  .drawer-body {
    max-height: calc(100% - 60px);
    overflow-y: auto;
  }

  .btn-close {
    background: none;
    border: none;
  }

  /* Make the filter panel sticky */
  /* Right filter panel fixed */
  .filter-box {
    position: fixed;
    right: 0;
    /* stick to right edge */
    top: 10px;
    /* adjust depending on your header height */
    width: 25%;
    /* match your grid size */
    height: calc(120vh - 100px);
    /* full height minus header */
    /* padding: 15px; */
    overflow-y: auto;
    /* scroll inside if content is long */
    z-index: 999;
    /* make sure it’s above content */
  }

  .report-sidebar li.fw-bold {
    font-weight: bold;
    position: relative;
    padding-right: 10px;
    /* space between text and line */
    display: flex;
    align-items: center;
    font-size: 18px;
  }

  .fs-6 {
    font-size: 15px;
  }

  .report-sidebar li.fw-bold::after {
    content: "";
    width: 100%;
    /* adjust this to make it shorter/longer */
    height: 1px;
    background-color: #dde;
    margin-left: 10px;
  }

  .flex-fill {
    width: 100% !important;
    border-radius: 0px;
  }

  .report-sidebar a {
    transition: 0.3s;
  }

  .report-sidebar a.active {
    background-color: #055ae3 !important;
    /* font-weight: bold; */
    border-radius: 3px;
    padding: 4px 8px;
    display: inline-block;
    color: white !important;
  }

  form.row.g-2 .report-input {
    padding: 0px 5px;
    /* vertical | horizontal */
    font-size: 14px;
    /* optional, control text size */
    height: 30px;
  }

  .filter-group {
    border-top-left-radius: 12px;
    padding: 20px;
    background-color: #f8f9fa;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    position: relative;
    height: 90%;
  }

  .report-card {
    text-decoration: none;
    color: inherit;
  }

  .report-card .card {
    border-radius: 12px;
    transition: all 0.3s ease;
  }

  .report-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  }

  .report-card.active .card {
    border: 2px solid #0d6efd;
    background-color: #e7f0ff;
  }

  /* Hover: simple background color change */
  .btn.flex-fill:hover {
    background-color: #055ae3 !important;
    color: #fff;
    /* text/icon turns white */
  }

  .remove-row-btn {
    margin-top: 8px;
    /* adjust spacing so it aligns nicely */
  }

  .btn-group .btn.active {
    background-color: #4e73df;
    /* same as your chart line */
    color: #fff;
  }

  .metric-card {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 120px;
    transition: transform 0.2s;
  }

  .metric-card:hover {
    transform: translateY(-3px);
  }

  .metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, #4e73df, #1cc88a, #4e73df);
    background-size: 200% 100%;
    /* make it larger for sliding */
    animation: slideGradient 3s linear infinite;
    /* continuous animation */
  }

  @keyframes slideGradient {
    0% {
      background-position: 0% 0;
    }

    100% {
      background-position: 200% 0;
    }
  }


  .metric-label {
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 8px;
  }

  .metric-value {
    font-size: 23px;
    font-weight: 700;
    color: #111;
  }

  .metrics-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  @media (max-width: 991px) {

    .col-8,
    .col-3 {
      flex: 0 0 100%;
      max-width: 100%;
    }
  }

  .quick-link {
    border-radius: 12px;
    transition: all 0.2s ease-in-out;
  }

  .quick-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  }

  .link {
    color: black;
  }

  .link:hover {
    text-decoration: underline;
  }

  .custom-input {
    width: 100%;
    padding-left: 10px;
    height: 35px;
    border: none;
    outline: none;
  }

  .custom-input:focus {
    outline: 1px solid rgba(105, 173, 31, 1);
  }

  .no-padding {
    padding: 0px !important;
  }

  td.remove-row-btn {
    cursor: default !important;
  }

  .table-hover tbody tr:hover td.remove-row-btn {
    background-color: transparent !important;
    cursor: default !important;
  }

  label {
    font-size: 15px;
  }

  .form-control {
    height: 35px;
    font-size: 14px;
    background-color: rgba(255, 255, 255, 0.5);
  }
</style>

<body class="hold-transition sidebar-mini">

  <div class="wrapper left-sider">

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

      <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <div style="color:white;" class="image">
            <span class="mt-2 title"><b>ZARLI MIN NWE</b></span><br>
            <span>Business Control System</span>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <!-- <div class="outer"> -->
        <!-- Sidebar Menu -->
        <nav class="mt-3">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">

            <li class="nav-item">
              <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <svg style="margin-left:6px;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-house-fill ms-2" viewBox="0 0 16 16">
                  <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                  <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z" />
                </svg>
                <p style="margin-left:8px;">Home</p>
              </a>
            </li>
            <?php
            $manage_pages = [
              'roles.php' => 'roles.manage',
              'users.php' => 'users.manage',
              'role_permissions.php' => 'permissions.manage',
            ];

            $manage_active = in_array($current_page, array_keys($manage_pages));

            if (array_filter($manage_pages, fn($perm) => hasPermission($perm))): ?>
              <li class="nav-item has-treeview <?php echo $manage_active ? 'menu-open' : ''; ?>">
                <a href="#" class="nav-link <?php echo $manage_active ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-user-shield"></i>
                  <p>
                    Access Control
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php foreach ($manage_pages as $file => $perm): ?>
                    <?php if (hasPermission($perm)): ?>
                      <li class="nav-item">
                        <a href="<?php echo $file; ?>" style="padding-left: 30px;" class="nav-link <?php echo $current_page == $file ? 'active' : ''; ?>">
                          <i class="far fa-circle nav-icon"></i>
                          <p><?php echo ucwords(str_replace(['.php', '_'], ['', ' '], $file)); ?></p>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endif; ?>




            <?php
              $config_pages = [
                'category.php' => 'category.view',
                'item.php' => 'item.view',
                'supplier.php' => 'supplier.view',
                'customer.php' => 'customer.view',
              ];

              $config_child_pages = [
                'category.php' => ['category_add.php', 'category_edit.php'],
                'item.php'     => ['item_add.php', 'item_edit.php'],
                'supplier.php' => ['supplier_add.php', 'supplier_edit.php'],
                'customer.php' => ['customer_add.php', 'customer_edit.php'],
              ];

              $config_page_keys = array_keys($config_pages);

              $config_active = in_array($current_page, $config_page_keys)
                            || in_array($current_page, array_merge(...array_values($config_child_pages)));

              if (array_filter($config_pages, fn($perm) => hasPermission($perm))): ?>
                <li class="nav-item has-treeview <?php echo $config_active ? 'menu-open' : ''; ?>">
                  <a href="#" class="nav-link <?php echo $config_active ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-cogs mr-1 ml-1"></i>
                    <p>
                      Configurations
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <?php foreach ($config_pages as $file => $perm): ?>
                      <?php if (hasPermission($perm)): ?>
                        <li class="nav-item">
                          <a href="<?php echo $file; ?>" style="padding-left: 30px;" class="nav-link
                            <?php
                              $isActive = $current_page == $file;
                              if (isset($config_child_pages[$file]) && in_array($current_page, $config_child_pages[$file])) {
                                $isActive = true;
                              }
                              echo $isActive ? 'active' : '';
                            ?>
                          ">
                            <i class="far fa-circle nav-icon"></i>
                            <p><?php echo ucwords(str_replace(['.php', '_'], ['', ' '], $file)); ?></p>
                          </a>
                        </li>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </ul>
                </li>
              <?php endif; ?>


            <?php
              $purchase_pages_perm = [
                'purchase_order.php'  => 'purchase.order.view',
                'purchase.php'        => 'purchase.view',
                'purchase_return.php' => 'purchase.return'
              ];

              $child_pages = [
                'purchase_order.php' => ['purchase_order_detail.php'],
                'purchase.php'       => ['add_purchase.php', 'purchase_detail.php', 'approve_purchase.php'],
              ];

              $purchase_pages = array_keys($purchase_pages_perm);

              $purchase_active = in_array($current_page, $purchase_pages) ||
                                in_array($current_page, array_merge(...array_values($child_pages)));

              if (array_filter($purchase_pages_perm, fn($perm) => hasPermission($perm))): ?>
                <li class="nav-item has-treeview <?php echo $purchase_active ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $purchase_active ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-shopping-bag mr-2"></i>
                        <p>Purchase<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php foreach ($purchase_pages_perm as $file => $perm): ?>
                            <?php if (hasPermission($perm)): ?>
                                <li class="nav-item">
                                    <a href="<?php echo $file; ?>" style="padding-left: 30px;" class="nav-link
                                      <?php
                                        $isActive = $current_page == $file;
                                        if (isset($child_pages[$file]) && in_array($current_page, $child_pages[$file])) {
                                          $isActive = true;
                                        }
                                        echo $isActive ? 'active' : '';
                                      ?>
                                    ">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p><?php echo ucwords(str_replace(['.php', '_'], ['', ' '], $file)); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
              <?php endif; ?>



            <?php
              $sale_pages_perm = [
                'sale_order.php'  => 'sale.order.view',
                'sale.php'        => 'sale.create',
                'sale_return.php' => 'sale.return'
              ];

              $sale_child_pages = [
                'sale_order.php' => ['sale_order_detail.php'],
                'sale.php'       => ['add_sale.php', 'sale_detail.php', 'approve_sale.php'],
              ];

              $sale_pages = array_keys($sale_pages_perm);

              $sale_active = in_array($current_page, $sale_pages) ||
                            in_array($current_page, array_merge(...array_values($sale_child_pages)));

              if (array_filter($sale_pages_perm, fn($perm) => hasPermission($perm))): ?>
                <li class="nav-item has-treeview <?php echo $sale_active ? 'menu-open' : ''; ?>">
                  <a href="#" class="nav-link <?php echo $sale_active ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-shopping-cart mr-2"></i>
                    <p>Sale<i class="right fas fa-angle-left"></i></p>
                  </a>
                  <ul class="nav nav-treeview">
                    <?php foreach ($sale_pages_perm as $file => $perm): ?>
                      <?php if (hasPermission($perm)): ?>
                        <li class="nav-item">
                          <a href="<?php echo $file; ?>" style="padding-left: 30px;" class="nav-link
                            <?php
                              $isActive = $current_page == $file;
                              if (isset($sale_child_pages[$file]) && in_array($current_page, $sale_child_pages[$file])) {
                                $isActive = true;
                              }
                              echo $isActive ? 'active' : '';
                            ?>
                          ">
                            <i class="far fa-circle nav-icon"></i>
                            <p><?php echo ucwords(str_replace(['.php', '_'], ['', ' '], $file)); ?></p>
                          </a>
                        </li>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </ul>
                </li>
              <?php endif; ?>


            <?php
            $account_pages_perm = [
              'account_payable.php' => 'account.payable.view',
              'account_receivable.php' => 'account.receivable.view',
            ];

            $accounting_child_pages = [
              'account_payable.php' => [
                  'account_payable_detail.php',
                  'account_payable_detail_per_voucher.php'
              ],
              'account_receivable.php' => [
                  'account_receivable_detail.php',
                  'account_receivable_detail_per_voucher.php'
              ]
            ];

            $account_active =
                in_array($current_page, array_keys($account_pages_perm))
                || in_array($current_page, array_merge(...array_values($accounting_child_pages)));

            if (array_filter($account_pages_perm, fn($perm) => hasPermission($perm))): ?>
              <li class="nav-item has-treeview <?php echo $account_active ? 'menu-open' : ''; ?>">
                <a href="#" class="nav-link <?php echo $account_active ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-money-bill-wave mr-2"></i>
                  <p>Accounting<i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <?php foreach ($account_pages_perm as $file => $perm): ?>
                    <?php if (hasPermission($perm)): ?>
                      <li class="nav-item">
                        <a href="<?php echo $file; ?>" style="padding-left: 30px;" class="nav-link 
                          <?php
                            $isActive = $current_page == $file;
                            if (isset($accounting_child_pages[$file]) 
                                && in_array($current_page, $accounting_child_pages[$file])) {
                                $isActive = true;
                            }
                            echo $isActive ? 'active' : '';
                          ?>
                        ">
                          <i class="far fa-circle nav-icon"></i>
                          <p><?php echo ucwords(str_replace(['.php', '_'], ['', ' '], $file)); ?></p>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endif; ?>


            <?php if (hasPermission('stock.manage')): ?>
              <li class="nav-item">
                <a href="stock_control.php" class="nav-link 
<?php 
  echo ($current_page == 'stock_control.php' || $current_page == 'stock_detail.php') ? 'active' : ''; 
?>">

                  <i class="nav-icon fas fa-box"></i>
                  <p style="margin-left:8px;">Stock Control</p>
                </a>
              </li>
            <?php endif; ?>

            <?php if (hasPermission('report.view')): ?>
              <li class="nav-item">
                <a href="choose_report.php" class="nav-link <?php echo $current_page == 'choose_report.php' ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-calendar-plus"></i>
                  <p style="margin-left:8px;">Reporting</p>
                </a>
              </li>
            <?php endif; ?>

            <?php if (hasPermission('company.manage')): ?>
              <li class="nav-item">
                <a href="company.php" class="nav-link <?php echo $current_page == 'company.php' ? 'active' : ''; ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-buildings-fill mr-1 ml-1" viewBox="0 0 16 16">
                    <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z" />
                  </svg>
                  <p style="margin-left:8px;">My Company</p>
                </a>
              </li>
            <?php endif; ?>

            <li class="nav-item">
              <a href="logout.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="ml-1 mr-1 bi bi-door-open-fill" viewBox="0 0 16 16">
                  <path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1" />
                </svg>
                <p style="margin-left:8px;">Logout</p>
              </a>
            </li>

          </ul>
        </nav>

        <!-- </div> -->
        <div class="sidebar-footer mt-4 text-center"
            style="font-size:12px; color:#aaa; padding:0px;">
          © <?php echo date('Y'); ?> ProTech. Powered by ProTech Solutions.

        </div>
      </div>

    </aside>

    <div class="content-wrapper">
      <div class="content-header" style="padding: 0px !important;">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 text-dark"></h1>
            </div>
          </div>
        </div>
      </div>