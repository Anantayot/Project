<?php
session_start();
include("connectdb.php");

// 🔒 ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
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
  <title>เปลี่ยนรหัสผ่าน | MyCommiss</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --red: #D10024;
      --light-bg: #f8f9fa;
    }

    body {
      background-color: var(--light-bg);
      font-family: "Prompt", sans-serif;
    }

    .card {
      max-width: 500px;
      margin: 60px auto;
      border-radius: 15px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .card-header {
      background: var(--red);
      color: #fff;
      font-weight: 600;
      border-radius: 15px 15px 0 0;
      text-align: center;
      font-size: 1.2rem;
      letter-spacing: 0.5px;
    }

    .form-label {
      font-weight: 500;
      color: #333;
    }

    .btn {
      border-radius: 10px;
      transition: all 0.2s ease-in-out;
      font-weight: 500;
    }
    .btn:hover { transform: scale(1.05); }

    /* ปุ่มธีมแดง */
    .btn-primary, .btn-outline-primary:hover {
      background-color: var(--red);
      border-color: var(--red);
      color: #fff;
    }

    .btn-outline-primary {
      border-color: var(--red);
      color: var(--red);
    }

    footer {
      background-color: var(--red);
      color: white;
      padding: 15px;
      margin-top: 60px;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<!-- 🔔 Toast แจ้งเตือน -->
<div class="toast-container">
  <?php if (isset($_SESSION['toast_error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><?= $_SESSION['toast_error'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_error']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['toast_success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_success']); ?>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">🔑 เปลี่ยนรหัสผ่าน</div>
  <div class="card-body p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">รหัสผ่านเดิม</label>
        <input type="password" name="old_password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">รหัสผ่านใหม่</label>
        <input type="password" name="new_password" class="form-control" minlength="6" required>
      </div>

      <div class="mb-3">
        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <a href="profile.php" class="btn btn-outline-primary">⬅️ กลับ</a>
        <button type="submit" class="btn btn-primary">💾 เปลี่ยนรหัสผ่าน</button>
      </div>
    </form>
  </div>
</div>

<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | เปลี่ยนรหัสผ่าน
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ✅ Toast แสดง 5 วิ แล้วปิดอัตโนมัติ
  document.addEventListener("DOMContentLoaded", () => {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(toastEl => {
      const toast = new bootstrap.Toast(toastEl, { delay: 5000, autohide: true });
      toast.show();
    });
  });
</script>

</body>
</html>
