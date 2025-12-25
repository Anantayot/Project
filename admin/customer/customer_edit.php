<?php
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) die("ไม่พบลูกค้า");

$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];

  $update = $conn->prepare("UPDATE customers SET name=?, email=?, phone=?, address=? WHERE customer_id=?");
  $update->execute([$name, $email, $phone, $address, $id]);
  header("Location: customers.php");
  exit;
}
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลลูกค้า
</h3>

<form method="post" class="card p-4 shadow-lg border-0">
  <div class="mb-3">
    <label class="form-label">ชื่อ-นามสกุล</label>
    <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">อีเมล</label>
    <input type="email" name="email" value="<?= htmlspecialchars($c['email']) ?>" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">เบอร์โทร</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">ที่อยู่</label>
    <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($c['address']) ?></textarea>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
    </button>
    <a href="customers.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left-circle"></i> กลับ
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
