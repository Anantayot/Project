<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?? 'MyCommiss Admin' ?></title>
  <link rel="icon" type="image/png" href="/Project/admin/partials/icon_mycommiss.png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    :root {
      --bg-dark: #121826;      /* สีพื้นหลังหลัก (เข้มกว่านิดหน่อยให้เหมือนภาพ) */
      --bg-card: #1f2937;      /* สีพื้นหลังของการ์ดและ Sidebar */
      --border-color: #374151; /* สีเส้นขอบ */
      --text-main: #f3f4f6;    /* สีข้อความหลัก */
      --text-muted: #9ca3af;   /* สีข้อความรอง */
      --primary: #22c55e;      /* สีเขียวหลัก */
      --primary-hover: #16a34a;
      --danger: #ef4444;       /* สีแดง */
      --warning: #facc15;      /* สีเหลืองสำหรับปุ่มแก้ไข */
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-main);
      font-family: 'Prompt', sans-serif;
      overflow-x: hidden;
      margin: 0;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--bg-dark); }
    ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #4b5563; }

    /* =========================================
       Sidebar CSS
    ========================================= */
    #sidebar {
      background-color: var(--bg-card);
      position: fixed;
      top: 0;
      left: 0;
      width: 260px;
      height: 100vh;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-right: 1px solid var(--border-color);
      z-index: 1050;
      display: flex;
      flex-direction: column;
    }

    .sidebar-brand {
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      font-weight: 700;
      border-bottom: 1px solid var(--border-color);
      color: #fff;
      letter-spacing: 0.5px;
      gap: 10px;
    }

    .sidebar-brand i, .sidebar-brand img {
      color: var(--primary);
      width: 35px;
      height: auto;
    }

    .admin-profile {
      padding: 25px 20px;
      text-align: center;
      border-bottom: 1px solid var(--border-color);
    }

    .admin-profile img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      border: 2px solid var(--primary);
      padding: 3px;
      background: var(--bg-dark);
      object-fit: cover;
      margin-bottom: 10px;
    }

    .admin-profile h6 {
      margin: 0 0 5px 0;
      font-weight: 600;
      color: var(--text-main);
      font-size: 1rem;
    }

    .admin-profile .status-online {
      color: var(--primary);
      font-size: 0.8rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }
    .status-online::before {
      content: '';
      display: block;
      width: 8px;
      height: 8px;
      background-color: var(--primary);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--primary);
    }

    .sidebar-menu {
      list-style: none;
      padding: 15px 15px;
      margin: 0;
      flex-grow: 1;
      overflow-y: auto;
    }

    .sidebar-menu li { margin-bottom: 5px; }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: var(--text-muted);
      text-decoration: none;
      border-radius: 12px;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .sidebar-menu a i { font-size: 1.25rem; transition: 0.3s; }

    .sidebar-menu a:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #fff;
      transform: translateX(4px);
    }

    .sidebar-menu a.active {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 4px 15px rgba(34, 197, 94, 0.25);
    }

    .sidebar-footer {
      padding: 15px;
      border-top: 1px solid var(--border-color);
    }

    .btn-logout {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 12px;
      background: rgba(239, 68, 68, 0.08);
      color: var(--danger);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 10px;
      font-weight: 500;
      text-decoration: none;
      transition: 0.3s;
    }

    .btn-logout:hover {
      background: var(--danger);
      color: #fff;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    /* =========================================
       Main Content & Topbar CSS
    ========================================= */
    .main-wrapper {
      margin-left: 260px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .topbar {
      height: 70px;
      padding: 0 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--bg-dark);
      border-bottom: 1px solid var(--border-color);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .topbar-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #fff;
      margin: 0;
    }

    .btn-storefront {
      border: 1px solid var(--primary);
      color: var(--primary);
      background: transparent;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: 0.3s;
    }
    .btn-storefront:hover {
      background: var(--primary);
      color: #fff;
    }

    .content-area {
      padding: 30px;
      flex-grow: 1;
      animation: fadeUp 0.6s ease-out;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* =========================================
       Global Elements (Cards, Tables, Forms)
       สำหรับแต่งไส้ในที่อยู่ในตัวแปร $pageContent
    ========================================= */
    .card-custom {
      background-color: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 20px;
      margin-top: 15px;
    }

    .btn-add-new {
      background-color: var(--primary);
      color: #fff;
      width: 100%;
      border: none;
      border-radius: 12px;
      padding: 12px;
      font-weight: 500;
      font-size: 1rem;
      margin-bottom: 20px;
      transition: 0.3s;
    }
    .btn-add-new:hover { background-color: var(--primary-hover); color: #fff; }

    /* ปรับแต่ง DataTables ให้เข้ากับตีมมืด */
    table.dataTable {
      border-collapse: collapse !important;
      width: 100% !important;
      color: var(--text-main);
    }
    table.dataTable thead th {
      background-color: var(--primary) !important;
      color: #fff !important;
      border-bottom: none !important;
      padding: 15px !important;
      font-weight: 500;
    }
    table.dataTable thead th:first-child { border-top-left-radius: 8px; }
    table.dataTable thead th:last-child { border-top-right-radius: 8px; }
    table.dataTable tbody tr {
      background-color: transparent !important;
      border-bottom: 1px solid var(--border-color) !important;
    }
    table.dataTable tbody td {
      padding: 15px !important;
      vertical-align: middle;
      border-top: none !important;
    }
    
    /* ปรับช่อง Input ของ DataTables */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input,
    .form-control, .form-select {
      background-color: var(--bg-dark);
      border: 1px solid var(--border-color);
      color: #fff;
      border-radius: 6px;
      padding: 5px 10px;
    }
    .dataTables_wrapper .dataTables_filter input:focus,
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
      background-color: var(--bg-dark);
      color: #fff;
      outline: none;
    }
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
      color: var(--text-muted) !important;
      margin-bottom: 15px;
    }

    /* ปุ่ม Action (แก้ไข/ลบ) */
    .btn-circle {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 1px solid;
      background: transparent;
      transition: all 0.2s ease;
      margin: 0 3px;
    }
    .btn-edit { border-color: var(--warning); color: var(--warning); }
    .btn-edit:hover { background: var(--warning); color: #000; }
    .btn-delete { border-color: var(--danger); color: var(--danger); }
    .btn-delete:hover { background: var(--danger); color: #fff; }

    /* ป้ายสถานะ (Badge) */
    .badge-status {
      background-color: rgba(255, 255, 255, 0.1);
      color: var(--text-muted);
      padding: 5px 12px;
      border-radius: 20px;
      font-weight: 400;
      font-size: 0.85rem;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* =========================================
       Mobile & Overlay
    ========================================= */
    .mobile-navbar {
      display: none;
      height: 70px;
      background: var(--bg-card);
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      border-bottom: 1px solid var(--border-color);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .mobile-navbar .btn-menu {
      background: none;
      border: none;
      color: var(--primary);
      font-size: 1.8rem;
      padding: 0;
    }

    .overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(3px);
      z-index: 1040;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    .overlay.show { opacity: 1; visibility: visible; }

    @media (max-width: 991px) {
      #sidebar { left: -260px; }
      #sidebar.show { left: 0; }
      .main-wrapper { margin-left: 0; }
      .topbar { display: none !important; }
      .mobile-navbar { display: flex; }
      .content-area { padding: 20px; }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="overlay" id="overlay"></div>

  <div class="main-wrapper">

    <div class="topbar">
      <h4 class="topbar-title"><?= $pageTitle ?? 'จัดการลูกค้า' ?></h4>
      <a href="#" class="btn-storefront"><i class="bi bi-shop"></i> ดูหน้าร้าน</a>
    </div>

    <div class="mobile-navbar">
      <button class="btn-menu" id="menuToggle"><i class="bi bi-list"></i></button>
      <h5 class="m-0 text-white"><?= $pageTitle ?? 'จัดการลูกค้า' ?></h5>
      <div></div>
    </div>

    <div class="content-area">
      <?= $pageContent ?? '' ?>
    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.getElementById('menuToggle');
      const overlay = document.getElementById('overlay');

      if (menuToggle) {
        menuToggle.addEventListener('click', () => {
          sidebar.classList.add('show');
          overlay.classList.add('show');
        });
      }

      if (overlay) {
        overlay.addEventListener('click', () => {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }

      window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        }
      });
    });
  </script>

</body>

</html>