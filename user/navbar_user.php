<?php
// ✅ ตรวจสอบ Session (ป้องกันการเรียกซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom sticky-top" style="z-index: 1050;">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="icon_mycommiss.png" alt="MyCommiss Logo" height="40" class="me-2 logo-img">
            <span class="brand-text">MyCommiss</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center py-2 py-lg-0">
                <li class="nav-item w-100 w-lg-auto text-center">
                    <a href="index.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        🏠 หน้าร้าน
                    </a>
                </li>

                <li class="nav-item w-100 w-lg-auto text-center">
                    <a href="cart.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">
                        🛒 ตะกร้า
                    </a>
                </li>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item w-100 w-lg-auto text-center">
                        <a href="orders.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                            📦 คำสั่งซื้อ
                        </a>
                    </li>

                    <li class="nav-item w-100 w-lg-auto text-center">
                        <a href="profile.php" class="nav-link user-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                            👤 <?= htmlspecialchars($_SESSION['customer_name']) ?>
                        </a>
                    </li>

                    <li class="nav-item w-100 w-lg-auto text-center ms-lg-2">
                        <a href="#" class="nav-link btn-logout text-danger fw-bold px-3" onclick="confirmLogout(event)">
                            🚪 ออกจากระบบ
                        </a>
                    </li>

                <?php else: ?>
                    <li class="nav-item w-100 w-lg-auto text-center">
                        <a href="login.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">
                            🔑 เข้าสู่ระบบ
                        </a>
                    </li>
                    <li class="nav-item w-100 w-lg-auto text-center ms-lg-2">
                        <a href="register.php" class="nav-link btn-register px-4 py-1 py-lg-2 mt-2 mt-lg-0 text-white rounded-pill shadow-sm">
                            สมัครสมาชิก
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
function confirmLogout(e) {
    e.preventDefault();
    if (confirm("คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?")) {
        window.location = "logout.php";
    }
}
</script>

<style>
    /* 💎 Brand Style */
    .brand-text {
        color: #D10024;
        font-size: 1.4rem;
        letter-spacing: -0.5px;
    }

    /* 💎 Link Style */
    .nav-link {
        color: #444 !important;
        font-size: 0.95rem;
        font-weight: 500;
        position: relative;
        padding: 10px 15px;
    }
    
    .nav-link:hover { color: #D10024 !important; }

    /* เส้นใต้ Active (เฉพาะบนจอใหญ่) */
    @media (min-width: 992px) {
        .nav-link::after {
            content: "";
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 0%;
            height: 2px;
            background-color: #D10024;
            transition: 0.3s;
            transform: translateX(-50%);
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 70%;
        }
    }

    /* 💎 ปุ่มสมัครสมาชิก */
    .btn-register {
        background-color: #D10024 !important;
        color: #fff !important;
        transition: 0.3s;
        font-weight: 600;
        display: inline-block;
    }
    .btn-register:hover {
        background-color: #a5001b !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(209, 0, 36, 0.2) !important;
    }

    /* 📱 Mobile UI ปรับปรุงให้เมนูไม่ติดกันเกินไป */
    @media (max-width: 991px) {
        .navbar-nav {
            margin-top: 15px;
            border-top: 1px solid #eee;
        }
        .nav-item {
            padding: 5px 0;
        }
        .nav-link.active {
            background-color: #fcfcfc;
            color: #D10024 !important;
            border-radius: 8px;
        }
        .logo-img { height: 32px; }
        .brand-text { font-size: 1.2rem; }
    }
</style>