<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
    <a class="sidebar-brand brand-logo text-decoration-none" href="index.php">
      <span class="text-white font-weight-bold" style="font-size: 1.5rem; font-family: 'Prompt', sans-serif;">MyCommiss</span>
    </a>
    <a class="sidebar-brand brand-logo-mini text-decoration-none" href="index.php">
      <span class="text-danger font-weight-bold" style="font-size: 1.5rem;">MC</span>
    </a>
  </div>
  <ul class="nav">
    <li class="nav-item profile">
      <div class="profile-desc">
        <div class="profile-pic">
          <div class="count-indicator">
            <img class="img-xs rounded-circle " src="../icon_mycommiss.png" alt="">
            <span class="count bg-success"></span>
          </div>
          <div class="profile-name">
            <h5 class="mb-0 font-weight-normal">ผู้ดูแลระบบ</h5>
            <span>Admin</span>
          </div>
        </div>
      </div>
    </li>
    <li class="nav-item nav-category">
      <span class="nav-link">เมนูจัดการระบบ</span>
    </li>
    
    <li class="nav-item menu-items <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <a class="nav-link" href="index.php">
        <span class="menu-icon">
          <i class="mdi mdi-speedometer"></i>
        </span>
        <span class="menu-title">แดชบอร์ด</span>
      </a>
    </li>

    <li class="nav-item menu-items <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
      <a class="nav-link" href="categories.php">
        <span class="menu-icon">
          <i class="mdi mdi-format-list-bulleted-type"></i>
        </span>
        <span class="menu-title">จัดการหมวดหมู่</span>
      </a>
    </li>

    <li class="nav-item menu-items <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
      <a class="nav-link" href="products.php">
        <span class="menu-icon">
          <i class="mdi mdi-laptop"></i>
        </span>
        <span class="menu-title">จัดการสินค้า</span>
      </a>
    </li>

    <li class="nav-item menu-items <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
      <a class="nav-link" href="orders.php">
        <span class="menu-icon">
          <i class="mdi mdi-cart"></i>
        </span>
        <span class="menu-title">จัดการคำสั่งซื้อ</span>
      </a>
    </li>

    <li class="nav-item menu-items <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
      <a class="nav-link" href="customers.php">
        <span class="menu-icon">
          <i class="mdi mdi-account-multiple"></i>
        </span>
        <span class="menu-title">ข้อมูลลูกค้า</span>
      </a>
    </li>

  </ul>
</nav>