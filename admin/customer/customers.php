<?php
$pageTitle = "จัดการลูกค้า";
include __DIR__ . "/../partials/connectdb.php";

// 🔹 ดึงข้อมูลลูกค้าทั้งหมด
$customers = $conn->query("SELECT * FROM customers ORDER BY customer_id ASC")->fetchAll();

ob_start();
?>

<!-- 🔹 ส่วนหัว -->
<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-people"></i> จัดการลูกค้า
</h3>

<div class="card shadow-lg border-0" 
     style="background: linear-gradient(145deg, #161b22, #0e1116); border:1px solid #2c313a;">
  <div class="card-body">

    <!-- ปุ่มเพิ่มลูกค้า -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h4 class="text-light fw-semibold mb-0">
        <i class="bi bi-list-ul"></i> รายชื่อลูกค้า
      </h4>
      <a href="customer_add.php" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle"></i> เพิ่มลูกค้า
      </a>
    </div>

    <!-- ตารางลูกค้า -->
    <div class="table-responsive">
      <table id="dataTable" class="table table-dark table-striped text-center align-middle mb-0"
             style="border-radius:10px; overflow:hidden;">
        <thead style="background:linear-gradient(90deg,#00d25b,#00b14a); color:#111; font-weight:600;">
          <tr>
            <th style="width:50px;">#</th>
            <th>ชื่อ-นามสกุล</th>
            <th>อีเมล</th>
            <th>เบอร์โทร</th>
            <th>ที่อยู่</th>
            <th>สมัครข่าวสาร</th>
            <th style="width:120px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($customers)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-info-circle"></i> ยังไม่มีข้อมูลลูกค้าในระบบ
              </td>
            </tr>
          <?php else: ?>
            <?php foreach($customers as $index => $c): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td class="text-start text-white"><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td class="text-warning"><?= htmlspecialchars($c['phone']) ?></td>
                <td class="text-light"><?= nl2br(htmlspecialchars($c['address'])) ?></td>

                <!-- ✅ แสดงสถานะ Subscribe -->
                <td>
                  <?php if ($c['subscribe'] == 1): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> สมัครแล้ว</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> ไม่ได้สมัคร</span>
                  <?php endif; ?>
                </td>

                <td>
                  <a href="customer_edit.php?id=<?= $c['customer_id'] ?>" 
                     class="btn btn-warning btn-sm me-1" title="แก้ไข">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="customer_delete.php?id=<?= $c['customer_id'] ?>" 
                     class="btn btn-danger btn-sm" title="ลบ"
                     onclick="return confirm('ยืนยันการลบลูกค้าคนนี้หรือไม่?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>

<!-- ✅ โหลดสคริปต์ DataTables หลัง Layout -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- 🔹 ตั้งค่า DataTables -->
<script>
$(document).ready(function() {
  const table = $('#dataTable').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
      searchPlaceholder: "🔍 ค้นหาชื่อลูกค้า / อีเมล / เบอร์โทร...",
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
      { orderable: false, targets: [6] } // ปิด sort ปุ่มจัดการ
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
