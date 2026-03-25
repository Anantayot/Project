<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ดึงรหัสออเดอร์จาก URL มาก่อน
$id = $_GET['id'] ?? null;
if (!$id) die("<div class='alert alert-danger text-center mt-5'>❌ ไม่พบคำสั่งซื้อ</div>");

// ✅ เปลี่ยนให้แสดงรหัสออเดอร์บน Topbar (เมนูด้านบน)
$pageTitle = "<i class='bi bi-receipt-cutoff me-2 text-success'></i> รายละเอียดคำสั่งซื้อ <span class='text-success'>#" . htmlspecialchars($id) . "</span>";

ob_start();

include __DIR__ . "/../partials/connectdb.php";

// ✅ อัปเดตสถานะคำสั่งซื้อ / ชำระเงิน
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST['action'] ?? '';

  // อนุมัติ/ปฏิเสธการชำระเงิน (เดิม)
  if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE orders 
                            SET payment_status='ชำระเงินแล้ว', 
                                admin_verified='อนุมัติ',
                                order_status='กำลังจัดเตรียม'
                            WHERE order_id=?");
    $stmt->execute([$id]);
    echo "<script>alert('✅ อนุมัติการชำระเงินเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
    exit;

  } elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE orders 
                            SET payment_status='ยกเลิก', 
                                admin_verified='ปฏิเสธ',
                                order_status='ยกเลิก'
                            WHERE order_id=?");
    $stmt->execute([$id]);
    echo "<script>alert('❌ ปฏิเสธคำสั่งซื้อนี้แล้ว');window.location='order_view.php?id=$id';</script>";
    exit;
  }

  // ✅ เปลี่ยนสถานะชำระเงิน (ใหม่)
  if ($action === 'update_payment_status') {
    $newPayment = $_POST['payment_status'] ?? '';

    if (in_array($newPayment, ['รอดำเนินการ','ชำระเงินแล้ว','ยกเลิก'])) {

      // ถ้าเลือก "ชำระเงินแล้ว" → อัปเดต admin_verified = 'อนุมัติ' ด้วย
      if ($newPayment === 'ชำระเงินแล้ว') {
        $stmt = $conn->prepare("UPDATE orders 
                                SET payment_status=?, 
                                    admin_verified='อนุมัติ',
                                    order_status='กำลังจัดเตรียม'
                                WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
        echo "<script>alert('💰 ชำระเงินแล้ว → แอดมินอนุมัติ + กำลังจัดเตรียมเรียบร้อย');window.location='order_view.php?id=$id';</script>";
        exit;
      }

      // ถ้าเป็นสถานะอื่น (เช่น ยกเลิก / รอดำเนินการ)
      $stmt = $conn->prepare("UPDATE orders SET payment_status=? WHERE order_id=?");
      $stmt->execute([$newPayment, $id]);
      echo "<script>alert('💰 เปลี่ยนสถานะชำระเงินเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
      exit;
    }
  }

  // ✅ เปลี่ยนสถานะคำสั่งซื้อ (ใหม่)
  if ($action === 'update_order_status') {
    $newOrder = $_POST['order_status'] ?? '';
    if (in_array($newOrder, ['รอดำเนินการ','กำลังจัดเตรียม','จัดส่งแล้ว','สำเร็จ','ยกเลิก'])) {
      $stmt = $conn->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
      $stmt->execute([$newOrder, $id]);
      echo "<script>alert('📦 เปลี่ยนสถานะคำสั่งซื้อเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
      exit;
    }
  }
}

// ✅ ดึงข้อมูลคำสั่งซื้อ
$sql = "SELECT o.*, c.name AS customer_name, c.phone, c.address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) die("<div class='alert alert-danger text-center mt-5'>❌ ไม่พบข้อมูลคำสั่งซื้อในฐานข้อมูล</div>");

// ✅ ดึงรายละเอียดสินค้า
$details = $conn->prepare("SELECT d.*, p.p_name, p.p_image 
                           FROM order_details d
                           LEFT JOIN product p ON d.p_id = p.p_id
                           WHERE d.order_id=?");
$details->execute([$id]);
$items = $details->fetchAll(PDO::FETCH_ASSOC);

/* --- Config สีสถานะให้ตรงกับหน้า Dashboard --- */
$statusColors = [
    'รอดำเนินการ'    => 'custom-yellow', 
    'กำลังจัดเตรียม'  => 'custom-blue',   
    'จัดส่งแล้ว'      => 'custom-blue',       
    'สำเร็จ'         => 'custom-success',       
    'ยกเลิก'         => 'danger'         
];
$verifyColors = [
    'รอตรวจสอบ'     => 'warning text-dark',
    'กำลังตรวจสอบ'   => 'purple',
    'อนุมัติ'         => 'custom-success', 
    'ปฏิเสธ'         => 'danger'
];
$paymentColors = [
    'รอดำเนินการ'    => 'custom-yellow',
    'ชำระเงินแล้ว'    => 'custom-success',
    'ยกเลิก'         => 'danger'
];
?>

<style>
  /* 🎨 ตกแต่งให้เข้ากับหน้า Dashboard หลัก */
  .custom-card {
    background: var(--bg-card, #1e293b);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
  }
  .info-label {
    color: #94a3b8;
    font-size: 0.95rem;
    font-weight: 500;
    width: 140px;
    display: inline-block;
  }
  .info-value {
    color: #f8fafc;
    font-weight: 500;
  }
  
  /* สี Custom Badges */
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } 
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } 
  .bg-custom-yellow { background-color: #facc15 !important; color: #0f172a !important; } 

  .badge-fixed {
    width: 125px;
    display: inline-block;
    text-align: center;
    font-weight: 600;
    padding: 6px 12px;
  }

  /* ตารางสินค้า */
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #ffffff !important;
    border-bottom: none;
  }
  .table-dark { --bs-table-bg: transparent; --bs-table-color: #e2e8f0; border-color: rgba(255, 255, 255, 0.05); }
  
  /* ฟอร์ม Dropdown */
  .form-select-custom {
    background-color: #0f172a;
    color: #fff;
    border: 1px solid #334155;
    border-radius: 8px;
  }
  .form-select-custom:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
  }
</style>

<div class="d-flex justify-content-end mb-4 mt-2">
  <a href="orders.php" class="btn btn-outline-light btn-sm rounded-pill px-4 py-2 shadow-sm">
    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับไปหน้าคำสั่งซื้อ
  </a>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5 col-lg-6">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-body p-4">
        <h5 class="fw-bold text-white mb-4 border-bottom border-secondary pb-2">
          <i class="bi bi-person-vcard text-info me-2"></i> ข้อมูลลูกค้า
        </h5>
        
        <div class="mb-3 d-flex">
          <span class="info-label">ชื่อลูกค้า:</span>
          <span class="info-value"><?= htmlspecialchars($order['customer_name'] ?? 'ไม่ระบุ') ?></span>
        </div>
        <div class="mb-3 d-flex">
          <span class="info-label">เบอร์โทรติดต่อ:</span>
          <span class="info-value"><?= htmlspecialchars($order['phone'] ?? '-') ?></span>
        </div>
        <div class="mb-3 d-flex">
          <span class="info-label">ที่อยู่จัดส่ง:</span>
          <span class="info-value" style="line-height: 1.6; flex: 1;"><?= htmlspecialchars($order['address'] ?? '-') ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-7 col-lg-6">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-body p-4">
        <h5 class="fw-bold text-white mb-4 border-bottom border-secondary pb-2">
          <i class="bi bi-box-seam text-success me-2"></i> ข้อมูลและการจัดการคำสั่งซื้อ
        </h5>

        <div class="row">
          <div class="col-md-6 mb-3">
            <span class="info-label">วันที่สั่งซื้อ:</span>
            <span class="info-value"><?= date("d/m/Y H:i", strtotime($order['order_date'])) ?></span>
          </div>
          <div class="col-md-6 mb-3">
            <?php 
              $method = $order['payment_method'];
              $methodText = ($method === 'QR') ? 'ชำระด้วย QR Code' : (($method === 'COD') ? 'เก็บเงินปลายทาง' : htmlspecialchars($method));
            ?>
            <span class="info-label">ช่องทางชำระเงิน:</span>
            <span class="badge bg-secondary rounded-pill px-3"><?= $methodText ?></span>
          </div>
        </div>

        <hr class="border-secondary my-3" style="opacity: 0.3;">

        <div class="d-flex flex-column flex-md-row align-items-md-center mb-3 gap-2">
          <span class="info-label mb-1 mb-md-0">สถานะชำระเงิน:</span>
          <?php 
            $payStatus = $order['payment_status'] ?? 'รอดำเนินการ';
            $payClass = $paymentColors[$payStatus] ?? 'secondary';
          ?>
          <span class="badge bg-<?= $payClass ?> rounded-pill badge-fixed me-md-3 mb-2 mb-md-0"><?= htmlspecialchars($payStatus) ?></span>
          
          <form method="post" class="d-flex gap-2 w-100" style="max-width: 300px;">
            <input type="hidden" name="action" value="update_payment_status">
            <select name="payment_status" class="form-select form-select-sm form-select-custom">
              <option value="รอดำเนินการ" <?= $payStatus=='รอดำเนินการ'?'selected':'' ?>>รอดำเนินการ</option>
              <option value="ชำระเงินแล้ว" <?= $payStatus=='ชำระเงินแล้ว'?'selected':'' ?>>ชำระเงินแล้ว</option>
              <option value="ยกเลิก" <?= $payStatus=='ยกเลิก'?'selected':'' ?>>ยกเลิก</option>
            </select>
            <button type="submit" class="btn btn-outline-success btn-sm px-3 rounded-3">บันทึก</button>
          </form>
        </div>

        <?php if ($order['payment_method'] !== 'COD'): ?>
        <div class="d-flex align-items-center mb-3">
          <span class="info-label">ตรวจสอบสลิป:</span>
          <?php 
            $adminStatus = $order['admin_verified'] ?? 'รอตรวจสอบ';
            $adminClass = $verifyColors[$adminStatus] ?? 'secondary';
          ?>
          <span class="badge bg-<?= $adminClass ?> rounded-pill badge-fixed"><?= htmlspecialchars($adminStatus) ?></span>
          
          <?php if (!empty($order['slip_image'])): ?>
            <a href="../../admin/uploads/slips/<?= htmlspecialchars($order['slip_image']) ?>" target="_blank" class="btn btn-sm btn-outline-info ms-3 rounded-pill px-3">
              <i class="bi bi-image me-1"></i> ดูสลิป
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row align-items-md-center mb-2 gap-2">
          <span class="info-label mb-1 mb-md-0">สถานะจัดส่ง:</span>
          <?php 
            $orderStatus = $order['order_status'] ?? 'รอดำเนินการ';
            $orderClass = $statusColors[$orderStatus] ?? 'secondary';
          ?>
          <span class="badge bg-<?= $orderClass ?> rounded-pill badge-fixed me-md-3 mb-2 mb-md-0"><?= htmlspecialchars($orderStatus) ?></span>
          
          <form method="post" class="d-flex gap-2 w-100" style="max-width: 300px;">
            <input type="hidden" name="action" value="update_order_status">
            <select name="order_status" class="form-select form-select-sm form-select-custom">
              <option value="รอดำเนินการ" <?= $orderStatus=='รอดำเนินการ'?'selected':'' ?>>รอดำเนินการ</option>
              <option value="กำลังจัดเตรียม" <?= $orderStatus=='กำลังจัดเตรียม'?'selected':'' ?>>กำลังจัดเตรียม</option>
              <option value="จัดส่งแล้ว" <?= $orderStatus=='จัดส่งแล้ว'?'selected':'' ?>>จัดส่งแล้ว</option>
              <option value="สำเร็จ" <?= $orderStatus=='สำเร็จ'?'selected':'' ?>>สำเร็จ</option>
              <option value="ยกเลิก" <?= $orderStatus=='ยกเลิก'?'selected':'' ?>>ยกเลิก</option>
            </select>
            <button type="submit" class="btn btn-outline-info btn-sm px-3 rounded-3">บันทึก</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="card custom-card shadow-lg mb-4">
  <div class="card-body p-0">
    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
      <h5 class="fw-bold text-white mb-0"><i class="bi bi-basket2 me-2 text-warning"></i> รายการสินค้าที่สั่งซื้อ</h5>
    </div>
    
    <div class="table-responsive">
      <table class="table table-dark align-middle text-center mb-0 border-0">
        <thead>
          <tr class="table-custom-header">
            <th class="py-3">#</th>
            <th class="py-3 text-start">รูปภาพ</th>
            <th class="py-3 text-start">ชื่อสินค้า</th>
            <th class="py-3">จำนวน</th>
            <th class="py-3 text-end">ราคา/ชิ้น</th>
            <th class="py-3 text-end pe-4">ยอดรวม</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $totalSum = 0;
          foreach ($items as $i => $it): 
            $totalSum += $it['subtotal'];
          ?>
          <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
            <td class="text-muted"><?= $i + 1 ?></td>
            <td class="text-start py-3">
              <img src="../../admin/uploads/<?= htmlspecialchars($it['p_image'] ?? 'noimg.png') ?>" width="60" class="rounded shadow-sm" style="object-fit: cover; aspect-ratio: 1/1;">
            </td>
            <td class="text-start fw-medium text-white"><?= htmlspecialchars($it['p_name']) ?></td>
            <td><span class="badge bg-secondary rounded-pill px-3"><?= (int)$it['quantity'] ?></span></td>
            <td class="text-end text-muted">฿<?= number_format($it['price'], 2) ?></td>
            <td class="text-end text-info fw-bold pe-4">฿<?= number_format($it['subtotal'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="border-0"></td>
            <td class="text-end fw-bold text-white fs-5 border-0 pt-4 pb-4">ยอดรวมทั้งหมด:</td>
            <td class="text-end fw-bold text-success fs-4 border-0 pe-4 pt-4 pb-4">฿<?= number_format($totalSum, 2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>