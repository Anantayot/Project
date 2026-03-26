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
  // คำนวณว่าไม่ได้ใช้งานมานานกี่วินาทีแล้ว
  $time_inactive = time() - $_SESSION['last_activity'];
  
  if ($time_inactive >= $timeout_duration) {
    // ถ้าเกิน 10 นาที ให้ล้างค่า Session ทิ้งทั้งหมด
    session_unset();
    session_destroy();
    
    // เด้งกลับไปหน้า login พร้อมส่งค่า ?timeout=1 ไปบอก
    header("Location: ../login.php?timeout=1");
    exit;
  }
}

// ✅ อัปเดตเวลาล่าสุด ทุกครั้งที่มีการกดรีเฟรชหรือเปลี่ยนหน้า
$_SESSION['last_activity'] = time();

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
    animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(15px);
  }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }
  .delay-4 { animation-delay: 0.4s; }

  /* แต่งกล่องสถิติให้พอดีและสวยงาม */
  .stat-card {
    background: var(--bg-card);
    border-radius: 15px; /* ปรับมุมให้โค้งพอดี */
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
    border-color: rgba(255, 255, 255, 0.15);
  }

  /* กล่องใส่ไอคอนปรับให้เล็กลงสมส่วน */
  .icon-box {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.5rem;
    color: #fff;
    box-shadow: 0 5px 12px rgba(0,0,0,0.2);
  }
  .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
  .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }

  /* กล่องต้อนรับ */
  .welcome-banner {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(30, 41, 59, 0.8) 100%);
    border-radius: 15px;
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-left: 5px solid var(--primary);
    position: relative;
    overflow: hidden;
  }
</style>

<div class="row g-3 mb-4"> <div class="col-12 col-sm-6 col-xl-3 fade-up delay-1">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-3 d-flex align-items-center justify-content-between"> <div>
          <h6 class="text-light mb-1 fw-normal">ยอดขายรวม (บาท)</h6> <h3 class="fw-bold text-white mb-0">฿ <?= number_format($total_income, 2) ?></h3>
        </div>
        <div class="icon-box bg-gradient-success">
          <i class="bi bi-wallet2"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-2">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">คำสั่งซื้อทั้งหมด</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_orders) ?> <span class="fs-6 text-light fw-normal">รายการ</span></h3>
        </div>
        <div class="icon-box bg-gradient-info">
          <i class="bi bi-cart-check"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-3">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">สินค้าในระบบ</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_products) ?> <span class="fs-6 text-light fw-normal">ชิ้น</span></h3>
        </div>
        <div class="icon-box bg-gradient-warning">
          <i class="bi bi-box-seam"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3 fade-up delay-4">
    <div class="card stat-card shadow-sm">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-light mb-1 fw-normal">สมาชิกลูกค้า</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_customers) ?> <span class="fs-6 text-light fw-normal">คน</span></h3>
        </div>
        <div class="icon-box bg-gradient-primary">
          <i class="bi bi-people"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="card welcome-banner shadow-sm fade-up delay-4">
  <div class="card-body p-4">
    <h4 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
      ยินดีต้อนรับสู่ MyCommiss Admin Panel <i class="bi bi-stars text-warning fs-5"></i>
    </h4>
    <p class="text-light mb-0 fs-6" style="max-width: 650px;"> คุณสามารถใช้แผงควบคุมนี้ในการจัดการสินค้า ตรวจสอบและอัปเดตสถานะคำสั่งซื้อ รวมถึงดูแลสมาชิกลูกค้าทั้งหมดได้อย่างง่ายดายผ่านเมนูด้านซ้ายมือครับ
    </p>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
// ✅ ชี้ไปดึง layout จากโฟลเดอร์ partials
include __DIR__ . "/partials/layout.php";
?>