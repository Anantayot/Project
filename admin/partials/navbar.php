<div class="topbar d-none d-lg-flex">
  <h4 class="mb-0 text-white fw-bold"><i class="bi bi-speedometer2 me-2 text-success"></i><?= $pageTitle ?? 'Dashboard' ?></h4>
  
  <div class="d-flex align-items-center gap-2">
    <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
      <i class="bi bi-shop me-1"></i> ดูหน้าร้าน
    </a>
    
    <a href="../logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
      <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
    </a>
  </div>
</div>

<nav class="mobile-navbar d-lg-none">
  <button class="btn-menu" id="menuToggle"><i class="bi bi-list"></i></button>
  
  <h5 class="brand-mobile text-truncate px-2 mb-0" style="max-width: 70%;"><?= $pageTitle ?? 'MyCommiss' ?></h5>
  
  <a href="../logout.php" class="text-danger fs-4" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
    <i class="bi bi-box-arrow-right"></i>
  </a>
</nav>