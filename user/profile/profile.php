<?php
session_start();
include("../includes/connectdb.php");

// 🔒 ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("<div class='container mt-5'><div class='alert alert-danger text-center shadow-sm'>❌ ไม่พบข้อมูลผู้ใช้</div></div>");
}

// ✅ เมื่อกดบันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  $subscribe = isset($_POST['subscribe']) ? 1 : 0;
  
  // เก็บชื่อรูปเดิมไว้ก่อน (ถ้าไม่มีการเปลี่ยนรูป จะได้ใช้ชื่อเดิม)
  $fileNameToSave = $user['profile_image'];

  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "❌ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (10 หลัก)";
    header("Location: profile.php");
    exit;
  } else {
    
    // 🖼️ 1. จัดการรูปภาพที่ครอปแล้ว (รับมาเป็น Base64)
    $cropped_image = $_POST['cropped_image'] ?? '';
    
    if (!empty($cropped_image)) {
        // แยกส่วนข้อมูล Base64
        $image_parts = explode(";base64,", $cropped_image);
        
        // ตรวจสอบว่าเป็นไฟล์รูปภาพจริงไหม
        if (count($image_parts) == 2 && strpos($image_parts[0], 'image/') !== false) {
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1]; // จะได้ png เสมอจาก Cropper.js
            $image_base64 = base64_decode($image_parts[1]);

            $uploadDir = __DIR__ . "/../admin/uploads/profiles/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // ตั้งชื่อไฟล์ใหม่
            $newFileName = "user_" . $customer_id . "_" . time() . ".png";
            $targetFile = $uploadDir . $newFileName;

            // บันทึกไฟล์ใหม่ลง Server
            if (file_put_contents($targetFile, $image_base64)) {
                
                // 🗑️ 2. ระบบลบรูปเก่าทิ้ง (ถ้ามีรูปเก่าอยู่ในระบบ)
                if (!empty($user['profile_image'])) {
                    $oldFilePath = $uploadDir . $user['profile_image'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // สั่งลบไฟล์เก่าทิ้งทันที!
                    }
                }
                
                $fileNameToSave = $newFileName; // อัปเดตชื่อรูปใหม่เพื่อลง Database
            } else {
                $_SESSION['toast_error'] = "❌ เกิดข้อผิดพลาดในการบันทึกรูปภาพ";
                header("Location: profile.php");
                exit;
            }
        }
    }

    // 💾 3. บันทึกข้อมูลลงฐานข้อมูล
    $stmt = $conn->prepare("UPDATE customers 
                            SET name = ?, email = ?, phone = ?, address = ?, subscribe = ?, profile_image = ? 
                            WHERE customer_id = ?");
    $stmt->execute([$name, $email, $phone, $address, $subscribe, $fileNameToSave, $customer_id]);

    $_SESSION['customer_name'] = $name;
    $_SESSION['toast_success'] = "✅ บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว";
    header("Location: ../index.php"); 
    exit;
  }
}

// 🎯 ตั้งค่ารูปโปรไฟล์ที่จะแสดง
if (!empty($user['profile_image']) && file_exists("../admin/uploads/profiles/" . $user['profile_image'])) {
    $profileImg = "../admin/uploads/profiles/" . htmlspecialchars($user['profile_image']);
} else {
    $profileImg = "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=D10024&color=fff&size=150&bold=true";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>โปรไฟล์ของฉัน | MyCommiss</title>
  <link rel="icon" type="image/png" href="../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    .profile-wrapper { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }

    /* 🔹 Profile Card */
    .profile-card { width: 100%; max-width: 650px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; border: none; }
    .card-header-custom { background-color: #D10024; color: #fff; padding: 30px 20px 20px; text-align: center; position: relative; }
    
    /* 🔹 ไอคอนและรูปโปรไฟล์ */
    .profile-icon { width: 110px; height: 110px; background-color: #fff; border-radius: 50%; margin: 0 auto 10px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); object-fit: cover; border: 4px solid #fff; }
    .upload-badge { position: absolute; bottom: 10px; right: -5px; width: 35px; height: 35px; background-color: #ffc107; color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #D10024; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .upload-badge:hover { background-color: #e0a800; transform: scale(1.1); }

    /* 🔹 Input Fields */
    .form-control { border-radius: 0 10px 10px 0; padding: 12px 15px; background-color: #fcfcfc; border: 1px solid #e0e0e0; }
    .form-control:focus { border-color: #D10024; box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15); background-color: #fff; z-index: 1; }
    .input-group-text { border-radius: 10px 0 0 10px; background-color: #fff; border: 1px solid #e0e0e0; border-right: none; }
    .form-check-input:checked { background-color: #D10024; border-color: #D10024; }

    /* 🔹 Buttons */
    .btn-submit { background-color: #D10024; color: #fff; border-radius: 50px; font-weight: 600; padding: 12px; border: none; transition: 0.3s; }
    .btn-submit:hover { background-color: #a5001b; color: #fff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2); }
    .btn-password { background-color: #ffc107; color: #000; border-radius: 50px; font-weight: 600; padding: 12px; border: none; transition: 0.3s; }
    .btn-password:hover { background-color: #e0a800; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2); }
    .btn-outline-custom { border: 1px solid #ddd; color: #555; border-radius: 50px; font-weight: 500; padding: 10px; transition: 0.3s; background: #fff; text-align: center; display: inline-block; }
    .btn-outline-custom:hover { background-color: #f1f1f1; color: #333; }

    footer { background-color: #fff; color: #6c757d; padding: 20px; font-size: 0.9rem; border-top: 1px solid #eee; text-align: center; }
    
    /* ✅ แต่งกรอบตัดรูปให้รองรับมือถือ (Mobile UI) */
    .img-container { 
      max-height: 60vh; /* ไม่ให้รูปยาวทะลุจอในมือถือ */
      width: 100%; 
      display: flex; 
      justify-content: center; 
      background-color: #e9ecef; 
    }
    #imageToCrop { max-width: 100%; display: block; }
    
    /* แต่งกรอบวงกลมจำลองให้ดูว่าผลลัพธ์จะเป็นยังไง */
    .cropper-view-box, .cropper-face {
      border-radius: 50%;
    }
  </style>
</head>
<body>

<?php include("../includes/navbar_user.php"); ?>

<div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 3000;">
  <?php if (isset($_SESSION['toast_error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show shadow-lg" role="alert">
      <div class="d-flex">
        <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_error'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_error']); ?>
  <?php endif; ?>
</div>

<div class="profile-wrapper">
  <div class="profile-card">
    
    <form method="POST" enctype="multipart/form-data" id="profileForm">
      
      <input type="hidden" name="cropped_image" id="cropped_image">
      
      <div class="card-header-custom">
        <div class="position-relative d-inline-block">
          <img src="<?= $profileImg ?>" id="previewImg" class="profile-icon" alt="Profile Picture">
          
          <label for="profile_image" class="upload-badge" title="เปลี่ยนรูปโปรไฟล์">
            <i class="bi bi-camera-fill"></i>
          </label>
        </div>
        
        <input type="file" id="profile_image" class="d-none" accept="image/jpeg, image/png, image/webp">
        
        <h4 class="mt-2 mb-0 fw-bold">ข้อมูลส่วนตัวของฉัน</h4>
        <p class="mb-0 mt-1 fw-normal" style="font-size: 0.9rem; opacity: 0.9;">จัดการข้อมูลส่วนตัวและที่อยู่สำหรับจัดส่งสินค้า</p>
      </div>

      <div class="card-body p-4 p-md-5">
          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">ชื่อ - นามสกุล</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
              <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" placeholder="ชื่อ นามสกุล" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">อีเมล</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" placeholder="example@email.com" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">เบอร์โทรศัพท์</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text"><i class="bi bi-telephone text-muted"></i></span>
              <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                     class="form-control" maxlength="10" pattern="[0-9]{10}" title="กรุณากรอกเฉพาะตัวเลข 10 หลัก"
                     oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required placeholder="08XXXXXXXX">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">ที่อยู่จัดส่งพัสดุ</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text align-items-start pt-3"><i class="bi bi-house text-muted"></i></span>
              <textarea name="address" rows="3" class="form-control" placeholder="บ้านเลขที่, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
          </div>

          <div class="form-check mb-4 bg-light p-3 rounded-3 border">
            <input class="form-check-input ms-1" type="checkbox" id="subscribe" name="subscribe" value="1" <?= $user['subscribe'] ? 'checked' : '' ?>>
            <label class="form-check-label ms-2 text-dark fw-medium" for="subscribe">
              <i class="bi bi-bell text-warning me-1"></i> สมัครรับข่าวสารและโปรโมชั่นพิเศษจากเรา
            </label>
          </div>

          <hr class="text-muted opacity-25 my-4">

          <div class="d-grid gap-3">
            <button type="submit" class="btn btn-submit fs-5">
              <i class="bi bi-floppy me-2"></i>บันทึกข้อมูล
            </button>
            
            <div class="row g-2 mt-2">
              <div class="col-sm-6 d-grid">
                <a href="../index.php" class="btn btn-outline-custom">
                  <i class="bi bi-arrow-left me-2"></i>กลับหน้าร้าน
                </a>
              </div>
              <div class="col-sm-6 d-grid">
                <a href="change_password.php" class="btn btn-password shadow-sm">
                  <i class="bi bi-shield-lock me-2"></i>เปลี่ยนรหัสผ่าน
                </a>
              </div>
            </div>
          </div>

      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white border-0">
        <h5 class="modal-title"><i class="bi bi-crop me-2"></i>เลื่อนและซูมเพื่อปรับรูป</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 bg-dark">
        <div class="img-container">
          <img id="imageToCrop" src="" alt="Picture">
        </div>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary rounded-pill px-4" id="cropBtn"><i class="bi bi-check2-circle me-1"></i> ยืนยันรูปนี้</button>
      </div>
    </div>
  </div>
</div>

<footer>
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
  let cropper;
  const imageInput = document.getElementById('profile_image');
  const imageToCrop = document.getElementById('imageToCrop');
  const cropModalElement = document.getElementById('cropModal');
  const cropModal = new bootstrap.Modal(cropModalElement);
  const previewImg = document.getElementById('previewImg');
  const hiddenCroppedInput = document.getElementById('cropped_image');

  // 1. เมื่อผู้ใช้เลือกรูป
  imageInput.addEventListener('change', function (e) {
    const files = e.target.files;
    
    if (files && files.length > 0) {
      const file = files[0];
      
      // เช็คขนาดไฟล์ฝั่ง Front-end (ไม่เกิน 5MB)
      if(file.size > 5 * 1024 * 1024) {
          alert("❌ ขนาดไฟล์รูปภาพใหญ่เกินไป (จำกัดไม่เกิน 5MB)");
          imageInput.value = ''; 
          return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        imageToCrop.src = event.target.result;
        cropModal.show(); // เปิด Modal
      };
      reader.readAsDataURL(file);
    }
  });

  // 2. เมื่อ Modal เปิดขึ้นมา ให้เริ่มการทำงานของ Cropper.js (ตั้งค่าให้ใช้ง่ายบนมือถือ)
  cropModalElement.addEventListener('shown.bs.modal', function () {
    cropper = new Cropper(imageToCrop, {
      aspectRatio: 1 / 1, // บังคับอัตราส่วน 1:1
      viewMode: 1, // ป้องกันไม่ให้ลากรูปหลุดขอบ
      dragMode: 'move', // ✅ ไฮไลท์: ให้ใช้นิ้ว "เลื่อนรูปภาพ" แทนการวาดกรอบ (เหมาะกับมือถือสุดๆ)
      autoCropArea: 1, // ซูมกรอบให้ใหญ่เต็มพื้นที่
      restore: false,
      guides: false, // ปิดเส้นตารางตารางเกะกะ
      center: true,
      highlight: false,
      cropBoxMovable: false, // ล็อกตำแหน่งกรอบไว้ตรงกลาง
      cropBoxResizable: false, // ล็อกขนาดกรอบ ห้ามหดห้ามขยาย ให้ใช้วิธีซูมรูปเอา
      toggleDragModeOnDblclick: false,
    });
  });

  // 3. เมื่อปิด Modal ให้ล้างค่า
  cropModalElement.addEventListener('hidden.bs.modal', function () {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    imageInput.value = ''; 
  });

  // 4. เมื่อกดปุ่ม "ยืนยันรูปนี้"
  document.getElementById('cropBtn').addEventListener('click', function () {
    if (!cropper) return;

    // ตัดรูปออกมาขนาด 400x400 (คมชัดและไฟล์ไม่ใหญ่)
    const canvas = cropper.getCroppedCanvas({
      width: 400,
      height: 400,
    });

    const base64Image = canvas.toDataURL('image/png');

    // เอาไปโชว์ให้ลูกค้าดู
    previewImg.src = base64Image;

    // ยัดใส่ Input ซ่อน
    hiddenCroppedInput.value = base64Image;

    cropModal.hide();
  });

  // Toast Alert
  document.addEventListener("DOMContentLoaded", () => {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(toastEl => {
      const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
      toast.show();
    });
  });
</script>

</body>
</html>