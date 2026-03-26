<?php
// ✅ ตรวจสอบ Session (ป้องกันการเรียกซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ นับจำนวนสินค้าในตะกร้า (แยกตามรายการสินค้า)
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top main-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center transition-link" href="../index.php">
            <div class="logo-wrapper">
                <img src="icon_mycommiss.png" alt="Logo" class="logo-img">
            </div>
            <span class="brand-text fw-bold ms-2">MyCommiss</span>
        </a>

        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center py-3 py-lg-0 gap-lg-2">
                
                <li class="nav-item">
                    <a href="../index.php" class="nav-link transition-link <?= basename($_SERVER['PHP_SELF']) == '../index.php' ? 'active' : '' ?>">
                        <i class="bi bi-house-door me-1 nav-icon"></i> หน้าร้าน
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../cart/cart.php" class="nav-link cart-link transition-link <?= basename($_SERVER['PHP_SELF']) == '../cart/cart.php' ? 'active' : '' ?>">
                        <div class="cart-icon-wrapper">
                            <i class="bi bi-cart3 nav-icon"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge animate-pop"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="d-lg-none ms-2">ตะกร้าสินค้า</span>
                    </a>
                </li>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item">
                        <a href="../order/orders.php" class="nav-link transition-link <?= basename($_SERVER['PHP_SELF']) == '../order/orders.php' ? 'active' : '' ?>">
                            <i class="bi bi-box-seam me-1 nav-icon"></i> คำสั่งซื้อ
                        </a>
                    </li>

                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link user-profile-btn dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-1"></i> <?= htmlspecialchars($_SESSION['customer_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 custom-dropdown" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item transition-link" href="../profile/profile.php"><i class="bi bi-person-gear me-2"></i> โปรไฟล์ส่วนตัว</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger fw-bold btn-logout" href="#" onclick="confirmLogout(event)">
                                    <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </li>

                    <div class="d-lg-none mobile-user-menu mt-3 pt-3 border-top">
                        <li class="nav-item">
                            <a href="../profile/profile.php" class="nav-link user-link transition-link <?= basename($_SERVER['PHP_SELF']) == '../profile/profile.php' ? 'active' : '' ?>">
                                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['customer_name']) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-danger fw-bold" onclick="confirmLogout(event)">
                                <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                            </a>
                        </li>
                    </div>

                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a href="../login.php" class="nav-link transition-link <?= basename($_SERVER['PHP_SELF']) == '../login.php' ? 'active' : '' ?>">
                            เข้าสู่ระบบ
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="../register.php" class="btn btn-register transition-link w-100">
                            สมัครสมาชิก
                        </a>
                    </li>
                <?php endif; ?>
                
            </ul>
        </div>
    </div>
</nav>

<style>
    /* 🌟 Root Variables for Themeing */
    :root {
        --primary-color: #D10024;
        --primary-hover: #a5001b;
        --text-dark: #2b2b2b;
        --text-gray: #6b6b6b;
        --bg-light: #f8f9fa;
        --transition-speed: 0.3s;
    }

    /* 🌟 Page Transition Animations */
    body {
        opacity: 0;
        animation: fadePageIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    body.fade-out {
        animation: fadePageOut 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes fadePageIn {
        0% { opacity: 0; transform: translateY(15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadePageOut {
        0% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-10px); }
    }

    /* 💎 Navbar Base */
    .main-navbar {
        box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
        transition: all var(--transition-speed) ease;
        padding: 0.5rem 0;
    }
    
    /* Glassmorphism on scroll (Requires JS to add 'scrolled' class, optional) */
    .main-navbar.scrolled {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    /* 💎 Brand & Logo */
    .logo-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        /* เอาพื้นหลัง (background), ขอบ (border-radius), และการขยับ (transition) ออก */
    }
    .logo-img { height: 100%; width: auto; object-fit: contain; }
    
    .brand-text { 
        color: var(--primary-color); 
        font-size: 1.4rem; 
        letter-spacing: -0.5px; 
    }

    /* 💎 Nav Links */
    .nav-link {
        color: var(--text-gray) !important;
        font-size: 1rem;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        transition: all var(--transition-speed);
        display: flex;
        align-items: center;
    }
    
    .nav-icon {
        transition: transform var(--transition-speed);
    }

    .nav-link:hover { 
        color: var(--primary-color) !important; 
        background-color: rgba(209, 0, 36, 0.03);
    }
    
    .nav-link:hover .nav-icon {
        transform: translateY(-2px);
    }

    .nav-link.active { 
        color: var(--primary-color) !important; 
        font-weight: 600;
    }

    /* 💎 Active Line Indicator (Desktop) */
    @media (min-width: 992px) {
        .nav-item { position: relative; }
        
        /* ใส่ :not() เพื่อยกเว้นไม่ให้เส้นแดงไปแสดงที่ปุ่ม Profile และ Cart */
        .nav-link:not(.user-profile-btn):not(.cart-link)::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: var(--primary-color);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 3px 3px 0 0;
        }
        .nav-link.active:not(.user-profile-btn):not(.cart-link)::after { width: 70%; }
        
        /* User Profile Button Customization */
        .user-profile-btn {
            background-color: var(--bg-light);
            border: 1px solid #eaeaea;
            border-radius: 50px;
            padding: 0.4rem 1.2rem !important;
            display: flex !important;
            align-items: center;
            gap: 5px; /* เพิ่มระยะห่างระหว่างไอคอนกับชื่อนิดหน่อย */
        }
        
        .user-profile-btn:hover { 
            background-color: #fff; 
            border-color: var(--primary-color); 
        }

        /* ปิดการแสดงผลลูกศร Dropdown และเศษเส้นแดงที่รบกวน */
        .user-profile-btn::after {
            display: none !important;
        }
    }

    /* 💎 Cart Icon & Badge */
    .cart-icon-wrapper { position: relative; display: inline-block; }
    .cart-link .nav-icon { font-size: 1.2rem; }
    
    .cart-badge {
        position: absolute;
        top: -8px;
        right: -10px;
        background-color: var(--primary-color);
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        padding: 0.2em 0.5em;
        border-radius: 50rem;
        border: 2px solid white;
        line-height: 1;
    }

    .animate-pop { animation: pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    @keyframes pop {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* 💎 Dropdown Menu Customization */
    .custom-dropdown {
        border-radius: 12px;
        padding: 0.5rem 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        animation: dropIn 0.2s ease-out;
    }
    @keyframes dropIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .dropdown-item {
        padding: 0.6rem 1.2rem;
        font-weight: 500;
        color: var(--text-dark);
        transition: 0.2s;
    }
    .dropdown-item:hover { background-color: rgba(209, 0, 36, 0.05); color: var(--primary-color); }
    .btn-logout:hover { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }

    /* 💎 Register Button */
    .btn-register {
        background-color: var(--primary-color);
        color: #fff !important;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        transition: all var(--transition-speed);
        border: 2px solid var(--primary-color);
    }
    .btn-register:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(209, 0, 36, 0.3);
    }

    /* 📱 Mobile Toggle & View Adjustments */
    .custom-toggler {
        border: none;
        font-size: 1.8rem;
        color: var(--text-dark);
        padding: 0;
    }
    .custom-toggler:focus { box-shadow: none; outline: none; }

    @media (max-width: 991px) {
        .navbar-collapse {
            background-color: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: absolute;
            top: 100%;
            left: 10px;
            right: 10px;
            z-index: 1000;
        }
        .nav-item { width: 100%; margin-bottom: 5px; }
        .nav-link { 
            padding: 0.8rem 1rem !important; 
            border-radius: 8px;
            background-color: var(--bg-light);
        }
        .nav-link.active { background-color: rgba(209, 0, 36, 0.08); }
        .btn-register { margin-top: 10px; }
    }
</style>

<script>
// 🌟 SweetAlert/Native Confirm for Logout
function confirmLogout(e) {
    e.preventDefault();
    if (confirm("คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?")) {
        document.body.classList.add('fade-out');
        setTimeout(() => {
            window.location = "../logout.php";
        }, 200); 
    }
}

// 🌟 Page Transition Logic
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll('a.transition-link:not([target="_blank"]):not([href^="#"]):not([onclick])');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (e.ctrlKey || e.metaKey) return;
            
            const targetUrl = this.href;
            const currentUrl = window.location.href;

            if (targetUrl !== currentUrl) {
                e.preventDefault();
                document.body.classList.add('fade-out'); 
                
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 200);
            }
        });
    });

    // Optional: Add shadow to navbar on scroll
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.main-navbar');
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
});

window.addEventListener('pageshow', function (event) {
    if (event.persisted || document.body.classList.contains('fade-out')) {
        document.body.classList.remove('fade-out');
    }
});
</script>