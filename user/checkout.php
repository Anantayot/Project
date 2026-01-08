<?php
session_start();
include("connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
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
      $conn->beginTransaction(); // :contentReference[oaicite:2]{index=2}

      // ✅ เตรียม statement สำหรับล็อกสต็อก + อัปเดตสต็อก
      $stmtLock = $conn->prepare("SELECT p_id, p_name, p_price, p_stock FROM product WHERE p_id = ? FOR UPDATE");
      $stmtUpdateStock = $conn->prepare("UPDATE product SET p_stock = p_stock - ? WHERE p_id = ?");

      // ✅ คำนวณราคารวมจาก DB + เช็คสต็อกจริง
      $totalPrice = 0;
      $itemsForInsert = []; // เก็บข้อมูลที่จะ insert ลง order_details แบบเชื่อถือได้

      foreach ($cart as $item) {
        $pid = (int)$item['id'];
        $qty = (int)$item['qty'];

        if ($qty <= 0) {
          throw new Exception("จำนวนสินค้าไม่ถูกต้อง");
        }

        // 🔒 ล็อกแถวสินค้าและอ่านสต็อกล่าสุด (FOR UPDATE) :contentReference[oaicite:3]{index=3}
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

        // ✅ เตรียมข้อมูลสำหรับ order_details (ใช้ราคา DB)
        $itemsForInsert[] = [
          'pid' => $pid,
          'qty' => $qty,
          'price' => $price
        ];
      }

      // ✅ เพิ่มคำสั่งซื้อ (ยังเป็น 'รอดำเนินการ' เหมือนเดิม)
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
      $orderId = $conn->lastInsertId(); // :contentReference[oaicite:4]{index=4}

      // ✅ เพิ่มรายละเอียดสินค้า (ใช้ข้อมูลที่ผ่านการเช็คแล้ว)
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
      $conn->commit(); // :contentReference[oaicite:5]{index=5}

      unset($_SESSION['cart']);
      $_SESSION['toast_success'] = "✅ ขอบคุณคุณ " . htmlspecialchars($user['name']) . " 🎉 คำสั่งซื้อของคุณถูกบันทึกแล้ว";
      header("Location: orders.php");
      exit;

    } catch (Exception $e) {
      // ✅ rollback เมื่อมีปัญหา :contentReference[oaicite:6]{index=6}
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
  <title>MyCommiss | ชำระเงิน</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #fff; font-family: "Prompt", sans-serif; }
    h3 { color: #D10024; }
    .card-header { background-color: #D10024; color: #fff; font-weight: 600; }
    .btn-success { background-color: #D10024; border: none; }
    .btn-success:hover { background-color: #a5001b; }
    .btn-secondary { border-radius: 8px; }
    .table thead { background-color: #f8f9fa; }
    footer { background-color: #D10024; color: #fff; margin-top: 50px; padding: 15px; font-size: 0.9rem; }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:3000;">
  <?php if (isset($_SESSION['toast_success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['toast_error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><?= $_SESSION['toast_error'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_error']); ?>
  <?php endif; ?>
</div>

<div class="container mt-4">
  <h3 class="fw-bold mb-4 text-center">💳 ยืนยันคำสั่งซื้อ</h3>

  <div class="row">
    <div class="col-md-7 mb-4">
      <div class="card shadow-sm border-0">
        <div class="card-header">สินค้าในตะกร้า</div>
        <div class="card-body">
          <table class="table table-borderless align-middle">
            <thead class="text-center">
              <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>รวม</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $total = 0;
              foreach ($cart as $item):
                $sum = $item['price'] * $item['qty'];
                $total += $sum;
              ?>
              <tr class="text-center">
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= (int)$item['qty'] ?></td>
                <td><?= number_format((float)$item['price'], 2) ?></td>
                <td><?= number_format((float)$sum, 2) ?></td>
              </tr>
              <?php endforeach; ?>
              <tr class="fw-bold text-danger text-end">
                <td colspan="3">💰 ราคารวมทั้งหมด</td>
                <td><?= number_format((float)$total, 2) ?> บาท</td>
              </tr>
            </tbody>
          </table>
          <div class="text-muted small">

          </div>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card shadow-sm border-0">
        <div class="card-header">ข้อมูลผู้สั่งซื้อ</div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label fw-semibold">ชื่อผู้ใช้</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">อีเมล</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">ที่อยู่จัดส่ง</label>
              <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">เบอร์โทรศัพท์</label>
              <input type="text" name="phone" maxlength="10" pattern="^[0-9]{10}$"
                     title="กรุณากรอกเฉพาะตัวเลข 10 หลัก"
                     oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                     class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">วิธีชำระเงิน</label>
              <select name="payment" class="form-select" required>
                <option value="COD">💵 เก็บเงินปลายทาง</option>
                <option value="QR">📱 ชำระด้วย QR Code</option>
              </select>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">✅ ยืนยันคำสั่งซื้อ</button>
              <a href="cart.php" class="btn btn-secondary mt-2">⬅️ กลับไปแก้ไขตะกร้า</a>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | ชำระเงิน
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
