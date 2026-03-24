<?php
session_start();
include("connectdb.php");

// 🔹 ดึงหมวดหมู่ทั้งหมดมาแสดงใน dropdown
$cats = $conn->query("SELECT * FROM category ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// รับค่าค้นหา
$search = $_GET['search'] ?? '';
$cat_id = $_GET['cat'] ?? '';

// 🔹 ดึงข้อมูลสินค้า 3 ประเภท (เมื่อไม่มีการค้นหา)
if (empty($search) && empty($cat_id)) {
  $newProducts = $conn->query("
    SELECT p.*, c.cat_name FROM product p 
    LEFT JOIN category c ON p.cat_id = c.cat_id 
    ORDER BY p_id DESC LIMIT 10
  ")->fetchAll(PDO::FETCH_ASSOC);

  $bestSellers = $conn->query("
    SELECT p.*, c.cat_name, SUM(d.quantity) AS total_sold
    FROM order_details d
    JOIN product p ON d.p_id = p.p_id
    LEFT JOIN category c ON p.cat_id = c.cat_id
    GROUP BY p.p_id
    ORDER BY total_sold DESC
    LIMIT 10
  ")->fetchAll(PDO::FETCH_ASSOC);

  $randomProducts = $conn->query("
    SELECT p.*, c.cat_name FROM product p 
    LEFT JOIN category c ON p.cat_id = c.cat_id 
    ORDER BY RAND() LIMIT 10
  ")->fetchAll(PDO::FETCH_ASSOC);
} else {
  $sql = "SELECT p.*, c.cat_name FROM product p LEFT JOIN category c ON p.cat_id = c.cat_id WHERE 1";
  $params = [];
  if (!empty($search)) {
    $sql .= " AND (p.p_name LIKE :kw OR c.cat_name LIKE :kw)";
    $params['kw'] = "%$search%";
  }
  if (!empty($cat_id) && $cat_id !== 'all') {
    $sql .= " AND p.cat_id = :cat";
    $params['cat'] = $cat_id;
  }
  $sql .= " ORDER BY c.cat_name ASC, p.p_name ASC";
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);
  $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หน้าร้าน | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>

  <style>
    body { background: #fff; font-family: "Prompt", sans-serif; overflow-x: hidden; }

    .navbar { background: #fff; border-bottom: 3px solid #D10024; }
    .navbar-brand { color: #D10024 !important; font-weight: 700; font-size: 1.4rem; }

    /* 🔍 Responsive Search Bar */
    .search-container {
      max-width: 900px;
      margin: 20px auto;
      padding: 0 15px;
    }
    .search-bar {
      background: #fff;
      border: 2px solid #D10024;
      border-radius: 50px;
      padding: 5px 10px;
      display: flex;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .search-bar select {
      border: none;
      background: none;
      width: 30%;
      min-width: 100px;
      padding: 10px;
      border-right: 1px solid #eee;
      outline: none;
    }
    .search-bar input {
      border: none;
      background: none;
      flex-grow: 1;
      padding: 10px 15px;
      outline: none;
    }
    .search-bar button {
      background: #D10024;
      border: none;
      color: #fff;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: 0.3s;
    }
    .search-bar button:hover { background: #a5001b; }

    /* ปรับแต่งบนมือถือ */
    @media (max-width: 576px) {
      .search-bar {
        flex-direction: column;
        border-radius: 15px;
        padding: 10px;
      }
      .search-bar select, .search-bar input {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #eee;
        margin-bottom: 5px;
      }
      .search-bar button {
        width: 100%;
        border-radius: 10px;
        height: 40px;
      }
      .section-title { font-size: 1.3rem; }
    }

    .section-title { font-weight: 700; color: #D10024; margin: 30px 0 20px; text-align:center; }

    /* 📦 Product Card */
    .product-card {
      border: 1px solid #eee;
      border-radius: 12px;
      transition: all 0.3s ease;
      background: #fff;
      height: 100%;
    }
    .product-card:hover {
      transform: translateY(-4px);
      border-color: #D10024;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .product-card img {
      height: 220px;
      object-fit: cover;
      width: 100%;
      border-top-left-radius: 11px;
      border-top-right-radius: 11px;
    }
    /* ปรับรูปบนมือถือให้เล็กลง */
    @media (max-width: 768px) {
      .product-card img { height: 160px; }
      .product-card .card-body { padding: 10px; }
      .product-card h6 { font-size: 0.9rem; }
    }

    .swiper { width: 100%; padding-bottom: 40px; }
    .swiper-button-next, .swiper-button-prev { color: #D10024; transform: scale(0.7); }

    .category-header {
      font-size: 1.1rem;
      font-weight: 600;
      color: #D10024;
      margin-top: 30px;
      border-left: 4px solid #D10024;
      padding-left: 10px;
    }
    footer { padding: 30px; border-top: 3px solid #D10024; margin-top: 50px; background: #f8f9fa; }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="container-fluid container-md">

  <div class="search-container">
    <form method="get" class="search-bar">
      <select name="cat">
        <option value="">-- ประเภท --</option>
        <option value="all" <?= $cat_id == 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= $c['cat_id'] ?>" <?= $cat_id == $c['cat_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['cat_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="search" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <?php if (!empty($search) || !empty($cat_id)): ?>
    <h3 class="section-title"><?= ($cat_id === 'all') ? 'สินค้าทั้งหมด' : 'ผลการค้นหา' ?></h3>

    <?php if (count($searchResults) > 0): ?>
      <?php if ($cat_id === 'all'): ?>
        <?php
          $grouped = [];
          foreach ($searchResults as $p) {
            $cat = $p['cat_name'] ?: 'ไม่มีหมวดหมู่';
            $grouped[$cat][] = $p;
          }
          foreach ($grouped as $catName => $products):
        ?>
          <h5 class="category-header"><?= htmlspecialchars($catName) ?></h5>
          <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mt-1">
            <?php foreach ($products as $p):
              $img = "../admin/uploads/" . $p['p_image'];
              if (!file_exists($img) || empty($p['p_image'])) $img = "img/default.png";
            ?>
              <div class="col">
                <div class="product-card card">
                  <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
                  <div class="card-body text-center d-flex flex-column">
                    <h6 class="text-truncate mb-2"><?= htmlspecialchars($p['p_name']) ?></h6>
                    <p class="fw-bold text-danger mb-2 mt-auto"><?= number_format($p['p_price'], 2) ?>.-</p>
                    <a href="product_detail.php?id=<?= $p['p_id'] ?>" class="btn btn-sm btn-danger w-100 mt-auto">ดูรายละเอียด</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
          <?php foreach ($searchResults as $p):
            $img = "../admin/uploads/" . $p['p_image'];
            if (!file_exists($img) || empty($p['p_image'])) $img = "img/default.png";
          ?>
            <div class="col">
              <div class="product-card card">
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
                <div class="card-body text-center d-flex flex-column">
                  <h6 class="text-truncate mb-2"><?= htmlspecialchars($p['p_name']) ?></h6>
                  <p class="fw-bold text-danger mb-2 mt-auto"><?= number_format($p['p_price'], 2) ?>.-</p>
                  <a href="product_detail.php?id=<?= $p['p_id'] ?>" class="btn btn-sm btn-danger w-100">ดูรายละเอียด</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="text-center text-muted mt-5">😢 ไม่พบสินค้าที่คุณกำลังมองหา</p>
    <?php endif; ?>

  <?php else: ?>
    <?php 
      $sections = [
        ['title' => 'สินค้าใหม่ล่าสุด', 'data' => $newProducts],
        ['title' => 'สินค้าขายดีที่สุด', 'data' => $bestSellers],
        ['title' => 'สินค้าแนะนำ', 'data' => $randomProducts]
      ];
      foreach ($sections as $sec):
    ?>
    <h3 class="section-title"><?= $sec['title'] ?></h3>
    <div class="swiper mySwiper">
      <div class="swiper-wrapper">
        <?php foreach ($sec['data'] as $p):
          $img = "../admin/uploads/" . $p['p_image'];
          if (!file_exists($img) || empty($p['p_image'])) $img = "img/default.png";
        ?>
          <div class="swiper-slide h-auto">
            <div class="product-card card">
              <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
              <div class="card-body text-center d-flex flex-column">
                <h6 class="text-truncate mb-2"><?= htmlspecialchars($p['p_name']) ?></h6>
                <?php if(isset($p['total_sold'])): ?>
                    <span class="badge bg-warning text-dark mb-2 mx-auto">ขายแล้ว <?= $p['total_sold'] ?></span>
                <?php endif; ?>
                <p class="fw-bold text-danger mt-auto mb-2"><?= number_format($p['p_price'], 2) ?>.-</p>
                <a href="product_detail.php?id=<?= $p['p_id'] ?>" class="btn btn-sm btn-danger w-100">ดูรายละเอียด</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-button-next d-none d-md-flex"></div>
      <div class="swiper-button-prev d-none d-md-flex"></div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<footer class="text-center">
  <p class="small text-muted mb-0">© <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  document.querySelectorAll('.mySwiper').forEach(swiperEl => {
    new Swiper(swiperEl, {
      slidesPerView: 2, // เริ่มต้นที่ 2 รูปสำหรับมือถือ
      spaceBetween: 15,
      autoplay: { delay: 4000 },
      navigation: {
        nextEl: swiperEl.querySelector('.swiper-button-next'),
        prevEl: swiperEl.querySelector('.swiper-button-prev'),
      },
      breakpoints: {
        640: { slidesPerView: 2 },
        768: { slidesPerView: 3 },
        1024: { slidesPerView: 4 },
        1400: { slidesPerView: 5 },
      },
    });
  });
</script>
</body>
</html>