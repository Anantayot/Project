<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูลจาก partials
include __DIR__ . "/partials/connectdb.php";

// บังคับให้ต้องล็อกอิน
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

// ดึงข้อมูลสรุปจากฐานข้อมูล
$total_products   = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
$total_customers  = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders     = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income     = $conn->query("SELECT SUM(total_price) FROM orders WHERE payment_status = 'ชำระเงินแล้ว'")->fetchColumn() ?: 0;

$pageTitle = 'แดชบอร์ด';

ob_start();
?>

<style>
  /* แอนิเมชันตอนโหลดหน้า */
  .fade-up {
    animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
  }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }
  .delay-4 { animation-delay: 0.4s; }

  /* แต่งกล่องสถิติ */
  .stat-card {
    background: var(--bg-card);
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.03);
    transition: all 0.3s ease;
    height: 100%;
  }
  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(255, 255, 255, 0.1);
  }

  /* กล่องใส่ไอคอน */
  .icon-box {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 1.8rem;
    color: #fff;
    box-shadow: 0 8px 15px rgba(0,0,0,0.2);
  }
  .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
  .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }

  /* กล่องต้อนรับ */
  .welcome-banner {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(30, 41, 59, 0.8) 100%);
    border-radius: 18px;
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-left: 6px solid var(--primary);
    position: relative;
    overflow: hidden;
  }
  .welcome-bg-icon {
    position: absolute;
    right: -20px;
    bottom: -30px;
    font-size: 10rem;
    color: rgba(34, 197, 94, 0.05);
    transform: rotate(-15deg);
    pointer-events: none;
  }
</style>

<div class="row g-4 mb-4">
  
  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-1">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2 fw-normal">ยอดขายรวม (บาท)</h6>
          <h3 class="fw-bold text-white mb-0">฿ <?= number_format($total_income, 2) ?></h3>
        </div>
        <div class="icon-box bg-gradient-success shadow-success">
          <i class="bi bi-wallet2"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-2">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2 fw-normal">คำสั่งซื้อทั้งหมด</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_orders) ?> <span class="fs-6 text-muted fw-normal">รายการ</span></h3>
        </div>
        <div class="icon-box bg-gradient-info">
          <i class="bi bi-cart-check"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-3">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2 fw-normal">สินค้าในระบบ</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_products) ?> <span class="fs-6 text-muted fw-normal">ชิ้น</span></h3>
        </div>
        <div class="icon-box bg-gradient-warning">
          <i class="bi bi-box-seam"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-4">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-muted mb-2 fw-normal">สมาชิกลูกค้า</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_customers) ?> <span class="fs-6 text-muted fw-normal">คน</span></h3>
        </div>
        <div class="icon-box bg-gradient-primary">
          <i class="bi bi-people"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="card welcome-banner shadow-sm fade-up delay-4">
  <div class="card-body p-4 p-md-5">
    <i class="bi bi-rocket-takeoff welcome-bg-icon"></i>
    
    <div class="position-relative z-index-1">
      <h3 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
        ยินดีต้อนรับสู่ MyCommiss Admin Panel <i class="bi bi-stars text-warning fs-4"></i>
      </h3>
      <p class="text-muted mb-0 fs-6" style="max-width: 600px;">
        คุณสามารถใช้แผงควบคุมนี้ในการจัดการสินค้า ตรวจสอบและอัปเดตสถานะคำสั่งซื้อ รวมถึงดูแลสมาชิกลูกค้าทั้งหมดได้อย่างง่ายดายผ่านเมนูด้านซ้ายมือครับ
      </p>
    </div>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials
include __DIR__ . "/partials/layout.php";
?>