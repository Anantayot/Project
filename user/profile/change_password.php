<?php
session_start();
include("../includes/connectdb.php");

// 🔒 ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ เมื่อมีการส่งฟอร์มเปลี่ยนรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old_password = $_POST['old_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  // ✅ ดึงข้อมูลผู้ใช้จากฐานข้อมูล
  $stmt = $conn->prepare("SELECT password FROM customers WHERE customer_id = ?");
  $stmt->execute([$customer_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    $_SESSION['toast_error'] = "❌ ไม่พบข้อมูลผู้ใช้";
    header("Location: change_password.php");
    exit;
  }

  // ✅ ตรวจสอบรหัสผ่านเก่า
  if (!password_verify($old_password, $user['password'])) {
    $_SESSION['toast_error'] = "❌ รหัสผ่านเดิมไม่ถูกต้อง";
    header("Location: change_password.php");
    exit;
  }

  // ✅ ตรวจสอบว่ารหัสใหม่ตรงกันไหม
  if ($new_password !== $confirm_password) {
    $_SESSION['toast_error'] = "❌ รหัสผ่านใหม่ไม่ตรงกัน";
    header("Location: change_password.php");
    exit;
  }

  // ✅ บันทึกรหัสใหม่ (เข้ารหัสก่อน)
  $hashed = password_hash($new_password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE customer_id = ?");
  $stmt->execute([$hashed, $customer_id]);

  $_SESSION['toast_success'] = "✅ เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
  header("Location: profile.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เปลี่ยนรหัสผ่าน | MyCommiss</title>
  <link rel="icon" type="image/png" href="../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Prompt", sans-serif;
      color: #333;
    }
    
    .password-wrapper {
      min-height: 80vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 15px;
    }

    /* 🔹 Card */
    .card-password {
      width: 100%;
      max-width: 500px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      overflow: hidden;
      border: none;
    }

    .card-header-custom {
      background-color: #D10024;
      color: #fff;
      font-weight: 700;
      font-size: 1.25rem;
      padding: 25px 20px;
      text-align: center;
      position: relative;
    }
    
    /* ไอคอนด้านบน */
    .lock-icon {
      width: 70px;
      height: 70px;
      background-color: #fff;
      color: #D10024;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 15px auto;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* 🔹 Input Fields */
    .form-control {
      border-radius: 0 10px 10px 0;
      padding: 12px 15px;
      background-color: #fcfcfc;
      border: 1px solid #e0e0e0;
    }
    .form-control:focus {
      border-color: #D10024;
      box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15);
      background-color: #fff;
      z-index: 1;
    }
    .input-group-text {
      border-radius: 10px 0 0 10px;
      background-color: #fff;
      border: 1px solid #e0e0e0;
      border-right: none;
    }

    /* 🔹 Buttons */
    .btn-submit {
      background-color: #D10024;
      color: #fff;
      border-radius: 50px;
      font-weight: 600;
      padding: 12px;
      border: none;
      transition: 0.3s;
    }
    .btn-submit:hover {
      background-color: #a5001b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
    }

    .btn-outline-custom {
      border: 1px solid #ddd;
      color: #555;
      border-radius: 50px;
      font-weight: 500;
      padding: 10px;
      transition: 0.3s;
      background: #fff;
      text-align: center;
      display: inline-block;
    }
    .btn-outline-custom:hover { background-color: #f1f1f1; color: #333; }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      text-align: center;
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

<div class="password-wrapper">
  <div class="card-password">
    
    <div class="card-header-custom">
      <div class="lock-icon">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <span>เปลี่ยนรหัสผ่าน</span>
      <p class="mb-0 mt-1 fw-normal" style="font-size: 0.85rem; opacity: 0.9;">ตั้งรหัสผ่านที่คาดเดายากเพื่อความปลอดภัย</p>
    </div>

    <div class="card-body p-4 p-md-5">
      <form method="POST">
        
        <div class="mb-4">
          <label class="form-label fw-semibold text-secondary mb-1">รหัสผ่านเดิม</label>
          <div class="input-group shadow-sm">
            <span class="input-group-text"><i class="bi bi-unlock text-muted"></i></span>
            <input type="password" name="old_password" class="form-control" placeholder="กรอกรหัสผ่านปัจจุบัน" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold text-secondary mb-1">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)</label>
          <div class="input-group shadow-sm">
            <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" name="new_password" class="form-control" placeholder="••••••••" minlength="6" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold text-secondary mb-1">ยืนยันรหัสผ่านใหม่</label>
          <div class="input-group shadow-sm">
            <span class="input-group-text"><i class="bi bi-check-circle text-muted"></i></span>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" minlength="6" required>
          </div>
        </div>

        <hr class="text-muted opacity-25 my-4">

        <div class="d-grid gap-3">
          <button type="submit" class="btn btn-submit fs-5">
            <i class="bi bi-check2-circle me-2"></i>ยืนยันการเปลี่ยนรหัสผ่าน
          </button>
          
          <a href="profile.php" class="btn btn-outline-custom mt-2">
            <i class="bi bi-arrow-left me-2"></i>ยกเลิกและกลับหน้าโปรไฟล์
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<footer>
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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