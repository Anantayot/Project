<?php
session_start();
include __DIR__ . "/partials/connectdb.php";

// ✅ ดึงข้อมูลสรุปจากฐานข้อมูล
$total_products   = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
$total_categories = $conn->query("SELECT COUNT(*) FROM category")->fetchColumn();
$total_customers  = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders     = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income     = $conn->query("SELECT SUM(total_price) FROM orders WHERE payment_status = 'ชำระเงินแล้ว'")->fetchColumn() ?? 0;

// ✅ ดึงข้อมูลรายเดือน (ลูกค้าใหม่)
$customer_stats = $conn->query("
  SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
  FROM customers
  GROUP BY month
  ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ✅ ดึงข้อมูลรายเดือน (รายได้เฉพาะชำระเงินแล้ว)
$income_stats = $conn->query("
  SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(total_price) AS total
  FROM orders
  WHERE payment_status = 'ชำระเงินแล้ว'
  GROUP BY month
  ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ✅ รวมเดือนทั้งหมดจากทั้งลูกค้าและรายได้
$months_all = array_unique(array_merge(
  array_column($customer_stats, 'month'),
  array_column($income_stats, 'month')
));
sort($months_all);

// ✅ เตรียมข้อมูลสำหรับกราฟ
$customers_per_month = [];
$income_per_month = [];

$customer_map = [];
foreach ($customer_stats as $row) {
  $customer_map[$row['month']] = (int)$row['total'];
}
$income_map = [];
foreach ($income_stats as $row) {
  $income_map[$row['month']] = (float)$row['total'];
}

foreach ($months_all as $m) {
  $customers_per_month[] = $customer_map[$m] ?? 0;
  $income_per_month[] = $income_map[$m] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - MyCommiss Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap');
body {
  background: #0d1117;
  font-family: "Prompt", sans-serif;
  color: #fff;
  overflow-x: hidden;
}

/* ✅ Sidebar */
#sidebar {
  background: #0d1117;
  position: fixed;
  top: 0; left: 0;
  width: 250px;
  height: 100vh;
  box-shadow: 0 0 25px rgba(0,0,0,0.6);
  transition: left 0.3s ease;
  z-index: 1000;
}
#sidebar.collapsed { left: -250px; }
#sidebar .brand {
  font-weight: 700;
  font-size: 1.25rem;
  text-align: center;
  padding: 22px 0;
  border-bottom: 1px solid #1f1f1f;
  color: #fff;
}
#sidebar .brand i { color: #00d25b; }
#sidebar ul { list-style: none; padding: 0; margin: 25px 0; }
#sidebar ul li a {
  display: flex; align-items: center;
  padding: 12px 20px;
  color: #b0b9c4; text-decoration: none;
  border-radius: 10px; transition: 0.3s;
  font-weight: 500; margin: 6px 10px;
}
#sidebar ul li a:hover {
  background: linear-gradient(145deg, rgba(0,210,91,0.25), rgba(0,210,91,0.15));
  color: #00d25b;
  transform: translateX(5px);
}
#sidebar ul li a.active {
  background: linear-gradient(145deg, #00d25b, #00b14a);
  color: #fff;
  box-shadow: 0 0 15px rgba(0,210,91,0.5);
}
.logout-btn {
  display: block; width: 85%; margin: 25px auto;
  padding: 12px 0; background: linear-gradient(145deg, #e74c3c, #c0392b);
  border-radius: 10px; color: #fff; font-weight: 600;
  text-align: center; transition: 0.3s;
}
.logout-btn:hover {
  background: linear-gradient(145deg, #ff5240, #e74c3c);
  box-shadow: 0 0 18px rgba(231,76,60,0.6);
  transform: translateY(-2px);
}

/* ✅ Main Panel */
.main-panel {
  margin-left: 250px;
  padding: 30px;
  background: #0d1117;
  min-height: 100vh;
}
.section-title {
  color: #fff;
  font-weight: 700;
  border-left: 5px solid #00d25b;
  padding-left: 10px;
  margin-bottom: 1.5rem;
}

/* ✅ Card */
.card-custom {
  background: linear-gradient(145deg, #161b22, #0e1116);
  border: 1px solid #2c313a;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  transition: transform .3s, box-shadow .3s;
}
.card-custom:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.5);
}
.card-custom h4 { color: #c9d1d9; font-weight: 600; }
.card-custom h2 { color: #00d25b; font-size: 2rem; font-weight: bold; }

/* ✅ กราฟ Container */
.chart-container {
  position: relative;
  height: 320px;
  width: 100%;
  overflow: hidden;
}

/* ✅ ไม่คลิกได้ */
.not-clickable {
  cursor: default !important;
  pointer-events: none !important;
  background: linear-gradient(145deg, #161b22, #101318);
  border: 1px solid #2c313a;
  box-shadow: inset 0 0 12px rgba(255,50,50,0.2);
}

/* ✅ Responsive */
@media (max-width: 991px) {
  #sidebar { left: -250px; }
  #sidebar.show { left: 0; }
  .navbar { display: flex; position: sticky; top: 0; z-index: 999; background: #161b22; }
  .main-panel { margin-left: 0; padding-top: 80px; }
}
  </style>
</head>
<body>

  <!-- ✅ Navbar มือถือ -->
  <nav class="navbar d-lg-none">
    <button class="toggle-btn" id="menuToggle"><i class="bi bi-list"></i></button>
    <h5 class="mb-0 fw-bold text-white">Dashboard</h5>
  </nav>

  <!-- ✅ Sidebar -->
  <aside id="sidebar">
    <div class="brand"><i class="bi bi-laptop"></i> MyCommiss</div>
    <ul>
      <li><a href="index.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li><a href="product/products.php"><i class="bi bi-box-seam me-2"></i> จัดการสินค้า</a></li>
      <li><a href="categories/categories.php"><i class="bi bi-tags me-2"></i> ประเภทสินค้า</a></li>
      <li><a href="customer/customers.php"><i class="bi bi-people me-2"></i> ลูกค้า</a></li>
      <li><a href="order/orders.php"><i class="bi bi-bag-check me-2"></i> คำสั่งซื้อ</a></li>
    </ul>
    <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a>
  </aside>

  <!-- ✅ Main Content -->
  <div class="main-panel">
    <h3 class="section-title"><i class="bi bi-speedometer2"></i> แผงควบคุมระบบหลังบ้าน</h3>

    <!-- 🔹 การ์ดสรุป -->
    <div class="row g-3">
      <div class="col-md-3 col-sm-6">
        <div class="card card-custom text-center p-3">
          <div class="card-body">
            <i class="bi bi-box-seam display-5 text-primary"></i>
            <h4 class="mt-3">สินค้า</h4>
            <h2><?= $total_products ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card card-custom text-center p-3">
          <div class="card-body">
            <i class="bi bi-tags display-5 text-info"></i>
            <h4 class="mt-3">ประเภทสินค้า</h4>
            <h2><?= $total_categories ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card card-custom text-center p-3">
          <div class="card-body">
            <i class="bi bi-people display-5 text-success"></i>
            <h4 class="mt-3">ลูกค้า</h4>
            <h2><?= $total_customers ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card card-custom text-center p-3">
          <div class="card-body">
            <i class="bi bi-bag-check display-5 text-warning"></i>
            <h4 class="mt-3">คำสั่งซื้อ</h4>
            <h2><?= $total_orders ?></h2>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 รายได้รวม -->
    <div class="row mt-4 justify-content-center">
      <div class="col-md-10">
        <div class="card card-custom text-center p-4 not-clickable">
          <div class="card-body">
            <i class="bi bi-cash-stack display-5 text-danger"></i>
            <h4 class="mt-3 text-white">รายได้รวมทั้งหมด (เฉพาะชำระเงินแล้ว)</h4>
            <h2 class="text-success"><?= number_format($total_income, 2) ?> ฿</h2>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 กราฟ -->
    <div class="row mt-5">
      <div class="col-md-6">
        <div class="card card-custom p-3">
          <h5 class="text-center text-info mb-3"><i class="bi bi-person-lines-fill"></i> ลูกค้าใหม่รายเดือน</h5>
          <div class="chart-container"><canvas id="customerChart"></canvas></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-custom p-3">
          <h5 class="text-center text-warning mb-3"><i class="bi bi-graph-up-arrow"></i> รายได้รวมรายเดือน (เฉพาะชำระเงินแล้ว)</h5>
          <div class="chart-container"><canvas id="incomeChart"></canvas></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // ✅ Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('menuToggle');
  if (toggleBtn) toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));

  // ✅ กราฟลูกค้าใหม่รายเดือน
  new Chart(document.getElementById('customerChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($months_all) ?>,
      datasets: [{
        label: 'ลูกค้าใหม่',
        data: <?= json_encode($customers_per_month) ?>,
        backgroundColor: 'rgba(0,123,255,0.8)',
        borderColor: '#007bff',
        borderWidth: 2,
        borderRadius: 6
      }]
    },
    options: {
      indexAxis: 'y',
      scales: {
        x: { beginAtZero: true, ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#fff' }, grid: { display: false } }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.parsed.x.toLocaleString()} คน`
          }
        }
      },
      maintainAspectRatio: false,
      animation: { duration: 1200, easing: 'easeOutQuart' }
    }
  });

  // ✅ กราฟรายได้รายเดือน
  new Chart(document.getElementById('incomeChart'), {
    type: 'line',
    data: {
      labels: <?= json_encode($months_all) ?>,
      datasets: [{
        label: 'รายได้ (บาท)',
        data: <?= json_encode($income_per_month) ?>,
        borderColor: '#00d25b',
        backgroundColor: 'rgba(0,210,91,0.2)',
        fill: true,
        tension: 0.3,
        borderWidth: 3,
        pointRadius: 5,
        pointBackgroundColor: '#00d25b',
        pointBorderColor: '#fff'
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        x: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.05)' } }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.parsed.y.toLocaleString()} ฿`
          }
        }
      },
      maintainAspectRatio: false,
      animation: { duration: 1200, easing: 'easeOutQuart' }
    }
  });
  </script>
</body>
</html>
