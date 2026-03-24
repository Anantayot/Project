<?php
session_start();
include("connectdb.php");

// 🔹 ดึงหมวดหมู่ทั้งหมดมาแสดงใน dropdown
$cats = $conn->query("SELECT * FROM category ORDER BY cat_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// รับค่าค้นหา
$search = $_GET['search'] ?? '';
$cat_id = $_GET['cat'] ?? '';

// 🔹 ดึงข้อมูลสินค้า (เหมือนเดิม)
if (empty($search) && empty($cat_id)) {
    $newProducts = $conn->query("SELECT p.*, c.cat_name FROM product p LEFT JOIN category c ON p.cat_id = c.cat_id ORDER BY p_id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $bestSellers = $conn->query("SELECT p.*, c.cat_name, SUM(d.quantity) AS total_sold FROM order_details d JOIN product p ON d.p_id = p.p_id LEFT JOIN category c ON p.cat_id = c.cat_id GROUP BY p.p_id ORDER BY total_sold DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $randomProducts = $conn->query("SELECT p.*, c.cat_name FROM product p LEFT JOIN category c ON p.cat_id = c.cat_id ORDER BY RAND() LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT p.*, c.cat_name FROM product p LEFT JOIN category c ON p.cat_id = c.cat_id WHERE 1";
    $params = [];
    if (!empty($search)) { $sql .= " AND (p.p_name LIKE :kw OR c.cat_name LIKE :kw)"; $params['kw'] = "%$search%"; }
    if (!empty($cat_id) && $cat_id !== 'all') { $sql .= " AND p.cat_id = :cat"; $params['cat'] = $cat_id; }
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

        /* 🔍 Search Bar Style - ปรับให้เหมือนรูปที่ส่งมา */
        .search-container { max-width: 850px; margin: 30px auto; padding: 0 15px; }
        .search-bar {
            background: #fff;
            border: 1px solid #D10024;
            border-radius: 50px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .search-bar select {
            border: none; background: none; width: 30%; min-width: 120px;
            padding: 10px; border-right: 1px solid #eee; outline: none; cursor: pointer;
        }
        .search-bar input { border: none; background: none; flex-grow: 1; padding: 10px 20px; outline: none; }
        .search-bar button {
            background: #D10024; border: none; color: #fff; border-radius: 50%;
            width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .search-bar button:hover { background: #a5001b; transform: scale(1.05); }

        @media (max-width: 576px) {
            .search-bar { flex-direction: column; border-radius: 20px; padding: 10px; }
            .search-bar select, .search-bar input { width: 100%; border-right: none; border-bottom: 1px solid #eee; }
            .search-bar button { width: 100%; border-radius: 12px; margin-top: 10px; height: 45px; }
        }

        .section-title { font-weight: 700; color: #D10024; margin: 40px 0 20px; text-align:center; position: relative; }
        .section-title::after { content: ''; display: block; width: 50px; height: 3px; background: #D10024; margin: 8px auto; }

        /* 📦 Product Card Style */
        .product-card {
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: #fff;
            height: 100%;
            overflow: hidden;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: #D10024; }
        .product-card .img-wrapper { padding: 15px; background: #fff; }
        .product-card img {
            height: 180px;
            object-fit: contain; /* ปรับให้รูปไม่เบี้ยวและเห็นครบ */
            width: 100%;
        }
        
        .card-title {
            font-size: 0.95rem;
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* ชื่อสินค้า 2 บรรทัด */
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
            margin-bottom: 10px;
            color: #333;
        }

        @media (max-width: 768px) {
            .product-card img { height: 140px; }
            .card-title { font-size: 0.85rem; }
        }

        .swiper { padding-bottom: 50px; }
        .swiper-button-next, .swiper-button-prev { color: #D10024; transform: scale(0.6); }
        .category-header { font-size: 1.2rem; font-weight: 700; color: #333; margin-top: 40px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        footer { padding: 40px; border-top: 1px solid #eee; margin-top: 60px; background: #fdfdfd; }
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
            <?php 
                $grouped = ($cat_id === 'all') ? [] : ['' => $searchResults];
                if ($cat_id === 'all') {
                    foreach ($searchResults as $p) {
                        $cat = $p['cat_name'] ?: 'ทั่วไป';
                        $grouped[$cat][] = $p;
                    }
                }
                foreach ($grouped as $catName => $products):
            ?>
                <?php if($catName): ?><h5 class="category-header"><?= htmlspecialchars($catName) ?></h5><?php endif; ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3 mt-1 mb-4">
                    <?php foreach ($products as $p):
                        $img = "../admin/uploads/" . $p['p_image'];
                        if (!file_exists($img) || empty($p['p_image'])) $img = "img/default.png";
                    ?>
                        <div class="col">
                            <div class="product-card card">
                                <div class="img-wrapper">
                                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
                                </div>
                                <div class="card-body text-center d-flex flex-column pt-0">
                                    <h6 class="card-title"><?= htmlspecialchars($p['p_name']) ?></h6>
                                    <p class="fw-bold text-danger mb-3 mt-auto fs-5"><?= number_format($p['p_price'], 2) ?>.-</p>
                                    <a href="product_detail.php?id=<?= $p['p_id'] ?>" class="btn btn-danger w-100 rounded-pill py-2 shadow-sm">ดูรายละเอียด</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">ไม่พบสินค้าที่คุณต้องการ ลองค้นหาใหม่อีกครั้ง</p>
            </div>
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
                            <div class="img-wrapper">
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
                            </div>
                            <div class="card-body text-center d-flex flex-column pt-0">
                                <h6 class="card-title"><?= htmlspecialchars($p['p_name']) ?></h6>
                                <?php if(isset($p['total_sold'])): ?>
                                    <span class="badge bg-warning text-dark mb-2 mx-auto">ขายแล้ว <?= $p['total_sold'] ?> ชิ้น</span>
                                <?php endif; ?>
                                <p class="fw-bold text-danger mt-auto mb-3 fs-5"><?= number_format($p['p_price'], 2) ?>.-</p>
                                <a href="product_detail.php?id=<?= $p['p_id'] ?>" class="btn btn-danger w-100 rounded-pill shadow-sm">ดูรายละเอียด</a>
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

<footer class="text-center mt-5">
    <p class="small text-muted mb-0">© <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    document.querySelectorAll('.mySwiper').forEach(swiperEl => {
        new Swiper(swiperEl, {
            slidesPerView: 2,
            spaceBetween: 15,
            autoplay: { delay: 4000, disableOnInteraction: false },
            navigation: {
                nextEl: swiperEl.querySelector('.swiper-button-next'),
                prevEl: swiperEl.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                992: { slidesPerView: 4 },
                1200: { slidesPerView: 5 },
            },
        });
    });
</script>
</body>
</html>