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

$pageTitle = "เพิ่มข้อมูลลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

$error = "";

// ✅ ตัวแปรสำหรับจำค่าที่กรอกไว้ (เวลา Error จะได้ไม่ต้องพิมพ์ใหม่)
$val_name = '';
$val_email = '';
$val_phone = '';
$val_address = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $val_name = trim($_POST['name']);
  $val_email = trim($_POST['email']);
  $val_phone = trim($_POST['phone']);
  $val_address = trim($_POST['address']);
  
  $fileNameToSave = NULL; // ตั้งค่าเริ่มต้นให้รูปเป็น Null (ถ้าไม่ได้อัปโหลด)

  // 🔍 ตรวจสอบอีเมลซ้ำก่อนเพิ่ม
  $check = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
  $check->execute([$val_email]);
  
  if ($check->fetchColumn() > 0) {
    $error = "อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่นครับ";
  } else {
    
    // 🖼️ 1. จัดการรูปลูกค้าที่แอดมินตัดมา (Base64)
    $cropped_image = $_POST['cropped_image'] ?? '';
    
    if (!empty($cropped_image)) {
        $image_parts = explode(";base64,", $cropped_image);
        
        if (count($image_parts) == 2 && strpos($image_parts[0], 'image/') !== false) {
            $image_base64 = base64_decode($image_parts[1]);
            
            // Path ที่จะเซฟรูป 
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Project/admin/uploads/profiles/";
            
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // สุ่มชื่อไฟล์ใหม่ บันทึกเป็น JPG
            $newFileName = "user_new_" . uniqid() . ".jpg";
            $targetFile = $uploadDir . $newFileName;

            // บันทึกไฟล์รูปลง Server
            if (file_put_contents($targetFile, $image_base64)) {
                $fileNameToSave = $newFileName; 
            } else {
                $error = "❌ ไม่สามารถบันทึกรูปภาพได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์";
            }
        }
    }

    if (empty($error)) {
        // 💾 2. บันทึกข้อมูลลงฐานข้อมูล (เพิ่มช่อง profile_image ด้วย)
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, profile_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$val_name, $val_email, $val_phone, $val_address, $fileNameToSave]);
        
        // แจ้งเตือนเมื่อสำเร็จและเด้งกลับไปหน้าจัดการลูกค้า
        echo "<script>alert('✅ เพิ่มข้อมูลลูกค้าและรูปภาพสำเร็จ!'); window.location='customers.php';</script>";
        exit;
    }
  }
}

// ✅ เริ่มเก็บ output หลังจากเช็ค POST เสร็จ
ob_start();

// ตั้งค่ารูปโปรไฟล์เริ่มต้น (Default) เป็นสีเขียว
$profileImg = "https://ui-avatars.com/api/?name=New&background=22c55e&color=fff&size=150&bold=true";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />

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

  /* ✅ สไตล์รูปโปรไฟล์และปุ่มกล้องถ่ายรูป */
  .profile-preview {
    width: 75px; 
    height: 75px; 
    object-fit: cover;
    border: 3px solid #22c55e; 
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    background-color: #fff;
  }
  .upload-badge-admin {
    position: absolute;
    bottom: -5px;
    right: -5px;
    width: 30px;
    height: 30px;
    background-color: #22c55e;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #1e293b;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
  }
  .upload-badge-admin:hover {
    transform: scale(1.1);
    background-color: #16a34a;
  }

  /* แต่งกล่อง Croppie */
  #croppie-demo { width: 100%; height: 350px; margin-top: 10px; }
  .modal-content-custom { background-color: #1e293b; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; }
</style>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    
    <div class="card custom-card shadow-lg mb-5">
      <form method="post" enctype="multipart/form-data" id="adminAddForm">
        
        <input type="hidden" name="cropped_image" id="cropped_image">

        <div class="card-header border-bottom border-secondary p-4 d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
          <h4 class="fw-bold text-white mb-0">
            <i class="bi bi-person-plus text-success me-2"></i> เพิ่มลูกค้าใหม่
          </h4>

          <div class="position-relative d-inline-block">
            <img src="<?= $profileImg ?>" id="previewImg" alt="New Customer Profile" class="profile-preview">
            <label for="profile_image" class="upload-badge-admin" title="คลิกเพื่อใส่รูปโปรไฟล์ลูกค้า">
              <i class="bi bi-camera-fill"></i>
            </label>
          </div>
          <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/jpeg, image/png, image/webp">

        </div>
        
        <div class="card-body p-4 p-md-5">
          
          <?php if(!empty($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 text-center">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
            </div>
          <?php endif; ?>

          <div class="row g-4">
            
            <div class="col-md-12">
              <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
              <input type="text" name="name" value="<?= htmlspecialchars($val_name) ?>" class="form-control form-control-custom" placeholder="กรอกชื่อและนามสกุล" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">อีเมล <span class="text-danger">*</span></label>
              <input type="email" name="email" value="<?= htmlspecialchars($val_email) ?>" class="form-control form-control-custom" placeholder="mycommiss@email.com" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">เบอร์โทรติดต่อ <span class="text-danger">*</span></label>
              <input type="tel" name="phone" value="<?= htmlspecialchars($val_phone) ?>" class="form-control form-control-custom" placeholder="08x-xxx-xxxx" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required>
            </div>

            <div class="col-12">
              <label class="form-label">ที่อยู่จัดส่ง <span class="text-danger">*</span></label>
              <textarea name="address" rows="3" class="form-control form-control-custom" placeholder="กรอกที่อยู่สำหรับจัดส่งสินค้า..." required><?= htmlspecialchars($val_address) ?></textarea>
            </div>

          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="customers.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto text-white">
              <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 w-100 w-sm-auto fw-bold shadow-sm hover-scale text-white" id="mainSubmitBtn">
              <i class="bi bi-check-circle me-1"></i> บันทึกข้อมูล
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog" style="margin-top: 10vh;">
    <div class="modal-content modal-content-custom shadow-lg border-0">
      <div class="modal-header border-bottom border-secondary" style="border-radius: 15px 15px 0 0;">
        <h5 class="modal-title text-success fw-bold"><i class="bi bi-crop me-2"></i>ปรับขนาดรูปลูกค้า</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center bg-black p-0">
        <div id="croppie-demo"></div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between px-4 pb-4 bg-black" style="border-radius: 0 0 15px 15px;">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold text-white" id="cropBtn"><i class="bi bi-check2-circle me-1"></i> ยืนยันการตัดรูป</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // 🌟 ระบบตัดรูป (Croppie)
    let croppieInstance = null;
    const cropModalElement = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalElement); 
    const imageInput = document.getElementById('profile_image');

    imageInput.addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        if(file.size > 5 * 1024 * 1024) {
            alert("❌ ไฟล์รูปใหญ่เกินไป (ต้องไม่เกิน 5MB)");
            imageInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
          cropModal.show(); 

          cropModalElement.addEventListener('shown.bs.modal', function initCroppie() {
            if (croppieInstance) { croppieInstance.destroy(); }

            croppieInstance = new Croppie(document.getElementById('croppie-demo'), {
              viewport: { width: 220, height: 220, type: 'circle' }, 
              boundary: { width: '100%', height: 300 }, 
              showZoomer: true, 
            });

            croppieInstance.bind({
              url: event.target.result
            });
            
            cropModalElement.removeEventListener('shown.bs.modal', initCroppie);
          });
        };
        reader.readAsDataURL(file);
      }
    });

    cropModalElement.addEventListener('hidden.bs.modal', function () {
      if (croppieInstance) {
        croppieInstance.destroy();
        croppieInstance = null;
      }
      imageInput.value = ''; 
    });

    document.getElementById('cropBtn').addEventListener('click', function () {
      if (!croppieInstance) return;

      croppieInstance.result({
        type: 'base64',
        format: 'jpeg', 
        size: { width: 400, height: 400 },
        quality: 0.9 
      }).then(function (base64) {
        
        document.getElementById('previewImg').src = base64;
        document.getElementById('cropped_image').value = base64;

        // เปลี่ยนข้อความปุ่มให้รู้ว่ามีรูปภาพติดมาด้วย
        const btnSubmit = document.getElementById('mainSubmitBtn');
        btnSubmit.innerHTML = "<i class='bi bi-floppy me-2'></i>บันทึกข้อมูลและรูปภาพ!";
        
        cropModal.hide();
      });
    });
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>