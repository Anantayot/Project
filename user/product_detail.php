<?php
session_start();
include("connectdb.php");

// ✅ ตรวจสอบว่ามี id สินค้าหรือไม่
if (!isset($_GET['id'])) {
  die("<div class='container mt-5'><div class='alert alert-danger text-center shadow-sm'>❌ ไม่พบรหัสสินค้า</div></div>");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT p.*, c.cat_name 
                        FROM product p 
                        LEFT JOIN category c ON p.cat_id = c.cat_id 
                        WHERE p_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  die("<div class='container mt-5'><div class='alert alert-danger text-center shadow-sm'>❌ ไม่พบสินค้านี้</div></div>");
}

// ✅ ตั้ง path รูปสินค้า
$imgPath = "../admin/uploads/" . $product['p_image'];
if (empty($product['p_image']) || !file_exists($imgPath)) {
  $imgPath = "img/default.png";
}

// ✅ วิธี 1: สต็อกจริงใช้จาก product.p_stock โดยตรง
$remainQty = isset($product['p_stock']) ? (int)$product['p_stock'] : null;

// ✅ วิธี B: ขายแล้ว = รวมจำนวนจาก order_details เฉพาะออเดอร์ที่ไม่ถูกยกเลิก
$soldStmt = $conn->prepare("
  SELECT COALESCE(SUM(od.quantity), 0) AS sold_qty
  FROM order_details od
  INNER JOIN orders o ON o.order_id = od.order_id
  WHERE od.p_id = ?
    AND o.payment_status <> 'ยกเลิก'
");
$soldStmt->execute([$id]);
$soldQty = (int)$soldStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['p_name']) ?> | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Prompt", sans-serif;
      color: #333;
    }

    /* 🔹 การ์ดสินค้าหลัก */
    .card-product {
      border: none;
      border-radius: 20px;
      background: #fff;
    }

    /* 🔹 โซนรูปภาพ */
    .product-img-wrapper {
      background-color: #fff;
      border: 1px solid #eee;
      border-radius: 15px;
      padding: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      min-height: 350px;
    }
    .product-img-wrapper img {
      max-height: 400px;
      max-width: 100%;
      object-fit: contain;
      transition: transform 0.4s ease;
    }
    .product-img-wrapper:hover img {
      transform: scale(1.05);
    }

    /* 🔹 Typography */
    .product-title {
      font-weight: 700;
      font-size: 1.8rem;
      color: #222;
      line-height: 1.3;
    }
    .product-price {
      font-weight: 700;
      font-size: 2.2rem;
      color: #D10024;
    }

    /* 🔹 Buttons & Inputs */
    .btn-primary-custom {
      background-color: #D10024;
      color: #fff;
      border: none;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-primary-custom:hover {
      background-color: #a5001b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
    }
    
    .qty-input {
      max-width: 120px;
      border-radius: 10px;
      text-align: center;
      font-weight: 600;
      font-size: 1.1rem;
    }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      margin-top: 60px;
    }

    /* 📱 มือถือ */
    @media (max-width: 768px) {
      .product-title { font-size: 1.4rem; }
      .product-price { font-size: 1.8rem; }
      .product-img-wrapper { padding: 15px; min-height: 250px; }
      .product-img-wrapper img { max-height: 250px; }
    }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 3000;">
  <?php if (isset($_SESSION['toast_success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
      <div class="d-flex">
        <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_success']); ?>
  <?php endif; ?>
</div>

<div class="container mt-4 mb-5">
  
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted"><i class="bi bi-house-door"></i> หน้าร้าน</a></li>
      <li class="breadcrumb-item text-muted" aria-current="page"><?= htmlspecialchars($product['cat_name'] ?? 'สินค้า') ?></li>
      <li class="breadcrumb-item active" aria-current="page" style="color: #D10024; font-weight: 500;">รายละเอียดสินค้า</li>
    </ol>
  </nav>

  <div class="card card-product shadow-sm">
    <div class="card-body p-4 p-md-5">
      <div class="row g-5 align-items-center">
        
        <div class="col-lg-5 col-md-6 mb-4 mb-md-0">
          <div class="product-img-wrapper shadow-sm">
            <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product['p_name']) ?>">
          </div>
        </div>

        <div class="col-lg-7 col-md-6">
          <span class="badge bg-light text-secondary border px-3 py-2 mb-3 fs-6">
            <i class="bi bi-tags me-1"></i> หมวดหมู่: <?= htmlspecialchars($product['cat_name'] ?? 'ไม่มีหมวดหมู่') ?>
          </span>
          
          <h1 class="product-title mb-3"><?= htmlspecialchars($product['p_name']) ?></h1>
          
          <div class="d-flex align-items-center gap-4 mb-4">
            <h2 class="product-price mb-0"><?= number_format($product['p_price'], 2) ?> ฿</h2>
            <div class="border-start ps-4">
              <span class="text-warning fs-5"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i></span>
            </div>
          </div>

          <div class="bg-light rounded p-3 mb-4">
            <p class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>ขายแล้ว:</strong> <?= $soldQty ?> ชิ้น</p>
            <p class="mb-0">
              <i class="bi bi-box-seam-fill text-primary me-2"></i><strong>คงเหลือในสต็อก:</strong> 
              <?= is_null($remainQty) ? 'ไม่ระบุ' : '<span class="fw-bold fs-5 mx-1">'.$remainQty.'</span> ชิ้น' ?>
            </p>
          </div>

          <h5 class="fw-bold mb-3">รายละเอียดสินค้า:</h5>
          <p class="text-muted mb-5" style="line-height: 1.8;">
            <?= nl2br(htmlspecialchars($product['p_description'])) ?: 'ไม่มีรายละเอียดสินค้าระบุไว้' ?>
          </p>

          <hr class="mb-4 text-muted">

          <div>
            <?php if (isset($_SESSION['customer_id'])): ?>

              <?php if (!is_null($remainQty) && $remainQty <= 0): ?>
                <div class="alert alert-danger d-flex align-items-center mb-0 p-3 rounded-3 shadow-sm">
                  <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                  <div>
                    <strong class="d-block">ขออภัย! สินค้าหมดสต็อก</strong>
                    <span class="small">โปรดรอสินค้าเข้ามาใหม่ในเร็วๆ นี้</span>
                  </div>
                </div>

              <?php else: ?>
                <form method="post" action="cart_add.php" class="d-flex flex-wrap align-items-end gap-3">
                  <input type="hidden" name="id" value="<?= (int)$product['p_id'] ?>">

                  <div>
                    <label for="qty" class="form-label fw-semibold text-muted mb-1">จำนวนที่ต้องการ</label>
                    <div class="input-group">
                      <span class="input-group-text bg-white"><i class="bi bi-plus-slash-minus text-muted"></i></span>
                      <input type="number" name="qty" id="qty" min="1" value="1" 
                             class="form-control qty-input" 
                             <?= (!is_null($remainQty) ? 'max="'.$remainQty.'"' : '') ?> 
                             required>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary-custom rounded-pill px-4 py-2 fs-5 flex-grow-1">
                    <i class="bi bi-cart-plus me-2"></i> เพิ่มลงตะกร้า
                  </button>
                </form>
              <?php endif; ?>

            <?php else: ?>
              <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm border-0">
                <i class="bi bi-lock-fill fs-4 me-3"></i>
                <span>กรุณาเข้าสู่ระบบก่อนเพื่อทำการสั่งซื้อสินค้าชิ้นนี้</span>
              </div>
              <div class="d-flex gap-2">
                <a href="login.php" class="btn btn-primary-custom rounded-pill px-5 py-2 fw-bold">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold">สมัครสมาชิก</a>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
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