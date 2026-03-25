<?php
session_start();
// 🔒 ตรวจสอบว่าแอดมินล็อกอินหรือยัง (เดี๋ยวเราจะทำหน้า login แอดมินทีหลัง)
// ถ้ายูสเซอร์นี้ยังไม่ได้ล็อกอิน ให้เด้งไปหน้า login
/* if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
*/

// ออกไป 1 โฟลเดอร์เพื่อเรียกใช้ connectdb.php ของระบบหลัก
include("partials/connectdb.php");

// 📊 ดึงสถิติเบื้องต้นมาแสดงบน Dashboard
// 1. จำนวนลูกค้าทั้งหมด
$stmt = $conn->query("SELECT COUNT(*) FROM customers");
$total_customers = $stmt->fetchColumn();

// 2. จำนวนสินค้าทั้งหมด
$stmt = $conn->query("SELECT COUNT(*) FROM product");
$total_products = $stmt->fetchColumn();

// 3. จำนวนคำสั่งซื้อทั้งหมด
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt->fetchColumn();

// 4. ยอดขายรวม (เฉพาะที่ชำระเงินแล้ว)
$stmt = $conn->query("SELECT SUM(total_price) FROM orders WHERE payment_status = 'ชำระเงินแล้ว'");
$total_sales = $stmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>ระบบจัดการหลังบ้าน | MyCommiss</title>
  
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="../icon_mycommiss.png" />
  
  <style>
    body { font-family: "Prompt", sans-serif; }
    .text-large { font-size: 2rem; font-weight: 600; }
  </style>
</head>
<body>
  <div class="container-scroller">
      
    <?php include("sidebar.php"); ?>

    <div class="container-fluid page-body-wrapper">
        
      <?php include("navbar_admin.php"); ?>

      <div class="main-panel">
        <div class="content-wrapper">
            
          <div class="row">
            <div class="col-12 grid-margin stretch-card">
              <div class="card corona-gradient-card">
                <div class="card-body py-4 px-4 px-md-5">
                  <div class="row align-items-center">
                    <div class="col-12 col-md-8 text-md-left text-center">
                      <h2 class="mb-2 font-weight-bold">ยินดีต้อนรับสู่ MyCommiss Admin Panel</h2>
                      <p class="mb-0 text-light">จัดการสินค้า คำสั่งซื้อ และดูแลลูกค้าของคุณได้ที่นี่</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h6 class="text-muted font-weight-normal">ยอดขายรวม (บาท)</h6>
                  <div class="row">
                    <div class="col-9">
                      <h3 class="text-success text-large">฿ <?= number_format($total_sales, 2) ?></h3>
                    </div>
                    <div class="col-3 d-flex align-items-center justify-content-center">
                      <i class="mdi mdi-cash-multiple icon-lg text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h6 class="text-muted font-weight-normal">คำสั่งซื้อทั้งหมด</h6>
                  <div class="row">
                    <div class="col-9">
                      <h3 class="text-info text-large"><?= number_format($total_orders) ?> <span class="fs-6 text-muted">รายการ</span></h3>
                    </div>
                    <div class="col-3 d-flex align-items-center justify-content-center">
                      <i class="mdi mdi-cart icon-lg text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h6 class="text-muted font-weight-normal">สินค้าในระบบ</h6>
                  <div class="row">
                    <div class="col-9">
                      <h3 class="text-warning text-large"><?= number_format($total_products) ?> <span class="fs-6 text-muted">ชิ้น</span></h3>
                    </div>
                    <div class="col-3 d-flex align-items-center justify-content-center">
                      <i class="mdi mdi-laptop icon-lg text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h6 class="text-muted font-weight-normal">สมาชิกลูกค้า</h6>
                  <div class="row">
                    <div class="col-9">
                      <h3 class="text-primary text-large"><?= number_format($total_customers) ?> <span class="fs-6 text-muted">คน</span></h3>
                    </div>
                    <div class="col-3 d-flex align-items-center justify-content-center">
                      <i class="mdi mdi-account-multiple icon-lg text-primary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
        
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © MyCommiss <?= date('Y') ?></span>
          </div>
        </footer>
        
      </div>
      </div>
  </div>

  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/hoverable-collapse.js"></script>
  <script src="assets/js/misc.js"></script>
  </body>
</html>