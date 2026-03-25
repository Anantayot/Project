<?php
// ✅ ตรวจสอบ Session (ป้องกันการเรียกซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ นับจำนวนสินค้าในตะกร้า (แยกตามรายการสินค้า)
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top main-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="icon_mycommiss.png" alt="Logo" class="logo-img me-2">
            <span class="brand-text fw-bold">MyCommiss</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center py-3 py-lg-0">
                
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i class="bi bi-house-door me-1"></i> หน้าร้าน
                    </a>
                </li>

                <li class="nav-item">
                    <a href="cart.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">
                        <i class="bi bi-cart3 me-1"></i> ตะกร้า
                        <?php if ($cart_count > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-1 animate-pop"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                            <i class="bi bi-box-seam me-1"></i> คำสั่งซื้อ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="profile.php" class="nav-link user-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['customer_name']) ?>
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a href="#" class="nav-link text-danger fw-bold btn-logout-hover" onclick="confirmLogout(event)">
                            <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                        </a>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">
                            เข้าสู่ระบบ
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a href="register.php" class="nav-link btn-register px-4 py-2 text-white rounded-pill shadow-sm">
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
    /* 💎 Navbar Base */
    .main-navbar {
        border-bottom: 2px solid #f8f9fa;
        z-index: 1050;
    }
    
    .logo-img { height: 42px; width: auto; transition: 0.3s; }
    .brand-text { color: #D10024; font-size: 1.5rem; letter-spacing: -0.5px; }

    /* 💎 Nav Links */
    .nav-link {
        color: #444 !important;
        font-size: 0.95rem;
        font-weight: 500;
        padding: 0.8rem 1.2rem !important;
        transition: 0.25s;
        border-radius: 8px;
    }
    
    .nav-link:hover { color: #D10024 !important; }

    /* 💎 Active State Indicator (Desktop) */
    @media (min-width: 992px) {
        .nav-link::after {
            content: "";
            display: block;
            width: 0;
            height: 2px;
            background: #D10024;
            transition: width 0.3s ease-in-out;
            margin: 2px auto 0;
            border-radius: 2px;
        }
        .nav-link.active::after { width: 80%; }
        .nav-link.active { color: #D10024 !important; }
    }

    /* 💎 User Link Custom Style */
    .user-link { color: #D10024 !important; font-weight: 600; }

    /* 💎 Logout Hover Effect */
    .btn-logout-hover:hover {
        background-color: rgba(209, 0, 36, 0.08);
    }

    /* 💎 Register Button */
    .btn-register {
        background-color: #D10024 !important;
        color: #fff !important;
        font-weight: 600;
        border: 2px solid #D10024;
        transition: 0.3s ease;
        text-align: center;
    }
    .btn-register:hover {
        background-color: #a5001b !important;
        border-color: #a5001b !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(209, 0, 36, 0.25) !important;
    }

    /* 💎 Animation สำหรับ Badge ตะกร้า */
    .animate-pop {
        animation: pop 0.3s ease-out forwards;
    }
    @keyframes pop {
        0% { transform: scale(0.5); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* 📱 Mobile Adjustments */
    @media (max-width: 991px) {
        .navbar-nav {
            background-color: #fff;
            padding: 1rem;
            border-top: 1px solid #f1f1f1;
            margin-top: 10px;
        }
        .nav-item { width: 100%; text-align: left !important; }
        .nav-link { padding: 12px 20px !important; }
        .nav-link.active { background-color: #fff5f6; color: #D10024 !important; }
        .logo-img { height: 35px; }
        .brand-text { font-size: 1.3rem; }
        .btn-register { width: 100%; margin-top: 10px; }
    }
</style>