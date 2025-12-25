<?php
include __DIR__ . "/../partials/connectdb.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['cat_name'];
  $desc = $_POST['cat_description'];

  $stmt = $conn->prepare("INSERT INTO category (cat_name, cat_description) VALUES (?, ?)");
  $stmt->execute([$name, $desc]);
  header("Location: categories.php");
  exit;
}
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-plus-circle"></i> เพิ่มประเภทสินค้า
</h3>

<form method="post" class="card p-4 shadow-lg border-0">
  <div class="mb-3">
    <label class="form-label">ชื่อประเภทสินค้า</label>
    <input type="text" name="cat_name" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">รายละเอียด</label>
    <textarea name="cat_description" rows="3" class="form-control"></textarea>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึก
    </button>
    <a href="categories.php" class="btn btn-secondary">
      <i class="bi bi-x-circle"></i> ยกเลิก
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
