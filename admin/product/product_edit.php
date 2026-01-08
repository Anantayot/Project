<?php
$pageTitle = "แก้ไขสินค้า";
ob_start();

include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if(!$id) die("❌ ไม่พบสินค้า");

$product = $conn->prepare("SELECT * FROM product WHERE p_id=?");
$product->execute([$id]);
$p = $product->fetch();

$cats = $conn->query("SELECT * FROM category")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name   = $_POST['name'];
  $price  = $_POST['price'];
  $stock  = $_POST['stock'];
  $cat_id = $_POST['cat_id'];
  $desc   = $_POST['description'];

  // flag ว่าผู้ใช้ติ๊ก "ลบรูปเดิม" ไหม
  $deleteImage = isset($_POST['delete_image']) && $_POST['delete_image'] === '1';

  // path โฟลเดอร์อัปโหลด
  $uploadDir = __DIR__ . "/../uploads/";

  // ค่า default = รูปเดิมในฐานข้อมูล
  $image = $p['p_image'];

  // 1) ถ้าติ๊ก "ลบรูปเดิม" ให้ลบไฟล์เก่าทิ้งก่อน
  if ($deleteImage && !empty($p['p_image'])) {
    $oldPath = $uploadDir . $p['p_image'];
    if (file_exists($oldPath)) {
      unlink($oldPath); // ลบไฟล์ออกจากเซิร์ฟเวอร์
    }
    $image = null; // ล้างค่าในฐานข้อมูล
  }

  // 2) ถ้ามีอัปโหลดรูปใหม่เข้ามา
  if (!empty($_FILES['image']['name'])) {
    // ถ้ามีรูปเดิม และยังไม่ถูกลบในขั้นตอนข้างบน ก็ลบด้วย (กันไฟล์ค้าง)
    if (!$deleteImage && !empty($p['p_image'])) {
      $oldPath = $uploadDir . $p['p_image'];
      if (file_exists($oldPath)) {
        unlink($oldPath);
      }
    }

    // กันชื่อซ้ำ
    $image = time() . "_" . basename($_FILES['image']['name']);

    // ถ้าโฟลเดอร์ยังไม่มีให้สร้าง
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    // ย้ายไฟล์อัปโหลดไปไว้ในโฟลเดอร์ uploads
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
      die("❌ ไม่สามารถอัปโหลดรูปภาพได้");
    }
  }

  // อัปเดตฐานข้อมูล
  $stmt = $conn->prepare("UPDATE product 
                          SET p_name=?, p_price=?, p_stock=?, p_description=?, p_image=?, cat_id=? 
                          WHERE p_id=?");
  $stmt->execute([$name, $price, $stock, $desc, $image, $cat_id, $id]);

  header("Location: products.php");
  exit;
}
?>

<h3 class="text-center mb-4 text-white">
  <i class="bi bi-pencil-square"></i> แก้ไขสินค้า
</h3>

<form method="post" enctype="multipart/form-data"
      class="card p-4 shadow-lg border-0"
      style="background:linear-gradient(145deg,#161b22,#0e1116);color:#fff;">
  
  <div class="mb-3">
    <label class="form-label">ชื่อสินค้า</label>
    <input type="text" name="name" value="<?= htmlspecialchars($p['p_name']) ?>" class="form-control" required>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">ราคา (฿)</label>
      <input type="number" name="price" value="<?= $p['p_price'] ?>" class="form-control" step="0.01" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">สต็อก</label>
      <input type="number" name="stock" value="<?= $p['p_stock'] ?>" class="form-control" required>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">หมวดหมู่</label>
    <select name="cat_id" class="form-select" required>
      <?php foreach($cats as $c): ?>
        <option value="<?= $c['cat_id'] ?>" <?= $p['cat_id']==$c['cat_id']?'selected':'' ?>>
          <?= htmlspecialchars($c['cat_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">รายละเอียด</label>
    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($p['p_description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">รูปภาพ</label><br>
    <?php 
      $imagePath = "../uploads/" . htmlspecialchars($p['p_image']);
      $fileOnDisk = __DIR__ . "/../uploads/" . $p['p_image'];
      if (!empty($p['p_image']) && file_exists($fileOnDisk)): ?>
        <img src="<?= $imagePath ?>" width="100" class="rounded mb-2"><br>

        <!-- checkbox ลบรูปเดิม -->
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" value="1" id="delete_image" name="delete_image">
          <label class="form-check-label" for="delete_image">
            ลบรูปภาพเดิม
          </label>
        </div>

      <?php else: ?>
        <span class="text-muted">ยังไม่มีรูปภาพ</span><br>
      <?php endif; ?>
    <input type="file" name="image" class="form-control mt-2">
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
    </button>
    <a href="products.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left-circle"></i> กลับ
    </a>
  </div>
</form>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>
