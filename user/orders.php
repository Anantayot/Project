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

    /* 🔹 Table Design (Desktop) */
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
    .badge.bg-warning { background-color: #ff9800 !important; color: #fff !important; }
    .badge.bg-success { background-color: #28a745 !important; color: #fff !important; }
    .badge.bg-danger { background-color: #dc3545 !important; color: #fff !important; }
    .badge.bg-info { background-color: #17a2b8 !important; color: #fff !important; }

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

    /* 📱 MOBILE RESPONSIVE (แปลง Table เป็น Card) */
    @media (max-width: 768px) {
      .table-hover thead { display: none; } /* ซ่อนหัวตารางบนมือถือ */
      
      .table-hover tbody { display: block; padding: 10px; }
      
      .table-hover tbody tr {
        display: block;
        border: 1px solid #eaeaea;
        border-radius: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
      }
      
      .table-hover tbody tr.row-cancelled {
        background-color: #fafafa; /* ออเดอร์ยกเลิกให้สีทึมลงนิดนึง */
      }
      
      .table-hover tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        padding: 10px 0 !important;
        text-align: right;
      }
      
      /* สร้าง Label ก่อนหน้าข้อมูล (ดึงจาก data-label) */
      .table-hover tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
        text-align: left;
      }
      
      /* แถวแรก (หมายเลขคำสั่งซื้อ) */
      .table-hover tbody td:first-child {
        border-bottom: 1px dashed #eee;
        margin-bottom: 10px;
        padding-bottom: 15px !important;
        font-size: 1.1rem;
      }

      /* แถวสุดท้าย (ปุ่มการจัดการ) */
      .table-hover tbody td:last-child {
        flex-direction: column;
        align-items: stretch;
        margin-top: 15px;
        padding-top: 15px !important;
        border-top: 1px solid #eee;
      }
      .table-hover tbody td:last-child::before {
        display: none; /* ซ่อน Label การจัดการ */
      }
      
      /* ขยายปุ่มให้เต็มหน้าจอมือถือ */
      .table-hover tbody td .d-flex {
        width: 100%;
        justify-content: space-between;
      }
      .table-hover tbody td .btn {
        flex: 1;
        margin: 0 5px;
        text-align: center;
      }
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
    
    <div class="text-center mb-4">
      <h2 class="fw-bold mb-0" style="color: #D10024;"><i class="bi bi-box-seam me-2"></i>ประวัติคำสั่งซื้อ</h2>
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
        <div class="table-responsive" style="overflow-x: hidden;"> <table class="table table-hover align-middle text-center">
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

                /* ===== สีสถานะ ===== */
                $payBadge = 'bg-warning';
                if ($status === 'ชำระเงินแล้ว') $payBadge = 'bg-success';
                if ($status === 'ยกเลิก') $payBadge = 'bg-danger';

                $orderBadge = 'bg-warning';
                if ($order_status === 'กำลังจัดเตรียม') $orderBadge = 'bg-info';
                if ($order_status === 'จัดส่งแล้ว' || $order_status === 'สำเร็จ') $orderBadge = 'bg-success';
                if ($order_status === 'ยกเลิก') $orderBadge = 'bg-danger';

                /* ===== วิธีชำระเงิน ===== */
                $methodText = htmlspecialchars($o['payment_method']);
                $methodIcon = 'bi-credit-card';
                if ($o['payment_method'] === 'QR') {
                  $methodText = 'QR Code';
                  $methodIcon = 'bi-qr-code-scan text-primary';
                } elseif ($o['payment_method'] === 'COD') {
                  $methodText = 'ชำระเงินปลายทาง';
                  $methodIcon = 'bi-cash-coin text-success';
                }

                $isCancelled = ($order_status === 'ยกเลิก' || $status === 'ยกเลิก');
                $rowClass = $isCancelled ? 'row-cancelled' : '';
            ?>
              <tr class="<?= $rowClass ?>">
                <td data-label="หมายเลขคำสั่งซื้อ" class="text-start ps-md-4 fw-bold text-dark">
                  #<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?>
                </td>
                <td data-label="วันที่สั่งซื้อ" class="text-muted small">
                  <?= date('d/m/Y', strtotime($o['order_date'])) ?>
                  <span class="d-inline d-md-block"><?= date('H:i', strtotime($o['order_date'])) ?> น.</span>
                </td>
                <td data-label="วิธีชำระเงิน">
                  <i class="bi <?= $methodIcon ?> me-1"></i> <?= $methodText ?>
                </td>
                <td data-label="ยอดชำระสุทธิ" class="fw-bold text-danger">
                  <?= number_format($o['total_price'], 2) ?> ฿
                </td>
                <td data-label="การชำระเงิน">
                  <span class="badge <?= $payBadge ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td data-label="สถานะจัดส่ง">
                  <span class="badge <?= $orderBadge ?>">
                    <?= htmlspecialchars($order_status) ?>
                  </span>
                </td>
                <td data-label="การจัดการ" class="text-end pe-md-4">
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