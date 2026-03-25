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
    // เรียงลำดับจากฐานข้อมูล (ดึงข้อมูลล่าสุดขึ้นก่อน)
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
  /* 🎨 แต่งตารางสำหรับ Desktop */
  .table-card {
    background: var(--bg-card, #1e1e2d);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #ffffff !important;
  }
  .table-dark { 
    --bs-table-bg: transparent; 
    --bs-table-color: #ffffff; 
    border-color: rgba(255, 255, 255, 0.05); 
  }
  
  #dataTable tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
    transition: all 0.3s ease-in-out;
  }

  /* 🎨 ตกแต่ง DataTables */
  .dataTables_wrapper .dataTables_length, 
  .dataTables_wrapper .dataTables_filter, 
  .dataTables_wrapper .dataTables_info, 
  .dataTables_wrapper .dataTables_processing, 
  .dataTables_wrapper .dataTables_paginate {
    color: #ffffff !important;
  }
  
  .dataTables_length select {
    background-color: #161b22;
    color: #ffffff;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 3px 10px;
    outline: none;
  }

  .page-item.active .page-link {
    background-color: #16a34a !important;
    border-color: #16a34a !important;
    color: #ffffff !important;
    box-shadow: 0 0 10px rgba(22, 163, 74, 0.4);
  }
  .page-link {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: #e2e8f0 !important;
  }
  .page-link:hover {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
  }

  /* สี Custom สำหรับป้ายสถานะ */
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-orange { background-color: #f97316 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } /* สีฟ้า */
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } /* สีเขียว 22c55e */

  /* 📱 ปรับแต่งสำหรับ Mobile */
  @media (max-width: 767px) {
    #dataTable thead { display: none; }
    
    #dataTable tbody tr {
      display: block;
      background: rgba(255, 255, 255, 0.04);
      border-radius: 12px;
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      position: relative;
    }
    
    #dataTable tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none;
      padding: 8px 0;
      text-align: right;
      color: #ffffff !important;
    }

    #dataTable tbody td:before {
      content: attr(data-label);
      float: left;
      font-weight: 500;
      color: #94a3b8; 
    }

    #dataTable td[data-label="ID"] { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 10px; font-size: 1.1rem; }
    #dataTable td[data-label="จัดการ"] { width: 100%; justify-content: center; padding-top: 15px; }
    #dataTable td[data-label="จัดการ"] a { width: 100%; font-weight: 600; }
    
    .dataTables_wrapper .dataTables_filter { text-align: left !important; margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_filter input { width: 100%; margin-left: 0 !important; margin-top: 5px; }
  }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold text-white mb-0">
    <i class="bi bi-bag-check me-2 text-success"></i> รายการคำสั่งซื้อ
  </h4>
</div>

<div class="card table-card shadow-lg border-0">
  <div class="card-body p-3 p-md-4">

    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #64748b;"></i>
        <h5 class="text-white mt-3">ยังไม่มีคำสั่งซื้อ</h5>
      </div>
    <?php else: ?>
      <div class="table-responsive" style="overflow-x: hidden;">
        <table id="dataTable" class="table table-dark align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header text-center">
              <th>ID</th>
              <th>ลูกค้า</th>
              <th>วันที่</th>
              <th>ยอดรวม</th>
              <th>พัสดุ</th>
              <th>การโอน</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td data-label="ID" data-sort="<?= $o['order_id'] ?>" class="fw-bold text-success">
                    #<?= htmlspecialchars($o['order_id']) ?>
                </td>
                <td data-label="ลูกค้า" class="text-white fw-medium"><?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?></td>
                <td data-label="วันที่" class="text-light"><?= date("d/m/y H:i", strtotime($o['order_date'])) ?></td>
                <td data-label="ยอดรวม" class="fw-bold text-info">฿<?= number_format($o['total_price'], 2) ?></td>

                <td data-label="พัสดุ" class="text-center text-md-start">
                  <?php
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    if ($status == 'สำเร็จ' || $status == 'จัดส่งแล้ว') $badge = 'success';
                    elseif ($status == 'กำลังจัดเตรียม') $badge = 'custom-blue'; // เปลี่ยนเป็นสีฟ้า
                    elseif ($status == 'ยกเลิก') $badge = 'danger';
                    else $badge = 'secondary';
                  ?>
                  <span class="badge bg-<?= $badge ?> rounded-pill px-3 py-2 fw-medium">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>

                <td data-label="การโอน" class="text-center text-md-start">
                  <?php
                    $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';
                    if ($verify == 'อนุมัติ') $vbadge = 'custom-success'; // เปลี่ยนเป็นสีเขียว 22c55e
                    elseif ($verify == 'ปฏิเสธ') $vbadge = 'danger';
                    elseif ($verify == 'กำลังตรวจสอบ') $vbadge = 'purple';
                    else $vbadge = 'warning text-dark';
                  ?>
                  <span class="badge bg-<?= $vbadge ?> rounded-pill px-3 py-2 fw-medium">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <td data-label="จัดการ" class="text-center">
                  <a href="order_view.php?id=<?= $o['order_id'] ?>" class="btn btn-outline-success rounded-pill btn-sm py-1 px-3">
                    <i class="bi bi-search me-1"></i> ตรวจสอบ
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
          responsive: false,
          order: [[0, "desc"]], // บังคับให้คอลัมน์แรก (ID) เรียงจากมากไปน้อย (descending)
          dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip><"clear">',
        });

        $(".dataTables_filter input")
            .addClass("form-control d-inline-block")
            .css({
                "background": "#161b22", 
                "color": "#ffffff", 
                "border": "1px solid #334155", 
                "border-radius": "8px",
                "padding": "5px 15px",
                "width": "auto"
            })
            .on("focus", function() {
                $(this).css({"border-color": "#22c55e", "box-shadow": "0 0 0 0.2rem rgba(34, 197, 94, 0.25)", "outline": "none"});
            })
            .on("blur", function() {
                $(this).css({"border-color": "#334155", "box-shadow": "none"});
            });
            
        $(".dataTables_info").addClass("text-light small mt-2 mt-md-0");
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>