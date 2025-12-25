<?php
session_start();
include("connectdb.php");

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id คำสั่งซื้อหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['toast_error'] = "❌ ไม่พบรหัสคำสั่งซื้อที่ต้องการยกเลิก";
  header("Location: orders.php");
  exit;
}

$order_id = intval($_GET['id']);

// ✅ ตรวจสอบคำสั่งซื้อของผู้ใช้ (ป้องกันแก้ URL)
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  $_SESSION['toast_error'] = "❌ ไม่พบคำสั่งซื้อของคุณ";
  header("Location: orders.php");
  exit;
}

// ✅ ตรวจสอบสถานะก่อนยกเลิก
if ($order['order_status'] !== 'รอดำเนินการ') {
  $_SESSION['toast_error'] = "⚠️ คำสั่งซื้อนี้ไม่สามารถยกเลิกได้";
  header("Location: order_detail.php?id=" . $order_id);
  exit;
}

// ✅ ดำเนินการยกเลิกคำสั่งซื้อ
try {
  $update = $conn->prepare("
    UPDATE orders 
    SET order_status = 'ยกเลิก', 
        payment_status = 'ยกเลิก' 
    WHERE order_id = ?
  ");
  $update->execute([$order_id]);

  // ✅ Toast แจ้งเตือนสำเร็จ
  $_SESSION['toast_error'] = "✅ ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว";
} catch (PDOException $e) {
  // ✅ กรณีมีปัญหาการเชื่อมต่อหรือ SQL error
  $_SESSION['toast_error'] = "❌ เกิดข้อผิดพลาดในการยกเลิกคำสั่งซื้อ: " . $e->getMessage();
}

// ✅ กลับไปหน้า orders.php พร้อม Toast
header("Location: orders.php");
exit;
?>
