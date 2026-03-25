<?php
// ✅ ตรวจสอบ Session (ป้องกันการเรียกซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

// ✅ ดึงข้อมูลตะกร้า
$cart = $_SESSION['cart'] ?? [];

// ✅ ฟังก์ชันลบสินค้าออกจากตะกร้า
if (isset($_GET['remove'])) {
  $id = intval($_GET['remove']);
  unset($_SESSION['cart'][$id]);
  $_SESSION['toast_success'] = "🗑️ ลบสินค้าออกจากตะกร้าแล้ว";
  header("Location: cart.php");
  exit;
}

/**
 * ✅ helper: ดึงสต็อกจาก DB ตามรายการสินค้าในตะกร้า (ครั้งเดียว)
 */
function fetchStocks(PDO $conn, array $cart): array {
  if (empty($cart)) return [];

  $ids = array_map(fn($it) => (int)$it['id'], $cart);
  $ids = array_values(array_unique(array_filter($ids)));

  if (empty($ids)) return [];

  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $sql = "SELECT p_id, p_stock FROM product WHERE p_id IN ($placeholders)";
  $stmt = $conn->prepare($sql);
  $stmt->execute($ids);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stocks = [];
  foreach ($rows as $r) {
    $stocks[(int)$r['p_id']] = (int)$r['p_stock'];
  }
  return $stocks;
}

// ✅ ฟังก์ชันอัปเดตจำนวนสินค้า (เช็คสต็อกจริงจาก DB)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {

  $cart = $_SESSION['cart'] ?? [];
  $stocks = fetchStocks($conn, $cart);

  $hadAdjust = false;
  $hadRemove = false;

  foreach ($_POST['qty'] as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;

    // ถ้าไม่พบสินค้าในตะกร้าจริง ข้าม
    if (!isset($_SESSION['cart'][$id])) continue;

    // ถ้าผู้ใช้ใส่ 0 หรือติดลบ -> ลบออก
    if ($qty <= 0) {
      unset($_SESSION['cart'][$id]);
      $hadRemove = true;
      continue;
    }

    // สต็อกจาก DB (ถ้าไม่พบสินค้าใน DB ให้ลบออกจากตะกร้า)
    if (!isset($stocks[$id])) {
      unset($_SESSION['cart'][$id]);
      $hadRemove = true;
      continue;
    }

    $stock = (int)$stocks[$id];

    // ถ้าหมดสต็อก -> ลบออก
    if ($stock <= 0) {
      unset($_SESSION['cart'][$id]);
      $hadRemove = true;
      continue;
    }

    // ถ้าใส่เกินสต็อก -> ปรับให้เท่าสต็อก
    if ($qty > $stock) {
      $_SESSION['cart'][$id]['qty'] = $stock;
      $hadAdjust = true;
    } else {
      $_SESSION['cart'][$id]['qty'] = $qty;
    }
  }

  if ($hadRemove && $hadAdjust) {
    $_SESSION['toast_error'] = "⚠️ ปรับจำนวนตามสต็อกจริง และลบสินค้าที่หมด/ไม่พบออกจากตะกร้าแล้ว";
  } elseif ($hadRemove) {
    $_SESSION['toast_error'] = "⚠️ ลบสินค้าที่หมดสต็อก/ไม่พบออกจากตะกร้าแล้ว";
  } elseif ($hadAdjust) {
    $_SESSION['toast_error'] = "⚠️ ปรับจำนวนตามสต็อกจริงแล้ว";
  } else {
    $_SESSION['toast_success'] = "🔁 อัปเดตจำนวนสินค้าเรียบร้อยแล้ว";
  }

  header("Location: cart.php");
  exit;
}

// โหลด cart หลังอัปเดต
$cart = $_SESSION['cart'] ?? [];
$total = 0;

// ✅ (เสริม) ดึงสต็อกมาเพื่อเอาไปใส่ max ใน input ให้กรอกไม่เกิน
$stocks = fetchStocks($conn, $cart);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตะกร้าสินค้า | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Prompt", sans-serif;
      color: #333;
    }
    .cart-wrapper { min-height: 80vh; }

    /* 🔹 Card & Table (Desktop) */
    .card-cart {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      background: #fff;
    }
    .table-cart th {
      background-color: #fcfcfc;
      color: #555;
      font-weight: 600;
      border-bottom: 2px solid #eee;
      padding: 15px;
    }
    .table-cart td {
      vertical-align: middle;
      border-bottom: 1px solid #f8f9fa;
      padding: 15px;
    }
    
    /* 🔹 Image */
    .item-img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eee;
      padding: 5px;
    }

    /* 🔹 Input & Buttons */
    .qty-input {
      max-width: 90px;
      border-radius: 10px;
      text-align: center;
      font-weight: 600;
      margin: 0 auto;
    }
    .btn-primary-custom {
      background-color: #D10024;
      color: #fff;
      border-radius: 50px;
      padding: 10px 25px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
    }
    .btn-primary-custom:hover {
      background-color: #a5001b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(209,0,36,0.2);
    }
    .btn-outline-custom {
      border: 1px solid #ddd;
      border-radius: 50px;
      padding: 10px 25px;
      font-weight: 500;
      color: #555;
      transition: 0.3s;
      background: #fff;
    }
    .btn-outline-custom:hover { background: #f1f1f1; color: #333; }
    
    .btn-update {
      background-color: #ffc107;
      color: #000;
      border-radius: 50px;
      padding: 10px 25px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
    }
    .btn-update:hover { background-color: #e0a800; transform: translateY(-2px); }
    
    .btn-delete {
      color: #dc3545;
      background: rgba(220, 53, 69, 0.1);
      border-radius: 10px;
      padding: 8px 12px;
      transition: 0.3s;
      border: none;
    }
    .btn-delete:hover { background: #dc3545; color: #fff; }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      margin-top: 60px;
    }

    /* 📱 MOBILE RESPONSIVE (แปลง Table เป็น Card) */
    @media (max-width: 768px) {
      .table-cart thead { 
        display: none; /* ซ่อนหัวตารางบนมือถือ */
      }
      .table-cart tbody tr {
        display: block;
        border: 1px solid #eee;
        border-radius: 15px;
        margin-bottom: 20px;
        padding: 15px;
        position: relative; /* เพื่อให้จัดวางปุ่มลบ (absolute) ได้ */
      }
      .table-cart tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        padding: 10px 0;
        text-align: right;
      }
      /* แถวแรก (รูปภาพ+ชื่อสินค้า) ให้ชิดซ้ายและกินพื้นที่เต็ม */
      .table-cart tbody td:first-child {
        justify-content: flex-start;
        padding-bottom: 15px;
        border-bottom: 1px dashed #eee;
        margin-bottom: 10px;
        padding-right: 40px; /* เว้นที่ว่างฝั่งขวาให้ปุ่มลบ */
      }
      
      /* สร้าง Label ก่อนหน้าตัวเลข ด้วยเทคนิค ::before (อิงจาก data-label ใน HTML) */
      .table-cart tbody td[data-label]::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
      }
      .table-cart tbody td:first-child::before {
        display: none; /* ช่องแรกไม่ต้องแสดง Label */
      }

      .qty-input { margin: 0; } /* รีเซ็ต margin-auto บนมือถือ */

      /* จัดปุ่มลบให้ไปอยู่มุมขวาบนของการ์ดแต่ละใบ */
      .td-delete {
        position: absolute;
        top: 15px;
        right: 15px;
        display: block !important;
        padding: 0 !important;
        border: none !important;
      }

      /* จัดการส่วนยอดรวม (Tfoot) */
      .table-cart tfoot, .table-cart tfoot tr, .table-cart tfoot td {
        display: block;
        width: 100%;
      }
      .table-cart tfoot tr { border: none; padding: 0; }
      .table-cart tfoot td.label-total { display: none; } /* ซ่อน Label เดิม */
      .table-cart tfoot td.value-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid #eee;
        padding-top: 15px;
        text-align: right !important;
      }
      .table-cart tfoot td.value-total::before {
        content: "ยอดชำระสุทธิ:";
        font-size: 1.1rem;
        color: #6c757d;
        font-weight: 600;
      }
    }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="cart-wrapper">
  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 3000;">
    <?php if (isset($_SESSION['toast_success'])): ?>
      <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert">
        <div class="d-flex">
          <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_success'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
      <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['toast_error'])): ?>
      <div class="toast align-items-center text-bg-danger border-0 show shadow-lg" role="alert">
        <div class="d-flex">
          <div class="toast-body fs-6 fw-medium px-3 py-2"><?= $_SESSION['toast_error'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
      <?php unset($_SESSION['toast_error']); ?>
    <?php endif; ?>
  </div>

  <div class="container mt-5">
    
    <div class="d-flex align-items-center mb-4">
      <h3 class="fw-bold mb-0" style="color: #D10024;"><i class="bi bi-cart3 me-2"></i>ตะกร้าสินค้าของคุณ</h3>
    </div>

    <?php if (empty($cart)): ?>
      <div class="card card-cart p-5 text-center">
        <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
        <h4 class="mt-3 fw-bold text-secondary">ไม่มีสินค้าในตะกร้า</h4>
        <p class="text-muted">ดูเหมือนว่าคุณยังไม่ได้เพิ่มสินค้าใดๆ ลงในตะกร้าเลย</p>
        <div class="mt-4">
          <a href="index.php" class="btn btn-primary-custom"><i class="bi bi-shop me-2"></i>กลับไปเลือกซื้อสินค้า</a>
        </div>
      </div>
    <?php else: ?>
      <form method="post">
        <div class="card card-cart p-md-4 p-3"> <div class="table-responsive" style="overflow-x: hidden;"> <table class="table table-cart align-middle text-center mb-0">
              <thead>
                <tr>
                  <th class="text-start rounded-start">สินค้า</th>
                  <th>ราคาต่อชิ้น</th>
                  <th>จำนวน</th>
                  <th>ราคารวม</th>
                  <th class="rounded-end">ลบ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cart as $item):
                  $sum = $item['price'] * $item['qty'];
                  $total += $sum;

                  $imgPath = "../admin/uploads/" . $item['image'];
                  if (empty($item['image']) || !file_exists($imgPath)) {
                    $imgPath = "img/default.png";
                  }

                  $id = (int)$item['id'];
                  $maxStock = $stocks[$id] ?? null;
                ?>
                  <tr>
                    <td data-label="สินค้า" class="text-start">
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= $imgPath ?>" class="item-img" alt="<?= htmlspecialchars($item['name']) ?>">
                        <span class="fw-semibold text-dark text-break" style="max-width: 200px;"><?= htmlspecialchars($item['name']) ?></span>
                      </div>
                    </td>
                    <td data-label="ราคาต่อชิ้น" class="text-muted"><?= number_format($item['price'], 2) ?> ฿</td>
                    <td data-label="จำนวน">
                      <div class="d-flex flex-column align-items-center align-items-md-center align-items-end">
                        <input
                          type="number"
                          name="qty[<?= $id ?>]"
                          value="<?= (int)$item['qty'] ?>"
                          min="1"
                          class="form-control qty-input"
                          <?= (!is_null($maxStock) ? 'max="'.$maxStock.'"' : '') ?>
                          required
                        >
                        <?php if (!is_null($maxStock)): ?>
                          <div class="small text-muted mt-1">เหลือ: <?= (int)$maxStock ?></div>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td data-label="ราคารวม" class="fw-bold text-dark"><?= number_format($sum, 2) ?> ฿</td>
                    <td class="td-delete">
                      <a href="cart.php?remove=<?= $id ?>" class="btn-delete text-decoration-none" onclick="return confirm('ต้องการลบสินค้านี้ออกจากตะกร้าใช่หรือไม่?');" title="ลบสินค้า">
                        <i class="bi bi-trash3-fill"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="table-light">
                  <td colspan="3" class="text-end fw-semibold text-muted pt-3 pb-3 label-total">ยอดชำระสุทธิ:</td>
                  <td colspan="2" class="text-end pe-md-4 pt-3 pb-3 fw-bold text-danger fs-4 value-total"><?= number_format($total, 2) ?> ฿</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 gap-3">
          <div class="text-center text-md-start">
             <a href="index.php" class="btn btn-outline-custom w-100 w-md-auto"><i class="bi bi-arrow-left me-2"></i>เลือกซื้อสินค้าต่อ</a>
          </div>
          
          <div class="d-flex flex-column flex-md-row gap-2">
            <button type="submit" name="update" class="btn btn-update shadow-sm w-100 w-md-auto">
              <i class="bi bi-arrow-clockwise me-2"></i>อัปเดตจำนวน
            </button>
            <a href="checkout.php" class="btn btn-primary-custom shadow-sm w-100 w-md-auto text-center">
              ดำเนินการชำระเงิน <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>
        </div>

      </form>
    <?php endif; ?>
  </div>
</div>

<footer class="text-center">
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
    toast.show();
  });
});
</script>

</body>
</html>