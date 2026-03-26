<?php
session_start();
include("connectdb.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // รับค่าจากฟอร์ม (อาจจะเป็น username ของแอดมิน หรือ email ของลูกค้า)
    $login_input = trim($_POST['email']); 
    $password = trim($_POST['password']);

    // =======================================
    // 1. ตรวจสอบว่าเป็น Admin หรือไม่ (เช็คจากตาราง admins)
    // =======================================
    $stmt_admin = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt_admin->execute([$login_input]);
    $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

    // เนื่องจากรหัสผ่านแอดมินในฐานข้อมูลไม่ได้เข้ารหัส จึงใช้ === เทียบได้เลย
    if ($admin && $password === $admin['password']) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['name'];
        
        // เด้งไปหน้า Dashboard ของ Admin
        header("Location: http://103.40.119.91/Project/admin/index.php");
        exit;
    }

    // =======================================
    // 2. ถ้าไม่ใช่ Admin ให้ตรวจสอบว่าเป็นลูกค้าหรือไม่ (เช็คจากตาราง customers)
    // =======================================
    $stmt_customer = $conn->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt_customer->execute([$login_input]);
    $user = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    // รหัสผ่านลูกค้ามีการเข้ารหัส ต้องใช้ password_verify
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['customer_name'] = $user['name'];

        $_SESSION['toast_success'] = "✅ เข้าสู่ระบบสำเร็จ ยินดีต้อนรับคุณ " . htmlspecialchars($user['name']);
        
        // เด้งไปหน้าร้านค้าปกติ
        header("Location: index.php");
        exit;
    } 

    // =======================================
    // 3. กรณีไม่พบทั้ง Admin และ ลูกค้า หรือ รหัสผ่านผิด
    // =======================================
    $_SESSION['toast_error'] = "❌ อีเมล/ชื่อผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง";
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | MyCommiss</title>
    <link rel="icon" type="image/png" href="icon_mycommiss.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa; /* เปลี่ยนพื้นหลังเป็นสีเทาอ่อนให้กล่องล็อกอินเด่นขึ้น */
            font-family: "Prompt", sans-serif;
            color: #333;
        }

        /* 🔹 การจัดวางให้กล่องอยู่กึ่งกลางหน้าจอเสมอ */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-container {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* 🔹 การ์ดล็อกอิน */
        .card-login {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
            background: #fff;
            padding: 40px 30px;
        }

        .brand-logo {
            height: 60px;
            margin-bottom: 15px;
        }

        /* 🔹 Input Field */
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            background-color: #fcfcfc;
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #D10024;
            box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15);
            background-color: #fff;
        }

        /* 🔹 ปุ่มเข้าสู่ระบบ */
        .btn-primary {
            background-color: #D10024;
            border: none;
            border-radius: 50px; /* ทำให้ปุ่มมนๆ เข้ากับหน้าเว็บ */
            font-weight: 600;
            padding: 12px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #a5001b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
        }

        /* 🔹 ลิงก์ */
        a { color: #D10024; text-decoration: none; font-weight: 500; transition: 0.2s; }
        a:hover { color: #a5001b; text-decoration: underline; }

        .btn-outline-secondary {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
        }

        /* 🔹 Footer */
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

<div class="login-wrapper">
    <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 3000;">
        <?php if (isset($_SESSION['toast_success'])): ?>
            <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body fs-6 fw-medium px-3 py-2">
                        <?= $_SESSION['toast_success'] ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            <?php unset($_SESSION['toast_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['toast_error'])): ?>
            <div class="toast align-items-center text-bg-danger border-0 show shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body fs-6 fw-medium px-3 py-2">
                        <?= $_SESSION['toast_error'] ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            <?php unset($_SESSION['toast_error']); ?>
        <?php endif; ?>
    </div>

    <div class="login-container">
        <div class="card-login">
            <div class="text-center mb-4">
                <img src="icon_mycommiss.png" alt="MyCommiss Logo" class="brand-logo">
                <h4 class="fw-bold" style="color: #D10024;">เข้าสู่ระบบ</h4>
                <p class="text-muted small">ยินดีต้อนรับกลับสู่ MyCommiss</p>
            </div>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">อีเมล</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="email" class="form-control border-start-0 ps-0" placeholder="mycommiss@email.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">รหัสผ่าน</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" minlength="4" required>
                    </div>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary fs-5">เข้าสู่ระบบ</button>
                </div>
            </form>

            <div class="text-center">
                <p class="mb-3 text-muted">ยังไม่มีบัญชีใช่หรือไม่? <a href="register.php">สมัครสมาชิกเลย</a></p>
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
    // ซ่อน Toast อัตโนมัติหลังจาก 4 วินาที
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(toastEl => {
        const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
        toast.show();
    });
});
</script>

</body>
</html>