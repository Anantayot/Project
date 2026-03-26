<?php
session_start();
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ ดึงไฟล์เชื่อมต่อฐานข้อมูล
include __DIR__ . "/partials/connectdb.php";

// ✅ ตรวจสอบการเข้าสู่ระบบ (ป้องกันคนพิมพ์ URL เข้ามาตรงๆ)
if (!isset($_SESSION['admin_id'])) { 
    // หมายเหตุ: เปลี่ยน 'admin_id' เป็นชื่อตัวแปร Session ที่คุณตั้งไว้ตอน Login สำเร็จ
    header("Location: login.php"); 
    exit;
}

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
  /* 🌟 แอนิเมชันตอนโหลดหน้า */
  .fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
  .delay-1 { animation-delay: 0.1s; } .delay-2 { animation-delay: 0.2s; } .delay-3 { animation-delay: 0.3s; } .delay-4 { animation-delay: 0.4s; }
  @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

  /* 📦 กล่องการ์ดทั่วไป (Modern Glassmorphism Style) */
  .custom-card { 
    background: linear-gradient(145deg, #1e293b, #151e2b); 
    border-radius: 16px; 
    border: 1px solid rgba(255, 255, 255, 0.08); 
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
  }
  .stat-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); 
    border-color: rgba(255, 255, 255, 0.2); 
  }
  
  /* 🔮 ไอคอนสถิติแบบมีแสงเรืองรอง */
  .icon-box { 
    width: 60px; height: 60px; 
    display: flex; align-items: center; justify-content: center; 
    border-radius: 16px; font-size: 1.6rem; color: #fff; 
    transition: all 0.3s ease;
  }
  .stat-card:hover .icon-box { transform: scale(1.1); }
  
  .bg-gradient-success { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3); }
  .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3); }
  .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3); }
  .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); }

  /* 🌌 ป้าย Welcome Banner */
  .welcome-banner { 
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(30, 41, 59, 0.9) 100%); 
    border-left: 6px solid #22c55e; 
    position: relative;
    overflow: hidden;
  }
  .welcome-banner::after {
    content: ''; position: absolute; top: -50%; right: -20%; width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(34,197,94,0.1) 0%, transparent 70%);
    border-radius: 50%;
  }
  
  /* 🛒 รายการสินค้าใกล้หมด */
  .list-group-item-dark { 
    background: transparent; border: none; 
    border-bottom: 1px dashed rgba(255,255,255,0.08); 
    padding: 16px 12px; color: #f8fafc; display: flex; align-items: center; gap: 15px; 
    transition: all 0.3s ease; 
  }
  .list-group-item-dark:last-child { border-bottom: none; }
  .list-group-item-dark:hover { 
    background: rgba(255,255,255,0.03); 
    border-radius: 12px; 
    transform: translateX(8px); /* โฮเวอร์แล้วเลื่อนขวานิดนึง */
  }
  .product-img-sm { 
    width: 50px; height: 50px; object-fit: cover; 
    border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); 
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }

  /* ⏳ Activity Timeline */
  .timeline { 
    border-left: 2px dashed rgba(255, 255, 255, 0.15); /* เปลี่ยนเส้นเป็น dashed ดูทันสมัย */
    margin: 0 0 0 20px; 
    padding: 0; 
    list-style: none; 
    display: flex; flex-direction: column; justify-content: space-between; 
    min-height: 100%; 
  }
  .timeline-item { 
    position: relative; 
    padding-left: 35px; 
    transition: transform 0.3s ease;
  }
  .timeline-item:hover { transform: translateX(5px); }
  .timeline-icon { 
    position: absolute; 
    left: -17px; 
    top: 0; 
    width: 32px; 
    height: 32px; 
    border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 0.9rem; color: #fff; 
    box-shadow: 0 0 0 6px #1e293b, 0 4px 10px rgba(0,0,0,0.3); 
    z-index: 1;
  }

  /* 📜 Custom Scrollbar แบบมินิมอล */
  .scrollable-box { height: 400px; overflow-y: auto; padding-right: 12px; }
  .scrollable-box::-webkit-scrollbar { width: 6px; }
  .scrollable-box::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 10px; }
  .scrollable-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
  .scrollable-box::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }

  /* แต่งหัวตาราง (Card Header) */
  .card-header { background: transparent !important; }

  @media (max-width: 767px) {
    .welcome-banner h4 { font-size: 1.25rem; }
    .chart-container { min-height: 250px; }
    .icon-box { width: 45px; height: 45px; font-size: 1.2rem; }
  }
</style>

<div class="card custom-card welcome-banner fade-up mb-4">
  <div class="card-body p-4">
    <h4 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
      ยินดีต้อนรับสู่ MyCommiss Admin Panel <i class="bi bi-stars text-warning fs-4 animate__animated animate__pulse animate__infinite"></i>
    </h4>
    <p class="text-light opacity-75 mb-0 fs-6 d-none d-md-block" style="max-width: 650px;">
      ตรวจสอบยอดขาย ดูแลคำสั่งซื้อ และจัดการคลังสินค้าของคุณได้จากหน้าแดชบอร์ดนี้แบบเรียลไทม์
    </p>
  </div>
</div>

<div class="row g-4 mb-4"> 
  <div class="col-6 col-xl-3 fade-up delay-1">
    <div class="card custom-card stat-card h-100">
      <div class="card-body p-4 d-flex align-items-center justify-content-between"> 
        <div>
          <h6 class="text-white-50 mb-1 fw-medium" style="font-size:0.85rem; letter-spacing: 0.5px;">ยอดขายรวม</h6>
          <h3 class="fw-bold text-white mb-0">฿<?= number_format($total_income) ?></h3>
        </div>
        <div class="icon-box bg-gradient-success d-none d-sm-flex"><i class="bi bi-wallet2"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-2">
    <div class="card custom-card stat-card h-100">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-white-50 mb-1 fw-medium" style="font-size:0.85rem; letter-spacing: 0.5px;">คำสั่งซื้อทั้งหมด</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_orders) ?></h3>
        </div>
        <div class="icon-box bg-gradient-info d-none d-sm-flex"><i class="bi bi-cart-check"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-3">
    <div class="card custom-card stat-card h-100">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-white-50 mb-1 fw-medium" style="font-size:0.85rem; letter-spacing: 0.5px;">สินค้าในระบบ</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_products) ?></h3>
        </div>
        <div class="icon-box bg-gradient-warning d-none d-sm-flex"><i class="bi bi-box-seam"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 fade-up delay-4">
    <div class="card custom-card stat-card h-100">
      <div class="card-body p-4 d-flex align-items-center justify-content-between">
        <div>
          <h6 class="text-white-50 mb-1 fw-medium" style="font-size:0.85rem; letter-spacing: 0.5px;">สมาชิกลูกค้า</h6>
          <h3 class="fw-bold text-white mb-0"><?= number_format($total_customers) ?></h3>
        </div>
        <div class="icon-box bg-gradient-primary d-none d-sm-flex"><i class="bi bi-people"></i></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-12 col-xl-8 fade-up delay-1">
    <div class="card custom-card h-100">
      <div class="card-header border-bottom border-light border-opacity-10 p-4">
        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-graph-up-arrow text-success fs-5"></i> สถิติยอดขาย 7 วันล่าสุด
        </h6>
      </div>
      <div class="card-body p-4 chart-container" style="position: relative; height: 320px;">
        <canvas id="salesChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4 fade-up delay-2">
    <div class="card custom-card h-100">
      <div class="card-header border-bottom border-light border-opacity-10 p-4">
        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-pie-chart text-info fs-5"></i> สัดส่วนสถานะออเดอร์
        </h6>
      </div>
      <div class="card-body p-4 d-flex justify-content-center align-items-center chart-container" style="position: relative; height: 320px;">
        <canvas id="orderStatusChart"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4 d-flex align-items-stretch">
  
  <div class="col-12 col-xl-6 fade-up delay-3 d-flex">
    <div class="card custom-card w-100 d-flex flex-column h-100">
      <div class="card-header border-bottom border-light border-opacity-10 p-4">
        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-bell-fill text-primary fs-5"></i> กิจกรรมล่าสุด
        </h6>
      </div>
      <div class="card-body p-4 flex-grow-1 scrollable-box">
        <ul class="timeline">
          
          <?php foreach($recent_orders_timeline as $ro): ?>
          <li class="timeline-item">
            <div class="timeline-icon bg-success shadow-sm"><i class="bi bi-cart-plus"></i></div>
            <h6 class="text-white mb-2 fw-semibold" style="font-size: 1rem;">ออเดอร์ใหม่ #<?= $ro['order_id'] ?></h6>
            <div class="d-flex justify-content-between align-items-center flex-wrap bg-dark bg-opacity-25 p-2 rounded-3 border border-light border-opacity-10">
              <span class="text-light" style="font-size: 0.85rem;">
                <i class="bi bi-person-circle me-1 text-white-50"></i> <?= htmlspecialchars($ro['customer_name'] ?? 'ไม่ระบุ') ?> 
                <span class="ms-2 badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">฿<?= number_format($ro['total_price']) ?></span>
              </span>
              <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= date("d/m/y H:i", strtotime($ro['order_date'])) ?></small>
            </div>
          </li>
          <?php endforeach; ?>
          
          <?php foreach($recent_customers as $rc): ?>
          <li class="timeline-item">
            <div class="timeline-icon bg-info shadow-sm"><i class="bi bi-person-check"></i></div>
            <h6 class="text-white mb-2 fw-semibold" style="font-size: 1rem;">สมาชิกลูกค้าใหม่</h6>
            <div class="d-flex justify-content-between align-items-center flex-wrap bg-dark bg-opacity-25 p-2 rounded-3 border border-light border-opacity-10">
              <span class="text-light d-block" style="font-size: 0.85rem;">
                <i class="bi bi-stars me-1 text-warning"></i> คุณ <?= htmlspecialchars($rc['name']) ?> ได้สมัครสมาชิก
              </span>
              <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= date("d/m/y", strtotime($rc['created_at'])) ?></small>
            </div>
          </li>
          <?php endforeach; ?>

        </ul>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-6 fade-up delay-4 d-flex">
    <div class="card custom-card w-100 d-flex flex-column h-100">
      <div class="card-header border-bottom border-light border-opacity-10 p-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i> สินค้าใกล้หมด
        </h6>
        <a href="product/products.php" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1" style="font-size:0.8rem; transition: 0.3s;">
          <i class="bi bi-gear-fill me-1"></i> จัดการสต็อก
        </a>
      </div>
      <div class="card-body p-3 flex-grow-1 scrollable-box"> 
        <div class="list-group list-group-flush gap-2">
          <?php if(empty($low_stock)): ?>
            <div class="text-center py-5 h-100 d-flex flex-column justify-content-center align-items-center">
              <div class="icon-box bg-success bg-opacity-10 text-success mb-3" style="width:80px; height:80px; font-size:2.5rem;">
                <i class="bi bi-check-circle-fill"></i>
              </div>
              <h5 class="text-white">สต็อกสินค้าทั้งหมดปลอดภัยดี</h5>
              <p class="text-white-50 mb-0">ยังไม่มีสินค้าที่ต้องเติมสต็อกในขณะนี้</p>
            </div>
          <?php else: ?>
            <?php foreach($low_stock as $ls): ?>
              <div class="list-group-item list-group-item-dark">
                <img src="/Project/admin/uploads/product/<?= htmlspecialchars($ls['p_image'] ?? 'noimg.png') ?>" class="product-img-sm" alt="product">
                
                <div class="flex-grow-1 ms-3 overflow-hidden">
                  <h6 class="mb-1 text-truncate text-white fw-semibold" style="font-size: 0.95rem;"><?= htmlspecialchars($ls['p_name']) ?></h6>
                  <small class="text-white-50"><i class="bi bi-upc-scan me-1"></i>รหัสสินค้า: #<?= htmlspecialchars($ls['p_id']) ?></small>
                </div>
                <div class="text-end ms-2">
                  <span class="badge <?= $ls['p_stock']==0 ? 'bg-danger border border-danger' : 'bg-warning text-dark border border-warning' ?> rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <?= $ls['p_stock']==0 ? '<i class="bi bi-x-circle me-1"></i>หมด' : 'เหลือ ' . $ls['p_stock'] . ' ชิ้น' ?>
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
    Chart.defaults.color = 'rgba(255, 255, 255, 0.6)';
    Chart.defaults.font.family = "'Prompt', sans-serif";

    // กราฟเส้นยอดขาย
    new Chart(document.getElementById('salesChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: <?= json_encode($sales_labels) ?>,
        datasets: [{
          label: 'ยอดขาย (บาท)', data: <?= json_encode($sales_data) ?>,
          borderColor: '#22c55e', 
          backgroundColor: function(context) {
            const chart = context.chart;
            const {ctx, chartArea} = chart;
            if (!chartArea) return null;
            let gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
            gradient.addColorStop(0, 'rgba(34, 197, 94, 0)');
            gradient.addColorStop(1, 'rgba(34, 197, 94, 0.4)');
            return gradient;
          },
          borderWidth: 3, fill: true, tension: 0.4, 
          pointBackgroundColor: '#1e293b', pointBorderColor: '#22c55e', 
          pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7
        }]
      },
      options: { 
        responsive: true, maintainAspectRatio: false, 
        plugins: { legend: { display: false }, tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(15, 23, 42, 0.9)' } }, 
        scales: { 
          x: { grid: { display: false, drawBorder: false } }, 
          y: { grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, beginAtZero: true } 
        } 
      }
    });

    // กราฟโดนัทสถานะ
    new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($order_statuses) ?>,
        datasets: [{
          data: <?= json_encode($status_data) ?>,
          backgroundColor: ['#f59e0b', '#0ea5e9', '#6366f1', '#22c55e', '#ef4444'], 
          borderWidth: 2, borderColor: '#1e293b', hoverOffset: 8
        }]
      },
      options: { 
        responsive: true, maintainAspectRatio: false, cutout: '75%', 
        plugins: { 
          legend: { position: 'bottom', labels: { padding: 20, font: {size: 12}, usePointStyle: true, pointStyle: 'circle' } },
          tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(15, 23, 42, 0.9)' }
        } 
      }
    });
  });
</script>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/partials/layout.php";
?>