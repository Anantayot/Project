-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2025 at 03:27 PM
-- Server version: 10.5.29-MariaDB-0+deb11u1
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mycommiss`
--
CREATE DATABASE IF NOT EXISTS `mycommiss` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mycommiss`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `name`, `created_at`) VALUES
(1, 'admin', '1234', 'Administrator', '2025-10-04 22:21:50');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `cat_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `cat_name`, `cat_description`) VALUES
(1, 'NOTEBOOK (โน้ตบุ๊ค)', ''),
(2, 'KEYBOARD (คีย์บอร์ด)', ''),
(3, 'Gaming Chair (เก้าอี้เกมมิ่ง)', ''),
(4, 'MONITOR (จอมอนิเตอร์)', ''),
(5, 'Game controller/Joystick ', ''),
(6, 'COOLING SYSTEM (ชุดระบายความร้อน)', ''),
(7, 'COMPUTER SET (คอมพิวเตอร์ยกเซ็ด)', ''),
(8, 'MOUSE (เมาส์)', ''),
(9, 'Gaming Headset (หูฟังเกมมิ่ง)', ''),
(10, 'SPEAKER (ลำโพง)', ''),
(11, 'UPS (เครื่องสำรองไฟฟ้า)', ''),
(12, 'VGA (การ์ดแสดงผล)', ''),
(13, 'เว็บแคม (WEBCAM)', ''),
(14, 'Smart TV', ''),
(15, 'SMARTPHONE (สมาร์ทโฟน)', ''),
(17, 'CALCULATOR (เครื่องคิดเลข) ', ''),
(18, 'PROJECTOR (โปรเจคเตอร์)', ''),
(19, 'กล้องถ่ายรูป (camera)', '');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscribe` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `password`, `phone`, `address`, `created_at`, `subscribe`) VALUES
(1, 'อนันตยศ อินทราพงษ์', '67010974018@msu.ac.th', '$2y$10$g2TOwNeq8WS/qknez/Dml.eHW.AdPRzysgMa7RcKhqjHCaeDQkUYi', '0903262100', 'ประจวบ', '2025-10-16 09:11:21', 0),
(2, 'นายวีรภัทร สุพร', '67010974016@msu.ac.th', '$2y$10$0BEEyQ050EjANd8vHl2Qju.15EijEZGyb2BT6zSf9xAuW9r9hDRHe', '0933705611', '57/4', '2025-10-18 01:53:15', 0),
(3, 'Ronaldo', '67010974003@msu.ac.th', '$2y$10$333UZ7MH5uDB/Jle9GNxVeTPdZpNA7zbuGMqu6S3SWnxTU58wWE5i', '0622301236', 'โปรตุเกต', '2025-10-18 06:10:54', 0),
(4, 'ฟหกฟหกฟห', 'aatad@asdasd', '$2y$10$eLOcVFss4Zc9V83ebKcIgut/1NsuLIIPR/Ly6cPHd8vXaCXbnVopu', '4894984894', '84894', '2025-10-18 06:28:08', 0),
(5, 'รัฐศาสตร์ บรรจงกุล', '67010974013@msu.ac.th', '$2y$10$XeqiNnPnqjtg2zre/VEZGeqI9CDy0rGDEsEUrvOiKj3NPU43UXTkK', '0826968780', 'ปารีส', '2025-10-18 06:46:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` enum('COD','QR') DEFAULT 'COD',
  `slip_image` varchar(255) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_status` enum('รอดำเนินการ','ชำระเงินแล้ว','ยกเลิก') DEFAULT 'รอดำเนินการ',
  `admin_verified` enum('รอตรวจสอบ','กำลังตรวจสอบ','อนุมัติ','ปฏิเสธ') DEFAULT 'รอตรวจสอบ',
  `order_status` enum('รอดำเนินการ','กำลังจัดเตรียม','จัดส่งแล้ว','สำเร็จ','ยกเลิก') DEFAULT 'รอดำเนินการ',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipped_date` datetime DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `total_price`, `shipping_address`, `payment_method`, `slip_image`, `payment_date`, `payment_status`, `admin_verified`, `order_status`, `order_date`, `shipped_date`, `tracking_number`, `note`) VALUES
(4, 1, '44880.00', 'ประจวบ', 'COD', NULL, NULL, 'ชำระเงินแล้ว', 'รอตรวจสอบ', 'สำเร็จ', '2025-10-16 11:02:03', NULL, NULL, NULL),
(5, 1, '35890.00', 'ประจวบ', 'COD', NULL, NULL, 'ชำระเงินแล้ว', 'อนุมัติ', 'สำเร็จ', '2025-10-16 11:03:32', NULL, NULL, NULL),
(6, 1, '39890.00', 'ประจวบ', 'COD', NULL, NULL, 'รอดำเนินการ', 'รอตรวจสอบ', 'รอดำเนินการ', '2025-10-16 11:04:17', NULL, NULL, NULL),
(7, 1, '19900.00', 'ประจวบ', 'QR', 'slip_1760616764_5719.jpeg', '2025-10-16 19:12:44', 'ชำระเงินแล้ว', 'อนุมัติ', 'กำลังจัดเตรียม', '2025-10-16 11:08:54', NULL, NULL, NULL),
(8, 1, '23900.00', 'ประจวบ', 'QR', 'slip_1760618196_5197.jpeg', '2025-10-16 19:36:36', 'ชำระเงินแล้ว', 'อนุมัติ', 'กำลังจัดเตรียม', '2025-10-16 11:09:18', NULL, NULL, NULL),
(9, 1, '15990.00', 'ประจวบ', 'QR', 'slip_1760618311_4171.jpeg', '2025-10-16 19:38:31', 'ยกเลิก', 'ปฏิเสธ', 'ยกเลิก', '2025-10-16 11:10:11', NULL, NULL, NULL),
(10, 1, '23900.00', 'ประจวบ', 'QR', 'slip_1760614847_5683.jpeg', '2025-10-16 18:40:47', 'ชำระเงินแล้ว', NULL, 'รอดำเนินการ', '2025-10-16 11:18:01', NULL, NULL, NULL),
(11, 1, '29790.00', 'ประจวบ', 'QR', 'slip_1760615261_6906.jpeg', '2025-10-16 18:47:41', 'ชำระเงินแล้ว', NULL, 'รอดำเนินการ', '2025-10-16 11:18:20', NULL, NULL, NULL),
(12, 1, '13990.00', 'ประจวบ', 'QR', 'slip_1760616090_7031.jpeg', '2025-10-16 19:01:30', 'รอดำเนินการ', 'กำลังตรวจสอบ', 'รอดำเนินการ', '2025-10-16 11:20:37', NULL, NULL, NULL),
(13, 1, '250.00', 'ประจวบ', 'QR', 'slip_1760615717_4074.jpeg', '2025-10-16 18:55:17', 'ยกเลิก', 'ปฏิเสธ', 'ยกเลิก', '2025-10-16 11:22:47', NULL, NULL, NULL),
(14, 1, '55960.00', 'ประจวบ', 'QR', NULL, NULL, 'ยกเลิก', NULL, 'ยกเลิก', '2025-10-16 11:46:23', NULL, NULL, NULL),
(15, 3, '19900.00', 'ซาอุดิอารเบีย - โปรตุเกต', 'QR', NULL, NULL, 'ยกเลิก', 'รอตรวจสอบ', 'ยกเลิก', '2025-10-18 06:11:28', NULL, NULL, NULL),
(16, 4, '23900.00', '84894', 'QR', NULL, NULL, 'รอดำเนินการ', NULL, 'รอดำเนินการ', '2025-10-18 06:33:26', NULL, NULL, NULL),
(17, 4, '23000.00', '84894', 'COD', NULL, NULL, 'รอดำเนินการ', 'รอตรวจสอบ', 'รอดำเนินการ', '2025-10-18 06:39:00', NULL, NULL, NULL),
(18, 5, '40900.00', 'ปารีส', 'QR', NULL, NULL, 'รอดำเนินการ', 'รอตรวจสอบ', 'รอดำเนินการ', '2025-10-18 06:47:57', NULL, NULL, NULL),
(19, 1, '23900.00', 'ประจวบ', 'QR', NULL, NULL, 'รอดำเนินการ', NULL, 'รอดำเนินการ', '2025-10-18 06:52:39', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `p_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `p_id`, `quantity`, `price`) VALUES
(7, 4, 4, 1, '15990.00'),
(8, 4, 8, 1, '19900.00'),
(9, 4, 17, 1, '8990.00'),
(10, 5, 4, 1, '15990.00'),
(11, 5, 8, 1, '19900.00'),
(12, 6, 4, 1, '15990.00'),
(13, 6, 3, 1, '23900.00'),
(14, 7, 8, 1, '19900.00'),
(15, 8, 3, 1, '23900.00'),
(16, 9, 4, 1, '15990.00'),
(17, 10, 3, 1, '23900.00'),
(18, 11, 3, 1, '23900.00'),
(19, 11, 19, 1, '5890.00'),
(20, 12, NULL, 1, '13990.00'),
(21, 13, 12, 1, '250.00'),
(22, 14, NULL, 4, '13990.00'),
(23, 15, 8, 1, '19900.00'),
(24, 16, 3, 1, '23900.00'),
(25, 17, 5, 1, '23000.00'),
(26, 18, 162, 1, '40900.00'),
(27, 19, 3, 1, '23900.00');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `p_stock` int(11) NOT NULL DEFAULT 0,
  `p_description` text DEFAULT NULL,
  `p_image` varchar(255) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`p_id`, `p_name`, `p_price`, `p_stock`, `p_description`, `p_image`, `cat_id`, `created_at`) VALUES
(3, 'ACER ASPIRE7 A715-59G-550T - TITANIUM BLACK', '23900.00', 6, 'Acer Aspire 7 คือโน้ตบุ๊กที่รวมความแรงและความหรูไว้ในเครื่องเดียว ดีไซน์เรียบเท่เหมาะกับทุกโอกาส มอบประสบการณ์ใช้งานลื่นไหลทั้งทำงานและเล่นเกม พกพาสะดวก ใช้งานได้มั่นใจในทุกสถานการณ์\r\n\r\n• ซีพียู : Intel Core i5-13420H\r\n• แรม : 16GB DDR4\r\n• เอสเอสดี : 512GB PCIe 4/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920x1080) IPS 144Hz\r\n• กราฟิก : Nvidia GeForce RTX3050 6GB GDDR6\r\n• ซอฟต์แวร์ : Windows 11 Home\r\n', '1759726356_3.jpg', 1, '2025-10-06 04:48:35'),
(4, 'ACER ASPIRE LITE 15 AL15-52P-586H – SILVER', '15990.00', 15, 'Acer Aspire Lite แล็ปท็อปบางเบา ดีไซน์หลากหลาย ตอบโจทย์ทุกไลฟ์สไตล์ รวมความสวยงามและประสิทธิภาพ มอบประสบการณ์ใช้งานที่ครบครันทั้งด้านดีไซน์และการประมวลผล\r\n\r\n• ซีพียู : Intel Core 5 120U\r\n• แรม : 16GB DDR5\r\n• เอสเอสดี : 512GB PCIe/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920x1080) IPS\r\n• กราฟิก : Intel Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home & Student 2024\r\n', '1759726597_4.jpg', 1, '2025-10-06 04:56:37'),
(5, 'ASUS VIVOBOOK 16 M1607KA-MB556WA - QUIET BLUE', '23000.00', 8, 'ASUS Vivobook 16 M1607KA-MB556WA มาพร้อมซีพียู AMD Ryzen AI 5 330 แรม 16GB DDR5 และ SSD ความจุ 512GB ให้ประสิทธิภาพที่รวดเร็ว หน้าจอ 16 นิ้ว WUXGA IPS รองรับการทำงานและความบันเทิงได้อย่างลงตัว กราฟิก AMD Radeon พร้อม Windows 11 Home และ Office Home 2024 ที่ใช้งานได้ทันที\r\n\r\n• ซีพียู : AMD Ryzen AI 5 330\r\n• แรม : 16GB DDR5 on board\r\n• เอสเอสดี : 512GB PCIe 4/NVMe M.2 SSD\r\n• จอแสดงผล : 16.0\" WUXGA (1920 x 1200) IPS-level Panel 60Hz refresh rate Anti-glare\r\n• กราฟิก : AMD Radeon Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home 2024 / Microsoft 365 Basic\r\n', '1759726835_5.jpg', 1, '2025-10-06 05:00:35'),
(6, 'DELL 15 DC15250I5161 - PLATINUM SILVER', '20900.00', 10, 'Dell 15 DC15250I5161 เหมาะกับงานเอกสารและใช้งานทั่วไป มาพร้อม Intel Core i5-1334U, RAM 16GB และ SSD 512GB ทำงานรวดเร็ว จอ 15.6” FHD 120Hz ภาพลื่นตา ใช้งานได้ครบทั้งทำงานและเรียน ราคาคุ้มค่า เหมาะกับคนที่ต้องการโน้ตบุ๊คประสิทธิภาพสมดุล\r\n\r\n• ซีพียู : Intel Core i5-1334U\r\n• แรม : 16GB DDR4 2666 MT/s\r\n• เอสเอสดี : 512GB PCIe/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920 x 1080) WVA 120Hz 250 nits Anti-Glare LED Backlit\r\n• กราฟิก : Intel UHD Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home 2024 / Microsoft 365 Basic\r\n', '1759726932_6.jpg', 1, '2025-10-06 05:02:12'),
(7, 'ASUS VIVOBOOK S16 D3607KA-OLED777WA - MATTE GRAY', '32900.00', 3, 'ASUS Vivobook S16 D3607KA มาพร้อม AMD Ryzen AI 7 350, RAM 32GB และ SSD 1TB จอ OLED 16” FHD สีสวย DCI-P3 95% ตอบสนองไว 0.2ms เหมาะทั้งงานกราฟิกและทำงานทั่วไป น้ำหนักเบา ดีไซน์พรีเมียม คุ้มค่ากับสายทำงานและความบันเทิง\r\n\r\n• ซีพียู : AMD Ryzen AI 7 350\r\n• แรม : 32GB (16GB x 2) DDR5\r\n• เอสเอสดี : 1TB PCIe 4/NVMe M.2 SSD\r\n• จอแสดงผล : 16\" FHD (1920 x 1200) OLED 0.2ms 60Hz 300nits DCI-P3 95%\r\n• กราฟิก : AMD Radeon Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home 2024 / Microsoft 365 Basic\r\n', '1759726997_7.jpg', 1, '2025-10-06 05:03:17'),
(8, 'ACER X BUTTERBEAR ASPIRE LITE 15 LIMITED EDITION AL15-42P-R6N3', '19900.00', 7, 'Acer จับมือ Butterbear เปิดตัวโน้ตบุ๊คดีไซน์สุดคิวต์ Aspire Lite 15 Limited Edition ลายหมี Butterbear น่ารักสดใส เหมาะกับสายครีเอทีฟหรือคนรักของน่ารัก ใช้งานได้ทั้งทำงานและเรียนในสไตล์ที่เป็นตัวเอง\r\n\r\n• ซีพียู : AMD Ryzen 5 7430U\r\n• แรม : 32GB DDR4\r\n• เอสเอสดี : 512GB PCIe/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920x1080)\r\n• กราฟิก : AMD Radeon Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home & Student 2024 / Microsoft 365 Basic\r\n', '1759727064_8.jpg', 1, '2025-10-06 05:04:24'),
(9, 'MSI VECTOR A16 HX A8WHG-020TH - COSMOS GRAY', '62900.00', 5, 'MSI Vector A16 HX แล็ปท็อปสายประสิทธิภาพที่ออกแบบมาสำหรับเกม งาน AI และ STEM ดีไซน์ทรงพลัง พกพาได้ ให้ประสบการณ์ที่รวดเร็ว ลื่นไหล และมั่นคง เหมาะกับผู้ใช้ที่ต้องการเครื่องแรงครบเครื่องในทุกสถานการณ์\r\n\r\n• ซีพียู : AMD Ryzen 9 8940HX\r\n• แรม : 16GB DDR5\r\n• เอสเอสดี : 512GB PCIe 4/NVMe M.2 SSD\r\n• จอแสดงผล : 16\" QHD+ (2560x1600) IPS 240Hz\r\n• กราฟิก : Nvidia GeForce RTX5070Ti 12GB GDDR7\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home & Student 2024\r\n', '1759727154_9.jpg', 1, '2025-10-06 05:05:54'),
(10, 'MSI RAIDER 18 HX AI A2XWJG-869TH - CORE BLACK', '165000.00', 4, 'MSI Raider 18 HX AI คือสุดยอดโน้ตบุ๊กเกมมิ่งระดับเดสก์ท็อปที่ผสานเทคโนโลยี AI ล้ำสมัยและจอแสดงผล Mini LED 4K 120 Hz เข้าไว้ด้วยกัน ด้วยดีไซน์ที่แข็งแกร่งและสมดุล มอบทั้งความแรงและความสวยงามให้พร้อมสำหรับทุกการสร้างสรรค์และเกมแรกของคุณ\r\n\r\n• ซีพียู : Intel Core Ultra 9 285HX\r\n• แรม : 64GB DDR5\r\n• เอสเอสดี : 2TB PCIe 5/NVMe M.2 SSD\r\n• จอแสดงผล : 18\" UHD+ (3840x2400) IPS 120Hz\r\n• กราฟิก : Nvidia GeForce RTX5090 24GB GDDR7\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home & Student 2024\r\n', '1759727248_10.jpg', 1, '2025-10-06 05:07:28'),
(11, 'STEELSERIES APEX PRO MINI OMNIPOINT SWITCH RGB EN – BLACK', '11490.00', 10, 'SteelSeries Apex Pro Mini เป็นคีย์บอร์ดเกมมิ่งไซซ์เล็กแบบ 60% การเชื่อมต่อไร้สายแบบ Dual Mode ทั้ง 2.4GHz Wireless สำหรับการเล่นเกมที่ต้องการความเสถียรและความหน่วงต่ำ และ Bluetooth สำหรับการใช้งานทั่วไปหรือเชื่อมต่อกับอุปกรณ์พกพาที่มาพร้อมกับสวิตช์แม่เหล็ก OmniPoint 2.0 Switch ซึ่งเป็นเทคโนโลยีสุดล้ำจาก SteelSeries ที่สามารถปรับความลึกของการกดได้ระหว่าง 0.1 – 4.0 มม. ตอบโจทย์ทั้งเกมเมอร์และสายพิมพ์ที่ต้องการความแม่นยำและตอบสนองไว\r\n\r\n• สวิตช์ : OmniPoint 2.0 Switch (Magnetic)\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ\r\n• เลย์เอาต์ : ANSI\r\n• ขนาดคีย์บอร์ด : 60% (Compact)\r\n• การเชื่อมต่อ : สาย USB-C เป็น USB-A แบบถอดออกได้, ไร้สาย 2.4GHz, บลูทูธ\r\n', '1759727672_11.jpg', 2, '2025-10-06 05:14:32'),
(12, 'SIGNO KB-712 (RUBBER DOME) (ILLUMINATED)', '250.00', 50, '• Switch : Rubber Dome\r\n• Lighting : 3 Mode LED\r\n• Keycap Font : English/Thai\r\n• Connectivity : USB\r\n', '1759727729_12.jpg', 2, '2025-10-06 05:15:29'),
(13, 'AJAZZ AK680 V2 - RED SWITCH RAINBOW LED EN/TH PURPLE', '590.00', 20, 'Ajazz AK680 V2 คีย์บอร์ดขนาด 65% ที่กะทัดรัดและใช้งานสะดวก มาพร้อมแสงไฟ Rainbow LED เพิ่มความสวยงามและบรรยากาศการใช้งาน รองรับเลย์เอาต์ ANSI มาตรฐาน ใช้งานผ่านการเชื่อมต่อแบบมีสายเพื่อความเสถียรสูงสุด มาพร้อมสาย USB-C to USB-A ที่แข็งแรงทนทาน เหมาะทั้งสำหรับการทำงานและการเล่นเกม ให้คุณสัมผัสการพิมพ์ที่คล่องตัวและประสบการณ์ที่เหนือระดับ\r\n\r\n• สวิตช์ : Red Switch (Linear)\r\n• ขนาด : 65%\r\n• แสงไฟ : Rainbow LED\r\n• คีย์แคป : ภาษาอังกฤษ / ภาษาไทย\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n', '1759727931_13.jpg', 2, '2025-10-06 05:18:51'),
(14, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) ONEX GX3 (BLACK)', '5890.00', 28, 'Gaming Chair', '1759728003_1.jpg', 3, '2025-10-06 05:20:03'),
(15, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) THERMALTAKE CYBERCHAIR E500 (GGC-EG5-BBLFDM-01) (BLACK)', '16900.00', 29, 'Gaming Chair\r\n', '1759728079_2.jpg', 3, '2025-10-06 05:21:19'),
(16, 'AJAZZ AK680 V2 - RED SWITCH RAINBOW LED EN/TH RED', '590.00', 12, 'Ajazz AK680 V2 คีย์บอร์ดขนาด 65% ที่กะทัดรัดและใช้งานสะดวก มาพร้อมแสงไฟ Rainbow LED เพิ่มความสวยงามและบรรยากาศการใช้งาน รองรับเลย์เอาต์ ANSI มาตรฐาน ใช้งานผ่านการเชื่อมต่อแบบมีสายเพื่อความเสถียรสูงสุด มาพร้อมสาย USB-C to USB-A ที่แข็งแรงทนทาน เหมาะทั้งสำหรับการทำงานและการเล่นเกม ให้คุณสัมผัสการพิมพ์ที่คล่องตัวและประสบการณ์ที่เหนือระดับ\r\n\r\n• สวิตช์ : Red Switch (Linear)\r\n• ขนาด : 65%\r\n• แสงไฟ : Rainbow LED\r\n• คีย์แคป : ภาษาอังกฤษ / ภาษาไทย\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n', '1759728127_14.jpg', 2, '2025-10-06 05:22:07'),
(17, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) DUCKY HURRICANE GAMING (DCHU1801) (BLACK)', '8990.00', 21, 'Gaming Chair', '1759728149_3.jpg', 3, '2025-10-06 05:22:29'),
(18, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) ONEX GX3 (BLACK-BLUE) ', '5890.00', 32, 'Gaming Chair', '1759728208_4.jpg', 3, '2025-10-06 05:23:28'),
(19, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) ONEX GX3 (BLACK-RED) ', '5890.00', 30, 'Gaming Chair', '1759728247_5.jpg', 3, '2025-10-06 05:24:07'),
(20, 'ACER AETHON 303 RGB – BLACK', '1990.00', 5, 'Acer Aethon 303 คีย์บอร์ดเกมมิ่งแบบมีสายที่มาพร้อมสวิตช์ Kailh Blue ให้สัมผัสการพิมพ์ที่แม่นยำและชัดเจน ดีไซน์แข็งแรงด้วยแผ่นอลูมิเนียมสีดำ ทนทานกว่า 50 ล้านครั้ง พร้อมไฟ RGB สุดตระการตา มอบประสบการณ์การเล่นเกมที่ดุดันและเต็มไปด้วยสไตล์\r\n\r\n• สวิตช์ : Kailh Blue Switches\r\n• ขนาด : 100% (Full-size)\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ / ภาษาไทย\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n', '1759728259_15.jpg', 2, '2025-10-06 05:24:19'),
(21, 'MING CGAHAIR (เก้าอี้เกมมิ่ง) COUGAR GAMING ARMOR TITAN (BLACK-ORANGE) ', '10900.00', 25, 'Gaming Chair\r\n', '1759728292_6.jpg', 3, '2025-10-06 05:24:52'),
(22, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) SIGNO E-SPORT BRANCO (GC-207BR) (BLACK-RED)', '5690.00', 29, 'Gaming Chair\r\n', '1759728363_7.jpg', 3, '2025-10-06 05:25:31'),
(23, 'GRAVASTAR MERCURY V75 SPECIAL EDITION - MAGNETIC SWITCH RGB EN LAVENDER PURPLE', '2590.00', 6, 'Gravastar Mercury V75 คีย์บอร์ดเกมมิ่งความเร็วสูง รองรับอัตรารีเฟรช 8,000Hz และสแกนคีย์ 256kHz ลดดีเลย์เหลือเพียง 0.125ms ปรับความลึกของการกดได้ตั้งแต่ 0.1–3.5 มม. รองรับโหมด Rapid Trigger และ LKP เพื่อความแม่นยำและตอบสนองที่เหนือกว่า โครงสร้างอะลูมิเนียมผสมพลาสติก แข็งแรงและเบา รองรับ Hot-Swap เปลี่ยนสวิตช์แม่เหล็กได้โดยไม่ต้องบัดกรี มาพร้อมโฟมซับเสียง 5 ชั้น และไฟ RGB ปรับได้ 16 โหมด แยกโซนอย่างอิสระ\r\n\r\n• สวิตช์ : Magnetic Switch (Linear)\r\n• ขนาด : 75%\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n• การเปลี่ยนสวิตช์ : เปลี่ยนสวิตช์ได้\r\n', '1759728350_16.jpg', 2, '2025-10-06 05:25:50'),
(24, 'GRAVASTAR MERCURY K1 LITE - GRAVASTAR X BSUN LINEAR SWITCH RGB EN CRYSTAL AURORA', '2990.00', 11, 'Gravastar Mercury K1 Lite คีย์บอร์ดที่เบาที่สุดในตระกูล K1 มาพร้อมดีไซน์โครงสร้างโพลีเมอร์แบบออร์แกนิกที่ให้ความแข็งแรงและเบาเป็นพิเศษ แตกต่างจากโลหะแบบเดิม พร้อมดีไซน์ที่เป็นเอกลักษณ์คล้ายโลหะเหลว มีความเสถียรทุกครั้งที่กดแป้น รองรับการเปลี่ยนสวิตช์แบบ hot-swap ทั้ง 3-pin และ 5-pin เหมาะสำหรับผู้ที่ต้องการคีย์บอร์ดที่โดดเด่นทั้งด้านฟังก์ชันและดีไซน์ล้ำสมัย\r\n\r\n• สวิตช์ : GravaStar x BSUN Linear Switch\r\n• ขนาด : 75%\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย / ไร้สาย 2.4GHz / บลูทูธ\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n• การเปลี่ยนสวิตช์ : เปลี่ยนสวิตช์ได้ รองรับสวิตช์ 3 ขา / 5 ขา\r\n', '1759728410_17.jpg', 2, '2025-10-06 05:26:50'),
(25, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) DXRACER MITH TEAM (RZ134/NY) (BLACK-YELLOW) ', '11500.00', 39, 'Gaming Chair\r\n', '1759728421_8.jpg', 3, '2025-10-06 05:27:01'),
(26, 'EGA X JJK K1 WHITE LIMITED EDITION - KTT LINEAR SWITCH RGB EN/TH', '2590.00', 3, 'EGA x JJK K1 White Limited Edition คีย์บอร์ด 98% ดีไซน์พรีเมียม มาพร้อมโครงสร้าง Gasket Mount ที่ช่วยลดแรงกระแทกและให้เสียงพิมพ์นุ่มแน่น หน้าจอ TFT Color Screen แสดงสถานะการเชื่อมต่อ เอฟเฟกต์ไฟ RGB และไฟล์ภาพเคลื่อนไหวได้แบบเรียลไทม์ รองรับการถอดเปลี่ยนสวิตช์ Hot-Swappable 5 Pin ใช้ Keycap PBT พิมพ์ลายทนทาน สัมผัสเยี่ยม เพลต Polycarbonate แข็งแรง ฟังก์ชัน Full Anti-Ghosting และไฟ RGB ปรับแต่งได้ รองรับ Windows และ Mac OS\r\n\r\n• สวิตช์ : KTT Linear Switch\r\n• ขนาด : 98%\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ / ภาษาไทย\r\n• เลย์เอาต์ : ANSI\r\n• จอแสดงผล : จอสีแบบ TFT\r\n• การเชื่อมต่อ : แบบใช้สาย / ไร้สาย 2.4GHz / บลูทูธ\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n• การเปลี่ยนสวิตช์ : เปลี่ยนสวิตช์ได้ รองรับสวิตช์ 3 ขา / 5 ขา\r\n', '1759728463_18.jpg', 2, '2025-10-06 05:27:43'),
(27, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) THERMALTAKE GF500 FLIGHT SIMULATOR COCKPIT - BLACK (GSC-F50-CPASBB-01)', '21990.00', 35, 'Thermaltake GF500 คือค็อกพิทจำลองการบินระดับพรีเมียม วัสดุโครงเหล็ก-อะลูมิเนียมแข็งแรง เบาะไฟเบอร์กลาสปรับเอนได้ พร้อมแผ่นวางจอย ปีกยกคันเร่ง และติดตั้งระบบไฟ RGB เหมาะสำหรับนักบินจำลองผู้ต้องการความสมจริงแบบจัดเต็ม\r\n', '1759728463_9.jpg', 3, '2025-10-06 05:27:43'),
(28, 'GAMING CHAIR (เก้าอี้เกมมิ่ง) CORSAIR TC500 LUXE CF-9010067-WW - FROST ', '16500.00', 42, 'Gaming Chair', '1759728501_10.jpg', 3, '2025-10-06 05:28:21'),
(29, 'GRAVASTAR MERCURY V75 PRO MAGNETIC SWITCH RGB EN - NEON GRAFFITI', '8590.00', 3, 'Gravastar Mercury V75 Pro คีย์บอร์ดเกมมิ่งระดับมือโปร มาพร้อมอัตราการตอบสนอง 8,000Hz และระบบสแกนคีย์ 256kHz ลดค่าหน่วงเหลือเพียง 0.125ms ปรับระดับการกดได้ตั้งแต่ 0.1–3.5 มม. รองรับ Dynamic Rapid Trigger และระบบ LKP เพื่อการตอบสนองที่แม่นยำ ตัวเครื่องแบบกึ่งอะลูมิเนียม แข็งแรงและน้ำหนักเบา รองรับ Hot-Swap สำหรับสวิตช์แม่เหล็ก Hall Effect พร้อมโฟมซับเสียง 5 ชั้น และไฟ RGB 16 โหมดแบบแยกโซน ปรับแต่งได้เต็มรูปแบบ\r\n\r\n• สวิตช์ : Magnetic switch (linear)\r\n• ขนาด : 75%\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n• สายเคเบิล : สาย USB-C เป็น USB-A\r\n• การเปลี่ยนสวิตช์ : เปลี่ยนสวิตช์ได้\r\n', '1759728509_19.jpg', 2, '2025-10-06 05:28:29'),
(30, 'STEELSERIES APEX PRO MINI GEN 3 OMNIPOINT 3.0 SWITCH RGB EN – BLACK', '9590.00', 8, 'Apex Pro Mini Gen 3 คือคีย์บอร์ดสายเกมที่เร็วที่สุดในโลก มาพร้อมสวิตช์ OmniPoint 3.0 แบบ Hall Effect ที่ปรับระดับได้ถึง 40 ระดับ ตอบสนองไวสุดขีด ดีไซน์ขนาดเล็กแต่ฟีเจอร์ครบ รองรับ Rapid Trigger, Protection Mode และใช้งานง่ายผ่าน GG Quickset เหมาะสำหรับเกมเมอร์สายแข่งขันตัวจริง\r\n\r\n• สวิตช์ : OmniPoint 3.0 Switch (Magnetic, Hall Effect, Linear)\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ\r\n• เลย์เอาต์ : ANSI\r\n• ขนาดคีย์บอร์ด : 60%\r\n• การเชื่อมต่อ : สาย USB-C เป็น USB-A แบบถอดออกได้\r\n', '1759728585_20.jpg', 2, '2025-10-06 05:29:45'),
(31, 'MSI G32C4X - 31.5 INCH VA FHD 250Hz CURVED FREESYNC PREMIUM', '4500.00', 7, '• Color gamut : 114% sRGB, 91% DCI-P3\r\n• Color Support : 1.07 Billion\r\n• Response Time : 1 ms(MPRT)\r\n• Brightness : 300 Nits\r\n• Aspect Ratio : 16:9\r\n', '1759729057_21.jpg', 4, '2025-10-06 05:37:37'),
(32, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) MICROSOFT XBOX CONTROLLER SERIES WLC (BLACK) (MCS-1V8-00014)', '1690.00', 42, 'Support : Xbox Series X / Xbox Series S / Xbox One / Windows 10 / Android / iOS\r\n', '1759729123_1.jpg', 5, '2025-10-06 05:38:43'),
(33, 'CONTROLLER (คอนโทรลเลอร์) LOGITECH GAMING GEAR CONTROLLER F310 CONSOLE STYTE', '2190.00', 52, '● Intuitive rear controls\r\n● Selectable step triggers\r\n● Premium audio\r\n● Extensive customization\r\n', '1759729205_2.jpg', 5, '2025-10-06 05:40:05'),
(34, 'SAMSUNG ODYSSEY G4 LS25BG400EEXXT - 25 INCH IPS FHD 240Hz G-SYNC COMPATIBLE, FREESYNC PREMIUM', '6500.00', 2, '• 25\"\r\n• IPS panel Anti-glare\r\n• 1920 x 1080 240Hz 1ms\r\n• 16.7 million colors\r\n', '1759729224_22.jpg', 4, '2025-10-06 05:40:24'),
(35, 'RACING WHEEL CONTROLLER (คอนโทรลเลอร์พวงมาลัย) LOGITECH GAMING GEAR G29 DRIVING FORCE WHEEL', '8990.00', 29, 'Support : PC / PS3 / PS4\r\n', '1759729241_3.jpg', 5, '2025-10-06 05:40:41'),
(36, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) RAZER WOLVERINE V2 PRO PS (WHITE)', '9990.00', 56, '• Razer™ HyperSpeed Wireless\r\n• Razer™ Mecha-Tactile Action Buttons\r\n• 8-Way Microswitch D-Pad\r\n', '1759729291_4.jpg', 5, '2025-10-06 05:41:31'),
(37, 'MSI MAG 274QRF QD E2 - 27 INCH RAPID IPS 2K 180Hz ADAPTIVE SYNC USB-C', '5500.00', 5, '• 27\"\r\n• Rapid IPS Anti-glare\r\n• 2560 x 1440 180Hz (DP) 144Hz (HDMI) 1ms\r\n• 1.07 billion colors\r\n• 2 x HDMI\r\n', '1759729292_23.jpg', 4, '2025-10-06 05:41:32'),
(38, 'RACING WHEEL ACCESSORIES (อุปกรณ์เสริมพวงมาลัย) MOZA KS STEERING WHEEL (MZ-RS047)', '15900.00', 46, 'For : Moza Racing Wheel Base\r\n', '1759729332_5.jpg', 5, '2025-10-06 05:42:12'),
(39, 'LG ULTRAGEAR 24GS60F-B - 23.8 INCH IPS FHD 180Hz AMD FREESYNC NVIDIA G-SYNC COMPATIBLE', '6500.00', 8, '• 23.8\" IPS panel Anti-glare\r\n• 1920 x 1080 180Hz 1ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n', '1759729332_24.jpg', 4, '2025-10-06 05:42:12'),
(40, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) MICROSOFT XBOX WIRELESS - DOOM THE DARK AGE (MCS-EP2-14851)', '2990.00', 57, 'คอนโทรลเลอร์ Xbox รุ่นลิมิเต็ด DOOM: The Dark Ages มาในดีไซน์ชุดเกราะ Slayer สีเขียวด้าน พร้อมหมุด 3D และปุ่ม Sentinel ABXY พร้อมปลดปล่อยพลังโบราณ พร้อมโค้ดสกิน Executioner สำหรับเกม DOOM: The Dark Ages\r\n\r\n• รองรับ: Xbox Series X / Xbox Series S / Xbox One / Windows 10,11 / Android / iOS\r\n\r\n', '1759729373_6.jpg', 5, '2025-10-06 05:42:53'),
(41, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) MICROSOFT XBOX WIRELESS (ELECTRIC VOLT) (QAU-00023)', '1890.00', 52, 'Support : Xbox Series X / Xbox Series S / Xbox One / Windows 10/11 / Android / iOS\r\n', '1759729408_7.jpg', 5, '2025-10-06 05:43:28'),
(42, 'LG ULTRAGEAR 34GP63A-B - 34 INCH VA 2K 160Hz CURVED AMD FREESYNC PREMIUM', '29900.00', 10, '• 34\"\r\n• VA panel\r\n• 3440 x 1440 160Hz (DP) 85Hz (HDMI) 1ms\r\n• 1800R Curved\r\n• 16.7 million colors\r\n• 2 x HDMI\r\n', '1759729413_25.jpg', 4, '2025-10-06 05:43:33'),
(43, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) RAZER WOLVERINE V3 PRO XBOX - WHITE', '6990.00', 57, 'Razer Wolverine V3 Pro ก้าวนำหน้าคู่แข่งไปหนึ่งก้าว หนึ่งช็อต และหนึ่งระดับด้วย Razer Wolverine V3 Pro คอนโทรลเลอร์ไร้สายสำหรับอีสปอร์ตที่สมบูรณ์แบบ ได้รับการรับรองอย่างเป็นทางการจาก Xbox ให้คุณเล่นเหมือนนักแข่งระดับโลกบนคอนโซลและ PC ด้วยความเร็ว การควบคุม และความแม่นยำที่ช่วยให้คุณคว้าชัยชนะ!\r\n\r\n• การเชื่อมต่อ : ใช้สาย ,ไร้สายความเร็วสูง\r\n• แบตเตอร์รี่ : ใช้งานต่อเนื่องสูงสุด 20 ชม.\r\n• ระบบที่ต้องการ : Xbox, PC\r\n', '1759729440_8.jpg', 5, '2025-10-06 05:44:00'),
(44, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) MICROSOFT XBOX WIRELESS - DEEP PINK (MCS-EP2-29913)', '1890.00', 48, 'ยกระดับเกมของคุณด้วย Xbox Wireless Controller ดีไซน์สวย จับถนัดมือ พร้อมแป้นทิศทางไฮบริดและปุ่มแชร์ในตัว รองรับการเชื่อมต่อกับ Xbox Series X|S, Xbox One, พีซี, Android และ iOS ได้อย่างรวดเร็ว\r\n\r\n• รองรับ: Xbox Series X / Xbox Series S / Xbox One / Windows 10,11 / Android / iOS\r\n', '1759729483_9.jpg', 5, '2025-10-06 05:44:43'),
(45, 'WIRELESS CONTROLLER (คอนโทรลเลอร์ไร้สาย) MICROSOFT XBOX - MCS-EP2-29569 HEART BREAKER', '2990.00', 46, 'Xbox Breaker Series Special Edition มาพร้อมลวดลายโดดเด่นทั้ง Ice Breaker, Heart Breaker และ Storm Breaker ที่ช่วยให้คอนโทรลเลอร์ดู “เฉียบ” และมีสไตล์ พร้อมปุ่ม Share ที่ช่วยให้จับภาพหรือคลิปได้ง่าย เชื่อมต่อได้ทั้ง Xbox, PC, มือถือ — เป็นตัวเลือกที่โดนใจทั้งเกมเมอร์และคนชอบของแต่ง เซ็ตให้ดูมีลูกเล่นในเกมสเตชันของคุณ\r\n\r\n• รองรับ: Xbox Series X / Xbox Series S / Xbox One / Windows 10,11 / Android / iOS\r\n\r\n', '1759729519_10.jpg', 5, '2025-10-06 05:45:19'),
(46, 'ASUS PROART PA329CV - 32 INCH IPS 4K 60Hz USB-C', '3900.00', 6, '• Color gamut : 100% sRGB\r\n• Color Support : 1.07 Billion\r\n• Response Time : 5 ms(GTG)\r\n• Brightness : 350 Nits\r\n• Aspect Ratio : 16:9\r\n', '1759729559_26.jpg', 4, '2025-10-06 05:45:59'),
(47, 'LG ULTRAGEAR 27GS60F-B - 27 INCH IPS FHD 180Hz AMD FREESYNC NVIDIA G-SYNC COMPATIBLE', '8500.00', 10, '• 27\"\r\n• IPS panel\r\n• 1920 x 1080 180Hz 1ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n• 1 x DP\r\n', '1759729618_27.jpg', 4, '2025-10-06 05:46:58'),
(48, 'LG ULTRAWIDE 29WQ600-W - 29 INCH IPS UWFHD 100Hz USB-C AMD FREESYNC', '7500.00', 20, '• 29\"\r\n• IPS panel\r\n• 2560 x 1080 100Hz 5ms\r\n• 16.7 million colors\r\n• 1 x USB-C (DP Alt Mode)\r\n', '1759729737_28.jpg', 4, '2025-10-06 05:48:57'),
(49, 'ACER NITRO VG270 GBMIPX - 27 INCH IPS FHD 120Hz', '18000.00', 11, '• 27\"\r\n• IPS panel\r\n• 1920 x 1080 120Hz 1ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n', '1759729800_29.jpg', 4, '2025-10-06 05:50:00'),
(50, 'GIGABYTE AORUS FO32U2P - 31.5 INCH OLED 4K 240Hz AMD FREESYNC PREMIUM PRO USB-C', '39900.00', 15, '• 31.5\"\r\n• OLED panel Anti-Reflection\r\n• 3840 x 2160 240Hz 0.03ms\r\n• 1.07 billion colors\r\n• 2 x HDMI\r\n', '1759729852_30.jpg', 4, '2025-10-06 05:50:52'),
(51, 'CPU AIR COOLER (พัดลมซีพียู) NOCTUA NH-D15', '4750.00', 89, '• Dimensions Height x Width x Depth (mm) : 165 x 150 x 161\r\n• TDP (W) : 140 w\r\n', '1759729971_1.jpg', 6, '2025-10-06 05:52:51'),
(52, 'CPU AIR COOLER (พัดลมซีพียู) NOCTUA NH-D15 CHROMAX (BLACK)', '5250.00', 208, 'Dimensions Height x Width x Depth (mm) : 160 x 150 x 135\r\n', '1759730014_2.jpg', 6, '2025-10-06 05:53:34'),
(53, 'CPU AIR COOLER (พัดลมซีพียู) DEEPCOOL AG400 PLUS', '990.00', 211, '• Intel LGA1700/1200/1151/1150/1155\r\n• AMD AM5/AM4\r\n', '1759730063_3.jpg', 6, '2025-10-06 05:54:23'),
(54, 'CPU AIR COOLER (พัดลมซีพียู) NOCTUA NH-U9S', '2890.00', 157, '• Dimensions Height x Width x Depth (mm) : 125 x 95 x 95\r\n• TDP (W) : ≈ 140 w\r\n\r\n', '1759730116_4.jpg', 6, '2025-10-06 05:55:16'),
(55, 'COMPUTER SET JIB MARU-2508098 RYZEN 5 7500F / RTX5060 8GB / B650M / 32GB DDR5 / M.2 512GB', '33900.00', 9, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n• บริการซ่อมและตรวจเช็คอาการ ฟรี! ได้ที่เจไอบีกว่า 130 สาขา ใน 70 จังหวัด\r\n', '1759730153_31.jpg', 7, '2025-10-06 05:55:53'),
(56, 'CPU AIR COOLER (พัดลมซีพียู) DEEPCOOL AK620', '2690.00', 211, 'Dimensions Length x Width x Height (mm) : 129 x 138 x 160\r\n', '1759730177_5.jpg', 6, '2025-10-06 05:56:17'),
(57, 'CPU AIR COOLER (พัดลมซีพียู) NOCTUA NH-U12S', '3340.00', 165, '• Dimensions Height x Width x Depth (mm) : 158 x 125 x 95\r\n• TDP (W) : ≈ 140 w\r\n', '1759730211_6.jpg', 6, '2025-10-06 05:56:51'),
(58, 'COMPUTER SET JIB MARU-CM25236 RYZEN 9 9950X3D / RTX5080 16GB / X870E / 96GB DDR5 / M.2 4TB', '175600.00', 15, '• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730234_32.jpg', 7, '2025-10-06 05:57:14'),
(59, 'CPU AIR COOLER (พัดลมซีพียู) NOCTUA NH-U12A', '5250.00', 72, 'Dimensions Height x Width x Depth (mm) : 158 x 125 x 58\r\n', '1759730253_7.jpg', 6, '2025-10-06 05:57:33'),
(60, 'CPU AIR COOLER (พัดลมซีพียู) DEEPCOOL AK500 DIGITAL WHITE', '2490.00', 189, '• 2066/2011-v3/2011/1700/1200/1151/1150/1155\r\n• AM5/AM4\r\n\r\n', '1759730298_8.jpg', 6, '2025-10-06 05:58:18'),
(61, 'COMPUTER SET JIB MARU-CM25133 RYZEN7 9700X / RTX5080 16GB / B850 / 64GB DDR5 / M.2 1TB', '88900.00', 8, '• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n• บริการซ่อมและตรวจเช็คอาการ ฟรี! ได้ที่เจไอบีกว่า 140 สาขา ใน 70 จังหวัด\r\n', '1759730342_33.jpg', 7, '2025-10-06 05:59:02'),
(62, 'CPU LIQUID COOLER (ระบบระบายความร้อนด้วยน้ำ) THERMALRIGHT FROZEN WARFRAM4', '3990.00', 57, 'Frozen Warframe 360 SE ARGB เป็นชุดน้ำปิดที่ออกแบบมาเพื่อการระบายความร้อนที่มีประสิทธิภาพสูง รองรับซ็อกเก็ต Intel และ AMD ล่าสุด พร้อมพัดลม ARGB ที่รองรับ RGB Sync ให้แสงสีที่สวยงาม เสียงพัดลมไม่ดังมาก และมีอัตราการไหลเวียนของอากาศที่ดี เหมาะสำหรับผู้ใช้ที่ต้องการทั้งความเย็นและดีไซน์ที่โดดเด่น\r\n\r\nรองรับซ็อกเก็ต : Intel LGA 1851, 1700, 1200, 115x และ AMD AM5, AM4\r\n', '1759730345_9.jpg', 6, '2025-10-06 05:59:05'),
(63, 'CPU LIQUID COOLER (ระบบระบายความร้อนด้วยน้ำ) ASUS ROG RYUJIN III 360 ARGB EXTREME WHITE EDITION', '15390.00', 66, 'ASUS ROG Ryujin III 360 ARGB Extreme White Edition เป็นระบบระบายความร้อนด้วยน้ำที่มีดีไซน์หรูหรา รองรับ ซ็อกเก็ต Intel และ AMD หลายรุ่น พร้อมจอแสดงผล LCD 3.5 นิ้วแสดงสถานะการทำงาน ระบบ RGB Sync ที่รองรับการควบคุมสีผ่าน Asus Aura Sync\r\n• RGB Sync : Asus Aura Sync\r\n• จอแสดงผล : 3.5\" Full Color LCD\r\n\r\n', '1759730387_10.jpg', 6, '2025-10-06 05:59:47'),
(64, 'COMPUTER SET JIB MARU-CM25221 ULTRA 7 265K / RTX5070TI 16GB / B860M / 64GB DDR5 / M.2 1TB', '76900.00', 5, '• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730400_34.jpg', 7, '2025-10-06 06:00:00'),
(65, 'COMPUTER SET JIB MARU-2510080 RYZEN 7 7800X3D / RTX5060 TI 8GB / B650M / 32GB DDR5 / M.2 1TB', '42900.00', 11, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730470_35.jpg', 7, '2025-10-06 06:01:10'),
(66, 'COMPUTER SET JIB MARU-CM25224 ULTRA 9 285K / RTX5090 32GB / Z890 / 96GB DDR5 / M.2 4TB', '261900.00', 3, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730575_36.jpg', 7, '2025-10-06 06:02:55'),
(67, 'COMPUTER SET JIB MARU-2510077 I7-14700K / RTX5060 TI 8GB / B760M / 32GB DDR5 / M.2 1TB', '43400.00', 4, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730669_37.jpg', 7, '2025-10-06 06:04:29'),
(68, 'X3D / RTX5070 12GB / X870 / 64GB DDR5 / M.2 1TB', '85900.00', 7, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n• ผ่อนสบายๆ 0% นาน 10 เดือน ทุกเซ็ต\r\n', '1759730788_38.jpg', 7, '2025-10-06 06:06:28'),
(69, 'ACER PREDATOR G300 – BLACK', '1190.00', 35, 'เมาส์เกมมิ่งสีดำดีไซน์เท่ ออกแบบมาเพื่อประสิทธิภาพในการเล่นเกม พร้อมรูปทรงที่จับถนัดมือ มอบความแม่นยำและความเร็วในการตอบสนอง เหมาะสำหรับเกมเมอร์ที่ต้องการอุปกรณ์ที่รองรับการเล่นเกมได้อย่างเต็มที่\r\n\r\n• ความละเอียด : สูงสุด 6,200 DPI\r\n• การเชื่อมต่อ : USB2.0\r\n• เซนเซอร์ : Pixart 3327', '1759730848_1.jpg', 8, '2025-10-06 06:07:07'),
(70, 'TX5060 8GB / B760M / 32GB DDR5 / M.2 1TB', '32800.00', 2, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n', '1759730890_39.jpg', 7, '2025-10-06 06:08:10'),
(71, 'ACER PREDATOR G100 – BLACK', '1090.00', 23, 'เมาส์เกมมิ่งดีไซน์เข้มดุดัน สีดำเรียบเท่ ออกแบบมาเพื่อการเล่นเกมโดยเฉพาะ มอบความแม่นยำและการตอบสนองที่รวดเร็ว เหมาะสำหรับเกมเมอร์ที่ต้องการอุปกรณ์ควบคุมที่ไว้วางใจได้ในทุกจังหวะของเกม\r\n\r\n• ความละเอียด : สูงสุด 6,200 DPI\r\n• การเชื่อมต่อ : USB2.0\r\n• เซนเซอร์ : Pixart 3327', '1759730898_2.jpg', 8, '2025-10-06 06:08:18'),
(72, 'RTX5080 16GB / Z790 / 64GB DDR5 / M.2 2TB', '59500.00', 11, '• สามารถปรับเปลี่ยนอุปกรณ์ได้ตามที่ต้องการ\r\n• ทุกเซ็ตที่กำหนด จัดส่งฟรีภายใน 4 ชั่วโมง *เฉพาะกรุงเทพฯ และปริมณฑล\r\n• อุปกรณ์คอมพิวเตอร์เสียภายใน 30 วัน นับจากวันซื้อ เปลี่ยนอุปกรณ์คอมพิวเตอร์ใหม่ให้ทันที ภายใน 24 ชั่วโมง เฉพาะซื้อผ่าน JIB Online เท่านั้น (เงื่อนไขเป็นไปตามที่กำหนด)\r\n', '1759730968_40.jpg', 7, '2025-10-06 06:09:28'),
(73, 'ACER PREDATOR G200 – BLACK', '1190.00', 43, 'เมาส์เกมมิ่งสีดำดีไซน์เข้ม สไตล์ดุดันตามแบบฉบับของ Predator ออกแบบเพื่อรองรับการเล่นเกมอย่างจริงจัง ให้ความแม่นยำสูงและตอบสนองรวดเร็ว เหมาะสำหรับเกมเมอร์ที่ต้องการอุปกรณ์ที่ควบคุมได้มั่นใจในทุกการเคลื่อนไหว\r\n\r\n• ความละเอียด : สูงสุด 6,200 DPI\r\n• การเชื่อมต่อ : USB2.0\r\n• เซนเซอร์ : Pixart 3327\r\n', '1759730971_3.jpg', 8, '2025-10-06 06:09:31'),
(74, 'HEADSET (หูฟัง) LOGITECH PRO X GAMING HEADSET WITH BLUE VOICE', '3690.00', 49, '• Headset Response : 20 Hz - 20000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz\r\n\r\n', '1759730991_1.jpg', 9, '2025-10-06 06:09:51'),
(75, 'HP M10 - BLACK', '190.00', 60, '• เมาส์ออปติคอลแบบมีสาย\r\n• เมาส์ออปติคอล 3 ปุ่ม ความละเอียด 1200 DPI\r\n• เชื่อมต่อผ่าน USB พร้อมสายยาว 1.6 เมตร\r\n• รองรับระบบปฏิบัติการ Windows XP, 7, 11\r\n', '1759731042_4.jpg', 8, '2025-10-06 06:10:42'),
(76, 'HEADSET (หูฟัง) ASUS ROG FUSION II 500', '3590.00', 47, '• Headset Response : 20 Hz - 40000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz\r\n\r\n', '1759731050_2.jpg', 9, '2025-10-06 06:10:50'),
(77, 'ARROW-X YDK-SK-M158 (MINT)', '120.00', 45, '• Up to 1,000 DPI\r\n• USB-A Wired connection\r\n', '1759731089_5.jpg', 8, '2025-10-06 06:11:29'),
(78, 'HEADSET (หูฟัง) ASUS ROG DELTA S CORE', '2690.00', 69, '• Headset Response : 20 Hz - 40000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz\r\n', '1759731091_3.jpg', 9, '2025-10-06 06:11:31'),
(79, 'HP G210 BLACK', '315.00', 34, '• 6 ปุ่ม\r\n• 3200/2400/1600/800 DPI\r\n• ไฟLED เปลี่ยนสีอัตโนมัติ\r\n', '1759731130_6.jpg', 8, '2025-10-06 06:12:10'),
(80, 'HEADSET (หูฟัง) HYPERX CLOUD III (RED)', '2590.00', 68, '• HyperX Signature Comfort and Durability\r\n• Angled 53mm Drivers, Tuned for Impeccable Audio\r\n• Crystal-Clear 10mm microphone, noise-cancelling, with LED mic-mute indicator\r\n• Multiplatform Compatible with 3.5mm, USB-C, and USB-A\r\n', '1759731132_4.jpg', 9, '2025-10-06 06:12:12'),
(81, 'HEADSET (หูฟัง) LOGITECH G G335 (WHITE)', '1590.00', 59, '• Headset Response : 20 Hz - 20000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz\r\n\r\n', '1759731165_5.jpg', 9, '2025-10-06 06:12:45'),
(82, 'JBL FLIP 6 (GREY)', '4150.00', 20, '20 Watt', '1759731172_41.jpg', 10, '2025-10-06 06:12:52'),
(83, 'HP G100PLUS BLACK', '385.00', 23, '• 4 ปุ่ม\r\n• 1600/1200/800 DPI ไฟRGB\r\n', '1759731179_7.jpg', 8, '2025-10-06 06:12:59'),
(84, 'WIRELESS HEADSET (หูฟังไร้สาย) HYPERX CLOUD III (BLACK)', '3890.00', 72, '• Up to 120 Hours\r\n• Ultra-clear microphone\r\n• Angled 53mm Drivers\r\n• Gaming-grade wireless\r\n', '1759731201_6.jpg', 9, '2025-10-06 06:13:21'),
(85, 'HEADSET (หูฟัง) RAZER BLACKSHARK V2 X (GREEN)', '1890.00', 75, '• Headset Response : 12 Hz - 28000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz\r\n', '1759731232_7.jpg', 9, '2025-10-06 06:13:52'),
(86, 'HP M270 BLACK', '235.00', 31, '• เซ็นเซอร์ SPC A704E\r\n• 6 ปุ่ม\r\n• 2400/1600/1200/800 DPI ไฟRGB', '1759731240_8.jpg', 8, '2025-10-06 06:14:00'),
(87, 'CREATIVE STAGE V2 SOUNDBAR-SUBWOOFER (BLACK)', '2150.00', 21, 'Main unit: 2 x 20 W, Subwoofer: 1 x 40W, Total System Power: Up to 80W RMS, Peak Power 160W\r\n\r\n', '1759731263_42.jpg', 10, '2025-10-06 06:14:23'),
(88, 'WIRELESS HEADSET (หูฟังไร้สาย) STEELSERIES ARCTIS NOVA PRO WIRELESS (BLACK) (PC & PLAYSTATION)', '15690.00', 54, '• ระบบตัดเสียงรบกวนแบบแอคทีฟ (ANC) พร้อมตัวเลือกการได้ยินทะลุผ่าน\r\n• ไดรเวอร์แม่เหล็กนีโอไดเมียมคุณภาพสูงแบบความละเอียดสูง\r\n• ไมโครโฟนตัดเสียงรบกวนที่ขับเคลื่อนด้วย AI พร้อมดีไซน์แบบซ่อนเก็บได้\r\n• เวลาเล่นไม่จำกัดด้วยแบตเตอรี่แบบถอดเปลี่ยนได้ 2 ก้อน + ชาร์จเร็ว\r\n• ความถี่ 2.4GHz และ Bluetooth พร้อมกันสำหรับผสมเสียงเกมและมือถือ\r\n• การเชื่อมต่อ USB คู่รองรับพีซี Mac PlayStation Switch VR และอื่นๆ\r\n• ไมโครโฟนตัดเสียงรบกวนที่ขับเคลื่อนด้วย AI พร้อมดีไซน์แบบซ่อนเก็บได้\r\n• เสียงรอบทิศทาง 360 องศาสำหรับพีซี (ซอฟต์แวร์ Sonar) และ PS5 (เสียง 3 มิติ Tempest)\r\n', '1759731265_8.jpg', 9, '2025-10-06 06:14:25'),
(89, 'RAPOO N500-BK WIRED OPTICAL (BLACK)', '204.00', 50, '• เมาส์มีสายขนาดพอดีมือ เหมาะกับการใช้งาน\r\n• สามารถปรับ dpi ได้ 1200/1800/2400/3600\r\n• ความละเอียด 1000dpi\r\n• มีปุ่มบังคับการทำงานหน้าหลัง back/forward\r\n• สามารถใช้งานได้ Windows® และ Mac OS®\r\n• ขนาดเมาส์ 121x76x43 mm\r\n• มีสีดำและขาว บรรจุในกล่องกระดาษ', '1759731277_9.jpg', 8, '2025-10-06 06:14:37'),
(90, 'HEADSET (หูฟัง) BEYERDYNAMIC MMX 100 (GREY)', '1690.00', 85, '• Headset Response : 5 Hz - 30000 Hz\r\n• Mic Response : 5 Hz - 18000 Hz\r\n', '1759731296_9.jpg', 9, '2025-10-06 06:14:56'),
(91, 'RAPOO EV200 (BLACK)', '390.00', 46, '● MODEL NAME : EV200\r\n● CONNECTIVITY : USB CABLE/ USB TYPE 3.0\r\n● COMPATIBILITY : WINDOWS XP / VISTA/7/8/10 / MACOS\r\n● FEATURES : BUTTONS 3 / DPI 1600 /DPI SWITCH /TRACKING TECHNOLOGY OPTICAL', '1759731327_10.jpg', 8, '2025-10-06 06:15:10'),
(92, 'CREATIVE PEBBLE V2 USB TYPE-C (BLACK)', '970.00', 15, '• 8 Watt RMS\r\n• 2.0 Channel\r\n', '1759731320_43.jpg', 10, '2025-10-06 06:15:20'),
(93, 'HEADSET (หูฟัง) NUBWO DRACOS X99 (WHITE)', '4690.00', 108, '• ระบบเชื่อมแบบ USB Plug&Play\r\n• มีไฟ Lighting เพิ่มความสวยงามยิ่งขึ้น\r\n• ระบบเสียงแบบ 7.1 Virtual Surround สมจริง\r\n• แถบคาดศีรษะแบบปรับได้ Adjustable Headband\r\n', '1759731356_10.jpg', 9, '2025-10-06 06:15:56'),
(94, 'SPEAKER (ลำโพงบลูทูธ) JBL CHARGE 5 (RED)', '5990.00', 30, '30 Watt RMS for woofer, 10 Watt RMS for tweeter', '1759731378_44.jpg', 10, '2025-10-06 06:16:18'),
(95, 'SPEAKER (ลำโพงบลูทูธ) JBL PARTYBOX 710', '32990.00', 8, '• Bluetooth 5,1\r\n• 800W RMS party speaker\r\n• Customizable lightshow\r\n• IPX4 splashproof\r\n', '1759731423_45.jpg', 10, '2025-10-06 06:17:03'),
(96, 'CREATIVE MUVO PLAY (BLACK)', '12990.00', 10, '10 Watt', '1759731462_46.jpg', 10, '2025-10-06 06:17:42'),
(97, 'GRAVASTAR MARS PRO (BLACK)', '8910.00', 15, '• Mars Pro เป็นลำโพงไร้สายที่มีระบบลำโพงคู่และตัวกระจายเสียงเบสแบบพาสซีฟเพื่อสร้างเสียงรอบด้านที่ทรงพลัง ระยะเวลาการใช้งานแบตเตอรี่ 15 ชั่วโมง ไฟ RGB 6 สี และเทคโนโลยี Bluetooth 5.0 สามารถจับคู่กับลำโพง Mars Pro ตัวอื่นเพื่อสร้างประสบการณ์เสียงสเตอริโอ\r\n• มีอัลกอริธึมเสียง DSP พิเศษในตัวที่ให้เสียงเบสที่หนักแน่น เสียงกลางที่แม่นยำ และเสียงสูงที่คมชัด\r\n• สร้างขึ้นจากโลหะผสมสังกะสีทรงกลม และการออกแบบจุดวางขาตั้งในรูปแบบสามเหลี่ยมทำให้มั่นใจได้ถึงความมั่นคงและสามารถดูดซับแรงกระแทกได้\r\n• การควบคุมระดับเสียงแบบสัมผัส ปัดขึ้นเพื่อพิ่มเสียงหรือปัดลงเพื่อลดเสียง\r\n', '1759731499_47.jpg', 10, '2025-10-06 06:18:19'),
(99, 'GRAVASTAR MARS PRO (YELLOW)', '12500.00', 11, '• Mars Pro เป็นลำโพงไร้สายที่มีระบบลำโพงคู่และตัวกระจายเสียงเบสแบบพาสซีฟเพื่อสร้างเสียงรอบด้านที่ทรงพลัง ระยะเวลาการใช้งานแบตเตอรี่ 15 ชั่วโมง ', '1759731584_48.jpg', 10, '2025-10-06 06:19:44'),
(100, 'GRAVASTAR SUPERNOVA (BLACK)', '7900.00', 23, '• Rated power 25W\r\n• Max power 30W\r\n• Bluetooth 5.3\r\n• Light 3 Modes 8 Colors\r\n• Playtime around 9 hours at 60% volume\r\n', '1759731655_49.jpg', 10, '2025-10-06 06:20:55'),
(101, 'MARSHALL WOBURN III - BLACK', '26000.00', 15, 'Woburn III การันตีว่าทุกพื้นที่จะเต็มไปด้วยเสียงซิกเนเจอร์ของ Marshall ที่ชัดเจนและทรงพลัง จนบ้านทั้งหลังสะเทือน! ระบบ Dynamic Loudness ในตัวจะปรับสมดุลเสียงให้เหมาะสม ทำให้ดนตรีของคุณฟังไพเราะสมบูรณ์แบบในทุกระดับความดัง Woburn III ยังพร้อมสำหรับเทคโนโลยี Bluetooth เจเนอเรชันถัดไป รองรับฟีเจอร์ใหม่ทันทีที่เปิดตัว\r\n\r\n• การเชื่อมต่อแบบไร้สาย Bluetooth 5.2 พร้อมรองรับ Bluetooth LE Audio\r\n• เสียงสเตริโอที่กว้างขึ้นแบบใหม่\r\n• เชื่อมต่อแล้วเปิดเพลงได้ทันที\r\n• ดีไซน์ไอคอนิกที่โดดเด่นไม่เหมือนใคร\r\n• แนวทางที่ยั่งยืนยิ่งขึ้น\r\n• การเชื่อมต่อและควบคุมที่ง่ายดาย\r\n', '1759731701_50.jpg', 10, '2025-10-06 06:21:41'),
(102, 'UPS (เครื่องสำรองไฟฟ้า) APC EASY UPS 1000VA (1000 VA/600 WATT) (BV1000I-MST)', '2990.00', 57, 'Output Capacity : 1000 VA / 600 Watt', '1759733616_1.jpg', 11, '2025-10-06 06:53:36'),
(103, 'UPS (เครื่องสำรองไฟฟ้า) APC EASY UPS 500VA (500 VA/300 WATT) (BV500I-MST)', '1860.00', 67, 'Output Capacity : 500 VA / 300 Watt\r\n', '1759733696_2.jpg', 11, '2025-10-06 06:54:56'),
(104, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME TE 1000 (1000 VA/900 WATT)', '11500.00', 51, 'Output Capacity : 1000 VA / 900 Watt', '1759733753_3.jpg', 11, '2025-10-06 06:55:53'),
(105, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME TE-3000 (3000 VA/2700 WATT)', '29900.00', 45, 'Output Capacity : 3000 VA / 2700 Watt', '1759733815_4.jpg', 11, '2025-10-06 06:56:55'),
(106, 'UPS (เครื่องสำรองไฟฟ้า) APC EASY UPS 800VA (800 VA/480 WATT) (BV800I-MST)', '2430.00', 57, 'Output Capacity : 800 VA / 480 Watt', '1759733872_5.jpg', 11, '2025-10-06 06:57:52'),
(107, 'VGA (การ์ดแสดงผล) YESTON X GRAVASTAR AMD RADEON RX 9070 XT 16G GDDR6 MERCURY NOVA OC', '35000.00', 12, 'Yeston x GravaStar AMD RADEON RX 9070 XT 16G GDDR6 Mercury Nova OC คือการผสมผสานอันล้ำสมัยระหว่างดีไซน์ โครงสร้างไบโอนิก 3D กับประสิทธิภาพการ์ดจอระดับสูง ถ่ายทอดแรงบันดาลใจจากธรรมชาติในการออกแบบ ให้รูปทรงใหม่ที่ไม่เหมือนใคร มาพร้อมแบ็คเพลตลายเส้นโค้งไม่สมมาตร สะท้อนความแปลกใหม่เหนือกาลเวลา เสริมด้วยระบบไฟ RGB แบบหลายโซนที่สร้างเอฟเฟกต์แสงตระการตา พร้อมพัดลมขนาดใหญ่ 9.3×3 ซม. และโครงสร้างเจาะรูแนวไบโอโมร์ฟิค เพื่อสร้างการไหลเวียนอากาศที่เป็นธรรมชาติ ระบายความร้อนได้ทรงพลังแต่ยังคงความเงียบสงบในการทำงาน\r\n\r\n• กราฟิกส์เอนจิน : Radeon RX 9070 XT\r\n• หน่วยความจำ : 16GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759733915_1.jpg', 12, '2025-10-06 06:58:35'),
(108, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ECO-II 800 (800 VA/360 WATT)', '1450.00', 47, 'Output Capacity : 800 VA / 360 Watt', '1759733935_6.jpg', 11, '2025-10-06 06:58:55'),
(109, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME STAR-1000 (1000 VA/600 WATT)', '3290.00', 65, 'Output Capacity : 1000 VA / 600 Watt', '1759733999_7.jpg', 11, '2025-10-06 06:59:59'),
(110, 'VGA (การ์ดแสดงผล) GIGABYTE GEFORCE RTX 5050 GAMING OC 8G (REV. 1.0) - 8GB GDDR6', '9690.00', 30, 'Gigabyte GeForce RTX 5050 GAMING OC 8G การ์ดจอรุ่นใหม่ที่ขับเคลื่อนด้วยสถาปัตยกรรม NVIDIA Blackwell และ DLSS 4 มาพร้อมหน่วยความจำ GDDR6 ขนาด 8GB แบบ 128 บิต รองรับการเล่นเกมและงานกราฟิกได้อย่างลื่นไหล ติดตั้งระบบระบายความร้อน WINDFORCE พร้อมพัดลม Hawk และเจลนำความร้อนเกรดเซิร์ฟเวอร์ ช่วยรักษาอุณหภูมิให้เสถียร เสริมความแข็งแรงด้วยโครงสร้างพิเศษ เหมาะสำหรับเกมเมอร์ที่ต้องการประสิทธิภาพและความทนทาน\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5050\r\n• หน่วยความจำ : 8GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 2 x DisplayPort, 2 x HDMI', '1759734050_2.jpg', 12, '2025-10-06 07:00:50'),
(111, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME TE-2000 (2000 VA/1800 WATT)', '23900.00', 38, 'Output Capacity : 2000 VA / 1800 Watt', '1759734068_8.jpg', 11, '2025-10-06 07:01:08'),
(112, 'VGA (การ์ดแสดงผล) GALAX GEFORCE RTX 5080 HOF GAMING BLACK EDITION - 16GB GDDR7', '51900.00', 10, 'Galax GeForce RTX 5080 HOF Gaming Black Edition การ์ดจอรุ่นเรือธงจากตระกูล Hall Of Fame ที่ขึ้นชื่อเรื่องการโอเวอร์คล็อกระดับโลก มอบพลังการประมวลผลขั้นสูง รองรับงานด้าน AI และการเล่นเกมกราฟิกระดับใหม่ได้อย่างเต็มประสิทธิภาพ ดีไซน์พรีเมียมพร้อมประสิทธิภาพที่เหนือชั้น เหมาะสำหรับเกมเมอร์และนักปรับแต่งที่ต้องการพลังสูงสุด\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5080\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734103_3.jpg', 12, '2025-10-06 07:01:43'),
(113, 'UPS (เครื่องสำรองไฟฟ้า) APC BACK-UPS, 2200VA (2200 VA/1200 WATT) (BX2200MI-MS)', '11230.00', 65, 'Output Capacity : 2200 VA / 1200 Watt', '1759734133_9.jpg', 11, '2025-10-06 07:02:13'),
(114, 'VGA (การ์ดแสดงผล) GALAX GEFORCE RTX 5070 TI HOF GAMING - 16GB GDDR7', '38900.00', 6, 'Galax GeForce RTX 5070 Ti HOF Gaming การ์ดจอรุ่นใหม่ที่สานต่อความเป็นตำนานจากซีรีส์ Hall Of Fame โดดเด่นด้วยศักยภาพในการโอเวอร์คล็อกระดับสูง มอบประสิทธิภาพการประมวลผลที่ทรงพลัง รองรับทั้งงานด้าน AI และการเล่นเกมยุคใหม่ได้อย่างลื่นไหล ออกแบบเพื่อตอบโจทย์คอเกมและสายปรับแต่งที่ต้องการประสิทธิภาพขั้นสุด\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5070 Ti\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734147_4.jpg', 12, '2025-10-06 07:02:27'),
(115, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ECO-II 1500 LCD (1500 VA/900 WATT)', '4890.00', 28, 'Output Capacity : 1500 VA / 900 Watt', '1759734188_10.jpg', 11, '2025-10-06 07:03:08'),
(116, 'VGA (การ์ดแสดงผล) GALAX GEFORCE RTX 5070 TI HOF GAMING BLACK EDITION - 16GB GDDR7', '37900.00', 5, 'Galax GeForce RTX 5070 Ti HOF Gaming Black Edition การ์ดจอประสิทธิภาพสูงจากตระกูล Hall Of Fame ที่ขึ้นชื่อเรื่องการโอเวอร์คล็อกระดับตำนาน ออกแบบมาเพื่อมอบพลังการประมวลผลขั้นสูง พร้อมศักยภาพในการโอเวอร์คล็อกอย่างเหนือชั้น รองรับเทคโนโลยี AI และการเล่นเกมยุคใหม่ได้อย่างเต็มประสิทธิภาพ เหมาะสำหรับนักเล่นเกมและผู้ที่ชื่นชอบการปรับแต่งประสิทธิภาพขั้นสุด\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5070 Ti\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734204_5.jpg', 12, '2025-10-06 07:03:24'),
(117, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ECO II-1000 LED (1000 VA/630 WATT)', '2590.00', 37, 'Output Capacity : 1000 VA/630 Watt', '1759734242_11.jpg', 11, '2025-10-06 07:04:02'),
(118, 'VGA (การ์ดแสดงผล) GALAX GEFORCE RTX 5050 1-CLICK OC WHITE - 8GB GDDR6', '9190.00', 8, 'GALAX GeForce RTX 5050 1-Click OC White กราฟิกการ์ดรุ่นใหม่ล่าสุด มาพร้อมพัดลมคู่ WINGS 3.0 ขนาด 92 มม. และฮีตไปป์ 6 มม. 2 เส้น ช่วยระบายความร้อนได้ดีเยี่ยม รองรับการเล่นเกมและงาน AI ได้เต็มประสิทธิภาพ บนสถาปัตยกรรม NVIDIA Blackwell ใหม่ เหมาะกับพีซีขนาดกะทัดรัดที่ต้องการพลังแรง\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5050\r\n• หน่วยความจำ : 8GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 2 x DisplayPort, 2 x HDMI', '1759734245_6.jpg', 12, '2025-10-06 07:04:05'),
(119, 'VGA (การ์ดแสดงผล) MSI GEFORCE RTX 5050 8G VENTUS 2X OC - 8GB GDDR6', '9590.00', 6, 'การ์ดจอ MSI GeForce RTX 5050 VENTUS 2X OC ใช้สถาปัตยกรรม NVIDIA Blackwell รองรับ DLSS 4 พร้อมความเร็ว Boost สูงสุด 2602 MHz มาพร้อมพัดลม TORX Fan 5.0 และท่อระบายความร้อนประสิทธิภาพสูง เสริมด้วยแผ่นหลังระบายลม และควบคุมผ่าน MSI Center และ Afterburner\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5050\r\n• หน่วยความจำ : 8GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734285_7.jpg', 12, '2025-10-06 07:04:45'),
(120, 'UPS (เครื่องสำรองไฟฟ้า) APC BACK-UPS 1600VA (1600 VA/900 WATT) (BX1600MI-MS)', '9800.00', 68, 'Output Capacity : 1600 VA / 900 Watt', '1759734309_12.jpg', 11, '2025-10-06 07:05:09'),
(121, 'VGA (การ์ดแสดงผล) ZOTAC GAMING GEFORCE RTX 5060 TI 16GB TWIN EDGE OC WHITE EDITION - 16GB GDDR7 (ZT-B50620Q-10M)', '17900.00', 8, 'Zotac Gaming GeForce RTX 5060 Ti 16GB Twin Edge OC White Edition การ์ดจอขนาดกะทัดรัดที่มาพร้อมสถาปัตยกรรม NVIDIA Blackwell และหน่วยความจำ GDDR7 ขนาด 16GB รองรับ DLSS 4 เพื่อภาพคมชัดลื่นไหล มาพร้อมการโอเวอร์คล็อกจากโรงงาน ระบบระบายความร้อน IceStorm 2.0 พัดลม BladeLink และเทคโนโลยี FREEZE Fan Stop เสริมด้วยแผ่นรองด้านหลังโลหะ แข็งแรง สวยงาม เหมาะสำหรับเคสขนาดเล็กที่ต้องการประสิทธิภาพระดับสูง\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5060 Ti\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734326_8.jpg', 12, '2025-10-06 07:05:26'),
(122, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ATOM 1500-LCD (1500 VA/900 WATT)', '5990.00', 38, 'Output Capacity : 1500 VA / 900 Watt', '1759734367_13.jpg', 11, '2025-10-06 07:06:07'),
(123, 'VGA (การ์ดแสดงผล) ASUS PRIME GEFORCE RTX 5070 WHITE OC EDITION 12GB GDDR7', '24400.00', 12, 'Asus PRIME GeForce RTX 5070 White OC Edition 12GB GDDR7 ถูกออกแบบมาเพื่อรองรับเครื่องขนาดเล็กด้วยดีไซน์ 2.5 สล็อตที่ประหยัดพื้นที่ พัดลม Axial-Tech มีใบพัดที่ยาวขึ้นช่วยเพิ่มการไหลของอากาศและการกระจายความร้อน แผ่นระบายความร้อน Phase-Change GPU Thermal Pad ช่วยถ่ายเทความร้อนได้อย่างมีประสิทธิภาพเพื่อยืดอายุการใช้งานของ GPU พัดลมแบบลูกปืนคู่ Dual-Ball Fan Bearings มีอายุการใช้งานยาวนานกว่าพัดลมแบบ Sleeve ถึงสองเท่า นอกจากนี้ยังมี Dual BIOS ให้ผู้ใช้เลือกโหมด Performance หรือ Quiet ได้ตามต้องการ พร้อมซอฟต์แวร์ GPU Tweak III สำหรับการปรับแต่งประสิทธิภาพอย่างเต็มที่\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5070\r\n• หน่วยความจำ : 12GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734372_9.jpg', 12, '2025-10-06 07:06:12'),
(124, 'VGA (การ์ดแสดงผล) ASUS TUF GAMING GEFORCE RTX 5090 32GB GDDR7', '103900.00', 5, 'Asus TUF Gaming GeForce RTX 5090 มาพร้อมหน่วยความจำ GDDR7 ขนาด 32GB และการออกแบบที่เน้นความทนทานระดับกองทัพ ใช้ชิ้นส่วน Military-Grade พร้อมเคลือบ PCB กันความชื้นและฝุ่น ระบบระบายความร้อนทรงพลังด้วยฮีทไปป์ 12 เส้นและ Vapor Chamber ขนาด 3.6 สล็อต พร้อมพัดลม Axial-Tech ให้แรงลมเพิ่มขึ้นถึง 23% ปรับโหมด BIOS ได้ระหว่างเงียบและประสิทธิภาพสูง รองรับซอฟต์แวร์ GPU TWEAK III สำหรับการจูนการ์ดอย่างเต็มประสิทธิภาพ\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5090\r\n• หน่วยความจำ : 32GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 2 x HDMI', '1759734412_10.jpg', 12, '2025-10-06 07:06:52'),
(125, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ATOM 2000-LCD (2000 VA/1200 WATT)', '6990.00', 39, 'Output Capacity : 2000 VA / 1200 Watt', '1759734415_14.jpg', 11, '2025-10-06 07:06:55'),
(126, 'UPS (เครื่องสำรองไฟฟ้า) CYBER POWER BU1000EA 1000VA/630WATT (BLACK)', '2950.00', 57, '1000VA / 630 Watts\r\nLine-interactive UPS Topology\r\nGenerator Compatible\r\nSimulated Sine Wave Output\r\nAutomatic Voltage Regulation (AVR)\r\nOverload Protection\r\nLED Status Indicator\r\nConfigurable Alarm\r\nBrick Form Factor', '1759734467_15.jpg', 11, '2025-10-06 07:07:47'),
(127, 'VGA (การ์ดแสดงผล) XFX SWIFT AMD RADEON RX 9060 XT OC GAMING EDITION 8GB - 8GB GDDR6 (RX-96TSW8GBQ)', '11400.00', 23, 'XFX Swift AMD Radeon RX 9060 XT OC Gaming Edition 8GB การ์ดจอระดับเริ่มต้นจาก XFX มาพร้อมดีไซน์เพรียวบางสไตล์แอโรไดนามิก เน้นการระบายความร้อนสูงสุดด้วยพัดลมประสิทธิภาพสูง ฮีตไปป์ขนาด 6 มม. และแผ่นทองแดงเคลือบนิกเกิล ช่วยให้เล่นเกมได้ลื่นไหลยิ่งขึ้น ใช้สถาปัตยกรรม AMD RDNA™ 4 รุ่นใหม่ รองรับ Ray Tracing และ AI เพิ่มประสิทธิภาพภาพ เสียง และการสตรีมได้อย่างน่าประทับใจ\r\n\r\n• กราฟิกส์เอนจิน : Radeon RX 9060 XT\r\n• หน่วยความจำ : 8GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 2 x DisplayPort, 1 x HDMI', '1759734475_11.jpg', 12, '2025-10-06 07:07:55'),
(128, 'VGA (การ์ดแสดงผล) MSI GEFORCE RTX 5060 TI 8G GAMING TRIO OC - 8GB GDDR7', '16400.00', 16, 'MSI GeForce RTX 5060 Ti 8G GAMING TRIO OC มาพร้อมสถาปัตยกรรม NVIDIA Blackwell และ DLSS 4 ให้ประสิทธิภาพการประมวลผลสูงถึง 2662 MHz พร้อมระบบระบายความร้อน TRI FROZR 4 ที่เงียบและเย็นขึ้น ด้วยพัดลม STORMFORCE, แผ่นฐานทองแดงเคลือบนิกเกิล และแผ่นระบายความร้อนหลังโลหะเสริมความแข็งแรง พร้อมฟินออกแบบใหม่ Wave Curved 4.0 และ Air Antegrade Fin 2.0 เพื่อเพิ่มประสิทธิภาพการไหลเวียนอากาศ ควบคุมง่ายผ่าน MSI Center และ Afterburner\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5060 Ti\r\n• หน่วยความจำ : 8GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734519_12.jpg', 12, '2025-10-06 07:08:39'),
(129, 'UPS (เครื่องสำรองไฟฟ้า) SYNDOME ATOM 850I-LCD (850 VA/480 WATT)', '2180.00', 67, 'Output Capacity : 850 VA / 480 Watt', '1759734522_16.jpg', 11, '2025-10-06 07:08:42'),
(130, 'VGA (การ์ดแสดงผล) ASUS ROG STRIX GEFORCE RTX 5070 12GB GDDR7 OC EDITION', '27900.00', 10, 'Asus ROG Strix GeForce RTX 5070 OC Edition 12GB GDDR7 มาพร้อมระบบระบายความร้อนขั้นสูงและพลังประมวลผลระดับพรีเมียมจากสถาปัตยกรรม NVIDIA Blackwell และ DLSS 4 พัดลม Axial-tech ขนาดใหญ่เพิ่มการไหลเวียนอากาศ 31% พร้อมเทคโนโลยี 0dB สำหรับการเล่นเกมแบบเงียบ ระบบ MaxContact และแผ่นระบายความร้อนแบบ phase-change ช่วยลดอุณหภูมิ GPU อย่างมีประสิทธิภาพ การควบคุมพลังงานแบบดิจิทัล คาปาซิเตอร์ 15K และการเคลือบป้องกันแผงวงจรเพิ่มความเสถียรในการใช้งาน รองรับไฟ ARGB แบบ Aura Sync และปรับแต่งได้ผ่านซอฟต์แวร์ GPU Tweak III\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5070\r\n• หน่วยความจำ : 12GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 2 x HDMI', '1759734576_13.jpg', 12, '2025-10-06 07:09:36'),
(131, 'UPS (เครื่องสำรองไฟฟ้า) APC BR1600SI (1600 VA/960 WATT)', '16900.00', 32, '• 1600 VA / 960 Watts\r\n• 8 Outlets\r\n• LCD interface', '1759734580_17.jpg', 11, '2025-10-06 07:09:40'),
(132, 'VGA (การ์ดแสดงผล) GIGABYTE GEFORCE RTX 5060 TI AERO OC 8G - 8GB GDDR7', '15700.00', 14, 'Gigabyte GeForce RTX 5060 Ti AERO OC 8G มาพร้อมสถาปัตยกรรม NVIDIA Blackwell และ DLSS 4 เพื่อภาพกราฟิกที่ลื่นไหลและสมจริง ใช้หน่วยความจำ GDDR7 ขนาด 8GB บนอินเทอร์เฟซ 128 บิต ระบบระบายความร้อน WINDFORCE พร้อมพัดลม Hawk และเจลนำความร้อนระดับเซิร์ฟเวอร์ช่วยควบคุมอุณหภูมิได้อย่างมีประสิทธิภาพ ไฟ RGB เพิ่มความโดดเด่น พร้อมระบบ Dual BIOS ให้เลือกใช้งานทั้งโหมดประสิทธิภาพหรือเงียบ และโครงสร้างที่เสริมความแข็งแรงเพื่อความทนทาน\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5060 Ti\r\n• หน่วยความจำ : 8GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734617_14.jpg', 12, '2025-10-06 07:10:17'),
(133, 'VGA (การ์ดแสดงผล) ASUS TUF GAMING GEFORCE RTX 5090 32GB GDDR7 OC EDITION', '105900.00', 3, 'ASUS TUF Gaming GeForce RTX 5090 มาพร้อมพลังจากสถาปัตยกรรม NVIDIA Blackwell เสริมด้วยระบบระบายความร้อนขั้นสูง และวัสดุเกรดทหารเพื่อความทนทานระดับพรีเมียม การออกแบบแบบ 3.6 สล็อต พร้อมท่อฮีตไปป์ 12 เส้นและแผ่นระบายความร้อนแบบเวเปอร์แชมเบอร์ ช่วยให้ถ่ายเทความร้อนได้อย่างยอดเยี่ยม พัดลม Axial-Tech เพิ่มลมได้มากขึ้นถึง 23% และแผ่นซับความร้อนแบบ Phase-Change ทำให้การใช้งานยาวนานขึ้น พร้อมโหมด BIOS สลับได้ระหว่างความเงียบหรือประสิทธิภาพ\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5090\r\n• หน่วยความจำ : 32GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 2 x HDMI', '1759734647_15.jpg', 12, '2025-10-06 07:10:47'),
(134, 'UPS (เครื่องสำรองไฟฟ้า) VERTIV LIEBERT GXT5 UPS 5000VA/5000W 230V (VTV-01201973)', '133960.00', 24, '5000VA/5000W', '1759734669_18.jpg', 11, '2025-10-06 07:11:09'),
(135, 'VGA (การ์ดแสดงผล) GALAX GEFORCE RTX 5080 EX GAMER 1-CLICK OC - 16GB GDDR7', '43500.00', 5, 'GALAX RTX 5080 EX Gamer ใช้พลังจากสถาปัตยกรรม NVIDIA Blackwell พร้อม DLSS 4 มอบกราฟิกระดับสูงและประสิทธิภาพที่เร็วเหนือชั้น รองรับ NVIDIA Studio สำหรับสายครีเอเตอร์ และ NIM microservices สำหรับการใช้งาน AI อย่างล้ำสมัย\r\n\r\n• กราฟิกส์เอนจิน : GeForce RTX 5080\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1759734677_16.jpg', 12, '2025-10-06 07:11:17'),
(136, 'UPS (เครื่องสำรองไฟฟ้า) APC SMART SMC LCD 230V 1000VA/600W WITH SMART CONNECT (SMC1000IC)', '18100.00', 48, '• Output Capacity : 1000 VA / 600 Watt', '1759734735_19.jpg', 11, '2025-10-06 07:12:15'),
(137, 'VGA (การ์ดแสดงผล) GIGABYTE RADEON RX 9070 GAMING OC 16G - 16GB GDDR6 (GV-R9070GAMING OC-16GD)', '22400.00', 20, '• ขับเคลื่อนด้วย Radeon RX 9070\r\n• มาพร้อมกับหน่วยความจำ 16GB GDDR6 อินเทอร์เฟซ 256bit\r\n• ระบบระบายความร้อน WINDFORCE\r\n• พัดลม Hawk\r\n• เจลนำความร้อนเกรดเซิร์ฟเวอร์\r\n• ไฟ RGB\r\n• Dual BIOS (Performance / Silent)\r\n• โครงสร้างเสริมความแข็งแรง', '1759734785_17.jpg', 12, '2025-10-06 07:13:05');
INSERT INTO `product` (`p_id`, `p_name`, `p_price`, `p_stock`, `p_description`, `p_image`, `cat_id`, `created_at`) VALUES
(138, 'UPS (เครื่องสำรองไฟฟ้า) APC 2200VA/1320W 230V GAMING RGB PURE SINE WAVE - BLACK (BGM2200B-MSX)', '14990.00', 30, 'APC Back-UPS Pro ให้การป้องกันไฟฟ้าระดับพรีเมียมสำหรับเกมมิ่งคอนโซล PC เราเตอร์/โมเด็ม และอุปกรณ์เกม ขนาด 2200VA/1320W มี 6 ช่องเสียบ (4 ช่องสำรองไฟ+กันไฟกระชาก, 2 ช่องกันไฟกระชาก) รองรับไฟกระชากสูงสุด 1080 Joules มีระบบปรับแรงดันอัตโนมัติ (AVR) และมาพร้อมซอฟต์แวร์ PowerChute เพื่อจัดการพลังงานและปิดระบบอย่างปลอดภัย\r\n\r\nกำลังไฟสูงสุด : 2200VA/1320W', '1759734813_20.jpg', 11, '2025-10-06 07:13:33'),
(139, 'VGA (การ์ดแสดงผล) ASROCK AMD RADEON RX 9070 XT TAICHI 16GB OC - 16GB GDDR6 (RX9070XT TC 16GO)', '30900.00', 18, '• AMD Radeon RX 9070 XT GPU\r\n• หน่วยความจำ 16GB GDDR6 256 บิต\r\n• 64 หน่วยคำนวณ AMD RDNA (พร้อมตัวเร่ง RT+AI)\r\n• รองรับ PCI Express 5.0\r\n• ตัวเชื่อมต่อพลังงาน 12V-2x6-pin\r\n• 3 x DisplayPort 2.1a / 1 x HDMI 2.1b', '1759734823_18.jpg', 12, '2025-10-06 07:13:43'),
(140, 'VGA (การ์ดแสดงผล) SAPPHIRE PURE AMD RADEON RX 9070 XT GPU - 16GB GDDR6', '26900.00', 6, '• GPU : ความเร็วบูสต์ (Boost Clock) : สูงสุด 3010 MHz\r\n• GPU : ความเร็วในการเล่นเกม (Game Clock) : สูงสุด 2460 MHz\r\n• หน่วยความจำ (Memory) : 16GB/256 bit DDR6. 20 Gbps\r\n• ตัวประมวลผลสตรีม (Stream Processors) : 4096\r\n• สถาปัตยกรรม AMD RDNA 4\r\n• ตัวเร่งแสง (Ray Accelerator) : 64', '1759734884_19.jpg', 12, '2025-10-06 07:14:44'),
(141, 'VGA (การ์ดแสดงผล) GIGABYTE GEFORCE RTX 3060 GAMING OC 12G - 12GB GDDR6 (GV-N3060GAMING OC-12GD) (REV. 2.0) (LHR)', '13000.00', 2, '• GeForce RTX 3060\r\n• 12GB GDDR6\r\n• 2 x DP\r\n• 2 x HDMI', '1759735082_20.jpg', 12, '2025-10-06 07:18:02'),
(142, 'WEBCAM (เว็บแคม) LOGITECH C922 PRO HD STREAM WEBCAM', '3290.00', 23, '• Full HD 1080p/30fps HD 720p/60fps\r\n• โฟกัสอัตโนมัติ\r\n• ไมค์คู่ระบบเสียงสเตอริโอ\r\n• ขาตั้ง\r\n• การเชื่อมต่อแบบ USB', '1759923244_1.webp', 13, '2025-10-08 11:34:04'),
(143, 'CONFERENCE BAR (กล้องสำหรับประชุมทางวีดีโอคอนเฟอเรนซ์) HOSHI (SBAR-SM210X) WITH EXTERNAL MICROPHONE', '29990.00', 5, '●Speakers 5w & Mic\r\n●Wired or Wireless PC Connect\r\n●Long time battery\r\n●3x Expansion microphone', '1759923311_2.jpg', 13, '2025-10-08 11:35:11'),
(144, 'WEBCAM (เว็บแคม) ELGATO FACECAM MK.2 (10WAC9901)', '5990.00', 20, '1080p60, 1080p30, 720p120, 720p60, 720p30, 540p120, 540p60, 540p30', '1759923451_3.jpg', 13, '2025-10-08 11:37:31'),
(145, 'WEBCAM (เว็บแคม) STREAMPLIFY CAM FHD', '1990.00', 23, '• Full HD 1080P/60FPS Webcam\r\n• Autofocus and Automatic Light Enhancement\r\n• Foldable and Portable Tripod\r\n• Wide Screen View 90° and 360° Swivel\r\n• Anti-spy Sliding Webcam Cover\r\n• Stereo Microphone w/ Realtek Solution', '1759923533_4.jpg', 13, '2025-10-08 11:38:53'),
(146, 'WEBCAM (เว็บแคม) LOGITECH BRIO100 FHD 1080P (ROSE)', '990.00', 16, 'เว็บแคม Full HD 1080p พร้อมระบบปรับสมดุลแสงอัตโนมัติ, ฉากปิดเลนส์ในตัวเพื่อความเป็นส่วนตัว และไมค์ในตัว', '1759923581_5.jpg', 13, '2025-10-08 11:39:41'),
(147, 'WEBCAM (เว็บแคม) LOGITECH BRIO300 FHD 1080P (ROSE)', '1990.00', 30, 'เว็บแคม 1080p พร้อมการแก้ไขสภาพแสงอัตโนมัติ, ไมค์ลดเสียงรบกวน และการเชื่อมต่อ USB-C.', '1759923641_6.jpg', 13, '2025-10-08 11:40:41'),
(148, 'WEBCAM (เว็บแคม) ELGATO FACECAM 4K - BLACK (10WAF9901)', '7490.00', 26, 'Elgato Facecam 4K กล้องเว็บแคมระดับสตูดิโอ 4K60 ที่สุดของกล้อง Facecam ที่ทรงพลังที่สุดเท่าที่เคยมีมา รองรับวิดีโอระดับ Ultra HD ที่ 60 เฟรมต่อวินาที พร้อมการควบคุมแบบ DSLR และลูกเล่นภาพยนตร์ในตัว รองรับการใส่ฟิลเตอร์เลนส์ได้เป็นครั้งแรกในกลุ่มเว็บแคม\r\n\r\n• เซนเซอร์ SONY STARVIS 2 คุณภาพสูง ขนาด 1/1.8 นิ้ว\r\n• รองรับวิดีโอ 4K60 (ผ่าน USB 3.0) พร้อม HDR\r\n• เลนส์ Elgato Prime F/4.0 มุมกว้าง 90°\r\n• โฟกัสคงที่ คมชัดตั้งแต่ระยะใกล้ถึงกลาง\r\n• รองรับฟิลเตอร์ขนาด 49 มม. เปลี่ยนลุคได้อิสระ\r\n• เชื่อมต่อ USB-C ใช้งานง่าย Plug and Play', '1759923692_7.jpg', 13, '2025-10-08 11:41:32'),
(149, 'WEBCAM (เว็บแคม) RAPOO C500AF 4K - BLACK', '1690.00', 26, 'Rapoo C500AF คือเว็บแคมที่ผสมผสานความคมชัดระดับ 4K กับดีไซน์ใช้งานง่าย—เพียงเสียบ USB ก็พร้อมใช้งานทันที โฟกัสภาพชัดทุกเฟรม และมีฝาครอบส่วนตัวให้คุณใช้งานอย่างมั่นใจในทุกคลิก', '1759923745_8.jpg', 13, '2025-10-08 11:42:25'),
(150, 'CONFERENCE CAMERA (กล้องสำหรับประชุมทางวีดีโอคอนเฟอเรนซ์) JABRA PANACAST 20', '6590.00', 13, '• 4k Ultra-HD: 3840 x 2160 @ 30 fps\r\n• 1080p Full HD: 1920 x 1080 @ 30 fps\r\n• 720p HD: 1280 x 720 @ 30 fps', '1759923785_9.jpg', 13, '2025-10-08 11:43:05'),
(151, 'WEBCAM (เว็บแคม) LOGITECH BRIO 305 (GRAPHITE)', '2190.00', 20, '• 1080p/30fps (1920x1080 พิกเซล)\r\n• 720p/30fps (1280x720 พิกเซล)', '1759923825_10.jpg', 13, '2025-10-08 11:43:45'),
(152, 'SMART TV (สมาร์ททีวี) XIAOMI A 2026 - 75 INCH', '23990.00', 15, 'Xiaomi TV A 75 2026 คือสมาร์ททีวีที่มอบประสบการณ์ภาพและเสียงที่สะกดทุกสายตาด้วยดีไซน์ขอบบางเฉียบ หน้าจอ 4K Eye-Care ที่ให้ภาพคมชัดพร้อมโหมดถนอมสายตา และระบบเสียงคุณภาพสูงจาก Dolby และ DTS พร้อมใช้งาน Google TV ที่เรียบง่ายและครบครัน\r\n\r\n• ขนาดจอ : 75 นิ้ว\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 60Hz\r\n• การเชื่อมต่อ : 3x HDMI1.4, 1x USB, 1x LAN, 1x Optical', '1759924934_1.jpg', 14, '2025-10-08 12:02:14'),
(153, 'SMART TV (สมาร์ททีวี) XIAOMI A 2026 - 65 INCH', '16590.00', 20, 'Xiaomi TV A 65 2026 คือสมาร์ททีวีที่มอบประสบการณ์ภาพและเสียงที่สะกดทุกสายตาด้วยดีไซน์ขอบบางเฉียบ หน้าจอ 4K Eye-Care ที่ให้ภาพคมชัดพร้อมโหมดถนอมสายตา และระบบเสียงคุณภาพสูงจาก Dolby และ DTS พร้อมใช้งาน Google TV ที่เรียบง่ายและครบครัน\r\n\r\n• ขนาดจอ : 65 นิ้ว\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 60Hz\r\n• การเชื่อมต่อ : 3x HDMI1.4, 1x USB, 1x LAN, 1x Optical', '1759924991_2.jpg', 14, '2025-10-08 12:03:11'),
(154, 'SMART TV (สมาร์ททีวี) XIAOMI A 2026 - 55 INCH', '11490.00', 23, 'Xiaomi TV A 55 2026 คือสมาร์ททีวีที่มอบประสบการณ์ภาพและเสียงที่สะกดทุกสายตาด้วยดีไซน์ขอบบางเฉียบ หน้าจอ 4K Eye-Care ที่ให้ภาพคมชัดพร้อมโหมดถนอมสายตา และระบบเสียงคุณภาพสูงจาก Dolby และ DTS พร้อมใช้งาน Google TV ที่เรียบง่ายและครบครัน\r\n\r\n• ขนาดจอ : 55 นิ้ว\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 60Hz\r\n• การเชื่อมต่อ : 3x HDMI1.4, 1x USB, 1x LAN, 1x Optical', '1759925042_3.jpg', 14, '2025-10-08 12:04:02'),
(155, 'SMART TV (สมาร์ททีวี) TCL P755 4K UHD GOOGLE - 55 INCH (55P755)', '17990.00', 13, 'TCL P755 4K UHD Google TV มอบภาพคมชัดระดับ Ultra HD พร้อมรองรับ HDR และ Dolby Vision™ มาพร้อมระบบ Google TV ใช้งานง่าย เข้าถึงแอปโปรดได้ทันที พร้อม Dolby Atmos® เพื่อประสบการณ์เสียงรอบทิศทางสุดสมจริง\r\n\r\n• ขนาดจอ : 55 นิ้ว\r\n• ประเภทจอ : DLED\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : MEMC(60Hz),120Hz VRR,DLG 120Hz\r\n• การเชื่อมต่อ : HDMI1.4 & HDMI2.0 & HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync', '1759925091_4.jpg', 14, '2025-10-08 12:04:51'),
(156, 'SMART TV (สมาร์ททีวี) TCL C6K PREMIUM QD-MINILED - 55 INCH (55C6K)', '21990.00', 46, 'TCL C6K QD-Mini LED TV ทีวีพรีเมียมที่ผสานเทคโนโลยี Quantum Dot และ Mini LED ให้ภาพคมชัด สีสันแม่นยำ รองรับ 4K HDR รีเฟรชเรทสูง ดูหนังและเล่นเกมได้อย่างลื่นไหล พร้อมดีไซน์บางหรูทันสมัย\r\n\r\n• ขนาดจอ : 55 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : MEMC 120HZ; VRR 48HZ~240Hz; DLG 240Hz\r\n• การเชื่อมต่อ : HDMI1.4 & HDMI2.0 & HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync', '1759925130_5.jpg', 14, '2025-10-08 12:05:30'),
(157, 'SMART TV (สมาร์ททีวี) TCL C6K PREMIUM QD-MINILED - 65 INCH (65C6K)', '28990.00', 2, 'TCL C6K QD-Mini LED TV ทีวีพรีเมียมที่ผสานเทคโนโลยี Quantum Dot และ Mini LED ให้ภาพคมชัด สีสันแม่นยำ รองรับ 4K HDR รีเฟรชเรทสูง ดูหนังและเล่นเกมได้อย่างลื่นไหล พร้อมดีไซน์บางหรูทันสมัย\r\n\r\n• ขนาดจอ : 65 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : MEMC 120HZ; VRR 48HZ~240Hz; DLG 240Hz\r\n• การเชื่อมต่อ : HDMI1.4 & HDMI2.0 & HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync', '1759925174_6.jpg', 14, '2025-10-08 12:06:14'),
(158, 'SMART TV (สมาร์ททีวี) TCL NXTVISION A300 PRO - 55 INCH (55A300 PRO)', '59990.00', 4, 'TCL NXTVISION A300 Pro TV ทีวีพรีเมียมที่มาพร้อมเทคโนโลยี NXTVISION ให้ภาพคมชัด สีสันแม่นยำสมจริง รองรับความละเอียดสูง เหมาะทั้งดูหนัง เล่นเกม และใช้งานในบ้านอัจฉริยะอย่างมีสไตล์\r\n\r\n• ขนาดจอ : 55 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 120; VRR~144HZ; DLG 240Hz\r\n• การเชื่อมต่อ : HDMI2.0&HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync Premium', '1759925212_7.jpg', 14, '2025-10-08 12:06:52'),
(159, 'SMART TV (สมาร์ททีวี) TCL NXTVISION A300 PRO - 75 INCH (75A300 PRO)', '99990.00', 6, 'TCL NXTVISION A300 Pro TV ทีวีพรีเมียมที่มาพร้อมเทคโนโลยี NXTVISION ให้ภาพคมชัด สีสันแม่นยำสมจริง รองรับความละเอียดสูง เหมาะทั้งดูหนัง เล่นเกม และใช้งานในบ้านอัจฉริยะอย่างมีสไตล์\r\n\r\n• ขนาดจอ : 75 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 120; VRR~144HZ; DLG 240Hz\r\n• การเชื่อมต่อ : HDMI2.0&HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync Premium', '1759925251_8.jpg', 14, '2025-10-08 12:07:31'),
(160, 'SMART TV (สมาร์ททีวี) TCL C7K PREMIUM QD-MINI LED - 55 INCH (55C7K)', '34990.00', 15, 'TCL C7K QD-Mini LED TV ทีวีพรีเมียมที่มาพร้อมเทคโนโลยี Quantum Dot และ Mini LED ให้ภาพสว่างคมชัด คอนทราสต์ลึก รองรับ 4K HDR และรีเฟรชเรทสูง เหมาะสำหรับทั้งดูหนังและเล่นเกมอย่างลื่นไหล\r\n\r\n• ขนาดจอ : 55 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : MEMC (120 Hz), VRR 48HZ~288HZ; DLG 240Hz\r\n• การเชื่อมต่อ : HDMI1.4 & HDMI2.0 & HDMI2.1, HDCP1.4 & HDCP2.2\r\n• เทคโนโลยีการซิงค์ : AMD FreeSync', '1759925295_9.jpg', 14, '2025-10-08 12:08:15'),
(161, 'SMART TV (สมาร์ททีวี) TCL 115INCH X955 PREMIUM QD-MINI LED (115X955 MAX)', '1299990.00', 2, 'TCL 115\" X955 Premium QD-Mini LED TV ทีวีระดับเรือธงขนาดใหญ่ถึง 115 นิ้ว ให้ภาพคมชัดด้วยเทคโนโลยี QD-Mini LED พร้อมสีสันสดใส คอนทราสต์ลึก รองรับ 4K HDR และระบบเสียงระดับพรีเมียม มอบประสบการณ์ความบันเทิงที่เหนือระดับ\r\n\r\n• ขนาดจอ : 115 นิ้ว\r\n• ประเภทจอ : VA\r\n• ความละเอียด : 3840×2160\r\n• รีเฟรชเรท : 144Hz VRR +240Hz DLG\r\n• การเชื่อมต่อ : 2x HDMI2.1, 2x HDMI2.0\r\n• เทคโนโลยีการซิงค์ : FreeSync Premium Pro', '1759925344_10.jpg', 14, '2025-10-08 12:09:04'),
(162, 'SMARTPHONE (สมาร์ทโฟน) SAMSUNG GALAXY S25 ULTRA (12GB/256GB) (TITANIUM WHITESILVER) (SM-S938BZSBTHL)', '40900.00', 23, '● Display/Screen Size: QHD+ 6.9\"\r\n● Chipset: Qualcomm Snapdragon 8 Elite\r\n● Ram/Rom: 12GB/256GB\r\n● OS: One UI 7.0 Base on Android 15', '1759925650_1.jpg', 15, '2025-10-08 12:14:10'),
(163, 'SMARTPHONE (สมาร์ทโฟน) SAMSUNG GALAXY S25 ULTRA (12GB/512GB) (TITANIUM SILVERBLUE) (SM-S938BZBCTHL)', '46900.00', 23, '● Display/Screen Size: QHD+ 6.9\"\r\n● Chipset: Qualcomm Snapdragon 8 Elite\r\n● Ram/Rom: 12GB/512GB\r\n● OS: One UI 7.0 Base on Android 15', '1759925717_2.jpg', 15, '2025-10-08 12:15:17'),
(164, 'SMARTPHONE (สมาร์ทโฟน) OPPO RENO13 5G (12GB/256GB) (LUMINOUS BLUE)', '14199.00', 20, '● Display/Screen Size: FHD+ 6.59\"\r\n● Chipset: MediaTek Dimensity 8350\r\n● Ram/Rom: 12GB/256GB\r\n● OS: Android 15', '1759925783_3.jpg', 15, '2025-10-08 12:16:23'),
(165, 'SMARTPHONE (สมาร์ทโฟน) OPPO RENO13 5G (12GB/256GB) (PLUME WHITE)', '14199.00', 20, '● Display/Screen Size: FHD+ 6.59\"\r\n● Chipset: MediaTek Dimensity 8350\r\n● Ram/Rom: 12GB/256GB\r\n● OS: Android 15', '1759925824_4.jpg', 15, '2025-10-08 12:17:04'),
(166, 'SMARTPHONE (สมาร์ทโฟน) SAMSUNG GALAXY A36 5G - 8GB/128GB LIGHT GREEN', '11199.00', 20, 'Samsung Galaxy A36 5G เป็นสมาร์ทโฟนระดับกลางที่มาพร้อมหน้าจอ Super AMOLED ขนาด 6.7 นิ้ว อัตรารีเฟรช 120Hz ให้ภาพคมชัดและลื่นไหล ขับเคลื่อนด้วยชิปเซ็ต Snapdragon รองรับ 5G แบตเตอรี่ 5000mAh ใช้งานได้ตลอดวัน พร้อมกล้องหลัก 50MP ถ่ายภาพสวยคมชัด เหมาะสำหรับการใช้งานทั่วไปและความบันเทิง\r\n\r\n● Display/Screen Size: FHD+ 6.7\"\r\n● Chipset: Snapdragon 6 Gen 3\r\n● Ram/Rom: 8GB/128GB\r\n● OS: One UI 7.0 based on Android 15', '1759925918_5.jpg', 15, '2025-10-08 12:18:38'),
(167, 'SMARTPHONE (สมาร์ทโฟน) SAMSUNG GALAXY A56 5G - 8GB/128GB AWESOME GRAPHITE', '12999.00', 20, 'Samsung Galaxy A56 5G มาพร้อมหน้าจอ Super AMOLED ขนาด 6.7 นิ้ว ความละเอียด FHD+ และอัตรารีเฟรช 120Hz ขับเคลื่อนด้วยชิปเซ็ต Exynos 1580 พร้อม RAM 8GB และพื้นที่เก็บข้อมูล 128GB กล้องหลัง 3 เลนส์: เลนส์หลัก 50MP (OIS), เลนส์ Ultra-Wide 12MP และเลนส์ Macro 5MP ​\r\n\r\n● Display/Screen Size: FHD+ 6.7\"\r\n● Chipset: Exynos 1580\r\n● Ram/Rom: 8GB/128GB\r\n● OS: One UI 7.0 based on Android 15', '1759926019_6.webp', 15, '2025-10-08 12:20:12'),
(168, 'SMARTPHONE (สมาร์ทโฟน) REDMI NOTE 14 PRO 5G - 12GB/256GB CORAL GREEN', '10790.00', 20, 'Redmi Note 14 Pro 5G สมาร์ทโฟนระดับกลาง มาพร้อมกล้อง AI 200MP ชิป Dimensity 7300 Ultra จอ AMOLED 120Hz แบตเตอรี่ 5110mAh ชาร์จไว 45W กันน้ำกันฝุ่น IP68\r\n\r\n• หน้าจอแสดงผล: AMOLED ขนาด 6.67 นิ้ว ความละเอียด 1.5K (2712 x 1220 พิกเซล)\r\n• ชิปประมวลผล: MediaTek Dimensity 7300-Ultra\r\n• หน่วยความจำ (RAM/ROM): 12GB/256GB\r\n• ระบบปฏิบัติการ: Xiaomi HyperOS บนพื้นฐาน Android 14', '1759926076_6.jpg', 15, '2025-10-08 12:21:16'),
(169, 'SMARTPHONE (สมาร์ทโฟน) REDMI NOTE 14 PRO 5G - 12GB/256GB MIDNIGHT BLACK', '10790.00', 20, 'Redmi Note 14 Pro 5G สมาร์ทโฟนระดับกลาง มาพร้อมกล้อง AI 200MP ชิป Dimensity 7300 Ultra จอ AMOLED 120Hz แบตเตอรี่ 5110mAh ชาร์จไว 45W กันน้ำกันฝุ่น IP68\r\n\r\n• หน้าจอแสดงผล: AMOLED ขนาด 6.67 นิ้ว ความละเอียด 1.5K (2712 x 1220 พิกเซล)\r\n• ชิปประมวลผล: MediaTek Dimensity 7300-Ultra\r\n• หน่วยความจำ (RAM/ROM): 12GB/256GB\r\n• ระบบปฏิบัติการ: Xiaomi HyperOS บนพื้นฐาน Android 14', '1759926122_8.jpg', 15, '2025-10-08 12:22:02'),
(170, 'SMARTPHONE (สมาร์ทโฟน) REALME C71 - 8GB/128GB FOREST OWL', '4799.00', 20, 'realme C71 คือสมาร์ตโฟนระดับเริ่มต้นที่เน้นความทนทาน ด้วยดีไซน์ “Light Feather” บางเฉียบและโครงสร้าง Armorshell ผ่านมาตรฐาน MIL‑STD‑810H กันน้ำ–ฝุ่น IP64 และทนต่อการตก รับประกันความแข็งแรง พร้อมการใช้งานทั่วไปอย่างไร้กังวล\r\n\r\n• หน้าจอแสดงผล: 6.67 นิ้ว\r\n• ซีพียู: UNISOC T7250\r\n• แรม/รอม: 8GB/128GB\r\n• ระบบปฏิบัติการ: realme UI 6.0 - Android 15', '1759926205_9.jpg', 15, '2025-10-08 12:23:25'),
(171, 'SMARTPHONE (สมาร์ทโฟน) REALME C75 - 8GB/256GB LIGHTNING GOLD', '6299.00', 20, 'realme C75 คือสมาร์ตโฟน “สายถึก” ที่มาพร้อมความคงทนขั้นสุดเหนือระดับในราคาย่อมเยา ด้วยโครง ArmorShell ผ่านการทดสอบ MIL‑STD‑810H และได้รับ IP69 กันน้ำ–ฝุ่นระดับท็อป มาพร้อม AI ฟีเจอร์อัจฉริยะ เหมาะสำหรับผู้ที่ต้องการมือถือใช้งานได้ไหลลื่นและทนทานในทุกสถานการณ์\r\n\r\n• หน้าจอแสดงผล: 6.72 นิ้ว\r\n• ซีพียู: Helio G92 Max\r\n• แรม/รอม: 8GB/256GB\r\n• ระบบปฏิบัติการ: Android 14', '1759926257_10.webp', 15, '2025-10-08 12:24:17'),
(177, 'CALCULATOR (เครื่องคิดเลข) SHARP EL-VN83SB-BK', '890.00', 20, '• แผงเป็นอลูมิเนียม เรียบหรู มีสไตล์ ให้ผิวสัมผัสของแผงปุ่มกด สวยงาม แข็งแรง ไม่แตกหักง่าย\r\n• ปุ่มกดทำจากพลาสติกที่มีสารต้านเชื้อแบคทีเรีย ป้องกันการเจริญเติบโตของแบคทีเรียบนพื้นผิวเพื่อสุขภาพที่ดี\r\n• สามารถปรับเอียงหน้าจอได้ถึง 40 องศา รับกับสายตาไม่ว่าจะวางมุมไหน\r\n• ปุ่มยางขนาดใหญ่อยู่ด้านหลัง ช่วงลดการลื่นไหลของเครื่องคิดเลข', '1759926947_1.jpg', 17, '2025-10-08 12:35:47'),
(178, 'CALCULATOR (เครื่องคิดเลข) SHARP EL-VN83SB-WH', '890.00', 20, '• แผงเป็นอลูมิเนียม เรียบหรู มีสไตล์ ให้ผิวสัมผัสของแผงปุ่มกด สวยงาม แข็งแรง ไม่แตกหักง่าย\r\n• ปุ่มกดทำจากพลาสติกที่มีสารต้านเชื้อแบคทีเรีย ป้องกันการเจริญเติบโตของแบคทีเรียบนพื้นผิวเพื่อสุขภาพที่ดี\r\n• สามารถปรับเอียงหน้าจอได้ถึง 40 องศา รับกับสายตาไม่ว่าจะวางมุมไหน\r\n• ปุ่มยางขนาดใหญ่อยู่ด้านหลัง ช่วงลดการลื่นไหลของเครื่องคิดเลข', '1759926991_2.jpg', 17, '2025-10-08 12:36:31'),
(179, 'CALCULATOR (เครื่องคิดเลข) SHARP EL-VM72B-BK', '690.00', 20, '• ดีไซน์เรียบหรู ออกแบบแผงปุ่มกดมีลักษณะเรียบ เนียน สบายมือ ไม่สะดุด\r\n• โครงสร้างที่รองรับการกดปุ่มแบบโดยตรง ตอบสนองได้รวดเร็ว คำนวณได้อย่างมีประสิทธิภาพ\r\n• ขนาดกระทัดรัดเหมาะกับการถือมือเดียว สะดวก ใช้งานง่าย พกพาไปได้ทุกที่\r\n• สามารถคำนวณภาษีและมีปุ่มจดจำข้อมูล ไม่พลาดทุกการคำนวณ และได้ผลลัพธ์อย่างถูกต้อง', '1759927027_3.jpg', 17, '2025-10-08 12:37:07'),
(180, 'CALCULATOR (เครื่องคิดเลข) SHARP EL-VM72B-WH', '690.00', 20, '• ดีไซน์เรียบหรู ออกแบบแผงปุ่มกดมีลักษณะเรียบ เนียน สบายมือ ไม่สะดุด\r\n• โครงสร้างที่รองรับการกดปุ่มแบบโดยตรง ตอบสนองได้รวดเร็ว คำนวณได้อย่างมีประสิทธิภาพ\r\n• ขนาดกระทัดรัดเหมาะกับการถือมือเดียว สะดวก ใช้งานง่าย พกพาไปได้ทุกที่\r\n• สามารถคำนวณภาษีและมีปุ่มจดจำข้อมูล ไม่พลาดทุกการคำนวณ และได้ผลลัพธ์อย่างถูกต้อง', '1759927077_4.jpg', 17, '2025-10-08 12:37:57'),
(181, 'CALCULATOR (เครื่องคิดเลข) CANON AS-120 ll (BLACK)', '258.00', 20, '- แบบตั้งโต๊ะขนาดกลาง\r\n- จอภาพ LCD\r\n- ปุ่ม 00 เพิ่มความสะดวกได้รวดเร็วยิ่งขึ้น\r\n- ใช้พลังงาน 2 ระบบ', '1759927109_5.jpg', 17, '2025-10-08 12:38:29'),
(182, 'CALCULATOR (เครื่องคิดเลข) CANON TX-1210Hi III (GRAY)', '665.00', 20, '- จอ LCD แสดงตัวเลขขนาดใหญ่ 12 หลัก\r\n- คำนวณค่าภาษี (TAX+, TAX-) และราคาตั้งขาย (MU) ได้อย่างรวดเร็ว\r\n- ปุ่ม GT แสดงผลยอดรวมอัตโนมัติ\r\n- ออกแบบปุ่มกดแบบ IT Touch Keyboard เสมือนสัมผัสแป้นคีย์บอร์ดคอมพิวเตอร์\r\n- ปุ่ม +/- สำหรับปรับเปลี่ยนค่าตัวเลขได้ง่าย\r\n- ใช้พลังงาน 2 ระบบ ทั้ง พลังงานแสงอาทิตย์ และแบตเตอรี่', '1759927141_6.jpg', 17, '2025-10-08 12:39:01'),
(183, 'CALCULATOR (เครื่องคิดเลข) CANON WS-1210Hi III (GRAY)', '715.00', 20, '• จอ LCD แสดงตัวเลข ขนาดใหญ่ 12 หลัก\r\n• ปรับระดับจอได้ เพื่อองศาที่พอดีต่อการมองเห็น\r\n• ใช้พลังงาน 2 ระบบ ทั้ง พลังงานแสงอาทิตย์ และแบตเตอรี่', '1759927175_7.jpg', 17, '2025-10-08 12:39:35'),
(184, 'CALCULATOR (เครื่องคิดเลข) CANON LS-100TS (GRAY)', '409.00', 20, '- จอแสดงผล LCD แสดงตัวเลข 10 หลัก\r\n- คำนวณหาค่าเปอร์เซ็นต์ทางธุรกิจ ต้นทุน ราคาขาย กำไร (Cost, Sell, Margin)\r\n- คำนวณภาษี (VAT) ได้อย่างรวดเร็ว\r\n- ใช้พลังงาน 2 ระบบ ทั้ง พลังงานแสงอาทิตย์ และแบตเตอรี่', '1759927215_8.jpg', 17, '2025-10-08 12:40:15'),
(185, 'CALCULATOR (เครื่องคิดเลข) CANON LS-88Hi III (PINK)', '248.00', 20, '• จอแสดงผล LCD ขนาดใหญ่พิเศษ แสดงตัวเลข 8 หลัก\r\n• ออกแบบจอให้ลาดเอียง เพื่อองศาที่พอดีต่อการมองเห็น\r\n• ปุ่มสแควรูท และเปอร์เซ็นต์\r\n• หน่วยความจำอิสระ Memory (M+, M-)\r\n• ใช้พลังงาน 2 ระบบ ทั้ง พลังงานแสงอาทิตย์ และแบตเตอรี่', '1759927249_9.jpg', 17, '2025-10-08 12:40:49'),
(186, 'CALCULATOR (เครื่องคิดเลข) CANON LS-88Hi III (PURPLE)', '248.00', 20, '• จอแสดงผล LCD ขนาดใหญ่พิเศษ แสดงตัวเลข 8 หลัก\r\n• ออกแบบจอให้ลาดเอียง เพื่อองศาที่พอดีต่อการมองเห็น\r\n• ปุ่มสแควรูท และเปอร์เซ็นต์\r\n• หน่วยความจำอิสระ Memory (M+, M-)\r\n• ใช้พลังงาน 2 ระบบ ทั้ง พลังงานแสงอาทิตย์ และแบตเตอรี่', '1759927279_10.jpg', 17, '2025-10-08 12:41:19'),
(187, 'PROJECTOR (โปรเจคเตอร์) XIAOMI SMART PROJECTOR L1 PRO - XMI-BHR07SXTH', '9990.00', 25, 'Xiaomi Smart Projector L1 Pro คือโปรเจ็กเตอร์อัจฉริยะที่เปลี่ยนทุกพื้นที่ให้กลายเป็นโรงหนังส่วนตัว ดีไซน์เรียบหรูตอบโจทย์ทั้งบ้านและออฟฟิศ ง่ายต่อการใช้งานด้วยระบบโฟกัสและปรับภาพอัตโนมัติ ทำให้คุณเพลิดเพลินกับภาพคมชัดสุดงามบนจอใหญ่ได้ทุกมุมอย่างไร้กังวล', '1759927536_1.jpg', 18, '2025-10-08 12:45:36'),
(188, 'PROJECTOR (โปรเจคเตอร์) EPSON EF-21G', '24590.00', 25, 'Epson EpiqVision Mini EF‑21G โปรเจคเตอร์ดีไซน์หรู ขนาดกะทัดรัด พร้อม Google TV และ Google Cast ในตัว เพลิดเพลินกับคอนเทนต์เต็มจอถึง 150″ เสียงสมจริงจากลำโพงในตัว รองรับ HDR, HLG และให้ความคมชัดสูงสไตล์ ‘Smart Lifestyle’ เหมาะสำหรับบ้านทันสมัย\r\n', '1759927573_2.jpg', 18, '2025-10-08 12:46:13'),
(189, 'PROJECTOR (โปรเจคเตอร์) WANBO T2 ULTRA - WHITE', '5910.00', 25, 'WANBO T2 Ultra โปรเจคเตอร์ Full HD 1080p มาพร้อม Android TV 11 ลำโพงคู่เสียงกระหึ่ม ปรับภาพอัตโนมัติ ใช้งานง่าย ดูหนังหรือสตรีมได้ทุกที่\r\n\r\n• ความสว่าง: 500 ANSI Lumens\r\n• ความละเอียด: 1920 x 1080 FHD', '1759927619_3.jpg', 18, '2025-10-08 12:46:59'),
(190, 'PROJECTOR (โปรเจคเตอร์) WANBO DAVINCI 1 PRO - GRAY', '8910.00', 25, 'WANBO DAVINCI 1 PRO โปรเจคเตอร์อัจฉริยะความละเอียด FHD รองรับ Android TV มีระบบเสียง Dolby Audio ภาพสว่างคมชัด พกพาง่าย ใช้งานได้ทุกที่\r\n\r\n• ความสว่าง: 600 ANSI Lumens\r\n• ความละเอียด: 1920 x 1080 FHD', '1759927648_4.jpg', 18, '2025-10-08 12:47:28'),
(191, 'PROJECTOR (โปรเจคเตอร์) WANBO MOZART 1 PRO', '13010.00', 25, 'WANBO Mozart 1 Pro โปรเจคเตอร์อัจฉริยะ Full HD สว่าง 900 ANSI ลูเมน รองรับ 4K และ HDR10 มาพร้อม Android TV 11, ลำโพงคู่ 8W, โฟกัสอัตโนมัติ และระบบหลีกเลี่ยงสิ่งกีดขวาง ใช้งานได้ทั้งดูหนังและเล่นเกม\r\n\r\n• ความสว่าง: 900 ANSI Lumens\r\n• ความละเอียด: 1920 x 1080 FHD', '1759927678_5.jpg', 18, '2025-10-08 12:47:58'),
(192, 'PROJECTOR (โปรเจคเตอร์) WANBO X5 PRO - BLACK', '10590.00', 25, 'WANBO X5 Pro โปรเจคเตอร์อัจฉริยะความสว่าง 1100 ANSI ลูเมน ความละเอียด Full HD รองรับ 4K มาพร้อม Android TV 11 ใช้งานแอป Netflix, YouTube ได้ทันที พร้อมระบบโฟกัสอัตโนมัติ และเสียงคุณภาพด้วยลำโพงคู่ 5W\r\n\r\n• ความสว่าง: 1100 ANSI Lumens\r\n• ความละเอียด: 1920 x 1080 FHD', '1759927720_6.jpg', 18, '2025-10-08 12:48:40'),
(193, 'PROJECTOR (โปรเจคเตอร์) WANBO X2 MAX - WHITE', '4010.00', 25, 'Wanbo X2 Max โปรเจคเตอร์ Full HD 1080p มาพร้อม Android 9.0, ความสว่าง 450 ANSI Lumens, ระบบโฟกัสอัตโนมัติ, รองรับ Wi-Fi 6 และลำโพงคู่ ใช้งานง่าย เหมาะกับดูหนัง เล่นเกม หรือพรีเซนต์งานในบ้าน\r\n\r\n• ความสว่าง: 450 ANSI Lumens\r\n• ความละเอียด: 1920 x 1080 FHD', '1759927752_7.jpg', 18, '2025-10-08 12:49:12'),
(194, 'PROJECTOR (โปรเจคเตอร์) ACER X1228i DLP (MR.JTV11.006)', '14900.00', 25, '• Maximum Resolution: 1920 x 1200\r\n• Lamp Power: 220W\r\n• Power source: 120 - 230 V\r\n• Contrast Ratio:20,000:1', '1759927788_8.jpg', 18, '2025-10-08 12:49:48'),
(195, 'PROJECTOR (โปรเจคเตอร์) BENQ 4K HDR 4LED GAMING PROJECTOR (X3100I)', '89990.00', 25, '• โปรเจคเตอร์เกมมิ่ง 4K HDR 4LED\r\n• ความสว่างสูง 4LED เฉดสีที่สดใส และพื้นที่สี DCI-P3 100%\r\n• ด้วยการจับคู่โทน HDR และเทคโนโลยี SSI Dynamic Black ช่วยปรับความเข้มของแสง\r\n• สัมผัสทุกมิติของการเล่นเกมผ่านตำแหน่งเสียงที่แม่นยำ', '1759927818_9.jpg', 18, '2025-10-08 12:50:18'),
(196, 'PROJECTOR (โปรเจคเตอร์) ACER M311 DLP WXGA (MR.JUT11.00W) WHITE', '23900.00', 25, '• Maximum Resolution: 1920 x 1200\r\n• Lamp Power: 220W\r\n• Power source: 120 - 230 V\r\n• Contrast Ratio:20,000:1', '1759927849_10.jpg', 18, '2025-10-08 12:50:49'),
(197, 'ACTION CAMERA (กล้องแอคชั่น) DJI CAMERA OSMO 360 ADVENTURE COMBO', '18090.00', 20, 'DJI Osmo Action 360 กล้องแอคชันที่มาพร้อมมุมมอง 360 องศา เก็บทุกจังหวะของการเคลื่อนไหวได้อย่างสมจริง เหมาะสำหรับสายผจญภัย คอนเทนต์ครีเอเตอร์ หรือผู้ที่ต้องการภาพมุมมองแปลกใหม่ ด้วยดีไซน์กะทัดรัดและฟีเจอร์ล้ำสมัยในเครื่องเดียว\r\n', '1759927937_1.jpg', 19, '2025-10-08 12:52:17'),
(198, 'ACTION CAMERA (กล้องแอคชั่น) DJI OSMO ACTION 5 ADVENTURE COMBO', '16900.00', 25, '• Osmo Action 5 Pro x1\r\n• Osmo Action Extreme Battery Plus (1950 mAh) x3\r\n• Osmo Action Horizontal-Vertical Protective Frame x1\r\n• Osmo Action Quick-Release Adapter Mount x1\r\n• Osmo Action Quick-Release Adapter Mount (Mini) x1\r\n• Osmo Action Curved Adhesive Base x1\r\n• Osmo Locking Screw x2\r\n• Type-C to Type-C PD Cable x1\r\n• Osmo Action Multifunctional Battery Case x1\r\n• Osmo 1.5m Extension Rod x1\r\n• Osmo Action 5 Pro Rubber Lens Protector x1\r\n• Osmo Action 5 Pro Glass Lens Cover x1\r\n• Osmo Action Anti-Slip Pad x1\r\n• DJI Logo Sticker x1\r\n• Quick Start Guide x1\r\n• Disclaimer x1\r\n• Warranty Card x1', '1759927978_2.jpg', 19, '2025-10-08 12:52:58'),
(199, 'ACTION CAMERA (กล้องแอคชั่น) DJI OSMO POCKET 3 CREATOR COMBO', '21900.00', 20, '● 1\" CMOS & 4K120\r\n● 2 Inch Rotatable Screen & Smart Horizontal-Vertical Shooting\r\n● 3-Axis Gimbal Mechanical Stabilization\r\n● ActiveTrack 6.0\r\n● Full-Pixel Fast Focusing\r\n● D-Log M & 10bit\r\n● Stereo Recording\r\n● Pocket-Sized', '1759928088_3.jpg', 19, '2025-10-08 12:54:48'),
(200, 'CAMERA (กล้องสำหรับเด็ก) MYFIRST CAMERA INSTA WI TEAL (FC2402SA-TL01)', '4099.00', 20, '• Photos & Videos\r\n• Instant Print\r\n• Connect App Via WiFi\r\n• Create Customized Labels', '1759928123_4.jpg', 19, '2025-10-08 12:55:23'),
(201, 'CAMERA (กล้องสำหรับเด็ก) MYFIRST CAMERA 3 PINK (FC2003SA-PK01)', '2690.00', 20, '• กล้องชัด 16 ล้านพิกเซล\r\n• หน้าจอสี ขนาด 2.0 นิ้ว\r\n• ความจุแบตเตอรี่ 1000 mAh\r\n• น้ำหนักกล้อง 80 กรัม', '1759928156_5.jpg', 19, '2025-10-08 12:55:56'),
(202, 'CAMERA (กล้องสำหรับเด็ก) MYFIRST CAMERA 3 BLUE (FC2003SA-BE01)', '2690.00', 20, '• กล้องชัด 16 ล้านพิกเซล\r\n• หน้าจอสี ขนาด 2.0 นิ้ว\r\n• ความจุแบตเตอรี่ 1000 mAh\r\n• น้ำหนักกล้อง 80 กรัม', '1759928193_6.jpg', 19, '2025-10-08 12:56:33'),
(203, 'ACTION CAMERA (กล้อง) GOPRO HERO 12 BLACK CREATOR EDITION (CHDFB-121-AS)', '22900.00', 20, '● Creator Set\r\n- Light Mod ไฟเสริมเพิ่มความสว่างและความคมชัดให้กับภาพของคุณ\r\n- Volta ไม้จับกล้องพร้อมแบตเตอรี่ 4,900 mAh พร้อมขาตั้ง 3 ขาและรีโมทในตัว\r\n- Media Mod ช่วยให้กล้อง GoPro รับเสียงได้ดีขึ้น มีช่องพอร์ท Micro HDMI สำหรับต่อเข้ากับมอนิเตอร์\r\n● HDR (High Dynamic Range) Video + Photo for more vivid images\r\n● Longer runtimes, including 1.5 hours at 5.3K30 and over 2.5 hours at 1080p30²\r\n● Optional Max Lens Mod 2.0 lens accessory enables ultra wide angle, 177-degree field of view in 4K60\r\n● New Bluetooth audio support for AirPods + other Bluetooth audio devices for wireless sound recording and voice control\r\n● 5.3K60, 4K120 and 2.7K240 video resolutions\r\n● HyperSmooth 6.0 video stabilization with 360° Horizon Lock³\r\n● Large image sensor captures ultra wide 156° field of view in 8:7\r\n● 27 megapixel photos with 24.7 megapixel stills from video\r\n● Waterproof to 33ft + legendary GoPro ruggedness', '1759928231_7.jpg', 19, '2025-10-08 12:57:11'),
(204, 'ACTION CAMERA (กล้องแอคชั่น) DJI OSMO ACTION 4 ADVENTURE COMBO', '13600.00', 20, '● แบตเตอรี่ทนความเย็นและใช้งานได้ยาวนาน\r\n● 4K/120fps & 155º FOV มุมกว้างพิเศษ\r\n● แม่เหล็กปล่อยเร็ว & รองรับการถ่ายภาพแนวตั้ง\r\n● ถ่ายรูปขอบฟ้า 360ºอย่างมั่นคง\r\n● ประสิทธิภาพการกันน้ำได้ถึง 18m', '1759928263_8.jpg', 19, '2025-10-08 12:57:43'),
(205, 'ACTION CAMERA (กล้องแอคชั่น) DJI OSMO ACTION 3 ADVENTURE COMBO', '10290.00', 20, '● ความละเอียด 4K/120fps & Super-Wide FOV\r\n● ระบกันสั่น HorizonSteady\r\n● กันน้ำลึก 16 เมตร', '1759928313_9.jpg', 19, '2025-10-08 12:58:33'),
(206, 'ACTION CAMERA (กล้อง) DJI POCKET 2 CREATOR COMBO', '13590.00', 20, '• Pocket-Sized\r\n• 3-Axis Stabilized Camera\r\n• ActiveTrack 3.0\r\n• AI Editor\r\n• High-Quality Images\r\n• DJI Matrix Stereo', '1759928349_10.jpg', 19, '2025-10-08 12:59:09'),
(207, 'MONITOR (จอมอนิเตอร์) DAHUA DHI-LM25-E231B - 24.5 IPS FHD 180Hz (3Y)', '70000.00', 50, 'i have', '1760773699_products71846_800.jpg', 4, '2025-10-18 07:48:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `p_id` (`p_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`p_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`p_id`) REFERENCES `product` (`p_id`) ON DELETE SET NULL;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
