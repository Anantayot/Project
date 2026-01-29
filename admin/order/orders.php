<?php
$pageTitle = "จัดการคำสั่งซื้อ";
include __DIR__ . "/../partials/connectdb.php";
ob_start();

$sql = "SELECT o.order_id, o.order_date, o.total_price, 
               o.order_status, o.admin_verified, 
               c.name AS customer_name 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.customer_id 
        ORDER BY o.order_id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-bag-check"></i> จัดการคำสั่งซื้อ
</h3>

<div class="card shadow-lg border-0"
     style="background: linear-gradient(145deg, #161b22, #0e1116); border:1px solid #2c313a;">
  <div class="card-body">

    <div class="table-responsive">
      <table id="dataTable" class="table table-dark table-striped text-center align-middle mb-0">
        <thead style="background:linear-gradient(90deg,#00d25b,#00b14a); color:#111;">
          <tr>
            <th>#</th>
            <th>รหัสคำสั่งซื้อ</th>
            <th>ชื่อลูกค้า</th>
            <th>วันที่</th>
            <th>ราคารวม</th>
            <th>สถานะคำสั่งซื้อ</th>
            <th>ตรวจสอบโดยแอดมิน</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>

<?php foreach ($orders as $i => $o): ?>

<tr>
  <td><?= $i+1 ?></td>
  <td class="fw-bold text-info">#<?= htmlspecialchars($o['order_id']) ?></td>
  <td><?= htmlspecialchars($o['customer_name'] ?? 'ไม่ระบุ') ?></td>
  <td><?= date("d/m/Y", strtotime($o['order_date'])) ?></td>
  <td class="fw-semibold text-success"><?= number_format($o['total_price'],2) ?></td>

  <!-- ===== สถานะคำสั่งซื้อ ===== -->
  <td>
    <?php
      $status = $o['order_status'] ?? 'รอดำเนินการ';

      switch ($status) {
        case 'กำลังจัดเตรียม':
          $badge = 'warning';           // ส้ม
          break;
        case 'จัดส่งแล้ว':
          $badge = 'purple';            // ม่วง
          break;
        case 'สำเร็จ':
          $badge = 'success';           // เขียว
          break;
        case 'ยกเลิก':
          $badge = 'danger';            // แดง
          break;
        case 'รอดำเนินการ':
        default:
          $badge = 'light';             // เทาอ่อน/ขาว
          break;
      }
    ?>
    <span class="badge bg-<?= $badge ?> px-3 py-2 rounded-pill">
      <?= htmlspecialchars($status) ?>
    </span>
  </td>

  <!-- ===== ตรวจสอบโดยแอดมิน ===== -->
  <td>
    <?php
      $verify = $o['admin_verified'] ?? 'รอตรวจสอบ';

      switch ($verify) {
        case 'อนุมัติ':
          $vbadge = 'success';       // เขียว
          break;
        case 'ปฏิเสธ':
          $vbadge = 'danger';        // แดง
          break;
        case 'กำลังตรวจสอบ':
          $vbadge = 'secondary';     // เทาเข้ม
          break;
        case 'รอตรวจสอบ':
        default:
          $vbadge = 'light';         // เทาอ่อน/ขาว
          break;
      }
    ?>
    <span class="badge bg-<?= $vbadge ?> px-3 py-2 rounded-pill">
      <?= htmlspecialchars($verify) ?>
    </span>
  </td>

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
  </div>
</div>

<!-- ===== CSS สีเพิ่มเติม ===== -->
<style>
.bg-purple{
  background:#8e44ad !important;
  color:#fff !important;
}
.bg-warning{
  background:#ffb300 !important;
  color:#111 !important;
}
.bg-danger{
  background:#D10024 !important;
  color:#fff !important;
}
.bg-light{
  background:#e9ecef !important;
  color:#111 !important;
}
</style>
