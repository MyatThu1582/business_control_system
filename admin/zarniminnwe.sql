-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 06:01 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zarniminnwe`
--

-- --------------------------------------------------------

--
-- Table structure for table `cash_purchase`
--

CREATE TABLE `cash_purchase` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `grn_no` int(11) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cash_purchase`
--

INSERT INTO `cash_purchase` (`id`, `date`, `grn_no`, `supplier_id`, `item_id`, `qty`, `po_no`, `amount`) VALUES
(1, '2026-02-05', 0, 'SUP-000', 'ITM-006', 2, '', 5000),
(2, '2026-02-01', 0, 'SUP-000', 'ITM-002', 20, '', 400),
(3, '2026-02-01', 0, 'SUP-000', 'ITM-001', 12, '', 60000),
(4, '2026-02-05', 0, 'SUP-000', 'ITM-003', 20, '', 10000),
(5, '2026-02-01', 0, 'SUP-000', 'ITM-004', 4, '', 5200),
(6, '2026-02-01', 0, 'SUP-000', 'ITM-005', 100, '', 20000),
(7, '2026-02-05', 222, 'SUP-000', 'ITM-006', 5, '', 12500),
(8, '2026-02-05', 223, 'SUP-000', 'ITM-004', 100, '', 130000),
(9, '2026-02-05', 22231, 'SUP-000', 'ITM-001', 100, '', 500000);

-- --------------------------------------------------------

--
-- Table structure for table `cash_sale`
--

CREATE TABLE `cash_sale` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `gin_no` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cash_sale`
--

INSERT INTO `cash_sale` (`id`, `date`, `gin_no`, `customer_id`, `item_id`, `qty`, `amount`) VALUES
(1, '2026-02-02', 0, 'CUS-000', 'ITM-006', 1, 2900),
(2, '2026-02-03', 0, 'CUS-000', 'ITM-001', 6, 33000),
(3, '2026-02-03', 0, 'CUS-000', 'ITM-002', 10, 7000),
(4, '2026-02-03', 0, 'CUS-000', 'ITM-003', 7, 4200),
(5, '2026-02-03', 0, 'CUS-000', 'ITM-004', 1, 1500),
(6, '2026-02-05', 0, 'CUS-000', 'ITM-005', 5, 1250),
(7, '2026-02-06', 1143, 'CUS-000', 'ITM-004', 20, 30000),
(8, '2026-02-08', 1111, 'CUS-000', 'ITM-004', 7, 10500);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `categories_code` varchar(255) NOT NULL,
  `categories_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `categories_code`, `categories_name`) VALUES
(1, 'CAT-001', 'Cream'),
(3, 'CAT-003', 'Eye Drop'),
(4, 'CAT-004', 'Injection'),
(5, 'CAT-005', 'Medical Equitement'),
(6, 'CAT-006', 'Medicine');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street_name` varchar(255) NOT NULL,
  `building_no` varchar(255) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `bank_account` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `name`, `street_name`, `building_no`, `phone`, `email`, `city`, `country`, `bank_account`, `logo`) VALUES
(1, 'Golden Future Co.,Ltd', 'Insein Road', 'No.45', 9795799559, 'contact@goldenfuture.com', 'Yangon', 'Myanmar', 'CB-0044556677', 'transparent-Photoroom (2).png');

-- --------------------------------------------------------

--
-- Table structure for table `credit_purchase`
--

CREATE TABLE `credit_purchase` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `grn_no` int(11) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_sale`
--

CREATE TABLE `credit_sale` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `gin_no` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `credit_sale`
--

INSERT INTO `credit_sale` (`id`, `date`, `gin_no`, `customer_id`, `item_id`, `qty`, `amount`) VALUES
(1, '2026-02-06', 3529541, 'CUS-000', 'ITM-004', 25, 37500);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `customer_id` varchar(100) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` int(11) NOT NULL,
  `customer_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `customer_id`, `customer_name`, `customer_phone`, `customer_address`) VALUES
(4, 'CUS-001', 'Aung Min', 912345671, 'Yangon'),
(5, 'CUS-002', 'Mya Thandar', 923456782, 'Mandalay'),
(6, 'CUS-003', 'Htet Htet', 934567893, 'Nay Pyi Taw'),
(7, 'CUS-004', 'Ko Ko', 945678904, 'Yangon'),
(8, 'CUS-005', 'Moe Moe', 956789015, 'Bago'),
(9, 'CUS-006', 'Ei Mon', 967890126, 'Taunggyi'),
(10, 'CUS-007', 'Zaw Zaw', 978901237, 'Yangon'),
(11, 'CUS-008', 'Thiri', 989012348, 'Pathein'),
(12, 'CUS-009', 'Swe Zin', 911122239, 'Mawlamyine'),
(13, 'CUS-010', 'Min Thu', 922233340, 'Myitkyina'),
(14, 'CUS-011', 'Nandar', 933344451, 'Yangon'),
(15, 'CUS-012', 'San Hlaing', 944455562, 'Hinthada'),
(16, 'CUS-013', 'Khin Zaw', 955566673, 'Sittwe'),
(17, 'CUS-014', 'Thant Zin', 966677784, 'Nay Pyi Taw'),
(18, 'CUS-015', 'May Khin', 977788895, 'Pyay'),
(19, 'CUS-016', 'Hla Hla', 988899906, 'Yangon'),
(20, 'CUS-017', 'Kyaw Kyaw', 919988777, 'Mandalay'),
(21, 'CUS-018', 'Ei Ei', 928877668, 'Lashio'),
(22, 'CUS-019', 'Aye Aye', 937766559, 'Meiktila'),
(23, 'CUS-020', 'Hnin Hnin', 9466554, 'Yangon'),
(24, 'CUS-000', 'Default Customer', 912345671, 'Yangon');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `id` int(11) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `categories_id` varchar(255) NOT NULL,
  `original_price` int(11) NOT NULL,
  `selling_price` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id`, `item_id`, `item_name`, `categories_id`, `original_price`, `selling_price`, `reorder_level`, `item_image`, `expiry_date`, `location`) VALUES
(1, 'ITM-001', 'Essence of chicken', 'CAT-006', 5000, 5500, 5, '', NULL, NULL),
(2, 'ITM-002', 'Albendazole', 'CAT-006', 20, 700, 20, '', NULL, NULL),
(3, 'ITM-003', 'Clarithromucin 250mg', 'CAT-006', 500, 600, 20, '', NULL, NULL),
(4, 'ITM-004', 'Mebendazle 500mg', 'CAT-006', 1300, 1500, 8, '', NULL, NULL),
(5, 'ITM-005', 'Erythromycin Stearate -250mg', 'CAT-006', 200, 250, 100, '', NULL, NULL),
(6, 'ITM-006', 'a ba hta inhlar (big)', 'CAT-006', 2500, 2900, 5, '1770349930_abahta.jfif', NULL, NULL),
(8, 'ITM-007', 'Testing', 'CAT-004', 120, 150, 30, '1770434542_images (2).jfif', '2027-02-01', 'Rack A / Section 5');

-- --------------------------------------------------------

--
-- Table structure for table `payable`
--

CREATE TABLE `payable` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `grn_no` varchar(255) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `paid` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `asc_id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment_no` varchar(100) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `remark` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `permission_key`, `description`) VALUES
(11, 'roles.manage', 'roles.manage', 'Manage Roles (roles.php)'),
(12, 'users.manage', 'users.manage', 'Manage Users (users.php)'),
(13, 'permissions.manage', 'permissions.manage', 'Manage Permissions (role_permissions.php)'),
(14, 'category.view', 'category.view', 'View Categories (category.php)'),
(15, 'item.view', 'item.view', 'View Items (item.php)'),
(16, 'supplier.view', 'supplier.view', 'View Suppliers (supplier.php)'),
(17, 'customer.view', 'customer.view', 'View Customers (customer.php)'),
(18, 'purchase.order.view', 'purchase.order.view', 'View Purchase Orders (purchase_order.php)'),
(19, 'purchase.create', 'purchase.create', 'Create Purchase (add_purchase.php)'),
(20, 'purchase.view', 'purchase.view', 'View Purchase Bills (purchase.php)'),
(21, 'purchase.return', 'purchase.return', 'Purchase Return (purchase_return.php)'),
(22, 'sale.order.view', 'sale.order.view', 'View Sale Orders (sale_order.php)'),
(23, 'sale.create', 'sale.create', 'Create Sale (sale.php)'),
(24, 'sale.return', 'sale.return', 'Sale Return (sale_return.php)'),
(25, 'account.payable.view', 'account.payable.view', 'View Account Payable (account_payable.php)'),
(26, 'account.payable.detail', 'account.payable.detail', 'View Account Payable Detail (account_payable_detail.php)'),
(27, 'account.payable.voucher', 'account.payable.voucher', 'View Account Payable Per Voucher (account_payable_detail_per_voucher.php)'),
(28, 'account.receivable.view', 'account.receivable.view', 'View Account Receivable (account_receivable.php)'),
(29, 'account.receivable.detail', 'account.receivable.detail', 'View Account Receivable Detail (account_receivable_detail.php)'),
(30, 'stock.manage', 'stock.manage', 'Manage Stock (stock_control.php)'),
(31, 'report.view', 'report.view', 'View Reports (choose_report.php)'),
(32, 'company.manage', 'company.manage', 'Manage Company (company.php)');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `id` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `supplier_id` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`id`, `order_no`, `supplier_id`, `order_date`, `status`) VALUES
(1, 'PO-221', 'SUP-000', '2026-02-07', 'Delivered');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `purchase_orderid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `item_id`, `price`, `qty`, `amount`, `order_no`, `purchase_orderid`) VALUES
(1, 'ITM-002', 20, 75, 1500, 'PO-221', 1),
(2, 'ITM-003', 500, 50, 25000, 'PO-221', 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return`
--

CREATE TABLE `purchase_return` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `return_type` varchar(100) NOT NULL,
  `grn_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receivable`
--

CREATE TABLE `receivable` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `gin_no` varchar(255) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `paid` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `asc_id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `payment_no` varchar(100) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `remark` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `receivable`
--

INSERT INTO `receivable` (`id`, `date`, `gin_no`, `customer_id`, `amount`, `paid`, `balance`, `sale_id`, `asc_id`, `group_id`, `status`, `payment_no`, `account_name`, `remark`) VALUES
(1, '2026-02-06', '3529541', 'CUS-000', 37500, 0, 37500, 1, 0, '3529541', 'Pending', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'This is Admin & can access the whole module'),
(2, 'User', 'User can not access all module, only allowed by admin'),
(3, 'Sale', 'Only can access configurations and sale section');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(3, 14),
(3, 15),
(3, 17),
(3, 22),
(3, 23),
(3, 24);

-- --------------------------------------------------------

--
-- Table structure for table `sale_order`
--

CREATE TABLE `sale_order` (
  `id` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `customer_id` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_order_items`
--

CREATE TABLE `sale_order_items` (
  `id` int(11) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `sale_orderid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_return`
--

CREATE TABLE `sale_return` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `return_type` varchar(100) NOT NULL,
  `gin_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `to_from` varchar(100) NOT NULL,
  `in_qty` int(11) NOT NULL,
  `out_qty` int(11) NOT NULL,
  `foc_qty` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `grn_no` varchar(100) DEFAULT NULL,
  `gin_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `date`, `item_id`, `to_from`, `in_qty`, `out_qty`, `foc_qty`, `balance`, `grn_no`, `gin_no`) VALUES
(1, '2026-02-05', 'ITM-006', 'purchase', 2, 0, 0, 2, 'GRN-006', NULL),
(2, '2026-02-01', 'ITM-002', 'purchase', 20, 0, 0, 20, 'GRN-004', NULL),
(3, '2026-02-01', 'ITM-001', 'purchase', 12, 0, 0, 12, 'GRN-005', NULL),
(4, '2026-02-05', 'ITM-003', 'purchase', 20, 0, 0, 20, 'GRN-003', NULL),
(5, '2026-02-01', 'ITM-004', 'purchase', 4, 0, 0, 4, 'GRN-002', NULL),
(6, '2026-02-01', 'ITM-005', 'purchase', 100, 0, 0, 100, 'GRN-001', NULL),
(7, '2026-02-02', 'ITM-006', 'sale', 0, 1, 0, 1, NULL, 'S-001'),
(8, '2026-02-03', 'ITM-001', 'sale', 0, 6, 0, 6, NULL, 'GRN-002'),
(9, '2026-02-03', 'ITM-002', 'sale', 0, 10, 0, 10, NULL, 'GRN-002'),
(10, '2026-02-03', 'ITM-003', 'sale', 0, 7, 0, 13, NULL, 'GRN-002'),
(11, '2026-02-03', 'ITM-004', 'sale', 0, 1, 0, 3, NULL, 'GRN-002'),
(12, '2026-02-05', 'ITM-005', 'sale', 0, 5, 0, 95, NULL, 'S-002'),
(13, '2026-02-05', 'ITM-006', 'purchase', 5, 0, 0, 6, '222', NULL),
(14, '2026-02-05', 'ITM-004', 'purchase', 106, 0, 6, 109, '223', NULL),
(15, '2026-02-06', 'ITM-004', 'sale', 0, 22, 2, 87, NULL, '1143'),
(16, '2026-02-05', 'ITM-001', 'purchase', 100, 0, 0, 106, '22231', NULL),
(17, '2026-02-08', 'ITM-004', 'sale', 0, 7, 0, 80, NULL, '1111'),
(18, '2026-02-06', 'ITM-004', 'sale', 0, 25, 0, 55, NULL, '3529541');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `supplier_id` varchar(100) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_phone` int(11) NOT NULL,
  `supplier_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `supplier_id`, `supplier_name`, `supplier_phone`, `supplier_address`) VALUES
(4, 'SUP-001', 'Golden Star Trading', 912345678, 'Yangon'),
(5, 'SUP-002', 'Asia Link Supplies', 923456789, 'Mandalay'),
(6, 'SUP-003', 'Myanmar Fresh Foods', 934567890, 'Nay Pyi Taw'),
(7, 'SUP-004', 'Royal Market Co., Ltd', 945678901, 'Yangon'),
(8, 'SUP-005', 'Shwe Pyi Supply House', 956789012, 'Bago'),
(9, 'SUP-006', 'Green Land Trading', 967890123, 'Taunggyi'),
(10, 'SUP-007', 'City Wholesale Center', 978901234, 'Yangon'),
(11, 'SUP-008', 'Unity Distribution', 989012345, 'Pathein'),
(12, 'SUP-009', 'Ever Best Supplier', 911122233, 'Mawlamyine'),
(13, 'SUP-010', 'Northern Star Co.', 922233344, 'Myitkyina'),
(14, 'SUP-011', 'Sunrise Trading Group', 933344455, 'Yangon'),
(15, 'SUP-012', 'Delta Region Supply', 944455566, 'Hinthada'),
(16, 'SUP-013', 'Blue Ocean Imports', 955566677, 'Sittwe'),
(17, 'SUP-014', 'Capital City Traders', 966677788, 'Nay Pyi Taw'),
(18, 'SUP-015', 'Golden Harvest Co.', 977788899, 'Pyay'),
(19, 'SUP-016', 'Top Choice Suppliers', 988899900, 'Yangon'),
(20, 'SUP-017', 'Fast Move Distribution', 919988776, 'Mandalay'),
(21, 'SUP-018', 'Prime Source Myanmar', 928877665, 'Lashio'),
(24, 'SUP-000', 'Default Supplier', 253, 'Yangon'),
(25, 'SUP-021', 'KOO', 989, 'Yangon');

-- --------------------------------------------------------

--
-- Table structure for table `temp_purchase`
--

CREATE TABLE `temp_purchase` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `temp_purchase`
--

INSERT INTO `temp_purchase` (`id`, `date`, `grn_no`, `supplier_id`, `po_no`, `type`, `status`) VALUES
(1, '2026-02-01', 'GRN-001', 'SUP-000', '', 'cash', 'approved'),
(2, '2026-02-01', 'GRN-002', 'SUP-000', '', 'cash', 'approved'),
(3, '2026-02-05', 'GRN-003', 'SUP-000', '', 'cash', 'approved'),
(4, '2026-02-01', 'GRN-004', 'SUP-000', '', 'cash', 'approved'),
(5, '2026-02-01', 'GRN-005', 'SUP-000', '', 'cash', 'approved'),
(6, '2026-02-05', 'GRN-006', 'SUP-000', '', 'cash', 'approved'),
(7, '2026-02-05', '222', 'SUP-000', '', 'cash', 'approved'),
(8, '2026-02-05', '223', 'SUP-000', '', 'cash', 'approved'),
(9, '2026-02-05', '22231', 'SUP-000', '', 'cash', 'approved'),
(10, '2026-02-07', '345', 'SUP-002', '', 'cash', 'draft'),
(11, '2026-02-07', '22232', 'SUP-000', 'PO-221', 'cash', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `temp_purchase_items`
--

CREATE TABLE `temp_purchase_items` (
  `id` int(11) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `percentage` int(11) NOT NULL,
  `percentage_amount` int(11) NOT NULL,
  `stock_foc` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `temp_purchase_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `temp_purchase_items`
--

INSERT INTO `temp_purchase_items` (`id`, `item_id`, `price`, `qty`, `type`, `percentage`, `percentage_amount`, `stock_foc`, `amount`, `grn_no`, `temp_purchase_id`) VALUES
(1, 'ITM-005', 200, 100, 'cash', 0, 0, 0, 20000, 'GRN-001', 1),
(2, 'ITM-004', 1300, 4, 'cash', 0, 0, 0, 5200, 'GRN-002', 2),
(3, 'ITM-003', 500, 20, 'cash', 0, 0, 0, 10000, 'GRN-003', 3),
(4, 'ITM-002', 20, 20, 'cash', 0, 0, 0, 400, 'GRN-004', 4),
(5, 'ITM-001', 5000, 12, 'cash', 0, 0, 0, 60000, 'GRN-005', 5),
(6, 'ITM-006', 2500, 2, 'cash', 0, 0, 0, 5000, 'GRN-006', 6),
(7, 'ITM-006', 2500, 5, 'cash', 0, 0, 0, 12500, '222', 7),
(8, 'ITM-004', 1300, 100, 'cash', 0, 0, 6, 130000, '223', 8),
(9, 'ITM-001', 5000, 100, 'cash', 0, 0, 0, 500000, '22231', 9),
(10, 'ITM-002', 20, 150, 'cash', 0, 0, 0, 3000, '345', 10),
(11, 'ITM-005', 200, 20, 'cash', 0, 0, 0, 4000, '345', 10),
(12, 'ITM-002', 20, 75, 'cash', 0, 0, 0, 1500, '22232', 11),
(13, 'ITM-003', 500, 50, 'cash', 0, 0, 0, 25000, '22232', 11);

-- --------------------------------------------------------

--
-- Table structure for table `temp_sale`
--

CREATE TABLE `temp_sale` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `so_no` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `temp_sale`
--

INSERT INTO `temp_sale` (`id`, `date`, `gin_no`, `customer_id`, `type`, `so_no`, `status`) VALUES
(1, '2026-02-02', 'S-001', 'CUS-000', 'cash', '', 'approved'),
(2, '2026-02-03', 'GRN-002', 'CUS-000', 'cash', '', 'approved'),
(3, '2026-02-05', 'S-002', 'CUS-000', 'cash', '', 'approved'),
(5, '2026-02-06', '1143', 'CUS-000', 'cash', '', 'approved'),
(10, '2026-02-08', '1111', 'CUS-000', 'cash', '', 'approved'),
(11, '2026-02-06', '3529541', 'CUS-000', 'credit', '', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `temp_sale_items`
--

CREATE TABLE `temp_sale_items` (
  `id` int(11) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `percentage` int(11) NOT NULL,
  `percentage_amount` int(11) NOT NULL,
  `stock_foc` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `temp_sale_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `temp_sale_items`
--

INSERT INTO `temp_sale_items` (`id`, `item_id`, `price`, `qty`, `type`, `percentage`, `percentage_amount`, `stock_foc`, `amount`, `gin_no`, `temp_sale_id`) VALUES
(1, 'ITM-006', 2900, 1, 'cash', 0, 0, 0, 2900, 'S-001', 1),
(2, 'ITM-001', 5500, 6, 'cash', 0, 0, 0, 33000, 'GRN-002', 2),
(3, 'ITM-002', 700, 10, 'cash', 0, 0, 0, 7000, 'GRN-002', 2),
(4, 'ITM-003', 600, 7, 'cash', 0, 0, 0, 4200, 'GRN-002', 2),
(5, 'ITM-004', 1500, 1, 'cash', 0, 0, 0, 1500, 'GRN-002', 2),
(6, 'ITM-005', 250, 5, 'cash', 0, 0, 0, 1250, 'S-002', 3),
(8, 'ITM-004', 1500, 20, 'cash', 0, 0, 2, 30000, '1143', 5),
(11, 'ITM-004', 1500, 7, 'cash', 0, 0, 0, 10500, '1111', 10),
(12, 'ITM-004', 1500, 25, 'credit', 0, 0, 0, 37500, '3529541', 11);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$H8rj.d8lrK9upNxBSXfESuJK8V527GNRazh4cInVoEoSmex5z5se2', 1),
(4, 'Neo', 'neo@gmail.com', '$2y$10$TZNln7XBtPZH/vPKdYsVxOdeHB0t2HsyJkNTzzEDpPEmDGJ4l8bE.', 2),
(7, 'Mg Mg', 'mgmg@gmail.com', '$2y$10$413eOb/Ioqu9y5tltLhER.jsRvmaL2k2rSebmJCOZ38STUa/LBly2', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cash_purchase`
--
ALTER TABLE `cash_purchase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cash_sale`
--
ALTER TABLE `cash_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_purchase`
--
ALTER TABLE `credit_purchase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_sale`
--
ALTER TABLE `credit_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payable`
--
ALTER TABLE `payable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_return`
--
ALTER TABLE `purchase_return`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receivable`
--
ALTER TABLE `receivable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sale_order`
--
ALTER TABLE `sale_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_order_items`
--
ALTER TABLE `sale_order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_return`
--
ALTER TABLE `sale_return`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_purchase`
--
ALTER TABLE `temp_purchase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_purchase_items`
--
ALTER TABLE `temp_purchase_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_sale`
--
ALTER TABLE `temp_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_sale_items`
--
ALTER TABLE `temp_sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cash_purchase`
--
ALTER TABLE `cash_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cash_sale`
--
ALTER TABLE `cash_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `credit_purchase`
--
ALTER TABLE `credit_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_sale`
--
ALTER TABLE `credit_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payable`
--
ALTER TABLE `payable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_return`
--
ALTER TABLE `purchase_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receivable`
--
ALTER TABLE `receivable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sale_order`
--
ALTER TABLE `sale_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_order_items`
--
ALTER TABLE `sale_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_return`
--
ALTER TABLE `sale_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `temp_purchase`
--
ALTER TABLE `temp_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `temp_purchase_items`
--
ALTER TABLE `temp_purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `temp_sale`
--
ALTER TABLE `temp_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `temp_sale_items`
--
ALTER TABLE `temp_sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
