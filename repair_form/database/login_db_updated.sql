-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 13, 2026 at 06:09 AM
-- Server version: 8.0.17
-- PHP Version: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `module_key` varchar(50) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `action_key` varchar(50) NOT NULL,
  `action_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `module_key`, `module_name`, `action_key`, `action_name`, `status`) VALUES
(1, 'dashboard', 'Dashboard', 'view', 'ดู', 'active'),
(2, 'leave', 'วันลา', 'view', 'ดู', 'active'),
(3, 'leave', 'วันลา', 'create', 'เพิ่ม', 'active'),
(4, 'leave', 'วันลา', 'edit', 'แก้ไข', 'active'),
(5, 'leave', 'วันลา', 'delete', 'ลบ', 'active'),
(6, 'leave', 'วันลา', 'approve', 'อนุมัติ', 'active'),
(7, 'leave', 'วันลา', 'manage', 'จัดการ', 'active'),
(8, 'e_document', 'หนังสือราชการ', 'view', 'ดู', 'active'),
(9, 'e_document', 'หนังสือราชการ', 'create', 'เพิ่ม', 'active'),
(10, 'e_document', 'หนังสือราชการ', 'edit', 'แก้ไข', 'active'),
(11, 'e_document', 'หนังสือราชการ', 'delete', 'ลบ', 'active'),
(12, 'e_document', 'หนังสือราชการ', 'manage', 'จัดการ', 'active'),
(13, 'vehicle', 'ยานพาหนะ', 'view', 'ดู', 'active'),
(14, 'vehicle', 'ยานพาหนะ', 'create', 'เพิ่ม', 'active'),
(15, 'vehicle', 'ยานพาหนะ', 'edit', 'แก้ไข', 'active'),
(16, 'vehicle', 'ยานพาหนะ', 'delete', 'ลบ', 'active'),
(17, 'vehicle', 'ยานพาหนะ', 'approve', 'อนุมัติ', 'active'),
(18, 'vehicle', 'ยานพาหนะ', 'manage', 'จัดการ', 'active'),
(19, 'repair', 'แจ้งซ่อม', 'view', 'ดู', 'active'),
(20, 'repair', 'แจ้งซ่อม', 'create', 'เพิ่ม', 'active'),
(21, 'repair', 'แจ้งซ่อม', 'edit', 'แก้ไข', 'active'),
(22, 'repair', 'แจ้งซ่อม', 'delete', 'ลบ', 'active'),
(23, 'repair', 'แจ้งซ่อม', 'approve', 'อนุมัติ', 'active'),
(24, 'repair', 'แจ้งซ่อม', 'manage', 'จัดการ', 'active'),
(25, 'structures', 'อาคาร', 'view', 'ดู', 'active'),
(26, 'structures', 'อาคาร', 'create', 'เพิ่ม', 'active'),
(27, 'structures', 'อาคาร', 'edit', 'แก้ไข', 'active'),
(28, 'structures', 'อาคาร', 'delete', 'ลบ', 'active'),
(29, 'structures', 'อาคาร', 'manage', 'จัดการ', 'active'),
(30, 'meeting', 'ห้องประชุม', 'view', 'ดู', 'active'),
(31, 'meeting', 'ห้องประชุม', 'create', 'จอง', 'active'),
(32, 'meeting', 'ห้องประชุม', 'edit', 'แก้ไข', 'active'),
(33, 'meeting', 'ห้องประชุม', 'delete', 'ลบ', 'active'),
(34, 'meeting', 'ห้องประชุม', 'approve', 'อนุมัติ', 'active'),
(35, 'meeting', 'ห้องประชุม', 'manage', 'จัดการ', 'active'),
(36, 'personnel', 'บุคลากร', 'view', 'ดู', 'active'),
(37, 'personnel', 'บุคลากร', 'create', 'เพิ่ม', 'active'),
(38, 'personnel', 'บุคลากร', 'edit', 'แก้ไข', 'active'),
(39, 'personnel', 'บุคลากร', 'delete', 'ลบ', 'active'),
(40, 'personnel', 'บุคลากร', 'manage', 'จัดการ', 'active'),
(41, 'users', 'ผู้ใช้งาน', 'view', 'ดู', 'active'),
(42, 'users', 'ผู้ใช้งาน', 'create', 'เพิ่ม', 'active'),
(43, 'users', 'ผู้ใช้งาน', 'edit', 'แก้ไข', 'active'),
(44, 'users', 'ผู้ใช้งาน', 'delete', 'ลบ', 'active'),
(45, 'users', 'ผู้ใช้งาน', 'manage', 'จัดการ', 'active'),
(46, 'admin', 'Admin', 'view', 'ดู', 'active'),
(47, 'admin', 'Admin', 'manage', 'จัดการ', 'active'),
(48, 'propertywork', 'ทรัพย์สิน', 'view', 'ดู', 'active'),
(49, 'propertywork', 'ทรัพย์สิน', 'create', 'เพิ่ม', 'active'),
(50, 'propertywork', 'ทรัพย์สิน', 'edit', 'แก้ไข', 'active'),
(51, 'propertywork', 'ทรัพย์สิน', 'delete', 'ลบ', 'active'),
(52, 'propertywork', 'ทรัพย์สิน', 'manage', 'จัดการ', 'active'),
(53, 'asset', 'งานทรัพย์สิน', 'view', 'ดู', 'active'),
(54, 'asset', 'งานทรัพย์สิน', 'create', 'เพิ่ม', 'active'),
(55, 'asset', 'งานทรัพย์สิน', 'edit', 'แก้ไข', 'active'),
(56, 'asset', 'งานทรัพย์สิน', 'delete', 'ลบ', 'active'),
(57, 'asset', 'งานทรัพย์สิน', 'manage', 'จัดการ', 'active'),
(58, 'attendance', 'ลงเวลา', 'view', 'ดู', 'active'),
(59, 'attendance', 'ลงเวลา', 'create', 'เพิ่ม', 'active'),
(60, 'attendance', 'ลงเวลา', 'edit', 'แก้ไข', 'active'),
(61, 'attendance', 'ลงเวลา', 'delete', 'ลบ', 'active'),
(62, 'attendance', 'ลงเวลา', 'manage', 'จัดการ', 'active'),
(63, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'view', 'ดู', 'active'),
(64, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'create', 'เพิ่ม', 'active'),
(65, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'edit', 'แก้ไข', 'active'),
(66, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'delete', 'ลบ', 'active'),
(67, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'approve', 'อนุมัติ', 'active'),
(69, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'receive', 'รับงาน', 'active'),
(68, 'computer', 'แจ้งซ่อมคอมพิวเตอร์', 'manage', 'จัดการ', 'active'),
(69, 'estate', 'ทะเบียนที่ดิน', 'view', 'ดู', 'active'),
(70, 'estate', 'ทะเบียนที่ดิน', 'create', 'เพิ่ม', 'active'),
(71, 'estate', 'ทะเบียนที่ดิน', 'edit', 'แก้ไข', 'active'),
(72, 'estate', 'ทะเบียนที่ดิน', 'delete', 'ลบ', 'active'),
(73, 'estate', 'ทะเบียนที่ดิน', 'manage', 'จัดการ', 'active'),
(74, 'finance', 'งานพัสดุ', 'view', 'ดู', 'active'),
(75, 'finance', 'งานพัสดุ', 'create', 'เพิ่ม', 'active'),
(76, 'finance', 'งานพัสดุ', 'edit', 'แก้ไข', 'active'),
(77, 'finance', 'งานพัสดุ', 'delete', 'ลบ', 'active'),
(78, 'finance', 'งานพัสดุ', 'manage', 'จัดการ', 'active'),
(79, 'maintenance', 'งานซ่อมบำรุง', 'view', 'ดู', 'active'),
(80, 'maintenance', 'งานซ่อมบำรุง', 'create', 'เพิ่ม', 'active'),
(81, 'maintenance', 'งานซ่อมบำรุง', 'edit', 'แก้ไข', 'active'),
(82, 'maintenance', 'งานซ่อมบำรุง', 'delete', 'ลบ', 'active'),
(83, 'maintenance', 'งานซ่อมบำรุง', 'manage', 'จัดการ', 'active'),
(84, 'medical', 'ศูนย์เครื่องมือแพทย์', 'view', 'ดู', 'active'),
(85, 'medical', 'ศูนย์เครื่องมือแพทย์', 'create', 'เพิ่ม', 'active'),
(86, 'medical', 'ศูนย์เครื่องมือแพทย์', 'edit', 'แก้ไข', 'active'),
(87, 'medical', 'ศูนย์เครื่องมือแพทย์', 'delete', 'ลบ', 'active'),
(88, 'medical', 'ศูนย์เครื่องมือแพทย์', 'manage', 'จัดการ', 'active'),
(89, 'security', 'รปภ.', 'view', 'ดู', 'active'),
(90, 'security', 'รปภ.', 'create', 'เพิ่ม', 'active'),
(91, 'security', 'รปภ.', 'edit', 'แก้ไข', 'active'),
(92, 'security', 'รปภ.', 'delete', 'ลบ', 'active'),
(93, 'security', 'รปภ.', 'manage', 'จัดการ', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `id` int(11) NOT NULL,
  `building_name` varchar(255) NOT NULL COMMENT 'ชื่ออาคาร',
  `amount` decimal(15,2) DEFAULT '0.00' COMMENT 'จำนวนเงิน',
  `start_date` date DEFAULT NULL COMMENT 'วันที่เริ่มสร้าง',
  `age` int(11) DEFAULT '0' COMMENT 'อายุอาคาร',
  `budget_type` varchar(100) DEFAULT NULL COMMENT 'งบประมาณ',
  `remark` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `building_name`, `amount`, `start_date`, `age`, `budget_type`, `remark`, `created_at`) VALUES
(1, 'ป้อมยาม', '0.00', '2006-01-01', 17, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(2, 'ระบบบำบัดน้ำเสีย', '0.00', '2002-01-01', 21, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(3, 'อาคารคลังพัสดุ(เอกซเรย์/บริหาร)', '243000.00', '2006-01-01', 17, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(4, 'คลังเก็บของกลาง', '0.00', '1996-01-01', 27, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(5, 'ห้องไม้กวัดนก', '0.00', '1996-01-01', 27, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(6, 'ห้องยานพาหนะ', '0.00', '1996-01-02', 10, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(7, 'ห้องพัก/ห้องเก็บของแม่บ้าน', '80000.00', '2020-05-01', 30, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(8, 'ทางเดินเชื่อมอาคารจาก NCD ถึงโรงครัว', '985000.00', '2000-01-01', 23, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(9, 'บ้านหมายเลข5(L)', '209856.00', '1992-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(10, 'บ้านพักเลขที่2 แพทย์นิติสัชกร(K)', '209856.00', '1992-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(11, 'บ้านพักเลขที่1 ผู้อำนวยการ (J)', '222720.00', '1996-01-01', 27, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(12, 'บ้านพักเลขที่4 บ้านทันตแพทย์(I)', '222720.00', '1996-01-01', 27, 'งบประมาณ', NULL, '2026-07-02 08:10:57'),
(13, 'บ้านพักเลขที่3หัวหน้ากลุ่มการพยาบาล(H)', '222720.00', '1996-01-01', 27, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(14, 'บ้านพักเลขที่6(G)', '692500.00', '2008-01-02', 15, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(15, 'บ้านพักแฟลต(F)', '222720.00', '1996-01-01', 21, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(16, 'บ้านพักชาย(E)', '310128.00', '1992-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(17, 'แฟลตชั้นหลังเก่า2ห้อง(D)', '620256.00', '1992-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(18, 'แฟลตพยาบาล 24ห้อง (C)', '8866213.00', '2016-12-09', 7, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(19, 'แฟลตพยาบาล24ห้อง (B)', '729000.00', '2022-01-01', 11, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(20, 'แฟลตพยาบาล20ห้อง (A)', '1026000.00', '2022-07-07', 26, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(21, 'บ้านพักเลขที่2', '209856.00', '2022-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(22, 'บ้านพักคนงาน (สวน)', '245760.00', '1992-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(23, 'ลานกีฬา', '300000.00', '2005-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(24, 'อาคารเก็บน้ำยาคลอรีน', '40000.00', '2008-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(25, 'ถังเก็บน้ำแบบบน ม.33', '150000.00', '1992-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(26, 'อาคารเก็บถังอ๊อกซิเจน', '23000.00', '2008-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(27, 'ที่ทิ้งขยะติดเชื้อ', '92000.00', '2006-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(28, 'เตาเผาขยะติดเชื้อ', '124460.00', '1996-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(29, 'เตาเผาขยะทั่วไป', '142240.00', '1993-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(30, 'อาคารเอนกประสงค์ พระราชทานนวราชวราราชย์', '2022640.00', '2009-01-01', NULL, 'เงินบริจาค', NULL, '2026-07-02 08:11:59'),
(31, 'โรงจอดรถบ้านพัก จนท', '142240.00', '2000-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(32, 'อาคารโรงกำเนิดไฟฟ้า', '155575.00', '1992-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(33, 'คลังยาใหญ่/ห้องServer', '800100.00', '1996-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(34, 'โรงประปาอาหาร/หน่วยจ่ายกลาง', '800100.00', '1996-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(35, 'อาคารซักฟอก/ซ่อมบำรุง', '800100.00', '1992-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(36, 'อาคารระบบประปา', '106680.00', '1992-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:11:59'),
(37, 'โรงจอดรถสำหรับรถพยาบาล', '0.00', '2022-04-01', NULL, 'เงินบริจาค', NULL, '2026-07-02 08:12:41'),
(38, 'โรงจอดรถผู้รับบริการและเจ้าหน้าที่', '243000.00', '2006-01-01', NULL, 'งบประมาณ', NULL, '2026-07-02 08:12:41'),
(39, 'อาคารแพทย์แผนไทยและกายภาพ', '1700000.00', '2011-01-01', 7, 'เงินบริจาค', NULL, '2026-07-02 08:12:41'),
(40, 'อาคารผู้ป่วยนอก 10 เตียง', '9500000.00', '1992-01-01', 31, 'งบประมาณ', NULL, '2026-07-02 08:12:41'),
(41, 'อาคารผู้ป่วยใน', '5700000.00', '2000-01-01', 31, 'เงินบำรุง', NULL, '2026-07-02 08:12:41'),
(42, 'อาคารผู้ป่วยนอก อำนวยการ', '7500000.00', '2000-01-01', 30, 'งบประมาณ', NULL, '2026-07-02 08:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ใช้งาน',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `status`, `created_at`) VALUES
(7, 'IT', 'ใช้งาน', '2026-03-06 14:56:27'),
(8, 'บริหาร', 'ใช้งาน', '2026-03-06 14:56:27'),
(9, 'ส่งเสริม', 'ใช้งาน', '2026-03-06 14:56:27');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doc_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `short_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sender_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sender_department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `send_type` enum('department','person') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'รอดำเนินการ',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sender_department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `doc_no`, `short_title`, `description`, `file_name`, `file_path`, `file_type`, `sender_name`, `sender_department`, `send_type`, `status`, `created_at`, `sender_department_id`) VALUES
(1, 1, 'd', 'e', 'e', 'ใบเสนอราคา.pdf', 'uploads/documents/1772787666_4671.pdf', 'application/pdf', 'Ople', 'e', 'person', 'แก้ไข', '2026-03-06 09:01:06', NULL),
(2, 1, 'd', 'e', 'e', 'ใบเสนอราคา.pdf', 'uploads/documents/1772787671_6023.pdf', 'application/pdf', 'Ople', 'e', 'person', 'ส่งสำเร็จ', '2026-03-06 09:01:11', NULL),
(3, 1, 'dfg', 'ทดสอบ', 'f', 'ใบสำคัญรับเงิน.pdf', 'uploads/documents/1772787760_3800.pdf', 'application/pdf', 'Ople', 'IT', 'person', 'ส่งสำเร็จ', '2026-03-06 09:02:40', NULL),
(4, 2, '12333', 'ทดสอบ', 'หนังสือรับเงิน', 'ใบส่งมอบงาน.pdf', 'uploads/documents/1772787869_8753.pdf', 'application/pdf', 'Oplee', 'IT', 'person', 'รอดำเนินการ', '2026-03-06 09:04:29', NULL),
(5, 2, '12333', 'ทดสอบ', 'หนังสือรับเงิน', 'ใบส่งมอบงาน.pdf', 'uploads/documents/1772787928_5679.pdf', 'application/pdf', 'Oplee', 'IT', 'person', 'รอดำเนินการ', '2026-03-06 09:05:28', NULL),
(6, 3, '111', 'ทดสอบ', 'E', 'payment-slip-1772784152146.png', 'uploads/documents/1772788998_3672.png', 'image/png', 'ณัฐวุฒิ วรรณพงษ์', 'It', 'person', 'ส่งสำเร็จ', '2026-03-06 09:23:18', NULL),
(7, 4, 'ทดสอบ', 'ทดสอบ', 'ทดสอบ', 'QO-20260300001.pdf', 'uploads/documents/1776322063_8371.pdf', 'application/pdf', 'Ople', 'IT', 'person', 'รอดำเนินการ', '2026-04-16 06:47:43', NULL),
(8, 5, '12333', 'ทดสอบ', 'ทดสอบ', 'Visit_site_Document (1).pdf', 'uploads/documents/1776494253_7101.pdf', 'application/pdf', 'ณัฐวุฒิ วรรณพงษ์', 'IT', 'person', 'รอดำเนินการ', '2026-04-18 06:37:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_logs`
--

CREATE TABLE `document_logs` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_logs`
--

INSERT INTO `document_logs` (`id`, `document_id`, `user_id`, `action_type`, `action_detail`, `created_at`) VALUES
(1, 1, 1, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:01:06'),
(2, 2, 1, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:01:11'),
(3, 2, 1, 'download', 'ดาวน์โหลดเอกสาร', '2026-03-06 09:02:16'),
(4, 3, 1, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:02:40'),
(5, 4, 2, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:04:29'),
(6, 5, 2, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:05:28'),
(7, 1, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น แก้ไข', '2026-03-06 09:14:18'),
(8, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น แก้ไข', '2026-03-06 09:15:40'),
(9, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น รอดำเนินการ', '2026-03-06 09:15:43'),
(10, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น แก้ไข', '2026-03-06 09:15:46'),
(11, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ยกเลิก', '2026-03-06 09:15:48'),
(12, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น รอดำเนินการ', '2026-03-06 09:16:00'),
(13, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น แก้ไข', '2026-03-06 09:16:04'),
(14, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ยกเลิก', '2026-03-06 09:16:06'),
(15, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 09:19:55'),
(16, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น รอดำเนินการ', '2026-03-06 09:20:01'),
(17, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น แก้ไข', '2026-03-06 09:20:04'),
(18, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ยกเลิก', '2026-03-06 09:20:06'),
(19, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 09:20:08'),
(20, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 09:20:40'),
(21, 6, 3, 'create', 'สร้างเอกสารใหม่', '2026-03-06 09:23:18'),
(22, 6, 3, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 09:23:29'),
(23, 3, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 09:23:44'),
(24, 2, 1, 'status_update', 'เปลี่ยนสถานะเอกสารเป็น ส่งสำเร็จ', '2026-03-06 10:43:50'),
(25, 7, 4, 'create', 'สร้างเอกสารใหม่', '2026-04-16 06:47:43'),
(26, 8, 5, 'create', 'สร้างเอกสารใหม่', '2026-04-18 06:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `document_receivers`
--

CREATE TABLE `document_receivers` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `receiver_user_id` int(11) DEFAULT NULL,
  `receiver_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `receiver_department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `receive_type` enum('department','person') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `received_at` datetime DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT '0',
  `receiver_department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_receivers`
--

INSERT INTO `document_receivers` (`id`, `document_id`, `receiver_user_id`, `receiver_name`, `receiver_email`, `receiver_department`, `receive_type`, `received_at`, `is_acknowledged`, `receiver_department_id`) VALUES
(1, 1, 2, 'Oplee', NULL, NULL, 'person', NULL, 0, NULL),
(2, 2, 2, 'Oplee', NULL, NULL, 'person', NULL, 0, NULL),
(3, 3, 2, 'Oplee', NULL, NULL, 'person', NULL, 0, NULL),
(4, 4, 1, 'Ople', NULL, NULL, 'person', NULL, 0, NULL),
(5, 5, 1, 'Ople', NULL, NULL, 'person', NULL, 0, NULL),
(6, 6, 1, 'Ople', NULL, NULL, 'person', NULL, 0, NULL),
(7, 7, 3, 'ณัฐวุฒิ วรรณพงษ์', NULL, NULL, 'person', NULL, 0, NULL),
(8, 8, 6, 'ปานจิตร', NULL, NULL, 'person', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `estate`
--

CREATE TABLE `estate` (
  `id` int(11) NOT NULL,
  `survey_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `land_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `deed_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rai` int(11) DEFAULT '0',
  `ngan` int(11) DEFAULT '0',
  `square_wa` int(11) DEFAULT '0',
  `land_office` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estate`
--

INSERT INTO `estate` (`id`, `survey_no`, `land_no`, `deed_no`, `rai`, `ngan`, `square_wa`, `land_office`, `created_at`) VALUES
(1, '123', '2565/10', '2565-1362', 5, 2, 10, 'สำนักงานที่ดินจังหวัดขอนแก่น', '2026-07-02 03:20:29');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `leave_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'รอดำเนินการ',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `head_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `head_status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `head_comment` text COLLATE utf8mb4_general_ci,
  `head_date` datetime DEFAULT NULL,
  `director_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `director_status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `director_comment` text COLLATE utf8mb4_general_ci,
  `director_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `fullname`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `created_at`, `head_name`, `head_id`, `head_status`, `head_comment`, `head_date`, `director_name`, `director_status`, `director_comment`, `director_date`) VALUES
(1, 1, 'Ople', 'ลาป่วย', '2026-03-06', '2026-03-06', 'ทดสอบ', 'อนุมัติ', '2026-03-06 07:27:51', NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL),
(2, 3, 'ณัฐวุฒิ วรรณพงษ์', 'ลาป่วย', '2026-03-06', '2026-03-06', 'R', 'อนุมัติ', '2026-03-06 09:24:37', NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL),
(3, 6, 'ปานจิตร', 'ลาป่วย', '2026-04-28', '2026-04-29', 'ตามนัด', 'รอดำเนินการ', '2026-04-17 04:52:22', NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_vacation`
--

CREATE TABLE `leave_vacation` (
  `id` int(11) NOT NULL,
  `leave_no` varchar(30) DEFAULT NULL,
  `employee_code` varchar(20) DEFAULT NULL,
  `employee_name` varchar(200) DEFAULT NULL,
  `position_name` varchar(150) DEFAULT NULL,
  `department` varchar(200) DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `leave_type` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `leave_days` int(11) DEFAULT NULL,
  `reason` text,
  `phone` varchar(30) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending_head','pending_director','approved','rejected') DEFAULT 'pending_head',
  `head_approve` varchar(150) DEFAULT NULL,
  `head_date` datetime DEFAULT NULL,
  `director_id` int(11) DEFAULT NULL,
  `director_approve` varchar(150) DEFAULT NULL,
  `director_date` datetime DEFAULT NULL,
  `remark` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `leave_vacation`
--

INSERT INTO `leave_vacation` (`id`, `leave_no`, `employee_code`, `employee_name`, `position_name`, `department`, `head_id`, `leave_type`, `start_date`, `end_date`, `leave_days`, `reason`, `phone`, `attachment`, `status`, `head_approve`, `head_date`, `director_id`, `director_approve`, `director_date`, `remark`, `created_at`) VALUES
(1, 'LV-2026-0001', '059', 'ทดสอบ', 'dsvfl', 'fdl', NULL, 'ลากิจ', '2026-08-03', '2026-08-03', 1, 'ทดสอบ', '94324', '', 'rejected', 'ณัฐวุฒิ', '2026-08-04 15:16:36', NULL, NULL, NULL, 'ก', '2026-08-03 04:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_bookings`
--

CREATE TABLE `meeting_bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `meeting_title` varchar(255) NOT NULL,
  `requester_name` varchar(150) NOT NULL,
  `department` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `attendees` int(11) DEFAULT '0',
  `detail` text,
  `status` enum('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
  `approved_by` varchar(150) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meeting_bookings`
--

INSERT INTO `meeting_bookings` (`id`, `room_id`, `meeting_title`, `requester_name`, `department`, `phone`, `meeting_date`, `start_time`, `end_time`, `attendees`, `detail`, `status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 1, 'd', 'ณัฐวุฒิ', 'd', 'd', '2026-08-13', '14:19:00', '16:19:00', 1, 'd', 'cancelled', NULL, NULL, '2026-08-13 03:20:58'),
(2, 3, 't', 'ณัฐวุฒิ', 'g', 'g', '2026-08-13', '10:21:00', '14:21:00', 1, 'g', 'rejected', NULL, NULL, '2026-08-13 03:21:19'),
(3, 1, 'ก', 'ณัฐวุฒิ', 'ทดสอบ', '94324', '2026-08-13', '11:25:00', '12:25:00', 1, '้้้', 'cancelled', NULL, NULL, '2026-08-13 03:25:29'),
(4, 3, 'd', 'ณัฐวุฒิ', 'd', 'd', '2026-08-13', '12:59:00', '16:59:00', 1, 'd', 'pending', NULL, NULL, '2026-08-13 05:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_rooms`
--

CREATE TABLE `meeting_rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(150) NOT NULL,
  `room_code` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT '0',
  `location` varchar(255) DEFAULT NULL,
  `equipment` text,
  `color` varchar(20) DEFAULT '#3f62a0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meeting_rooms`
--

INSERT INTO `meeting_rooms` (`id`, `room_name`, `room_code`, `capacity`, `location`, `equipment`, `color`, `status`, `created_at`) VALUES
(1, 'ห้องประชุมภูไท', 'PHUTHAI', 30, 'อาคารอำนวยการ ชั้น 2', 'โปรเจคเตอร์,จอภาพ,ไมโครโฟน,เครื่องเสียง', '#ef6b63', 'active', '2026-08-13 02:39:23'),
(2, 'ห้องประชุมภูทยา', 'PHUTAYA', 20, 'อาคารผู้ป่วยนอก ชั้น 2', 'จอภาพ,ไมโครโฟน,เครื่องเสียง', '#3f55e8', 'active', '2026-08-13 02:39:23'),
(3, 'ห้องประชุมพุทธา', 'PHUTTHA', 15, 'อาคารอำนวยการ ชั้น 1', 'โปรเจคเตอร์,จอภาพ', '#21c94a', 'active', '2026-08-13 02:39:23'),
(4, 'ห้องประชุมหัวหน้าฝ่ายการ', 'HEAD', 12, 'อาคารอำนวยการ ชั้น 2', 'จอภาพ,ไมโครโฟน', '#27c7d2', 'active', '2026-08-13 02:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullname` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `employee_code`, `prefix`, `fullname`, `position_name`, `department`, `department_id`, `phone`, `email`, `room_location`, `photo`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ก', 'นาย', 'ก', 'ก', 'ก', NULL, 'กก', 'd@l.com', 'ก', NULL, 'active', 'ก', '2026-08-13 03:48:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `asset_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'เลขครุภัณฑ์',
  `budget_year` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ปีงบ',
  `property_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ชื่อทรัพย์สิน',
  `property_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ประเภท',
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'หมวดหมู่',
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'ยี่ห้อ',
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'รุ่น',
  `serial_no` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Serial Number',
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'หน่วยงาน',
  `department_unit` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ประจำอยู่หน่วยงาน',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'สถานที่ใช้งาน',
  `responsible_person` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'ผู้รับผิดชอบ',
  `purchase_date` date DEFAULT NULL COMMENT 'วันที่จัดซื้อ',
  `warranty_date` date DEFAULT NULL COMMENT 'วันหมดประกัน',
  `vendor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'ผู้จำหน่าย',
  `price` decimal(12,2) DEFAULT '0.00' COMMENT 'ราคาซื้อ',
  `risk_level` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ความเสี่ยง',
  `withdraw_status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'การเบิกใช้',
  `borrow_department` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'หน่วยงานขอยืม',
  `status` enum('ใช้งาน','ชำรุด','ส่งซ่อม','จำหน่าย') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'ใช้งาน',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'รูปภาพ',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'หมายเหตุ',
  `qr_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'QR Code',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `asset_no`, `budget_year`, `property_name`, `property_type`, `category`, `brand`, `model`, `serial_no`, `department`, `department_unit`, `location`, `responsible_person`, `purchase_date`, `warranty_date`, `vendor`, `price`, `risk_level`, `withdraw_status`, `borrow_department`, `status`, `image`, `note`, `qr_code`, `created_by`, `created_at`) VALUES
(265, '4110-001-0002/32/63', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตู้เย็น', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-04-03', NULL, '6', '23900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(266, '4110-001-0002/31/63', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตู้เย็น', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-04-03', NULL, '6', '23900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(267, '4120-002-010/58/63', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-03-20', NULL, '6', '23000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(268, '4120-002-010/57/63', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-03-20', NULL, '6', '27900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(269, '4120-002-010/32/57', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2014-04-08', NULL, '12', '30900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(270, '4120-002-010/31/57', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2014-04-08', NULL, '12', '30900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 00:57:27'),
(271, '4120-002-010/33/58', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2015-04-30', NULL, '11', '36889.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(272, '4120-002-010/34/58', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ 36,498 btu', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2015-05-15', NULL, '11', '51900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(273, '4120-010/35/57', NULL, 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'เครื่องปรับอากาศ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2014-07-01', NULL, '12', '44940.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(274, '7440-016-0003/53/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'คอมพิวเตอร์ Lenovo 520-22ICB', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-08-30', NULL, '6', '17000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(275, '7440-016-0003/50/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'คอมพิวเตอร์ Lenovo 520-22ICB', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-07-10', NULL, '6', '18700.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(276, '7440-016-0003/49/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'คอมพิวเตอร์ Lenovo 520-22ICB', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2021-03-10', NULL, '5', '18700.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(277, '7440-016-0003/48/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'คอมพิวเตอร์ Lenovo 520-22ICB', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-07-10', NULL, '6', '18700.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(278, '7440-016-0003/52/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'เครื่องสแกนเนอร์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-01-15', NULL, '7', '20000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(279, '7440-016-0003/51/62', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'เครื่องปริ้นเตอร์ mp 287', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-11-26', NULL, '7', '3500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(280, '7440-016-0003/50/61', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'เครื่องพิมพ์ m6600', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-08-30', NULL, '7', '6900.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(281, '7440-016-0003/49/61', NULL, 'ครุภัณฑ์คอมพิวเตอร์', 'เครื่องพิมพ์ HP m130', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์คอมพิวเตอร์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-08-27', NULL, '7', '8990.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(282, '3210-001/7/59', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต กง7527', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2016-03-21', NULL, '10', '1990000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(283, '3210-001/6/56', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต กง4692', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2013-08-26', NULL, '12', '1800000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(284, '3210-001/5/54', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต กง7774', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2011-05-04', NULL, '15', '1799800.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(285, '2340-003-0005/1/52', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถสกู๊ดเตอร์ไฟฟ้าพร้อมตู้สแตนเลสใส่ของ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2009-02-26', NULL, '17', '99510.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(286, '3210-001/4/51', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต ยี่ห้อโตโยต้า', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2007-11-16', NULL, '18', '1295000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:00:15'),
(287, '2340-003-0004/4/49', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถจักรยานยนต์ สีแดง', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2006-09-27', NULL, '19', '35000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(288, '2340-003-0004/3/49', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถจักรยานยนต์ สีน้ำเงิน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2006-09-27', NULL, '19', '35000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(289, '2320-008-0006/1/48', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถบรรทุก (กะบะสำเร็จรูป) ยี่ห้ออีซูซุ กข8547', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2005-11-08', NULL, '20', '494000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(290, '3210-004/1', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถโดยสาร ยี่ห้อโตโยต้า 12 ที่นั่ง นข1169', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2004-12-08', NULL, '21', '990000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(291, '3210-001/3', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต ยี่ห้อโตโยต้า นข 808', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2002-03-21', NULL, '24', '945000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(292, '3210-001/2', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต ยี่ห้อโตโยต้า บจ.5701', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '1999-05-17', NULL, '27', '670000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(293, '3210-002/1', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต ยี่ห้อวอลโว่', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '1997-12-29', NULL, '28', '4000000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(294, '3210-001/1', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถตู้พยาบาล พร้อมอุปกรณ์ช่วยชีวิต เครื่องเบนซิน ยี่ห้อโตโยต้า', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '1992-11-13', NULL, '33', '500000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(295, '2320-008/1', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถบรรทุก (ปิคอัพ) น้ำหนักบรรทุก 1 ตัน เครื่องยนต์เบนซิน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '1992-12-01', NULL, '33', '350000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(296, '6530-004-1103/2/63', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องดึงคอและหลัง', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-02-20', NULL, '6', '374500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(297, '6525-008-1101/3/63', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องตรวจสมรรถภาพทารกในครรภ์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2020-02-20', NULL, '6', '308000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(298, '6515-069-3101/37/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดัน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-03-15', NULL, '7', '53500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(299, '6515-038-3091/2/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'ตู้ดูดสารกำจัดไอสารเคมี', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-01-09', NULL, '7', '90000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(300, '6520-007-0011/8/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'ยูนิตทำฟัน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-10-22', NULL, '6', '410000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(301, '6525-004-1001/3/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องเอ็กซ์เรย์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-10-15', NULL, '6', '1748000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(302, '6515-026-1002/3/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความอิ่มตัวของออกซิเจนในเลือด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-27', NULL, '8', '35000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(303, '6515-027-2002/3/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องเฝ้าระวังสัญญาณไฟฟ้าหัวใจ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-05-04', NULL, '8', '94500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(304, '6515-031-0203/4/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องให้ความอบอุ่นและช่วยชีวิตแรกเกิด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-05-04', NULL, '8', '300000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(305, '6515-014-2004/3/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องปั่นตกตะกอนหลอดเลือด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-05', NULL, '8', '87000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(306, '6530-005-1111/9/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'โคมไฟส่องทางการแพทย์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-27', NULL, '8', '15000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(307, '6515-025-1001/14/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลาย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-27', NULL, '8', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(308, '6515-025-1001/15/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลาย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-27', NULL, '8', '38000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:01:40'),
(309, '6515-058-0001/12/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องตรวจหู ตา คอ จมูก', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-27', NULL, '8', '33000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(310, '6530-001-0018/9/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-05-11', NULL, '8', '35000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(311, '6530-001-0018/8/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-05-11', NULL, '8', '35000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(312, '6515-027-1005/9/61', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องฟังเสียงหัวใจทารกในครรภ์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2018-02-14', NULL, '8', '22000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(313, '3510-012-0004/4/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องซักผ้าอุตสาหกรรม', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-10-24', NULL, '8', '760000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(314, '6515-022-3102/7/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องมือในการใส่ท่อหายใจ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-26', NULL, '9', '21500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(315, '6515-022-3102/6/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องมือในการใส่ท่อหายใจ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-26', NULL, '9', '21500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(316, '6515-003-0303/8/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องผลิตอ๊อกซิเจน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-26', NULL, '9', '25000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(317, '6515-003-0303/7/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องผลิตอ๊อกซิเจน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-26', NULL, '9', '25000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(318, '6515-027-2002/3/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องเฝ้าระวังสัญญาณไฟฟ้าหัวใจ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-26', NULL, '9', '88000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(319, '6515-006-0002/10/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องปันอีมาโตรคริท', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-24', NULL, '9', '76000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(320, '6515-006-0002/9/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องปันอีมาโตรคริท', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-24', NULL, '9', '76000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(321, '6530-001-0018/4/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วยชนิดปรับระดับได้', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-24', NULL, '9', '40000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:01'),
(322, '6530-001-0018/3/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วยชนิดปรับระดับได้', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-24', NULL, '9', '40000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(323, '6530-001-0018/2/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วยชนิดปรับระดับได้', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-24', NULL, '9', '40000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(324, '6530-001-0018/1/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เตียงผู้ป่วยชนิดปรับระดับได้', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-24', NULL, '9', '40000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(325, '6515-025-1001/11/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลาย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-22', NULL, '9', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(326, '6515-025-1001/10/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลาย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-22', NULL, '9', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(327, '6515-069-3101/32/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันอัตโนมัติ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-22', NULL, '9', '70000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(328, '6515-069-3101/31/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันอัตโนมัติ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-22', NULL, '9', '70000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(329, '6515-038-0017/2/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'ตู้เย็นใช้เก็บเลือด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-03-13', NULL, '9', '94000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(330, '6530-004-1401/2/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'หม้อต้มแผ่นประคบร้อน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-01-12', NULL, '9', '85000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(331, '6515-003-3121/5/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องช่วยหายใจอัตโนมัติ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-05-22', NULL, '7', '85000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(332, '6515-026-1002/5/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดออกซิเจนในเลือดอัตโนมัติ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-05-22', NULL, '7', '45000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(333, '6520-004-0003/7/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องขูดหินปูน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-20', NULL, '9', '18000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(334, '6515-069-3101/36/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันแบบมีล้อเลื่อน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-05-24', NULL, '9', '17500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:03:16'),
(335, '6515-027-2002/2/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความอิ่มตัวของอ๊อกซิเจนที่มี PI', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-07-31', NULL, '8', '50000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(336, '3920-005-1104/13/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'รถเข็นฉีดยาทำด้วยสแตนเลส 1 ลิ้นชัก', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-08-17', NULL, '8', '7000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(337, '6515-069-3101/35/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันอัตโนมัติที่ต้นแขน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-12-02', NULL, '8', '3600.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(338, '6515-069-3101/34/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันอัตโนมัติที่ต้นแขน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-07-31', NULL, '8', '4500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(339, '6515-069-3101/33/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องวัดความดันอัตโนมัติที่ต้นแขน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-07-31', NULL, '8', '4500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(340, '6515-025-1001/13/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลายทางหลอดเลือด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-08-29', NULL, '8', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(341, '6515-025-1001/12/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลายทางหลอดเลือด', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-08-29', NULL, '8', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(342, '6515-022-3102/8/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องมือในการใส่ท่อหายใจ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-08-29', NULL, '8', '21500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(343, '6530-004-1103/1/2555', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องดึงคอและหลังอัตโนมัติ พร้อมเตียงคอและหลัง', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2012-11-07', NULL, '13', '200000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(344, '6530-004-1106/1/54', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องทำความร้อนสำหรับต้มแผ่นประคบร้อน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2011-09-14', NULL, '14', '75500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(345, '6530-004-1105/1/54', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องให้การรักษาโดยใช้คลื่นอัลตร้าซาวด์', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2011-10-17', NULL, '14', '75000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(346, '6515-027-1004/3/57', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องกระตุกหัวใจ Difibrilator', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2014-02-20', NULL, '12', '328500.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(347, '6515-027-1004/3/53', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องกระตุกหัวใจ Difibrilator', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2010-07-08', NULL, '15', '249000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(348, '6515-025-1001/12/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องควบคุมการให้สารละลาย', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-08-29', NULL, '6', '55000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(349, '6520-007-0001/8/62', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'ยูนิตทำฟัน', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2019-10-22', NULL, '6', '410000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(350, '6515-006-0002/11/60', NULL, 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'เครื่องปันอีมาโตรคริท', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์วิทยาศาสตร์การแพทย์', 'ตกลงราคา', NULL, NULL, 'ปกติ', NULL, '2017-04-24', NULL, '9', '410000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10'),
(351, '2320-008-0004/1', NULL, 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'รถกระบะ', 'ซื้อ', 'งบประมาณ', 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'สอบราคา', NULL, NULL, 'ปกติ', NULL, '2020-12-01', NULL, '5', '826000.00', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-07-02 01:04:10');

-- --------------------------------------------------------

--
-- Table structure for table `repairs`
--

CREATE TABLE `repairs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reporter_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'รอดำเนินการ',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `technician_id` int(11) DEFAULT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `system_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repairs`
--

INSERT INTO `repairs` (`id`, `user_id`, `topic`, `reporter_name`, `detail`, `status`, `created_at`, `technician_id`, `department`, `system_type`, `location`) VALUES
(14, 5, 'ระบบแจ้งซ่อมทั่วไป', 'ณัฐวุฒิ วรรณพงษ์', '๐ฤ\"', 'รอดำเนินการ', '2026-04-18 08:51:25', 1, 'IT', 'dwd', '33'),
(15, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'ffb', 'ซ่อมเสร็จแล้ว', '2026-04-19 16:34:44', 1, NULL, NULL, NULL),
(16, 5, 'ระบบแจ้งซ่อมทั่วไป', 'ณัฐวุฒิ วรรณพงษ์', 'dd', 'รอดำเนินการ', '2026-04-19 22:32:41', 1, NULL, NULL, NULL),
(17, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:33:23', 3, NULL, NULL, NULL),
(18, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:33:31', 3, NULL, NULL, NULL),
(19, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:33:44', 3, NULL, NULL, NULL),
(20, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:33:58', 3, NULL, NULL, NULL),
(21, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:34:10', 3, NULL, NULL, NULL),
(22, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'f', 'รอดำเนินการ', '2026-04-19 22:34:55', 1, NULL, NULL, NULL),
(23, 5, 'ระบบไฟฟ้า', 'ณัฐวุฒิ วรรณพงษ์', 'f', 'รอดำเนินการ', '2026-04-19 22:36:24', 1, NULL, NULL, NULL),
(24, 5, 'ระบบแจ้งซ่อมทั่วไป', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:38:59', 1, NULL, NULL, NULL),
(25, 5, 'ระบบแจ้งซ่อมทั่วไป', 'ณัฐวุฒิ วรรณพงษ์', 'd', 'รอดำเนินการ', '2026-04-19 22:39:07', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `repair_categories`
--

CREATE TABLE `repair_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ใช้งาน',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_categories`
--

INSERT INTO `repair_categories` (`id`, `category_name`, `status`, `created_at`) VALUES
(1, 'ระบบแจ้งซ่อมทั่วไป', 'ใช้งาน', '2026-03-09 02:48:51'),
(2, 'ระบบไฟฟ้า', 'ใช้งาน', '2026-03-09 02:48:51'),
(3, 'ระบบคอมพิวเตอร์', 'ใช้งาน', '2026-03-09 02:48:51'),
(5, 'ระบบการซ่อมเครื่องมือแพทย์', 'ใช้งาน', '2026-03-09 02:48:51');

-- --------------------------------------------------------

--
-- Table structure for table `repair_jobs`
--

CREATE TABLE `repair_jobs` (
  `id` int(11) NOT NULL COMMENT 'ลำดับรายการ',
  `sender_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อผู้ส่ง/ผู้แจ้ง',
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'แผนกที่สังกัด',
  `repair_system` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รายละเอียดการแจ้งซ่อม',
  `repair_status` enum('pending','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'สถานะการซ่อม',
  `priority` enum('normal','urgent','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'normal' COMMENT 'ระดับความสำคัญ',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'หมายเหตุจากช่าง/แอดมิน',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่แจ้งซ่อม',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไขล่าสุด',
  `technician_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `repair_jobs`
--

INSERT INTO `repair_jobs` (`id`, `sender_name`, `department`, `repair_system`, `location`, `details`, `repair_status`, `priority`, `admin_note`, `created_at`, `technician_id`) VALUES
(48, 'ณัฐวุฒิ วรรณพงษ์', 'บริหาร', NULL, 'ผู้ป่วยนอก', 's', 'pending', 'normal', NULL, '2026-06-22 10:04:51', NULL),
(49, 'Oplee', 'IT', NULL, 'ผู้ป่วยใน', 's', 'pending', 'normal', NULL, '2026-06-22 10:07:15', 1),
(50, 'ณัฐวุฒิ วรรณพงษ์', 'OPD', NULL, 'ผู้ป่วยนอก', 's', 'pending', 'normal', NULL, '2026-06-23 03:03:02', 1),
(51, 'ณัฐวุฒิ วรรณพงษ์', 'IPD', NULL, 'ผู้ป่วยใน', 'gtrg', 'pending', 'normal', NULL, '2026-06-23 03:03:23', 2),
(52, 'Opleeด', 'บริหาร', 'ระบบคอมพิวเตอร์', 'ผู้ป่วยนอก', 'w', 'pending', 'normal', NULL, '2026-06-23 03:28:37', 2),
(53, 'ณัฐวุฒิ วรรณพงษ์', 'IPD', 'ระบบไฟฟ้า', 'ผู้ป่วยนอก', 's', 'pending', '', NULL, '2026-06-23 04:32:05', 2),
(54, 'ณัฐวุฒิ วรรณพงษ์', 'IT', 'ระบบจัดการเครื่องมือแพทย์', 'บริหาร', 'd', 'pending', '', NULL, '2026-06-23 04:39:22', 3),
(55, 'ณัฐวุฒิ วรรณพงษ์', 'บริหาร', 'ระบบคอมพิวเตอร์', 'ผู้ป่วยนอก', 'DX', 'pending', '', NULL, '2026-06-23 04:42:09', 3),
(56, 'Ople', 'OPD', 'ระบบไฟฟ้า', 'ตึก10เตียง', 'S', 'pending', '', NULL, '2026-06-23 04:43:59', 3),
(57, 'ณัฐวุฒิ วรรณพงษ์', 'IT', 'ระบบจัดการเครื่องมือแพทย์', 'ผู้ป่วยใน', 's', 'pending', 'normal', NULL, '2026-06-23 05:04:02', 2),
(58, 'ณัฐวุฒิ วรรณพงษ์', 'IPD', 'ระบบทั่วไป', 'ผู้ป่วยใน', 'ห', 'pending', 'emergency', NULL, '2026-06-23 05:18:36', 2);

-- --------------------------------------------------------

--
-- Table structure for table `repair_receive_jobs`
--

CREATE TABLE `repair_receive_jobs` (
  `id` int(11) NOT NULL,
  `repair_job_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `technician_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `repair_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `qty` int(11) DEFAULT '0',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `technician_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_receive_jobs`
--

INSERT INTO `repair_receive_jobs` (`id`, `repair_job_id`, `technician_id`, `technician_name`, `status`, `repair_type`, `device_name`, `price`, `qty`, `total_price`, `technician_note`, `created_at`) VALUES
(1, 58, 7, 'ณัฐวุฒิ', 'เสร็จสิ้น', 'ส่งซ่อมข้างนอก', 'หน้าจอ', '0.00', 1, '0.00', 'หกป', '2026-06-24 05:13:27'),
(2, 56, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ซ่อมเอง', 'หน้าจอ', '244.00', 1, '244.00', 'หน้าจอ', '2026-06-24 05:18:18'),
(3, 58, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ซ่อมเอง', 'หน้าจอ', '0.00', 1, '0.00', '', '2026-06-24 05:20:37'),
(4, 56, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ส่งซ่อมข้างนอก', 'หน้าจอ', '0.00', 1, '0.00', 'ด', '2026-06-24 06:07:09'),
(5, 58, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ส่งซ่อมข้างนอก', 'หน้าจอ', '0.00', 1, '0.00', 'dx', '2026-06-25 01:27:10'),
(6, 52, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ซ่อมเอง', 'หน้าจอ', '10.00', 1, '10.00', 'ทดสอบ', '2026-06-25 01:39:22'),
(7, 54, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ซ่อมเอง', 'หน้าจอ', '0.00', 1, '0.00', 'ก', '2026-06-25 01:46:03'),
(8, 58, 7, 'ณัฐวุฒิ', 'กำลังดำเนินการ', 'ซ่อมเอง', '', '0.00', 1, '0.00', '', '2026-07-02 06:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `id` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ใช้งาน',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`id`, `name`, `department`, `status`, `created_at`) VALUES
(1, 'นาย วโรดม ทรวงโพธิ์', 'ไฟฟ้า', 'ใช้งาน', '2026-04-18 04:27:15'),
(2, 'นาย พิษณุ ขุนสูงเนิน	', 'คอมพิวเตอร์', 'ใช้งาน', '2026-04-18 04:27:15'),
(3, 'นาย ทวี แก้วสีบุตร', 'แอร์', 'ใช้งาน', '2026-04-18 04:27:15'),
(4, 'นาย ณัฐวุฒิ วรรณพงษ์', 'ประปา', 'ใช้งาน', '2026-04-18 04:27:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `role` enum('admin','technician','manager','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `supervisor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `created_at`, `email`, `department`, `department_id`, `status`, `role`, `supervisor_id`) VALUES
(5, 'ณัฐวุฒิ วรรณพงษ์', 'Natthawut', '$2y$10$2tB9CcN3wtiHm0YROL3OZeSdgZlDCCbTxio1LREOZxGa5h96bAqny', '2026-04-16 07:37:46', NULL, NULL, NULL, 'active', 'user', NULL),
(6, 'ปานจิตร', 'Panjit', '$2y$10$c6YO9iT.PV/hmnxH5rBJCuWr.LbEID640hC8yQpXOClN3oqykwgcG', '2026-04-17 04:43:43', NULL, NULL, NULL, 'active', '', NULL),
(7, 'ณัฐวุฒิ', 'root', '$2y$10$FOuCMsaqYGZpjoo2fN9AFenqQlIHuoUtXG2Xeq1sbztX2xFpjqL.y', '2026-06-19 02:44:30', NULL, NULL, NULL, 'active', 'admin', NULL),
(8, 'ณัฐวุฒิ', 'roott', '$2y$10$b.kGHoY4w4WUp.NmblVnjugDEfodO0icU/BQ0PghUiEbl0ftjZV.m', '2026-08-05 02:01:08', NULL, NULL, NULL, 'active', 'admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `created_at`) VALUES
(3, 5, 'meeting.view', '2026-08-13 05:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_requests`
--

CREATE TABLE `vehicle_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `urgency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `car` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `use_date` date DEFAULT NULL,
  `use_time` time DEFAULT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `companions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_requests`
--

INSERT INTO `vehicle_requests` (`id`, `user_id`, `fullname`, `subject`, `urgency`, `location`, `car`, `use_date`, `use_time`, `detail`, `document`, `created_at`, `companions`) VALUES
(48, 5, 'Ople', NULL, NULL, NULL, 'f', '2026-05-23', NULL, 'พดพด', NULL, '2026-05-15 03:46:45', 'Array'),
(49, 5, 'Ople', 'ประชุม', 'ปกติ', 'สสจ', 'f', '2026-05-23', '10:49:00', 'พดพด', NULL, '2026-05-15 03:46:45', 'ณัฐวุฒิ วรรณพงษ์'),
(50, 7, 'natthwaut wannaphong', NULL, NULL, NULL, '355', '2026-06-23', NULL, 'se', NULL, '2026-06-23 06:21:44', 'Array'),
(51, 7, 'natthwaut wannaphong', 'ประชุม', 'ด่วนมาก', 'สสจ', '355', '2026-06-23', '14:21:00', 'se', NULL, '2026-06-23 06:21:44', 'ณัฐวุฒิ วรรณพงษ์, ปานจิตร');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_module_action` (`module_key`,`action_key`);

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_receivers`
--
ALTER TABLE `document_receivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estate`
--
ALTER TABLE `estate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_vacation`
--
ALTER TABLE `leave_vacation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meeting_bookings`
--
ALTER TABLE `meeting_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`meeting_date`),
  ADD KEY `idx_room` (`room_id`);

--
-- Indexes for table `meeting_rooms`
--
ALTER TABLE `meeting_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_code` (`employee_code`),
  ADD KEY `idx_fullname` (`fullname`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repairs`
--
ALTER TABLE `repairs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_categories`
--
ALTER TABLE `repair_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_jobs`
--
ALTER TABLE `repair_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_receive_jobs`
--
ALTER TABLE `repair_receive_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_permission` (`user_id`,`permission_key`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `document_logs`
--
ALTER TABLE `document_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `document_receivers`
--
ALTER TABLE `document_receivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `estate`
--
ALTER TABLE `estate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_vacation`
--
ALTER TABLE `leave_vacation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meeting_bookings`
--
ALTER TABLE `meeting_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meeting_rooms`
--
ALTER TABLE `meeting_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352;

--
-- AUTO_INCREMENT for table `repairs`
--
ALTER TABLE `repairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `repair_categories`
--
ALTER TABLE `repair_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `repair_jobs`
--
ALTER TABLE `repair_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ลำดับรายการ', AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `repair_receive_jobs`
--
ALTER TABLE `repair_receive_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meeting_bookings`
--
ALTER TABLE `meeting_bookings`
  ADD CONSTRAINT `fk_room` FOREIGN KEY (`room_id`) REFERENCES `meeting_rooms` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
