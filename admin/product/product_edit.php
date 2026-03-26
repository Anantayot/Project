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

$pageTitle = "แก้ไขสินค้า";
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if(!$id) {
  echo "<script>alert('❌ ไม่พบรหัสสินค้า'); window.location='products.php';</script>";
  exit;
}

// 🔹 ดึงข้อมูลสินค้าเดิม
$product = $conn->prepare("SELECT * FROM product WHERE p_id=?");
$product->execute([$id]);
$p = $product->fetch(PDO::FETCH_ASSOC);

if (!$p) {
  echo "<script>alert('❌ ไม่พบข้อมูลสินค้าในระบบ'); window.location='products.php';</script>";
  exit;
}

// 🔹 ดึงหมวดหมู่ทั้งหมด
$cats = $conn->query("SELECT * FROM category ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$error = "";

// 🔹 จัดการอัปเดตข้อมูลเมื่อกด Submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name   = trim($_POST['name']);
  $price  = $_POST['price'];
  $stock  = $_POST['stock'];
  $cat_id = $_POST['cat_id'];
  $desc   = trim($_POST['description']);

  // flag ว่าผู้ใช้ติ๊ก "ลบรูปเดิม" ไหม
  $deleteImage = isset($_POST['delete_image']) && $_POST['delete_image'] === '1';

  // path โฟลเดอร์อัปโหลด
  $uploadDir = __DIR__ . "/../uploads/";

  // ค่า default = รูปเดิมในฐานข้อมูล
  $image = $p['p_image'];

  try {
    // 1) ถ้าติ๊ก "ลบรูปเดิม" ให้ลบไฟล์เก่าทิ้งก่อน
    if ($deleteImage && !empty($p['p_image'])) {
      $oldPath = $uploadDir . $p['p_image'];
      if (file_exists($oldPath)) {
        unlink($oldPath); // ลบไฟล์ออกจากเซิร์ฟเวอร์
      }
      $image = null; // ล้างค่าในฐานข้อมูล
    }

    // 2) ถ้ามีอัปโหลดรูปใหม่เข้ามา
    if (!empty($_FILES['image']['name'])) {
      // ถ้ามีรูปเดิม และยังไม่ถูกลบในขั้นตอนข้างบน ก็ลบด้วย (กันไฟล์ขยะค้าง)
      if (!$deleteImage && !empty($p['p_image'])) {
        $oldPath = $uploadDir . $p['p_image'];
        if (file_exists($oldPath)) {
          unlink($oldPath);
        }
      }

      // สร้างชื่อไฟล์ใหม่กันซ้ำ และตัดอักขระพิเศษออก
      $image = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['image']['name']));

      // ถ้าโฟลเดอร์ยังไม่มีให้สร้าง
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      // ย้ายไฟล์อัปโหลด
      if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
        throw new Exception("ไม่สามารถอัปโหลดรูปภาพได้");
      }
    }

    // 3) อัปเดตฐานข้อมูล
    $stmt = $conn->prepare("UPDATE product 
                            SET p_name=?, p_price=?, p_stock=?, p_description=?, p_image=?, cat_id=? 
                            WHERE p_id=?");
    $stmt->execute([$name, $price, $stock, $desc, $image, $cat_id, $id]);

    echo "<script>alert('✅ บันทึกการแก้ไขสินค้าสำเร็จ!'); window.location='products.php';</script>";
    exit;

  } catch (Exception $e) {
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
    border-color: #facc15; /* สีเหลืองทองโหมดแก้ไข */
    box-shadow: 0 0 0 0.25rem rgba(250, 204, 21, 0.25);
    color: #fff;
  }
  .form-control-custom::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
  .form-select-custom option {
    background-color: #1e293b;
    color: #fff;
  }
  
  /* แต่งช่องอัปโหลดไฟล์ */
  input[type="file"].form-control-custom::file-selector-button {
    background-color: #2c313a; 
    color: #fff;
    border: none;
    padding: 8px 15px;
    margin-right: 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
  }
  input[type="file"].form-control-custom::file-selector-button:hover {
    background-color: #facc15; /* Hover เป็นสีเหลือง */
    color: #000;
  }
  
  .text-info-white {
    color: rgba(255, 255, 255, 0.7) !important;
    font-size: 0.85rem;
  }
</style>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    
    <div class="card custom-card shadow-lg mb-5">
      <div class="card-header border-bottom border-secondary p-4" style="border-color: rgba(255,255,255,0.05) !important;">
        <h4 class="fw-bold text-white mb-0 text-center">
          <i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขสินค้า <span class="text-warning">#<?= htmlspecialchars($id) ?></span>
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
              <input type="text" name="name" value="<?= htmlspecialchars($p['p_name']) ?>" class="form-control form-control-custom" required>
            </div>

            <div class="col-md-12">
              <label class="form-label">หมวดหมู่สินค้า <span class="text-danger">*</span></label>
              <select name="cat_id" class="form-select form-select-custom" required>
                <option value="">-- กรุณาเลือกหมวดหมู่ --</option>
                <?php foreach($cats as $c): ?>
                  <option value="<?= $c['cat_id'] ?>" <?= $p['cat_id'] == $c['cat_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['cat_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
              <input type="number" name="price" value="<?= $p['p_price'] ?>" class="form-control form-control-custom" step="0.01" min="0" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">จำนวนสต็อก <span class="text-danger">*</span></label>
              <input type="number" name="stock" value="<?= $p['p_stock'] ?>" class="form-control form-control-custom" min="0" required>
            </div>

            <div class="col-12">
              <label class="form-label">รายละเอียดสินค้า</label>
              <textarea name="description" rows="4" class="form-control form-control-custom"><?= htmlspecialchars($p['p_description'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label">รูปภาพปัจจุบัน</label>
              <div class="p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1);">
                <?php 
                  $imagePath = "../uploads/" . htmlspecialchars($p['p_image']);
                  $fileOnDisk = __DIR__ . "/../uploads/" . $p['p_image'];
                  
                  if (!empty($p['p_image']) && file_exists($fileOnDisk)): 
                ?>
                  <div class="d-flex align-items-center gap-3">
                    <img src="<?= $imagePath ?>" class="rounded shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #334155;">
                    
                    <div class="form-check form-switch mt-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="delete_image" name="delete_image" value="1">
                      <label class="form-check-label text-danger" for="delete_image" style="cursor: pointer;">
                        <i class="bi bi-trash"></i> ลบรูปภาพเดิมทิ้ง
                      </label>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="text-muted"><i class="bi bi-image"></i> สินค้านี้ยังไม่มีรูปภาพ</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">อัปโหลดรูปภาพใหม่ <span class="text-muted" style="font-size: 0.85rem;">(หากต้องการเปลี่ยนรูป)</span></label>
              <input type="file" name="image" class="form-control form-control-custom" accept="image/*" onchange="previewImage(this)">
              <small class="text-info-white d-block mt-2"><i class="bi bi-info-circle"></i> รองรับไฟล์ภาพ (JPG, PNG, GIF) ขนาดไม่ควรเกิน 2MB</small>
            </div>

            <div class="col-12 text-center d-none" id="preview-container">
              <label class="form-label d-block text-start text-warning">ตัวอย่างรูปภาพใหม่ที่จะใช้:</label>
              <img id="image-preview" src="" alt="Preview" class="img-thumbnail bg-dark border-secondary shadow" style="max-height: 250px; object-fit: cover; border-radius: 10px;">
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto">
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

<script>
function previewImage(input) {
  const container = document.getElementById('preview-container');
  const preview = document.getElementById('image-preview');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      container.classList.remove('d-none'); // แสดงรูปตัวอย่างใหม่
    }
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = "";
    container.classList.add('d-none'); // ซ่อนถ้ากดยกเลิก
  }
}
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>