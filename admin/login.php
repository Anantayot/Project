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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');
    
    body { 
      font-family: 'Prompt', sans-serif; 
      background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a); 
      min-height: 100vh; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      overflow: hidden; 
      margin: 0; 
    }
    
    /* Background Animation */
    .stars { 
      position: absolute; 
      width: 200%; 
      height: 200%; 
      background: radial-gradient(white, rgba(255,255,255,0) 70%) 0 0 / 3px 3px, 
                  radial-gradient(white, rgba(255,255,255,0) 70%) 50px 50px / 3px 3px; 
      background-repeat: repeat; 
      animation: moveStars 100s linear infinite; 
      opacity: 0.15; 
    }
    @keyframes moveStars { from { transform: translateY(0); } to { transform: translateY(-1000px); } }
    
    /* Login Card Glassmorphism */
    .login-card { 
      position: relative; 
      z-index: 2; 
      width: 100%; 
      max-width: 420px; 
      background: rgba(30, 41, 59, 0.7); 
      backdrop-filter: blur(15px); 
      border: 1px solid rgba(255, 255, 255, 0.1); 
      border-radius: 24px; 
      padding: 3rem 2.5rem; 
      color: #fff; 
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
      animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1); 
    }
    @keyframes fadeInUp { 
      from { opacity: 0; transform: translateY(40px); } 
      to { opacity: 1; transform: translateY(0); } 
    }
    
    /* Header */
    .login-icon { 
      font-size: 3.5rem; 
      color: #22c55e; 
      margin-bottom: 0.5rem; 
      filter: drop-shadow(0 0 15px rgba(34, 197, 94, 0.4));
    }
    .login-title { 
      font-weight: 700; 
      font-size: 1.8rem; 
      letter-spacing: 0.5px;
      margin-bottom: 2rem;
    }
    .login-title span {
      color: #22c55e;
    }
    
    /* Custom Inputs */
    .custom-input-group {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      transition: all 0.3s ease;
      overflow: hidden;
    }
    .custom-input-group:focus-within {
      background: rgba(255, 255, 255, 0.08);
      border-color: #22c55e;
      box-shadow: 0 0 15px rgba(34, 197, 94, 0.2);
    }
    .custom-input-group .input-group-text {
      background: transparent;
      border: none;
      color: #94a3b8;
      padding-left: 1.2rem;
    }
    .custom-input-group:focus-within .input-group-text {
      color: #22c55e;
    }
    .form-control { 
      background: transparent; 
      border: none; 
      color: #f8fafc; 
      padding: 1rem 1rem 1rem 0.5rem; 
      font-size: 1rem; 
      box-shadow: none !important;
    }
    .form-control::placeholder { 
      color: #64748b; 
    }
    
    /* Toggle Password Button */
    .btn-toggle-password {
      background: transparent;
      border: none;
      color: #94a3b8;
      padding-right: 1.2rem;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn-toggle-password:hover { color: #f8fafc; }
    
    /* Login Button */
    .btn-login { 
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); 
      border: none; 
      border-radius: 12px; 
      padding: 1rem; 
      color: #fff; 
      font-weight: 600; 
      font-size: 1.1rem;
      letter-spacing: 0.5px;
      transition: all 0.3s ease; 
      margin-top: 1rem;
    }
    .btn-login:hover { 
      transform: translateY(-2px); 
      color: #fff; 
      box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.5); 
    }
    .btn-login:active {
      transform: translateY(0);
    }
    
    /* Alert */
    .alert-custom { 
      background: rgba(239, 68, 68, 0.1); 
      color: #fca5a5; 
      border: 1px solid rgba(239, 68, 68, 0.2); 
      border-radius: 12px; 
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
  </style>
</head>
<body>
  <div class="stars"></div>
  
  <div class="login-card text-center mx-3 mx-md-0">
    <div class="login-icon">
      <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="login-title">เข้าสู่ระบบ<span>แอดมิน</span></h3>
    
    <?php if ($error): ?>
      <div class="alert alert-custom py-2 mb-4 text-start">
        <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="input-group mb-4 custom-input-group text-start">
        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
        <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required autocomplete="off">
      </div>
      
      <div class="input-group mb-4 custom-input-group text-start">
        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="รหัสผ่าน" required>
        <button type="button" class="btn-toggle-password" id="togglePassword">
          <i class="bi bi-eye-slash-fill"></i>
        </button>
      </div>
      
      <button type="submit" class="btn btn-login w-100 shadow-sm">
        เข้าสู่ระบบ <i class="bi bi-arrow-right ms-1"></i>
      </button>
    </form>
  </div>

  <script>
    // สคริปต์สำหรับกดปุ่มรูปตาเพื่อดูรหัสผ่าน
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#passwordInput');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function (e) {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      if(type === 'text') {
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
      } else {
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
      }
    });
  </script>
</body>
</html>