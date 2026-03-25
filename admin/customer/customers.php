<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// บังคับให้ต้องล็อกอิน
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login.php");
  exit;
}

$pageTitle = "จัดการลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

// 🔹 ดึงข้อมูลลูกค้าทั้งหมด
try {
  $customers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC")->fetchAll(PDO::FETCH_ASSOC);
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

  /* ปรับแต่งสำหรับมือถือ */
  @media (max-width: 768px) {
    .d-mobile-none { display: none !important; } /* ซ่อนอีเมล/ที่อยู่ บนมือถือ */
    .table-dark td, .table-dark th { padding: 10px 5px; font-size: 0.85rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
  }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <h4 class="fw-bold text-white mb-0">
    <i class="bi bi-people me-2 text-success"></i> รายชื่อลูกค้า
  </h4>
  <a href="customer_add.php" class="btn btn-success rounded-pill px-4 shadow-sm transition-all hover-scale">
    <i class="bi bi-plus-circle me-1"></i> เพิ่มลูกค้าใหม่
  </a>
</div>

<div class="card table-card shadow-lg">
  <div class="card-body p-3 p-md-4">

    <?php if(empty($customers)): ?>
      <div class="text-center py-5">
        <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีข้อมูลลูกค้าในระบบ</h5>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table id="dataTable" class="table table-dark table-striped table-hover text-center align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header">
              <th style="width: 80px;">รหัสลูกค้า</th>
              <th class="text-start">ชื่อ-นามสกุล</th>
              <th class="d-mobile-none">อีเมล</th>
              <th>เบอร์โทร</th>
              <th class="d-mobile-none">ที่อยู่จัดส่ง</th>
              <th>รับข่าวสาร</th>
              <th style="width: 130px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($customers as $c): ?>
              <tr>
                <td class="fw-bold text-success">#<?= htmlspecialchars($c['customer_id']) ?></td>
                <td class="text-start text-white fw-medium"><?= htmlspecialchars($c['name']) ?></td>
                <td class="text-light d-mobile-none"><?= htmlspecialchars($c['email'] ?: '-') ?></td>
                <td class="text-info"><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                <td class="text-light d-mobile-none text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($c['address']) ?>">
                  <?= htmlspecialchars($c['address'] ?: '-') ?>
                </td>

                <td>
                  <?php if ($c['subscribe'] == 1): ?>
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> สมัครแล้ว</span>
                  <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-75 rounded-pill px-3 py-2 text-light"><i class="bi bi-x-circle me-1"></i> ไม่ได้รับ</span>
                  <?php endif; ?>
                </td>

                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="customer_edit.php?id=<?= $c['customer_id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle" title="แก้ไข">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="customer_delete.php?id=<?= $c['customer_id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle" title="ลบ" onclick="return confirm('ยืนยันการลบลูกค้ารหัส #<?= htmlspecialchars($c['customer_id']) ?> หรือไม่? ข้อมูลนี้จะไม่สามารถกู้คืนได้');">
                      <i class="bi bi-trash"></i>
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
          responsive: true,
          order: [[0, "desc"]], // เรียงตามรหัสลูกค้าใหม่สุดขึ้นก่อน
          columnDefs: [
            { orderable: false, targets: [6] } // ปิดปุ่มเรียงที่คอลัมน์ "จัดการ"
          ],
          dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // แต่งกล่องค้นหา
        $(".dataTables_filter input")
          .addClass("form-control form-control-sm ms-2")
          .css({
            "background": "rgba(255,255,255,0.05)",
            "color": "#fff",
            "border": "1px solid rgba(255,255,255,0.1)",
            "border-radius": "8px",
            "padding": "6px 15px",
            "min-width": "200px"
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
          
        $(".dataTables_info, .dataTables_length, .dataTables_filter").addClass("text-light");
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials
include __DIR__ . "/../partials/layout.php";
?>