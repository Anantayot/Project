<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ดึงรหัสออเดอร์จาก URL มาก่อน
$id = $_GET['id'] ?? null;
if (!$id) die("<div class='alert alert-danger text-center mt-5'>❌ ไม่พบคำสั่งซื้อ</div>");

$pageTitle = "รายละเอียดคำสั่งซื้อ #" . htmlspecialchars($id);

ob_start();

include __DIR__ . "/../partials/connectdb.php";

// ✅ อัปเดตสถานะคำสั่งซื้อ / ชำระเงิน
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST['action'] ?? '';

  // อนุมัติ/ปฏิเสธการชำระเงิน (เดิม)
  if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE orders 
                            SET payment_status='ชำระเงินแล้ว', 
                                admin_verified='อนุมัติ',
                                order_status='กำลังจัดเตรียม'
                            WHERE order_id=?");
    $stmt->execute([$id]);
    echo "<script>alert('✅ อนุมัติการชำระเงินเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
    exit;

  } elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE orders 
                            SET payment_status='ยกเลิก', 
                                admin_verified='ปฏิเสธ',
                                order_status='ยกเลิก'
                            WHERE order_id=?");
    $stmt->execute([$id]);
    echo "<script>alert('❌ ปฏิเสธคำสั่งซื้อนี้แล้ว');window.location='order_view.php?id=$id';</script>";
    exit;
  }

  // ✅ เปลี่ยนสถานะชำระเงิน (ใหม่)
  if ($action === 'update_payment_status') {
    $newPayment = $_POST['payment_status'] ?? '';

    if (in_array($newPayment, ['รอดำเนินการ','ชำระเงินแล้ว','ยกเลิก'])) {

      if ($newPayment === 'ชำระเงินแล้ว') {
        $stmt = $conn->prepare("UPDATE orders 
                                SET payment_status=?, 
                                    admin_verified='อนุมัติ',
                                    order_status='กำลังจัดเตรียม'
                                WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
        echo "<script>alert('💰 ชำระเงินแล้ว → แอดมินอนุมัติ + กำลังจัดเตรียมเรียบร้อย');window.location='order_view.php?id=$id';</script>";
        exit;
      }
      
      elseif ($newPayment === 'ยกเลิก') {
        $stmt = $conn->prepare("UPDATE orders 
                                SET payment_status=?, 
                                    admin_verified='ปฏิเสธ',
                                    order_status='ยกเลิก'
                                WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
        echo "<script>alert('❌ ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
        exit;
      }

      else {
        $stmt = $conn->prepare("UPDATE orders SET payment_status=? WHERE order_id=?");
        $stmt->execute([$newPayment, $id]);
        echo "<script>alert('💰 เปลี่ยนสถานะชำระเงินเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
        exit;
      }
    }
  }

  // ✅ เปลี่ยนสถานะคำสั่งซื้อ (ใหม่)
  if ($action === 'update_order_status') {
    $newOrder = $_POST['order_status'] ?? '';
    if (in_array($newOrder, ['รอดำเนินการ','กำลังจัดเตรียม','จัดส่งแล้ว','สำเร็จ','ยกเลิก'])) {
        
      if ($newOrder === 'ยกเลิก') {
          $stmt = $conn->prepare("UPDATE orders 
                                  SET order_status=?, 
                                      payment_status='ยกเลิก', 
                                      admin_verified='ปฏิเสธ' 
                                  WHERE order_id=?");
          $stmt->execute([$newOrder, $id]);
          echo "<script>alert('❌ ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
          exit;
      } else {
          $stmt = $conn->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
          $stmt->execute([$newOrder, $id]);
          echo "<script>alert('📦 เปลี่ยนสถานะคำสั่งซื้อเรียบร้อยแล้ว');window.location='order_view.php?id=$id';</script>";
          exit;
      }
    }
  }
}

// ✅ ดึงข้อมูลคำสั่งซื้อ
$sql = "SELECT o.*, c.name AS customer_name, c.phone, c.address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) die("<div class='alert alert-danger text-center mt-5'>❌ ไม่พบข้อมูลคำสั่งซื้อในฐานข้อมูล</div>");

// ✅ ดึงรายละเอียดสินค้า
$details = $conn->prepare("SELECT d.*, p.p_name, p.p_image 
                           FROM order_details d
                           LEFT JOIN product p ON d.p_id = p.p_id
                           WHERE d.order_id=?");
$details->execute([$id]);
$items = $details->fetchAll(PDO::FETCH_ASSOC);

/* --- Config สีสถานะให้ตรงกับหน้า Dashboard --- */
$statusColors = [
    'รอดำเนินการ'    => 'custom-yellow', 
    'กำลังจัดเตรียม'  => 'custom-blue',   
    'จัดส่งแล้ว'      => 'custom-blue',       
    'สำเร็จ'         => 'custom-success',       
    'ยกเลิก'         => 'danger'         
];
$verifyColors = [
    'รอตรวจสอบ'     => 'warning text-dark',
    'กำลังตรวจสอบ'   => 'purple',
    'อนุมัติ'         => 'custom-success', 
    'ปฏิเสธ'         => 'danger'
];
$paymentColors = [
    'รอดำเนินการ'    => 'custom-yellow',
    'ชำระเงินแล้ว'    => 'custom-success',
    'ยกเลิก'         => 'danger'
];
?>

<style>
  /* 🎨 ตกแต่งให้เข้ากับหน้า Dashboard หลัก */
  .custom-card {
    background: var(--bg-card, #1e293b);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
  }
  .info-label {
    color: #94a3b8;
    font-size: 0.95rem;
    font-weight: 500;
    width: 140px;
    display: inline-block;
  }
  .info-value {
    color: #f8fafc;
    font-weight: 500;
  }
  
  /* สี Custom Badges */
  .bg-purple { background-color: #8b5cf6 !important; color: #fff; }
  .bg-custom-blue { background-color: #3b82f6 !important; color: #fff; } 
  .bg-custom-success { background-color: #22c55e !important; color: #fff; } 
  .bg-custom-yellow { background-color: #facc15 !important; color: #0f172a !important; } 

  .badge-fixed {
    width: 125px;
    display: inline-block;
    text-align: center;
    font-weight: 600;
    padding: 6px 12px;
  }

  /* ตารางสินค้า */
  .table-custom-header {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important;
    color: #ffffff !important;
    border-bottom: none;
  }
  .table-dark { --bs-table-bg: transparent; --bs-table-color: #e2e8f0; border-color: rgba(255, 255, 255, 0.05); }
  
  /* ฟอร์ม Dropdown */
  .form-select-custom {
    background-color: #0f172a;
    color: #fff;
    border: 1px solid #334155;
    border-radius: 8px;
  }
  .form-select-custom:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
  }

  /* 📱 ปรับแต่งสำหรับ Mobile (แก้ให้อ่านง่ายขึ้นมากๆ) */
  @media (max-width: 767px) {
    .info-label {
      width: 120px;
      margin-bottom: 5px;
    }
    
    .form-wrapper {
      flex-direction: column;
      align-items: stretch !important;
    }

    .form-wrapper form {
      max-width: 100% !important;
      margin-top: 10px;
    }

    /* เปลี่ยนตารางสินค้าเป็น Card บนมือถือ */
    #productTable thead { display: none; }
    
    #productTable tbody tr {
      display: flex;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    #productTable tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none !important;
      padding: 10px 0;
      text-align: right !important;
      border-bottom: 1px dashed rgba(255, 255, 255, 0.1) !important;
    }
    #productTable tbody td:last-child {
      border-bottom: none !important;
      padding-bottom: 0;
    }

    /* เพิ่ม Label ให้ข้อมูลใน Card ฝั่งซ้าย */
    #productTable tbody td:nth-child(1):before { content: "รูปภาพ"; font-weight: 500; color: #94a3b8; margin-right: 15px; }
    #productTable tbody td:nth-child(2):before { content: "ชื่อสินค้า"; font-weight: 500; color: #94a3b8; margin-right: 15px; }
    #productTable tbody td:nth-child(3):before { content: "จำนวน"; font-weight: 500; color: #94a3b8; margin-right: 15px; }
    #productTable tbody td:nth-child(4):before { content: "ราคา/ชิ้น"; font-weight: 500; color: #94a3b8; margin-right: 15px; }
    #productTable tbody td:nth-child(5):before { content: "ยอดรวม"; font-weight: 500; color: #94a3b8; margin-right: 15px; }

    /* ปรับให้รูปกับข้อความไม่เบียดกัน */
    .mobile-product-name { text-align: right; max-width: 160px; word-wrap: break-word; }

    /* ปรับ Tfoot (ยอดรวม) บนมือถือ */
    #productTable tfoot tr {
      display: flex;
      flex-direction: column;
      padding: 15px;
      background: rgba(22, 163, 74, 0.15);
      border-radius: 12px;
      border: 1px solid rgba(34, 197, 94, 0.3);
    }
    #productTable tfoot td {
      display: flex;
      justify-content: space-between;
      padding: 5px 0 !important;
      border: none !important;
      width: 100%;
    }
    #productTable tfoot td:first-child { display: none; } /* ซ่อนช่องว่าง td colspan=3 */
    
    .mobile-total-label { font-size: 1.1rem !important; }
    .mobile-total-value { font-size: 1.4rem !important; text-align: right; }
  }
<style>
  .custom-card { background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 15px; }
  .table-custom-header { background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%) !important; color: #fff !important; }
  .table-dark { --bs-table-bg: transparent; border-color: rgba(255, 255, 255, 0.05); }

  /* 📱 ปรับปรุงพิเศษสำหรับ Mobile (แก้ปัญหาชื่อสินค้าโดนบีบ) */
  @media (max-width: 767px) {
    #productTable thead { display: none; }
    #productTable tbody tr {
      display: block;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    #productTable tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none !important;
      padding: 6px 0;
    }

    /* 🖼️ ปรับส่วนรูปภาพบนมือถือ */
    #productTable tbody td:nth-child(1) {
      justify-content: center;
      border-bottom: 1px solid rgba(255,255,255,0.1) !important;
      padding-bottom: 15px;
      margin-bottom: 10px;
    }
    #productTable tbody td:nth-child(1) img { width: 100px !important; height: 100px !important; }

    /* 🏷️ ใส่หัวข้อกำกับด้านซ้าย */
    #productTable tbody td:nth-child(2):before { content: "สินค้า"; color: #94a3b8; font-size: 0.85rem; }
    #productTable tbody td:nth-child(3):before { content: "จำนวน"; color: #94a3b8; font-size: 0.85rem; }
    #productTable tbody td:nth-child(4):before { content: "ราคา/ชิ้น"; color: #94a3b8; font-size: 0.85rem; }
    #productTable tbody td:nth-child(5):before { content: "ยอดรวม"; color: #94a3b8; font-size: 0.85rem; }

    /* ✍️ ปรับตัวหนังสือ */
    .mobile-product-name { 
      text-align: right; 
      max-width: 70%; 
      font-weight: 600; 
      color: #fff !important; 
      word-wrap: break-word; 
    }
    .text-price-white { color: #fff !important; font-weight: 500; }
    .text-subtotal-cyan { color: #22d3ee !important; font-weight: 700; font-size: 1.1rem; }

    /* 💰 ส่วนยอดรวมสุทธิด้านล่าง */
    #productTable tfoot tr {
      display: block;
      background: rgba(22, 163, 74, 0.15);
      border-radius: 12px;
      padding: 15px;
      border: 1px solid rgba(34, 197, 94, 0.3);
    }
    #productTable tfoot td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      border: none !important;
      padding: 0 !important;
    }
    #productTable tfoot td:first-child { display: none; }
    .total-label { font-size: 1.1rem !important; }
    .total-value { font-size: 1.5rem !important; color: #4ade80 !important; }
  }
</style>

<div class="card custom-card shadow-lg mb-4">
  <div class="card-body p-0">
    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
      <h5 class="fw-bold text-white mb-0"><i class="bi bi-basket2 me-2 text-warning"></i> รายการสินค้าที่สั่งซื้อ</h5>
    </div>
    
    <div class="table-responsive">
      <table id="productTable" class="table table-dark align-middle text-center mb-0 border-0 w-100">
        <thead>
          <tr class="table-custom-header">
            <th class="py-3 text-start ps-4">รูปภาพ</th>
            <th class="py-3 text-start">ชื่อสินค้า</th>
            <th class="py-3">จำนวน</th>
            <th class="py-3 text-end">ราคา/ชิ้น</th>
            <th class="py-3 text-end pe-4">ยอดรวม</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $totalSum = 0;
          foreach ($items as $it): 
            $totalSum += $it['subtotal'];
          ?>
          <tr>
            <td class="text-start py-3 ps-md-4">
              <img src="/Project/admin/uploads/<?= htmlspecialchars($it['p_image'] ?? 'noimg.png') ?>" width="60" class="rounded shadow-sm border border-secondary" style="object-fit: cover; aspect-ratio: 1/1;">
            </td>
            <td class="text-start fw-medium text-white mobile-product-name"><?= htmlspecialchars($it['p_name']) ?></td>
            <td><span class="badge bg-secondary rounded-pill px-3"><?= (int)$it['quantity'] ?></span></td>
            <td class="text-end text-white text-price-white">฿<?= number_format($it['price'], 2) ?></td>
            <td class="text-end text-subtotal-cyan pe-md-4">฿<?= number_format($it['subtotal'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="border-0 d-none d-md-table-cell"></td>
            <td class="text-end fw-bold text-white border-0 pt-4 pb-4 total-label">ยอดรวมทั้งหมด:</td>
            <td class="text-end fw-bold text-success fs-4 border-0 pe-md-4 pt-4 pb-4 total-value">฿<?= number_format($totalSum, 2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
include __DIR__ . "/../partials/layout.php";
?>