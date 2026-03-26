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

$pageTitle = "เพิ่มสินค้าใหม่";
include __DIR__ . "/../partials/connectdb.php";

$error = "";

// 🔹 ดึงข้อมูลหมวดหมู่มาแสดงใน Dropdown
try {
  $cats = $conn->query("SELECT * FROM category ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name   = trim($_POST['name']);
    $price  = $_POST['price'];
    $stock  = $_POST['stock'];
    $cat_id = $_POST['cat_id'];
    $desc   = trim($_POST['description']);

    $image = "";
    // 🔹 ตรวจสอบว่ามีการอัปโหลดไฟล์และไม่มีข้อผิดพลาดจาก PHP
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        
        // 1. ตรวจสอบนามสกุลไฟล์
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 2. ตรวจสอบประเภทไฟล์จริง (Mime Type) เพื่อความปลอดภัยขั้นสูง
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($ext, $allowed_ext)) {
            $error = "❌ นามสกุลไฟล์ไม่ได้รับอนุญาต (ใช้ได้เฉพาะ JPG, PNG, GIF, WEBP)";
        } elseif (!in_array($mime_type, $allowed_mime)) {
            $error = "❌ ประเภทไฟล์ไม่ถูกต้อง (ไฟล์ที่คุณอัปโหลดไม่ใช่รูปภาพจริง)";
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB
            $error = "❌ ไฟล์มีขนาดใหญ่เกินไป (จำกัดไม่เกิน 2MB)";
        } else {
            // 🔹 ถ้าผ่านทุกด่าน ให้สร้างชื่อไฟล์ใหม่
            $image = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            $targetDir = __DIR__ . "/../uploads/products/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true); // ใช้ 0755 ตามมาตรฐานความปลอดภัย
            }

            if (!move_uploaded_file($file_tmp, $targetDir . $image)) {
                $error = "❌ ไม่สามารถย้ายไฟล์ไปยังโฟลเดอร์ปลายทางได้";
            }
        }
    } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "❌ เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (Error Code: " . $_FILES['image']['error'] . ")";
    }

    // ถ้าไม่มี Error ให้บันทึกลงฐานข้อมูล
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO product (p_name, p_price, p_stock, p_description, p_image, cat_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $price, $stock, $desc, $image, $cat_id]);

            echo "<script>alert('✅ เพิ่มสินค้าสำเร็จ!'); window.location='products.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
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
  .form-control-custom, .form-select-custom {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #f8fafc;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
  }
  .form-control-custom:focus, .form-select-custom:focus {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: #22c55e;
    box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.25);
    color: #fff;
  }
  .form-control-custom::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
  .form-select-custom option {
    background-color: #1e293b;
    color: #fff;
  }
  
  /* ✅ แก้ไข CSS สำหรับปุ่มเลือกไฟล์ ให้สีไม่กลืน */
  input[type="file"].form-control-custom::file-selector-button {
    background-color: #2c313a; /* สีเทาเข้ม เข้าตีม */
    color: #fff; /* ข้อความสีขาว */
    border: none;
    padding: 8px 15px;
    margin-right: 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
  }
  input[type="file"].form-control-custom::file-selector-button:hover {
    background-color: #22c55e; /* เปลี่ยนเป็นสีเขียวเมื่อ hover */
  }

  /* ✅ แก้ไข CSS สำหรับข้อความแนะนำ ให้เป็นสีขาว */
  .text-info-white {
    color: rgba(255, 255, 255, 0.7) !important; /* สีขาวโปร่งใสเล็กน้อย */
    font-size: 0.85rem;
  }
</style>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    
    <div class="card custom-card shadow-lg mb-5">
      <div class="card-header border-bottom border-secondary p-4" style="border-color: rgba(255,255,255,0.05) !important;">
        <h4 class="fw-bold text-white mb-0 text-center">
          <i class="bi bi-plus-circle text-success me-2"></i> เพิ่มสินค้าใหม่
        </h4>
      </div>
      
      <div class="card-body p-4 p-md-5">

        <?php if(!empty($error)): ?>
          <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 text-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="row g-4">
            
            <div class="col-md-12">
              <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-custom" placeholder="กรอกชื่อสินค้า" required>
            </div>

            <div class="col-md-12">
              <label class="form-label">หมวดหมู่สินค้า <span class="text-danger">*</span></label>
              <select name="cat_id" class="form-select form-select-custom" required>
                <option value="">-- กรุณาเลือกหมวดหมู่ --</option>
                <?php foreach($cats as $c): ?>
                  <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
              <input type="number" name="price" class="form-control form-control-custom" step="0.01" min="0" placeholder="0.00" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">จำนวนสต็อก <span class="text-danger">*</span></label>
              <input type="number" name="stock" class="form-control form-control-custom" min="0" placeholder="จำนวนสินค้าที่มี" required>
            </div>

            <div class="col-12">
              <label class="form-label">รายละเอียดสินค้า (ไม่บังคับ)</label>
              <textarea name="description" rows="4" class="form-control form-control-custom" placeholder="กรอกข้อมูลเพิ่มเติมเกี่ยวกับสินค้า..."></textarea>
            </div>

            <div class="col-12">
              <label class="form-label">รูปภาพสินค้า</label>
              <input type="file" name="image" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this)">
              <small class="text-info-white d-block mt-2"><i class="bi bi-info-circle"></i> รองรับไฟล์ภาพ (JPG, PNG, GIF) ขนาดไม่ควรเกิน 2MB</small>
            </div>

            <div class="col-12 text-center d-none" id="preview-container">
              <img id="image-preview" src="" alt="Preview" class="img-thumbnail bg-dark border-secondary mt-2 shadow" style="max-height: 250px; object-fit: cover; border-radius: 10px;">
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto">
              <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 w-100 w-sm-auto fw-bold shadow-sm hover-scale">
              <i class="bi bi-check-circle me-1"></i> บันทึกข้อมูลสินค้า
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<script>
function previewImage(input) {
  const container = document.getElementById('preview-container');
  const preview = document.getElementById('image-preview');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      container.classList.remove('d-none'); // แสดงกล่องรูป
    }
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = "";
    container.classList.add('d-none'); // ซ่อนกล่องถ้ายกเลิกการเลือกรูป
  }
}
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>