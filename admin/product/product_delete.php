<?php
include __DIR__ . "/../partials/connectdb.php"; // ✅ path ถูกต้อง

$id = $_GET['id'] ?? null;
if (!$id) {
  die("<script>alert('ไม่พบสินค้า!');history.back();</script>");
}

// 🔹 1. ดึงชื่อไฟล์รูปภาพก่อนลบ
$stmt = $conn->prepare("SELECT p_image FROM product WHERE p_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  die("<script>alert('❌ ไม่พบสินค้านี้ในฐานข้อมูล');history.back();</script>");
}

// 🔹 2. ลบสินค้าออกจากฐานข้อมูล
$stmt = $conn->prepare("DELETE FROM product WHERE p_id = ?");
$stmt->execute([$id]);

// 🔹 3. ถ้ามีรูปภาพ ให้ลบออกจากโฟลเดอร์ uploads/
if (!empty($product['p_image'])) {
  $imagePath = __DIR__ . "/../uploads/products/" . $product['p_image'];
  if (file_exists($imagePath)) {
    unlink($imagePath); // ✅ ลบไฟล์รูปจริงออกจากเครื่อง
  }
}

// 🔹 4. กลับไปหน้ารายการสินค้า
echo "<script>alert('✅ ลบสินค้าพร้อมรูปภาพเรียบร้อยแล้ว');window.location='products.php';</script>";
exit;
?>
