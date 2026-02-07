-- PHP-only backup
-- 2026-02-07 10:44:40
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cash_purchase`;
CREATE TABLE `cash_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `grn_no` int(11) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `cash_purchase` (`id`,`date`,`grn_no`,`supplier_id`,`item_id`,`qty`,`po_no`,`amount`) VALUES
('1','2026-02-05','0','SUP-000','ITM-006','2','','5000'),
('2','2026-02-01','0','SUP-000','ITM-002','20','','400'),
('3','2026-02-01','0','SUP-000','ITM-001','12','','60000'),
('4','2026-02-05','0','SUP-000','ITM-003','20','','10000'),
('5','2026-02-01','0','SUP-000','ITM-004','4','','5200'),
('6','2026-02-01','0','SUP-000','ITM-005','100','','20000'),
('7','2026-02-05','222','SUP-000','ITM-006','5','','12500'),
('8','2026-02-05','223','SUP-000','ITM-004','100','','130000'),
('9','2026-02-05','22231','SUP-000','ITM-001','100','','500000');

DROP TABLE IF EXISTS `cash_sale`;
CREATE TABLE `cash_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `gin_no` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `cash_sale` (`id`,`date`,`gin_no`,`customer_id`,`item_id`,`qty`,`amount`) VALUES
('1','2026-02-02','0','CUS-000','ITM-006','1','2900'),
('2','2026-02-03','0','CUS-000','ITM-001','6','33000'),
('3','2026-02-03','0','CUS-000','ITM-002','10','7000'),
('4','2026-02-03','0','CUS-000','ITM-003','7','4200'),
('5','2026-02-03','0','CUS-000','ITM-004','1','1500'),
('6','2026-02-05','0','CUS-000','ITM-005','5','1250'),
('7','2026-02-06','1143','CUS-000','ITM-004','20','30000'),
('8','2026-02-08','1111','CUS-000','ITM-004','7','10500');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_code` varchar(255) NOT NULL,
  `categories_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `categories` (`id`,`categories_code`,`categories_name`) VALUES
('1','CAT-001','Cream'),
('3','CAT-003','Eye Drop'),
('4','CAT-004','Injection'),
('5','CAT-005','Medical Equitement'),
('6','CAT-006','Medicine');

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `street_name` varchar(255) NOT NULL,
  `building_no` varchar(255) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `bank_account` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `company` (`id`,`name`,`street_name`,`building_no`,`phone`,`email`,`city`,`country`,`bank_account`,`logo`) VALUES
('1','Golden Future Co.,Ltd','Insein Road','No.45','9795799559','contact@goldenfuture.com','Yangon','Myanmar','CB-0044556677','transparent-Photoroom (2).png');

DROP TABLE IF EXISTS `credit_purchase`;
CREATE TABLE `credit_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `grn_no` int(11) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `credit_sale`;
CREATE TABLE `credit_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `gin_no` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `credit_sale` (`id`,`date`,`gin_no`,`customer_id`,`item_id`,`qty`,`amount`) VALUES
('1','2026-02-06','3529541','CUS-000','ITM-004','25','37500');

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(100) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` int(11) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `customer` (`id`,`customer_id`,`customer_name`,`customer_phone`,`customer_address`) VALUES
('4','CUS-001','Aung Min','912345671','Yangon'),
('5','CUS-002','Mya Thandar','923456782','Mandalay'),
('6','CUS-003','Htet Htet','934567893','Nay Pyi Taw'),
('7','CUS-004','Ko Ko','945678904','Yangon'),
('8','CUS-005','Moe Moe','956789015','Bago'),
('9','CUS-006','Ei Mon','967890126','Taunggyi'),
('10','CUS-007','Zaw Zaw','978901237','Yangon'),
('11','CUS-008','Thiri','989012348','Pathein'),
('12','CUS-009','Swe Zin','911122239','Mawlamyine'),
('13','CUS-010','Min Thu','922233340','Myitkyina'),
('14','CUS-011','Nandar','933344451','Yangon'),
('15','CUS-012','San Hlaing','944455562','Hinthada'),
('16','CUS-013','Khin Zaw','955566673','Sittwe'),
('17','CUS-014','Thant Zin','966677784','Nay Pyi Taw'),
('18','CUS-015','May Khin','977788895','Pyay'),
('19','CUS-016','Hla Hla','988899906','Yangon'),
('20','CUS-017','Kyaw Kyaw','919988777','Mandalay'),
('21','CUS-018','Ei Ei','928877668','Lashio'),
('22','CUS-019','Aye Aye','937766559','Meiktila'),
('23','CUS-020','Hnin Hnin','9466554','Yangon'),
('24','CUS-000','Default Customer','912345671','Yangon');

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `categories_id` varchar(255) NOT NULL,
  `original_price` int(11) NOT NULL,
  `selling_price` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `item` (`id`,`item_id`,`item_name`,`categories_id`,`original_price`,`selling_price`,`reorder_level`,`item_image`,`expiry_date`,`location`) VALUES
('1','ITM-001','Essence of chicken','CAT-006','5000','5500','5','1770445737_images (1).jfif','2026-12-07','Rack B / Section 4'),
('2','ITM-002','Albendazole','CAT-006','20','700','20','1770445617_Albendazole.jfif','2026-12-07','Rack A / Section 4'),
('3','ITM-003','Clarithromucin 250mg','CAT-006','500','600','20','1770445592_Clarithromucin 250mg.jfif','2027-01-07','Rack A / Section 4'),
('4','ITM-004','Mebendazle 500mg','CAT-006','1300','1500','8','1770445459_mebendazole-500mg-tablet.jpeg','2027-01-01','Rack A / Section 6'),
('5','ITM-005','Erythromycin Stearate -250mg','CAT-006','200','250','100','1770445033_Erythromycin-Stearate-Tablets.jpg','2027-01-01','Rack A / Section 4'),
('6','ITM-006','a ba hta inhlar (big)','CAT-006','2500','2900','5','1770349930_abahta.jfif',NULL,NULL),
('9','ITM-007','a ba hta inhlar','CAT-006','1700','2000','10','1770445886_images (2).jfif','2027-01-07','Rack A / Section 1');

DROP TABLE IF EXISTS `payable`;
CREATE TABLE `payable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `remark` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `permissions` (`id`,`name`,`permission_key`,`description`) VALUES
('11','roles.manage','roles.manage','Manage Roles (roles.php)'),
('12','users.manage','users.manage','Manage Users (users.php)'),
('13','permissions.manage','permissions.manage','Manage Permissions (role_permissions.php)'),
('14','category.view','category.view','View Categories (category.php)'),
('15','item.view','item.view','View Items (item.php)'),
('16','supplier.view','supplier.view','View Suppliers (supplier.php)'),
('17','customer.view','customer.view','View Customers (customer.php)'),
('18','purchase.order.view','purchase.order.view','View Purchase Orders (purchase_order.php)'),
('19','purchase.create','purchase.create','Create Purchase (add_purchase.php)'),
('20','purchase.view','purchase.view','View Purchase Bills (purchase.php)'),
('21','purchase.return','purchase.return','Purchase Return (purchase_return.php)'),
('22','sale.order.view','sale.order.view','View Sale Orders (sale_order.php)'),
('23','sale.create','sale.create','Create Sale (sale.php)'),
('24','sale.return','sale.return','Sale Return (sale_return.php)'),
('25','account.payable.view','account.payable.view','View Account Payable (account_payable.php)'),
('26','account.payable.detail','account.payable.detail','View Account Payable Detail (account_payable_detail.php)'),
('27','account.payable.voucher','account.payable.voucher','View Account Payable Per Voucher (account_payable_detail_per_voucher.php)'),
('28','account.receivable.view','account.receivable.view','View Account Receivable (account_receivable.php)'),
('29','account.receivable.detail','account.receivable.detail','View Account Receivable Detail (account_receivable_detail.php)'),
('30','stock.manage','stock.manage','Manage Stock (stock_control.php)'),
('31','report.view','report.view','View Reports (choose_report.php)'),
('32','company.manage','company.manage','Manage Company (company.php)'),
('34','backup.manage','backup.manage','Manage Data Backup(backup.php)'),
('35','restore.manage','restore.manage','Manage Data Restore(restore.php)');

DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE `purchase_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(100) NOT NULL,
  `supplier_id` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `purchase_order` (`id`,`order_no`,`supplier_id`,`order_date`,`status`) VALUES
('1','PO-221','SUP-000','2026-02-07','Delivered');

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `purchase_orderid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `purchase_order_items` (`id`,`item_id`,`price`,`qty`,`amount`,`order_no`,`purchase_orderid`) VALUES
('1','ITM-002','20','75','1500','PO-221','1'),
('2','ITM-003','500','50','25000','PO-221','1');

DROP TABLE IF EXISTS `purchase_return`;
CREATE TABLE `purchase_return` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `return_type` varchar(100) NOT NULL,
  `grn_no` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `receivable`;
CREATE TABLE `receivable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `remark` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `receivable` (`id`,`date`,`gin_no`,`customer_id`,`amount`,`paid`,`balance`,`sale_id`,`asc_id`,`group_id`,`status`,`payment_no`,`account_name`,`remark`) VALUES
('1','2026-02-06','3529541','CUS-000','37500','0','37500','1','0','3529541','Pending','','','');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id`,`name`,`description`) VALUES
('1','Admin','This is Admin & can access the whole module'),
('2','User','User can not access all module, only allowed by admin'),
('3','Sale','Only can access configurations and sale section');

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `role_permissions` (`role_id`,`permission_id`) VALUES
('1','11'),
('1','12'),
('1','13'),
('1','14'),
('1','15'),
('1','16'),
('1','17'),
('1','18'),
('1','19'),
('1','20'),
('1','21'),
('1','22'),
('1','23'),
('1','24'),
('1','25'),
('1','26'),
('1','27'),
('1','28'),
('1','29'),
('1','30'),
('1','31'),
('1','32'),
('1','34'),
('1','35'),
('2','14'),
('2','15'),
('2','16'),
('2','17'),
('2','18'),
('2','19'),
('2','21'),
('2','22'),
('2','23'),
('2','24'),
('3','14'),
('3','15'),
('3','17'),
('3','22'),
('3','23'),
('3','24');

DROP TABLE IF EXISTS `sale_order`;
CREATE TABLE `sale_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(100) NOT NULL,
  `customer_id` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_order` (`id`,`order_no`,`customer_id`,`order_date`,`status`) VALUES
('1','PO-258300','CUS-006','2026-02-07','Pending');

DROP TABLE IF EXISTS `sale_order_items`;
CREATE TABLE `sale_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `order_no` varchar(100) NOT NULL,
  `sale_orderid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_order_items` (`id`,`item_id`,`price`,`qty`,`amount`,`order_no`,`sale_orderid`) VALUES
('1','ITM-002','700','2','1400','PO-258300','1');

DROP TABLE IF EXISTS `sale_return`;
CREATE TABLE `sale_return` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `return_type` varchar(100) NOT NULL,
  `gin_no` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `stock`;
CREATE TABLE `stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `to_from` varchar(100) NOT NULL,
  `in_qty` int(11) NOT NULL,
  `out_qty` int(11) NOT NULL,
  `foc_qty` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `grn_no` varchar(100) DEFAULT NULL,
  `gin_no` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `stock` (`id`,`date`,`item_id`,`to_from`,`in_qty`,`out_qty`,`foc_qty`,`balance`,`grn_no`,`gin_no`) VALUES
('1','2026-02-05','ITM-006','purchase','2','0','0','2','GRN-006',NULL),
('2','2026-02-01','ITM-002','purchase','20','0','0','20','GRN-004',NULL),
('3','2026-02-01','ITM-001','purchase','12','0','0','12','GRN-005',NULL),
('4','2026-02-05','ITM-003','purchase','20','0','0','20','GRN-003',NULL),
('5','2026-02-01','ITM-004','purchase','4','0','0','4','GRN-002',NULL),
('6','2026-02-01','ITM-005','purchase','100','0','0','100','GRN-001',NULL),
('7','2026-02-02','ITM-006','sale','0','1','0','1',NULL,'S-001'),
('8','2026-02-03','ITM-001','sale','0','6','0','6',NULL,'GRN-002'),
('9','2026-02-03','ITM-002','sale','0','10','0','10',NULL,'GRN-002'),
('10','2026-02-03','ITM-003','sale','0','7','0','13',NULL,'GRN-002'),
('11','2026-02-03','ITM-004','sale','0','1','0','3',NULL,'GRN-002'),
('12','2026-02-05','ITM-005','sale','0','5','0','95',NULL,'S-002'),
('13','2026-02-05','ITM-006','purchase','5','0','0','6','222',NULL),
('14','2026-02-05','ITM-004','purchase','106','0','6','109','223',NULL),
('15','2026-02-06','ITM-004','sale','0','22','2','87',NULL,'1143'),
('16','2026-02-05','ITM-001','purchase','100','0','0','106','22231',NULL),
('17','2026-02-08','ITM-004','sale','0','7','0','80',NULL,'1111'),
('18','2026-02-06','ITM-004','sale','0','25','0','55',NULL,'3529541');

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` varchar(100) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_phone` int(11) NOT NULL,
  `supplier_address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `supplier` (`id`,`supplier_id`,`supplier_name`,`supplier_phone`,`supplier_address`) VALUES
('4','SUP-001','Golden Star Trading','912345678','Yangon'),
('5','SUP-002','Asia Link Supplies','923456789','Mandalay'),
('6','SUP-003','Myanmar Fresh Foods','934567890','Nay Pyi Taw'),
('7','SUP-004','Royal Market Co., Ltd','945678901','Yangon'),
('8','SUP-005','Shwe Pyi Supply House','956789012','Bago'),
('9','SUP-006','Green Land Trading','967890123','Taunggyi'),
('10','SUP-007','City Wholesale Center','978901234','Yangon'),
('11','SUP-008','Unity Distribution','989012345','Pathein'),
('12','SUP-009','Ever Best Supplier','911122233','Mawlamyine'),
('13','SUP-010','Northern Star Co.','922233344','Myitkyina'),
('14','SUP-011','Sunrise Trading Group','933344455','Yangon'),
('15','SUP-012','Delta Region Supply','944455566','Hinthada'),
('16','SUP-013','Blue Ocean Imports','955566677','Sittwe'),
('17','SUP-014','Capital City Traders','966677788','Nay Pyi Taw'),
('18','SUP-015','Golden Harvest Co.','977788899','Pyay'),
('19','SUP-016','Top Choice Suppliers','988899900','Yangon'),
('20','SUP-017','Fast Move Distribution','919988776','Mandalay'),
('21','SUP-018','Prime Source Myanmar','928877665','Lashio'),
('24','SUP-000','Default Supplier','253','Yangon'),
('25','SUP-021','KOO','989','Yangon');

DROP TABLE IF EXISTS `temp_purchase`;
CREATE TABLE `temp_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `po_no` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `temp_purchase` (`id`,`date`,`grn_no`,`supplier_id`,`po_no`,`type`,`status`) VALUES
('1','2026-02-01','GRN-001','SUP-000','','cash','approved'),
('2','2026-02-01','GRN-002','SUP-000','','cash','approved'),
('3','2026-02-05','GRN-003','SUP-000','','cash','approved'),
('4','2026-02-01','GRN-004','SUP-000','','cash','approved'),
('5','2026-02-01','GRN-005','SUP-000','','cash','approved'),
('6','2026-02-05','GRN-006','SUP-000','','cash','approved'),
('7','2026-02-05','222','SUP-000','','cash','approved'),
('8','2026-02-05','223','SUP-000','','cash','approved'),
('9','2026-02-05','22231','SUP-000','','cash','approved'),
('10','2026-02-07','345','SUP-002','','cash','draft'),
('11','2026-02-07','22232','SUP-000','PO-221','cash','draft');

DROP TABLE IF EXISTS `temp_purchase_items`;
CREATE TABLE `temp_purchase_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `percentage` int(11) NOT NULL,
  `percentage_amount` int(11) NOT NULL,
  `stock_foc` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `grn_no` varchar(100) NOT NULL,
  `temp_purchase_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `temp_purchase_items` (`id`,`item_id`,`price`,`qty`,`type`,`percentage`,`percentage_amount`,`stock_foc`,`amount`,`grn_no`,`temp_purchase_id`) VALUES
('1','ITM-005','200','100','cash','0','0','0','20000','GRN-001','1'),
('2','ITM-004','1300','4','cash','0','0','0','5200','GRN-002','2'),
('3','ITM-003','500','20','cash','0','0','0','10000','GRN-003','3'),
('4','ITM-002','20','20','cash','0','0','0','400','GRN-004','4'),
('5','ITM-001','5000','12','cash','0','0','0','60000','GRN-005','5'),
('6','ITM-006','2500','2','cash','0','0','0','5000','GRN-006','6'),
('7','ITM-006','2500','5','cash','0','0','0','12500','222','7'),
('8','ITM-004','1300','100','cash','0','0','6','130000','223','8'),
('9','ITM-001','5000','100','cash','0','0','0','500000','22231','9'),
('10','ITM-002','20','150','cash','0','0','0','3000','345','10'),
('11','ITM-005','200','20','cash','0','0','0','4000','345','10'),
('12','ITM-002','20','75','cash','0','0','0','1500','22232','11'),
('13','ITM-003','500','50','cash','0','0','0','25000','22232','11');

DROP TABLE IF EXISTS `temp_sale`;
CREATE TABLE `temp_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `so_no` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `temp_sale` (`id`,`date`,`gin_no`,`customer_id`,`type`,`so_no`,`status`) VALUES
('1','2026-02-02','S-001','CUS-000','cash','','approved'),
('2','2026-02-03','GRN-002','CUS-000','cash','','approved'),
('3','2026-02-05','S-002','CUS-000','cash','','approved'),
('5','2026-02-06','1143','CUS-000','cash','','approved'),
('10','2026-02-08','1111','CUS-000','cash','','approved'),
('11','2026-02-06','3529541','CUS-000','credit','','approved');

DROP TABLE IF EXISTS `temp_sale_items`;
CREATE TABLE `temp_sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `percentage` int(11) NOT NULL,
  `percentage_amount` int(11) NOT NULL,
  `stock_foc` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `gin_no` varchar(100) NOT NULL,
  `temp_sale_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `temp_sale_items` (`id`,`item_id`,`price`,`qty`,`type`,`percentage`,`percentage_amount`,`stock_foc`,`amount`,`gin_no`,`temp_sale_id`) VALUES
('1','ITM-006','2900','1','cash','0','0','0','2900','S-001','1'),
('2','ITM-001','5500','6','cash','0','0','0','33000','GRN-002','2'),
('3','ITM-002','700','10','cash','0','0','0','7000','GRN-002','2'),
('4','ITM-003','600','7','cash','0','0','0','4200','GRN-002','2'),
('5','ITM-004','1500','1','cash','0','0','0','1500','GRN-002','2'),
('6','ITM-005','250','5','cash','0','0','0','1250','S-002','3'),
('8','ITM-004','1500','20','cash','0','0','2','30000','1143','5'),
('11','ITM-004','1500','7','cash','0','0','0','10500','1111','10'),
('12','ITM-004','1500','25','credit','0','0','0','37500','3529541','11');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`) VALUES
('1','Admin','admin@gmail.com','$2y$10$H8rj.d8lrK9upNxBSXfESuJK8V527GNRazh4cInVoEoSmex5z5se2','1'),
('4','Neo','neo@gmail.com','$2y$10$TZNln7XBtPZH/vPKdYsVxOdeHB0t2HsyJkNTzzEDpPEmDGJ4l8bE.','2'),
('7','Mg Mg','mgmg@gmail.com','$2y$10$413eOb/Ioqu9y5tltLhER.jsRvmaL2k2rSebmJCOZ38STUa/LBly2','3');

SET FOREIGN_KEY_CHECKS = 1;
