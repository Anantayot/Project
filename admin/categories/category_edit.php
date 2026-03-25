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

$pageTitle = "แก้ไขประเภทสินค้า";
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "<script>alert('❌ ไม่พบรหัสประเภทสินค้า'); window.location='categories.php';</script>";
  exit;
}

// 🔹 ดึงข้อมูลประเภทสินค้าเดิมมาแสดง
$stmt = $conn->prepare("SELECT * FROM category WHERE cat_id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  echo "<script>alert('❌ ไม่พบข้อมูลประเภทสินค้าในระบบ'); window.location='categories.php';</script>";
  exit;
}

$error = "";

// 🔹 จัดการเมื่อมีการกดปุ่มบันทึกแก้ไข
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['cat_name']);
  $desc = trim($_POST['cat_description']);

  try {
    $update = $conn->prepare("UPDATE category SET cat_name=?, cat_description=? WHERE cat_id=?");
    $update->execute([$name, $desc, $id]);
    
    echo "<script>alert('✅ บันทึกการแก้ไขข้อมูลสำเร็จ!'); window.location='categories.php';</script>";
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
    border-color: #facc15; /* เปลี่ยนกรอบเป็นสีเหลืองทองให้เข้ากับโหมดแก้ไข */
    box-shadow: 0 0 0 0.25rem rgba(250, 204, 21, 0.25);
    color: #fff;
  }
  .form-control-custom::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
</style>

<div class="d-flex justify-content-start mb-4">
  <a href="categories.php" class="btn btn-outline-light btn-sm rounded-pill px-4 py-2 shadow-sm transition-all hover-scale">
    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับไปหน้ารายการ
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7 col-xl-6">
    
    <div class="card custom-card shadow-lg mb-5">
      <div class="card-header border-bottom border-secondary p-4" style="border-color: rgba(255,255,255,0.05) !important;">
        <h4 class="fw-bold text-white mb-0 text-center">
          <i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขประเภทสินค้า <span class="text-warning">#<?= htmlspecialchars($id) ?></span>
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
              <input type="text" name="cat_name" value="<?= htmlspecialchars($c['cat_name']) ?>" class="form-control form-control-custom" required>
            </div>

            <div class="col-md-12">
              <label class="form-label">รายละเอียด (ไม่บังคับ)</label>
              <textarea name="cat_description" rows="3" class="form-control form-control-custom"><?= htmlspecialchars($c['cat_description'] ?? '') ?></textarea>
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="categories.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto">
              <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-warning rounded-pill px-5 py-2 w-100 w-sm-auto fw-bold shadow-sm hover-scale text-dark">
              <i class="bi bi-check-circle me-1"></i> บันทึกการแก้ไข
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