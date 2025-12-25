<?php
session_start();
include __DIR__ . "/partials/connectdb.php";

// ถ้ามีการล็อกอินอยู่แล้ว → กลับไปหน้า Dashboard
if (isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  // 🌟 แบบทดสอบ (ในอนาคตเชื่อมฐานข้อมูลได้)
  if ($username === "admin" && $password === "1234") {
    $_SESSION['admin'] = $username;
    header("Location: index.php");
    exit;
  } else {
    $error = "❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
  }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เข้าสู่ระบบ - MyCommiss Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Prompt', sans-serif;
      background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    /* 💫 เอฟเฟกต์ดาวเคลื่อนไหวพื้นหลัง */
    .stars {
      position: absolute;
      width: 200%;
      height: 200%;
      background: radial-gradient(white, rgba(255,255,255,0) 70%) 0 0 / 3px 3px,
                  radial-gradient(white, rgba(255,255,255,0) 70%) 50px 50px / 3px 3px;
      background-repeat: repeat;
      animation: moveStars 100s linear infinite;
      opacity: 0.25;
    }
    @keyframes moveStars {
      from { transform: translateY(0); }
      to { transform: translateY(-1000px); }
    }

    /* กล่อง Login */
    .login-card {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 420px;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 20px;
      padding: 2.5rem;
      color: #fff;
      box-shadow: 0 0 30px rgba(0,0,0,0.4);
      animation: fadeIn 1.2s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-title {
      font-weight: 700;
      font-size: 1.8rem;
      background: linear-gradient(90deg, #00d4ff, #22c55e);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .form-control {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      color: #fff;
      border-radius: 10px;
      padding: 0.8rem;
      text-align: center;
      font-size: 1rem;
      transition: 0.3s;
    }
    .form-control:focus {
      background: rgba(255,255,255,0.15);
      border-color: #22c55e;
      box-shadow: 0 0 10px rgba(34,197,94,0.4);
    }

    .btn-login {
      background: linear-gradient(90deg, #22c55e, #16a34a);
      border: none;
      border-radius: 10px;
      padding: 0.75rem;
      color: #fff;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-login:hover {
      background: linear-gradient(90deg, #16a34a, #15803d);
      box-shadow: 0 0 20px rgba(22,165,74,0.5);
      transform: scale(1.02);
    }

    .alert {
      background: rgba(255,0,0,0.1);
      color: #ff7676;
      border: 1px solid rgba(255,0,0,0.3);
      border-radius: 10px;
    }

    /* โลโก้ไอคอน */
    .login-icon {
      font-size: 3rem;
      color: #22c55e;
      margin-bottom: 0.5rem;
    }
  </style>
</head>

<body>
  <div class="stars"></div>

  <div class="login-card text-center">
    <div class="login-icon">
      <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="login-title mb-4">เข้าสู่ระบบแอดมิน</h3>

    <form method="post">
      <div class="form-group mb-3">
        <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
      </div>
      <div class="form-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
      </div>

      <?php if ($error): ?>
        <div class="alert py-2 mb-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <button type="submit" class="btn btn-login w-100 fw-bold">
        <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
      </button>
    </form>

    <p class="mt-4 text-secondary small">
      © <?= date('Y') ?> MyCommiss Admin. All rights reserved.
    </p>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
