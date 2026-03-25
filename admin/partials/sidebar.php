<aside id="sidebar">
  <div class="sidebar-brand">
    <i class="bi bi-laptop"></i> <span class="ms-2">MyCommiss</span>
  </div>
  
  <div class="admin-profile">
    <img src="/mycommiss/icon_mycommiss.png" alt="Admin" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'">
    <h6><?= $_SESSION['admin_name'] ?? 'ผู้ดูแลระบบ' ?></h6>
    <small><i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>Online</small>
  </div>

  <ul class="sidebar-menu">
    <li><a href="/mycommiss/admin/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> แดชบอร์ด</a></li>
    <li><a href="/mycommiss/admin/product/products.php" class="<?= strpos($_SERVER['PHP_SELF'], 'product') !== false ? 'active' : '' ?>"><i class="bi bi-box-seam"></i> จัดการสินค้า</a></li>
    <li><a href="/mycommiss/admin/categories/categories.php" class="<?= strpos($_SERVER['PHP_SELF'], 'categories') !== false ? 'active' : '' ?>"><i class="bi bi-tags"></i> ประเภทสินค้า</a></li>
    <li><a href="/mycommiss/admin/customer/customers.php" class="<?= strpos($_SERVER['PHP_SELF'], 'customer') !== false ? 'active' : '' ?>"><i class="bi bi-people"></i> ข้อมูลลูกค้า</a></li>
    <li><a href="/mycommiss/admin/order/orders.php" class="<?= strpos($_SERVER['PHP_SELF'], 'order') !== false ? 'active' : '' ?>"><i class="bi bi-bag-check"></i> คำสั่งซื้อ</a></li>
  </ul>

  <div class="sidebar-footer">
    <a href="/mycommiss/admin/logout.php" class="btn-logout" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
      <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
    </a>
  </div>
</aside>