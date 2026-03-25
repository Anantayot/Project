<div class="topbar d-none d-lg-flex">
  <h4 class="mb-0 text-white fw-bold"><i class="bi bi-speedometer2 me-2 text-success"></i><?= $pageTitle ?? 'Dashboard' ?></h4>
  
  <div class="d-flex align-items-center gap-2">
    <a href="/Project/user/index.php" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
      <i class="bi bi-shop me-1"></i> ดูหน้าร้าน
    </a>
  </div>
</div>

<nav class="mobile-navbar d-lg-none">
  <button class="btn-menu" id="menuToggle"><i class="bi bi-list"></i></button>
  
  <h5 class="brand-mobile text-truncate px-2 mb-0" style="max-width: 70%;"><?= $pageTitle ?? 'MyCommiss' ?></h5>
  
  <a href="/Project/user/index.php" class="text-success fs-4">
    <i class="bi bi-shop"></i>
  </a>
</nav>