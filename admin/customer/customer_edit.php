<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['admin_id'])) { 
  header("Location: ../login.php"); 
  exit;
}

// 🕒 ระบบจับเวลา Session Timeout
$timeout_duration = 600;
if (isset($_SESSION['last_activity'])) {
  $time_inactive = time() - $_SESSION['last_activity'];
  if ($time_inactive >= $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit;
  }
}
$_SESSION['last_activity'] = time();
$pageTitle = "แก้ไขข้อมูลลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "<script>alert('❌ ไม่พบรหัสลูกค้า'); window.location='customers.php';</script>";
  exit;
}

// 🔹 ดึงข้อมูลลูกค้าเดิมมาแสดง
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  echo "<script>alert('❌ ไม่พบข้อมูลลูกค้าในระบบ'); window.location='customers.php';</script>";
  exit;
}

$error = "";

// 🔹 จัดการเมื่อมีการกดปุ่มบันทึกแก้ไข
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  
  // เก็บชื่อไฟล์รูปเดิมไว้ก่อนเป็นค่าเริ่มต้น
  $fileNameToSave = $c['profile_image'];

  // 🔍 ตรวจสอบอีเมลซ้ำ
  $check = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ? AND customer_id != ?");
  $check->execute([$email, $id]);
  
  if ($check->fetchColumn() > 0) {
    $error = "อีเมลนี้ถูกใช้งานโดยลูกค้ารายอื่นแล้ว กรุณาใช้อีเมลอื่นครับ";
  } else {
    
    // 🖼️ 1. จัดการรูปลูกค้าที่แอดมินตัดมาใหม่ (Base64)
    $cropped_image = $_POST['cropped_image'] ?? '';
    
    if (!empty($cropped_image)) {
        $image_parts = explode(";base64,", $cropped_image);
        
        if (count($image_parts) == 2 && strpos($image_parts[0], 'image/') !== false) {
            $image_base64 = base64_decode($image_parts[1]);
            
            // Path ที่จะเซฟรูป (ใช้ Absolute Path เพื่อความชัวร์)
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Project/admin/uploads/profiles/";
            
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // สุ่มชื่อไฟล์ใหม่ บันทึกเป็น JPG
            $newFileName = "user_" . $id . "_" . uniqid() . ".jpg";
            $targetFile = $uploadDir . $newFileName;

            // บันทึกไฟล์รูปลง Server
            if (file_put_contents($targetFile, $image_base64)) {
                
                // 🗑️ ระบบลบรูปเก่าทิ้ง (เพื่อไม่ให้เปลืองพื้นที่ Server)
                if (!empty($c['profile_image'])) {
                    $oldFilePath = $uploadDir . $c['profile_image'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // ✅ สั่งลบไฟล์เก่าทิ้งทันที
                    }
                }
                
                $fileNameToSave = $newFileName; // อัปเดตตัวแปรเพื่อบันทึกชื่อใหม่ลงฐานข้อมูล
            } else {
                $error = "❌ ไม่สามารถบันทึกรูปภาพได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์";
            }
        }
    }

    if (empty($error)) {
        // 💾 2. บันทึกข้อมูลลงฐานข้อมูล
        $update = $conn->prepare("UPDATE customers SET name=?, email=?, phone=?, address=?, profile_image=? WHERE customer_id=?");
        $update->execute([$name, $email, $phone, $address, $fileNameToSave, $id]);
        
        echo "<script>alert('✅ บันทึกการแก้ไขข้อมูลและรูปภาพสำเร็จ!'); window.location='customers.php';</script>";
        exit;
    }
  }
}

// ✅ เตรียมข้อมูลสำหรับแสดงผลในฟอร์ม
$val_name = $_POST['name'] ?? $c['name'];
$val_email = $_POST['email'] ?? $c['email'];
$val_phone = $_POST['phone'] ?? $c['phone'];
$val_address = $_POST['address'] ?? $c['address'];

// ✅ ดึงรูปโปรไฟล์มาแสดงให้ Admin เห็น
if (!empty($c['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . "/Project/admin/uploads/profiles/" . $c['profile_image'])) {
    $profileImg = "/Project/admin/uploads/profiles/" . htmlspecialchars($c['profile_image']);
} else {
    $profileImg = "https://ui-avatars.com/api/?name=" . urlencode($c['name']) . "&background=D10024&color=fff&size=150&bold=true";
}

ob_start();
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
    border-color: #facc15; 
    box-shadow: 0 0 0 0.25rem rgba(250, 204, 21, 0.25);
    color: #fff;
  }
  
  /* ✅ สไตล์รูปโปรไฟล์และปุ่มกล้องถ่ายรูป */
  .profile-preview {
    width: 75px; 
    height: 75px; 
    object-fit: cover;
    border: 3px solid #facc15; 
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
    background-color: #facc15;
    color: #000;
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
    background-color: #eab308;
  }

  /* แต่งกล่อง Croppie ให้เข้ากับธีม Admin */
  #croppie-demo { width: 100%; height: 350px; margin-top: 10px; }
  .modal-content-custom { background-color: #1e293b; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; }
</style>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    
    <div class="card custom-card shadow-lg mb-5">
      <form method="post" enctype="multipart/form-data" id="adminEditForm">
        
        <input type="hidden" name="cropped_image" id="cropped_image">
        
        <div class="card-header border-bottom border-secondary p-4 d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
          <h4 class="fw-bold text-white mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลลูกค้า <span class="text-warning">#<?= htmlspecialchars($id) ?></span>
          </h4>
          
          <div class="position-relative d-inline-block">
            <img src="<?= $profileImg ?>" id="previewImg" alt="Customer Profile" class="profile-preview" title="รูปโปรไฟล์ของ <?= htmlspecialchars($c['name']) ?>">
            <label for="profile_image" class="upload-badge-admin" title="คลิกเพื่อเปลี่ยนรูปโปรไฟล์ลูกค้า">
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
              <input type="text" name="name" value="<?= htmlspecialchars($val_name) ?>" class="form-control form-control-custom" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">อีเมล <span class="text-danger">*</span></label>
              <input type="email" name="email" value="<?= htmlspecialchars($val_email) ?>" class="form-control form-control-custom" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">เบอร์โทรติดต่อ <span class="text-danger">*</span></label>
              <input type="tel" name="phone" value="<?= htmlspecialchars($val_phone) ?>" class="form-control form-control-custom" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required>
            </div>

            <div class="col-12">
              <label class="form-label">ที่อยู่จัดส่ง <span class="text-danger">*</span></label>
              <textarea name="address" rows="3" class="form-control form-control-custom" required><?= htmlspecialchars($val_address) ?></textarea>
            </div>
          </div>

          <hr class="border-secondary my-4" style="opacity: 0.2;">

          <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
            <a href="customers.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100 w-sm-auto text-white">
              <i class="bi bi-x-circle me-1"></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-warning rounded-pill px-5 py-2 w-100 w-sm-auto fw-bold shadow-sm hover-scale text-dark" id="mainSubmitBtn">
              <i class="bi bi-check-circle me-1"></i> บันทึกการแก้ไข
            </button>
          </div>

        </div>
      </form>
    </div>

  </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog" style="margin-top: 10vh;">
    <div class="modal-content modal-content-custom shadow-lg">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-warning fw-bold"><i class="bi bi-crop me-2"></i>ปรับขนาดรูปโปรไฟล์ลูกค้า</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center bg-black rounded-bottom p-0">
        <div id="croppie-demo"></div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between px-4 pb-4 bg-black rounded-bottom">
        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" id="cropBtn"><i class="bi bi-check2-circle me-1"></i> ยืนยันการตัดรูป</button>
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

        // แจ้งแอดมินว่าให้กดบันทึก
        const btnSubmit = document.getElementById('mainSubmitBtn');
        btnSubmit.innerHTML = "<i class='bi bi-floppy me-2'></i>บันทึกการแก้ไขและรูปภาพ!";
        btnSubmit.classList.add('btn-success');
        btnSubmit.classList.remove('btn-warning');
        btnSubmit.classList.remove('text-dark');
        btnSubmit.classList.add('text-white');

        cropModal.hide();
      });
    });
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>