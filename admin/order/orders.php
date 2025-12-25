<?php
$pageTitle = "จัดการคำสั่งซื้อ";
include __DIR__ . "/../partials/connectdb.php";
ob_start();

try {
    $sql = "SELECT o.order_id, o.order_date, o.total_price, o.order_status, o.admin_verified, c.name AS customer_name 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            ORDER BY o.order_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='color:red;text-align:center;margin-top:20px;'>❌ SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!-- 🔹 ส่วนหัว -->
<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-bag-check"></i> จัดการคำสั่งซื้อ
</h3>

<div class="card shadow-lg border-0"
     style="background: linear-gradient(145deg, #161b22, #0e1116); border:1px solid #2c313a;">
  <div class="card-body">

    <?php if (empty($orders)): ?>
      <div class="alert alert-warning text-center mb-0">ยังไม่มีคำสั่งซื้อในระบบ</div>
    <?php else: ?>
      <div class="table-responsive">
        <table id="dataTable" class="table table-dark table-striped text-center align-middle mb-0"
               style="border-radius:10px; overflow:hidden;">
          <thead style="background:linear-gradient(90deg,#00d25b,#00b14a); color:#111; font-weight:600;">
            <tr>
              <th>#</th>
              <th>รหัสคำสั่งซื้อ</th>
              <th>ชื่อลูกค้า</th>
              <th>วันที่สั่งซื้อ</th>
              <th>ราคารวม (฿)</th>
              <th>สถานะคำสั่งซื้อ</th>
              <th>ตรวจสอบโดยแอดมิน</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $i => $o): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td class="fw-bold text-info">#<?= htmlspecialchars($o['order_id']) ?></td>
                <td class="text-white"><?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?></td>
                <td><?= date("d/m/Y", strtotime($o['order_date'])) ?></td>
                <td class="fw-semibold text-success"><?= number_format($o['total_price'], 2) ?></td>

                <!-- 🔹 สถานะคำสั่งซื้อ -->
                <td>
                  <?php
                    $status = $o['order_status'] ?? 'รอดำเนินการ';
                    if ($status == 'สำเร็จ') $badge = 'success'; // เขียว
                    elseif ($status == 'กำลังจัดเตรียม') $badge = 'warning text-dark'; // เหลือง
                    elseif ($status == 'จัดส่งแล้ว') $badge = 'info'; // ฟ้า
                    elseif ($status == 'ยกเลิก') $badge = 'danger'; // แดง
                    else $badge = 'secondary'; // เทา
                  ?>
                  <span class="badge bg-<?= $badge ?> px-3 py-2 rounded-pill">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>

                <!-- 🔹 ตรวจสอบโดยแอดมิน -->
                <td>
                  <?php
                    $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';
                    if ($verify == 'อนุมัติ') $vbadge = 'success'; // เขียว
                    elseif ($verify == 'ปฏิเสธ') $vbadge = 'danger'; // แดง MyCommiss
                    elseif ($verify == 'กำลังตรวจสอบ') $vbadge = 'purple text-white'; // ม่วงอมฟ้า
                    else $vbadge = 'secondary'; // เทา
                  ?>
                  <span class="badge bg-<?= $vbadge ?> px-3 py-2 rounded-pill">
                    <?= htmlspecialchars($verify) ?>
                  </span>
                </td>

                <!-- 🔹 ปุ่มจัดการ -->
                <td>
                  <a href="order_view.php?id=<?= $o['order_id'] ?>"
                     class="btn btn-outline-light btn-sm"
                     style="border-color:#00d25b; color:#00d25b;">
                    <i class="bi bi-eye"></i> ดู
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- 🎨 เพิ่ม CSS สี badge -->
<style>
  .bg-purple {
    background-color: #8e44ad !important; /* ม่วงอมฟ้า */
    color: #fff !important;
  }
  .bg-danger {
    background-color: #D10024 !important; /* แดง MyCommiss */
  }
  .bg-warning {
    background-color: #ffb300 !important; /* เหลืองส้มสดขึ้น */
    color: #111 !important;
  }
</style>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>

<!-- ✅ โหลดสคริปต์ DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- 🔹 ตั้งค่า DataTables -->
<script>
$(document).ready(function() {
  const table = $('#dataTable').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
      searchPlaceholder: "🔍 ค้นหาคำสั่งซื้อ / ชื่อลูกค้า / สถานะ...",
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
      info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
      infoEmpty: "ไม่มีข้อมูล",
      infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)"
    },
    pageLength: 10,
    responsive: true,
    order: [[0, "asc"]],
    columnDefs: [
      { orderable: false, targets: [7] } // ปิด sort ที่ปุ่ม "จัดการ"
    ]
  });

  $(".dataTables_filter input")
    .addClass("form-control form-control-sm ms-2")
    .css({
      "width": "250px",
      "background": "#161b22",
      "color": "#fff",
      "border": "1px solid #2c313a"
    });

  $(".dataTables_length select")
    .addClass("form-select form-select-sm bg-dark text-light border-secondary");
});
</script>
