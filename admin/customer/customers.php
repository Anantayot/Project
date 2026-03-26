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

$pageTitle = "จัดการลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

// 🔹 ดึงข้อมูลลูกค้าทั้งหมด (เรียงจากน้อยไปมาก ASC)
try {
  $customers = $conn->query("SELECT * FROM customers ORDER BY customer_id ASC")->fetchAll(PDO::FETCH_ASSOC);
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
    cursor: pointer; 
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

  /* ✅ สไตล์รูปโปรไฟล์ในตาราง */
  .table-profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    background-color: #fff;
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
      margin-top: auto; margin-bottom: auto; /* ให้อยู่กึ่งกลางแนวตั้ง */
    }
    
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
    <a href="customer_add.php" class="btn btn-success rounded-pill px-4 py-2 shadow-sm w-100 w-md-auto transition-all hover-scale">
      <i class="bi bi-person-plus me-1"></i> เพิ่มลูกค้าใหม่
    </a>
  </div>
</div>

<div class="card table-card shadow-lg mt-2">
  <div class="card-body p-3 p-md-4">

    <?php if(empty($customers)): ?>
      <div class="text-center py-5">
        <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีข้อมูลลูกค้าในระบบ</h5>
      </div>
    <?php else: ?>
      <div> 
        <table id="dataTable" class="table table-dark table-striped table-hover text-center align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header text-center">
              <th style="width: 80px;">รหัส</th>
              <th class="text-start">ข้อมูลลูกค้า</th>
              <th class="text-start">อีเมล</th>
              <th>เบอร์โทร</th>
              <th class="text-start">ที่อยู่จัดส่ง</th>
              <th>รับข่าวสาร</th>
              <th style="width: 120px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($customers as $c): ?>
              <tr>
                <td data-label="รหัสลูกค้า" data-sort="<?= $c['customer_id'] ?>" class="fw-bold text-success mobile-value">
                  <div class="mobile-right-content">#<?= htmlspecialchars($c['customer_id']) ?></div>
                </td>
                
                <td data-label="ข้อมูลลูกค้า" data-sort="<?= htmlspecialchars($c['name']) ?>" class="text-md-start text-white fw-medium mobile-value">
                  <div class="mobile-right-content">
                    <div class="d-flex align-items-center justify-content-end justify-content-md-start gap-3">
                      <?php
                        // ตรวจสอบรูปภาพ
                        $serverFilePath = $_SERVER['DOCUMENT_ROOT'] . "/Project/admin/uploads/profiles/" . $c['profile_image'];
                        if (!empty($c['profile_image']) && file_exists($serverFilePath)) {
                            $profileImg = "/Project/admin/uploads/profiles/" . htmlspecialchars($c['profile_image']);
                        } else {
                            // ✅ เปลี่ยนสีพื้นหลังรูปโปรไฟล์ตัวอักษรย่อให้เป็นสีเขียว 22c55e ตรงนี้ครับ
                            $profileImg = "https://ui-avatars.com/api/?name=" . urlencode($c['name']) . "&background=22c55e&color=fff&size=100&bold=true";
                        }
                      ?>
                      <img src="<?= $profileImg ?>" alt="Profile" class="table-profile-img">
                      <span><?= htmlspecialchars($c['name']) ?></span>
                    </div>
                  </div>
                </td>
                
                <td data-label="อีเมล" class="text-md-start text-light mobile-value">
                  <div class="mobile-right-content"><?= htmlspecialchars($c['email'] ?: '-') ?></div>
                </td>
                
                <td data-label="เบอร์โทร" class="text-info fw-semibold mobile-value">
                  <div class="mobile-right-content"><?= htmlspecialchars($c['phone'] ?: '-') ?></div>
                </td>
                
                <td data-label="ที่อยู่" class="text-md-start text-light mobile-value">
                  <div class="mobile-right-content">
                    <span class="d-inline-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($c['address']) ?>">
                      <?= htmlspecialchars($c['address'] ?: '-') ?>
                    </span>
                  </div>
                </td>

                <td data-label="รับข่าวสาร" class="mobile-value">
                  <div class="mobile-right-content">
                    <?php if ($c['subscribe'] == 1): ?>
                      <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> รับข่าวสาร</span>
                    <?php else: ?>
                      <span class="badge bg-secondary bg-opacity-75 rounded-pill px-3 py-2 text-white"><i class="bi bi-x-circle me-1"></i> ไม่ได้รับ</span>
                    <?php endif; ?>
                  </div>
                </td>

                <td data-label="จัดการ" class="mobile-value">
                  <div class="d-flex justify-content-center mobile-actions">
                    <a href="customer_edit.php?id=<?= $c['customer_id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="แก้ไขข้อมูล">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="customer_delete.php?id=<?= $c['customer_id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="ลบข้อมูล" onclick="return confirm('ยืนยันการลบลูกค้ารหัส #<?= htmlspecialchars($c['customer_id']) ?> หรือไม่?');">
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
          order: [[0, "desc"]], 
          columnDefs: [
            { orderable: false, targets: [6] } 
          ],
          dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3"ip>'
        });

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