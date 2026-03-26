<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ตรวจสอบการเข้าสู่ระบบ (ป้องกันคนพิมพ์ URL เข้ามาตรงๆ)
if (!isset($_SESSION['admin_id'])) { 
    // หมายเหตุ: เปลี่ยน 'admin_id' เป็นชื่อตัวแปร Session ที่คุณตั้งไว้ตอน Login สำเร็จ
    header("Location: ../login.php"); 
    exit;
}

// 🕒 ระบบจับเวลา Session Timeout (10 นาที = 600 วินาที)
$timeout_duration = 600;

if (isset($_SESSION['last_activity'])) {
  // คำนวณว่าไม่ได้ใช้งานมานานกี่วินาทีแล้ว
  $time_inactive = time() - $_SESSION['last_activity'];
  
  if ($time_inactive >= $timeout_duration) {
    // ถ้าเกิน 10 นาที ให้ล้างค่า Session ทิ้งทั้งหมด
    session_unset();
    session_destroy();
    
    // เด้งกลับไปหน้า login พร้อมส่งค่า ?timeout=1 ไปบอก
    header("Location: ../login.php?timeout=1");
    exit;
  }
}

// ✅ อัปเดตเวลาล่าสุด ทุกครั้งที่มีการกดรีเฟรชหรือเปลี่ยนหน้า
$_SESSION['last_activity'] = time();

$pageTitle = "จัดการประเภทสินค้า";
// ✅ แก้ไขพาธให้ชี้ไปหาไฟล์ฐานข้อมูลอย่างถูกต้อง
include __DIR__ . "/../partials/connectdb.php";

// 🔹 ดึงข้อมูลประเภทสินค้าทั้งหมด เรียงจากน้อยไปมาก
try {
  $stmt = $conn->query("SELECT * FROM category ORDER BY cat_id ASC");
  $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      display: flex; justify-content: space-between; align-items: flex-start;
      padding: 12px 0; border: none !important;
      border-bottom: 1px dashed rgba(255, 255, 255, 0.1) !important;
      font-size: 0.95rem; width: 100%;
    }
    #dataTable tbody td:last-child {
      border-bottom: none !important; padding-top: 18px; padding-bottom: 5px;
    }
    #dataTable tbody td::before {
      content: attr(data-label); font-weight: 500; color: #94a3b8;
      text-align: left; min-width: 90px; margin-right: 15px; flex-shrink: 0; white-space: nowrap;
    }
    
    /* 📌 บังคับให้ข้อมูลชิดขวาสุด บนมือถือ */
    .mobile-right-content {
      text-align: right;
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      word-break: break-word;
    }
    
    .mobile-actions { display: flex; justify-content: flex-end; width: 100%; gap: 10px; }
  }

  @media (min-width: 768px) {
    .mobile-right-content { display: contents; }
  }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div class="ms-auto w-100 w-md-auto text-end">
    <a href="category_add.php" class="btn btn-success rounded-pill px-4 py-2 shadow-sm transition-all hover-scale w-100 w-md-auto">
      <i class="bi bi-plus-circle me-1"></i> เพิ่มประเภทสินค้า
    </a>
  </div>
</div>

<div class="card table-card shadow-lg mt-2">
  <div class="card-body p-3 p-md-4">

    <?php if (empty($cats)): ?>
      <div class="text-center py-5">
        <i class="bi bi-tags text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีข้อมูลประเภทสินค้า</h5>
      </div>
    <?php else: ?>
      <div> 
        <table id="dataTable" class="table table-dark table-striped table-hover text-center align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header text-center">
              <th style="width: 150px;">รหัสประเภท</th>
              <th class="text-start">ชื่อประเภทสินค้า</th>
              <th style="width: 150px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cats as $c): ?>
              <tr>
                <td data-label="รหัสประเภท" data-sort="<?= $c['cat_id'] ?>" class="fw-bold text-success mobile-value">
                  <div class="mobile-right-content">#<?= htmlspecialchars($c['cat_id']) ?></div>
                </td>
                
                <td data-label="ชื่อประเภท" data-sort="<?= htmlspecialchars($c['cat_name']) ?>" class="text-md-start text-white fw-medium mobile-value">
                  <div class="mobile-right-content"><?= htmlspecialchars($c['cat_name']) ?></div>
                </td>
                
                <td data-label="จัดการ" class="mobile-value">
                  <div class="d-flex justify-content-center mobile-actions">
                    <a href="category_edit.php?id=<?= $c['cat_id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="แก้ไขข้อมูล">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="category_delete.php?id=<?= $c['cat_id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="ลบข้อมูล" onclick="return confirm('ยืนยันการลบประเภทสินค้านี้หรือไม่? (หากลบ สินค้าที่อยู่ในประเภทนี้อาจไม่แสดงผล)');">
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
          responsive: false, 
          order: [[0, "asc"]], // ✅ เรียงตามรหัสจากน้อยไปมาก
          columnDefs: [
            { orderable: false, targets: [2] } // ปิด sort คอลัมน์ที่ 3 (จัดการ)
          ],
          dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3"ip>'
        });

        // ✅ แต่งกล่องค้นหาและช่อง Dropdown ให้ข้อความเป็นสีขาว
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
          
        // เปิด Tooltip (ข้อความลอยเมื่อเอาเมาส์ชี้ปุ่ม)
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
      };
    };
  });
</script>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials อย่างถูกต้อง
include __DIR__ . "/../partials/layout.php";
?>