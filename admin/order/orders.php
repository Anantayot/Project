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
  /* 🎨 แต่งตารางสำหรับ Desktop */
  .table-card {
    background: var(--bg-card);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #fff !important;
  }
  .table-dark { --bs-table-bg: transparent; border-color: rgba(255, 255, 255, 0.05); }

  /* 📱 ปรับแต่งสำหรับ Mobile (Mobile First) */
  @media (max-width: 767px) {
    /* ซ่อนหัวตารางบนมือถือ */
    #dataTable thead { display: none; }
    
    /* ปรับแต่ละแถวให้กลายเป็นการ์ด */
    #dataTable tbody tr {
      display: block;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.05);
      position: relative;
    }
    
    /* จัดเลย์เอาต์ภายในแต่ละการ์ด */
    #dataTable tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none;
      padding: 5px 0;
      text-align: right;
    }

    /* เพิ่มหัวข้อกำกับแต่ละข้อมูล */
    #dataTable tbody td:before {
      content: attr(data-label);
      float: left;
      font-weight: 500;
      color: var(--text-muted);
    }

    /* ปรับแต่งจุดเฉพาะ */
    #dataTable td[data-label="ID"] { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 10px; font-size: 1.1rem; }
    #dataTable td[data-label="จัดการ"] { width: 100%; justify-content: center; padding-top: 10px; }
    #dataTable td[data-label="จัดการ"] a { width: 100%; }
    
    .dataTables_wrapper .dataTables_filter { text-align: left !important; margin-bottom: 15px; }
  }

  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-orange { background-color: #f97316 !important; color: #fff; }
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
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีคำสั่งซื้อ</h5>
      </div>
    <?php else: ?>
      <div class="table-responsive" style="overflow-x: hidden;">
        <table id="dataTable" class="table table-dark align-middle w-100">
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
                <td data-label="ID" class="fw-bold text-success">#<?= htmlspecialchars($o['order_id']) ?></td>
                <td data-label="ลูกค้า" class="text-white fw-medium"><?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?></td>
                <td data-label="วันที่" class="text-light"><?= date("d/m/y H:i", strtotime($o['order_date'])) ?></td>
                <td data-label="ยอดรวม" class="fw-bold text-info">฿<?= number_format($o['total_price'], 2) ?></td>

                <td data-label="พัสดุ">
                  <?php
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    if ($status == 'สำเร็จ' || $status == 'จัดส่งแล้ว') $badge = 'success';
                    elseif ($status == 'กำลังจัดเตรียม') $badge = 'orange'; 
                    elseif ($status == 'ยกเลิก') $badge = 'danger';
                    else $badge = 'secondary';
                  ?>
                  <span class="badge bg-<?= $badge ?> rounded-pill px-3">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>

                <td data-label="การโอน">
                  <?php
                    $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';
                    if ($verify == 'อนุมัติ') $vbadge = 'success';
                    elseif ($verify == 'ปฏิเสธ') $vbadge = 'danger';
                    elseif ($verify == 'กำลังตรวจสอบ') $vbadge = 'purple';
                    else $vbadge = 'warning text-dark';
                  ?>
                  <span class="badge bg-<?= $vbadge ?> rounded-pill px-3">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <td data-label="จัดการ">
                  <a href="order_view.php?id=<?= $o['order_id'] ?>" class="btn btn-outline-success rounded-pill btn-sm py-2">
                    <i class="bi bi-search me-1"></i> ตรวจสอบข้อมูล
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
          responsive: false, // ปิด responsive ของตัวปลั๊กอินเพื่อให้ใช้ CSS card ของเราแทน
          order: [[0, "desc"]],
          dom: '<"top"f>rt<"bottom"lp><"clear">',
        });

        // ตกแต่ง UI
        $(".dataTables_filter input").addClass("form-control").css({"background": "#161b22", "color": "#fff", "border": "1px solid #334155", "border-radius": "10px"});
        $(".dataTables_info, .dataTables_length, .dataTables_filter").addClass("text-light small mt-2");
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>