<?php
session_start();
include("connectdb.php");

// ✅ ตรวจสอบว่ามี id สินค้าหรือไม่
if (!isset($_GET['id'])) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสสินค้า</p>");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT p.*, c.cat_name 
                        FROM product p 
                        LEFT JOIN category c ON p.cat_id = c.cat_id 
                        WHERE p_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบสินค้านี้</p>");
}

// ✅ ตั้ง path รูปสินค้า
$imgPath = "../admin/uploads/" . $product['p_image'];
if (!file_exists($imgPath) || empty($product['p_image'])) {
  $imgPath = "img/default.png";
}

// ✅ ดึงจำนวนสินค้าที่ขายไปแล้ว (รวมยอด order_details)
$soldStmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS sold_qty FROM order_details WHERE p_id = ?");
$soldStmt->execute([$id]);
$soldQty = $soldStmt->fetchColumn();

// ✅ คำนวณจำนวนที่เหลือในสต็อก (ถ้ามีฟิลด์ p_stock)
$remainQty = isset($product['p_stock']) ? max(0, $product['p_stock'] - $soldQty) : 'ไม่ระบุ';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['p_name']) ?> | รายละเอียดสินค้า</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff;
      font-family: "Prompt", sans-serif;
      color: #212529;
    }
    h3, h4 { color: #D10024; }
    .btn-primary, .btn-success { background-color: #D10024; border: none; }
    .btn-primary:hover, .btn-success:hover { background-color: #a5001b; }
    .card { border: none; border-radius: 12px; }
    footer {
      background-color: #D10024;
      color: #fff;
      margin-top: 50px;
      padding: 15px;
      font-size: 0.9rem;
    }
    .text-danger { color: #D10024 !important; }
  </style>
</head>
<body>

<!-- ✅ Navbar -->
<?php include("navbar_user.php"); ?>

<!-- 🔔 Toast แจ้งเตือน -->
<?php if (isset($_SESSION['toast_success'])): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['toast_success']); ?>
<?php endif; ?>

<!-- ✅ ส่วนแสดงรายละเอียดสินค้า -->
<div class="container mt-5">
  <div class="card shadow p-4">
    <div class="row g-4 align-items-center">
      <div class="col-md-5 text-center">
        <img src="<?= $imgPath ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['p_name']) ?>" style="max-height:350px; object-fit:cover;">
      </div>

      <div class="col-md-7">
        <h3 class="fw-bold mb-2"><?= htmlspecialchars($product['p_name']) ?></h3>
        <p class="text-muted mb-1">หมวดหมู่: <?= htmlspecialchars($product['cat_name'] ?? '-') ?></p>
        <h4 class="fw-bold mb-3"><?= number_format($product['p_price'], 2) ?> บาท</h4>
        <p class="mb-4"><?= nl2br(htmlspecialchars($product['p_description'])) ?></p>

        <!-- ✅ แสดงจำนวนขายและจำนวนคงเหลือ -->
        <p><strong class="text-success">✅ ขายแล้ว:</strong> <?= $soldQty ?> ชิ้น</p>
        <p><strong class="text-primary">📦 คงเหลือในสต็อก:</strong> <?= is_numeric($remainQty) ? $remainQty . ' ชิ้น' : $remainQty ?></p>

        <div class="mt-3">
          <?php if (isset($_SESSION['customer_id'])): ?>
            <!-- ✅ ส่งข้อมูลไป cart_add.php -->
            <form method="post" action="cart_add.php">
              <input type="hidden" name="id" value="<?= $product['p_id'] ?>">
              <div class="d-flex align-items-center gap-2 mb-3">
                <label for="qty" class="fw-semibold">จำนวน:</label>
                <input type="number" name="qty" id="qty" min="1" value="1" class="form-control w-25 text-center">
              </div>
              <button type="submit" class="btn btn-success me-2">🛒 เพิ่มลงตะกร้า</button>
              <a href="index.php" class="btn btn-secondary">⬅️ กลับหน้าร้าน</a>
            </form>
          <?php else: ?>
            <div class="alert alert-warning mt-3">
              🔑 กรุณาเข้าสู่ระบบก่อนเพื่อทำการสั่งซื้อ
            </div>
            <a href="login.php" class="btn btn-primary me-2">เข้าสู่ระบบ</a>
            <a href="index.php" class="btn btn-secondary">⬅️ กลับหน้าร้าน</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Footer -->
<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | รายละเอียดสินค้า
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
    toast.show();
  });
});
</script>
</body>
</html>
