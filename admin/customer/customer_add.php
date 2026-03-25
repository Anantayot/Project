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

$pageTitle = "เพิ่มข้อมูลลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);

  // 🔍 ตรวจสอบอีเมลซ้ำก่อนเพิ่ม
  $check = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
  $check->execute([$email]);
  if ($check->fetchColumn() > 0) {
    $error = "อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่นครับ";
  } else {
    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $address]);
    
    // แจ้งเตือนเมื่อสำเร็จและเด้งกลับไปหน้าจัดการลูกค้า
    echo "<script>alert('✅ เพิ่มข้อมูลลูกค้าสำเร็จ!'); window.location='customers.php';</script>";
    exit;
  }
}

// ✅ เริ่มเก็บ output หลังจากเช็ค POST เสร็จ
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

<div class="d-flex justify-content-start mb-4">
  <a href="customers.php" class="btn btn-outline-light btn-sm rounded-pill px-4 py-2 shadow-sm transition-all hover-scale">
    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    
    <div class="card custom-card shadow-lg mb-5">
      <div class="card-header border-bottom border-secondary p-4" style="border-color: rgba(255,255,255,0.05) !important;">
        <h4 class="fw-bold text-white mb-0 text-center">
          <i class="bi bi-person-plus text-success me-2"></i> เพิ่มข้อมูลลูกค้าใหม่
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
              <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-custom" placeholder="กรอกชื่อและนามสกุล" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">อีเมล <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control form-control-custom" placeholder="example@email.com" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">เบอร์โทรติดต่อ <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control form-control-custom" placeholder="08x-xxx-xxxx" required>
            </div>

            <div class="col-12">
              <label class="form-label">ที่อยู่จัดส่ง <span class="text-danger">*</span></label>
              <textarea name="address" rows="3" class="form-control form-control-custom" placeholder="กรอกที่อยู่สำหรับจัดส่งสินค้า..." required></textarea>
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="customers.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto">
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