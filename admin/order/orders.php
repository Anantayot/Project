<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจากโฟลเดอร์ partials
include __DIR__ . "/../partials/connectdb.php";

// 👇 เปลี่ยนชื่อ Title ตรงนี้ ซึ่งจะไปแสดงที่ Topbar
$pageTitle = "รายการคำสั่งซื้อ";

// บังคับให้ต้องล็อกอิน
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login.php");
  exit;
}

/* =========================================================
   🟢 ส่วนตั้งค่า (Config): แก้ไขสีหรือเพิ่มสถานะใหม่ได้ที่นี่เลย!
   ========================================================= */
$statusColors = [
    'รอดำเนินการ'    => 'custom-yellow', 
    'กำลังจัดเตรียม'  => 'custom-blue',   
    'จัดส่งแล้ว'      => 'success',       
    'สำเร็จ'         => 'success',       
    'ยกเลิก'         => 'danger'         
];

$verifyColors = [
    'รอตรวจสอบ'     => 'warning text-dark',
    'กำลังตรวจสอบ'   => 'purple',
    'อนุมัติ'         => 'custom-success', 
    'ปฏิเสธ'         => 'danger'
];

try {
    $sql = "SELECT o.order_id, o.order_date, o.total_price, o.order_status, o.admin_verified, c.name AS customer_name 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            ORDER BY o.order_id DESC"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center mt-4'>❌ SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

ob_start();
?>

<style>
  .table-card { background: var(--bg-card, #1e1e2d); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.05); }
  .table-custom-header { background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important; color: #ffffff !important; }
  .table-dark { --bs-table-bg: transparent; --bs-table-color: #ffffff; border-color: rgba(255, 255, 255, 0.05); }
  #dataTable tbody tr:hover { background-color: rgba(255, 255, 255, 0.05) !important; transition: all 0.3s ease-in-out; }

  .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { color: #ffffff !important; }
  .dataTables_length select { background-color: #161b22; color: #ffffff; border: 1px solid #334155; border-radius: 8px; padding: 3px 10px; outline: none; }
  .page-item.active .page-link { background-color: #16a34a !important; border-color: #16a34a !important; color: #ffffff !important; box-shadow: 0 0 10px rgba(22, 163, 74, 0.4); }
  .page-link { background-color: rgba(255, 255, 255, 0.05) !important; border-color: rgba(255, 255, 255, 0.1) !important; color: #e2e8f0 !important; }
  .page-link:hover { background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; }

  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } 
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } 
  .bg-custom-yellow { background-color: #facc15 !important; color: #0f172a !important; } 

  /* 👉 เพิ่ม Class กำหนดขนาดป้ายสถานะให้เท่ากันทั้งหมด */
  .badge-fixed {
    width: 125px; /* กำหนดความกว้างคงที่ ปรับลดเพิ่มได้ตามต้องการ */
    display: inline-block;
    text-align: center;
  }

  @media (max-width: 767px) {
    #dataTable thead { display: none; }
    #dataTable tbody tr { display: block; background: rgba(255, 255, 255, 0.04); border-radius: 12px; margin-bottom: 15px; padding: 15px; border: 1px solid rgba(255, 255, 255, 0.08); position: relative; }
    #dataTable tbody td { display: flex; justify-content: space-between; align-items: center; border: none; padding: 8px 0; text-align: right; color: #ffffff !important; }
    #dataTable tbody td:before { content: attr(data-label); float: left; font-weight: 500; color: #94a3b8; }
    #dataTable td[data-label="ID"] { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom