<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("../includes/connectdb.php");

// ✅ ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id หรือไม่
if (!isset($_GET['id'])) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสคำสั่งซื้อ</p>");
}

$order_id = intval($_GET['id']);

// ✅ ดึงข้อมูลคำสั่งซื้อของลูกค้าคนนี้
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบคำสั่งซื้อนี้ หรือคุณไม่มีสิทธิ์ดู</p>");
}

// ✅ ดึงข้อมูลลูกค้า
$stmtUser = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmtUser->execute([$customer_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// ✅ ฟังก์ชัน Toast
function setToast($type, $msg) {
  $_SESSION["toast_" . $type] = $msg;
}

// ✅ ถ้าสถานะคือ “กำลังตรวจสอบ” ให้แสดง Toast แจ้งเตือน
if ($order['admin_verified'] === 'กำลังตรวจสอบ') {
  $_SESSION['toast_info'] = "⚙️ อยู่ระหว่างตรวจสอบสลิป กรุณารอการอนุมัติจากแอดมิน";
}

// ✅ เปลี่ยนวิธีการชำระเงิน
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_payment'])) {
  $new_payment = $_POST['new_payment'];

  if (!in_array($new_payment, ['COD', 'QR'])) {
    setToast('error', '❌ วิธีชำระเงินไม่ถูกต้อง');
    header("Location: order_detail.php?id={$order_id}");
    exit;
  }

  $stmt = $conn->prepare("UPDATE orders 
                          SET payment_method = :method, payment_status = 'รอดำเนินการ', admin_verified = NULL 
                          WHERE order_id = :oid AND customer_id = :cid");
  $stmt->execute([
    ':method' => $new_payment,
    ':oid' => $order_id,
    ':cid' => $customer_id
  ]);

  setToast('success', '✅ เปลี่ยนวิธีชำระเงินเรียบร้อยแล้ว');
  header("Location: order_detail.php?id={$order_id}");
  exit;
}

// ✅ เมื่อกดปุ่ม "สั่งซื้อสินค้าอีกครั้ง" (Reorder)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reorder'])) {
  // ดึงรายการสินค้าทั้งหมดในออเดอร์นี้
  $stmtReorder = $conn->prepare("SELECT p_id, quantity FROM order_details WHERE order_id = ?");
  $stmtReorder->execute([$order_id]);
  $reorderItems = $stmtReorder->fetchAll(PDO::FETCH_ASSOC);

  $addedCount = 0;

  foreach ($reorderItems as $item) {
      $pid = (int)$item['p_id'];
      $qty = (int)$item['quantity'];

      // ดึงข้อมูลสินค้าและเช็คสต็อกปัจจุบัน
      $stmtStock = $conn->prepare("SELECT p_name, p_price, p_image, p_stock FROM product WHERE p_id = ?");
      $stmtStock->execute([$pid]);
      $p = $stmtStock->fetch(PDO::FETCH_ASSOC);

      if ($p && $p['p_stock'] > 0) {
          // ถ้าสต็อกมีน้อยกว่าจำนวนที่เคยสั่ง ให้เอาจำนวนสต็อกที่มี
          $addQty = ($qty > $p['p_stock']) ? $p['p_stock'] : $qty;

          if (!isset($_SESSION['cart'])) {
              $_SESSION['cart'] = [];
          }

          if (isset($_SESSION['cart'][$pid])) {
              $_SESSION['cart'][$pid]['qty'] += $addQty;
              // เช็คอีกทีไม่ให้เกินสต็อก
              if ($_SESSION['cart'][$pid]['qty'] > $p['p_stock']) {
                  $_SESSION['cart'][$pid]['qty'] = $p['p_stock'];
              }
          } else {
              $_SESSION['cart'][$pid] = [
                  'id' => $pid,
                  'name' => $p['p_name'],
                  'price' => $p['p_price'],
                  'image' => $p['p_image'],
                  'qty' => $addQty
              ];
          }
          $addedCount++;
      }
  }

  if ($addedCount > 0) {
      $_SESSION['toast_success'] = "🛒 เพิ่มสินค้าจากคำสั่งซื้อเดิมลงตะกร้าแล้ว";
  } else {
      $_SESSION['toast_error'] = "❌ ขออภัย สินค้าในคำสั่งซื้อนี้หมดสต็อกทั้งหมด";
  }
  header("Location: ../cart/cart.php");
  exit;
}

// ✅ ดึงรายการสินค้าในคำสั่งซื้อ
$stmt2 = $conn->prepare("SELECT d.*, p.p_name, p.p_image 
                         FROM order_details d 
                         LEFT JOIN product p ON d.p_id = p.p_id 
                         WHERE d.order_id = ?");
$stmt2->execute([$order_id]);
$details = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>รายละเอียดคำสั่งซื้อ #<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?> | MyCommiss</title>
  <link rel="icon" type="image/png" href="../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    .page-wrapper { min-height: 80vh; padding-bottom: 50px; }

    /* 🔹 Cards */
    .card-custom {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      background: #fff;
      overflow: hidden;
      margin-bottom: 25px;
    }
    .card-header-custom {
      background-color: #fff;
      border-bottom: 2px solid #f1f1f1;
      padding: 18px 25px;
      font-weight: 700;
      font-size: 1.1rem;
      color: #333;
    }

    /* 🔹 Typography & Badges */
    .info-label { color: #6c757d; font-size: 0.9rem; font-weight: 600; margin-bottom: 5px; }
    .info-value { font-size: 1.05rem; font-weight: 500; color: #222; margin-bottom: 15px; }
    
    .badge { font-size: 0.85rem; padding: 6px 12px; font-weight: 500; border-radius: 8px; }
    .bg-warning { background-color: #ff9800 !important; color: #fff !important; }
    .bg-success { background-color: #28a745 !important; color: #fff !important; }
    .bg-danger { background-color: #dc3545 !important; color: #fff !important; }
    .bg-info { background-color: #17a2b8 !important; color: #fff !important; }

    /* 🔹 Table */
    .table-cart th {
      background-color: #fcfcfc;
      color: #555;
      font-weight: 600;
      border-bottom: 2px solid #eee;
      padding: 15px 10px;
    }
    .table-cart td { vertical-align: middle; padding: 15px 10px; border-bottom: 1px solid #f8f9fa; }
    .product-img {
      width: 70px; height: 70px; object-fit: contain;
      border-radius: 10px; border: 1px solid #eee; padding: 5px;
    }

    /* 🔹 Buttons */
    .btn-red { background-color: #D10024; color: #fff; border-radius: 50px; font-weight: 500; transition: 0.3s; border: none; }
    .btn-red:hover { background-color: #a5001b; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(209, 0, 36, 0.2); }
    .btn-outline-red { border: 1px solid #D10024; color: #D10024; border-radius: 50px; font-weight: 500; transition: 0.3s; background: #fff; }
    .btn-outline-red:hover { background-color: #D10024; color: #fff; }

    footer { background-color: #fff; color: #6c757d; padding: 20px; font-size: 0.9rem; border-top: 1px solid #eee; margin-top: auto; }

    /* 📱 MOBILE RESPONSIVE (Table to Card) */
    @media (max-width: 768px) {
      /* ปรับตารางสินค้าในมือถือ */
      .table-cart thead { display: none; }
      .table-cart tbody tr {
        display: flex;
        flex-direction: column;
        border: 1px solid #eee;
        border-radius: 15px;
        margin: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      }
      .table-cart tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        padding: 8px 0;
        text-align: right;
      }
      
      /* รูปและชื่อสินค้า (ชิดซ้ายเต็มบรรทัด) */
      .table-cart tbody td:first-child {
        justify-content: flex-start;
        border-bottom: 1px dashed #eee;
        margin-bottom: 10px;
        padding-bottom: 15px;
      }
      
      /* สร้าง Label อัตโนมัติด้วย CSS ::before */
      .table-cart tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
        text-align: left;
      }
      .table-cart tbody td:first-child::before { display: none; } /* ซ่อน Label ช่องแรก */

      /* ส่วนสรุปยอด (tfoot) */
      .table-cart tfoot, .table-cart tfoot tr, .table-cart tfoot td {
        display: block;
        width: 100%;
      }
      .table-cart tfoot tr { border: none; padding: 0 10px; }
      .table-cart tfoot td.label-total { display: none; } 
      .table-cart tfoot td.value-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid #eee;
        padding-top: 15px;
        margin-top: 10px;
        text-align: right !important;
      }
      .table-cart tfoot td.value-total::before {
        content: "ยอดชำระสุทธิ:";
        font-size: 1.1rem;
        color: #6c757d;
        font-weight: 600;
      }

      /* ปรับปุ่มด้านล่างให้เต็มจอในมือถือ */
      .action-buttons {
        flex-direction: column;
        width: 100%;
      }
      .action-buttons a, .action-buttons button {
        width: 100%;
        margin-bottom: 10px;
        text-align: center;
      }
    }
  </style>
</head>
<body>

<?php include("../includes/navbar_user.php"); ?>

<div class="page-wrapper">
  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index:3000;">
    <?php foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $color): ?>
      <?php if (isset($_SESSION["toast_{$key}"])): ?>
        <div class="toast align-items-center text-bg-<?= $color ?> border-0 show shadow-lg" role="alert">
          <div class="d-flex">
            <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION["toast_{$key}"] ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>
        <?php unset($_SESSION["toast_{$key}"]); ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div class="container mt-5">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <h2 class="fw-bold mb-3 mb-md-0" style="color: #D10024;">
        <i class="bi bi-receipt me-2"></i>รายละเอียดคำสั่งซื้อ #<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?>
      </h2>
      <a href="orders.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>กลับหน้าประวัติ</a>
    </div>

    <?php
      // เตรียมข้อมูลสถานะ
      $methodText = ($order['payment_method'] === 'QR') ? 'สแกน QR Code' :
                    (($order['payment_method'] === 'COD') ? 'เก็บเงินปลายทาง (COD)' : htmlspecialchars($order['payment_method']));

      $payment_status = $order['payment_status'] ?? 'รอดำเนินการ';
      $order_status = $order['order_status'] ?? 'รอดำเนินการ';
      
      // ✅ ตรวจสอบการยกเลิก
      $isCancelled = ($order_status === 'ยกเลิก' || $payment_status === 'ยกเลิก');

      $admin_verified = $order['admin_verified'] ?? 'รอตรวจสอบ';
      
      // ✅ ถ้าออเดอร์ถูกยกเลิก ให้เปลี่ยนการตรวจสอบจากแอดมินเป็น "ปฏิเสธ"
      if ($isCancelled) {
          $admin_verified = 'ปฏิเสธ';
      }

      $paymentBadge = ($payment_status === 'ชำระเงินแล้ว') ? 'success' : (($payment_status === 'ยกเลิก') ? 'danger' : 'warning');
      $orderBadge = ($order_status === 'จัดส่งแล้ว' || $order_status === 'สำเร็จ') ? 'success' :
                    (($order_status === 'กำลังจัดเตรียม') ? 'info' : (($order_status === 'ยกเลิก') ? 'danger' : 'warning'));
      $adminBadge = ($admin_verified === 'อนุมัติ') ? 'success' : (($admin_verified === 'กำลังตรวจสอบ') ? 'info' :
                    (($admin_verified === 'ปฏิเสธ') ? 'danger' : 'warning'));
    ?>

    <div class="row">
      <div class="col-lg-7">
        
        <div class="card card-custom">
          <div class="card-header-custom"><i class="bi bi-info-circle me-2 text-muted"></i>สถานะคำสั่งซื้อ</div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-sm-6">
                <div class="info-label">วันที่สั่งซื้อ</div>
                <div class="info-value"><i class="bi bi-calendar-event text-muted me-2"></i><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?> น.</div>
                
                <div class="info-label mt-3">สถานะคำสั่งซื้อ</div>
                <div class="info-value"><span class="badge bg-<?= $orderBadge ?>"><?= htmlspecialchars($order_status) ?></span></div>
                
                <?php if (!empty($order['shipped_date'])): ?>
                  <div class="info-label mt-3">วันที่จัดส่ง</div>
                  <div class="info-value text-success"><i class="bi bi-truck me-2"></i><?= date('d/m/Y H:i', strtotime($order['shipped_date'])) ?> น.</div>
                <?php endif; ?>
              </div>
              <div class="col-sm-6 border-start ps-sm-4 mt-4 mt-sm-0">
                <div class="info-label">วิธีชำระเงิน</div>
                <div class="info-value"><i class="bi bi-credit-card text-muted me-2"></i><?= $methodText ?></div>

                <div class="info-label mt-3">สถานะการชำระเงิน</div>
                <div class="info-value"><span class="badge bg-<?= $paymentBadge ?>"><?= htmlspecialchars($payment_status) ?></span></div>

                <?php if ($order['payment_method'] !== 'COD'): ?>
                  <div class="info-label mt-3">การตรวจสอบโดยแอดมิน</div>
                  <div class="info-value"><span class="badge bg-<?= $adminBadge ?>"><?= htmlspecialchars($admin_verified) ?></span></div>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($payment_status === 'รอดำเนินการ' && !in_array($admin_verified, ['กำลังตรวจสอบ', 'อนุมัติ']) && !$isCancelled): ?>
              <hr class="text-muted opacity-25 my-4">
              <div class="bg-light p-3 rounded-3 border">
                <div class="info-label mb-2"><i class="bi bi-arrow-repeat me-1"></i>ต้องการเปลี่ยนวิธีชำระเงิน?</div>
                <form method="post" class="d-flex gap-2">
                  <select name="new_payment" class="form-select w-auto" required>
                    <option value="COD" <?= $order['payment_method'] === 'COD' ? 'selected' : '' ?>>เก็บเงินปลายทาง</option>
                    <option value="QR" <?= $order['payment_method'] === 'QR' ? 'selected' : '' ?>>ชำระด้วย QR Code</option>
                  </select>
                  <button type="submit" class="btn btn-outline-red px-3">ยืนยัน</button>
                </form>
              </div>
            <?php endif; ?>
            
            <?php if ($order['payment_method'] === 'QR' && $payment_status === 'รอดำเนินการ' && !in_array($admin_verified, ['กำลังตรวจสอบ', 'อนุมัติ']) && $payment_status !== 'ชำระเงินแล้ว' && !$isCancelled): ?>
              <div class="mt-4 text-center">
                <a href="payment/payment_confirm.php?id=<?= $order_id ?>" class="btn btn-warning rounded-pill px-5 py-2 fw-bold text-dark shadow-sm">
                  <i class="bi bi-wallet2 me-2"></i>แจ้งชำระเงิน
                </a>
              </div>
            <?php endif; ?>
            
          </div>
        </div>

      </div>

      <div class="col-lg-5">
        <div class="card card-custom h-100">
          <div class="card-header-custom"><i class="bi bi-person-lines-fill me-2 text-muted"></i>ข้อมูลผู้สั่งซื้อและจัดส่ง</div>
          <div class="card-body p-4 d-flex flex-column">
            
            <?php if (!empty($order['tracking_number'])): ?>
              <div class="alert alert-success d-flex align-items-center shadow-sm mb-4">
                <i class="bi bi-box2-heart fs-3 me-3"></i>
                <div>
                  <div class="small text-muted">หมายเลขพัสดุ (Tracking Number)</div>
                  <strong class="fs-5 tracking-text"><?= htmlspecialchars($order['tracking_number']) ?></strong>
                </div>
              </div>
            <?php endif; ?>

            <div class="bg-light p-4 rounded-4 border flex-grow-1">
              <div class="mb-3">
                <div class="text-muted small fw-semibold mb-1">ชื่อผู้สั่งซื้อ</div>
                <div class="fw-medium text-dark"><i class="bi bi-person me-2 text-muted"></i><?= htmlspecialchars($user['name'] ?? '-') ?></div>
              </div>

              <div class="mb-3">
                <div class="text-muted small fw-semibold mb-1">เบอร์โทรศัพท์</div>
                <div class="fw-medium text-dark"><i class="bi bi-telephone me-2 text-muted"></i><?= htmlspecialchars($user['phone'] ?? '-') ?></div>
              </div>

              <div class="mb-3">
                <div class="text-muted small fw-semibold mb-1">อีเมล</div>
                <div class="fw-medium text-dark"><i class="bi bi-envelope me-2 text-muted"></i><?= htmlspecialchars($user['email'] ?? '-') ?></div>
              </div>

              <div class="mt-4">
                <div class="text-muted small fw-semibold mb-1">ที่อยู่สำหรับจัดส่งพัสดุ</div>
                <div class="fw-medium text-dark" style="line-height: 1.6;">
                  <i class="bi bi-house-door text-danger me-2"></i>
                  <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'ไม่ระบุที่อยู่')) ?>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    <div class="card card-custom mt-2">
      <div class="card-header-custom"><i class="bi bi-cart3 me-2 text-muted"></i>รายการสินค้าที่สั่งซื้อ</div>
      <div class="card-body p-0">
        <div class="table-responsive" style="overflow-x: hidden;"> <table class="table table-cart text-center align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start ps-4">สินค้า</th>
                <th>ราคา/หน่วย</th>
                <th>จำนวน</th>
                <th class="text-end pe-4">ยอดรวม</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($details as $d): 
                $sum = $d['price'] * $d['quantity'];
                $imgPath = "../../admin/uploads/products/" . $d['p_image'];
                if (!file_exists($imgPath) || empty($d['p_image'])) $imgPath = "img/default.png";
              ?>
                <tr>
                  <td data-label="สินค้า" class="text-start ps-md-4">
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= $imgPath ?>" class="product-img bg-white" alt="<?= htmlspecialchars($d['p_name']) ?>">
                      <span class="fw-semibold text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($d['p_name']) ?></span>
                    </div>
                  </td>
                  <td data-label="ราคาต่อหน่วย" class="text-muted"><?= number_format($d['price'], 2) ?> ฿</td>
                  <td data-label="จำนวน" class="fw-medium text-dark"><?= $d['quantity'] ?> ชิ้น</td>
                  <td data-label="ยอดรวม" class="text-end pe-md-4 fw-bold text-dark"><?= number_format($sum, 2) ?> ฿</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <td colspan="3" class="text-end fw-semibold text-muted pt-3 pb-3 label-total">ยอดชำระสุทธิ:</td>
                <td class="text-end pe-md-4 pt-3 pb-3 fw-bold text-danger fs-4 value-total"><?= number_format($order['total_price'], 2) ?> ฿</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4 action-buttons gap-2">
      <?php if ($isCancelled): ?>
        <form method="post" class="m-0 w-100 w-md-auto text-end">
          <input type="hidden" name="reorder" value="1">
          <button type="submit" class="btn btn-red rounded-pill px-4 shadow-sm w-100 w-md-auto">
             <i class="bi bi-cart-plus me-1"></i> สั่งซื้อสินค้าอีกครั้ง
          </button>
        </form>
      <?php elseif ($order_status === 'รอดำเนินการ' && $payment_status !== 'ยกเลิก'): ?>
        <a href="order_cancel.php?id=<?= $order_id ?>" 
           class="btn btn-outline-danger rounded-pill px-4 w-100 w-md-auto"
           onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกคำสั่งซื้อนี้? (ไม่สามารถกู้คืนได้)');">
           <i class="bi bi-x-circle me-1"></i> ยกเลิกคำสั่งซื้อ
        </a>
      <?php endif; ?>
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