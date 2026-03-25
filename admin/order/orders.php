<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจากโฟลเดอร์ partials
include __DIR__ . "/../partials/connectdb.php";

$pageTitle = "รายการคำสั่งซื้อ";

// บังคับให้ต้องล็อกอิน
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login.php");
  exit;
}

/* =========================================================
   🟢 ส่วนตั้งค่า (Config): แก้ไขสีหรือเพิ่มสถานะใหม่ได้ที่นี่เลย!
   ========================================================= */
$statusColors = [
    'รอดำเนินการ'    => 'custom-yellow', 
    'กำลังจัดเตรียม'  => 'custom-blue',   
    'จัดส่งแล้ว'      => 'success',       
    'สำเร็จ'         => 'success',       
    'ยกเลิก'         => 'danger'         
];

$verifyColors = [
    'รอตรวจสอบ'     => 'warning text-dark',
    'กำลังตรวจสอบ'   => 'purple',
    'อนุมัติ'         => 'custom-success', 
    'ปฏิเสธ'         => 'danger'
];

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
    letter-spacing: 0.5px;
  }
  .table-custom-header th {
    border-bottom: none;
    padding: 15px 10px;
    cursor: pointer; /* ให้รู้ว่ากดเรียงได้ */
  }
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

  /* ✅ เปลี่ยนสีข้อความ DataTables เป็นสีขาวทั้งหมด */
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_processing,
  .dataTables_wrapper .dataTables_paginate {
    color: #ffffff !important;
  }
  .dataTables_wrapper label {
    color: #ffffff !important; 
    font-weight: 500;
  }
  
  /* 🔸 Pagination เข้าธีม */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    color: #fff !important;
    border-radius: 6px;
    margin: 0 3px;
    border: 1px solid transparent !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: rgba(34, 197, 94, 0.2) !important;
    border: 1px solid #22c55e !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(145deg, #22c55e, #16a34a) !important;
    color: #fff !important;
    border: none !important;
  }

  /* 🎨 สี Custom Badges */
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } 
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } 
  .bg-custom-yellow { background-color: #facc15 !important; color: #0f172a !important; } 

  .badge-fixed {
    width: 120px;
    display: inline-block;
    text-align: center;
    font-weight: 600;
    padding: 6px 12px;
  }

  /* 📱 ปรับแต่งสำหรับมือถือ (Mobile Card View) */
  @media (max-width: 768px) {
    #dataTable thead { display: none; }
    #dataTable tbody tr {
      display: flex; flex-direction: column;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 15px; margin-bottom: 20px;
      padding: 15px 20px; border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    #dataTable tbody td {
      display: flex; justify-content: space-between; align-items: center;
      padding: 12px 0; border: none !important;
      border-bottom: 1px dashed rgba(255, 255, 255, 0.1) !important;
      font-size: 0.95rem;
    }
    #dataTable tbody td:last-child {
      border-bottom: none !important; padding-top: 18px; padding-bottom: 5px;
    }
    #dataTable tbody td::before {
      content: attr(data-label); font-weight: 500; color: #94a3b8;
      text-align: left; min-width: 90px; margin-right: 15px; flex-shrink: 0;
    }
    .mobile-value { text-align: right !important; word-break: break-word; flex-grow: 1; }
    .mobile-actions { display: flex; justify-content: flex-end; width: 100%; gap: 10px; }
  }
</style>

<div class="card table-card shadow-lg mt-2">
  <div class="card-body p-3 p-md-4">

    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีคำสั่งซื้อ</h5>
      </div>
    <?php else: ?>
      <div>
        <table id="dataTable" class="table table-dark table-striped table-hover text-center align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header text-center">
              <th style="width: 80px;">ID</th>
              <th class="text-start">ลูกค้า</th>
              <th>วันที่</th>
              <th>ยอดรวม</th>
              <th>สถานะพัสดุ</th>
              <th>การชำระเงิน</th>
              <th style="width: 120px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td data-label="ID" data-sort="<?= $o['order_id'] ?>" class="fw-bold text-success mobile-value">
                    #<?= htmlspecialchars($o['order_id']) ?>
                </td>

                <td data-label="ลูกค้า" data-sort="<?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?>" class="text-md-start text-white fw-medium mobile-value">
                  <?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?>
                </td>

                <td data-label="วันที่" data-sort="<?= strtotime($o['order_date']) ?>" class="text-light mobile-value">
                  <?= date("d/m/y H:i", strtotime($o['order_date'])) ?>
                </td>

                <td data-label="ยอดรวม" data-sort="<?= $o['total_price'] ?>" class="fw-bold text-info mobile-value">
                  ฿<?= number_format($o['total_price'], 2) ?>
                </td>

                <td data-label="พัสดุ" data-sort="<?= $o['order_status'] ?>" class="mobile-value">
                  <?php 
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    $badgeClass = $statusColors[$status] ?? 'secondary'; 
                  ?>
                  <span class="badge bg-<?= $badgeClass ?> rounded-pill badge-fixed">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>

                <td data-label="การโอน" data-sort="<?= $o['admin_verified'] ?>" class="mobile-value">
                  <?php 
                    $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';
                    $vBadgeClass = $verifyColors[$verify] ?? 'secondary'; 
                  ?>
                  <span class="badge bg-<?= $vBadgeClass ?> rounded-pill badge-fixed">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <td data-label="จัดการ" class="mobile-value">
                  <div class="d-flex justify-content-center mobile-actions">
                    <a href="order_view.php?id=<?= $o['order_id'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 transition-all hover-scale" data-bs-toggle="tooltip" title="ตรวจสอบคำสั่งซื้อ">
                      <i class="bi bi-search me-1"></i> ตรวจสอบ
                    </a>
                  </div>
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
          responsive: false, // ปิดไว้เพราะเราใช้ CSS Card View บนมือถือ
          order: [[0, "desc"]], // ✅ เรียงจากคำสั่งซื้อใหม่ล่าสุด (ID มากไปน้อย)
          columnDefs: [
            { orderable: false, targets: [6] } // ปิด sort จัดการ
          ],
          dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3"ip>'
        });

        // ✅ แต่งกล่องค้นหาและช่อง Dropdown ให้ข้อความเป็นสีขาวแบบเดียวกับหน้าสินค้า
        $(".dataTables_filter input")
          .addClass("form-control form-control-sm text-white")
          .css({
            "background": "rgba(255,255,255,0.05)", "color": "#ffffff",
            "border": "1px solid rgba(255,255,255,0.1)", "border-radius": "8px",
            "padding": "8px 15px", "min-width": "250px"
          });

        $(".dataTables_length select")
          .addClass("form-select form-select-sm text-white")
          .css({
            "background": "rgba(255,255,255,0.05)", "color": "#ffffff",
            "border": "1px solid rgba(255,255,255,0.1)", "border-radius": "8px",
            "padding": "6px 30px 6px 15px"
          });
          
        // เปิด Tooltip
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>