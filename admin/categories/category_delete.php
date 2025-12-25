<?php
include __DIR__ . "/../partials/connectdb.php";


$id = $_GET['id'] ?? null;

// ถ้าไม่มี id
if(!$id) {
  die("❌ ไม่พบรหัสประเภทสินค้า");
}

// ลบข้อมูล
$stmt = $conn->prepare("DELETE FROM category WHERE cat_id = ?");
$stmt->execute([$id]);

// กลับไปหน้ารายการ
header("Location: categories.php");
exit;
?>
