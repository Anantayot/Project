-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 19, 2026 at 12:46 PM
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
(1, 'Notebook (โน้ตบุ๊ก)', ''),
(2, 'Keyboard (คีย์บอร์ด)', ''),
(3, 'Gaming Chair (เก้าอี้เกมมิ่ง)', ''),
(4, 'Monitor (จอภาพ)', ''),
(5, 'Joystick (จอยเกม)', ''),
(6, 'Cooling System (ระบบระบายความร้อน)', ''),
(7, 'CPU (ซีพียู)', ''),
(8, 'Mouse (เมาส์)', ''),
(9, 'Gaming Headset (หูฟังเกมมิ่ง)', ''),
(10, 'Speaker (ลำโพง)', ''),
(11, 'UPS (เครื่องสำรองไฟ)', ''),
(12, 'Graphics Card (การ์ดจอ)', ''),
(13, 'Webcam (เว็บแคม)', ''),
(14, 'RAM (แรม)', ''),
(15, 'Storage (อุปกรณ์จัดเก็บข้อมูล)', ''),
(16, 'Motherboard (เมนบอร์ด)', ''),
(17, 'Power Supply (พาวเวอร์ซัพพลาย)', ''),
(18, 'Mouse Pad (แผ่นรองเมาส์)', ''),
(19, 'Sound Card (การ์ดเสียง)', ''),
(20, 'Computer Case (เคสคอมพิวเตอร์)', '');

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
(1, 1, '53780.00', 'ประจวบ', 'COD', NULL, NULL, 'ชำระเงินแล้ว', 'อนุมัติ', 'กำลังจัดเตรียม', '2026-01-13 10:24:04', NULL, NULL, NULL),
(2, 1, '25880.00', 'ประจวบ', 'QR', NULL, NULL, 'ชำระเงินแล้ว', 'อนุมัติ', 'สำเร็จ', '2026-01-13 15:57:51', NULL, NULL, NULL),
(3, 1, '5380.00', 'ประจวบ', 'COD', NULL, NULL, 'รอดำเนินการ', 'รอตรวจสอบ', 'รอดำเนินการ', '2026-02-16 05:47:51', NULL, NULL, NULL),
(4, 1, '11890.00', 'ประจวบ', 'COD', NULL, NULL, 'รอดำเนินการ', 'รอตรวจสอบ', 'รอดำเนินการ', '2026-02-16 05:48:37', NULL, NULL, NULL),
(5, 1, '7980.00', 'ประจวบ', 'COD', NULL, NULL, 'ชำระเงินแล้ว', 'อนุมัติ', 'สำเร็จ', '2026-02-18 03:42:54', NULL, NULL, NULL),
(6, 1, '12340.00', 'ประจวบ', 'COD', NULL, NULL, 'ชำระเงินแล้ว', 'อนุมัติ', 'กำลังจัดเตรียม', '2026-02-18 03:43:11', NULL, NULL, NULL);

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
(1, 1, 66, 2, '17990.00'),
(2, 1, 29, 2, '8900.00'),
(3, 2, 59, 1, '1990.00'),
(4, 2, 6, 1, '20990.00'),
(5, 2, 33, 1, '2900.00'),
(6, 3, 11, 1, '390.00'),
(7, 3, 57, 1, '4990.00'),
(8, 4, 46, 1, '8990.00'),
(9, 4, 33, 1, '2900.00'),
(10, 5, 60, 1, '3990.00'),
(11, 5, 189, 1, '3990.00'),
(12, 6, 141, 1, '3290.00'),
(13, 6, 129, 1, '1150.00'),
(14, 6, 170, 1, '7900.00');

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
(1, 'ASUS VIVOBOOK GO 15 M1504FA-NJ516W (MIXED BLACK)', '12990.00', 51, '• AMD Ryzen 5 7520U\r\n• 8GB LPDDR5 (ON BOARD)\r\n• 512GB PCIe/NVMe M.2 SSD\r\n• 15.6\" FHD Anti-Glare\r\n• AMD Radeon Graphics (Integrated)\r\n• Windows 11 Home', '1770968588_1.jpg', 1, '2026-02-13 07:43:08'),
(2, 'ACER ASPIRE LITE16 AL16-52P-51Y1 (GRAY)', '15490.00', 30, '• Intel Core i5-1334U\r\n• 16GB DDR5\r\n• 512GB NVMe PCIe M.2 SSD\r\n• 16\" WUXGA (1920x1200) Non-Touch\r\n• Intel Graphics (Integrated)\r\n• Windows 11 Home + Office Home 2024', '1770968675_1.jpg', 1, '2026-02-13 07:43:44'),
(3, 'ACER ASPIRE LITE 15 AL15-41P-R47V (SILVER)', '15990.00', 40, '• AMD Ryzen 7 5700U\r\n• 16GB DDR4\r\n• 512GB NVMe PCIe M.2 SSD\r\n• 15.6\" FHD (1920x1080) IPS\r\n• AMD Radeon Graphics (Integrated)\r\n• Windows 11 Home + Office Home 2024', '1770968652_1.jpg', 1, '2026-02-13 07:44:12'),
(4, 'DELL INSPIRON 3530-OIN3530340701GTH-PS (PLATINUM SILVER)', '20990.00', 10, '• Intel Core i5-1334U\r\n• 16GB (8GB x 2) DDR4 2666MHz\r\n• 512GB PCIe/NVMe M.2 SSD\r\n• 15.6\" FHD (1920 x 1080) 120Hz WVA Anti-Glare 250nit\r\n• Intel Iris Xe Graphics (Integrated)\r\n• Windows 11 Home / Office Home & Student 2021', '1770968706_1.jpg', 1, '2026-02-13 07:45:06'),
(5, 'HP 15-FD0342TU', '16990.00', 4, '• Intel Core i5-1334U\r\n• 16GB DDR4 3200\r\n• 512GB NVMe PCIe M.2 SSD\r\n• 15.6\" FHD (1920x1080) IPS Non-Touch\r\n• Intel Iris Xe Graphics (Integrated)\r\n• Windows 11 Home + Office Home & Student 2021 + 365 Basic', '1770968736_1.jpg', 1, '2026-02-13 07:45:36'),
(6, 'ACER ASPIRE 7 A715-59G-54H5 - BLACK', '20990.00', 5, '• ซีพียู : Intel Core i5-12450H\r\n• แรม : 16GB DDR4\r\n• เอสเอสดี : 512GB PCIe/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920x1080) 144Hz IPS\r\n• กราฟิก : Nvidia GeForce RTX3050 6GB GDDR6\r\n• ซอฟต์แวร์ : Windows 11 Home', '1770968763_1.jpg', 1, '2026-02-13 07:46:03'),
(7, 'ASUS VIVOBOOK 15 X1504VA-NJ348WA (QUIET BLUE)', '12990.00', 5, '• Intel Core i3-1315U\r\n• 8GB DDR4 (On Board)\r\n• 512GB PCIe 3/NVMe M.2 SSD\r\n• 15.6\" FHD (1920 x 1080) 60Hz 250nits 45% NTSC\r\n• Intel UHD Graphics (Integrated)\r\n• Windows 11 Home / Office Home 2024 / Microsoft 365 Basic', '1770968801_1.jpg', 1, '2026-02-13 07:46:29'),
(8, 'ASUS VIVOBOOK 16 X1607CA-MB535WA - QUIET BLUE', '24990.00', 5, '• ซีพียู : Intel Core Ultra 5 225H\r\n• แรม : 16GB DDR5 (On Board)\r\n• เอสเอสดี : 512GB PCIe 4/NVMe M.2 SSD\r\n• จอแสดงผล : 16\" WUXGA (1920 x 1200) IPS-level Panel 60Hz 45% NTSC 300nits\r\n• กราฟิก : Intel Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home 2024 / Microsoft 365 Basic', '1770968839_1.jpg', 1, '2026-02-13 07:47:19'),
(9, 'ACER X BUTTERBEAR ASPIRE LITE 15 LIMITED EDITION AL15-42P-R6N3', '19990.00', 6, '• ซีพียู : AMD Ryzen 5 7430U\r\n• แรม : 32GB DDR4\r\n• เอสเอสดี : 512GB PCIe/NVMe M.2 SSD\r\n• จอแสดงผล : 15.6\" FHD (1920x1080)\r\n• กราฟิก : AMD Radeon Graphics (Integrated)\r\n• ซอฟต์แวร์ : Windows 11 Home / Office Home 2024 / Microsoft 365 Basic', '1770968868_1.jpg', 1, '2026-02-13 07:47:48'),
(10, 'DELL XPS13-SNSX739001 (SILVER)', '71900.00', 7, 'i7-10510U/16GB/512GB SSD/Integrated Graphics/13.3\"UHD Touch/Win10Pro/Silver\r\nWarranty : 3Y. (1Y. On-site + 2Y. Carry-in)', '1770968893_1.jpg', 1, '2026-02-13 07:48:13'),
(11, 'SIGNO KB-725W EXODIA RUBBER DOME SWITCH RGB EN/TH - WHITE', '390.00', 8, '• สวิตช์ : Rubber Dome Switch\r\n• ขนาด : 96%\r\n• แสงไฟ : RGB\r\n• คีย์แคป : ภาษาอังกฤษ / ภาษาไทย\r\n• เลย์เอาต์ : ANSI\r\n• การเชื่อมต่อ : แบบใช้สาย\r\n• สายเคเบิล : สาย USB-C เป็น USB-A', '1770969084_1.jpg', 2, '2026-02-13 07:51:24'),
(12, 'NUBWO NK44 - WHITE', '199.00', 18, 'คีย์บอร์ดแบบ Rubber Dome\r\nเชื่อมต่อใช้งานด้วย USB 2.0\r\nรองรับระบบ Windows / Android / Mac / Smart TV', '1770969133_1.jpg', 2, '2026-02-13 07:52:13'),
(13, 'SIGNO KB-712 (RUBBER DOME) (ILLUMINATED)', '250.00', 19, '• Switch : Rubber Dome\r\n• Lighting : 3 Mode LED\r\n• Keycap Font : English/Thai\r\n• Connectivity : USB', '1770969166_1.jpg', 2, '2026-02-13 07:52:46'),
(14, 'RAZER BLACKWIDOW V3 TENKEYLESS (RAZER GREEN SWITCH) (RGB) (EN/TH)', '2890.00', 16, '• Switch : Razer Green Switch (Clicky)\r\n• Lighting : RGB\r\n• Keycap Font : English/Thai\r\n• Connectivity : USB Type-A (Wired)', '1770969200_1.jpg', 2, '2026-02-13 07:53:20'),
(15, 'HYPERX ALLOY ORIGINS 60 (BLACK) (HYPERX RED SWITCH - RGB - EN)', '2290.00', 15, '• HyperX Red Switch (Linear)\r\n• RGB LED\r\n• English Legends (Font)\r\n• ANSI\r\n• Wired USB (USB-C to USB-A)', '1770969226_1.jpg', 2, '2026-02-13 07:53:46'),
(16, 'RAZER ORNATA V3 X (BLACK) (MEMBRANE - RGB -EN/TH)', '1090.00', 14, '• Membrane\r\n• RGB LED\r\n• English / Thai Keycap\r\n• ANSI\r\n• Wired USB', '1770969253_1.jpg', 2, '2026-02-13 07:54:13'),
(17, 'STEELSERIES APEX 5 (STEELSERIES HYBRID BLUE MECHANICAL RGB SWITCH) (RGB LED) (EN)', '4590.00', 18, '• SteelSeries Hybrid Blue Mechanical RGB Switch (Clicky)\r\n• RGB LED\r\n• English Legend (Font)\r\n• Wired USB 2.0\r\n• OLED Smart Display', '1770969289_1.jpg', 2, '2026-02-13 07:54:49'),
(18, 'AJAZZ AK680 LETS PLAY (RED-WHITE) (BLUE SWITCH RAINBOW LED EN/TH)', '650.00', 17, '• Blue Switch (Clicky)\r\n• Rainbow LED\r\n• English / Thai Keycap\r\n• ANSI\r\n• Wired (Detachable USB-C to USB-A)\r\n• Windows / macOS / Android', '1770969319_1.jpg', 2, '2026-02-13 07:55:19'),
(19, 'REDRAGON K617 FIZZ (WHITE & GREY) (BLUE SWITCH - RGB LED - EN/TH)', '1290.00', 17, '• Blue Switch (Clicky)\r\n• RGB LED\r\n• English / Thai Legends (Font)\r\n• ANSI\r\n• Wired USB (USB-C to USB-A)\r\n• Hot Swappable', '1770969344_1.jpg', 2, '2026-02-13 07:55:44'),
(20, 'AJAZZ AK820MAX MAGNETIC SWTICH EDITION (MOUNTAIN MIST) (MAGNETIC SWITCH RGB EN/TH)', '2290.00', 15, '• Magnetic Switch\r\n• RGB\r\n• English / Thai Keycap\r\n• ANSI\r\n• Wired (Detachable USB-C to USB-A)\r\n• Hot Swappable', '1770969369_1.jpg', 2, '2026-02-13 07:56:09'),
(21, 'SIGNO E-SPORT BAROCCO (GC-203BW) (BLACK-WHITE)', '4490.00', 5, 'Gaming Chair', '1770969464_1.jpg', 3, '2026-02-13 07:57:33'),
(22, 'ONEX GX3 (BLACK)', '5890.00', 12, 'Gaming Chair', '1770969493_1.jpg', 3, '2026-02-13 07:58:13'),
(23, 'ANDA SEAT DARK SERIES (AD4XL-WIZARD-B-PV/C) (BLACK)', '12900.00', 18, 'Gaming Chair', '1770969521_1.jpg', 3, '2026-02-13 07:58:41'),
(24, 'THERMALTAKE CYBERCHAIR E500 (GGC-EG5-BBLFDM-01) (BLACK)', '16900.00', 19, 'Gaming Chair', '1770969563_1.jpg', 3, '2026-02-13 07:59:23'),
(25, 'ONEX GX3 (BLACK-RED)', '5890.00', 19, 'Gaming Chair', '1770969590_1.jpg', 3, '2026-02-13 07:59:50'),
(26, 'OCPC SATAN SERIES (OC-GC-SAT-BW) (BLUE-WHITE)', '6990.00', 15, 'Gaming Chair', '1770969617_1.jpg', 3, '2026-02-13 08:00:17'),
(27, 'ANDA SEAT JIB SERIES (AD12XL-02-S-PV-JIB) (BLUE)', '15990.00', 12, 'Gaming Chair', '1770969666_1.jpg', 3, '2026-02-13 08:01:06'),
(28, 'DXRACER MITH TEAM (RZ134/NY) (BLACK-YELLOW)', '11500.00', 15, 'Gaming Chair', '1770969712_1.jpg', 3, '2026-02-13 08:01:37'),
(29, 'ANDA SEAT PHANTOM 3 SERIES (AD18Y-06-B-PV/C) (BLACK)', '8900.00', 17, 'Gaming Chair', '1770969791_1.jpg', 3, '2026-02-13 08:02:16'),
(30, 'NOBLECHAIRS EPIC (JAVA EDITION) PU HYBRID LEATHER (GC-NBC-EPIC-JE)', '13900.00', 18, 'Gaming Chair', '1770969764_1.jpg', 3, '2026-02-13 08:02:44'),
(31, 'ACER VG240YBMIIX 23.8 INCH IPS 75Hz', '3600.00', 14, '• Panel Size : 23.8\"\r\n• Panel Type : IPS\r\n• Maximum Resolution : 1920 x 1080\r\n• Refresh Rate : 75 Hz\r\n• Response Time : 1 ms\r\n• Variable Refresh Rate : AMD FreeSync', '1770969942_1.jpg', 4, '2026-02-13 08:05:42'),
(32, 'Asus Proart PA24ACRV - 23.8 Inch IPS 2K 75Hz', '9900.00', 19, '• ขนาดจอ : 23.8 นิ้ว\r\n• ประเภทจอ : IPS ไม่สะท้อนแสง\r\n• ความละเอียด : 2560 x 1440\r\n• รีเฟรชเรท : 75Hz\r\n• การตอบสนอง : 5ms\r\n• การรองรับสี : 16.7 ล้านสี\r\n• การเชื่อมต่อ : 1 x HDMI, 2 x DP, 1 x USB-C (DP Alt Mode + PD 96W)\r\n• เทคโนโลยีการซิงค์ : Adaptive Sync\r\n• การปรับตั้ง : ปรับได้ทั้งความสูง, หมุนแนวตั้ง, หมุนซ้าย-ขวา, และเอียง\r\n• การติดตั้งบนผนัง : VESA ขนาด 100 x 100 มม.', '1770969972_1.jpg', 4, '2026-02-13 08:06:12'),
(33, 'ASUS VA24EHF - 23.8 INCH IPS FHD 100Hz', '2900.00', 12, '• 23.8\"\r\n• IPS panel Anti-glare\r\n• 1920 x 1080 100Hz 1ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n• Tilt Adjustable\r\n• VESA mount 100 x 100 mm', '1770970002_1.jpg', 4, '2026-02-13 08:06:42'),
(34, 'ASUS ROG STRIX XG27ACS - 27 INCH FAST IPS 2K 180Hz', '10900.00', 15, '• 27\"\r\n• Fast IPS panel Non-glare\r\n• 2560 x 1440 180Hz (DP) 144Hz (HDMI) 1ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n• 1 x DP\r\n• 1 x USB-C (DP Alt Mode + PD 7.5W)\r\n• AMD FreeSync\r\n• Nvidia G-Sync Compatible\r\n• Height, Pivot, Swivel, Tilt adjustable\r\n• VESA mount 100 x 100 mm', '1770970034_1.jpg', 4, '2026-02-13 08:07:14'),
(35, 'SAMSUNG ODYSSEY G5 LS27CG510EEXXT - 27 INCH VA 2K 165Hz', '7500.00', 16, '• 27\"\r\n• VA panel Anti-glare\r\n• 2560 x 1440 165Hz 1ms\r\n• 16.7 million colors\r\n• 2 x HDMI\r\n• 1 x DP\r\n• AMD FreeSync Premium\r\n• Height, Pivot, Swivel, Tilt adjustable\r\n• VESA mount 100 x 100 mm', '1770970068_1.jpg', 4, '2026-02-13 08:07:48'),
(36, 'Asus Proart Pa248Qv - 24.1 Inch Ips Fhd 75Hz', '7350.00', 14, '• 24.1\"\r\n• IPS panel Non-glare\r\n• 1920 x 1200 75Hz 5ms\r\n• 16.7 million colors\r\n• 1 x HDMI\r\n• 1 x DP\r\n• Height, Pivot, Swivel, Tilt adjustable\r\n• VESA mount 100 x 100 mm', '1770970094_1.jpg', 4, '2026-02-13 08:08:14'),
(37, 'ASUS TUF GAMING VG249Q3A - 23.8 INCH IPS FHD 180Hz', '4300.00', 13, '• 23.8\"\r\n• IPS panel Non-glare\r\n• 1920 x 1080 180Hz 1ms\r\n• 16.7 million color\r\n• 2 x HDMI\r\n• 1 x DisplayPort\r\n• AMD FreeSync Premium\r\n• VESA mount 100 x 100 mm', '1770970122_1.jpg', 4, '2026-02-13 08:08:42'),
(38, 'MONITOR (จอมอนิเตอร์) DELL U2723QE - 27 INCH IPS 4K 60Hz', '17900.00', 16, '• Color gamut : 100% sRGB, 98% DCI-P3\r\n• Color Support : 1.07 Billion\r\n• Response Time : 5 ms(GTG)\r\n• Brightness : 400 Nits\r\n• Aspect Ratio : 16:9\r\n• KVM support\r\n• DisplayHDR 400', '1770970150_1.jpg', 4, '2026-02-13 08:09:10'),
(39, 'MSI G32C4X - 31.5 Inch VA FHD 250Hz', '7950.00', 14, '• Color gamut : 114% sRGB, 91% DCI-P3\r\n• Color Support : 1.07 Billion\r\n• Response Time : 1 ms(MPRT)\r\n• Brightness : 300 Nits\r\n• Aspect Ratio : 16:9', '1770970173_1.jpg', 4, '2026-02-13 08:09:33'),
(40, 'Lg Ultragear 27Gs85Q-B - 27 Inch Nano Ips 2K 180Hz-200Hz', '10900.00', 14, '• 27\"\r\n• Nano IPS panel\r\n• 2560 x 1440 180Hz-200Hz (OC) 1ms\r\n• 1.07 billion colors\r\n• 2 x HDMI\r\n• 1 x DP\r\n• AMD FreeSync\r\n• Nvidia G-Sync Compatible\r\n• Height, Pivot, Tilt adjustable\r\n• VESA mount 100 x 100 mm', '1770970205_1.jpg', 4, '2026-02-13 08:10:05'),
(41, 'MICROSOFT XBOX CONTROLLER SERIES WLC (BLACK) (MCS-1V8-00014)', '1790.00', 14, 'Support : Xbox Series X / Xbox Series S / Xbox One / Windows 10 / Android / iOS', '1770970471_1.webp', 5, '2026-02-13 08:14:31'),
(42, 'ASUS ROG RAIKIRI GU200X (BLACK)', '2190.00', 13, '● Intuitive rear controls\r\n● Selectable step triggers\r\n● Premium audio\r\n● Extensive customization', '1770970517_1.jpg', 5, '2026-02-13 08:15:17'),
(43, 'NUBWO NJ025 (RED)', '180.00', 15, 'Support : PC', '1770970551_1.jpg', 5, '2026-02-13 08:15:51'),
(44, 'FANTECH GP-13 SHOOTER II GAMING CONTROLLER (BLACK)', '550.00', 18, '• USB wired Gaming Controller\r\n• Buttons : 19 pcs\r\n• Vibration: Yes\r\n• Cable length 1.8M\r\n• Weight 177 g', '1770970572_1.jpg', 5, '2026-02-13 08:16:12'),
(45, 'FANTECH REVOLVER II WGP12 (WHITE)', '690.00', 21, 'For : PC / PS3', '1770970598_1.jpg', 5, '2026-02-13 08:16:38'),
(46, 'RAZER WOLVERINE V2 PRO PS (BLACK)', '8990.00', 15, '• Razer™ HyperSpeed Wireless\r\n• Razer™ Mecha-Tactile Action Buttons\r\n• 8-Way Microswitch D-Pad', '1770970621_1.jpg', 5, '2026-02-13 08:17:01'),
(47, 'RAZER WOLVERINE V2 PRO PS (WHITE)', '9990.00', 14, '• Razer™ HyperSpeed Wireless\r\n• Razer™ Mecha-Tactile Action Buttons\r\n• 8-Way Microswitch D-Pad', '1770970649_1.jpg', 5, '2026-02-13 08:17:29'),
(48, 'FANTECH EOS PRO MULTI-PLATFORM GAMEPAD (WHITE) (WGP-15)', '1390.00', 14, '• รองรับ PC, PS, Android, IOS, MacOS, TvOS, SWITCH, CLOUD GAMING/GAMEPASS, TESLA VEHICLES\r\n• อุปกรณ์รองรับถึงแค่ ps3', '1770970673_1.jpg', 5, '2026-02-13 08:17:53'),
(49, 'REDRAGON HARROW PRO G808', '990.00', 14, '• PC with Windows XP or higher and Android\r\n• Compatible with Xinput and Dinput for enhanced gaming compatibility', '1770970698_1.jpg', 5, '2026-02-13 08:18:13'),
(50, 'ONIKUMA IRUKA TRI-MODE GAMING WIRELESS (WHITE)', '690.00', 21, '• รองรับ 2.4 GHz / Bluetooth / Type-C Cable\r\n• ปุ่ม Full Hall Trigger ควบคุมแม่นยำยิ่งขึ้น\r\n• มีปุ่ม Macro ด้านหลัง 2 ปุ่ม MR / ML สามารถปรับแต่งได้\r\n• มีระบบสั่นในด้ามจับจอยทั้ง 2 ข้าง ปรับความแรงได้\r\n• รองรับ 6-Axis Gyroscope\r\n• มีเอฟเฟกต์แสงไฟ RGB ปรับได้\r\n• แบบเตอรี่ 800 mAh เล่นได้ประมาณ 16 ชั่วโมง\r\n• มีฟังก์ชัน Turbo Rapid Fire\r\n• รองรับการใช้งาน Switch / iOS / Android / PC Steam', '1770970722_1.jpg', 5, '2026-02-13 08:18:42'),
(51, 'HYTE THICC Q60 (WHITE)', '9990.00', 14, '• Intel Socket LGA 1700, 1200, 115x, 1151, 1150, 1155, 1156, 2011, 2066\r\n• AMD Socket AM5, AM4, TR4\r\n• 5” 720 x 1280 60Hz Ultraslim IPS Display', '1770970785_1.jpg', 6, '2026-02-13 08:19:45'),
(52, 'THERMALRIGHT FROZEN WARFRAME 240 BLACK ARGB', '3990.00', 14, '• Intel Socket LGA 1700, 1200, 115x, 2011, 2066\r\n• AMD Socket AM5, AM4', '1770970810_1.jpg', 6, '2026-02-13 08:20:10'),
(53, 'CORSAIR NAUTILUS 360 RS ARGB (BLACK)', '3890.00', 15, '• Intel Socket LGA 1851, 1700\r\n• AMD Socket AM5, AM4\r\n• Asus Aura Sync, MSI Mystic Light RGB, Asrock Polychrome RGB, Gigabyte RGB Fusion', '1770970843_1.jpg', 6, '2026-02-13 08:20:43'),
(54, 'ASUS PRIME LC 360 ARGB (BLACK)', '3290.00', 13, '• Intel Socket LGA 1851, 1700, 1200, 115x\r\n• AMD Socket AM5, AM4', '1770970874_1.jpg', 6, '2026-02-13 08:21:14'),
(55, 'MSI MAG CORELIQUID A13 240 (BLACK)', '2290.00', 14, '• Intel Socket LGA 1851, 1700\r\n• AMD Socket AM5, AM4\r\n• MSI Mystic Light RGB', '1770970925_1.jpg', 6, '2026-02-13 08:22:05'),
(56, 'CORSAIR NAUTILUS 360 RS (BLACK)', '3590.00', 14, '• Intel Socket LGA 1851, 1700\r\n• AMD Socket AM5, AM4', '1770970953_1.jpg', 6, '2026-02-13 08:22:33'),
(57, 'DEEPCOOL MYSTIQUE 360 (BLACK)', '4990.00', 15, '• Intel Socket LGA 1700, 1200, 1151, 1150, 1155\r\n• AMD Socket AM5, AM4\r\n• Asus Aura Sync, Razer Chroma RGB, Gigabyte RGB Fusion, MSI Mystic Light RGB, Asrock Polychrome RGB', '1770970998_1.jpg', 6, '2026-02-13 08:23:18'),
(58, 'ARCTIC LIQUID FREEZER III 240 (BLACK)', '3190.00', 17, '• Intel Socket LGA 1851, 1700\r\n• AMD Socket AM5, AM4', '1770971027_1.jpg', 6, '2026-02-13 08:23:47'),
(59, 'ID-COOLING DX240 MAX (BLACK)', '1990.00', 14, '• Intel Socket LGA 1851, 1700, 1200, 115x\r\n• AMD Socket AM5, AM4', '1770971057_1.jpg', 6, '2026-02-13 08:24:17'),
(60, 'DEEPCOOL MYSTIQUE 240 (BLACK)', '3990.00', 13, '• Intel Socket LGA 1700, 1200, 1151, 1150, 1155\r\n• AMD Socket AM5, AM4\r\n• Aura Sync, Razer Chroma RGB, RGB Fusion, Mystic Light RGB, Polychrome RGB', '1770971086_1.jpg', 6, '2026-02-13 08:24:46'),
(61, 'INTEL CORE I7-14700K - 20C 28T 2.5-5.6GHz', '12900.00', 15, '• 20 (8P+12E) Cores\r\n• 28 Threads\r\n• CPU cooler NOT included\r\n• Intel UHD graphics 770\r\n• PCIe 5.0 and 4.0\r\n• Compatible with H610, B660, B760, H670, H770, Z690, Z790 chipset', '1770971281_1.jpg', 7, '2026-02-13 08:28:01'),
(62, 'INTEL CORE I3-12100F 3.3 GHz', '3590.00', 13, '• 4 (4P) Cores\r\n• 8 Threads\r\n• Discrete Graphics Required, No Integrated Graphics\r\n• PCIe 5.0 and 4.0', '1770971312_1.jpg', 7, '2026-02-13 08:28:32'),
(63, 'INTEL CORE I5-12400F 2.5 GHz', '5290.00', 15, '• 6 (6P) Cores\r\n• 12 Threads\r\n• Discrete Graphics Required, No Integrated Graphics\r\n• PCIe 5.0 and 4.0', '1770971336_1.jpg', 7, '2026-02-13 08:28:56'),
(64, 'INTEL CORE I5-14400F - 10C 16T 1.8-4.7GHz', '5790.00', 15, '• 10 (6P+4E) Cores\r\n• 16 Threads\r\n• No integrated graphics, discrete graphics required\r\n• PCIe 5.0 and 4.0\r\n• Compatible with H610, B660, B760, H670, H770, Z690, Z790 chipset', '1770971365_1.jpg', 7, '2026-02-13 08:29:25'),
(65, 'INTEL CORE I9-14900K - 24C 32T 2.4-6.0GHz', '17400.00', 15, '• 24 (8P+16E) Cores\r\n• 32 Threads\r\n• CPU cooler NOT included\r\n• Intel UHD graphics 770\r\n• PCIe 5.0 and 4.0\r\n• Compatible with H610, B660, B760, H670, H770, Z690, Z790 chipset', '1770971393_1.webp', 7, '2026-02-13 08:29:53'),
(66, 'AMD RYZEN 7 9800X3D - 8C 16T 4.7-5.2GHz', '17990.00', 13, '• 8 Cores\r\n• 16 Threads\r\n• CPU cooler NOT included\r\n• AMD Radeon Graphics\r\n• PCIe 5.0\r\n• Compatible with A620, B650, B650E, B840, B850, X670, X670E, X870, X870E chipset', '1770971442_1.jpg', 7, '2026-02-13 08:30:30'),
(67, 'AMD RYZEN 5 5600X 3.7 GHz', '4190.00', 21, '• 6 Cores\r\n• 12 Threads\r\n• Discrete Graphics Required, No Integrated Graphics\r\n• PCIe 4.0', '1770971469_1.jpg', 7, '2026-02-13 08:31:09'),
(68, 'AMD RYZEN 7 7800X3D 4.2 GHz', '15190.00', 14, '• 8 Cores\r\n• 16 Threads\r\n• CPU Cooler NOT Included\r\n• AMD Radeon Graphics\r\n• PCIe 5.0\r\n• Compatible with B650, B650E, X670, X670E Chipset', '1770971515_1.jpg', 7, '2026-02-13 08:31:55'),
(69, 'AMD RYZEN 5 5600G 3.9 GHz', '3990.00', 14, '• 6 Cores\r\n• 12 Threads\r\n• AMD Radeon Graphics\r\n• PCIe 3.0', '1770971541_1.jpg', 7, '2026-02-13 08:32:21'),
(70, 'AMD RYZEN 3 3200G 3.6 GHz', '1990.00', 14, '• 4 Cores\r\n• 4 Threads\r\n• AMD Radeon Vega 8 Graphics\r\n• PCIe 3.0', '1770971566_1.jpg', 7, '2026-02-13 08:32:46'),
(71, 'RAZER DEATHADDER ESSENTIAL (BLACK)', '550.00', 14, '• DPI : 6,400\r\n• Button : 5\r\n• Lighting : Green Color\r\n• Connectivity : USB (Wired)', '1771224684_1.jpg', 8, '2026-02-16 06:51:24'),
(72, 'LOGITECH G304 LIGHTSPEED WIRELESS', '990.00', 13, '• 200-12000 DPI\r\n• 6 Button\r\n• Wireless 2.4GHz', '1771224853_1.jpg', 8, '2026-02-16 06:54:13'),
(73, 'HYPERX PULSEFIRE HASTE 2 CORE (WHITE)', '1590.00', 17, '• Up to 12,000 DPI\r\n• 2.4GHz Wireless / Bluetooth 5.2\r\n• HyperX Core Sensor\r\n• TTC Gold Switch\r\n• Durability 20 million clicks\r\n• Speed 300 IPS\r\n• Acceleration 35 G\r\n• Buttons 6', '1771224896_1.jpg', 8, '2026-02-16 06:54:56'),
(74, 'ZOWIE EC3-DW - BLACK', '3990.00', 19, '• DPI : 400, 800, 1000, 1200, 1600, 3200\r\n• การเชื่อมต่อ : USB 2.0, 2.4G ไร้สาย\r\n• ปุ่มที่ตั้งค่าได้ : 7 ปุ่ม\r\n• อัตราการส่งข้อมูล : สูงสุด 4000Hz', '1771224948_1.jpg', 8, '2026-02-16 06:55:48'),
(75, 'LOGITECH G PRO X SUPERLIGHT 2 DEX (PINK)', '3690.00', 21, '• Up to 100 - 32,000 DPI\r\n• 2.4GHz Wireless / USB-C\r\n• Sensor HERO\r\n• Max Speed 500 IPS\r\n• Max Acceleration 40 G\r\n• Programmable Buttons 5', '1771225006_1.jpg', 8, '2026-02-16 06:56:46'),
(76, 'LOGITECH G304 LIGHTSPEED WIRELESS (WHITE)', '990.00', 15, '• 200-12000 DPI\r\n• 6 Button\r\n• Wireless 2.4GHz', '1771225052_1.jpg', 8, '2026-02-16 06:57:32'),
(77, 'HYPERX PULSEFIRE HASTE 2 (WHITE)', '1150.00', 14, '• Up to 26000 DPI\r\n• HyperX Switch up to 100 million clicking life\r\n• Precision HyperX 26K sensor\r\n• Ultra-lightweight 53g design', '1771225547_1.jpg', 8, '2026-02-16 07:05:47'),
(78, 'ULA SC680 3IN1 GAMING MODE (BLACK)', '1150.00', 15, '• Up to 26,000 DPI\r\n• PAW3395\r\n• Type-C / Wireless 2.4G / Bluetooth\r\n• Battery 500mAh\r\n• 58 Grams', '1771225642_1.jpg', 8, '2026-02-16 07:07:22'),
(79, 'RAZER COBRA ZENLESSS ZONE ZERO EDITION - BLACK', '2190.00', 12, '• DPI : 8,500 DPI\r\n• เซ็นเซอร์ : Optical Sensor\r\n• การเชื่อมต่อ : แบบมีสาย - สายเคเบิล Razer Speedflex\r\n• ปุ่มที่ตั้งค่าได้ : 6 ปุ่ม\r\n• ความเร็วสูงสุด : 300 IPS\r\n• ไฟ : Razer Chroma RGB', '1771225827_1.jpg', 8, '2026-02-16 07:10:27'),
(80, 'HYPERX PULSEFIRE SAGA - BLACK', '2390.00', 23, '• DPI : สูงสุด 26,000\r\n• เซนเซอร์ : HyperX 26K Sensor\r\n• การเชื่อมต่อ : สาย USB Type-A\r\n• ปุ่มที่ตั้งค่าได้ : 6 ปุ่ม\r\n• อัตราการส่งข้อมูล : สูงสุด 8,000Hz\r\n• ความเร็วสูงสุด : 650 IPS\r\n• รองรับระบบปฏิบัติการ : PC, Xbox Series X|S, PS5\r\n• ไฟ : RGB', '1771225901_1.jpg', 8, '2026-02-16 07:11:41'),
(81, 'HYPERX GAMING GEAR CLOUD ALPHA', '3190.00', 21, ' HyperX Dual Chamber Drivers ช่วยให้การแยกเสียงเด่นชัดมากขึ้น การเพี้ยนเสียงลดลง\r\n• มาตรฐานความสบายในการสวมใส่ตามสไตล์ HyperX\r\n• โครงอะลูมิเนียมทนทาน พร้อมแถบคาดศีรษะแบบยืดออกได้\r\n• สายเชื่อมต่อแบบถักถอดแยกได้ พร้อมระบบควบคุมเสียง in-line ที่สะดวกสบาย\r\n• ไมโครโฟนตัดเสียงรบกวนถอดแยกได้\r\n• รับรองมาตรฐานโดย Discord และ TeamSpeak™\r\n• รองรับการทำงานกับอุปกรณ์หลากหลายรูปแบบ', '1771226387_1.jpg', 9, '2026-02-16 07:19:47'),
(82, 'LOGITECH PRO X GAMING HEADSET WITH BLUE VOICE', '3690.00', 24, '• Headset Response : 20 Hz - 20000 Hz\r\n• Mic Response : 100 Hz - 10000 Hz', '1771226510_1.jpg', 9, '2026-02-16 07:21:50'),
(83, 'FANTECH HG29 (BLACK)', '490.00', 13, '• USB 2.0 Wired connection\r\n• Length 2M', '1771226779_1.jpg', 9, '2026-02-16 07:26:19'),
(84, 'FANTECH WHG02 (BLACK)', '1490.00', 15, '• Bluetooth / 2.4GHz Wireless / Wired connection\r\n• Battery 1050 mAh', '1771227697_1.jpg', 9, '2026-02-16 07:41:37'),
(85, 'ONIKUMA X31 RGB 3.5MM - BLACK', '459.00', 12, '• ไดร์เวอร์ขนาด 50 มม.\r\n• เอฟเฟกต์แสงไฟ RGB ที่โดดเด่น\r\n• ให้เสียงที่คมชัดรอบทิศทาง\r\n• ไมโครโฟน HD เสียงคมชัด\r\n• ไมโครโฟนปรับได้ 360 องศา\r\n• ฟองน้ำนุ่ม ถ่ายเทอากาศ สวมใส่สบาย\r\n• เชื่อมต่อด้วย USB + 3.5mm', '1771228248_1.jpg', 9, '2026-02-16 07:50:48'),
(86, 'EGA TYPE H17 - WHITE', '690.00', 23, '• การเชื่อมต่อ Bluetooth 5.4 / 2.4GHz Wireless / AUX 3.5mm\r\n• ไฟ LED Lighting\r\n• ไมโครโฟนยืดหยุ่น ปรับได้ตามการใช้งาน\r\n• แผงควบคุมบนตัวหูฟัง (เปิด/ปิด, เพิ่ม/ลดเสียง)\r\n• ระยะส่งสัญญาณสูงสุด 10 เมตร\r\n• แบตเตอรี่ในตัว ขนาด 600mAh\r\n• มีอุปกรณ์เสริม สายชาร์จ USB-to-Type-C / อะแดปเตอร์ OTG สำหรับใช้งานร่วมกับโทรศัพท์', '1771228372_1.jpg', 9, '2026-02-16 07:52:52'),
(87, 'FANTECH HG30 CARBON 7.1 - WHITE', '890.00', 12, '• เชื่อมต่อผ่านสาย USB-A\r\n• ไดรเวอร์ขนาด 40 มม. ปรับจูนพิเศษ ให้คุณภาพเสียงยอดเยี่ยม\r\n• ระบบเสียงรอบทิศทางเสมือนจริงแบบ 7.1\r\n• ไมโครโฟนตัดเสียงรบกวน\r\n• รองรับการใช้งานหลากหลายแพลตฟอร์ม MAC, PC, PS4, PS5 และ Nintendo Switch', '1771228898_1.jpg', 9, '2026-02-16 08:01:38'),
(88, 'HYPERX CLOUD FLIGHT 2 - WHITE', '4790.00', 21, '• เชื่อมต่อไร้สายผ่าน 2.4GHz, Bluetooth และระบบ Instant Pair\r\n• ไฟ RGB ปรับแต่งได้ พร้อมแผ่นตกแต่งถอดเปลี่ยนได้\r\n• แบตเตอรี่ใช้งานได้นานสูงสุด 100 ชั่วโมง\r\n• ออกแบบให้สวมใส่สบาย มีไมโครโฟนให้เลือก 2 แบบ\r\n• รองรับการใช้งานกับ PC, PS5, PS4, Nintendo Switch, Mac และมือถือ; รองรับซอฟต์แวร์ NGENUITY', '1771229112_1.jpg', 9, '2026-02-16 08:05:12'),
(89, 'RAZER BLACKSHARK V3 PRO - WHITE', '8490.00', 21, '• ไดรเวอร์ Razer TriForce Bio-Cellulose 50 มม. Gen-2 ให้เสียงคมชัดและสมจริง\r\n• ระบบ Hybrid ANC ตัดเสียงรบกวนรอบข้างได้อย่างมีประสิทธิภาพ\r\n• รองรับการเชื่อมต่อ 2.4GHz / Bluetooth / USB / 3.5 มม.\r\n• ไมโครโฟนถอดได้ HyperClear Full Band 12 มม. ให้เสียงคมชัด\r\n• แบตเตอรี่ใช้งานได้สูงสุด 70 ชม.\r\n• รองรับ THX Spatial Audio เพื่อประสบการณ์เสียงรอบทิศทาง', '1771229688_1.jpg', 9, '2026-02-16 08:14:48'),
(90, 'AULA G7 - WHITE', '890.00', 14, '• รองรับการเชื่อมต่อ 3 โหมด แจ็ค 3.5 มม. / ไร้สาย 2.4 GHz / บลูทูธ\r\n• ค่าหน่วงต่ำเพียง 10ms เหมาะสำหรับการเล่นเกมส์แบบเรียลไทม์\r\n• ไดรเวอร์ขนาด 40 มม. ให้เสียงคมชัด ทรงพลัง\r\n• ไมโครโฟนถอดได้ พร้อมฟังก์ชั่นตัดเสียงรบกวน\r\n• แบตเตอรี่ใช้งานยาวนานสูงสุด 60 ชั่วโมง\r\n• ดีไซน์สวมใส่สบาย ระบายอากาศดี ไม่อับร้อน', '1771229743_1.jpg', 9, '2026-02-16 08:15:43'),
(91, 'FANTECH SONAR GS202 MOBILE GAMING & MUSIC SPEAKER (BLACK)', '300.00', 23, '• Portable USB2.0 Speaker\r\n• 45MM Driver Unit\r\n• RGB Illumination', '1771232416_1.jpg', 10, '2026-02-16 09:00:16'),
(92, 'CREATIVE STAGE AIR V2 - BLACK', '1690.00', 21, '2 x 5W, Total System Peak power: 20W', '1771232515_1.jpg', 10, '2026-02-16 09:01:55'),
(93, 'JBL FLIP 6 (BLACK)', '5190.00', 12, '20 Watt', '1771232618_1.jpg', 10, '2026-02-16 09:03:38'),
(94, 'FANTECH ARTHAS GS733 GAMING BLACK', '250.00', 12, '• 8 Watt RMS\r\n• 2.0 Channel', '1771232721_1.jpg', 10, '2026-02-16 09:05:21'),
(95, 'SIGNO E-SPORT ENRIKO SB-610 RGB', '650.00', 6, '10 Watt RMS', '1771232868_1.jpg', 10, '2026-02-16 09:07:48'),
(96, 'FANTECH BEAT GS203 MOBILE GAMING & MUSIC SPEAKER (WHITE)', '270.00', 16, '• Portable Speaker (USB2.0 Power + 3.5mm Audio)\r\n• Bass Resonance Membrane\r\n• 45MM Driver Unit\r\n• RGB Illumination', '1771232934_1.jpg', 10, '2026-02-16 09:07:49'),
(97, 'NUBWO LAURIMUS NS047 RGB 2.0 BLACK', '240.00', 14, '• RGB Gaming speaker\r\n• R.M.S. 3W x 2CH\r\n• Frequency Response 60Hz – 20KHz\r\n• Driver Unit 2.5″x 2 full range\r\n• RGB led light Control Modes Switchable\r\n• Impedance 30hm\r\n• USB + 3.5 jack and volume controller', '1771232983_1.jpg', 10, '2026-02-16 09:09:43'),
(98, 'JBL GO 4 (RED) (JBLGO4RED)', '1790.00', 12, '• บลูทูธ 5.3\r\n• กันน้ำและกันฝุ่น\r\n• ควบคุมทุกอย่างด้วยแอปฯ JBL Portable\r\n• เชื่อมต่อ ลำโพงหลายตัว ด้วยเทคโนโลยี Auracast\r\n• ใส่ใจสิ่งแวดล้อม ผลิตจากวัสดุรีไซเคิล', '1771233035_1.jpg', 10, '2026-02-16 09:10:35'),
(99, 'SIGNO E-SPORT SAGGIO SP-614 LED BLACK', '199.00', 14, '2x 3 Watt', '1771233182_1.jpg', 10, '2026-02-16 09:12:50'),
(100, 'MICROLAB B51 (BLACK)', '420.00', 15, '• ดีไซน์เพรียวบางและทันสมัยแนวคิดที่ไม่รบกวนสายตา\r\n• ลำโพงสเตอริโอสำหรับโน้ตบุ๊กและแท็บเล็ต\r\n• พกพาสะดวกและกะทัดรัด พกพาสะดวก\r\n• ไดรเวอร์ลำโพงขนาด 1.5 นิ้ว ออกแบบมาเพื่อความละเอียดเต็มช่วง\r\n• มีระบบป้องกันแม่เหล็กเพื่อการวางตำแหน่งที่ปราศจากการรบกวน\r\n• แผงควบคุมด้านบนเพื่อการเข้าถึงที่ง่ายดาย เปิดเครื่อง ไฟ LED และปุ่มปรับระดับเสียง\r\n• ใช้พลังงานจาก USB ไม่ต้องใช้แบตเตอรี่\r\n• เชื่อมต่อกับแจ็คสเตอริโอขนาด 3.5 มม.', '1771233310_1.jpg', 10, '2026-02-16 09:15:10'),
(101, 'SYNDOME TE 1000', '11500.00', 12, 'Output Capacity : 1000 VA / 900 Watt', '1771235461_1.jpg', 11, '2026-02-16 09:51:01'),
(102, 'APC BR500CI-AS', '3250.00', 21, 'OUTPUT CAPACITY : 500 VA / 300 WATT', '1771235512_1.jpg', 11, '2026-02-16 09:51:52'),
(103, 'APC EASY UPS 1200VA', '4500.00', 13, 'Output Capacity : 1200 VA / 650 Watt', '1771235562_1.jpg', 11, '2026-02-16 09:52:42'),
(104, 'SYNDOME TE-3000', '28900.00', 8, 'Output Capacity : 3000 VA / 2700 Watt', '1771235620_1.jpg', 11, '2026-02-16 09:53:40'),
(105, 'APC BACK-UPS 625VA', '2990.00', 21, 'Output Capacity : 625 VA / 325 Watt', '1771235730_1.jpg', 11, '2026-02-16 09:55:30'),
(106, 'APC BACK-UPS, 2200VA', '11230.00', 12, 'Output Capacity : 2200 VA / 1200 Watt', '1771235844_1.jpg', 11, '2026-02-16 09:57:24'),
(107, 'APC EASY UPS 700VA', '1830.00', 14, 'Output Capacity : 700 VA / 360 Watt', '1771235924_1.jpg', 11, '2026-02-16 09:58:44'),
(108, 'SYNDOME ATOM 1000-LCD', '4390.00', 17, 'Output Capacity : 1000 VA / 600 Watt', '1771235980_1.jpg', 11, '2026-02-16 09:59:40'),
(109, 'SYNDOME ECO II-1200-LCD', '3800.00', 13, 'Output Capacity : 1200 VA / 720 Watt', '1771236038_1.jpg', 11, '2026-02-16 10:00:38'),
(110, 'APC EASY UPS ON-LINE (SRV1KI-E)', '13530.00', 14, '• High Quality, Double-conversion On-Line UPS\r\n• 3 Min Run Time for 900 W Load (Approx.)', '1771236101_1.jpg', 11, '2026-02-16 10:01:41'),
(111, 'ASUS TUF GAMING GEFORCE RTX 5080 16GB GDDR7 OC EDITION', '56900.00', 12, '• GeForce RTX 5080\r\n• 16GB GDDR7\r\n• 3 x DisplayPort\r\n• 2 x HDMI', '1771241393_1.jpg', 12, '2026-02-16 11:29:53'),
(112, 'GIGABYTE GEFORCE RTX 5070 TI GAMING OC 16G GDDR7', '34900.00', 21, '• กราฟิกส์เอนจิน : GeForce RTX 5070 Ti\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1771241517_1.jpg', 12, '2026-02-16 11:31:57'),
(113, 'MSI GEFORCE RTX 5070 TI 16G VENTUS 3X OC GDDR7', '32900.00', 12, '• กราฟิกส์เอนจิน : GeForce RTX 5070 Ti\r\n• หน่วยความจำ : 16GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1771241586_1.jpg', 12, '2026-02-16 11:33:06'),
(114, 'ASUS PRIME GEFORCE RTX 5060 8GB GDDR7 OC EDITION', '14900.00', 16, '• กราฟิกส์เอนจิน : GeForce RTX 5060\r\n• หน่วยความจำ : 8GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1771241702_1.jpg', 12, '2026-02-16 11:35:02'),
(115, 'ASUS DUAL GEFORCE RTX 5060 8GB GDDR7 OC EDITION', '12900.00', 12, '• กราฟิกส์เอนจิน : GeForce RTX 5060\r\n• หน่วยความจำ : 8GB GDDR7\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1771242214_1.jpg', 12, '2026-02-16 11:43:34'),
(116, 'ASROCK AMD RADEON RX 9070 XT TAICHI 16GB OC GDDR6', '29900.00', 11, '• AMD Radeon RX 9070 XT GPU\r\n• หน่วยความจำ 16GB GDDR6 256 บิต\r\n• 64 หน่วยคำนวณ AMD RDNA (พร้อมตัวเร่ง RT+AI)\r\n• รองรับ PCI Express 5.0\r\n• ตัวเชื่อมต่อพลังงาน 12V-2x6-pin\r\n• 3 x DisplayPort 2.1a / 1 x HDMI 2.1b', '1771242301_1.jpg', 12, '2026-02-16 11:45:01'),
(117, 'ASROCK AMD RADEON RX 9060 XT CHALLENGER 16GB OC GDDR6', '13500.00', 7, '• กราฟิกส์เอนจิน : Radeon RX 9060 XT\r\n• หน่วยความจำ : 16GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 2 x DisplayPort, 1 x HDMI', '1771242370_1.jpg', 12, '2026-02-16 11:46:10'),
(118, 'POWERCOLOR HELLHOUND AMD RADEON RX 7800 XT 16GB GDDR6', '18900.00', 16, '• Radeon RX 7800 XT\r\n• 16GB GDDR6\r\n• 3 x DisplayPort*\r\n• 1 x HDMI\r\n• Amethyst Purple & Ice Blue LED Lighting\r\n*Only 2x simultaneous DP2.1 connections can be supported', '1771242508_1.jpg', 12, '2026-02-16 11:48:28'),
(119, 'POWERCOLOR FIGHTER AMD RADEON RX 7600 8GB GDDR6', '8990.00', 16, '• Radeon RX 7600\r\n• 8GB GDDR6\r\n• 3 x DisplayPort\r\n• 1 x HDMI', '1771242612_1.jpg', 12, '2026-02-16 11:50:12'),
(120, 'ASUS PRIME RADEON RX 9070 XT OC EDITION 16GB GDDR6', '28500.00', 18, '• กราฟิกส์เอนจิน : Radeon RX 9070 XT\r\n• หน่วยความจำ : 16GB GDDR6\r\n• คอนเน็กเตอร์สำหรับจอภาพ : 3 x DisplayPort, 1 x HDMI', '1771242661_1.jpg', 12, '2026-02-16 11:51:01'),
(121, 'LOGITECH C922 PRO HD STREAM WEBCAM', '3290.00', 21, '• Full HD 1080p/30fps HD 720p/60fps\r\n• โฟกัสอัตโนมัติ\r\n• ไมค์คู่ระบบเสียงสเตอริโอ\r\n• ขาตั้ง\r\n• การเชื่อมต่อแบบ USB', '1771242936_1.jpg', 13, '2026-02-16 11:55:36'),
(122, 'LOGITECH BRIO 4K HD RIGHT LIGHT', '6900.00', 12, '4K Ultra HD 1080p Full HD', '1771242997_1.jpg', 13, '2026-02-16 11:56:37'),
(123, 'LOGITECH QCAM & MONO C270', '650.00', 12, '3 MP HD', '1771243080_1.jpg', 13, '2026-02-16 11:58:00'),
(124, 'AVERMEDIA PW313 LIVE STREAMER CAM', '1690.00', 12, '1080p FHD video recording', '1771243164_1.jpg', 13, '2026-02-16 11:59:24'),
(125, 'NUBWO NWC-500 (2K)', '490.00', 16, '• 5 Megapixel\r\n• 2K (2560 x 1440) AF 30F/S\r\n• Auto focus\r\n• Dual microphone\r\n• Privacy cover\r\n• Micro USB 2.0 Connection\r\n• Support Android / Xbox one / Windows / Linux / MacOS / Ubuntu', '1771243222_1.jpg', 13, '2026-02-16 12:00:22'),
(126, 'LOGITECH C930E BUSINESS WEBCAM', '4190.00', 16, 'Designed for business, a 1080p webcam with wide field of view and digital zoom.', '1771243363_1.jpg', 13, '2026-02-16 12:02:43'),
(127, 'RAPOO C260 FULL HD', '990.00', 17, 'Full HD 1080P', '1771243436_1.jpg', 13, '2026-02-16 12:03:56'),
(128, 'LOGITECH BRIO100', '990.00', 12, 'เว็บแคม Full HD 1080p พร้อมระบบปรับสมดุลแสงอัตโนมัติ, ฉากปิดเลนส์ในตัวเพื่อความเป็นส่วนตัว และไมค์ในตัว', '1771243527_1.jpg', 13, '2026-02-16 12:05:27'),
(129, 'SIGNO WB-400 ZOOMER', '1150.00', 17, '4 MP 2K/30fps', '1771243613_1.jpg', 13, '2026-02-16 12:06:53'),
(130, 'AVERMEDIA LIVE STREAMER CAM 513', '5290.00', 12, '• Record 4K UltraHD video at 30 fps.\r\n• Built in privacy shutter.\r\n• Plug and Play.', '1771243674_1.png', 13, '2026-02-16 12:07:54'),
(131, ' KINGSTON FURY BEAST 16GB (8GBx2) DDR4 3200MHz', '4690.00', 16, '• 16GB (8GBx2)\r\n• DDR4\r\n• 3200MHz\r\n• KF432C16BBK2/16', '1771243846_1.jpg', 14, '2026-02-16 12:10:46'),
(132, 'KINGSTON FURY BEAST 32GB (16GBx2) DDR5 5200MHz', '12900.00', 11, '• 32GB (16GBx2)\r\n• DDR5\r\n• 5200 MHz\r\n• KF552C40BBK2-32', '1771243925_1.jpg', 14, '2026-02-16 12:12:05'),
(133, 'G.SKILL RIPJAWS V 16GB (8GBx2) DDR4 3200MHz', '3990.00', 16, '• 16GB (8GBx2)\r\n• DDR4\r\n• 3200MHz\r\n• F4-3200C16D-16GVKB', '1771243994_1.jpg', 14, '2026-02-16 12:13:14'),
(134, 'G.SKILL TRIDENT Z NEO 16GB (8GBx2) DDR4 3200MHz', '3990.00', 8, '• 16GB (8GBx2)\r\n• DDR4\r\n• 3200MHz\r\n• F4-3200C16D-16GTZN\r\n• RGB Lighting', '1771244084_1.jpg', 14, '2026-02-16 12:14:44'),
(135, 'CORSAIR VENGEANCE 32GB (16GBx2) DDR5 5200MHz', '12400.00', 17, '• 32GB (16GBx2)\r\n• DDR5\r\n• 5200MHz\r\n• CL40\r\n• Intel XMP\r\n• CMK32GX5M2B5200C40', '1771244139_1.jpg', 14, '2026-02-16 12:15:39'),
(136, 'CORSAIR VENGEANCE LPX 16GB (8GBx2) DDR4 3200MHz', '4350.00', 16, '• 16GB (8GBx2)\r\n• DDR4\r\n• 3200MHz\r\n• CMK16GX4M2B3200C16W', '1771244270_1.jpg', 14, '2026-02-16 12:17:50'),
(137, 'CORSAIR VENGEANCE RGB 32GB (16GBx2) DDR5 6000MHz', '15900.00', 15, '• 32GB (16GBx2)\r\n• DDR5\r\n• 6000MHz\r\n• CL36\r\n• Intel XMP\r\n• CMH32GX5M2E6000C36', '1771244345_1.jpg', 14, '2026-02-16 12:19:05'),
(138, 'G.SKILL TRIDENT Z RGB 32GB (16GBx2) DDR4 3200MHz', '9990.00', 15, '• 32GB (16GBx2)\r\n• DDR4\r\n• 3200MHz\r\n• F4-3200C16D-32GTZR', '1771244433_1.jpg', 14, '2026-02-16 12:20:34'),
(139, 'KINGSTON FURY BEAST 16GB (8GBx2) DDR5 5200MHz', '5990.00', 15, '• 16GB (8GBx2)\r\n• DDR5\r\n• 5200MHz\r\n• CL40\r\n• KF552C40BBAK2-16', '1771244545_1.jpg', 14, '2026-02-16 12:22:25'),
(140, ' KINGSTON FURY RENEGADE 32GB (16GBx2) DDR5 6400MHz', '13400.00', 13, '• 32GB (16GBx2)\r\n• DDR5\r\n• 6400MHz\r\n• CL32\r\n• KF564C32RSAK2-32', '1771244654_1.jpg', 14, '2026-02-16 12:24:14'),
(141, 'SSD SAMSUNG 980 PRO 1 TB PCIe 4x4/NVMe M.2 2280 ', '3290.00', 7, '• 1 TB\r\n• Sequential Read (up to) 7,000 MB/s\r\n• Sequential Write (up to) 5,000 MB/s\r\n• PCIe Gen 4 x 4', '1771245521_1.jpg', 15, '2026-02-16 12:38:41'),
(142, 'SSD WD BLACK SN770 500 GB PCIe 4x4/NVMe M.2 2280', '1290.00', 12, '• 500 GB\r\n• Sequential Read (up to) 5,000 MB/s\r\n• Sequential Write (up to) 4,000 MB/s\r\n• PCIe Gen 4 x 4', '1771245802_1.jpg', 15, '2026-02-16 12:43:22'),
(143, 'SSD HIKSEMI FUTURE 1 TB PCIe 4x4/NVMe M.2 2280', '5450.00', 17, '• 1 TB\r\n• Up to 7,450 MB/s Read\r\n• Up to 6,600 MB/s Write\r\n• PCIe Gen 4 x 4', '1771245911_1.jpg', 15, '2026-02-16 12:45:11'),
(144, 'SSD KINGSTON NV2 500 GB PCIe 4/NVMe M.2 2280', '1290.00', 14, '• 500 GB\r\n• Up to 3,500 MB/s Read\r\n• Up to 2,100 MB/s Write\r\n• PCIe 4.0', '1771246021_1.jpg', 15, '2026-02-16 12:47:01'),
(145, 'SSD KINGSTON FURY RENEGADE 1 TB  PCIe 4x4/NVMe M.2 2280 (SFYRS/1000G)', '3990.00', 14, '• 1 TB\r\n• Sequential Read (up to) 7,300 MB/s\r\n• Sequential Write (up to) 6,000 MB/s\r\n• PCIe Gen 4 x 4', '1771246155_1.jpg', 15, '2026-02-16 12:49:15'),
(146, 'SSD WD BLUE SA510 1 TB 2.5 INCH SATA3', '4150.00', 16, '• 1 TB\r\n• Up to 560 MB/s Read Speed\r\n• Up to 520 MB/s Write Speed\r\n• SATA 3 (6Gb/s)', '1771246217_1.jpg', 15, '2026-02-16 12:50:17'),
(147, 'SSD SAMSUNG 870 EVO 2 TB 2.5 INCH SATA3', '8490.00', 7, '• 2 TB\r\n• Up to 560 MB/s Sequential Read Speed\r\n• Up to 530 MB/s Sequential Write Speed', '1771246405_1.jpg', 15, '2026-02-16 12:53:25'),
(148, 'HDD WD BLUE 4 TB 5400RPM SATA3', '4290.00', 7, '• 4 TB\r\n• 256 MB Cache\r\n• 5400 RPM\r\n• SATA 3\r\n• Desktop Hard Drive\r\n• CMR Technology', '1771246545_1.jpg', 15, '2026-02-16 12:55:45'),
(149, 'HDD SEAGATE IRONWOLF 12 TB 7200RPM SATA3', '12100.00', 14, '• 12 TB\r\n• 256 MB Cache\r\n• 7200 RPM\r\n• SATA 3\r\n• NAS Hard Drive', '1771246650_1.jpg', 15, '2026-02-16 12:57:30'),
(150, 'HDD SEAGATE IRONWOLF 4 TB 5400RPM SATA3', '3890.00', 7, '• 4 TB\r\n• 256 MB Cache\r\n• 5400 RPM\r\n• SATA 3\r\n• NAS Hard Drive\r\n• CMR Technology', '1771246793_1.jpg', 15, '2026-02-16 12:59:53'),
(151, 'ASROCK B450M STEEL LEGEND - AMD SOCKET AM4 DDR4 MICRO-ATX', '2790.00', 14, '• Socket AM4 for AMD Ryzen 2000, 3000, 4000 G, 5000 & 5000 G-Series Processors\r\n• AMD B450\r\n• DDR4, 4 x DIMM\r\n• 1 x DP\r\n• 1 x HDMI\r\n• 2 x M.2 Sockets\r\n• 4 x SATA3 Connectors\r\n• 1 x USB 3.2 Gen1 Type-A Header (front)\r\n• 1 x USB 3.2 Gen2 Type-C (rear)\r\n• 1 x USB 3.2 Gen2 Type-A (rear)\r\n• 1 x PS/2\r\n• 1 Gigabit LAN', '1771255671_1.jpg', 16, '2026-02-16 15:27:51'),
(152, 'MSI PRO B760M-P - INTEL SOCKET 1700 DDR5 MICRO-ATX', '2390.00', 8, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• 4 DIMMs, DDR 5\r\n• 1 x PCIe 4.0 x 16 slot\r\n• 1 x HDMI, 1 x DisplayPort, 1 x D-Sub (VGA)\r\n• 2 x M.2 Gen 4 x 4 slots & 4 x SATA 3\r\n• 1 x USB 3.2 Gen 1 Type-A front panel connector\r\n• 1 x USB 3.2 Gen 2 Type-C\r\n• 1 Gigabit LAN', '1771255813_1.jpg', 16, '2026-02-16 15:30:13'),
(153, 'ASUS PRIME B760M-A WIFI (DDR5) (SOCKET LGA 1700) (MICRO-ATX)', '4090.00', 9, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• CPU with base power greater than 125W is not supported\r\n• 4 x DIMM, DDR5\r\n• 1 x DisplayPort\r\n• 2 x HDMI\r\n• 2 x M.2 slots & 4x SATA 3\r\n• 1 x USB 3.2 Gen 1 Type-C front panel connector\r\n• 2 x USB 3.2 Gen 2 Type-A\r\n• 1 x PS/2\r\n• 2.5Gb Ethernet, Wi-Fi 6, Bluetooth v5.2\r\n• Aura Sync RGB header', '1771255891_1.jpg', 16, '2026-02-16 15:31:31'),
(154, 'MSI B760M GAMING PLUS WIFI - INTEL SOCKET 1700 DDR5 MICRO-ATX', '3860.00', 6, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• Intel B760\r\n• DDR5, 4 x DIMM\r\n• 2 x DP\r\n• 2 x HDMI\r\n• 2 x M.2 slots\r\n• 4 x SATA 6Gb/s ports\r\n• 1 x USB 3.2 Gen 1 Type-C connector (front)\r\n• 2 x USB 3.2 Gen 2 Type-A (rear)\r\n• 2 x USB 3.2 Gen 1 Type-A (rear)\r\n• 1 x PS/2\r\n• 2.5Gbps LAN\r\n• Intel Wi-Fi 6E\r\n• Bluetooth 5.3', '1771255950_1.jpg', 16, '2026-02-16 15:32:30'),
(155, 'ASUS TUF GAMING B760M-PLUS WIFI II - INTEL SOCKET 1700 DDR5 MICRO-ATX', '4950.00', 16, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• Intel B760\r\n• DDR5, 4 x DIMM\r\n• 1 x DP\r\n• 1 x HDMI\r\n• 3 x M.2 slots\r\n• 4 x SATA 6Gb/s ports\r\n• 1 x USB Type-C 10Gbps connector (front)\r\n• 1 x USB Type-C 20Gbps (rear)\r\n• 1 x USB Type-A 10Gbps (rear)\r\n• 2.5Gb Ethernet\r\n• Wi-Fi 6E\r\n• Bluetooth v5.3', '1771256131_1.jpg', 16, '2026-02-16 15:35:31'),
(156, 'GIGABYTE B760M GAMING X DDR4 (REV. 1.0) (SOCKET LGA 1700) (MICRO-ATX)', '4190.00', 15, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• DDR4 4 DIMMs\r\n• 2 PCIe 4.0 x4 M.2 Connectors\r\n• 2.5GbE LAN\r\n• Front USB-C 10Gb/s, DP, HDMI', '1771256270_1.jpg', 16, '2026-02-16 15:37:50'),
(157, 'ASROCK B550M STEEL LEGEND - SOCKET AM4 DDR4 MICRO-ATX', '3890.00', 14, '• Supports AMD AM4 Socket Ryzen 3000, 3000 G, 4000 G, 5000 & 5000 G-Series Processors*\r\n• AMD B550\r\n• DDR4, 4 x DIMM\r\n• 1 x DisplayPort\r\n• 1 x HDMI\r\n• 2 x M.2 socket\r\n• 6 x SATA 3 connectors\r\n• 2 x USB 3.2 Gen2 (Rear Type A+C)\r\n• 8 x USB 3.2 Gen1 (4 Front, 4 Rear)\r\n• AMD CrossFireX\r\n• Dragon 2.5G LAN\r\n• *Not compatible with AMD Athlon Processors.', '1771256465_1.jpg', 16, '2026-02-16 15:41:05'),
(158, 'ASROCK B760M PG LIGHTNING - INTEL SOCKET 1700 DDR5 MICRO-ATX', '2690.00', 12, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• Intel B760\r\n• DDR5, 4 x DIMM\r\n• 1 x PCIe 5.0 x 16\r\n• 1 x HDMI\r\n• 1 x DisplayPort\r\n• 4 x SATA3\r\n• 3 x Hyper M.2 (PCIe Gen4x4)\r\n• 1 x USB 3.2 Gen1 Type-C (Rear)\r\n• 1 x USB 3.2 Gen1 Type-A Header\r\n• Dragon 2.5G LAN', '1771256689_1.jpg', 16, '2026-02-16 15:44:49'),
(159, 'GIGABYTE B760M GAMING X (REV. 1.0) - INTEL SOCKET 1700 DDR5 MICRO-ATX', '4190.00', 12, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• DDR5 4 DIMMs\r\n• 2 x PCIe 4.0 x4 M.2 Connectors\r\n• 2.5GbE LAN\r\n• Front USB-C 10Gb/s, DP, HDMI', '1771256894_1.jpg', 16, '2026-02-16 15:48:14'),
(160, 'ASROCK B760M STEEL LEGEND WIFI - INTEL SOCKET 1700 DDR5 MICRO-ATX', '4990.00', 14, '• Socket LGA1700 for Intel 12th, 13th & 14th Gen Processors\r\n• DDR5\r\n• 1 PCIe 5.0 x16\r\n• HDMI, DisplayPort, eDP\r\n• 3 Hyper M.2 (PCIe Gen4x4)\r\n• USB 3.2 Gen2 Type-C\r\n• 2.5G LAN, WiFi 6E + Bluetooth 5.3', '1771257055_1.jpg', 16, '2026-02-16 15:50:55'),
(161, 'ANTEC ATOM V550 - 550W BLACK ATX', '990.00', 15, '• 550 Watt\r\n• NON Modular\r\n• ATX', '1771257519_1.jpg', 17, '2026-02-16 15:58:39'),
(162, 'AEROCOOL AE-650W - 650W 80 PLUS BLACK ATX', '1390.00', 25, '• 650 Watt\r\n• 80 Plus\r\n• NON Modular\r\n• ATX', '1771257651_1.jpg', 17, '2026-02-16 16:00:51'),
(163, 'THERMALTAKE SMART BX1 750W - 750W 80 PLUS BRONZE (BLACK) (ATX)', '1990.00', 12, '• 750 Watt\r\n• 80 Plus Bronze', '1771257716_1.jpg', 17, '2026-02-16 16:01:56'),
(164, 'SILVERSTONE STRIDER ESSENTIAL 500W 80 PLUS BLACK ATX (SST-ST50F-ES230)', '1390.00', 17, '• 500 Watt\r\n• 80 Plus', '1771257786_1.jpg', 17, '2026-02-16 16:03:06'),
(165, 'ASUS TUF GAMING 650B - 650W 80 PLUS BRONZE (BLACK) (ATX)', '2490.00', 12, '• Capacitors and chokes pass demanding tests to achieve Military-grade Certification.\r\n• Dual ball fan bearings can last up to twice as long as sleeve bearing designs.\r\n• A protective PCB coating protects against moisture, dust, and extreme temperatures.\r\n• An 80 Plus Bronze Certification is earned with high-quality components that pass rigorous testing.\r\n• Axial-tech fan design features a smaller fan hub that facilitates longer blades and a barrier ring that increases downward air pressure.\r\n• 0dB technology lets you enjoy light gaming in relative silence.\r\n• Sleeved cables leave your rig looking tactically clean.\r\n• 80cm 8-pin CPU connector (EPS 12V)', '1771257924_1.jpg', 17, '2026-02-16 16:05:24'),
(166, 'ASUS ROG-THOR-1600T-GAMING - 1600W 80 PLUS TITANIUM BLACK-SILVER ATX', '21900.00', 12, '• 1600 Watt\r\n• 80 Plus Titanium\r\n• Fully Modular\r\n• ATX\r\n• Aura Sync RGB\r\n• OLED Display\r\n• PCIe 5.0 12VHPWR (16 Pin) Connector', '1771258044_1.jpg', 17, '2026-02-16 16:07:24'),
(167, 'GAMDIAS AURA GP550 - 550W BLACK ATX', '990.00', 12, '• 550 Watt\r\n• NON Modular\r\n• ATX', '1771258231_1.jpg', 17, '2026-02-16 16:10:31'),
(168, 'CORSAIR CX650 - 650W 80 PLUS BRONZE BLACK ATX (CP-9020278-NA)', '1690.00', 15, '• 650 Watt\r\n• 80 Plus Bronze\r\n• NON Modular\r\n• ATX', '1771258400_1.jpg', 17, '2026-02-16 16:13:20'),
(169, 'SUPER FLOWER LEADEX III GOLD ATX 3.1 850W - 850W 80 PLUS GOLD BLACK ATX', '3690.00', 12, '• 850 Watt\r\n• 80 Plus Gold\r\n• Fully Modular\r\n• ATX\r\n• 16 Pin Connector (12V-2x6)', '1771258527_1.jpg', 17, '2026-02-16 16:15:27'),
(170, 'ASUS ROG STRIX 1000W PLATINUM BLACK ATX', '7900.00', 13, '• กำลังไฟ : 1000 วัตต์\r\n• ประสิทธิภาพ : 80 Plus Platinum\r\n• มอดุลาร์ : Fully Modular\r\n• ฟอร์มแฟกเตอร์ : ATX', '1771258726_1.jpg', 17, '2026-02-16 16:18:46'),
(171, 'LOGITECH GAMING G640 LARGE', '990.00', 12, 'Dimension 460 x 400 x 3 mm', '1771261237_1.jpg', 18, '2026-02-16 17:00:37'),
(172, 'RAZER GOLIATHUS CHROMA', '1290.00', 16, 'DIMENSION 355 X 255 X 3 MM', '1771261365_1.jpg', 18, '2026-02-16 17:02:45'),
(173, 'SIGNO MT-310 CORVUS', '100.00', 11, '• Speed Edition\r\n• Medium Size 320 x 240 x 4 mm', '1771261558_1.jpg', 18, '2026-02-16 17:05:58'),
(174, 'RAZER GIGANTUS V2 LARGE', '590.00', 17, 'Dimension 450 x 400 x 3 mm', '1771261648_1.jpg', 18, '2026-02-16 17:07:28'),
(175, 'SIGNO MT-300', '50.00', 16, 'Dimension 270 x 230 x 3 mm', '1771261763_1.jpg', 18, '2026-02-16 17:08:59'),
(176, 'SIGNO MT-309', '170.00', 21, 'Dimension 770 x 295 x 3 mm', '1771261846_1.jpg', 18, '2026-02-16 17:10:46'),
(177, 'NUBWO NP021', '120.00', 12, 'Dimension 780 x 300 x 3 mm', '1771261942_1.jpg', 18, '2026-02-16 17:12:22'),
(178, 'NUBWO NP020', '120.00', 9, 'Dimension 780 x 300 x 3 mm', '1771262046_1.jpg', 18, '2026-02-16 17:14:06'),
(179, 'NUBWO GALAXY X93 RGB', '350.00', 15, 'Dimension 800 x 300 x 4 mm', '1771262108_1.jpg', 18, '2026-02-16 17:15:08'),
(180, 'ONIKUMA G6 RGB', '490.00', 16, '• 800 x 300 x 4 mm\r\n• Speed Surafce\r\n• RGB', '1771262211_1.jpg', 18, '2026-02-16 17:16:51'),
(181, 'UGREEN USB 2.0 TO EXTERNAL SOUND ADAPTER 0.3 METER', '310.00', 12, 'USB Type A to Audio ports', '1771263280_1.jpg', 19, '2026-02-16 17:34:40'),
(182, 'CREATIVE SOUND BLASTER PLAY 3', '790.00', 16, 'USB Type A to Audio ports', '1771263315_1.jpg', 19, '2026-02-16 17:35:15'),
(183, 'CREATIVE SOUND BLASTER X G1', '1090.00', 15, '7.1 Channel Type USB port', '1771263355_1.jpg', 19, '2026-02-16 17:35:55'),
(184, 'CREATIVE SOUND BLASTER G3 EXTERNAL', '1990.00', 15, 'USB Type-C to Audio ports', '1771263399_1.jpg', 19, '2026-02-16 17:36:39'),
(185, 'CREATIVE SOUND BLASTER PLAY 4', '990.00', 15, '• Plug-and-play\r\n• Hi-res USB DAC with Auto Mute\r\n• Two-way Noise Cancellation\r\n• USB-C Connectivity with USB-C to USB-A Adapter', '1771263445_1.jpg', 19, '2026-02-16 17:37:25'),
(186, 'CREATIVE SOUND BLASTER X G6', '3890.00', 15, '7.1 Channel Type USB Port', '1771263482_1.jpg', 19, '2026-02-16 17:38:02'),
(187, 'CREATIVE SOUND BLASTER X4', '4190.00', 14, 'Hi-res 7.1 External USB DAC and Amp Sound Card with Super X-Fi and SmartComms Kit', '1771263534_1.jpg', 19, '2026-02-16 17:38:54'),
(188, 'CREATIVE SOUND BLASTER G8 - HI-RES GAMING DUAL USB DAC AND AMP', '4990.00', 14, '• Dual USB Audio Input and Mixing\r\n• Up to 32-Bit / 384 kHz Playback\r\n• Proprietary XAMP Dedicated Amplifier\r\n• Sound Blaster Acoustic Engine\r\n• Scout Mode', '1771263577_1.jpg', 19, '2026-02-16 17:39:37'),
(189, 'CREATIVE SOUND BLASTER GC7', '3990.00', 16, 'Game Streaming USB DAC and Amp with Programmable Buttons and Super X-Fi', '1771263615_1.jpg', 19, '2026-02-16 17:40:15'),
(190, 'CREATIVE SOUND BLASTER X AE-5 PLUS (BLACK)', '4290.00', 15, '7.1 Channel Type PCI Express Card', '1771263660_1.jpg', 19, '2026-02-16 17:41:00'),
(191, 'THERMALTAKE VERSA J21 TEMPERED GLASS EDITION (BLACK) (ATX)', '1490.00', 16, '• MB Support : ATX, Micro-ATX, Mini-DTX, Mini-ITX\r\n• Max. CPU cooler height : 160 mm\r\n• Max. GPU length : 310 mm\r\n• Max. PSU length : 220 mm\r\n• Compatible PSU : ATX\r\n• Expansion slots : 7\r\n• 3.5\" / 2.5\" combo bay x 2\r\n• 2.5\" bay x 2', '1771263937_1.jpg', 20, '2026-02-16 17:45:37'),
(192, 'ASUS TUF GAMING GT501 (BLACK) (E-ATX)', '2990.00', 14, '• Maximum CPU Cooler Height : 180 mm\r\n• 3.5\" & 2.5\" Combo Bay x 4\r\n• 2.5\" Bay x 3', '1771263972_1.jpg', 20, '2026-02-16 17:46:12'),
(193, 'MONTECH X2 MESH (BLACK) (ATX)', '1290.00', 17, '• MB Support : ATX, Micro-ATX, Mini-DTX, Mini-ITX\r\n• Max. CPU cooler height : 165 mm\r\n• Max. GPU length : 305 mm\r\n• Compatible PSU : ATX\r\n• Expansion slots : 7\r\n• 3.5\" / 2.5\" combo bay x 1\r\n• 3.5\" bay x 1\r\n• 2.5\" bay x 2', '1771264004_1.jpg', 20, '2026-02-16 17:46:44'),
(194, 'ASUS ROG STRIX HELIOS WHITE EDITION (E-ATX) (GX601)', '9900.00', 15, '• Mainboard Support : E-ATX, ATX, Micro-ATX, Mini-DTX, Mini-ITX\r\n• Max. CPU Cooler Height : 190 mm\r\n• Max. GPU Length : 450 mm\r\n• Max. PSU Length : 220 mm\r\n• Compatible PSU : ATX\r\n• Expansion Slots : 8+2\r\n• 3.5\" / 2.5\" Combo Bay x 2\r\n• 2.5\" Bay x 4\r\n• Aura Sync ARGB\r\n• Stylish Fabric Handles', '1771264070_1.jpg', 20, '2026-02-16 17:47:50'),
(195, 'MONTECH X2 MESH (WHITE) (ATX)', '1350.00', 7, '• MB Support : ATX, Micro-ATX, Mini-DTX, Mini-ITX\r\n• Max. CPU cooler height : 165 mm\r\n• Max. GPU length : 305 mm\r\n• Compatible PSU : ATX\r\n• Expansion slots : 7\r\n• 3.5\" / 2.5\" combo bay x 1\r\n• 3.5\" bay x 1\r\n• 2.5\" bay x 2', '1771264111_1.jpg', 20, '2026-02-16 17:48:31'),
(196, 'ASUS ROG HYPERION GR701 (BLACK) (E-ATX)', '15900.00', 14, '• Maximum CPU Cooler Height : 190 mm\r\n• 3.5\" / 2.5\" Combo Bay x 2\r\n• 2.5\" Bay x 5\r\n• Aura Sync ARGB', '1771264164_1.jpg', 20, '2026-02-16 17:49:24'),
(197, 'DEEPCOOL MACUBE 110 (BLACK)', '1590.00', 15, '• Dimension : 400 x 225 x 431 mm\r\n• Maximum CPU Cooler Height : 165 mm\r\n• 3.5\" Bay x 2\r\n• 2.5\" Bay x 2\r\n• 5.25\" Bay x -', '1771264203_1.jpg', 20, '2026-02-16 17:50:03'),
(198, 'AEROCOOL CS-107 (BLACK) (MICRO-ATX)', '990.00', 11, '• Mainboard Support : Micro-ATX, Mini-DTX, Mini-ITX\r\n• Max. CPU Cooler Height : 157 mm\r\n• Max. GPU Length : 286 mm\r\n• Max. PSU Length : 159 mm\r\n• Compatible PSU : ATX\r\n• Expansion Slots : 4\r\n• 3.5\" Bay x 1\r\n• 2.5\" Bay x 2', '1771264247_1.jpg', 20, '2026-02-16 17:50:14'),
(199, 'MONTECH X3 MESH (BLACK)', '1690.00', 16, '• Dimension : 370 x 210 x 480 mm\r\n• Maximum CPU Cooler Height : 160 mm\r\n• 3.5\" Bay x 2\r\n• 2.5\" Bay x 2', '1771264291_1.jpg', 20, '2026-02-16 17:51:31'),
(200, 'COOLER MASTER TD300 MESH (BLACK) (MICRO-ATX)', '2190.00', 15, '• Dimension : 367 x 210 x 410 mm\r\n• Maximum CPU Cooler Height : 166 mm\r\n• 3.5\" & 2.5\" Combo Bay x 2\r\n• 2.5\" Bay x 2\r\n• 2 Years warranty for electrical components, e.g. USB port / audio port / switch\r\n• 1 Year warranty for case fan\r\n• รับประกัน 2 ปีสำหรับอุปกรณ์ไฟฟ้า เช่น พอร์ต USB / พอร์ตเสียง / สวิตช์\r\n• รับประกัน 1 ปีสำหรับพัดลมเคส', '1771264456_1.jpg', 20, '2026-02-16 17:52:11');

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

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
