<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("../../includes/connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../../login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id คำสั่งซื้อหรือไม่
if (!isset($_GET['id'])) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสคำสั่งซื้อ</p>");
}

$order_id = intval($_GET['id']);

// ✅ ดึงข้อมูลคำสั่งซื้อพร้อมชื่อลูกค้า และอีเมล (เพิ่ม c.email)
$stmt = $conn->prepare("SELECT o.*, c.name, c.email 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.customer_id 
                        WHERE o.order_id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบคำสั่งซื้อนี้ หรือคุณไม่มีสิทธิ์ดู</p>");
}

/* =======================================================
   ✅ ฟังก์ชันสร้าง QR พร้อมเพย์ (มาตรฐาน EMVCo)
   ======================================================= */
function generatePromptPayPayload($promptPayID, $amount = 0.00) {
  $id = preg_replace('/[^0-9]/', '', $promptPayID);
  if (strlen($id) == 10) {
    $id = '0066' . substr($id, 1);
  }

  $data = [
    '00' => '01',
    '01' => '11',
    '29' => formatField('00', 'A000000677010111') . formatField('01', $id),
    '53' => '764',
    '54' => sprintf('%0.2f', $amount),
    '58' => 'TH',
  ];

  $payload = '';
  foreach ($data as $id => $val) {
    $payload .= $id . sprintf('%02d', strlen($val)) . $val;
  }
  $payload .= '6304';
  return $payload . strtoupper(crc16($payload));
}

function formatField($id, $value) {
  return $id . sprintf('%02d', strlen($value)) . $value;
}

function crc16($data) {
  $crc = 0xFFFF;
  for ($i = 0; $i < strlen($data); $i++) {
    $crc ^= ord($data[$i]) << 8;
    for ($j = 0; $j < 8; $j++) {
      if ($crc & 0x8000)
        $crc = ($crc << 1) ^ 0x1021;
      else
        $crc <<= 1;
      $crc &= 0xFFFF;
    }
  }
  return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

/* =======================================================
    ✅ ยืนยันการชำระเงิน (อัปโหลดสลิป + อัปเดตสถานะ) มีระบบความปลอดภัย
    ======================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $uploadDir = __DIR__ . "/../../../admin/uploads/slips/";

  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  if (!is_writable($uploadDir)) die("<script>alert('❌ ระบบ Server ไม่มีสิทธิ์เขียนไฟล์'); window.history.back();</script>");

  $fileName = "";
  
  // 🔒 1. ตรวจสอบว่ามีการอัปโหลดไฟล์มาจริงๆ และไม่มี Error
  if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['slip']['tmp_name'];
      $fileSize = $_FILES['slip']['size'];
      
      // 🔒 2. ตรวจสอบขนาดไฟล์ (จำกัดไว้ที่ไม่เกิน 5MB)
      $maxSize = 5 * 1024 * 1024; // 5 MB
      if ($fileSize > $maxSize) {
          echo "<script>alert('❌ ไฟล์มีขนาดใหญ่เกินไป (จำกัดไม่เกิน 5MB)'); window.history.back();</script>";
          exit;
      }

      // 🔒 3. ตรวจสอบประเภทไฟล์จริงๆ จากเนื้อหา (MIME Type) ป้องกันการเปลี่ยนนามสกุลไฟล์หลอกๆ
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $tmpName);
      finfo_close($finfo);

      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!in_array($mimeType, $allowedMimeTypes)) {
          echo "<script>alert('❌ อนุญาตให้อัปโหลดเฉพาะไฟล์รูปภาพเท่านั้น (JPG, PNG, GIF, WEBP)'); window.history.back();</script>";
          exit;
      }

      // 🔒 4. ตรวจสอบนามสกุลไฟล์ (Extension Whitelist)
      $ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!in_array($ext, $allowedExtensions)) {
          echo "<script>alert('❌ นามสกุลไฟล์ไม่ถูกต้อง'); window.history.back();</script>";
          exit;
      }

      // 🔒 5. สุ่มชื่อไฟล์ใหม่ให้เดาได้ยาก ป้องกันการเกิดชื่อซ้ำและช่องโหว่ LFI
      $fileName = "slip_" . date('YmdHis') . "_" . uniqid() . "." . $ext;
      $targetFile = $uploadDir . $fileName;

      if (!move_uploaded_file($tmpName, $targetFile)) {
          echo "<script>alert('❌ ไม่สามารถบันทึกไฟล์ลงเซิร์ฟเวอร์ได้'); window.history.back();</script>";
          exit;
      }
      
  } else {
      // กรณีไม่แนบสลิปมา หรือเกิด Error ระหว่างอัปโหลด
      echo "<script>alert('❌ กรุณาแนบภาพสลิปที่ถูกต้อง'); window.history.back();</script>";
      exit;
  }

  // ✅ ต้องดึงยอดเงินจาก DB อีกครั้ง เพื่อให้มีค่าส่งไป Webhook
  $stmt_check = $conn->prepare("SELECT total_price FROM orders WHERE order_id = ? AND customer_id = ?");
  $stmt_check->execute([$order_id, $customer_id]);
  $order_data = $stmt_check->fetch(PDO::FETCH_ASSOC);
  $total_amount = $order_data ? $order_data['total_price'] : 0;

  // ✅ อัปเดตสถานะการชำระเงิน
  $stmt = $conn->prepare("UPDATE orders 
                          SET payment_status = 'รอดำเนินการ',
                              admin_verified = 'กำลังตรวจสอบ',
                              slip_image = :slip,
                              payment_date = NOW()
                          WHERE order_id = :oid AND customer_id = :cid");
  $stmt->execute([
      ':slip' => $fileName,
      ':oid' => $order_id,
      ':cid' => $customer_id
  ]);

  /* =======================================================
      ✅ ส่งข้อมูลไปยัง Webhook หลังจากบันทึก DB สำเร็จ
      ======================================================= */
  $webhook_url = "http://103.40.119.91:5678/webhook-test/778284f3-0ba4-473f-9d10-fee5d2416f4f";

  // ✅ สร้างรหัสคำสั่งซื้อแบบ #00154
  $formatted_order_id = "#" . str_pad($order_id, 5, '0', STR_PAD_LEFT);

  $payload_data = [
      'order_id'      => $formatted_order_id, // ส่งแบบ #00154
      'customer_id'   => $customer_id,
      'customer_name' => $order['name'],
      'email'         => $order['email'],     // ส่ง email ไปด้วย
      'amount'        => $total_amount,
      'slip_image'    => $fileName,
      'status'        => 'payment_submitted',
      'timestamp'     => date('Y-m-d H:i:s')
  ];

  // ตรวจสอบว่ามีฟังก์ชัน curl_init ไหม (กัน Error 500 กรณี Server ไม่รองรับ)
  if (function_exists('curl_init')) {
      $ch = curl_init($webhook_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
      curl_exec($ch);
      curl_close($ch);
  }

  echo "<script>
      alert('✅ แจ้งชำระเงินเรียบร้อยแล้ว! ระบบจะทำการตรวจสอบโดยแอดมิน');
      window.location='../order_detail.php?id=$order_id';
  </script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แจ้งชำระเงิน | MyCommiss</title>
  <link rel="icon" type="image/png" href="../../includes/icon_mycommiss.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  
  <style>
    body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
    .payment-wrapper { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }

    /* 🔹 Card Layout */
    .card-payment {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      background: #fff;
      width: 100%;
      max-width: 550px;
      overflow: hidden;
    }
    .card-header-payment {
      background-color: #D10024;
      color: #fff;
      padding: 20px;
      text-align: center;
      font-size: 1.25rem;
      font-weight: 700;
    }

    /* 🔹 QR Code Box */
    .qr-box {
      background: #fff;
      padding: 20px;
      border-radius: 15px;
      border: 2px dashed #D10024;
      display: inline-block;
      margin: 15px 0;
    }
    #qrcode img { margin: 0 auto; } /* จัด QR ให้อยู่กึ่งกลาง */

    .total-price {
      font-size: 2.2rem;
      font-weight: 700;
      color: #D10024;
      margin-bottom: 0;
    }

    /* 🔹 Form Elements */
    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #ddd;
      background-color: #fcfcfc;
    }
    .form-control:focus {
      border-color: #D10024;
      box-shadow: 0 0 0 0.2rem rgba(209, 0, 36, 0.15);
      background-color: #fff;
    }

    /* 🔹 Buttons */
    .btn-submit {
      background-color: #D10024;
      color: #fff;
      border-radius: 50px;
      font-weight: 600;
      padding: 12px;
      border: none;
      transition: 0.3s;
    }
    .btn-submit:hover {
      background-color: #a5001b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(209, 0, 36, 0.2);
    }
    .btn-outline-custom {
      border: 1px solid #ddd;
      color: #555;
      border-radius: 50px;
      font-weight: 500;
      padding: 10px;
      transition: 0.3s;
      background: #fff;
    }
    .btn-outline-custom:hover { background-color: #f1f1f1; color: #333; }

    /* 🔹 Footer */
    footer {
      background-color: #fff;
      color: #6c757d;
      padding: 20px;
      font-size: 0.9rem;
      border-top: 1px solid #eee;
      text-align: center;
    }
  </style>
</head>
<body>

<?php include("../../includes/navbar_user.php"); ?>

<div class="payment-wrapper">
  <div class="card-payment">
    <div class="card-header-payment">
      <i class="bi bi-qr-code-scan me-2"></i>แจ้งชำระเงิน
    </div>
    <div class="card-body p-4 p-md-5">
      
      <div class="text-center mb-4">
        <p class="text-muted mb-1">คำสั่งซื้อหมายเลข</p>
        <h4 class="fw-bold text-dark">#<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></h4>
      </div>

      <?php if ($order['payment_method'] === 'QR'): ?>
        <?php
          $shopPromptPay = "0903262100"; // หมายเลขพร้อมเพย์ร้าน
          $payload = generatePromptPayPayload($shopPromptPay, $order['total_price']);
        ?>
        <div class="text-center bg-light rounded-4 p-4 mb-4 border">
          <p class="fw-semibold text-secondary mb-3">สแกน QR Code เพื่อชำระเงินผ่านแอปธนาคาร</p>
          
          <div class="qr-box shadow-sm">
            <div id="qrcode"></div>
          </div>
          
          <div class="mt-3">
            <div class="text-muted small">ยอดที่ต้องชำระ</div>
            <div class="total-price"><?= number_format($order['total_price'], 2) ?> ฿</div>
          </div>
          
        </div>

        <script>
          // สร้าง QR Code อัตโนมัติ
          const payload = "<?= $payload ?>";
          new QRCode(document.getElementById("qrcode"), {
            text: payload,
            width: 180,
            height: 180,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
          });
        </script>
      <?php endif; ?>

      <hr class="text-muted opacity-25 my-4">

      <form method="post" enctype="multipart/form-data">
        <div class="mb-4">
          <label for="slip" class="form-label fw-semibold text-dark">
            <i class="bi bi-image me-2 text-muted"></i>แนบหลักฐานการโอนเงิน (สลิป) <span class="text-danger">*</span>
          </label>
          <input type="file" name="slip" id="slip" class="form-control" accept="image/*" required>
          <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>กรุณาแนบภาพสลิปที่เห็นยอดเงินและวันที่ชัดเจน (ขนาดไม่เกิน 5MB)</div>
        </div>

        <div class="d-grid gap-3 mt-5">
          <button type="submit" class="btn btn-submit fs-5">
            <i class="bi bi-cloud-arrow-up me-2"></i>ยืนยันการชำระเงิน
          </button>
          <div class="row g-2">
            <div class="col-6">
              <a href="../orders.php" class="btn btn-outline-custom w-100"><i class="bi bi-clock-history me-2"></i>ประวัติสั่งซื้อ</a>
            </div>
            <div class="col-6">
              <a href="../order_detail.php?id=<?= $order_id ?>" class="btn btn-outline-custom w-100"><i class="bi bi-file-earmark-text me-2"></i>รายละเอียด</a>
            </div>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<footer>
  © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>