<?php
include("connectdb.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm = trim($_POST['confirm']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  $subscribe = isset($_POST['subscribe']) ? 1 : 0; // ✅ รับค่าจาก checkbox

  // ✅ ตรวจสอบเบอร์โทรศัพท์ (ต้องเป็นตัวเลข 10 หลัก)
  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "⚠️ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (เฉพาะตัวเลข 10 หลัก)";
    header("Location: register.php");
    exit;
  }

  // ✅ ตรวจสอบรหัสผ่านตรงกันไหม
  if ($password !== $confirm) {
    $_SESSION['toast_error'] = "❌ รหัสผ่านไม่ตรงกัน";
    header("Location: register.php");
    exit;
  }

  // ✅ ตรวจสอบอีเมลซ้ำ
  $check = $conn->prepare("SELECT * FROM customers WHERE email = ?");
  $check->execute([$email]);
  if ($check->rowCount() > 0) {
    $_SESSION['toast_error'] = "⚠️ อีเมลนี้ถูกใช้ไปแล้ว";
    header("Location: register.php");
    exit;
  }

  // ✅ เข้ารหัสรหัสผ่าน
  $hashed = password_hash($password, PASSWORD_DEFAULT);

  // ✅ บันทึกข้อมูลลงฐานข้อมูล
  $stmt = $conn->prepare("
    INSERT INTO customers (name, email, password, phone, address, subscribe)
    VALUES (:name, :email, :password, :phone, :address, :subscribe)
  ");
  $stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':password' => $hashed,
    ':phone' => $phone,
    ':address' => $address,
    ':subscribe' => $subscribe
  ]);

  // ✅ Toast สำเร็จ
  $_SESSION['toast_success'] = "✅ สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>สมัครสมาชิก | MyCommiss</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #ffffff;
      font-family: "Prompt", sans-serif;
      color: #212529;
    }

    /* 🔹 Card */
    .card {
      border: none;
      border-radius: 12px;
    }
    .card-header {
      background-color: #D10024;
      color: #fff;
      font-weight: 700;
      font-size: 1.2rem;
      letter-spacing: 0.5px;
    }

    /* 🔹 Buttons */
    .btn-primary {
      background-color: #D10024;
      border: none;
      border-radius: 8px;
      font-weight: 600;
    }
    .btn-primary:hover {
      background-color: #a5001b;
    }
    .btn-secondary {
      border-radius: 8px;
    }

    /* 🔹 Toast */
    .toast-success {
      background-color: #28a745 !important;
    }
    .toast-danger {
      background-color: #dc3545 !important;
    }

    /* 🔹 Links */
    a {
      color: #D10024;
      text-decoration: none;
    }
    a:hover {
      color: #a5001b;
      text-decoration: underline;
    }

    footer {
      background-color: #D10024;
      color: #fff;
      margin-top: 60px;
      padding: 15px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<!-- 🔔 Toast แจ้งเตือน -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:3000;">
  <?php if (isset($_SESSION['toast_success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><?= $_SESSION['toast_success'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['toast_error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><?= $_SESSION['toast_error'] ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <?php unset($_SESSION['toast_error']); ?>
  <?php endif; ?>
</div>

<!-- 🔹 Register Form -->
<div class="container mt-5" style="max-width:600px;">
  <div class="card shadow">
    <div class="card-header text-center">สมัครสมาชิก</div>
    <div class="card-body p-4">
      <form method="post">
        <div class="mb-3">
          <label class="form-label fw-semibold">ชื่อ-นามสกุล</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">อีเมล</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirm" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">เบอร์โทรศัพท์</label>
          <input type="text" name="phone" class="form-control" maxlength="10"
                 pattern="^[0-9]{10}$" title="กรุณากรอกเฉพาะตัวเลข 10 หลัก" required
                 oninput="this.value=this.value.replace(/[^0-9]/g,'');">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">ที่อยู่</label>
          <textarea name="address" class="form-control" rows="3"></textarea>
        </div>

        <!-- ✅ ช่องติกรับข่าวสาร -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="subscribe" id="subscribe" value="1">
          <label class="form-check-label" for="subscribe">
            ต้องการรับข่าวสารและโปรโมชั่นจากร้านผ่านทางอีเมล
          </label>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">✅ สมัครสมาชิก</button>
        </div>
      </form>
    </div>
  </div>

  <div class="text-center mt-3">
    <a href="login.php">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a><br>
    <a href="index.php" class="btn btn-secondary btn-sm mt-2">⬅️ กลับหน้าหลัก</a>
  </div>
</div>

<!-- 🔹 Footer -->
<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | สมัครสมาชิก
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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