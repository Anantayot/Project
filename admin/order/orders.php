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
            ORDER BY o.order_id DESC"; // เปลี่ยนเป็น DESC เพื่อให้รายการใหม่สุดอยู่บนสุด
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
  /* แต่งหัวตาราง */
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #fff !important;
    font-weight: 500;
    letter-spacing: 0.5px;
  }
  .table-custom-header th {
    border-bottom: none;
    padding: 15px 10px;
  }
  /* แต่งตัวตาราง */
  .table-dark {
    --bs-table-bg: transparent;
    --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
    --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.05);
  }
  .table-dark td {
    padding: 15px 10px;
    vertical-align: middle;
  }
  
  /* สีสถานะแบบ Custom */
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-orange { background-color: #f97316 !important; color: #fff; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold text-white mb-0">
    <i class="bi bi-bag-check me-2 text-success"></i> รายการคำสั่งซื้อทั้งหมด
  </h4>
</div>

<div class="card table-card shadow-lg">
  <div class="card-body p-4">

    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีคำสั่งซื้อในระบบ</h5>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table id="dataTable" class="table table-dark table-striped table-hover text-center w-100">
          <thead>
            <tr class="table-custom-header">
              <th>#</th>
              <th>รหัสคำสั่งซื้อ</th>
              <th class="text-start">ชื่อลูกค้า</th>
              <th>วันที่สั่งซื้อ</th>
              <th>ราคารวม (฿)</th>
              <th>สถานะพัสดุ</th>
              <th>สถานะการโอน</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $i => $o): ?>
              <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td class="fw-bold text-success">#<?= htmlspecialchars($o['order_id']) ?></td>
                <td class="text-start text-white"><?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?></td>
                <td class="text-light"><?= date("d/m/Y H:i", strtotime($o['order_date'])) ?></td>
                <td class="fw-bold text-info">฿<?= number_format($o['total_price'], 2) ?></td>

                <td>
                  <?php
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    if ($status == 'สำเร็จ' || $status == 'จัดส่งแล้ว') $badge = 'success';
                    elseif ($status == 'กำลังจัดเตรียม') $badge = 'orange'; 
                    elseif ($status == 'ยกเลิก') $badge = 'danger';
                    else $badge = 'secondary';
                  ?>
                  <span class="badge bg-<?= $badge ?> px-3 py-2 rounded-pill shadow-sm">
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
                  <span class="badge bg-<?= $vbadge ?> px-3 py-2 rounded-pill shadow-sm">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <td>
                  <a href="order_view.php?id=<?= $o['order_id'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 transition-all">
                    <i class="bi bi-search"></i> ตรวจสอบ
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
  // ใช้ JavaScript โหลดสคริปต์เพื่อป้องกันปัญหาตีกับ jQuery ในหน้า layout.php
  document.addEventListener("DOMContentLoaded", function() {
    let script1 = document.createElement('script');
    script1.src = "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js";
    document.body.appendChild(script1);
    
    script1.onload = () => {
      let script2 = document.createElement('script');
      script2.src = "https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js";
      document.body.appendChild(script2);
      
      script2.onload = () => {
        // เมื่อโหลดเสร็จแล้ว ให้เปิดใช้งาน DataTable
        $('#dataTable').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
          },
          pageLength: 10,
          responsive: true,
          order: [[0, "asc"]],
          columnDefs: [
            { orderable: false, targets: [7] } // ปิดปุ่มเรียงที่คอลัมน์ "จัดการ"
          ]
        });

        // แต่งกล่องค้นหา
        $(".dataTables_filter input")
          .addClass("form-control form-control-sm ms-2")
          .css({
            "background": "rgba(255,255,255,0.05)",
            "color": "#fff",
            "border": "1px solid rgba(255,255,255,0.1)",
            "border-radius": "8px",
            "padding": "6px 15px"
          });

        // แต่ง Dropdown เลือกจำนวนหน้า
        $(".dataTables_length select")
          .addClass("form-select form-select-sm")
          .css({
            "background": "rgba(255,255,255,0.05)",
            "color": "#fff",
            "border": "1px solid rgba(255,255,255,0.1)",
            "border-radius": "8px"
          });
          
        // เปลี่ยนสีข้อความรอบๆ เป็นสีอ่อน
        $(".dataTables_info, .dataTables_length, .dataTables_filter").addClass("text-light mb-3 mt-2");
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials
include __DIR__ . "/../partials/layout.php";
?>