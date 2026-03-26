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

  // ✅ ตรวจสอบความยาวรหัสผ่าน (ต้อง 6 ตัวอักษรขึ้นไป)
  if (strlen($password) < 6) {
    $_SESSION['toast_error'] = "❌ รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    header("Location: register.php");
    exit;
  }

  // ✅ ตรวจสอบรหัสผ่านตรงกันไหม (ฝั่งเซิร์ฟเวอร์กันเหนียวอีกรอบ)
  if ($password !== $confirm) {
    $_SESSION['toast_error'] = "❌ รหัสผ่านไม่ตรงกัน";
    header("Location: register.php");
    exit;
  }

  // ✅ ตรวจสอบเบอร์โทรศัพท์ (ต้องเป็นตัวเลข 10 หลัก)
  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "⚠️ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (เฉพาะตัวเลข 10 หลัก)";
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
        background-color: #f8f9fa;
        font-family: "Prompt", sans-serif;
        color: #333;
    }

    .register-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .register-container {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .card-register {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 600px;
        background: #fff;
        padding: 40px 30px;
    }

    .brand-logo {
        height: 60px;
        margin-bottom: 15px;
    }

    /* 🔹 Input Field */
    .form-control {
        border-radius: 0 10px 10px 0;
        padding: 10px 15px;
        background-color: #fcfcfc;
        border: 1px solid #e0e0e0;
    }
    
    /* แก้ไขไม่ให้เกิดขอบสีแดง/ฟ้าตอนคลิกช่องรหัสผ่านแล้วปุ่มตาดูแยกส่วน */
    .form-control:focus {
        border-color: #D10024;
        box-shadow: none;
        background-color: #fff;
        z-index: 1;
    }
    .input-group:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15);
        border-radius: 10px;
    }
    .input-group:focus-within .form-control,
    .input-group:focus-within .input-group-text {
        border-color: #D10024;
    }

    .input-group-text {
        border-radius: 10px 0 0 10px;
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-right: none;
    }

    /* สไตล์สำหรับปุ่มโชว์รหัสผ่าน (เพิ่มเข้ามาใหม่) */
    .toggle-password {
        cursor: pointer;
        background-color: #fcfcfc;
        border-radius: 0 10px 10px 0 !important; /* บังคับให้ขอบขวาของตาโค้งมน */
        border-left: none; /* เอาเส้นขอบซ้ายออกจะได้เนียนกับกล่องพิมพ์ */
        border-right: 1px solid #e0e0e0;
    }
    .input-group:focus-within .toggle-password {
        background-color: #fff;
    }
    
    /* เอาขอบขวาของ form-control ออกถ้ารหัสผ่านมีปุ่มตา */
    .password-input {
        border-radius: 0 !important;
        border-right: none !important;
    }

    /* 🔹 ปุ่ม */
    .btn-primary {
        background-color: #D10024;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        padding: 12px;
        transition: 0.3s;
    }
    .btn-primary:hover:not(:disabled) {
        background-color: #a5001b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
    }
    .btn-primary:disabled {
        background-color: #e58a99;
        cursor: not-allowed;
    }

    a { color: #D10024; text-decoration: none; font-weight: 500; transition: 0.2s; }
    a:hover { color: #a5001b; text-decoration: underline; }

    .btn-outline-secondary {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
    }

    .form-check-input:checked {
        background-color: #D10024;
        border-color: #D10024;
    }

    footer {
        background-color: #fff;
        color: #6c757d;
        padding: 20px;
        font-size: 0.9rem;
        border-top: 1px solid #eee;
    }
  </style>
</head>
<body>

<div class="register-wrapper">
  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 3000;">
    <?php if (isset($_SESSION['toast_success'])): ?>
      <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
        <div class="d-flex">
          <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_success'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
      <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>

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

  <div class="register-container">
    <div class="card-register">
      <div class="text-center mb-4">
        <img src="icon_mycommiss.png" alt="MyCommiss Logo" class="brand-logo">
        <h4 class="fw-bold" style="color: #D10024;">สมัครสมาชิก</h4>
        <p class="text-muted small">สร้างบัญชีเพื่อเริ่มต้นช้อปปิ้งกับเรา</p>
      </div>

      <form method="post">
        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">ชื่อ-นามสกุล</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
            <input type="text" name="name" class="form-control" placeholder="ชื่อ นามสกุล" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">อีเมล</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control" placeholder="mycommiss@email.com" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-secondary">รหัสผ่าน</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
              <input type="password" name="password" id="passwordInput" class="form-control password-input" placeholder="อย่างน้อย 6 ตัวอักษร" minlength="6" required>
              <span class="input-group-text toggle-password" id="togglePasswordBtn1">
                <i class="bi bi-eye-slash text-muted" id="eyeIcon1"></i>
              </span>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-secondary">ยืนยันรหัสผ่าน</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-shield-lock text-muted"></i></span>
              <input type="password" name="confirm" id="confirmInput" class="form-control password-input" placeholder="อย่างน้อย 6 ตัวอักษร" minlength="6" required>
              <span class="input-group-text toggle-password" id="togglePasswordBtn2">
                <i class="bi bi-eye-slash text-muted" id="eyeIcon2"></i>
              </span>
            </div>
            <div id="passwordMatchMessage" class="mt-1 small fw-medium"></div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">เบอร์โทรศัพท์</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone text-muted"></i></span>
            <input type="text" name="phone" class="form-control" placeholder="08XXXXXXXX" maxlength="10"
                   pattern="^[0-9]{10}$" title="กรุณากรอกเฉพาะตัวเลข 10 หลัก" required
                   oninput="this.value=this.value.replace(/[^0-9]/g,'');">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold text-secondary">ที่อยู่จัดส่ง</label>
          <div class="input-group">
            <span class="input-group-text align-items-start pt-2"><i class="bi bi-house text-muted"></i></span>
            <textarea name="address" class="form-control" rows="3" placeholder="บ้านเลขที่, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์"></textarea>
          </div>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" name="subscribe" id="subscribe" value="1">
          <label class="form-check-label text-muted small" for="subscribe">
            ต้องการรับข่าวสารและโปรโมชั่นจากร้านผ่านทางอีเมล
          </label>
        </div>

        <div class="d-grid mb-4">
          <button type="submit" id="submitBtn" class="btn btn-primary fs-5">สมัครสมาชิก</button>
        </div>
      </form>

      <div class="text-center">
        <p class="mb-3 text-muted">มีบัญชีผู้ใช้งานอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left"></i> กลับหน้าร้าน
        </a>
      </div>
    </div>
  </div>

  <footer class="text-center">
    © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // ซ่อน Toast อัตโนมัติ
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
    toast.show();
  });

  // ระบบสลับโชว์รหัสผ่าน (ช่องแรก)
  const toggleBtn1 = document.getElementById('togglePasswordBtn1');
  const passwordInput = document.getElementById('passwordInput');
  const eyeIcon1 = document.getElementById('eyeIcon1');

  toggleBtn1.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      eyeIcon1.classList.toggle('bi-eye');
      eyeIcon1.classList.toggle('bi-eye-slash');
  });

  // ระบบสลับโชว์รหัสผ่าน (ช่องยืนยัน)
  const toggleBtn2 = document.getElementById('togglePasswordBtn2');
  const confirmInput = document.getElementById('confirmInput');
  const eyeIcon2 = document.getElementById('eyeIcon2');

  toggleBtn2.addEventListener('click', function () {
      const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmInput.setAttribute('type', type);
      eyeIcon2.classList.toggle('bi-eye');
      eyeIcon2.classList.toggle('bi-eye-slash');
  });

  // ระบบเช็ครหัสผ่านตรงกัน Real-time
  const matchMessage = document.getElementById('passwordMatchMessage');
  const submitBtn = document.getElementById('submitBtn');

  function checkPasswordMatch() {
      const pwd = passwordInput.value;
      const confirm = confirmInput.value;

      // ถ้าช่องยืนยันยังว่างอยู่ ให้ยังไม่แสดงข้อความเตือน
      if (confirm === '') {
          matchMessage.innerHTML = '';
          submitBtn.disabled = false;
          return;
      }

      if (pwd === confirm) {
          matchMessage.innerHTML = '<i class="bi bi-check-circle-fill"></i> รหัสผ่านตรงกัน';
          matchMessage.className = 'mt-1 small fw-medium text-success';
          submitBtn.disabled = false; // รหัสตรงกัน กดปุ่มสมัครได้
      } else {
          matchMessage.innerHTML = '<i class="bi bi-x-circle-fill"></i> รหัสผ่านไม่ตรงกัน';
          matchMessage.className = 'mt-1 small fw-medium text-danger';
          submitBtn.disabled = true; // รหัสไม่ตรงกัน ล็อคปุ่มสมัคร
      }
  }

  // ให้ทำงานทุกครั้งที่มีการพิมพ์ในช่องใดช่องหนึ่ง
  passwordInput.addEventListener('input', checkPasswordMatch);
  confirmInput.addEventListener('input', checkPasswordMatch);
});
</script>

</body>
</html>