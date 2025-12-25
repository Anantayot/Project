<?php
include __DIR__ . "/../partials/connectdb.php";

$cats = $conn->query("SELECT * FROM category")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name   = $_POST['name'];
  $price  = $_POST['price'];
  $stock  = $_POST['stock'];
  $cat_id = $_POST['cat_id'];
  $desc   = $_POST['description'];

  $image = "";
  if (!empty($_FILES['image']['name'])) {
    // 🔹 สร้างชื่อไฟล์ใหม่ ป้องกันชื่อซ้ำ
    $image = time() . "_" . basename($_FILES['image']['name']);

    // 🔹 โฟลเดอร์เก็บรูป (อยู่นอก /product/)
    $targetDir = __DIR__ . "/../uploads/";

    // 🔹 ถ้าโฟลเดอร์ยังไม่มี ให้สร้าง
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    // 🔹 ย้ายไฟล์จาก temp ไปยัง uploads/
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image)) {
      die("❌ ไม่สามารถอัปโหลดรูปภาพได้");
    }
  }

  // 🔹 เพิ่มข้อมูลลงฐานข้อมูล
  $stmt = $conn->prepare("
    INSERT INTO product (p_name, p_price, p_stock, p_description, p_image, cat_id)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$name, $price, $stock, $desc, $image, $cat_id]);

  header("Location: products.php");
  exit;
}
?>

<h3 class="mb-4 text-center fw-bold text-white">
  <i class="bi bi-plus-circle"></i> เพิ่มสินค้าใหม่
</h3>

<form method="post" enctype="multipart/form-data" 
      class="card p-4 shadow-lg border-0"
      style="background:linear-gradient(145deg,#161b22,#0e1116);border:1px solid #2c313a;color:#fff;">
  
  <div class="mb-3">
    <label class="form-label">ชื่อสินค้า</label>
    <input type="text" name="name" class="form-control" required>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">ราคา (฿)</label>
      <input type="number" name="price" class="form-control" step="0.01" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">สต็อก</label>
      <input type="number" name="stock" class="form-control" required>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">หมวดหมู่</label>
    <select name="cat_id" class="form-select" required>
      <option value="">-- เลือกหมวดหมู่ --</option>
      <?php foreach($cats as $c): ?>
        <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">รายละเอียด</label>
    <textarea name="description" rows="3" class="form-control"></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">รูปภาพ</label>
    <input type="file" name="image" class="form-control">
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึก
    </button>
    <a href="products.php" class="btn btn-secondary">
      <i class="bi bi-x-circle"></i> ยกเลิก
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>