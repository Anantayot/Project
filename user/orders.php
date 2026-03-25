<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("connectdb.php");

if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ดึงเฉพาะออเดอร์ของลูกค้าคนนี้ (เรียงจากล่าสุดไปเก่าสุด)
$sql = "SELECT * FROM orders WHERE customer_id = :cid ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':cid', $customer_id, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติคำสั่งซื้อ | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    
    .orders-wrapper { min-height: 80vh; padding-bottom: 50px; }

    /* 🔹 Table Design */
    .card-table {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      background: #fff;
      overflow: hidden;
    }
    .table { margin-bottom: 0; }
    .table thead th {
      background-color: #fcfcfc;
      color: #555;
      font-weight: 600;
      border-bottom: 2px solid #eee;
      padding: 15px 10px;
    }
    .table tbody td {
      vertical-align: middle;
      padding: 15px 10px;
      border-bottom: 1px solid #f8f9fa;
    }
    .table-hover tbody tr:hover { background-color: #fafafa; }

    /* 🔹 Buttons */
    .btn-custom-outline {
      border: 1px solid #D10024;
      color: #D10024;
      border-radius: 50px;
      font-weight: 500;
      padding: 6px 15px;
      transition: 0.3s;
      background: #fff;
    }
    .btn-custom-outline:hover {
      background-color: #D10024;
      color: #fff;
      box-shadow: 0 4px 10px rgba(209, 0, 36, 0.15);
    }
    
    .btn-custom-warning {
      background-color: #ffc107;
      color: #000;
      border-radius: 50px;
      font-weight: 600;
      padding: 6px 15px;
      border: none;
      transition: 0.3s;
    }
    .btn-custom-warning:hover {
      background-color: #e0a800;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(255, 193, 7, 0.2);
    }

    /* 🔹 Badges */
    .badge {
      font-size: 0.85rem;
      padding: 6px 12px;
      font-weight: 500;
      border-radius: 8px;
    }
    .bg-soft-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .bg-soft-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .bg-soft-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .bg-soft-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

    /* ยกเลิกแถว */
    .row-cancelled td { opacity: 0.6; }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      margin-top: auto;
    }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="orders-wrapper">
  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index:3000;">
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $color): ?>
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
    
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="fw-bold mb-0" style="color: #D10024;"><i class="bi bi-box-seam me-2"></i>ประวัติคำสั่งซื้อ</h2>
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="bi bi-shop me-1"></i>กลับหน้าร้าน</a>
    </div>

    <?php if (empty($orders)): ?>
      <div class="card card-table p-5 text-center">
        <i class="bi bi-receipt text-muted opacity-50" style="font-size: 5rem;"></i>
        <h4 class="mt-3 fw-bold text-secondary">ยังไม่มีประวัติคำสั่งซื้อ</h4>
        <p class="text-muted">คุณยังไม่เคยทำการสั่งซื้อสินค้าใดๆ ในระบบ</p>
        <div class="mt-3">
          <a href="index.php" class="btn btn-custom-outline px-4 py-2"><i class="bi bi-cart me-2"></i>เริ่มช้อปปิ้งเลย</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card card-table">
        <div class="table-responsive">
          <table class="table table-hover align-middle text-center">
            <thead>
              <tr>
                <th class="text-start ps-4">หมายเลขคำสั่งซื้อ</th>
                <th>วันที่สั่งซื้อ</th>
                <th>วิธีชำระเงิน</th>
                <th>ยอดชำระสุทธิ</th>
                <th>การชำระเงิน</th>
                <th>สถานะจัดส่ง</th>
                <th class="text-end pe-4">การจัดการ</th>
              </tr>
            </thead>
            <tbody>
            <?php 
              foreach ($orders as $o): 
                $status = $o['payment_status'] ?? 'รอดำเนินการ';
                $order_status = $o['order_status'] ?? 'รอดำเนินการ';
                $admin_verified = $o['admin_verified'] ?? 'รอตรวจสอบ';

                /* ===== สีสถานะการชำระเงิน ===== */
                $payBadge = 'bg-soft-warning';
                if ($status === 'ชำระเงินแล้ว') $payBadge = 'bg-soft-success';
                if ($status === 'ยกเลิก') $payBadge = 'bg-soft-danger';

                /* ===== สีสถานะคำสั่งซื้อ ===== */
                $orderBadge = 'bg-soft-warning';
                if ($order_status === 'กำลังจัดเตรียม') $orderBadge = 'bg-soft-info';
                if ($order_status === 'จัดส่งแล้ว' || $order_status === 'สำเร็จ') $orderBadge = 'bg-soft-success';
                if ($order_status === 'ยกเลิก') $orderBadge = 'bg-soft-danger';

                /* ===== แปลงวิธีชำระเงิน ===== */
                $methodText = htmlspecialchars($o['payment_method']);
                $methodIcon = 'bi-credit-card';
                if ($o['payment_method'] === 'QR') {
                  $methodText = 'QR Code';
                  $methodIcon = 'bi-qr-code-scan text-primary';
                } elseif ($o['payment_method'] === 'COD') {
                  $methodText = 'ปลายทาง (COD)';
                  $methodIcon = 'bi-cash-coin text-success';
                }

                $isCancelled = ($order_status === 'ยกเลิก' || $status === 'ยกเลิก');
                $rowClass = $isCancelled ? 'row-cancelled bg-light' : '';
            ?>
              <tr class="<?= $rowClass ?>">
                <td class="text-start ps-4 fw-bold text-dark">
                  #<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?>
                </td>
                <td class="text-muted small">
                  <?= date('d/m/Y', strtotime($o['order_date'])) ?><br>
                  <?= date('H:i', strtotime($o['order_date'])) ?> น.
                </td>
                <td>
                  <i class="bi <?= $methodIcon ?> me-1"></i> <?= $methodText ?>
                </td>
                <td class="fw-bold text-danger">
                  <?= number_format($o['total_price'], 2) ?> ฿
                </td>
                <td>
                  <span class="badge <?= $payBadge ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?= $orderBadge ?>">
                    <?= htmlspecialchars($order_status) ?>
                  </span>
                </td>
                <td class="text-end pe-4">
                  <div class="d-flex justify-content-end gap-2">
                    
                    <?php if (
                      $o['payment_method'] === 'QR' &&
                      $status === 'รอดำเนินการ' &&
                      !in_array($admin_verified, ['กำลังตรวจสอบ', 'อนุมัติ']) &&
                      !$isCancelled
                    ): ?>
                      <a href="payment_confirm.php?id=<?= $o['order_id'] ?>" class="btn btn-custom-warning btn-sm" title="แจ้งชำระเงิน">
                        <i class="bi bi-wallet2 me-1"></i> แจ้งโอน
                      </a>
                    <?php endif; ?>

                    <a href="order_detail.php?id=<?= $o['order_id'] ?>" class="btn btn-custom-outline btn-sm" title="ดูรายละเอียด">
                      รายละเอียด
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
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