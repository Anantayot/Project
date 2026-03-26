<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$pageTitle = "จัดการสินค้า";
include __DIR__ . "/../partials/connectdb.php";

// 🔹 ดึงข้อมูลสินค้าทั้งหมด (JOIN กับหมวดหมู่) เรียงตาม ID
try {
  $sql = "SELECT p.*, c.cat_name 
          FROM product p
          LEFT JOIN category c ON p.cat_id = c.cat_id
          ORDER BY p.p_id ASC";
  $products = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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

  /* 🔸 ควบคุมข้อความยาวบนจอคอม */
  .truncate-text {
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    
    /* 📌 คลาสบังคับให้ข้อมูลดันไปชิดขวาสุด */
    .mobile-right-content {
      text-align: right;
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      word-break: break-word;
    }
    
    .mobile-actions { display: flex; justify-content: flex-end; width: 100%; gap: 10px; }
    .truncate-text { max-width: 100%; white-space: normal; text-align: right; }
  }

  @media (min-width: 768px) {
    .mobile-right-content { display: contents; }
  }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div class="ms-auto w-100 w-md-auto text-end">
    <a href="product_add.php" class="btn btn-success rounded-pill px-4 py-2 shadow-sm w-100 w-md-auto transition-all hover-scale">
      <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้าใหม่
    </a>
  </div>
</div>

<div class="card table-card shadow-lg mt-2">
  <div class="card-body p-3 p-md-4">

    <?php if(empty($products)): ?>
      <div class="text-center py-5">
        <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">ยังไม่มีข้อมูลสินค้าในระบบ</h5>
      </div>
    <?php else: ?>
      <div> 
        <table id="dataTable" class="table table-dark table-striped table-hover text-center align-middle w-100 mb-0">
          <thead>
            <tr class="table-custom-header text-center">
              <th style="width: 80px;">รหัส</th>
              <th style="width: 100px;">รูปภาพ</th>
              <th class="text-start">ชื่อสินค้า</th>
              <th>ราคา (฿)</th>
              <th>หมวดหมู่</th>
              <th>สต็อก</th>
              <th style="width: 120px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($products as $p): ?>
              <tr>
                <td data-label="รหัสสินค้า" data-sort="<?= $p['p_id'] ?>" class="fw-bold text-success mobile-value">
                  <div class="mobile-right-content">#<?= htmlspecialchars($p['p_id']) ?></div>
                </td>
                
                <td data-label="รูปภาพ" class="mobile-value">
                  <div class="mobile-right-content">
                    <?php 
                      $imagePath = __DIR__ . "/../uploads/" . $p['p_image'];
                      $imageURL  = "../uploads/" . htmlspecialchars($p['p_image']);
                      
                      if (!empty($p['p_image']) && file_exists($imagePath)): 
                    ?>
                      <img src="<?= $imageURL ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded border border-secondary shadow-sm" alt="product">
                    <?php else: ?>
                      <span class="badge bg-secondary">ไม่มีรูป</span>
                    <?php endif; ?>
                  </div>
                </td>
                
                <td data-label="ชื่อสินค้า" data-sort="<?= htmlspecialchars($p['p_name']) ?>" class="text-md-start text-white fw-medium truncate-text mobile-value" title="<?= htmlspecialchars($p['p_name']) ?>">
                  <div class="mobile-right-content"><?= htmlspecialchars($p['p_name']) ?></div>
                </td>
                
                <td data-label="ราคา" data-sort="<?= $p['p_price'] ?>" class="text-info fw-bold mobile-value">
                  <div class="mobile-right-content">฿<?= number_format($p['p_price'], 2) ?></div>
                </td>
                
                <td data-label="หมวดหมู่" data-sort="<?= htmlspecialchars($p['cat_name'] ?? '') ?>" class="text-light mobile-value">
                  <div class="mobile-right-content"><?= htmlspecialchars($p['cat_name'] ?? '-') ?></div>
                </td>
                
                <td data-label="สต็อก" data-sort="<?= $p['p_stock'] ?>" class="mobile-value">
                  <div class="mobile-right-content">
                    <?php if($p['p_stock'] > 0): ?>
                      <span class="badge bg-success bg-opacity-75 px-3 py-2 rounded-pill"><?= htmlspecialchars($p['p_stock']) ?> ชิ้น</span>
                    <?php else: ?>
                      <span class="badge bg-danger px-3 py-2 rounded-pill">หมด</span>
                    <?php endif; ?>
                  </div>
                </td>
                
                <td data-label="จัดการ" class="mobile-value">
                  <div class="mobile-right-content">
                    <div class="d-flex justify-content-end mobile-actions w-100">
                      <a href="product_edit.php?id=<?= $p['p_id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="แก้ไขสินค้า">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <a href="product_delete.php?id=<?= $p['p_id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle transition-all hover-scale" data-bs-toggle="tooltip" title="ลบสินค้า" onclick="return confirm('ยืนยันการลบสินค้า #<?= htmlspecialchars($p['p_id']) ?> หรือไม่?');">
                        <i class="bi bi-trash"></i>
                      </a>
                    </div>
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
          order: [[0, "asc"]], // เรียงตามรหัสสินค้าจากน้อยไปมาก
          columnDefs: [
            { orderable: false, targets: [1, 6] } // ปิด sort รูปภาพ และจัดการ
          ],
          dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3"ip>'
        });

        // แต่งกล่องค้นหาและช่อง Dropdown ให้ข้อความเป็นสีขาว
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