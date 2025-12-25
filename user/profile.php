<?php
session_start();
include("connectdb.php");

// 🔒 ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("<p class='text-center text-danger mt-5'>❌ ไม่พบข้อมูลผู้ใช้</p>");
}

// ✅ เมื่อกดบันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  $subscribe = isset($_POST['subscribe']) ? 1 : 0; // ✅ subscribe toggle

  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['toast_error'] = "❌ กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (10 หลัก)";
    header("Location: profile.php");
    exit;
  } else {
    $stmt = $conn->prepare("UPDATE customers 
                            SET name = ?, email = ?, phone = ?, address = ?, subscribe = ? 
                            WHERE customer_id = ?");
    $stmt->execute([$name, $email, $phone, $address, $subscribe, $customer_id]);

    $_SESSION['customer_name'] = $name;
    $_SESSION['toast_success'] = "✅ บันทึกข้อมูลเรียบร้อยแล้ว";
    header("Location: index.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>โปรไฟล์ของฉัน | MyCommiss</title>
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

    .profile-card {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .card-header {
      background: var(--red);
      color: #fff;
      font-weight: 600;
      border-radius: 15px 15px 0 0;
      letter-spacing: 0.5px;
    }

    .form-label { font-weight: 500; color: #333; }

    .btn { border-radius: 10px; transition: all 0.2s ease-in-out; font-weight: 500; }
    .btn:hover { transform: scale(1.05); }

    .btn-primary, .btn-outline-primary:hover {
      background-color: var(--red);
      border-color: var(--red);
      color: #fff;
    }

    .btn-outline-primary {
      border-color: var(--red);
      color: var(--red);
    }

    .btn-success { background-color: #28a745; border: none; }

    footer {
      background-color: var(--red);
      color: white;
      margin-top: 50px;
      padding: 15px;
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

<div class="container">
  <div class="profile-card">
    <div class="card-header text-center py-3">👤 โปรไฟล์ของฉัน</div>
    <div class="card-body p-4">

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">ชื่อ - นามสกุล</label>
          <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">อีเมล</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">เบอร์โทรศัพท์</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                 class="form-control" maxlength="10" pattern="[0-9]{10}"
                 oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required>
        </div>

        <div class="mb-3">
          <label class="form-label">ที่อยู่จัดส่ง</label>
          <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <!-- ✅ สมัครข่าวสาร -->
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="subscribe" name="subscribe" 
                 value="1" <?= $user['subscribe'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="subscribe">
            สมัครรับข่าวสารและโปรโมชั่นจาก MyCommiss
          </label>
        </div>

        <div class="d-flex justify-content-center align-items-center gap-3 mt-4 flex-wrap">
          <a href="index.php" class="btn btn-outline-primary">⬅️ กลับหน้าหลัก</a>
          <a href="change_password.php" class="btn btn-success">🔑 เปลี่ยนรหัสผ่าน</a>
          <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูล</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | โปรไฟล์ผู้ใช้
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
