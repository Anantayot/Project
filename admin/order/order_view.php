<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "รายละเอียดคำสั่งซื้อ";
ob_start();

include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) die("❌ ไม่พบคำสั่งซื้อ");

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

if (!$order) die("❌ ไม่พบข้อมูลคำสั่งซื้อในฐานข้อมูล");

// ✅ ดึงรายละเอียดสินค้า
$details = $conn->prepare("SELECT d.*, p.p_name, p.p_image 
                           FROM order_details d
                           LEFT JOIN product p ON d.p_id = p.p_id
                           WHERE d.order_id=?");
$details->execute([$id]);
$items = $details->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
/* 🎨 ปรับสี badge ใหม่ */
.badge-status {
  color: #fff;
  font-weight: 600;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 0.9rem;
}
.bg-waiting { background-color: #f0ad4e; }     /* เหลือง - รอดำเนินการ / รอตรวจสอบ */
.bg-approve { background-color: #28a745; }     /* เขียว - อนุมัติ */
.bg-reject { background-color: #D10024; }      /* แดงหลักของระบบ MyCommiss */
.bg-progress { background-color: #0dcaf0; }    /* ฟ้า - กำลังจัดเตรียม / จัดส่งแล้ว */
.bg-complete { background-color: #198754; }    /* เขียวเข้ม - สำเร็จ */
.bg-default { background-color: #6c757d; }     /* เทา - อื่นๆ */
</style>


<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-receipt"></i> รายละเอียดคำสั่งซื้อ #<?= htmlspecialchars($order['order_id']) ?>
</h3>

<!-- 🔹 ข้อมูลลูกค้า -->
<div class="card p-4 shadow-lg border-0 mb-4" style="background:linear-gradient(145deg,#161b22,#0e1116);color:#fff;">
  <div class="row">
    <div class="col-md-6">
      <h5 class="fw-bold text-success"><i class="bi bi-person-circle"></i> ข้อมูลลูกค้า</h5>
      <p><b>ชื่อ:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
      <p><b>เบอร์โทร:</b> <?= htmlspecialchars($order['phone']) ?></p>
      <p><b>ที่อยู่:</b> <?= htmlspecialchars($order['address']) ?></p>
    </div>

    <div class="col-md-6">
      <h5 class="fw-bold text-info"><i class="bi bi-clipboard-data"></i> ข้อมูลคำสั่งซื้อ</h5>
      <p><b>วันที่สั่งซื้อ:</b> <?= date("d/m/Y", strtotime($order['order_date'])) ?></p>

      <!-- ✅ ช่องทางการชำระเงิน -->
      <?php 
        $method = $order['payment_method'];
        $methodText = ($method === 'QR') ? 'ชำระด้วย QR Code' :
                      (($method === 'COD') ? 'เก็บเงินปลายทาง' : htmlspecialchars($method));
      ?>
      <p><b>ช่องทางการชำระเงิน:</b> <?= $methodText ?></p>

      <!-- ✅ สถานะชำระเงิน -->
<p><b>สถานะชำระเงิน:</b>
  <?php 
    $payStatus = $order['payment_status'] ?? 'รอดำเนินการ';
    // กำหนดสีและไอคอน
    switch ($payStatus) {
      case 'ชำระเงินแล้ว':
        $payColor = 'approve';
        $payIcon = '';
        break;
      case 'ยกเลิก':
        $payColor = 'reject';
        $payIcon = '';
        break;
      case 'รอดำเนินการ':
      default:
        $payColor = 'waiting';
        $payIcon = '';
        break;
    }
  ?>
  <span class="badge-status bg-<?= $payColor ?>">
    <?= $payIcon . ' ' . htmlspecialchars($payStatus) ?>
  </span>
</p>


      <!-- ✅ เปลี่ยนสถานะชำระเงิน -->
      <form method="post" class="d-flex gap-2 mb-3">
        <input type="hidden" name="action" value="update_payment_status">
        <select name="payment_status" class="form-select form-select-sm w-auto bg-dark text-light border-secondary">
          <option value="รอดำเนินการ" <?= $order['payment_status']=='รอดำเนินการ'?'selected':'' ?>>รอดำเนินการ</option>
          <option value="ชำระเงินแล้ว" <?= $order['payment_status']=='ชำระเงินแล้ว'?'selected':'' ?>>ชำระเงินแล้ว</option>
          <option value="ยกเลิก" <?= $order['payment_status']=='ยกเลิก'?'selected':'' ?>>ยกเลิก</option>
        </select>
        <button type="submit" class="btn btn-outline-light btn-sm">💰 บันทึก</button>
      </form>

      <!-- ✅ ตรวจสอบโดยแอดมิน -->
<?php if ($order['payment_method'] !== 'COD'): ?>
<p><b>ตรวจสอบโดยแอดมิน:</b>
  <?php 
    $adminStatus = $order['admin_verified'] ?? 'รอตรวจสอบ';
    switch ($adminStatus) {
      case 'อนุมัติ':
        $adminColor = 'approve';
        $icon = '';
        break;
      case 'ปฏิเสธ':
        $adminColor = 'reject';
        $icon = '';
        break;
      case 'กำลังตรวจสอบ':
        $adminColor = 'progress';
        $icon = '';
        break;
      default:
        $adminColor = 'waiting';
        $icon = '';
    }
  ?>
  <span class="badge-status bg-<?= $adminColor ?>">
    <?= $icon . ' ' . htmlspecialchars($adminStatus) ?>
  </span>
</p>
<?php endif; ?>

<!-- ✅ สถานะคำสั่งซื้อ -->
<p><b>สถานะคำสั่งซื้อ:</b>
  <?php 
    $status = $order['order_status'] ?? 'รอดำเนินการ';
    switch ($status) {
      case 'สำเร็จ':
        $statusColor = 'complete';
        $icon = '';
        break;
      case 'กำลังจัดเตรียม':
        $statusColor = 'progress';
        $icon = '';
        break;
      case 'จัดส่งแล้ว':
        $statusColor = 'progress';
        $icon = '';
        break;
      case 'ยกเลิก':
        $statusColor = 'reject';
        $icon = '';
        break;
      case 'รอดำเนินการ':
        $statusColor = 'waiting';
        $icon = '';
        break;
      default:
        $statusColor = 'default';
        $icon = '⚙️';
    }
  ?>
  <span class="badge-status bg-<?= $statusColor ?>">
    <?= $icon . ' ' . htmlspecialchars($status) ?>
  </span>
</p>


      <!-- ✅ ปุ่มเปลี่ยนสถานะคำสั่งซื้อ -->
      <form method="post" class="d-flex gap-2">
        <input type="hidden" name="action" value="update_order_status">
        <select name="order_status" class="form-select form-select-sm w-auto bg-dark text-light border-secondary">
          <option value="รอดำเนินการ" <?= $order['order_status']=='รอดำเนินการ'?'selected':'' ?>>รอดำเนินการ</option>
          <option value="กำลังจัดเตรียม" <?= $order['order_status']=='กำลังจัดเตรียม'?'selected':'' ?>>กำลังจัดเตรียม</option>
          <option value="จัดส่งแล้ว" <?= $order['order_status']=='จัดส่งแล้ว'?'selected':'' ?>>จัดส่งแล้ว</option>
          <option value="สำเร็จ" <?= $order['order_status']=='สำเร็จ'?'selected':'' ?>>สำเร็จ</option>
          <option value="ยกเลิก" <?= $order['order_status']=='ยกเลิก'?'selected':'' ?>>ยกเลิก</option>
        </select>
        <button type="submit" class="btn btn-outline-light btn-sm">📝 บันทึก</button>
      </form>

      <!-- 🔹 สลิป -->
      <?php if (!empty($order['slip_image']) && $order['payment_method'] !== 'COD'): ?>
        <p class="mt-3"><b>หลักฐานการชำระเงิน:</b></p>
        <a href="../../admin/uploads/slips/<?= htmlspecialchars($order['slip_image']) ?>" 
           target="_blank" class="btn btn-outline-light btn-sm">
          🧾 ดูรูปสลิป
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- 🔹 รายการสินค้า -->
<div class="card p-3 shadow-lg border-0" style="background:#161b22;">
  <h5 class="fw-bold text-white mb-3"><i class="bi bi-basket2"></i> รายการสินค้า</h5>
  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle text-center mb-0">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>รูป</th>
          <th>สินค้า</th>
          <th>จำนวน</th>
          <th>ราคา (฿)</th>
          <th>รวม (฿)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $totalSum = 0;
        foreach ($items as $i => $it): 
          $totalSum += $it['subtotal'];
        ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><img src="../../admin/uploads/<?= htmlspecialchars($it['p_image'] ?? 'noimg.png') ?>" width="50" class="rounded"></td>
          <td class="text-start"><?= htmlspecialchars($it['p_name']) ?></td>
          <td><?= (int)$it['quantity'] ?></td>
          <td><?= number_format($it['price'], 2) ?></td>
          <td><?= number_format($it['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 🔹 ยอดรวม -->
<div class="text-end mt-4">
  <h4 class="fw-bold text-success">
    <i class="bi bi-cash-stack"></i> ยอดรวมทั้งหมด: <?= number_format(array_sum(array_column($items,'subtotal')), 2) ?> ฿
  </h4>

  <a href="orders.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> กลับ
  </a>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
