<?php
include __DIR__ . "/../partials/connectdb.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];

  // 🔍 ตรวจสอบอีเมลซ้ำก่อนเพิ่ม
  $check = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
  $check->execute([$email]);
  if ($check->fetchColumn() > 0) {
    $error = "❌ อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น";
  } else {
    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $address]);
    header("Location: customers.php");
    exit;
  }
}
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-person-plus"></i> เพิ่มข้อมูลลูกค้า
</h3>

<?php if(!empty($error)): ?>
  <div class="alert alert-danger text-center"><?= $error ?></div>
<?php endif; ?>

<form method="post" class="card p-4 shadow-lg border-0">
  <div class="mb-3">
    <label class="form-label">ชื่อ-นามสกุล</label>
    <input type="text" name="name" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">อีเมล</label>
    <input type="email" name="email" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">เบอร์โทร</label>
    <input type="text" name="phone" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">ที่อยู่</label>
    <textarea name="address" rows="3" class="form-control" required></textarea>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึก
    </button>
    <a href="customers.php" class="btn btn-secondary">
      <i class="bi bi-x-circle"></i> ยกเลิก
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
