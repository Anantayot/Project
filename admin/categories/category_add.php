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

$pageTitle = "เพิ่มประเภทสินค้า";
include __DIR__ . "/../partials/connectdb.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['cat_name']);
  $desc = trim($_POST['cat_description']);

  try {
    $stmt = $conn->prepare("INSERT INTO category (cat_name, cat_description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
    
    // แจ้งเตือนเมื่อสำเร็จและเด้งกลับไปหน้าจัดการ
    echo "<script>alert('✅ เพิ่มประเภทสินค้าสำเร็จ!'); window.location='categories.php';</script>";
    exit;
  } catch (PDOException $e) {
    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
  }
}

// ✅ เริ่มเก็บเนื้อหาเข้า Layout
ob_start();
?>

<style>
  .custom-card {
    background: var(--bg-card, #1e293b);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
  }
  .form-label {
    color: #94a3b8;
    font-weight: 500;
    margin-bottom: 8px;
  }
  .form-control-custom {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #f8fafc;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
  }
  .form-control-custom:focus {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: #22c55e;
    box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.25);
    color: #fff;
  }
  .form-control-custom::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
</style>

<div class="row justify-content-center">
  <div class="col-lg-7 col-xl-6">
    
    <div class="card custom-card shadow-lg mb-5">
      <div class="card-header border-bottom border-secondary p-4" style="border-color: rgba(255,255,255,0.05) !important;">
        <h4 class="fw-bold text-white mb-0 text-center">
          <i class="bi bi-plus-circle text-success me-2"></i> เพิ่มประเภทสินค้า
        </h4>
      </div>
      
      <div class="card-body p-4 p-md-5">

        <?php if(!empty($error)): ?>
          <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 text-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="row g-4">
            
            <div class="col-md-12">
              <label class="form-label">ชื่อประเภทสินค้า <span class="text-danger">*</span></label>
              <input type="text" name="cat_name" class="form-control form-control-custom" placeholder="เช่น คีย์บอร์ด, เมาส์, อุปกรณ์ไอที" required>
            </div>

            <div class="col-md-12">
              <label class="form-label">รายละเอียด (ไม่บังคับ)</label>
              <textarea name="cat_description" rows="3" class="form-control form-control-custom" placeholder="กรอกคำอธิบายเพิ่มเติมเกี่ยวกับประเภทสินค้านี้..."></textarea>
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="categories.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto">
              <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 w-100 w-sm-auto fw-bold shadow-sm hover-scale">
              <i class="bi bi-check-circle me-1"></i> บันทึกข้อมูล
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>