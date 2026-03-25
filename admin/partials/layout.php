<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?? 'MyCommiss Admin' ?></title>
  <link rel="icon" type="image/png" href="/mycommiss/admin/partials/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    :root {
      --bg-dark: #0f172a; --bg-card: #1e293b; --border-color: #334155;
      --text-main: #f8fafc; --text-muted: #94a3b8;
      --primary: #22c55e; --primary-hover: #16a34a; --danger: #ef4444;
    }

    body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Prompt', sans-serif; overflow-x: hidden; margin: 0; }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--bg-dark); }
    ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }

    /* Sidebar CSS */
    #sidebar { background-color: var(--bg-card); position: fixed; top: 0; left: 0; width: 260px; height: 100vh; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-right: 1px solid var(--border-color); z-index: 1050; display: flex; flex-direction: column; }
    .sidebar-brand { height: 70px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; border-bottom: 1px solid var(--border-color); color: #fff; letter-spacing: 0.5px; }
    .sidebar-brand i { color: var(--primary); font-size: 1.6rem; }
    .admin-profile { padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color); }
    .admin-profile img { width: 65px; height: 65px; border-radius: 50%; border: 2px solid var(--primary); padding: 3px; background: var(--bg-dark); object-fit: cover; margin-bottom: 10px; }
    .admin-profile h6 { margin: 0; font-weight: 600; color: var(--text-main); }
    .admin-profile small { color: var(--primary); font-size: 0.8rem; font-weight: 500; }
    .sidebar-menu { list-style: none; padding: 15px 10px; margin: 0; flex-grow: 1; overflow-y: auto; }
    .sidebar-menu li { margin-bottom: 5px; }
    .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: var(--text-muted); text-decoration: none; border-radius: 12px; transition: all 0.3s ease; font-weight: 500; }
    .sidebar-menu a i { font-size: 1.25rem; transition: 0.3s; }
    .sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(5px); }
    .sidebar-menu a.active { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%); color: #fff; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.25); }
    .sidebar-footer { padding: 15px; border-top: 1px solid var(--border-color); }
    .btn-logout { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; font-weight: 500; text-decoration: none; transition: 0.3s; }
    .btn-logout:hover { background: var(--danger); color: #fff; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }

    /* Main Content & Topbar CSS */
    .main-wrapper { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .topbar { height: 70px; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1000; }
    .mobile-navbar { display: none; height: 70px; background: var(--bg-card); align-items: center; justify-content: space-between; padding: 0 20px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1000; }
    .mobile-navbar .btn-menu { background: none; border: none; color: var(--primary); font-size: 1.8rem; padding: 0; }
    .mobile-navbar .brand-mobile { font-weight: 700; color: #fff; font-size: 1.25rem; margin: 0; }
    .content-area { padding: 30px; flex-grow: 1; animation: fadeUp 0.6s ease-out; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* Overlay CSS */
    .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px); z-index: 1040; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
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
    
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="content-area">
      <?= $pageContent ?? '' ?>
    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.getElementById('menuToggle');
      const overlay = document.getElementById('overlay');

      if(menuToggle){
        menuToggle.addEventListener('click', () => {
          sidebar.classList.add('show');
          overlay.classList.add('show');
        });
      }

      if(overlay){
        overlay.addEventListener('click', () => {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }

      window.addEventListener('resize', () => {
        if(window.innerWidth >= 992){
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        }
      });
    });
  </script>

</body>
</html>