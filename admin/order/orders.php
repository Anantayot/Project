<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจากโฟลเดอร์ partials
include __DIR__ . "/../partials/connectdb.php";

$pageTitle = "จัดการคำสั่งซื้อ";

// บังคับให้ต้องล็อกอิน
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login.php");
  exit;
}

try {
    $sql = "SELECT o.order_id, o.order_date, o.total_price, o.order_status, o.admin_verified, c.name AS customer_name 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            ORDER BY o.order_id DESC"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center mt-4'>❌ SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

ob_start();
?>

<style>
  .table-card {
    background: var(--bg-card);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    overflow: hidden;
  }
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #fff !important;
    font-weight: 500;
  }
  .table-dark {
    --bs-table-bg: transparent;
    border-color: rgba(255, 255, 255, 0.05);
  }
  
  /* ปรับแต่งสำหรับมือถือ */
  @media (max-width: 768px) {
    .d-mobile-none { display: none !important; } /* ซ่อนวันที่บนมือถือเพื่อประหยัดพื้นที่ */
    .table-dark td, .table-dark th { padding: 10px 5px; font-size: 0.85rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    .badge { font-size: 0.7rem; padding: 0.4em 0.6em; }
  }

  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-orange { background-color: #f97316 !important; color: #fff; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold text-white mb-0">
    <i class="bi bi-bag-check me-2 text-success"></i> รายการคำสั่งซื้อ
  </h4>
</div>

<div class="card table-card shadow-lg">
  <div class="card-body p-2 p-md-4"> <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีคำสั่งซื้อ</h5>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table id="dataTable" class="table table-dark table-hover text-center w-100 align-middle">
          <thead>
            <tr class="table-custom-header">
              <th>ID</th>
              <th class="text-start">ลูกค้า</th>
              <th class="d-mobile-none">วันที่</th> <th>ยอดรวม</th>
              <th>พัสดุ</th>
              <th>การโอน</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td class="fw-bold text-success">#<?= htmlspecialchars($o['order_id']) ?></td>
                <td class="text-start text-white text-truncate" style="max-width: 100px;">
                    <?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?>
                </td>
                <td class="text-light d-mobile-none"> <?= date("d/m/y", strtotime($o['order_date'])) ?>
                </td>
                <td class="fw-bold text-info">฿<?= number_format($o['total_price'], 0) ?></td>

                <td>
                  <?php
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    if ($status == 'สำเร็จ' || $status == 'จัดส่งแล้ว') $badge = 'success';
                    elseif ($status == 'กำลังจัดเตรียม') $badge = 'orange'; 
                    elseif ($status == 'ยกเลิก') $badge = 'danger';
                    else $badge = 'secondary';
                  ?>
                  <span class="badge bg-<?= $badge ?> rounded-pill">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>

                <td>
                  <?php
                    $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';
                    if ($verify == 'อนุมัติ') $vbadge = 'success';
                    elseif ($verify == 'ปฏิเสธ') $vbadge = 'danger';
                    elseif ($verify == 'กำลังตรวจสอบ') $vbadge = 'purple';
                    else $vbadge = 'warning text-dark';
                  ?>
                  <span class="badge bg-<?= $vbadge ?> rounded-pill">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <td>
                  <a href="order_view.php?id=<?= $o['order_id'] ?>" class="btn btn-sm btn-outline-success rounded-pill">
                    <i class="bi bi-search"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script>
  document.addEventListener("DOMContentLoaded", function() {
    let script1 = document.createElement('script');
    script1.src = "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js";
    document.body.appendChild(script1);
    
    script1.onload = () => {
      let script2 = document.createElement('script');
      script2.src = "https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js";
      document.body.appendChild(script2);
      
      script2.onload = () => {
        $('#dataTable').DataTable({
          language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' },
          pageLength: 10,
          responsive: true,
          order: [[0, "desc"]], // เรียงตาม ID ใหม่สุดขึ้นก่อน
          dom: '<"top"f>rt<"bottom"lp><"clear">', // ปรับตำแหน่ง Search ให้เหมาะกับมือถือ
        });

        // ตกแต่งเพิ่มเติม
        $(".dataTables_filter input").addClass("form-control form-control-sm").css({"background": "#161b22", "color": "#fff", "border": "1px solid #334155"});
        $(".dataTables_info, .dataTables_length, .dataTables_filter").addClass("text-light small mt-2");
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>