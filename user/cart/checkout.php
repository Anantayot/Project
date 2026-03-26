<?php
session_start();
include("../includes/connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../login.php");
  exit;
}

$cid = $_SESSION['customer_id'];

// ✅ ดึงข้อมูลลูกค้าจากฐานข้อมูล
$stmtUser = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmtUser->execute([$cid]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  $_SESSION['toast_error'] = "⚠️ ตะกร้าสินค้าว่าง กรุณาเลือกสินค้าก่อนสั่งซื้อ";
  header("Location: cart.php");
  exit;
}

// ✅ เมื่อกดยืนยันคำสั่งซื้อ
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $address = trim($_POST['address']);
  $phone = trim($_POST['phone']);
  $payment = $_POST['payment'];

  if (empty($address) || empty($phone)) {
    $_SESSION['toast_error'] = "❌ กรุณากรอกที่อยู่และเบอร์โทรให้ครบถ้วน";
  } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "⚠️ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (เฉพาะตัวเลข 10 หลัก)";
  } else {
    try {
      // ✅ เริ่ม transaction
      $conn->beginTransaction();

      // ✅ เตรียม statement สำหรับล็อกสต็อก + อัปเดตสต็อก
      $stmtLock = $conn->prepare("SELECT p_id, p_name, p_price, p_stock FROM product WHERE p_id = ? FOR UPDATE");
      $stmtUpdateStock = $conn->prepare("UPDATE product SET p_stock = p_stock - ? WHERE p_id = ?");

      // ✅ คำนวณราคารวมจาก DB + เช็คสต็อกจริง
      $totalPrice = 0;
      $itemsForInsert = [];

      foreach ($cart as $item) {
        $pid = (int)$item['id'];
        $qty = (int)$item['qty'];

        if ($qty <= 0) {
          throw new Exception("จำนวนสินค้าไม่ถูกต้อง");
        }

        // 🔒 ล็อกแถวสินค้าและอ่านสต็อกล่าสุด (FOR UPDATE)
        $stmtLock->execute([$pid]);
        $p = $stmtLock->fetch(PDO::FETCH_ASSOC);

        if (!$p) {
          throw new Exception("ไม่พบสินค้า ID: {$pid}");
        }

        $stock = (int)$p['p_stock'];
        $price = (float)$p['p_price'];

        // ✅ เช็คสต็อกพอไหม
        if ($qty > $stock) {
          throw new Exception("สินค้า \"{$p['p_name']}\" เหลือไม่พอ (คงเหลือ {$stock} ชิ้น)");
        }

        // ✅ ตัดสต็อกจริง
        $stmtUpdateStock->execute([$qty, $pid]);

        // ✅ รวมราคา (ใช้ราคาจาก DB เท่านั้น)
        $totalPrice += $price * $qty;

        // ✅ เตรียมข้อมูลสำหรับ order_details
        $itemsForInsert[] = [
          'pid' => $pid,
          'qty' => $qty,
          'price' => $price
        ];
      }

      // ✅ เพิ่มคำสั่งซื้อ (สถานะ 'รอดำเนินการ')
      $stmt = $conn->prepare("INSERT INTO orders 
        (customer_id, shipping_address, payment_method, total_price, order_date, payment_status) 
        VALUES (:cid, :address, :payment, :total, NOW(), 'รอดำเนินการ')");
      $stmt->execute([
        ':cid' => $cid,
        ':address' => $address,
        ':payment' => $payment,
        ':total' => $totalPrice
      ]);

      // ✅ เอา order id
      $orderId = $conn->lastInsertId();

      // ✅ เพิ่มรายละเอียดสินค้า
      $stmtDetail = $conn->prepare("INSERT INTO order_details (order_id, p_id, quantity, price)
                                    VALUES (:oid, :pid, :qty, :price)");
      foreach ($itemsForInsert as $it) {
        $stmtDetail->execute([
          ':oid' => $orderId,
          ':pid' => $it['pid'],
          ':qty' => $it['qty'],
          ':price' => $it['price']
        ]);
      }

      // ✅ commit
      $conn->commit();

      unset($_SESSION['cart']);
      $_SESSION['toast_success'] = "✅ ขอบคุณคุณ " . htmlspecialchars($user['name']) . " 🎉 คำสั่งซื้อของคุณถูกบันทึกแล้ว";
      header("Location: ../order/orders.php");
      exit;

    } catch (Exception $e) {
      // ✅ rollback เมื่อมีปัญหา
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }
      $_SESSION['toast_error'] = "❌ " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ชำระเงิน | MyCommiss</title>
  <link rel="icon" type="image/png" href="../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    
    .checkout-wrapper { min-height: 80vh; padding-bottom: 50px; }
    
    /* 🔹 Cards */
    .card-checkout {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      background: #fff;
      overflow: hidden;
    }
    .card-header-custom {
      background-color: #fff;
      border-bottom: 2px solid #f1f1f1;
      padding: 20px 25px;
      font-weight: 700;
      font-size: 1.2rem;
      color: #333;
    }

    /* 🔹 Table Summary */
    .table-summary th, .table-summary td {
      vertical-align: middle;
      padding: 12px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .product-img-small {
      width: 50px;
      height: 50px;
      object-fit: contain;
      border-radius: 8px;
      border: 1px solid #eee;
      background: #fff;
    }

    /* 🔹 Form Elements */
    .form-control, .form-select {
      border-radius: 10px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      background-color: #fcfcfc;
    }
    .form-control:focus, .form-select:focus {
      border-color: #D10024;
      box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15);
      background-color: #fff;
    }

    /* 🔹 Buttons */
    .btn-submit {
      background-color: #D10024;
      color: #fff;
      border-radius: 50px;
      font-weight: 600;
      padding: 12px;
      transition: 0.3s;
      border: none;
    }
    .btn-submit:hover {
      background-color: #a5001b;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
    }
    .btn-outline-custom {
      border: 1px solid #ddd;
      border-radius: 50px;
      padding: 10px 20px;
      font-weight: 500;
      color: #555;
      transition: 0.3s;
      background: #fff;
    }
    .btn-outline-custom:hover { background: #f1f1f1; color: #333; }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      margin-top: auto;
    }

    /* 📱 MOBILE RESPONSIVE */
    @media (max-width: 768px) {
      .card-body { padding: 15px !important; }
      
      /* แปลง Table รายการสินค้า เป็น Flexbox ให้ดูเหมือนการ์ด */
      .table-summary tbody tr {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        padding-bottom: 12px;
        margin-bottom: 12px;
        border-bottom: 1px dashed #eee;
      }
      .table-summary tbody td {
        padding: 0;
        border: none;
      }
      /* รูปภาพ */
      .table-summary tbody td:nth-child(1) {
        width: 60px;
      }
      /* ชื่อสินค้า และ ราคาต่อชิ้น */
      .table-summary tbody td:nth-child(2) {
        width: calc(100% - 60px);
        padding-left: 15px;
      }
      /* ราคารวม */
      .table-summary tbody td:nth-child(3) {
        width: 100%;
        text-align: right !important;
        margin-top: 8px;
        font-size: 1.1rem;
        color: #D10024 !important; /* เน้นสีแดง */
      }

      /* ส่วนสรุปยอดรวม */
      .total-summary-box {
        flex-direction: column;
        align-items: flex-end !important;
        text-align: right;
      }
      .total-summary-box span:first-child {
        font-size: 1rem !important;
        margin-bottom: 5px;
      }
    }
  </style>
</head>
<body>

<?php include("../includes/navbar_user.php"); ?>

<div class="checkout-wrapper">

  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index:3000;">
    <?php if (isset($_SESSION['toast_success'])): ?>
      <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
        <div class="d-flex">
          <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_success'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
      <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['toast_error'])): ?>
      <div class="toast align-items-center text-bg-danger border-0 show shadow-lg" role="alert">
        <div class="d-flex">
          <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_error'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
      <?php unset($_SESSION['toast_error']); ?>
    <?php endif; ?>
  </div>

  <div class="container mt-5">
    
    <div class="text-center mb-4 mb-md-5">
      <h2 class="fw-bold" style="color: #D10024;"><i class="bi bi-wallet2 me-2"></i>ชำระเงิน</h2>
      <p class="text-muted small d-none d-md-block">ตรวจสอบสินค้าและกรอกข้อมูลการจัดส่งเพื่อยืนยันคำสั่งซื้อ</p>
    </div>

    <form method="post">
      <div class="row g-4">
        
        <div class="col-lg-7 mb-2 mb-lg-4">
          <div class="card card-checkout h-100">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
              <span><i class="bi bi-bag-check me-2 text-muted"></i>รายการสินค้า (<?= count($cart) ?>)</span>
              <a href="cart.php" class="text-decoration-none small text-muted"><i class="bi bi-pencil-square"></i> แก้ไข</a>
            </div>
            <div class="card-body p-4">
              <div class="table-responsive" style="overflow-x: hidden;">
                <table class="table table-borderless table-summary mb-0">
                  <tbody>
                    <?php
                    $total = 0;
                    foreach ($cart as $item):
                      $sum = $item['price'] * $item['qty'];
                      $total += $sum;
                      
                      // ดึงรูปภาพเล็ก
                      $imgPath = "../../admin/uploads/" . $item['image'];
                      if (empty($item['image']) || !file_exists($imgPath)) $imgPath = "img/default.png";
                    ?>
                    <tr>
                      <td style="width: 60px;">
                        <img src="<?= $imgPath ?>" class="product-img-small" alt="<?= htmlspecialchars($item['name']) ?>">
                      </td>
                      <td>
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="text-muted small"><?= number_format((float)$item['price'], 2) ?> ฿ x <?= (int)$item['qty'] ?> ชิ้น</div>
                      </td>
                      <td class="text-end fw-bold text-dark align-bottom">
                        <?= number_format((float)$sum, 2) ?> ฿
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              
              <hr class="text-muted opacity-25 my-3 my-md-4">
              
              <div class="d-flex justify-content-between align-items-center px-2 total-summary-box">
                <span class="fs-5 text-muted fw-semibold">ยอดชำระสุทธิ</span>
                <span class="fs-3 fw-bold text-danger"><?= number_format((float)$total, 2) ?> ฿</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card card-checkout">
            <div class="card-header-custom d-flex align-items-center">
              <i class="bi bi-truck me-2 text-muted"></i>ข้อมูลการจัดส่ง
            </div>
            <div class="card-body p-4">
              
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold mb-1">ชื่อผู้สั่งซื้อ</label>
                  <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['name']) ?>" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold mb-1">อีเมล</label>
                  <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label text-dark fw-semibold mb-1">ที่อยู่สำหรับจัดส่ง <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-white text-muted align-items-start pt-2 border-end-0"><i class="bi bi-geo-alt"></i></span>
                  <textarea name="address" class="form-control border-start-0 ps-0" rows="3" required placeholder="กรุณาระบุ บ้านเลขที่, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label text-dark fw-semibold mb-1">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                  <input type="text" name="phone" maxlength="10" pattern="^[0-9]{10}$"
                         title="กรุณากรอกเฉพาะตัวเลข 10 หลัก"
                         oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                         class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($user['phone']) ?>" required placeholder="08XXXXXXXX">
                </div>
              </div>

              <hr class="text-muted opacity-25 mb-4">

              <div class="mb-4 mb-md-5">
                <label class="form-label text-dark fw-semibold mb-2">ช่องทางการชำระเงิน <span class="text-danger">*</span></label>
                <select name="payment" class="form-select form-select-lg fs-6" required>
                  <option value="" disabled selected>-- เลือกวิธีชำระเงิน --</option>
                  <option value="COD">💵 ชำระเงินปลายทาง</option>
                  <option value="QR">📱 สแกนชำระผ่าน QR Code</option>
                </select>
              </div>

              <div class="d-grid gap-3">
                <button type="submit" class="btn btn-submit fs-5"><i class="bi bi-check2-circle me-2"></i>ยืนยันคำสั่งซื้อ</button>
                <a href="cart.php" class="btn btn-outline-custom text-center"><i class="bi bi-arrow-left me-2"></i>ย้อนกลับไปแก้ไขตะกร้า</a>
              </div>

            </div>
          </div>
        </div>

      </div>
    </form>
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