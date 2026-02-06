-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 03:59 AM
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
-- Database: `protech_inventory`
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
(1, '2026-01-12', 121, 'SUP0001', 'INV0001', 10, '', 10000000),
(2, '2026-01-12', 121, 'SUP0001', 'INV0003', 10, '', 15000000),
(3, '2026-01-12', 121, 'SUP0001', 'INV0006', 5, '', 2500000),
(4, '2026-01-12', 12112, 'SUP0002', 'INV0006', 100, '', 50000000),
(5, '2026-01-12', 12112, 'SUP0002', 'INV0002', 100, '', 200000000),
(6, '2026-01-12', 12112, 'SUP0002', 'INV0005', 50, '', 750000),
(7, '2026-01-01', 0, 'Sup-001', 'ITM-005', 30, '', 1800000),
(8, '2026-01-01', 0, 'Sup-001', 'ITM-001', 20, '', 5600000),
(9, '2026-01-02', 0, 'SUP-008', 'ITM-030', 20, '', 760000),
(10, '2026-01-02', 0, 'SUP-008', 'ITM-031', 200, '', 60000);

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
(11, '2026-01-13', 3529541, '10083', 'INV0006', 1, 550000),
(12, '2026-01-13', 356, '10083', 'INV0006', 1, 550000),
(13, '2026-01-13', 35295, '10083', 'INV0002', 1, 2200000),
(14, '2026-01-13', 35686, '1005', 'INV0002', 5, 11000000),
(15, '2026-01-13', 3561, '10084', 'INV0006', 3, 1650000),
(16, '2026-01-01', 0, 'CUS-008', 'ITM-005', 2, 150000),
(17, '2026-01-01', 0, 'CUS-008', 'ITM-031', 5, 3000),
(18, '2026-01-02', 0, 'CUS-015', 'ITM-041', 5, 295000),
(19, '2026-01-02', 0, 'CUS-015', 'ITM-023', 3, 690000),
(20, '2026-01-21', 0, 'CUS-005', 'ITM-005', 3, 225000);

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
(4, 'CAT-001', 'Electronics'),
(5, 'CAT-002', 'Computer & Accessories'),
(6, 'CAT-003', 'Mobile Phones'),
(7, 'CAT-004', 'Office Supplies'),
(8, 'CAT-005', 'Furniture'),
(9, 'CAT-006', 'Home Appliances'),
(10, 'CAT-007', 'Stationery'),
(11, 'CAT-008', 'Tools & Hardware'),
(12, 'CAT-009', 'Networking Equipment'),
(13, 'CAT-010', 'Miscellaneous');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street_name` varchar(255) NOT NULL,
  `building_no` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
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
(1, 'Golden Future Co.,Ltd', 'Insein Road', 'No.45', 2147483647, 'contact@goldenfuture.com', 'Yangon', 'Myanmar', 'CB-0044556677', 'abstract-geometric-logo-or-infinity-line-logo-for-your-company-free-vector.jpg');

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

--
-- Dumping data for table `credit_purchase`
--

INSERT INTO `credit_purchase` (`id`, `date`, `grn_no`, `supplier_id`, `item_id`, `qty`, `po_no`, `amount`) VALUES
(1, '2026-01-13', 222, 'SUP0003', 'INV0004', 30, '', 750000);

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
(1, '2026-01-26', 35664, 'CUS-011', 'ITM-001', 10, 3200000);

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
(23, 'CUS-020', 'Hnin Hnin', 946655440, 'Yangon'),
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
  `reorder_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id`, `item_id`, `item_name`, `categories_id`, `original_price`, `selling_price`, `reorder_level`) VALUES
(7, 'ITM-001', 'LED TV 32 inch', 'CAT-001', 280000, 320000, 5),
(8, 'ITM-002', 'LED TV 43 inch', 'CAT-001', 420000, 480000, 4),
(9, 'ITM-003', 'Bluetooth Speaker', 'CAT-001', 35000, 45000, 10),
(10, 'ITM-004', 'Digital Camera', 'CAT-001', 250000, 290000, 3),
(11, 'ITM-005', 'Smart Watch', 'CAT-001', 60000, 75000, 8),
(12, 'ITM-006', 'Laptop Dell Inspiron', 'CAT-002', 650000, 720000, 4),
(13, 'ITM-007', 'Wireless Mouse', 'CAT-002', 8000, 12000, 20),
(14, 'ITM-008', 'Mechanical Keyboard', 'CAT-002', 35000, 48000, 15),
(15, 'ITM-009', 'USB Flash Drive 32GB', 'CAT-002', 6000, 9000, 30),
(16, 'ITM-010', 'External Hard Disk 1TB', 'CAT-002', 85000, 98000, 6),
(17, 'ITM-011', 'Samsung Galaxy A14', 'CAT-003', 320000, 355000, 6),
(18, 'ITM-012', 'iPhone 11', 'CAT-003', 780000, 850000, 3),
(19, 'ITM-013', 'Xiaomi Redmi 12', 'CAT-003', 210000, 235000, 7),
(20, 'ITM-014', 'Phone Charger Type-C', 'CAT-003', 7000, 12000, 25),
(21, 'ITM-015', 'Wireless Earbuds', 'CAT-003', 25000, 39000, 12),
(22, 'ITM-016', 'Printer HP LaserJet', 'CAT-004', 380000, 420000, 2),
(23, 'ITM-017', 'A4 Paper (500 sheets)', 'CAT-004', 6500, 8500, 40),
(24, 'ITM-018', 'Stapler Machine', 'CAT-004', 4500, 7000, 20),
(25, 'ITM-019', 'Whiteboard Marker Set', 'CAT-004', 3500, 6000, 25),
(26, 'ITM-020', 'Desk Organizer', 'CAT-004', 9000, 13000, 10),
(27, 'ITM-021', 'Office Chair', 'CAT-005', 85000, 110000, 5),
(28, 'ITM-022', 'Office Table', 'CAT-005', 120000, 155000, 3),
(29, 'ITM-023', 'Steel Cabinet', 'CAT-005', 190000, 230000, 2),
(30, 'ITM-024', 'Bookshelf', 'CAT-005', 75000, 98000, 4),
(31, 'ITM-025', 'Visitor Chair', 'CAT-005', 35000, 48000, 8),
(32, 'ITM-026', 'Electric Kettle', 'CAT-006', 18000, 25000, 12),
(33, 'ITM-027', 'Microwave Oven', 'CAT-006', 210000, 245000, 3),
(34, 'ITM-028', 'Air Conditioner 1.5HP', 'CAT-006', 620000, 690000, 2),
(35, 'ITM-029', 'Standing Fan', 'CAT-006', 45000, 58000, 6),
(36, 'ITM-030', 'Rice Cooker', 'CAT-006', 38000, 52000, 8),
(37, 'ITM-031', 'Ball Pen (Blue)', 'CAT-007', 300, 600, 100),
(38, 'ITM-032', 'Notebook A5', 'CAT-007', 1200, 2000, 60),
(39, 'ITM-033', 'Highlighter Pen', 'CAT-007', 800, 1500, 40),
(40, 'ITM-034', 'Correction Tape', 'CAT-007', 700, 1200, 30),
(41, 'ITM-035', 'Pencil Box', 'CAT-007', 2500, 4000, 20),
(42, 'ITM-036', 'Electric Drill', 'CAT-008', 95000, 120000, 4),
(43, 'ITM-037', 'Screwdriver Set', 'CAT-008', 18000, 26000, 10),
(44, 'ITM-038', 'Hammer', 'CAT-008', 12000, 18000, 12),
(45, 'ITM-039', 'Measuring Tape', 'CAT-008', 6000, 9500, 20),
(46, 'ITM-040', 'Tool Box', 'CAT-008', 28000, 38000, 6),
(47, 'ITM-041', 'WiFi Router', 'CAT-009', 45000, 59000, 8),
(48, 'ITM-042', 'Network Switch 8 Port', 'CAT-009', 32000, 42000, 5),
(49, 'ITM-043', 'LAN Cable 10m', 'CAT-009', 3500, 6500, 30),
(50, 'ITM-044', 'Wireless Access Point', 'CAT-009', 78000, 92000, 4),
(51, 'ITM-045', 'Crimping Tool', 'CAT-009', 14000, 21000, 6),
(52, 'ITM-046', 'Power Extension Socket', 'CAT-010', 7500, 12000, 15),
(53, 'ITM-047', 'UPS 650VA', 'CAT-010', 85000, 98000, 3),
(54, 'ITM-048', 'Flashlight', 'CAT-010', 6500, 10000, 12),
(55, 'ITM-049', 'Cleaning Spray', 'CAT-010', 3500, 6000, 25),
(56, 'ITM-050', 'Wall Clock', 'CAT-010', 18000, 26000, 5);

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
(3, 'PO-27041353', 'SUP-020', '2026-01-26', 'Pending');

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
(4, 'ITM-008', 35000, 50, 1750000, 'PO-27041353', 3);

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
(1, '2026-01-26', '35664', 'CUS-011', 3200000, 0, 3200000, 1, 0, '', 'Pending', '', '', '');

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

--
-- Dumping data for table `sale_order`
--

INSERT INTO `sale_order` (`id`, `order_no`, `customer_id`, `order_date`, `status`) VALUES
(5, 'PO-162871', 'CUS-005', '2026-01-20', 'Delivered'),
(6, 'PO-775264', 'CUS-011', '2026-01-26', 'Delivered');

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

--
-- Dumping data for table `sale_order_items`
--

INSERT INTO `sale_order_items` (`id`, `item_id`, `price`, `qty`, `amount`, `order_no`, `sale_orderid`) VALUES
(5, 'ITM-005', 75000, 3, 225000, 'PO-162871', 5),
(6, 'ITM-001', 320000, 10, 3200000, 'PO-775264', 6);

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
(29, '2026-01-01', 'ITM-005', 'purchase', 30, 0, 0, 30, '0', NULL),
(30, '2026-01-01', 'ITM-001', 'purchase', 20, 0, 0, 20, '0', NULL),
(31, '2026-01-02', 'ITM-030', 'purchase', 20, 0, 0, 20, '0', NULL),
(32, '2026-01-02', 'ITM-031', 'purchase', 200, 0, 0, 200, '0', NULL),
(33, '2026-01-01', 'ITM-005', 'sale', 0, 2, 0, 28, NULL, '0'),
(34, '2026-01-01', 'ITM-031', 'sale', 0, 5, 0, 195, NULL, '0'),
(37, '2026-01-21', 'ITM-005', 'sale', 0, 3, 0, 25, NULL, '0'),
(40, '2026-01-26', 'ITM-001', 'sale', 0, 10, 0, 10, NULL, '35664');

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
(22, 'SUP-019', 'Trust Way Trading', 937766554, 'Meiktila'),
(23, 'SUP-020', 'One Stop Wholesale', 946655443, 'Yangon');

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
(5, '2026-01-01', 'VR001', 'Sup-001', '', 'cash', 'approved'),
(6, '2026-01-02', 'VR002', 'SUP-008', '', 'cash', 'approved'),
(7, '2026-01-24', 'PR-11111', 'SUP-017', '', 'cash', 'draft');

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
(17, 'ITM-005', 60000, 30, 'cash', 0, 0, 0, 1800000, 'VR001', 5),
(18, 'ITM-001', 280000, 20, 'cash', 0, 0, 0, 5600000, 'VR001', 5),
(19, 'ITM-030', 38000, 20, 'cash', 0, 0, 0, 760000, 'VR002', 6),
(20, 'ITM-031', 300, 200, 'cash', 0, 0, 0, 60000, 'VR002', 6),
(21, 'ITM-041', 45000, 3, 'cash', 0, 0, 0, 135000, 'PR-11111', 7);

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
(18, '2026-01-01', 'SVR001', 'CUS-008', 'cash', '', 'approved'),
(20, '2026-01-21', 'SA-002', 'CUS-005', 'cash', 'PO-162871', 'approved'),
(21, '2026-01-26', '35664', 'CUS-011', 'credit', 'PO-775264', 'approved'),
(22, '2026-01-27', '555', 'CUS-003', 'cash', '', 'draft');

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
(13, 'ITM-005', 75000, 2, 'cash', 0, 0, 0, 150000, 'SVR001', 18),
(14, 'ITM-031', 600, 5, 'cash', 0, 0, 0, 3000, 'SVR001', 18),
(17, 'ITM-005', 75000, 3, 'cash', 0, 0, 0, 225000, 'SA-002', 20),
(18, 'ITM-001', 320000, 10, 'credit', 0, 0, 0, 3200000, '35664', 21),
(24, 'ITM-031', 600, 50, '', 0, 0, 0, 30000, '555', 22);

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
(1, 'Admin', 'admin@gmail.com', '$2y$10$Gqi9XKF/uh/XlRU9nblUa.5vh4DtDyh2yR5oezBlPNVIAw08DA2w6', 1),
(4, 'Neoo', 'neo@gmail.com', '$2y$10$TZNln7XBtPZH/vPKdYsVxOdeHB0t2HsyJkNTzzEDpPEmDGJ4l8bE.', 2),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cash_sale`
--
ALTER TABLE `cash_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `credit_purchase`
--
ALTER TABLE `credit_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `payable`
--
ALTER TABLE `payable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_return`
--
ALTER TABLE `purchase_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sale_order_items`
--
ALTER TABLE `sale_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sale_return`
--
ALTER TABLE `sale_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `temp_purchase`
--
ALTER TABLE `temp_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `temp_purchase_items`
--
ALTER TABLE `temp_purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `temp_sale`
--
ALTER TABLE `temp_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `temp_sale_items`
--
ALTER TABLE `temp_sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
