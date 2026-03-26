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
  
  $fileNameToSave = $user['profile_image'];

  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "❌ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (10 หลัก)";
    header("Location: profile.php");
    exit;
  } else {
    
    // 🖼️ 1. จัดการอัปโหลดไฟล์รูปภาพโดยตรง (ไม่ใช้ Base64 แล้ว)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        
        $tmpName = $_FILES['profile_image']['tmp_name'];
        $fileSize = $_FILES['profile_image']['size'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp']; 

        // ตรวจสอบขนาด (ไม่เกิน 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            $_SESSION['toast_error'] = "❌ ขนาดรูปภาพใหญ่เกินไป (จำกัด 5MB)";
            header("Location: profile.php");
            exit;
        }

        // ตรวจสอบนามสกุล
        if (in_array($ext, $allowed)) {
            $uploadDir = "../admin/uploads/profiles/";
            
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // สุ่มชื่อไฟล์ใหม่
            $newFileName = "user_" . $customer_id . "_" . uniqid() . "." . $ext;
            $targetFile = $uploadDir . $newFileName;

            // ย้ายไฟล์ลง Server
            if (move_uploaded_file($tmpName, $targetFile)) {
                
                // ลบรูปเก่าทิ้ง
                if (!empty($user['profile_image'])) {
                    $oldFilePath = $uploadDir . $user['profile_image'];
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath); 
                    }
                }
                
                $fileNameToSave = $newFileName;
            } else {
                $_SESSION['toast_error'] = "❌ ไม่สามารถบันทึกรูปภาพได้";
                header("Location: profile.php");
                exit;
            }
        } else {
            $_SESSION['toast_error'] = "❌ รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น";
            header("Location: profile.php");
            exit;
        }
    }

    // 💾 2. บันทึกข้อมูลลงฐานข้อมูล
    $stmt = $conn->prepare("UPDATE customers 
                            SET name = ?, email = ?, phone = ?, address = ?, subscribe = ?, profile_image = ? 
                            WHERE customer_id = ?");
    $stmt->execute([$name, $email, $phone, $address, $subscribe, $fileNameToSave, $customer_id]);

    $_SESSION['customer_name'] = $name;
    $_SESSION['toast_success'] = "✅ บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว";
    header("Location: profile.php"); 
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>โปรไฟล์ของฉัน | MyCommiss</title>
  <link rel="icon" type="image/png" href="../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    .profile-wrapper { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }

    .profile-card { width: 100%; max-width: 650px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; border: none; }
    .card-header-custom { background-color: #D10024; color: #fff; padding: 30px 20px 20px; text-align: center; position: relative; }
    
    /* ✅ ให้รูปถูกตัดเป็นวงกลมตรงกลางเสมอ ด้วย object-fit: cover */
    .profile-icon { 
      width: 130px; 
      height: 130px; 
      background-color: #fff; 
      border-radius: 50%; 
      margin: 0 auto 10px auto; 
      box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
      object-fit: cover; 
      object-position: center; /* ให้อยู่ตรงกลางรูปเสมอ */
      border: 4px solid #fff; 
      transition: 0.3s;
    }
    
    .upload-badge { position: absolute; bottom: 10px; right: -5px; width: 35px; height: 35px; background-color: #ffc107; color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #D10024; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .upload-badge:hover { background-color: #e0a800; transform: scale(1.1); }

    .form-control { border-radius: 0 10px 10px 0; padding: 12px 15px; background-color: #fcfcfc; border: 1px solid #e0e0e0; }
    .form-control:focus { border-color: #D10024; box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15); background-color: #fff; z-index: 1; }
    .input-group-text { border-radius: 10px 0 0 10px; background-color: #fff; border: 1px solid #e0e0e0; border-right: none; }
    .form-check-input:checked { background-color: #D10024; border-color: #D10024; }

    .btn-submit { background-color: #D10024; color: #fff; border-radius: 50px; font-weight: 600; padding: 12px; border: none; transition: 0.3s; }
    .btn-submit:hover { background-color: #a5001b; color: #fff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2); }
    .btn-password { background-color: #ffc107; color: #000; border-radius: 50px; font-weight: 600; padding: 12px; border: none; transition: 0.3s; }
    .btn-password:hover { background-color: #e0a800; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2); }
    .btn-outline-custom { border: 1px solid #ddd; color: #555; border-radius: 50px; font-weight: 500; padding: 10px; transition: 0.3s; background: #fff; text-align: center; display: inline-block; }
    .btn-outline-custom:hover { background-color: #f1f1f1; color: #333; }

    footer { background-color: #fff; color: #6c757d; padding: 20px; font-size: 0.9rem; border-top: 1px solid #eee; text-align: center; }
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

  <?php if (isset($_SESSION['toast_success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
      <div class="d-flex">
        <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_success']); ?>
  <?php endif; ?>
</div>

<div class="profile-wrapper">
  <div class="profile-card">
    <form method="POST" enctype="multipart/form-data">
      
      <div class="card-header-custom">
        <div class="position-relative d-inline-block">
          <img src="<?= $profileImg ?>" id="previewImg" class="profile-icon" alt="Profile Picture">
          
          <label for="profile_image" class="upload-badge" title="เปลี่ยนรูปโปรไฟล์">
            <i class="bi bi-camera-fill"></i>
          </label>
        </div>
        
        <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/jpeg, image/png, image/webp" onchange="previewFile(this)">
        
        <h4 class="mt-2 mb-0 fw-bold">ข้อมูลส่วนตัวของฉัน</h4>
        <p class="mb-0 mt-1 fw-normal" style="font-size: 0.9rem; opacity: 0.9;">จัดการข้อมูลส่วนตัวและที่อยู่สำหรับจัดส่งสินค้า</p>
      </div>

      <div class="card-body p-4 p-md-5">
          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">ชื่อ - นามสกุล</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
              <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary mb-1">อีเมล</label>
            <div class="input-group shadow-sm">
              <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
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
              <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
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
            <button type="submit" class="btn btn-submit fs-5" id="mainSubmitBtn">
              <i class="bi bi-floppy me-2"></i>บันทึกข้อมูล
            </button>
            <div class="row g-2 mt-2">
              <div class="col-sm-6 d-grid">
                <a href="../index.php" class="btn btn-outline-custom"><i class="bi bi-arrow-left me-2"></i>กลับหน้าร้าน</a>
              </div>
              <div class="col-sm-6 d-grid">
                <a href="change_password.php" class="btn btn-password shadow-sm"><i class="bi bi-shield-lock me-2"></i>เปลี่ยนรหัสผ่าน</a>
              </div>
            </div>
          </div>
      </div>
    </form>
  </div>
</div>

<footer>
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // ✅ โค้ด Preview รูปภาพแบบง่ายๆ เบาหวิว
  function previewFile(input) {
    const file = input.files[0];
    
    if (file) {
        // เช็คขนาดไฟล์เบื้องต้น
        if(file.size > 5 * 1024 * 1024) {
            alert("❌ ไฟล์รูปใหญ่เกินไป (ต้องไม่เกิน 5MB)");
            input.value = '';
            return;
        }

        // อ่านไฟล์แล้วโชว์ในแท็ก img
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            
            // แจ้งเตือนให้กดบันทึก
            const btnSubmit = document.getElementById('mainSubmitBtn');
            btnSubmit.innerHTML = "<i class='bi bi-floppy me-2'></i>บันทึกเพื่อเปลี่ยนรูปภาพ!";
            btnSubmit.classList.add('btn-warning');
            btnSubmit.classList.remove('btn-submit');
            btnSubmit.style.color = '#000';
        }
        reader.readAsDataURL(file);
    }
  }

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