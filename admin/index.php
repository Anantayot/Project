<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูล
include __DIR__ . "/partials/connectdb.php";

// 🕒 ระบบจับเวลา Session Timeout (10 นาที = 600 วินาที)
$timeout_duration = 600;

if (isset($_SESSION['last_activity'])) {
  $time_inactive = time() - $_SESSION['last_activity'];
  if ($time_inactive >= $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1"); 
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
// 📈 2. ข้อมูลกราฟยอดขายย้อนหลัง 7 วัน
// ==========================================
$sales_labels = [];
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales_labels[] = date('d/m', strtotime($date));
    $stmt = $conn->prepare("SELECT SUM(total_price) FROM orders WHERE DATE(order_date) = ? AND payment_status = 'ชำระเงินแล้ว'");
    $stmt->execute([$date]);
    $sales_data[] = $stmt->fetchColumn() ?: 0;
}

// ==========================================
// 🍩 3. ข้อมูลกราฟสัดส่วนสถานะคำสั่งซื้อ
// ==========================================
$order_statuses = ['รอดำเนินการ', 'กำลังจัดเตรียม', 'จัดส่งแล้ว', 'สำเร็จ', 'ยกเลิก'];
$status_data = [];
foreach ($order_statuses as $st) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE order_status = ?");
    $stmt->execute([$st]);
    $status_data[] = $stmt->fetchColumn() ?: 0;
}

// ==========================================
// ⚠️ 4. สินค้าใกล้หมดสต็อก (ดึงมาทั้งหมดที่ <= 5)
// ==========================================
$low_stock = $conn->query("
    SELECT p_id, p_name, p_stock, p_image 
    FROM product 
    WHERE p_stock <= 5 
    ORDER BY p_stock ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// 🔔 5. กิจกรรมล่าสุด (Timeline) - รวม 5 รายการ
// ==========================================
// ดึง 4 ออเดอร์ล่าสุด
$recent_orders_timeline = $conn->query("
    SELECT o.order_id, o.order_date, o.total_price, c.name AS customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.customer_id 
    ORDER BY o.order_id DESC LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);

// ดึง 1 ลูกค้าใหม่ล่าสุด
$recent_customers = $conn->query("
    SELECT customer_id, name, created_at 
    FROM customers 
    ORDER BY customer_id DESC LIMIT 1
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'แดชบอร์ด';
ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* แอนิเมชันตอนโหลดหน้า */
  .fade-up { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(15px); }
  .delay-1 { animation-delay: 0.1s; } .delay-2 { animation-delay: 0.2s; } .delay-3 { animation-delay: 0.3s; } .delay-4 { animation-delay: 0.4s; }
  @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

  /* กล่องการ์ดทั่วไป */
  .custom-card { background: var(--bg-card, #1e293b); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.3s ease; }
  .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important; border-color: rgba(255, 255, 255, 0.15); }
  
  .icon-box { width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; border-radius: 14px; font-size: 1.5rem; color: #fff; box-shadow: 0 5px 12px rgba(0,0,0,0.2); }
  .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
  .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }

  .welcome-banner { background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(30, 41, 59, 0.8) 100%); border-left: 5px solid var(--primary, #22c55e); }
  
  /* รายการสินค้าใกล้หมด */
  .list-group-item-dark { background: transparent; border: none; border-bottom: 1px dashed rgba(255,255,255,0.1); padding: 15px 0; color: #f8fafc; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
  .list-group-item-dark:last-child { border-bottom: none; }
  .list-group-item-dark:hover { background: rgba(255,255,255,0.02); border-radius: 8px; padding-left: 10px; padding-right: 10px; }
  .product-img-sm { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); }

  /* ✅ Activity Timeline */
  .timeline { 
    border-left: 2px solid rgba(255, 255, 255, 0.1); 
    margin: 0 0 0 15px; 
    padding: 0; 
    list-style: none; 
  }
  .timeline-item { 
    position: relative; 
    padding-left: 30px; 
    margin-bottom: 25px; 
  }
  .timeline-item:last-child { margin-bottom: 0; }
  .timeline-icon { 
    position: absolute; 
    left: -15px; 
    top: 0; 
    width: 28px; 
    height: 28px; 
    border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 0.8rem; color: #fff; 
    box-shadow: 0 0 0 5px #1e293b; 
  }

  /* ✅ คลาสสำหรับให้เลื่อน Scroll ได้เมื่อรายการยาวเกินไป */
  .scrollable-box {
    max-height: 420px;
    overflow-y: auto;
    padding-right: 10px;
  }
  /* ตกแต่ง Scrollbar ให้ดูหรูหรา */
  .scrollable-box::-webkit-scrollbar { width: 5px; }
  .scrollable-box::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 10px; }
  .scrollable-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
  .scrollable-box::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }

  @media (max-width: 767px) {
    .welcome-banner h4 { font-size: 1.2rem; }
    .chart-container { min-height: 250px; }
  }
</style>

<div class="card custom-card welcome-banner shadow-sm fade-up mb-4">
  <div class="card-body p-3 p-md-4">
    <h4 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">ยินดีต้อนรับสู่ MyCommiss Admin Panel <i class="bi bi-stars text-warning fs-5"></i></h4>
    <p class="text-light mb-0 fs-6 d-none d-md-block" style="max-width: 650px;">ตรวจสอบยอดขาย ดูแลคำสั่งซื้อ และจัดการคลังสินค้าของคุณได้จากหน้าแดชบอร์ดนี้ครับ</p>
  </div>
</div>

<div class="row g-3 mb-4"> 
  <div class="col-6 col-xl-3 fade-up delay-1">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between"> 
        <div><h6 class="text-light mb-1 fw-normal" style="font-size:0.85rem;">ยอดขายรวม</h6><h4 class="fw-bold text-white mb-0">฿<?= number_format($total_income) ?></h4></div>
        <div class="icon-box bg-gradient-success d-none d-sm-flex"><i class="bi bi-wallet2"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-2">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div><h6 class="text-light mb-1 fw-normal" style="font-size:0.85rem;">คำสั่งซื้อทั้งหมด</h6><h4 class="fw-bold text-white mb-0"><?= number_format($total_orders) ?></h4></div>
        <div class="icon-box bg-gradient-info d-none d-sm-flex"><i class="bi bi-cart-check"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-3">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div><h6 class="text-light mb-1 fw-normal" style="font-size:0.85rem;">สินค้าในระบบ</h6><h4 class="fw-bold text-white mb-0"><?= number_format($total_products) ?></h4></div>
        <div class="icon-box bg-gradient-warning d-none d-sm-flex"><i class="bi bi-box-seam"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-4">
    <div class="card custom-card stat-card shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div><h6 class="text-light mb-1 fw-normal" style="font-size:0.85rem;">สมาชิกลูกค้า</h6><h4 class="fw-bold text-white mb-0"><?= number_format($total_customers) ?></h4></div>
        <div class="icon-box bg-gradient-primary d-none d-sm-flex"><i class="bi bi-people"></i></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-12 col-xl-8 fade-up delay-1">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-header border-bottom border-secondary p-3"><h6 class="fw-bold text-white mb-0"><i class="bi bi-graph-up-arrow text-success me-2"></i> สถิติยอดขาย 7 วันล่าสุด</h6></div>
      <div class="card-body p-3 chart-container" style="position: relative; height: 320px;"><canvas id="salesChart"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-xl-4 fade-up delay-2">
    <div class="card custom-card shadow-lg h-100">
      <div class="card-header border-bottom border-secondary p-3"><h6 class="fw-bold text-white mb-0"><i class="bi bi-pie-chart text-info me-2"></i> สัดส่วนสถานะออเดอร์</h6></div>
      <div class="card-body p-3 d-flex justify-content-center align-items-center chart-container" style="position: relative; height: 320px;"><canvas id="orderStatusChart"></canvas></div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4 d-flex align-items-stretch">
  
  <div class="col-12 col-xl-6 fade-up delay-3 d-flex">
    <div class="card custom-card shadow-lg w-100 d-flex flex-column">
      <div class="card-header border-bottom border-secondary p-3"><h6 class="fw-bold text-white mb-0"><i class="bi bi-bell-fill text-primary me-2"></i> กิจกรรมล่าสุด</h6></div>
      <div class="card-body p-4 flex-grow-1 scrollable-box">
        <ul class="timeline">
          
          <?php foreach($recent_orders_timeline as $ro): ?>
          <li class="timeline-item">
            <div class="timeline-icon bg-success"><i class="bi bi-cart"></i></div>
            <h6 class="text-white mb-1" style="font-size: 0.95rem;">ออเดอร์ใหม่ #<?= $ro['order_id'] ?></h6>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <span class="text-white-50" style="font-size: 0.85rem;"><?= htmlspecialchars($ro['customer_name'] ?? 'ไม่ระบุ') ?> - <span class="text-info fw-bold">฿<?= number_format($ro['total_price']) ?></span></span>
              <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= date("d/m/y H:i", strtotime($ro['order_date'])) ?></small>
            </div>
          </li>
          <?php endforeach; ?>
          
          <?php foreach($recent_customers as $rc): ?>
          <li class="timeline-item">
            <div class="timeline-icon bg-info"><i class="bi bi-person"></i></div>
            <h6 class="text-white mb-1" style="font-size: 0.95rem;">สมาชิกลูกค้าใหม่</h6>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <span class="text-white-50 d-block" style="font-size: 0.85rem;">คุณ <?= htmlspecialchars($rc['name']) ?> ได้สมัครสมาชิก</span>
              <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= date("d/m/y", strtotime($rc['created_at'])) ?></small>
            </div>
          </li>
          <?php endforeach; ?>

        </ul>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-6 fade-up delay-4 d-flex">
    <div class="card custom-card shadow-lg w-100 d-flex flex-column">
      <div class="card-header border-bottom border-secondary p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-white mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i> สินค้าใกล้หมด</h6>
        <a href="product/products.php" class="btn btn-sm btn-outline-light rounded-pill" style="font-size:0.75rem;">จัดการสต็อก</a>
      </div>
      <div class="card-body p-3 flex-grow-1 scrollable-box"> 
        <div class="list-group list-group-flush">
          <?php if(empty($low_stock)): ?>
            <div class="text-center py-5 text-muted"><i class="bi bi-check-circle text-success fs-1 mb-3 d-block"></i>สต็อกสินค้าทั้งหมดปลอดภัยดีครับ</div>
          <?php else: ?>
            <?php foreach($low_stock as $ls): ?>
              <div class="list-group-item list-group-item-dark">
                <img src="/Project/admin/uploads/<?= htmlspecialchars($ls['p_image'] ?? 'noimg.png') ?>" class="product-img-sm border-secondary" alt="product">
                
                <div class="flex-grow-1 ms-3 overflow-hidden">
                  <h6 class="mb-1 text-truncate text-white" style="font-size: 0.9rem;"><?= htmlspecialchars($ls['p_name']) ?></h6>
                  <small class="text-white-50">รหัสสินค้า: #<?= htmlspecialchars($ls['p_id']) ?></small>
                </div>
                <div class="text-end ms-2">
                  <span class="badge <?= $ls['p_stock']==0 ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-3 py-2">
                    <?= $ls['p_stock']==0 ? 'หมดชั่วคราว' : 'เหลือ ' . $ls['p_stock'] . ' ชิ้น' ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Prompt', sans-serif";

    // กราฟเส้น
    new Chart(document.getElementById('salesChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: <?= json_encode($sales_labels) ?>,
        datasets: [{
          label: 'ยอดขาย (บาท)', data: <?= json_encode($sales_data) ?>,
          borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)',
          borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#1e293b', pointBorderColor: '#22c55e', pointRadius: 4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true } } }
    });

    // กราฟโดนัท
    new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($order_statuses) ?>,
        datasets: [{
          data: <?= json_encode($status_data) ?>,
          backgroundColor: ['#facc15', '#0ea5e9', '#6366f1', '#22c55e', '#ef4444'], borderWidth: 0, hoverOffset: 5
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { padding: 20, font: {size: 12} } } } }
    });
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/partials/layout.php";
?>