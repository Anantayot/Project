<?php
session_start();
include __DIR__ . "../partials/connectdb.php";

// ✅ บังคับให้ต้องล็อกอินก่อนถึงจะเข้า Dashboard ได้
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

// ✅ ดึงข้อมูลสรุปจากฐานข้อมูล
$total_products   = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
$total_customers  = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders     = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income     = $conn->query("SELECT SUM(total_price) FROM orders WHERE payment_status = 'ชำระเงินแล้ว'")->fetchColumn() ?: 0;

$pageTitle = 'แดชบอร์ด'; // ตั้งชื่อหน้าที่จะไปโชว์ที่ Topbar

// 🌟 เริ่มเก็บเนื้อหา HTML ลงตัวแปร
ob_start();
?>

<div class="row g-4 mb-4">
  
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card bg-card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 15px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2">ยอดขายรวม (บาท)</h6>
          <h3 class="fw-bold text-success mb-0">฿ <?= number_format($total_income, 2) ?></h3>
        </div>
        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
          <i class="bi bi-cash-stack text-success fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card bg-card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 15px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2">คำสั่งซื้อทั้งหมด</h6>
          <h3 class="fw-bold text-info mb-0"><?= number_format($total_orders) ?> <span class="fs-6 text-muted fw-normal">รายการ</span></h3>
        </div>
        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
          <i class="bi bi-cart-check text-info fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card bg-card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 15px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2">สินค้าในระบบ</h6>
          <h3 class="fw-bold text-warning mb-0"><?= number_format($total_products) ?> <span class="fs-6 text-muted fw-normal">ชิ้น</span></h3>
        </div>
        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
          <i class="bi bi-box-seam text-warning fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card bg-card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 15px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2">สมาชิกลูกค้า</h6>
          <h3 class="fw-bold text-primary mb-0"><?= number_format($total_customers) ?> <span class="fs-6 text-muted fw-normal">คน</span></h3>
        </div>
        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
          <i class="bi bi-people text-primary fs-3"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(34,197,94,0.1) 0%, rgba(15,23,42,0) 100%); border-radius: 15px; border-left: 4px solid var(--primary) !important;">
  <div class="card-body p-4">
    <h4 class="fw-bold text-white mb-2">ยินดีต้อนรับสู่ MyCommiss Admin Panel 🚀</h4>
    <p class="text-muted mb-0">คุณสามารถจัดการสินค้า ตรวจสอบคำสั่งซื้อ และดูแลสมาชิกลูกค้าได้จากเมนูด้านซ้ายมือครับ</p>
  </div>
</div>

<?php
$pageContent = ob_get_clean(); // 🌟 สิ้นสุดการเก็บ HTML

// ✅ เรียกใช้ layout.php เพื่อประกอบร่าง (Sidebar + Navbar + Content)
include __DIR__ . "/layout.php";
?>