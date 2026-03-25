<nav class="navbar p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
    <a class="navbar-brand brand-logo-mini text-decoration-none" href="index.php">
        <span class="text-danger font-weight-bold" style="font-size: 1.5rem;">MC</span>
    </a>
  </div>
  <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
    
    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="mdi mdi-menu"></span>
    </button>
    
    <ul class="navbar-nav navbar-nav-right">
      <li class="nav-item dropdown">
        <a class="nav-link" id="profileDropdown" href="#" data-toggle="dropdown">
          <div class="navbar-profile">
            <img class="img-xs rounded-circle" src="../icon_mycommiss.png" alt="">
            <p class="mb-0 d-none d-sm-block navbar-profile-name">ผู้ดูแลระบบ</p>
            <i class="mdi mdi-menu-down d-none d-sm-block"></i>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="profileDropdown">
          <h6 class="p-3 mb-0">ตั้งค่าบัญชี</h6>
          <div class="dropdown-divider"></div>
          
          <a class="dropdown-item preview-item" href="../index.php" target="_blank">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-dark rounded-circle">
                <i class="mdi mdi-web text-info"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <p class="preview-subject mb-1">ไปที่หน้าร้าน</p>
            </div>
          </a>
          
          <div class="dropdown-divider"></div>
          
          <a class="dropdown-item preview-item" href="logout.php" onclick="return confirm('คุณต้องการออกจากระบบผู้ดูแลใช่หรือไม่?');">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-dark rounded-circle">
                <i class="mdi mdi-logout text-danger"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <p class="preview-subject mb-1">ออกจากระบบ</p>
            </div>
          </a>
          
        </div>
      </li>
    </ul>
    
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="mdi mdi-format-line-spacing"></span>
    </button>
  </div>
</nav>