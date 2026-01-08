<?php
session_start();
include("connectdb.php");

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

$customer_id = (int)$_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id คำสั่งซื้อหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['toast_error'] = "❌ ไม่พบรหัสคำสั่งซื้อที่ต้องการยกเลิก";
  header("Location: orders.php");
  exit;
}

$order_id = (int)$_GET['id'];

try {
  $conn->beginTransaction(); // 

  // ✅ ล็อกแถวคำสั่งซื้อของผู้ใช้นี้ (กันกดยกเลิกซ้อน/ชนกัน)
  $stmt = $conn->prepare("SELECT order_id, order_status, payment_status
                          FROM orders
                          WHERE order_id = ? AND customer_id = ?
                          FOR UPDATE");
  $stmt->execute([$order_id, $customer_id]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$order) {
    throw new Exception("❌ ไม่พบคำสั่งซื้อของคุณ");
  }

  // ✅ ตรวจสอบสถานะก่อนยกเลิก
  // (คง logic เดิม: ยกเลิกได้เฉพาะ 'รอดำเนินการ')
  if (($order['order_status'] ?? '') !== 'รอดำเนินการ') {
    throw new Exception("⚠️ คำสั่งซื้อนี้ไม่สามารถยกเลิกได้");
  }

  // ✅ ดึงรายการสินค้าในคำสั่งซื้อ
  $stmtItems = $conn->prepare("SELECT p_id, quantity FROM order_details WHERE order_id = ?");
  $stmtItems->execute([$order_id]);
  $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

  // ✅ คืนสต็อก (ไม่ลบ order_details)
  if (!empty($items)) {
    $stmtRestock = $conn->prepare("UPDATE product SET p_stock = p_stock + ? WHERE p_id = ?");

    foreach ($items as $it) {
      $pid = (int)$it['p_id'];
      $qty = (int)$it['quantity'];

      if ($qty > 0) {
        $stmtRestock->execute([$qty, $pid]);
      }
    }
  }

  // ✅ เปลี่ยนสถานะเป็น 'ยกเลิก'
  $update = $conn->prepare("
    UPDATE orders
    SET order_status = 'ยกเลิก',
        payment_status = 'ยกเลิก'
    WHERE order_id = ? AND customer_id = ?
  ");
  $update->execute([$order_id, $customer_id]);

  $conn->commit(); // 

  // ✅ Toast แจ้งเตือนสำเร็จ
  $_SESSION['toast_success'] = "✅ ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว (คืนสต็อกแล้ว)";
  header("Location: orders.php");
  exit;

} catch (Exception $e) {
  if ($conn->inTransaction()) {
    $conn->rollBack(); // 
  }
  $_SESSION['toast_error'] = "❌ " . $e->getMessage();
  header("Location: order_detail.php?id=" . $order_id);
  exit;

} catch (PDOException $e) {
  if ($conn->inTransaction()) {
    $conn->rollBack(); // 
  }
  $_SESSION['toast_error'] = "❌ เกิดข้อผิดพลาดในการยกเลิกคำสั่งซื้อ: " . $e->getMessage();
  header("Location: order_detail.php?id=" . $order_id);
  exit;
}
?>
