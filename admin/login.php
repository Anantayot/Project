<?php
session_start();
// เปิดโหมดโชว์ Error ไว้ชั่วคราว
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจากโฟลเดอร์ partials ที่อยู่ระดับเดียวกัน
include __DIR__ . "/partials/connectdb.php";

// ถ้าล็อกอินแล้วให้เด้งไปหน้า Dashboard
if (isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

$error = '';

// ✅ เพิ่มการเช็คว่าโดนเด้งออกมาเพราะหมดเวลาหรือไม่
if (isset($_GET['timeout'])) {
  $error = "กรุณาเข้าสู่ระบบใหม่";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  // ตรวจสอบรหัสผ่าน
  if ($admin && $password === $admin['password']) {
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['name']; 
    
    // ✅ เพิ่มบรรทัดนี้: บันทึกเวลาล่าสุดที่ล็อกอินสำเร็จ
    $_SESSION['last_activity'] = time(); 
    
    header("Location: index.php");
    exit;
  } else {
    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
  }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เข้าสู่ระบบ - MyCommiss Admin</title>
  <link rel="icon" type="image/png" href="partials/icon_mycommiss.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');
    
    body { 
      font-family: 'Prompt', sans-serif; 
      /* เปลี่ยนพื้นหลังเป็นสีเทาอ่อนๆ สะอาดตา */
      background-color: #f1f5f9; 
      background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
      background-size: 24px 24px;
      min-height: 100vh; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      margin: 0; 
      -webkit-font-smoothing: antialiased;
    }
    
    /* Login Card - สไตล์ Minimal สีขาว */
    .login-wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
      padding: 15px;
    }

    .login-card { 
      width: 100%; 
      max-width: 400px; 
      background: #ffffff; 
      border-radius: 24px; 
      padding: 3rem 2rem; 
      color: #334155; 
      /* ใส่เงาให้นูนขึ้นมาดูมีมิติ */
      box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1); 
      border: 1px solid #f1f5f9;
    }
    
    /* Header */
    .login-icon { 
      font-size: 3.5rem; 
      color: #10b981; 
      margin-bottom: 0.5rem; 
    }
    .login-title { 
      font-weight: 700; 
      font-size: 1.75rem; 
      letter-spacing: 0.5px;
      margin-bottom: 2rem;
      color: #0f172a;
    }
    .login-title span {
      color: #10b981;
    }
    
    /* Custom Inputs (แคปซูล) */
    .custom-input-group {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 50px; 
      transition: all 0.3s ease;
      overflow: hidden;
      margin-bottom: 1.2rem !important;
    }
    .custom-input-group:focus-within {
      background: #ffffff;
      border-color: #10b981;
      box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }
    .custom-input-group .input-group-text {
      background: transparent;
      border: none;
      color: #94a3b8;
      padding-left: 1.5rem;
      font-size: 1.1rem;
    }
    .custom-input-group:focus-within .input-group-text {
      color: #10b981;
    }
    .form-control { 
      background: transparent; 
      border: none; 
      color: #1e293b; 
      padding: 1rem 1rem 1rem 0.5rem; 
      font-size: 16px; /* บังคับ 16px เพื่อไม่ให้ iOS ซูม */
      box-shadow: none !important;
    }
    .form-control::placeholder { 
      color: #94a3b8; 
    }
    
    /* Toggle Password Button */
    .btn-toggle-password {
      background: transparent;
      border: none;
      color: #94a3b8;
      padding-right: 1.5rem;
      padding-left: 1rem;
      cursor: pointer;
      transition: 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn-toggle-password:hover { color: #475569; }
    
    /* Login Button */
    .btn-login { 
      background: #10b981; 
      border: none; 
      border-radius: 50px; 
      padding: 0.9rem; 
      color: #fff; 
      font-weight: 600; 
      font-size: 1.1rem;
      letter-spacing: 0.5px;
      transition: all 0.3s ease; 
      margin-top: 1.5rem;
    }
    .btn-login:hover { 
      transform: translateY(-3px); 
      background: #059669;
      color: #fff; 
      box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4); 
    }
    .btn-login:active {
      transform: translateY(0);
      box-shadow: none;
    }
    
    /* Alert */
    .alert-custom { 
      background: #fef2f2; 
      color: #ef4444; 
      border: 1px solid #fee2e2; 
      border-radius: 16px; 
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
    }

    /* มือถือเล็กๆ */
    @media (max-width: 400px) {
      .login-card {
        padding: 2.5rem 1.5rem;
      }
      .login-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  
  <div class="login-wrapper">
    <div class="login-card text-center">
      <div class="login-icon">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <h3 class="login-title">เข้าสู่ระบบ<span>แอดมิน</span></h3>
      
      <?php if ($error): ?>
        <div class="alert alert-custom mb-4 text-start">
          <i class="bi bi-exclamation-circle-fill fs-5"></i> 
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="input-group custom-input-group text-start">
          <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
          <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required autocomplete="off">
        </div>
        
        <div class="input-group custom-input-group text-start">
          <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="รหัสผ่าน" required>
          <button type="button" class="btn-toggle-password" id="togglePassword">
            <i class="bi bi-eye-slash-fill fs-5"></i>
          </button>
        </div>
        
        <button type="submit" class="btn btn-login w-100">
          เข้าสู่ระบบ <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </form>
    </div>
  </div>

  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#passwordInput');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function (e) {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      if(type === 'text') {
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
        icon.style.color = '#10b981'; // สีเขียว
      } else {
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
        icon.style.color = '#94a3b8'; // สีเทา
      }
    });
  </script>
</body>
</html>