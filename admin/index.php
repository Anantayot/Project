<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจาก partials
include __DIR__ . "/partials/connectdb.php";

// 🕒 ระบบจับเวลา Session Timeout (10 นาที = 600 วินาที)
$timeout_duration = 600;

if (isset($_SESSION['last_activity'])) {
  $time_inactive = time() - $_SESSION['last_activity'];
  if ($time_inactive >= $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit;
  }
}
$_SESSION['last_activity'] = time();

// ==========================================
// 📊 1. ดึงข้อมูลสถิติรวม (Stat Cards)
// ==========================================
$total_products   = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
$total_customers  = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders     = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income     = $conn->query("SELECT SUM(total_price) FROM orders WHERE payment_status = 'ชำระเงินแล้ว'")->fetchColumn() ?: 0;

// ==========================================
// 🛒 2. ดึงข้อมูล 5 คำสั่งซื้อล่าสุด
// ==========================================
$recent_orders = $conn->query("
    SELECT o.order_id, o.order_date, o.total_price, o.order_status, c.name AS customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.customer_id 
    ORDER BY o.order_id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// ⚠️ 3. ดึงข้อมูลสินค้าใกล้หมดสต็อก (เหลือน้อยกว่าหรือเท่ากับ 5)
// ==========================================
$low_stock = $conn->query("
    SELECT p_id, p_name, p_stock, p_image 
    FROM product 
    WHERE p_stock <= 5 
    ORDER BY p_stock ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* Config สีสถานะสำหรับตารางคำสั่งซื้อ */
$statusColors = [
    'รอดำเนินการ'    => 'warning text-dark', 
    'กำลังจัดเตรียม'  => 'info text-dark',   
    'จัดส่งแล้ว'      => 'primary',       
    'สำเร็จ'         => 'success',       
    'ยกเลิก'         => 'danger'         
];

$pageTitle = 'แดชบอร์ด';

ob_start();
?>

<style>
  /* แอนิเมชันตอนโหลดหน้า */
  .fade-up { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(15px); }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }
  .delay-4 { animation-delay: 0.4s; }
  @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

  /* กล่องการ์ดทั่วไป */
  .custom-card {
    background: var(--bg-card, #1e293b);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }
  
  /* กล่องสถิติด้านบน */
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.15);
  }
  .icon-box {
    width: 55px; height: 55px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 14px; font-size: 1.5rem; color: #fff;
    box-shadow: 0 5px 12px rgba(0,0,0,0.2);
  }
  .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
  .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }

  /* กล่องต้อนรับ */
  .welcome-banner {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(30, 41, 59, 0.8) 100%);
    border-left: 5px solid var(--primary, #22c55e);
  }

  /* ตาราง Recent Orders */
  .table-dark { --bs-table-bg: transparent; --bs-table-color: #f8fafc; border-color: rgba(255, 255, 255, 0.05); }
  .table-dark th { color: #94a3b8; font-weight: 500; font-size: 0.9rem; padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .table-dark td { padding: 15px 10px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.05); }
  
  /* รายการสินค้าใกล้หมด */
  .list-group-item-dark {
    background: transparent;
    border: none;
    border-bottom: 1px dashed rgba(255,255,255,0.1);
    padding: 12px 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .list-group-item-dark:last-child { border-bottom: none; }
  .product-img-sm { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); }
</style>

<div class="card custom-card welcome-banner shadow-sm fade-up mb-4">
  <div class="card-body p-4">
    <h4 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
      ยินดีต้อนรับสู่ MyCommiss Admin Panel <i class="bi bi-stars text-warning fs-5"></i>
    </h4>
    <p class="text-light mb-0 fs-6" style="max-width: 650px;"> 
      คุณสามารถใช้แผงควบคุมนี้ในการจัดการสินค้า ตรวจสอบและอัปเดตสถานะคำสั่งซื้อ รวมถึงดูแลสมาชิกลูกค้าทั้งหมดได้อย่างง่ายดายผ่านเมนูด้านซ้ายมือครับ
    </p>
  </div>
</div>

<div class="row g-3 mb-4"> 
  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-1">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between"> 
        <div>
          <h6 class="text-light mb-1 fw-normal">ยอดขายรวม (บาท)</h6> 
          <h3 class="fw-bold text-white mb-0">฿ <?= number_format($total_income, 2) ?></h3>
        </div>
        <div class="icon-box bg-gradient-success"><i class="bi bi-wallet2"></i></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-2">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">คำสั่งซื้อทั้งหมด</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_orders) ?> <span class="fs-6 text-light fw-normal">รายการ</span></h3>
        </div>
        <div class="icon-box bg-gradient-info"><i class="bi bi-cart-check"></i></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-3">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">สินค้าในระบบ</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_products) ?> <span class="fs-6 text-light fw-normal">ชิ้น</span></h3>
        </div>
        <div class="icon-box bg-gradient-warning"><i class="bi bi-box-seam"></i></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-4">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">สมาชิกลูกค้า</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_customers) ?> <span class="fs-6 text-light fw-normal">คน</span></h3>
        </div>
        <div class="icon-box bg-gradient-primary"><i class="bi bi-people"></i></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  
  <div class="col-lg-8 fade-up delay-3">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-header border-bottom border-secondary p-4 d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-clock-history text-info me-2"></i> 5 คำสั่งซื้อล่าสุด</h5>
        <a href="order/orders.php" class="btn btn-sm btn-outline-light rounded-pill px-3">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark text-center align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start ps-4">รหัส</th>
                <th class="text-start">ลูกค้า</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ยอดรวม</th>
                <th class="pe-4">สถานะ</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($recent_orders)): ?>
                <tr><td colspan="5" class="py-4 text-muted">ยังไม่มีคำสั่งซื้อใหม่</td></tr>
              <?php else: ?>
                <?php foreach($recent_orders as $ro): ?>
                  <tr>
                    <td class="text-start ps-4 fw-bold text-success">#<?= htmlspecialchars($ro['order_id']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($ro['customer_name'] ?? 'ไม่ระบุ') ?></td>
                    <td class="text-light"><?= date("d/m/y H:i", strtotime($ro['order_date'])) ?></td>
                    <td class="fw-bold text-info">฿<?= number_format($ro['total_price'], 2) ?></td>
                    <td class="pe-4">
                      <?php 
                        $status = $ro['order_status'] ?? 'รอดำเนินการ';
                        $badge = $statusColors[$status] ?? 'secondary'; 
                      ?>
                      <span class="badge bg-<?= $badge ?> rounded-pill px-3"><?= htmlspecialchars($status) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 fade-up delay-4">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-header border-bottom border-secondary p-4 d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i> สินค้าใกล้หมด</h5>
        <a href="product/products.php" class="btn btn-sm btn-outline-light rounded-pill px-3">จัดการ</a>
      </div>
      <div class="card-body p-4">
        <ul class="list-group list-group-flush">
          <?php if(empty($low_stock)): ?>
            <div class="text-center py-4 text-muted">
              <i class="bi bi-check-circle text-success fs-2 mb-2 d-block"></i>
              สต็อกสินค้าปลอดภัยดีครับ
            </div>
          <?php else: ?>
            <?php foreach($low_stock as $ls): ?>
              <li class="list-group-item list-group-item-dark">
                <img src="uploads/<?= htmlspecialchars($ls['p_image'] ?? 'noimg.png') ?>" class="product-img-sm" alt="product">
                <div class="flex-grow-1 min-w-0">
                  <h6 class="mb-1 text-truncate text-white" style="font-size: 0.95rem;" title="<?= htmlspecialchars($ls['p_name']) ?>">
                    <?= htmlspecialchars($ls['p_name']) ?>
                  </h6>
                  <small class="text-muted">รหัส: #<?= htmlspecialchars($ls['p_id']) ?></small>
                </div>
                <div class="text-end">
                  <?php if($ls['p_stock'] == 0): ?>
                    <span class="badge bg-danger rounded-pill px-2">หมด!</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark rounded-pill px-2">เหลือ <?= $ls['p_stock'] ?></span>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

</div>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials
include __DIR__ . "/partials/layout.php";
?>