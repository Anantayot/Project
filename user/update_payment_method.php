<?php
session_start();
include("connectdb.php");

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

// ตรวจสอบข้อมูลที่ส่งมา
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['order_id'], $_POST['payment_method'])) {
  $_SESSION['toast_error'] = "❌ ข้อมูลไม่ถูกต้อง";
  header("Location: orders.php");
  exit;
}

$order_id = intval($_POST['order_id']);
$payment_method = $_POST['payment_method'];
$customer_id = $_SESSION['customer_id'];

// ตรวจสอบสิทธิ์การแก้ไข
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  $_SESSION['toast_error'] = "❌ ไม่พบคำสั่งซื้อของคุณ";
  header("Location: orders.php");
  exit;
}

// อัปเดตวิธีชำระเงิน
$update = $conn->prepare("UPDATE orders SET payment_method = ? WHERE order_id = ?");
$update->execute([$payment_method, $order_id]);

$_SESSION['toast_success'] = "✅ เปลี่ยนวิธีชำระเงินเรียบร้อยแล้ว";
header("Location: order_detail.php?id=" . $order_id);
exit;
?>
