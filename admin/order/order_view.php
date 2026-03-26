<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ดึงรหัสออเดอร์จาก URL มาก่อน
$id = $_GET['id'] ?? null;
if (!$id) die("<div class='alert alert-danger text-center mt-5'>❌ ไม่พบคำสั่งซื้อ</div>");

$pageTitle = "รายละเอียดคำสั่งซื้อ #" . htmlspecialchars($id);

ob_start();

include __DIR__ . "/../partials/connectdb.php";

// ✅ อัปเดตสถานะคำสั่งซื้อ / ชำระเงิน
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST['action'] ?? '';

  if ($action === 'update_payment_status') {
    $newPayment = $_POST['payment_status'] ?? '';
    if (in_array($newPayment, ['รอดำเนินการ','ชำระเงินแล้ว','ยกเลิก'])) {
      if ($newPayment === 'ชำระเงินแล้ว') {
        $stmt = $conn->prepare("UPDATE orders SET payment_status=?, admin_verified='อนุมัติ', order_status='กำลังจัดเตรียม' WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
      } elseif ($newPayment === 'ยกเลิก') {
        $stmt = $conn->prepare("UPDATE orders SET payment_status=?, admin_verified='ปฏิเสธ', order_status='ยกเลิก' WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
      } else {
        $stmt = $conn->prepare("UPDATE orders SET payment_status=? WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
      }
      echo "<script>alert('💰 อัปเดตสถานะชำระเงินเรียบร้อย');window.location='order_view.php?id=$id';</script>";
      exit;
    }
  }

  if ($action === 'update_order_status') {
    $newOrder = $_POST['order_status'] ?? '';
    if (in_array($newOrder, ['รอดำเนินการ','กำลังจัดเตรียม','จัดส่งแล้ว','สำเร็จ','ยกเลิก'])) {
      if ($newOrder === 'ยกเลิก') {
          $stmt = $conn->prepare("UPDATE orders SET order_status=?, payment_status='ยกเลิก', admin_verified='ปฏิเสธ' WHERE order_id=?");
          $stmt->execute([$newOrder, $id]);
      } else {
          $stmt = $conn->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
          $stmt->execute([$newOrder, $id]);
      }
      echo "<script>alert('📦 อัปเดตสถานะพัสดุเรียบร้อย');window.location='order_view.php?id=$id';</script>";
      exit;
    }
  }
}

// ✅ ดึงข้อมูล
$sql = "SELECT o.*, c.name AS customer_name, c.phone, c.address FROM orders o LEFT JOIN customers c ON o.customer_id = c.customer_id WHERE o.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$details = $conn->prepare("SELECT d.*, p.p_name, p.p_image FROM order_details d LEFT JOIN product p ON d.p_id = p.p_id WHERE d.order_id=?");
$details->execute([$id]);
$items = $details->fetchAll(PDO::FETCH_ASSOC);

$statusColors = ['รอดำเนินการ'=>'custom-yellow','กำลังจัดเตรียม'=>'custom-blue','จัดส่งแล้ว'=>'custom-blue','สำเร็จ'=>'custom-success','ยกเลิก'=>'danger'];
$verifyColors = ['รอตรวจสอบ'=>'warning text-dark','กำลังตรวจสอบ'=>'purple','อนุมัติ'=>'custom-success','ปฏิเสธ'=>'danger'];
$paymentColors = ['รอดำเนินการ'=>'custom-yellow','ชำระเงินแล้ว'=>'custom-success','ยกเลิก'=>'danger'];
?>

<style>
  .custom-card { background: var(--bg-card, #1e293b); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 15px; }
  .info-label { color: #94a3b8; font-size: 0.95rem; font-weight: 500; width: 140px; display: inline-block; }
  .info-value { color: #f8fafc; font-weight: 500; }
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } 
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } 
  .bg-custom-yellow { background-color: #facc15 !important; color: #0f172a !important; } 
  .badge-fixed { width: 125px; display: inline-block; text-align: center; font-weight: 600; padding: 6px 12px; }
  .table-custom-header { background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important; color: #ffffff !important; border: none; }
  .form-select-custom { background-color: #0f172a; color: #fff; border: 1px solid #334155; border-radius: 8px; }

  /* 📱 ปรับแต่งตารางสินค้าบนมือถือ (แก้บั๊กตัวหนังสือเบียด) */
  @media (max-width: 767px) {
    #productTable thead { display: none; }
    #productTable tbody tr {
      display: block;
      background: rgba(255, 255, 255, 0.02);
      border-radius: 12px;
      margin-bottom: 20px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    #productTable tbody td {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border: none !important;
      padding: 8px 0;
      text-align: right !important;
    }
    /* ดันข้อมูลฝั่งขวาให้ชิดขอบ */
    .mobile-val { flex: 1; text-align: right; color: #fff; font-weight: 500; }
    .mobile-lab { color: #94a3b8; font-weight: 500; margin-right: 15px; min-width: 80px; text-align: left; }

    #productTable tbody td:first-child { justify-content: center; border-bottom: 1px solid rgba(255,255,255,0.05) !important; padding-bottom: 15px; margin-bottom: 10px; }
    
    #productTable tfoot tr { display: block; padding: 15px; background: rgba(22, 163, 74, 0.15); border-radius: 12px; }
    #productTable tfoot td { display: flex; justify-content: space-between; align-items: center; border: none !important; width: 100%; }
    #productTable tfoot td:first-child { display: none; }
    .mobile-total-val { font-size: 1.5rem !important; color: #4ade80 !important; font-weight: 700; }
  }
</style>

<div class="d-flex justify-content-end mb-4 mt-2">
  <a href="orders.php" class="btn btn-outline-light btn-sm rounded-pill px-4 py-2 shadow-sm w-100 w-md-auto text-center hover-scale transition-all">
    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับไปหน้าคำสั่งซื้อ
  </a>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5 col-lg-6">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-body p-4">
        <h5 class="fw-bold text-white mb-4 border-bottom border-secondary pb-2"><i class="bi bi-person-vcard text-info me-2"></i> ข้อมูลลูกค้า</h5>
        <div class="mb-3 d-flex"><span class="info-label">ชื่อลูกค้า:</span><span class="info-value"><?= htmlspecialchars($order['customer_name'] ?? 'ไม่ระบุ') ?></span></div>
        <div class="mb-3 d-flex"><span class="info-label">เบอร์โทรติดต่อ:</span><span class="info-value"><?= htmlspecialchars($order['phone'] ?? '-') ?></span></div>
        <div class="mb-3 d-flex"><span class="info-label">ที่อยู่จัดส่ง:</span><span class="info-value" style="flex:1;"><?= htmlspecialchars($order['address'] ?? '-') ?></span></div>
      </div>
    </div>
  </div>

  <div class="col-xl-7 col-lg-6">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-body p-4">
        <h5 class="fw-bold text-white mb-4 border-bottom border-secondary pb-2"><i class="bi bi-box-seam text-success me-2"></i> การจัดการคำสั่งซื้อ</h5>
        
        <div class="d-flex flex-wrap gap-4 mb-3">
          <div><span class="info-label w-auto me-2">วันที่สั่ง:</span><span class="info-value"><?= date("d/m/y H:i", strtotime($order['order_date'])) ?></span></div>
          <div><span class="info-label w-auto me-2">ชำระโดย:</span><span class="badge bg-secondary"><?= $order['payment_method'] ?></span></div>
        </div>

        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="info-label">สถานะชำระเงิน:</span>
            <span class="badge bg-<?= $paymentColors[$order['payment_status']] ?> badge-fixed"><?= $order['payment_status'] ?></span>
            <form method="post" class="d-flex gap-2 ms-md-auto">
              <input type="hidden" name="action" value="update_payment_status">
              <select name="payment_status" class="form-select form-select-sm form-select-custom w-auto">
                <option value="รอดำเนินการ">รอดำเนินการ</option>
                <option value="ชำระเงินแล้ว">ชำระเงินแล้ว</option>
                <option value="ยกเลิก">ยกเลิก</option>
              </select>
              <button class="btn btn-success btn-sm">ตกลง</button>
            </form>
          </div>

          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="info-label">สถานะพัสดุ:</span>
            <span class="badge bg-<?= $statusColors[$order['order_status']] ?> badge-fixed"><?= $order['order_status'] ?></span>
            <form method="post" class="d-flex gap-2 ms-md-auto">
              <input type="hidden" name="action" value="update_order_status">
              <select name="order_status" class="form-select form-select-sm form-select-custom w-auto">
                <option value="รอดำเนินการ">รอดำเนินการ</option>
                <option value="กำลังจัดเตรียม">กำลังจัดเตรียม</option>
                <option value="จัดส่งแล้ว">จัดส่งแล้ว</option>
                <option value="สำเร็จ">สำเร็จ</option>
                <option value="ยกเลิก">ยกเลิก</option>
              </select>
              <button class="btn btn-info btn-sm">ตกลง</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card custom-card shadow-lg">
  <div class="p-4 border-bottom border-secondary"><h5 class="text-white mb-0"><i class="bi bi-basket2 text-warning me-2"></i> รายการสินค้า</h5></div>
  <div class="table-responsive">
    <table id="productTable" class="table table-dark align-middle mb-0 w-100">
      <thead>
        <tr class="table-custom-header text-center">
          <th class="ps-4">รูปภาพ</th>
          <th class="text-start">ชื่อสินค้า</th>
          <th>จำนวน</th>
          <th class="text-end">ราคา/ชิ้น</th>
          <th class="text-end pe-4">ยอดรวม</th>
        </tr>
      </thead>
      <tbody>
        <?php $total = 0; foreach ($items as $it): $total += $it['subtotal']; ?>
        <tr>
          <td class="text-md-center ps-md-4">
            <img src="/Project/admin/uploads/<?= htmlspecialchars($it['p_image'] ?? 'noimg.png') ?>" width="70" class="rounded border border-secondary shadow-sm">
          </td>
          <td>
            <div class="mobile-lab d-md-none">สินค้า</div>
            <div class="mobile-val text-start text-white fw-bold"><?= htmlspecialchars($it['p_name']) ?></div>
          </td>
          <td>
            <div class="mobile-lab d-md-none">จำนวน</div>
            <div class="mobile-val"><span class="badge bg-secondary px-3"><?= (int)$it['quantity'] ?></span></div>
          </td>
          <td>
            <div class="mobile-lab d-md-none">ราคา/ชิ้น</div>
            <div class="mobile-val text-white">฿<?= number_format($it['price'], 2) ?></div>
          </td>
          <td class="pe-md-4">
            <div class="mobile-lab d-md-none">ยอดรวม</div>
            <div class="mobile-val text-info fw-bold">฿<?= number_format($it['subtotal'], 2) ?></div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="text-white">
          <td colspan="3" class="d-none d-md-table-cell"></td>
          <td class="text-end fw-bold fs-5 pt-4 pb-4">ยอดรวมทั้งหมด:</td>
          <td class="text-end fw-bold text-success fs-3 pe-md-4 pt-4 pb-4 mobile-total-val">฿<?= number_format($total, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>