<?php
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) die("ไม่พบรหัสประเภทสินค้า");

$stmt = $conn->prepare("SELECT * FROM category WHERE cat_id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['cat_name'];
  $desc = $_POST['cat_description'];

  $update = $conn->prepare("UPDATE category SET cat_name=?, cat_description=? WHERE cat_id=?");
  $update->execute([$name, $desc, $id]);
  header("Location: categories.php");
  exit;
}
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-pencil-square"></i> แก้ไขประเภทสินค้า
</h3>

<form method="post" class="card p-4 shadow-lg border-0">
  <div class="mb-3">
    <label class="form-label">ชื่อประเภทสินค้า</label>
    <input type="text" name="cat_name" value="<?= htmlspecialchars($cat['cat_name']) ?>" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">รายละเอียด</label>
    <textarea name="cat_description" rows="3" class="form-control"><?= htmlspecialchars($cat['cat_description'] ?? '') ?></textarea>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
    </button>
    <a href="categories.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left-circle"></i> กลับ
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
