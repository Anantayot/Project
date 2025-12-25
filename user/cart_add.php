<?php
session_start();
include("connectdb.php");

// ✅ ตรวจสอบการส่งข้อมูล
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id'])) {
  header("Location: index.php");
  exit;
}

$productId = intval($_POST['id']);
$qty = intval($_POST['qty'] ?? 1);

// ✅ ถ้ายังไม่ล็อกอิน → ให้ไปหน้า login
if (!isset($_SESSION['customer_id'])) {
  $_SESSION['toast_error'] = "⚠️ กรุณาเข้าสู่ระบบก่อนสั่งซื้อสินค้า";
  header("Location: login.php");
  exit;
}

// ✅ ดึงข้อมูลสินค้า
$stmt = $conn->prepare("SELECT * FROM product WHERE p_id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  $_SESSION['toast_error'] = "❌ ไม่พบสินค้านี้";
  header("Location: index.php");
  exit;
}

// ✅ ตรวจสอบว่าสินค้าหมดหรือไม่
if ($product['p_stock'] <= 0) {
  $_SESSION['toast_error'] = "❌ สินค้านี้หมดสต็อกแล้ว";
  header("Location: index.php");
  exit;
}

// ✅ เพิ่มสินค้าลงตะกร้าใน session
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$productId])) {
  $_SESSION['cart'][$productId]['qty'] += $qty;
} else {
  $_SESSION['cart'][$productId] = [
    'id' => $product['p_id'],
    'name' => $product['p_name'],
    'price' => $product['p_price'],
    'image' => $product['p_image'],
    'qty' => $qty
  ];
}

// ✅ Toast แจ้งเตือน
$_SESSION['toast_success'] = "✅ เพิ่มสินค้า <b>" . htmlspecialchars($product['p_name']) . "</b> ในตะกร้าเรียบร้อยแล้ว!";

// ✅ กลับไปหน้า cart.php พร้อม Toast
header("Location: cart.php");
exit;
?>
